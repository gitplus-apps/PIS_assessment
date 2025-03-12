<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class StaffRouteController extends Controller
{

    public function student()
    {
        $prog = DB::table('tblprog')->where('school_code', Auth::user()->school->school_code)->where('deleted', '0')
            ->get();
        $sess = DB::table('tblsession')->where('school_code', Auth::user()->school->school_code)->where('deleted', '0')
            ->get();
        $batch = DB::table('tblbatch')->where('school_code', Auth::user()->school->school_code)->where('deleted', '0')
            ->get();
        $level = DB::table('tbllevel')->where('school_code', Auth::user()->school->school_code)->where('deleted', '0')
            ->get();
        $branch = DB::table('tblbranch')->where('school_code', Auth::user()->school->school_code)->where('deleted', '0')
            ->get();
        $courses = DB::table('tblsubject')
            ->join('tblsubject_assignment', "tblsubject_assignment.subcode", 'tblsubject.subcode')
            // ->where('tblstaff.staffno',Auth::user()->userid)
            ->where('tblsubject.deleted', '0')
            ->where('tblsubject_assignment.deleted', '0')
            ->where('tblsubject_assignment.staffno', Auth::user()->userid)
            // ->where('tblstaff.deleted', '0')
            ->get();
        return view("staff.student.index", [
            "prog" => $prog,
            "sess" => $sess,
            "batch" => $batch,
            "level" => $level,
            "branch" => $branch,
            "courses" => $courses,
            "staff_no" => Auth::user()->userid
        ]);
    }

    public function messaging()
    {
        $staff = DB::table("tblstaff")->where('school_code', Auth::user()->school->school_code)->where("deleted", 0)->get();
        // $student = DB::table("tblstudent")->where('school_code', Auth::user()->school->school_code)->where("deleted", 0)->get();

        $newCourses = DB::table('tblsubject')->select('tblsubject.subcode')
            ->join('tblsubject_assignment', "tblsubject_assignment.subcode", 'tblsubject.subcode')
            ->where('tblsubject.deleted', '0')
            ->where('tblsubject_assignment.deleted', '0')
            ->where('tblsubject_assignment.staffno', Auth::user()->userid)
            ->get()->toArray();

        $courseCode = [];
        foreach ($newCourses as  $value) {
            $courseCode[] = $value->subcode;
        }

        $students = DB::table("tblgrade")->select(
            "tblstudent.*",
        )
            ->join("tblstudent", "tblstudent.current_grade", "tblgrade.grade_code")
            ->join("tblsubject", "tblsubject.subcode", "tblsubject.subcode")
            ->whereIn("tblsubject.subcode", $courseCode)
            ->where("tblstudent.deleted", 0)
            ->where("tblsubject.deleted", 0)
            ->where("tblgrade.deleted", 0)
            ->get();

        

        return view('staff.message.index', [
            "staff" => $staff,
            "students" => $students,

        ]);
    }

    public function dashboard()
    {
        Cookie::queue(Cookie::make("schoolname", Auth::user()->school->school_name, "1440", "/smartuniversity/login"));
        Cookie::queue(Cookie::make("schoollogo", Auth::user()->school->logo, "1440", "/smartuniversity/login"));

        $staff = DB::table("tblstaff")->where('school_code', Auth::user()->school->school_code)->where("deleted", 0)->count();
        $depart = DB::table("tbldepart")->where('school_code', Auth::user()->school->school_code)->where("deleted", 0)->count();
        $prog = DB::table("tblprog")->where('school_code', Auth::user()->school->school_code)->where("deleted", 0)->count();
        $courses = DB::table('tblsubject')
            ->join('tblsubject_assignment', "tblsubject_assignment.subcode", 'tblsubject.subcode')
            ->where('tblsubject.deleted', '0')
            ->where('tblsubject_assignment.deleted', '0')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('tblsubject_assignment.staffno', Auth::user()->userid)
            ->count();


        $newCourses = DB::table('tblsubject')->select('tblsubject.subcode')
            ->join('tblsubject_assignment', "tblsubject_assignment.subcode", 'tblsubject.subcode')
            ->where('tblsubject.deleted', '0')
            ->where('tblsubject_assignment.deleted', '0')
            ->where('tblsubject_assignment.staffno', Auth::user()->userid)
            ->get()->toArray();

        $courseCode = [];
        foreach ($newCourses as  $value) {
            $courseCode[] = $value->subcode;
        }

        $students = DB::table("tblgrade")->select(
            "tblstudent.email",
            "tblstudent.fname",
        )
            ->join("tblstudent", "tblstudent.current_grade", "tblgrade.grade_code")
            ->join("tblsubject", "tblsubject.subcode", "tblsubject.subcode")
            ->whereIn("tblsubject.subcode", $courseCode)
            ->where("tblstudent.deleted", 0)
            ->where("tblsubject.deleted", 0)
            ->where("tblgrade.deleted", 0)
            ->get();

        return view('staff.staff_dashboard', [
            "courses" => $courses,
            "students" => $students,
            "staff" => $staff,
            "depart" => $depart,
            "prog" => $prog,
        ]);
    }

    public function notice()
    {
        $code = Auth::user()->school_code;
        $student = Student::where('school_code', '=', $code)
            ->where('deleted', '0')->get();
        $acyear = DB::table("tblacyear")->where("school_code", Auth::user()->school_code)->where("deleted", "0")->get();
        // $grade = Grade::where("school_code", Auth::user()->school_code)->where("deleted", "0")->get();
        $notice = DB::table("tblnotice_type")->where("school_code", Auth::user()->school_code)->where("deleted", "0")->get();
        $count = DB::table("tbllibrary_book")->where("school_code", Auth::user()->school_code)->where("deleted", "0")->count();
        $recipient = DB::table("tblnotice_recipient")->where("school_code", Auth::user()->school_code)->where("deleted", "0")->get();
        $courses = DB::table('tblsubject')
            ->join('tblsubject_assignment', "tblsubject_assignment.subcode", 'tblsubject.subcode')
            // ->where('tblstaff.staffno',Auth::user()->userid)
            ->where('tblsubject.deleted', '0')
            ->where('tblsubject_assignment.deleted', '0')
            ->where('tblsubject_assignment.staffno', Auth::user()->userid)
            // ->where('tblstaff.deleted', '0')
            ->get();
        return view("staff.notice.index", [
            "student" => $student,
            "notice" => $notice,
            "count" => $count,
            "courses" => $courses,
            "recipient" => $recipient,
        ]);
    }
}
