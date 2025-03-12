<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class Routecontroller extends Controller
{
    public function __construct()

    {
        $this->middleware(['auth']);
    }

    public function dashboard(Request $request)
    {

        if (Auth::user()->usertype === 'ADM') {
            Cookie::queue(Cookie::make("schoolname", Auth::user()->school->school_name, "1440", "/smartuniversity/login"));
            Cookie::queue(Cookie::make("schoollogo", Auth::user()->school->logo, "1440", "/smartuniversity/login"));

            $courses = DB::table("tblsubject")->where('school_code', Auth::user()->school->school_code)->where("deleted", 0)->count();
            $students = DB::table("tblstudent")->where('school_code', Auth::user()->school->school_code)->where("deleted", 0)->count();
            $staff = DB::table("tblstaff")->where('school_code', Auth::user()->school->school_code)->where("deleted", 0)->count();
            $depart = DB::table("tbldepart")->where('school_code', Auth::user()->school->school_code)->where("deleted", 0)->count();
            $prog = DB::table("tblprog")->where('school_code', Auth::user()->school->school_code)->where("deleted", 0)->count();
            $user = DB::table('tbluser')->where('school_code', Auth::user()->school->school_code)->where('deleted', '0')->count();
            // $username= DB::table('tbluser')->select('tbladmin.fname')
            //     ->join('tbladmin', 'tbluser.email', 'tbladmin.email', Auth::user()->email)
            //     ->where('tbluser.school_code', Auth::user()->school->school_code)->where('tbluser.deleted', '0')
            //     ->where('tbladmin.deleted', '0')
            //     ->where('tbladmin.school_code', Auth::user()->school->school_code)
            //     ->get();
            return view('main_dashboard', [
                "courses" => $courses,
                "students" => $students,
                "staff" => $staff,
                "depart" => $depart,
                "prog" => $prog,
                "user" => $user,
                // "username"=>$username
            ]);
        }

        if (Auth::user()->usertype === 'STA') {
            $schoolCode = Auth::user()->school->school_code;
        $staffNo = Auth::user()->userid;
    
        $staff = DB::table("tblstaff")->where('school_code', $schoolCode)->where("deleted", 0)->count();
        $depart = DB::table("tbldepart")->where('school_code', $schoolCode)->where("deleted", 0)->count();
        $prog = DB::table("tblprog")->where('school_code', $schoolCode)->where("deleted", 0)->count();
        $courses = DB::table('tblsubject')
            ->join('tblsubject_assignment', "tblsubject_assignment.subcode", 'tblsubject.subcode')
            ->where('tblsubject.deleted', '0')
            ->where('tblsubject_assignment.deleted', '0')
            ->where('tblsubject.school_code', $schoolCode)
            ->where('tblsubject_assignment.staffno', $staffNo)
            ->count();
    
        $newCourses = DB::table('tblsubject')->select('tblsubject.subcode')
            ->join('tblsubject_assignment', "tblsubject_assignment.subcode", 'tblsubject.subcode')
            ->where('tblsubject.deleted', '0')
            ->where('tblsubject_assignment.deleted', '0')
            ->where('tblsubject_assignment.staffno', $staffNo)
            ->pluck('subcode')->toArray();
    
        $students = DB::table("tblgrade")
            ->join("tblstudent", "tblstudent.current_grade", "tblgrade.grade_code")
            ->join("tblsubject", "tblsubject.subcode", "tblsubject.subcode")
            ->whereIn("tblsubject.subcode", $newCourses)
            ->where("tblstudent.deleted", 0)
            ->where("tblsubject.deleted", 0)
            ->where("tblgrade.deleted", 0)
            ->count();

            //$coursesList = DB::table("tblsubject")->select('subcode', 'subname')->get();
            $coursesList = DB::table('tblsubject')
            ->join('tblsubject_assignment', "tblsubject_assignment.subcode", 'tblsubject.subcode')
            ->where('tblsubject.deleted', '0')
            ->where('tblsubject_assignment.deleted', '0')
            ->where('tblsubject.school_code', $schoolCode)
            ->where('tblsubject_assignment.staffno', $staffNo)
            ->select('tblsubject.subcode', 'tblsubject.subname')
            ->get();
    
            $studentsD = DB::table("tblgrade")
            ->join("tblstudent", "tblstudent.student_no", "=", "tblgrade.grade_code")
            ->join("tblsubject", "tblsubject.subcode", "=", "tblsubject.subcode")
            ->leftJoin("tblassmain", function ($join) {
                $join->on("tblassmain.student_no", "=", "tblstudent.student_no")
                     ->on("tblassmain.subcode", "=", "tblsubject.subcode")
                     ->where("tblassmain.deleted", "0");
            })
            ->select(
                "tblstudent.student_no",
                "tblstudent.fname",
                "tblstudent.lname",
                "tblsubject.subcode",
                "tblsubject.subname",
                DB::raw("COALESCE(tblassmain.total_score, 0) as total_score")
            )
            ->whereIn("tblsubject.subcode", $newCourses)
            ->where("tblstudent.deleted", 0)
            ->where("tblsubject.deleted", 0)
            ->where("tblgrade.deleted", 0)
            ->paginate(10);
        
    
        return view('staff.staff_dashboard', [
            "courses" => $courses,
            "coursesList" => $coursesList,
            "students" => $students,
            "staff" => $staff,
            "depart" => $depart,
            "prog" => $prog,
            "studentsD" => $studentsD
        ]);
        }

        if (Auth::user()->usertype === 'STU') {
            $staff = DB::table("tblstudent")->where('school_code', Auth::user()->school->school_code)->where("deleted", 0)->count();
            $depart = DB::table("tbldepart")->where('school_code', Auth::user()->school->school_code)->where("deleted", 0)->count();
            $prog = DB::table("tblprog")
            ->join("tblstudent", function ($join) {
                $join->on("tblprog.prog_code", "=", "tblstudent.prog")
                ->where("tblstudent.student_no", "=", Auth::user()->userid);
                })
            ->where("tblprog.school_code", Auth::user()->school->school_code)
            ->select('tblprog.prog_desc')
            ->where("tblprog.deleted", 0)
            ->get();
            $courses = DB::table('tblsubject')
                ->join('tblgrade', "tblsubject.subcode", 'tblsubject.subcode')
                ->where('tblsubject.deleted', '0')
                ->where('tblgrade.deleted', '0')
                ->where('tblsubject.school_code', Auth::user()->school->school_code)
                ->where('tblgrade.grade_code', Auth::user()->userid)
                ->count();

                $coursesD = DB::table('tblsubject')
                ->join('tblgrade', "tblsubject.subcode", 'tblsubject.subcode')
                ->where('tblsubject.deleted', '0')
                ->where('tblgrade.deleted', '0')
                ->where('tblsubject.school_code', Auth::user()->school->school_code)
                ->where('tblgrade.grade_code', Auth::user()->userid)
                ->select('tblsubject.credit', 'tblsubject.subcode', 'tblsubject.subname')
                ->get();


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

            $students = DB::table("tblgrade")
                ->join("tblstudent", "tblstudent.current_grade", "tblgrade.grade_code")
                ->join("tblsubject", "tblsubject.subcode", "tblsubject.subcode")
                ->whereIn("tblsubject.subcode", $courseCode)
                ->where("tblstudent.deleted", 0)
                ->where("tblsubject.deleted", 0)
                ->where("tblgrade.deleted", 0)
                ->count();




                $user = auth()->user();
                $school_code = $user->school_code;
                
                $totalGradePoints = 0;
                $totalCreditUnits = 0;
                $gpa = 0;

            $grades = DB::table('tblassmain')
            ->join('tblsubject', 'tblassmain.subcode', '=', 'tblsubject.subcode')
            ->where('tblassmain.school_code', $school_code)
            ->where('tblassmain.deleted', '0')
            ->where('tblassmain.student_no', $user->userid)
            ->select(
                'tblassmain.*', 
                'tblsubject.subname as subname',
                'tblsubject.credit' // Fetching credit unit
            )
            ->distinct()
            ->get();

        // Calculate grades & grade points
        $grades = $grades->map(function ($grade) use (&$totalGradePoints, &$totalCreditUnits) {
            $grade->letter_grade = $this->calculateGrade($grade->total_score);
            $grade->credit_grade = $this->gradePoint($grade->total_score, $grade->credit);

            // Accumulate grade points and credit units for GPA calculation
            $totalGradePoints += $grade->credit_grade;
            $totalCreditUnits += $grade->credit;

            return $grade;
        });

        
        if ($totalCreditUnits > 0) {
            $gpa = number_format($totalGradePoints / $totalCreditUnits, 2, '.', '');
        } else {
            $gpa = number_format(0, 2, '.', ''); // Ensure GPA is always in decimal format
        }

        $performance = DB::table('tblassmain') 
    ->join('tblsubject', 'tblassmain.subcode', '=', 'tblsubject.subcode')
    ->where('tblassmain.school_code', $school_code)
    ->where('tblassmain.deleted', '0')
    ->where('tblassmain.student_no', $user->userid)
    ->select(
        'tblsubject.subname',
        DB::raw('AVG(tblassmain.total_score) as avg_score')
    )
    ->groupBy('tblsubject.subname')
    ->get();

// Convert data to JSON for Chart.js
$courseTitles = $performance->pluck('subname')->toArray();
$averageScores = $performance->pluck('avg_score')->toArray();

            return view('student.student_dashboard', [
                "courses" => $courses,
                "coursesD" => $coursesD,
                "students" => $students,
                "staff" => $staff,
                "depart" => $depart,
                "prog" => $prog,
                "gpa"=> $gpa,
                "courseTitles" => json_encode($courseTitles),
                "averageScores" => json_encode($averageScores),
            ]);
        }

    }

    private function calculateGrade($num)
    {
        if ($num >= 80) {
            return "A";
        } elseif ($num >= 75 && $num <= 79) {
            return "B+";
        } elseif ($num >= 70 && $num <= 74) {
            return "B";
        } elseif ($num >= 65 && $num <= 69) {
            return "C+";
        } elseif ($num >= 60 && $num <= 64) {
            return "C";
        } elseif ($num >= 55 && $num <= 59) {
            return "D+";
        } elseif ($num >= 50 && $num <= 54) {
            return "D";
        } elseif ($num >= 0 && $num <= 49) {
            return "E";
        } else {
            return "Invalid Grade";
        }
    }

    public function gradePoint($num, $credit)
    {
        if ($num >= 80) {
            return 4.00 * $credit;
        } elseif ($num >= 75 && $num <= 79) {
            return 3.50 * $credit;
        } elseif ($num >= 70 && $num <= 74) {
            return 3.00 * $credit;
        } elseif ($num >= 65 && $num <= 69) {
            return 2.50 * $credit;
        } elseif ($num >= 60 && $num <= 64) {
            return 2.00 * $credit;
        } elseif ($num >= 55 && $num <= 59) {
            return 1.50 * $credit;
        } elseif ($num >= 50 && $num <= 54) {
            return 1.00 * $credit;
        } elseif ($num >= 0 && $num <= 49) {
            return 0.00 * $credit;
        }
    }

    //reurning the student module view
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
        $student = DB::table('tblstudent')->where('school_code', Auth::user()->school->school_code)->where('deleted', '0')
            ->get();
        $course = DB::table('tblsubject')->where('school_code', Auth::user()->school->school_code)->where('deleted', '0')
            ->get();
        $semester = DB::table("tblsemester")->where("deleted", 0)->get();

        return view("modules.students.index", [
            "prog" => $prog,
            "sess" => $sess,
            "batch" => $batch,
            "level" => $level,
            "branch" => $branch,
            "semester" => $semester,
            "student" => $student,
            "course" => $course,
        ]);
    }

    //returning the staff module view
    public function staff()
    {
        $rel = DB::table("tblrelation_type")->where("deleted", 0)->get();
        $qual = DB::table("tblqual")->where("deleted", 0)->get();
        $staff = DB::table("tblstaff")->where('school_code', Auth::user()->school->school_code)->where("deleted", 0)->get();
        $emp = DB::table("tblemp_type")->where("deleted", 0)->get();
        $dept = DB::table("tbldepart")->where('school_code', Auth::user()->school->school_code)->where("deleted", 0)->get();
        $acc = DB::table("tblstaff_account_type")->where("deleted", 0)->get();
        $bank = DB::table("tblbank")->where("deleted", 0)->get();
        return view('modules.staff.index', [
            "rel" => $rel,
            "qual" => $qual,
            "staff" => $staff,
            "emp" => $emp,
            "dept" => $dept,
            "acc" => $acc,
            "bank" => $bank,
        ]);
    }
    //returning the grades module view
    public function applications()
    {
        $total = DB::table("tblapplications")->where("deleted", 0)
            ->where("school_code", Auth::user()->school->school_code)->count();
        $m = DB::table("tblapplications")->where("deleted", 0)->where("gender", "M")
            ->where("school_code", Auth::user()->school->school_code)->count();
        $f = DB::table("tblapplications")->where("deleted", 0)->where("gender", "F")
            ->where("school_code", Auth::user()->school->school_code)->count();
        return view('modules.applications.index', [
            "total" => $total,
            "m" => $m,
            "f" => $f,
        ]);
    }

    //returning the library module view
    public function  library()
    {
        return view('modules.studentlibrary.index');
    }
    //returning the bills module view
    public function bill()
    {
        //Fetching data from tables
        $acTrem = DB::table("tblacyear")->where("deleted", "0")->get();
        $student = DB::table("tblstudent")->where("school_code", Auth::user()->school->school_code)->where("deleted", "0")->get();
        $billItem = DB::table("tblbill_item")->where("school_code", Auth::user()->school->school_code)->where("deleted", "0")->get();

        //fetcting  branches
        $branches = DB::table('tblbranch')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', '0')
            ->get();
        //fetchinng batches 
        $batches = DB::table('tblbatch')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', '0')
            ->get();
        //fetching  departments
        $departments = DB::table('tbldepart')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', '0')
            ->get();
        //fethcing sessions
        $sessions = DB::table('tblsession')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', '0')
            ->get();
        //selecting  programs 
        $programs = DB::table('tblprog')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', '0')
            ->get();
        //fetching semesters
        $semester = DB::table('tblsemester')
            ->where('deleted', '0')
            ->get();
        //Fetching levels
        $level = DB::table('tbllevel')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', '0')
            ->get();

        //fetching bill items
        $billItem = DB::table('tblbill_item')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', '0')
            ->get();
        return view('modules.bill.index', [
            "acterm" => $acTrem,
            "studentList" => $student,
            "billitem" => $billItem,
            "departments" => $departments,
            "batches" => $batches,
            "sessions" => $sessions,
            "branches" => $branches,
            "programs" => $programs,
            "semester" => $semester,
            "level" => $level,
            "billItem" => $billItem
        ]);
    }
    //returning the transcripts module view
    public function transcript()
    {
        return view('modules.transcripts.index');
    }
    //retruning department module
    public function department()
    {
        $femaleAcademics = DB::table("tblstaff")->where('school_code', Auth::user()->school->school_code)->where("staff_type", "AC")->where("deleted", 0)->where("gender", "F")->count();
        $maleAcademics = DB::table("tblstaff")->where('school_code', Auth::user()->school->school_code)->where("staff_type", "AC")->where("deleted", 0)->where("gender", "M")->count();
        $femaleNonAcademics = DB::table("tblstaff")->where('school_code', Auth::user()->school->school_code)->where("staff_type", "NAC")->where("deleted", 0)->where("gender", "F")->count();
        $maleNonAcademics = DB::table("tblstaff")->where('school_code', Auth::user()->school->school_code)->where("staff_type", "NAC")->where("deleted", 0)->where("gender", "M")->count();
        $totalAcadmics = DB::table("tblstaff")->where('school_code', Auth::user()->school->school_code)->where("staff_type", "AC")->where("deleted", 0)->count();
        $totalNonAcadmics = DB::table("tblstaff")->where('school_code', Auth::user()->school->school_code)->where("staff_type", "NAC")->where("deleted", 0)->count();
        return view('modules.department.index', [
            "femaleAcademics" => $femaleAcademics,
            "maleAcademics" => $maleAcademics,
            "femaleNonAcademics" => $femaleNonAcademics,
            "maleNonAcademics" => $maleNonAcademics,
            "totalNonAcadmics" => $totalNonAcadmics,
            "totalAcadmics" => $totalAcadmics,
        ]);
    }

    //returning admin course module
    public function admincourse()
    {
        //fetching  departments
        $departments = DB::table('tbldepart')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', '0')
            ->get();
        //selecting  programs 
        $programs = DB::table('tblprog')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', '0')
            ->get();

        $staff = DB::table('tblstaff')
            ->select(
                'tblstaff.*',
                //'tbltitle.title_desc'
            )->get();
            // ->where('school_code', Auth::user()->school->school_code)
            // ->leftJoin('tbltitle', 'tbltitle.title_code', 'tblstaff.title')
            // ->where('tblstaff.deleted', '0')
            // ->where('tbltitle.deleted', '0')
            // ->get();

        //retrieving all students per school
        $students = DB::table('tblstudent')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', '0')
            ->get();

        $courses = DB::table('tblsubject')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', '0')
            ->get();
        //fetcting  branches
        $branches = DB::table('tblbranch')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', '0')
            ->get();
        //fetchinng batches 
        $batches = DB::table('tblbatch')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', '0')
            ->get();
        //fethcing sessions
        $sessions = DB::table('tblsession')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', '0')
            ->get();
        //fetching semester
        $semester = DB::table('tblsemester')
            ->get();
        // //academic year
        $level = DB::table('tbllevel')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', '0')
            ->get();
        return view('modules.admincourses.index', [
            'programs' => $programs,
            'staff' => $staff,
            "schoolCode" => Auth::user()->school->school_code,
            "courses" => $courses,
            "branches" => $branches,
            "batches" => $batches,
            "student" => $students,
            "departments" => $departments,
            "sessions" => $sessions,
            "semester" => $semester,
            "level" => $level

        ]);
    }

    //returning programs

    public function program()
    {
        $type = DB::table("tblprog_type")->where("deleted", 0)->get();
        $duration = DB::table("tblprog_duration")->where("deleted", 0)->get();
        return view('modules.program.index', [
            "type" => $type,
            "duration" => $duration,
        ]);
    }

    //returning payment
    public function payment()
    {
        //retrieving all students per school
        $students = DB::table('tblstudent')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', '0')
            ->get();
        $billItem = DB::table('tblbill_item')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', 0)
            ->get();
        $branches = DB::table('tblbranch')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', '0')
            ->get();
        $session = DB::table('tblsession')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', '0')
            ->get();
        $prog = DB::table('tblprog')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', '0')
            ->get();
        $batch = DB::table('tblbatch')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', '0')
            ->get();

        $semester = DB::table("tblsemester")->where("deleted", 0)->get();
        return view('modules.payment.index', [
            "students" => $students,
            "billItems" => $billItem,
            "semester" => $semester,
            "branches" => $branches,
            "prog" => $prog,
            "session" => $session,
            "batch" => $batch,
        ]);
    }

    public function expenditure()
    {

        $batches = DB::table("tblbatch")
            ->select("batch_code as code", "batch_desc as label")
            ->where("school_code", Auth::user()->school_code)
            ->where("deleted", 0)
            ->orderBy("createdate", 'desc')
            ->get();
        $branches = DB::table('tblbranch')
            ->select("branch_code as code", "branch_desc as label")
            ->where('school_code', Auth::user()->school->school_code)
            ->orderBy("branch_desc")
            ->where('deleted', '0')
            ->get();
        $semester = DB::table("tblsemester")
            ->select('sem_code as code', 'sem_desc as label')
            ->orderBy("createdate")
            ->where("deleted", 0)->get();
        $exp = DB::table("tblexp_cat")
            ->select('code', 'name as label')
            ->where("school_code", Auth::user()->school_code)
            ->orderBy("name")
            ->where("deleted", 0)->get();
        return view("modules.expenditure.index", [
            "batches" => $batches,
            "categories" => $exp,
            "semesters" => $semester,
            "branches" => $branches,
        ]);
    }

    public function req()
    {
        $branches = DB::table('tblbranch')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', '0')
            ->get();
        $item = DB::table("tblitem")->where("school_code", Auth::user()->school->school_code)
            ->where("deleted", 0)->get();
        $semester = DB::table("tblsemester")->where("deleted", 0)->get();
        return view("modules.req.index", [
            "items" => $item,
            "semester" => $semester,
            "branches" => $branches,
        ]);
    }

    


    public function messaging()
    {
        $staff = DB::table("tblstaff")->where('school_code', Auth::user()->school->school_code)->where("deleted", 0)->get();
        $student = DB::table("tblstudent")->where('school_code', Auth::user()->school->school_code)->where("deleted", 0)->get();
        return view('modules.message.index', [
            "staff" => $staff,
            "student" => $student,
        ]);
    }
    //returning usermanagement module
    public function manageUser()
    {
        $userType = DB::table('tbluser')->where('school_code', Auth::user()->school->school_code)->where('deleted', '0')->get();

        //fetcting  branches
        $branches = DB::table('tblbranch')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', '0')
            ->get();
        //fetching  departments
        $departments = DB::table('tbldepart')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', '0')
            ->get();
        //fetcting  programs
        $programs = DB::table('tblprog')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', '0')
            ->get();
        $courses = DB::table('tblsubject')
            ->where('school_code', Auth::user()->school->school_code)
            ->where('deleted', '0')
            ->get();
        return view('modules.usermanagement.index', [
            "userType" => $userType,
            "departments" => $departments,
            "branches" => $branches,
            "programs" => $programs,
            "courses" => $courses
        ]);
    }

    public function dwTranscript($code)
    {
        $student = DB::table('tblstudent')
            ->where("student_no", $code)
            ->where('deleted', 0)
            ->first();
        if (empty($student)) {
            abort(404);
        }

        $prog = DB::table("tblprog")
            ->where("prog_code", $student->prog)
            ->first();
        $school = DB::table('tblschool')
            ->where("school_code", $student->school_code)
            ->first();
        $courses = [];
        $grade = [];
        $gpa = [];
        $semesters = DB::table("tblsemester")
            ->select("sem_code", "sem_desc")
            ->where("deleted", 0)
            ->orderBy("sem_code")
            ->get();
        foreach ($semesters as $semester) {
            $courses[$semester->sem_code] =  DB::table("tblsubject")
                ->where("semester", $semester->sem_code)
                ->where("prog", $student->prog)
                ->get();
            $addGP = 0;
            $totalCredit = 0;

            //checking if there is a course match in assessment table
            foreach ($courses[$semester->sem_code] as $course) {
                $assessment = DB::table("tblassmain")
                    ->join("tblsubject", "tblassmain.subcode", "tblsubject.subcode")
                    ->where("tblassmain.student_no", $code)
                    ->where("tblassmain.branch_code", $student->branch_code)
                    ->where("tblassmain.acyear", $student->batch)
                    ->where("tblassmain.subcode", $course->subcode)
                    ->first();
                //credit
                $gp = "-";
                $grades = "-";
                if (!empty($assessment)) {

                    $gp = $this->gradePoint($assessment->total_score, $course->credit);
                    $grades = $this->grade($assessment->total_score);
                }
                if ($gp != "-" && $grade != "-") {
                    $addGP += $gp;
                    $totalCredit += $course->credit;
                }
                $grade[$course->subcode] = [
                    "subcode" => $course->subcode,
                    "title" => $course->subname,
                    "credit" => $course->credit,
                    "grade" => $grades,
                    "gp" => $gp,
                    'gpa' => null,
                ];
            }
            if ($totalCredit == 0) $totalCredit = 1;
            $gpa[$semester->sem_code]  = $addGP / $totalCredit;
        }





        return view("modules.students.transcript", compact("student", "courses", "semesters", 'grade', 'gpa', 'prog', 'school'));
    }


    // public function gradePoint($num, $credit)
    // {
    //     if ($num >= 80) {

    //         return 4.00 * $credit;
    //     } elseif ($num >= 75 && $num <= 79) {
    //         return 3.50 * $credit;
    //     } elseif ($num >= 70 && $num <= 74) {
    //         return 3.00 * $credit;
    //     } elseif ($num >= 65 && $num <= 69) {
    //         return 2.50 * $credit;
    //     } elseif ($num >= 60 && $num <= 64) {
    //         return 2.00 * $credit;
    //     } elseif ($num >= 55 && $num <= 59) {
    //         return 1.50 * $credit;
    //     } elseif ($num >= 50 && $num <= 54) {
    //         return 1.00 * $credit;
    //     } elseif ($num >= 0 && $num <= 49) {
    //         return 0.00 * $credit;
    //     }
    // }

    // public function grade($num){
    //     if ($num >= 80) {

    //         return "A";
    //     } elseif ($num >= 75 && $num <= 79) {
    //         return "B+";
    //     } elseif ($num >= 70 && $num <= 74) {
    //         return "B";
    //     } elseif ($num >= 65 && $num <= 69) {
    //         return "C+";
    //     } elseif ($num >= 60 && $num <= 64) {
    //         return "C";
    //     } elseif ($num >= 55 && $num <= 59) {
    //         return "D+";
    //     } elseif ($num >= 50 && $num <= 54) {
    //         return "D";
    //     } elseif ($num >= 0 && $num <= 49) {
    //         return "E";
    //     }
    // }

    public function graded($num)
    {
        if ($num >= 80) {
            return "A";
        } elseif ($num >= 75 && $num <= 79) {
            return "B+";
        } elseif ($num >= 70 && $num <= 74) {
            return "B";
        } elseif ($num >= 65 && $num <= 69) {
            return "C+";
        } elseif ($num >= 60 && $num <= 64) {
            return "C";
        } elseif ($num >= 55 && $num <= 59) {
            return "D+";
        } elseif ($num >= 50 && $num <= 54) {
            return "D";
        } elseif ($num >= 0 && $num <= 49) {
            return "E";
        } else {
            return "Invalid Grade";
        }
    }

    public function grade()
    {
        return view('modules.grades.index');
    }

    public function batch()
    {
        return view('modules.batch.index');
    }

    public function services(){
        $stuServices = DB::table('tblservice_student')
        ->select('tblservice_student.*', 'tblstudent.fname', 'tblstudent.mname', 
        'tblstudent.lname', 'tblservices.service_name')
        ->join('tblstudent', 'tblservice_student.student_no', 'tblstudent.student_no')
        ->join('tblservices', 'tblservice_student.service_code', 'tblservices.service_code')
        ->where('tblservice_student.deleted', 0)
        ->get();

        $services = DB::table('tblservices')
        ->select('tblservices.*')
        ->where('deleted', 0)
        ->get();
        return view('modules.adminservices.index', ['services' => $services, 'stuServices' => $stuServices]);
    }

    public function inventory(){
        $items = DB::table('tblinventory_item')
        ->select('item_code','item_desc')
        ->get();
        return view('modules.inventory.index', ['items' => $items]);
    }

    public function supplier(){
        return view('modules.supplier.index');
    }
}
