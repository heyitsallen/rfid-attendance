<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FacultyController extends Controller
{
    public function attendance() {

            $active = 'attendance';
        return view('faculty.attendance', compact('active'));
    }

    public function schedule() {
              $active = 'schedule';
        return view('faculty.schedule', compact('active'));
    }

    public function personal() {

              $active = 'personal';
        return view('faculty.personal', compact('active'));
    }
}
