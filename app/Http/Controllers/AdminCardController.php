<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\{Card, User, SchoolYear};
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class AdminCardController extends Controller
{
    // POST /admin/cards/check
    public function check(Request $request)
    {
        $data = $request->validate([
            'uid' => 'required|string|max:64',
        ]);

        // Normalize to uppercase HEX (strip spaces/colons)
        $uid = strtoupper(preg_replace('/[^0-9A-F]/i', '', $data['uid']));

        $card = Card::with('user')->where('uid', $uid)->first();

        if (!$card) {
            return response()->json(['registered' => false]);
        }

        return response()->json([
            'registered' => true,
            'card' => [
                'id' => $card->id,
                'uid' => $card->uid,
                'status' => $card->status ?? ($card->is_active ? 'active' : 'inactive'),
            ],
            'user' => $card->user ? [
                'id' => $card->user->id,
                'name' => trim(($card->user->firstname ?? '') . ' ' . ($card->user->lastname ?? '')),
                'role' => $card->user->role,
            ] : null,
        ]);
    }

    // POST /admin/cards/link (called by your fetch() in linkCard())
    public function link(Request $request)
    {
        $data = $request->validate([
            'uid' => ['required', 'string', 'max:64'],
            'user_id' => ['required', Rule::exists('users', 'id')],
            'school_year_id' => ['required', Rule::exists('school_years', 'id')],
        ]);

        // Normalize UID
        $uid = strtoupper(preg_replace('/[^0-9A-F]/i', '', $data['uid']));
        if ($uid === '') {
            return response()->json(['ok' => false, 'message' => 'Invalid UID'], 422);
        }

        $user = User::findOrFail($data['user_id']);
        $sy = SchoolYear::findOrFail($data['school_year_id']);
        $now = Carbon::now('Asia/Manila');

        // Block hijack: if UID exists on another user, stop
        $existing = Card::where('uid', $uid)->first();
        if ($existing && (int) $existing->user_id !== (int) $user->id) {
            return response()->json(['ok' => false, 'message' => 'UID already linked to another user.'], 422);
        }

        // Create or reuse the record
        $card = $existing ?: new Card();
        $card->uid = $uid;
        $card->user_id = $user->id;

        // Set school year if the column exists
        if (Schema::hasColumn('cards', 'school_year_id')) {
            $card->school_year_id = $sy->id;
        }

        // Mark active according to your schema
        if (Schema::hasColumn('cards', 'status')) {
            $card->status = 'active';
        }
        if (Schema::hasColumn('cards', 'is_active')) {
            $card->is_active = true;
        }

        // Only set issued_at the first time
        if (Schema::hasColumn('cards', 'issued_at') && empty($card->issued_at)) {
            $card->issued_at = $now;
        }

        if (Schema::hasColumn('cards', 'revoked_at')) {
            $card->revoked_at = null;
        }

        $card->save();

        // Return JSON (your JS reloads the page after this)
        return response()->json(['ok' => true, 'card_id' => $card->id]);
    }

    // PATCH /admin/cards/{card}
    public function update(Request $request, Card $card)
    {
        $data = $request->validate([
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'school_year_id' => ['nullable', 'exists:school_years,id'],
            'issued_at' => ['nullable', 'date'],
            'revoked_at' => ['nullable', 'date'],
        ]);

        $updates = [];

        if (array_key_exists('status', $data) && $data['status'] !== null) {
            $updates['status'] = $data['status'];

            if (Schema::hasColumn('cards', 'is_active')) {
                $updates['is_active'] = $data['status'] === 'active';
            }

            if (Schema::hasColumn('cards', 'revoked_at')) {
                // if setting inactive and no explicit revoked_at, stamp now
                if ($data['status'] === 'inactive' && empty($data['revoked_at'])) {
                    $updates['revoked_at'] = now();
                }

                // if setting active and no explicit revoked_at, clear it
                if ($data['status'] === 'active' && empty($data['revoked_at'])) {
                    $updates['revoked_at'] = null;
                }
            }
        }

        if (array_key_exists('school_year_id', $data)) {
            if (Schema::hasColumn('cards', 'school_year_id')) {
                $updates['school_year_id'] = $data['school_year_id'];
            }
        }

        if (!empty($data['issued_at']) && Schema::hasColumn('cards', 'issued_at')) {
            $updates['issued_at'] = \Illuminate\Support\Carbon::parse($data['issued_at']);
        }

        if (array_key_exists('revoked_at', $data) && Schema::hasColumn('cards', 'revoked_at')) {
            $updates['revoked_at'] = $data['revoked_at'] ? \Illuminate\Support\Carbon::parse($data['revoked_at']) : null;
        }

        $card->update($updates);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Card updated.');
    }
}
