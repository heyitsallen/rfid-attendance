<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SectionSchedule extends Model
{
    protected $fillable = [
        'section_id',
        'faculty_id',
        'faculty_assigned_subject_id',
        'room_id',          // ← add this
        'day',
        'start_time',
        'end_time',
    ];

    // Relationships
    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function faculty(): BelongsTo { return $this->belongsTo(User::class, 'faculty_id'); }
    public function assigned(): BelongsTo { return $this->belongsTo(FacultyAssignedSubject::class, 'faculty_assigned_subject_id'); }
    public function room(): BelongsTo { return $this->belongsTo(Room::class); } // ← add this

    // Optional helpers/scopes (nice for controllers)
    public static function dayFromCarbon(Carbon $now): string
    {
        $map = [1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun'];
        return $map[$now->isoWeekday()];
    }

    public function scopeHappeningNow($q, Carbon $now, ?int $roomId = null)
    {
        $dayEnum = self::dayFromCarbon($now);
        $timeNow = $now->format('H:i:s');

        $q->where('day', $dayEnum)
          ->whereTime('start_time', '<=', $timeNow)
          ->whereTime('end_time', '>=', $timeNow);

        if ($roomId) $q->where('room_id', $roomId);

        return $q;
    }
}
