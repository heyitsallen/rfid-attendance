<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{SectionSchedule, Section, User, Room, FacultyAssignedSubject};

class SectionSchedulesTableSeeder extends Seeder
{
    public function run(): void
    {
        $section = Section::where('name','BSCS-1A')->first();
        $faculty = User::where('role','faculty')->first();
        $fas     = FacultyAssignedSubject::first();
        $room    = Room::where('room_number','101')->first();

        if ($section && $faculty && $fas) {
            SectionSchedule::updateOrCreate(
                [
                    'section_id'                  => $section->id,
                    'faculty_id'                  => $faculty->id,
                    'faculty_assigned_subject_id' => $fas->id,
                    'room_id'                     => $room?->id,
                    'day'                         => 'Mon',
                    'start_time'                  => '09:00:00',
                    'end_time'                    => '10:30:00',
                ],
                []
            );
        }
    }
}
