<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\{
    User, Device, Section, SectionSchedule, YearLevel,
    FacultyAssignedSubject, SubjectCurriculum, Room, SchoolYear
};

class AdminController extends Controller
{
    // --- DASHBOARD ---
    public function dashboard()
    {

    $active = 'dashboard';
    return view('admin.dashboard', compact('active'));
    }

    // --- MANAGEMENT VIEWS ---
    public function management()
    {

    $active = 'management';
    return view('admin.management', compact('active'));
    }

    // --- ATTENDANCE VIEW ---
    public function attendance()
    {

    $active = 'attendance';
    return view('admin.attendance', compact('active'));
    }

    // --- REPORTS VIEW ---
    public function reports()
    {
    
    $active = 'reports';
    return view('admin.reports', compact('active'));
    }
}
