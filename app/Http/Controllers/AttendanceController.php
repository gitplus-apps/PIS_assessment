<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Course;
use App\Models\CourseRegistration;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
{
    $courses = DB::table('tblsubject')->where('deleted', '0')->get();
    $semesters = DB::table('tblsemester')->where('deleted', '0')->get();
    $selectedCourse = $request->input('subcode');
    $selectedSemester = $request->input('sem_code');
    $dates = DB::table('tblattendance')
                ->select('attendance_date')
                ->where('subcode', $selectedCourse)
                ->where('semester', $selectedSemester)
                ->distinct()
                ->orderBy('attendance_date')
                ->pluck('attendance_date')
                ->toArray();

    $students = [];
    $attendanceData = collect();

    if ($selectedCourse && $selectedSemester) {
        $students = DB::table('tblgrade')
            ->join('tblstudent', 'tblgrade.grade_code', '=', 'tblstudent.student_no')
            ->where('tblsubject.subcode', $selectedCourse)
            ->where('tblgrade.semester', $selectedSemester)
            ->select('tblstudent.student_no', 'tblstudent.fname', 'tblstudent.lname')
            ->get();

        $attendanceData = DB::table('tblattendance')
            ->where('subcode', $selectedCourse)
            ->where('semester', $selectedSemester)
            ->get()
            ->groupBy('student_code')
            ->map(function ($records) {
                return $records->keyBy('attendance_date')->map(function ($record) {
                    return $record->status;
                });
            });
    }

    return view('modules.attendance.index', compact('courses', 'selectedCourse', 'students', 'dates', 'attendanceData', 'semesters', 'selectedSemester'));
}


    public function create()
    {
        $courses = DB::table('tblsubject')->where('deleted', '0')->get();
        $semesters = DB::table('tblsemester')->where('deleted', '0')->get();
        return view('modules.attendance.create', compact('courses', 'semesters'));
    }

    public function store(Request $request)
    {
    
        foreach ($request->students as $student_code => $status) {
            Attendance::updateOrCreate(
                [
                    'transid' => uniqid(),
                    'school_code' => Auth::user()->school_code,
                    'branch_code' => Auth::user()->branch_code,
                    "semester" => $request->semester,
                    'student_code' => $student_code, 
                    'attendance_date' => $request->attendance_date
                ],
                [
                    'subcode' => $request->subcode, 
                    'status' => $status
                ]
            );
        }

        return redirect()->route('attendance.index')->with('success', 'Attendance recorded successfully.');
    }

    public function getStudents(Request $request)
    {
        $students = Student::whereIn('student_no', function ($query) use ($request) {
            $query->select('student_code')->from('tblgrade')->where('subcode', $request->subcode);
        })->get();

        return response()->json($students);
    }
}
