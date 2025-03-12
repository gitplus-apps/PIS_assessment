<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Staff;
use App\Models\CourseAssignment;
use App\Models\StaffAttendance;
use Carbon\Carbon;

class StaffAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $courses = DB::table('tblsubject')->where('deleted', '0')->get();
        $selectedCourse = $request->input('subcode');
        $dates = DB::table('tblstaff_attendance')
                    ->select('attendance_date')
                    ->where('subcode', $selectedCourse)
                    ->distinct()
                    ->orderBy('attendance_date')
                    ->pluck('attendance_date')
                    ->toArray();

        $staffs = [];
        $attendanceData = collect();

        if ($selectedCourse) {
            $staffs = DB::table('tblsubject_assignment')
                ->join('tblstaff', 'tblsubject_assignment.staffno', '=', 'tblstaff.staffno')
                ->where('tblsubject_assignment.subcode', $selectedCourse)
                ->where('tblsubject_assignment.deleted', '0')
                ->select('tblstaff.staffno', 'tblstaff.fname', 'tblstaff.lname')
                ->get();

            $attendanceData = DB::table('tblstaff_attendance')
                ->where('subcode', $selectedCourse)
                ->get()
                ->groupBy('staffno')
                ->map(function ($records) {
                    return $records->keyBy('attendance_date')->map(function ($record) {
                        return $record->status;
                    });
                });
        }

        return view('modules.staffattendance.index', compact('courses', 'selectedCourse', 'staffs', 'dates', 'attendanceData'));
    }

    public function create()
    {
        $courses = DB::table('tblsubject')->where('deleted', '0')->get();
        return view('modules.staffattendance.create', compact('courses'));
    }

    public function store(Request $request)
    {

        foreach ($request->staffs as $staffno => $status) {
            StaffAttendance::updateOrCreate(
                [
                    'transid' => uniqid(),
                    'school_code' => Auth::user()->school_code,
                    'branch_code' => Auth::user()->branch_code,
                    'subcode' => $request->subcode,
                    'staffno' => $staffno,
                    'attendance_date' => $request->attendance_date
                ],
                [
                    'status' => $status,
                    'createuser' => Auth::user()->name
                ]
            );
        }

        return redirect()->route('staffattendance.index')->with('success', 'Attendance recorded successfully.');
    }

    public function getStaffs(Request $request)
    {
        $staffs = DB::table('tblsubject_assignment')
            ->join('tblstaff', 'tblsubject_assignment.staffno', '=', 'tblstaff.staffno')
            ->where('tblsubject_assignment.subcode', $request->subcode)
            ->where('tblsubject_assignment.deleted', '0')
            ->select('tblstaff.staffno', 'tblstaff.fname', 'tblstaff.lname')
            ->get();

        return response()->json($staffs);
    }
}
