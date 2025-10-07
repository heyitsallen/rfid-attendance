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

         $active      = 'management';
    $students    = \App\Models\User::with(['cards.schoolYear'])
                    ->where('role','student')->orderBy('lastname')->get();
    $faculties   = \App\Models\User::with(['cards.schoolYear'])
                    ->where('role','faculty')->orderBy('lastname')->get();
    $schoolYears = \App\Models\SchoolYear::orderBy('date_start','desc')->get();
    $currentSY   = \App\Models\SchoolYear::orderBy('date_start','desc')->first();

    return view('admin.management', compact('active','students','faculties','schoolYears','currentSY'));
    }

    // app/Http/Controllers/AdminController.php

public function managementUsers()
{
    // prepare the same data you used before in /admin/management
    // e.g. $students, $faculties, $schoolYears, $currentSY
    $students    = \App\Models\User::where('role','student')->latest()->get();
    $faculties   = \App\Models\User::where('role','faculty')->latest()->get();
    $schoolYears = \App\Models\SchoolYear::orderByDesc('starts_on')->get();
    $currentSY   = $schoolYears->firstWhere('is_current', true) ?? $schoolYears->first();

    return view('admin.manageuser', compact('students','faculties','schoolYears','currentSY'));
}

public function managementCards()
{
    // simple listing; adjust filters as needed
    $cards = \App\Models\Card::with(['user','schoolYear'])->latest()->paginate(50);
    return view('admin.managecard', compact('cards'));
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
