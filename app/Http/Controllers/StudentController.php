<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function attendance() {

          $active = 'attendance';
        return view('student.attendance', compact('active'));
    }

        public function schedule() {

         $active = 'attendance';
        return view('student.schedule', compact('active'));
    }
}
