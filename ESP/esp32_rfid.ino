#include <Arduino.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>
#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <ArduinoJson.h>

// ==== CONFIGURE THESE ====
// WiFi
const char* WIFI_SSID = "seoultosoulphasus";
const char* WIFI_PASS = "praiseGod0813!";

// API
const String API_BASE     = "http://192.168.50.197:8000/api";
const String SERIAL_NO    = "room-101";  // ← was DEVICE_ID
const String DEVICE_TOKEN = "VpAclEfle3OEFET1FPybWDILVxnpQtpaptqp6fiContWSBT2zTuooKgnt4rA30Ky";

// LCD (adjust I2C address after scanning!)
LiquidCrystal_I2C lcd(0x27, 16, 2);   // If blank, run I2C scanner and update

// RFID (RC522)
#define SS_PIN   D3
#define RST_PIN  D0
MFRC522 mfrc522(SS_PIN, RST_PIN);

// Buzzer (active buzzer MH-FMD)
#define BUZZER_PIN D4

// Debounce UID
String lastUID = "";
unsigned long lastScanMs = 0;
const unsigned long SCAN_COOLDOWN_MS = 2500;

inline void buzzOn()  { digitalWrite(BUZZER_PIN, LOW); }   // active-low
inline void buzzOff() { digitalWrite(BUZZER_PIN, HIGH); }  // idle

void beepOK() {
  Serial.println("[BUZZER] OK beep");
  buzzOn();  delay(100);
  buzzOff(); delay(100);
  buzzOn();  delay(100);
  buzzOff(); // IMPORTANT: end off
}

void beepErr() {
  Serial.println("[BUZZER] ERROR beep");
  // two short beeps, end OFF
  buzzOn();  delay(500);
  buzzOff(); // IMPORTANT: end off
}


// ================= LCD =================
void lcdMsg(const String &l1, const String &l2) {
  lcd.clear();
  lcd.setCursor(0,0); lcd.print(l1.substring(0,16));
  lcd.setCursor(0,1); lcd.print(l2.substring(0,16));
}

// ================= WIFI =================
void connectWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);
  lcdMsg("Connecting WiFi", WIFI_SSID);
  Serial.print("Connecting to WiFi");

  unsigned long start = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - start < 20000) {
    delay(500);
    Serial.print(".");
  }

  if (WiFi.status() == WL_CONNECTED) {
    lcdMsg("WiFi connected", WiFi.localIP().toString());
    Serial.println("\nWiFi connected! IP: " + WiFi.localIP().toString());
  } else {
    lcdMsg("WiFi failed", "Retrying…");
    Serial.println("\nWiFi connection failed.");
  }
}

// ================= RFID UID HEX =================
String uidToHex(MFRC522::Uid *uid) {
  String s = "";
  for (byte i=0; i<uid->size; i++) {
    if (uid->uidByte[i] < 0x10) s += "0";
    s += String(uid->uidByte[i], HEX);
  }
  s.toUpperCase();
  return s;
}

// ================= HTTP POST =================
bool postScan(const String& uid, String &disp1, String &disp2, String &beep) {
  if (WiFi.status() != WL_CONNECTED) {
    connectWiFi();
    if (WiFi.status() != WL_CONNECTED) return false;
  }

  WiFiClient client;     // HTTP (not TLS). Use WiFiClientSecure for https
  HTTPClient http;
  http.setTimeout(5000); // 5s timeout

  const String url = API_BASE + "/attendance/scan";

  // Build JSON payload matching Laravel controller: serial_no, device_token, uid
  StaticJsonDocument<384> doc;
  doc["serial_no"]    = SERIAL_NO;
  doc["device_token"] = DEVICE_TOKEN;
  doc["uid"]          = uid;

  String payload;
  serializeJson(doc, payload);

  Serial.println("[HTTP] POST " + url);
  Serial.println("[HTTP] Sending: " + payload);

  http.begin(client, url);
  http.addHeader("Content-Type", "application/json");
  const int code = http.POST(payload);

  if (code > 0) {
    String resp = http.getString();
    Serial.println("[HTTP] Response code: " + String(code));
    Serial.println("[HTTP] Body: " + resp);

    StaticJsonDocument<512> res;
    DeserializationError err = deserializeJson(res, resp);
    if (!err) {
      bool ok = res["ok"] | false;
      disp1 = res["display_line1"] | "";
      disp2 = res["display_line2"] | "";
      beep  = res["beep"] | (ok ? "ok" : "error");
      http.end();
      return ok;
    } else {
      Serial.println("[JSON] Parse error");
    }
  } else {
    Serial.println("[HTTP] Request failed: " + String(code));
  }

  http.end();
  return false;
}

// ================== SETUP ==================
void setup() {
  Serial.begin(115200);

  pinMode(BUZZER_PIN, OUTPUT);
  buzzOff();  // idle HIGH for active buzzer used here

  Wire.begin(D2, D1);   // SDA=D2, SCL=D1
  lcd.init();
  lcd.backlight();
  lcdMsg("RFID Attendance", "Booting…");

  SPI.begin();
  mfrc522.PCD_Init();
  mfrc522.PCD_SetAntennaGain(mfrc522.RxGain_max);
  mfrc522.PCD_AntennaOn();

  delay(500);
  connectWiFi();
  lcdMsg("Ready", "Scan your card");
}

// ================== LOOP ==================
void loop() {
  if (!mfrc522.PICC_IsNewCardPresent() || !mfrc522.PICC_ReadCardSerial()) {
    delay(50);
    return;
  }

  String uid = uidToHex(&mfrc522.uid);
  Serial.println("Card UID: " + uid);

  unsigned long nowMs = millis();
  if (uid == lastUID && (nowMs - lastScanMs) < SCAN_COOLDOWN_MS) {
    lcdMsg("Please wait…", "Duplicate scan");
    delay(800);
    return;
  }
  lastUID = uid; lastScanMs = nowMs;

  lcdMsg("Sending…", uid);

  String l1, l2, beep;
  bool ok = postScan(uid, l1, l2, beep);

  Serial.println("Server Response:");
  Serial.println("  LCD1: " + l1);
  Serial.println("  LCD2: " + l2);
  Serial.println("  BEEP: " + beep);

  lcdMsg(l1.length() ? l1 : (ok ? "Accepted" : "Rejected"),
         l2.length() ? l2 : (ok ? "Marked" : "Not allowed"));

  if (beep == "ok" || beep == "double") {
    beepOK();
  } else if (beep == "error") {
    beepErr();
  }

  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();
}
