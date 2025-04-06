<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NewAssessmentController extends Controller
{
    public function index(Request $request)
    {
        $classes = DB::table('tblclass')->where('deleted', '0')->get();
        $acyear = date('Y');
        $acyearr = DB::table('tblacyear')->where('current_term', '1')->select('acyear_desc', 'acterm');

        $staffNo = auth()->user()->userid;
        // $subjects = DB::table('tblsubject_assignment')
        //     ->where('staffno', $staffNo)
        //     ->where('deleted', '0')
        //     ->get();
        $subjects = DB::table('tblsubject_assignment')
            ->join('tblsubject', 'tblsubject_assignment.subcode', '=', 'tblsubject.subcode')
            ->where('tblsubject_assignment.staffno', $staffNo)
            ->where('tblsubject_assignment.deleted', '0')
            ->select('tblsubject.subname', 'tblsubject.subcode') // Select only subject names
            ->get();

        $students = DB::table('tblstudent')->where('deleted', '0')
            ->select('tblstudent.student_no', 'tblstudent.fname', 'tblstudent.mname', 'tblstudent.lname')
            ->get();

        $academicYears = DB::table('tblacyear')->where('deleted', '0')->get();

        $query = DB::table('tblassmain_ai as a')
            ->join('tblstudent as s', 'a.student_no', '=', 's.student_no')
            ->join('tblsubject_assignment as sa', function ($join) use ($staffNo, $acyear) {
                $join->on('a.subcode', '=', 'sa.subcode')
                    ->where('sa.staffno', $staffNo)
                    ->where('sa.acyear', $acyear);
            })
            ->select('a.*', 's.fname', 's.lname', 's.current_class')
            ->where('a.deleted', '0');

        if ($request->filled('class_code')) {
            $query->where('a.class_code', $request->class_code);
        }

        if ($request->filled('subcode')) {
            $query->where('a.subcode', $request->subcode);
        }

        if ($request->filled('term')) {
            $query->where('a.term', $request->term);
        }

        $assessments = $query->get();

        return view('modules.newassessment.index', compact('classes', 'subjects', 'academicYears', 'assessments', 'students', 'acyearr'));
    }

    public function getSubjectsByClass(Request $request)
{
    $class = DB::table('tblclass')
        ->where('class_code', $request->class_code)
        ->where('deleted', '0')
        ->first();

    if (!$class) {
        return response()->json(['subjects' => []]);
    }

    $gradeCode = $class->grade_code;
    $staffNo = auth()->user()->userid;

    $subjects = DB::table('tblsubject_assignment')
        ->join('tblsubject', 'tblsubject_assignment.subcode', '=', 'tblsubject.subcode')
        ->where('tblsubject_assignment.staffno', $staffNo)
        ->where('tblsubject_assignment.grade_code', $gradeCode)
        ->where('tblsubject_assignment.deleted', '0')
        ->select('tblsubject.subname', 'tblsubject.subcode')
        ->get();

    return response()->json(['subjects' => $subjects]);
}


public function filter(Request $request)
{
    $request->validate([
        'class_code' => 'required',
        'subcode' => 'required',
        'term' => 'required',
    ]);

    $classCode = $request->class_code;
    $subcode = $request->subcode;
    $term = $request->term;

    // Fetch students with their assessments if available
    $students = DB::table('tblstudent')
        ->leftJoin('tblassmain_ai', function ($join) use ($classCode, $subcode, $term) {
            $join->on('tblassmain_ai.student_no', '=', 'tblstudent.student_no')
                ->where('tblassmain_ai.class_code', $classCode)
                ->where('tblassmain_ai.subcode', $subcode)
                ->where('tblassmain_ai.term', $term)
                ->where('tblassmain_ai.deleted', '0');
        })
        ->where('tblstudent.current_class', $classCode)
        ->where('tblstudent.deleted', '0') // Exclude deleted students
        ->select(
            'tblstudent.student_no',
            'tblstudent.fname',
            'tblstudent.mname',
            'tblstudent.lname',
            'tblstudent.current_class',
            DB::raw('COALESCE(tblassmain_ai.transid, tblstudent.transid) as transid'),
            DB::raw('IFNULL(tblassmain_ai.class_code, "' . $classCode . '") as class_code'),
            DB::raw('IFNULL(tblassmain_ai.subcode, "' . $subcode . '") as subcode'),
            DB::raw('IFNULL(tblassmain_ai.term, "' . $term . '") as term'),
            DB::raw('IFNULL(tblassmain_ai.deleted, "0") as deleted'),
            DB::raw('COALESCE(tblassmain_ai.class_score, 0) as class_score'),
            DB::raw('COALESCE(tblassmain_ai.sat1, 0) as sat1'),
            DB::raw('COALESCE(tblassmain_ai.sat2, 0) as sat2'),
            DB::raw('COALESCE(tblassmain_ai.sat1_paper1, 0) as sat1_paper1'),
            DB::raw('COALESCE(tblassmain_ai.sat1_paper2, 0) as sat1_paper2'),
            DB::raw('COALESCE(tblassmain_ai.sat2_paper1, 0) as sat2_paper1'),
            DB::raw('COALESCE(tblassmain_ai.sat2_paper2, 0) as sat2_paper2'),
            DB::raw('COALESCE(tblassmain_ai.total_class_score, 0) as total_score'),
            DB::raw('COALESCE(tblassmain_ai.exam, 0) as exams'),
            DB::raw('COALESCE(tblassmain_ai.exam70, 0) as exams70'),
            DB::raw('COALESCE(tblassmain_ai.total_grade, 0) as total_grade'),
            DB::raw('COALESCE(tblassmain_ai.grade, "Ungraded") as grade'),
            DB::raw('COALESCE(tblassmain_ai.t_remarks, "No Remarks") as t_remarks')
        )
        ->orderBy("tblstudent.fname", "asc")
        ->get();

    return response()->json([
        'ok' => true,
        'students' => $students,
    ]);
}




public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        "class_code" => "required",
        "subcode" => "required",
        "term" => "required",
        "student_no" => "required",
    ]);

    if ($validator->fails()) {
        return response()->json([
            "ok" => false,
            "msg" => "Validation failed. Please complete all required fields.",
        ]);
    }

    $studentDetails = DB::table("tblstudent")
        ->where("deleted", "0")
        ->where("school_code", $request->school_code)
        ->where("student_no", $request->student_no)
        ->first();

    if (!$studentDetails) {
        return response()->json([
            "ok" => false,
            "msg" => "Student not found.",
        ]);
    }

    $sat1 = $request->sat1_paper1 + $request->sat1_paper2;
    $sat2 = $request->sat2_paper1 + $request->sat2_paper2;
    //$totalClassScore = (($sat1 + $request->sat2 + $request->class_score) / 220) * 30;

    $totalClassScore = (($request->sat1 + $request->sat2 + $request->class_score) / 220) * 30;
    $totalClassScore = round($totalClassScore);
    $exam70 = $request->exam*0.7;
    $totalGrade = $totalClassScore + $exam70;
    $totalGrade = round($totalGrade);

    // Get grade and remarks
    ["grade" => $grade, "remarks" => $remarks] = $this->calculateGrade($totalGrade);

    try {
        $existingAssessment = DB::table("tblassmain_ai")
            ->where("student_no", $request->student_no)
            ->where("class_code", $request->class_code)
            ->where("term", $request->term)
            ->where("subcode", $request->subcode)
            ->first();

        if ($existingAssessment) {
            // Update the existing assessment
            $user = auth()->user();
            DB::table("tblassmain_ai")
                ->where("transid", $existingAssessment->transid)
                ->update([
                    "class_score" => $request->class_score,
                    "sat1" => $request->sat1,
                    "sat1" => $sat1,
                    "sat2" => $sat2,
                    "sat1_paper1" => $request->sat1_paper1,
                    "sat1_paper2" => $request->sat1_paper2,
                    "sat2_paper1" => $request->sat2_paper1,
                    "sat2_paper2" => $request->sat2_paper2,
                    "total_class_score" => $totalClassScore,
                    "exam" => $request->exam,
                    "exam70" => $exam70,
                    "total_grade" => $totalGrade,
                    "grade" => $grade,
                    "t_remarks" => $remarks,
                    "deleted" => "0",
                    "modifyuser" => $user->userid,
                    "modifydate" => now(),
                ]);

            return response()->json([
                "ok" => true,
                "msg" => "Assessment updated successfully",
            ]);
        } else {
            // Create a new assessment
            $newTransId = strtoupper(bin2hex(random_bytes(5)));
            $user = auth()->user();
            DB::table("tblassmain_ai")->insert([
                "transid" => $newTransId,
                "school_code" => $request->school_code,
                "acyear" => $studentDetails->admyear,
                "term" => $request->term,
                "student_no" => $request->student_no,
                "subcode" => $request->subcode,
                "class_code" => $request->class_code,
                "sat1_paper1" => $request->sat1_paper1,
                "sat1_paper2" => $request->sat1_paper2,
                "sat1" => $sat1,
                "sat2_paper1" => $request->sat2_paper1,
                "sat2_paper2" => $request->sat2_paper2,
                "sat2" => $sat2,
                "total_class_score" => $totalClassScore,
                "exam" => $request->exam,
                "exam70" => $exam70,
                "total_grade" => $totalGrade,
                "grade" => $grade,
                "t_remarks" => $remarks,
                "deleted" => "0",
                "createuser" => $user->userid,
                "createdate" => now(),
            ]);

            return response()->json([
                "ok" => true,
                "msg" => "Assessment added successfully",
            ]);
    {
        $class = DB::table('tblclass')
            ->where('class_code', $request->class_code)
            ->where('deleted', '0')
            ->first();

        if (! $class) {
            return response()->json(['subjects' => []]);
        }

        $gradeCode = $class->grade_code;
        $staffNo = auth()->user()->userid;

        $subjects = DB::table('tblsubject_assignment')
            ->join('tblsubject', 'tblsubject_assignment.subcode', '=', 'tblsubject.subcode')
            ->where('tblsubject_assignment.staffno', $staffNo)
            ->where('tblsubject_assignment.grade_code', $gradeCode)
            ->where('tblsubject_assignment.deleted', '0')
            ->select('tblsubject.subname', 'tblsubject.subcode')
            ->get();

        return response()->json(['subjects' => $subjects]);
    }

    public function filter(Request $request)
    {
        $request->validate([
            'class_code' => 'required',
            'subcode' => 'required',
            'term' => 'required',
        ]);

        $classCode = $request->class_code;
        $subcode = $request->subcode;
        $term = $request->term;

        // Fetch students with their assessments if available
        $students = DB::table('tblstudent')
            ->leftJoin('tblassmain_ai', function ($join) use ($classCode, $subcode, $term) {
                $join->on('tblassmain_ai.student_no', '=', 'tblstudent.student_no')
                    ->where('tblassmain_ai.class_code', $classCode)
                    ->where('tblassmain_ai.subcode', $subcode)
                    ->where('tblassmain_ai.term', $term)
                    ->where('tblassmain_ai.deleted', '0');
            })
            ->where('tblstudent.current_class', $classCode)
            ->where('tblstudent.deleted', '0') // Exclude deleted students
            ->select(
                'tblstudent.student_no',
                'tblstudent.fname',
                'tblstudent.mname',
                'tblstudent.lname',
                'tblstudent.current_class',
                DB::raw('COALESCE(tblassmain_ai.transid, tblstudent.transid) as transid'),
                DB::raw('IFNULL(tblassmain_ai.class_code, "'.$classCode.'") as class_code'),
                DB::raw('IFNULL(tblassmain_ai.subcode, "'.$subcode.'") as subcode'),
                DB::raw('IFNULL(tblassmain_ai.term, "'.$term.'") as term'),
                DB::raw('IFNULL(tblassmain_ai.deleted, "0") as deleted'),
                DB::raw('COALESCE(tblassmain_ai.class_score, 0) as class_score'),
                DB::raw('COALESCE(tblassmain_ai.paper1, 0) as paper1'),
                DB::raw('COALESCE(tblassmain_ai.paper2, 0) as paper2'),
                DB::raw('COALESCE(tblassmain_ai.total_class_score, 0) as total_score'),
                DB::raw('COALESCE(tblassmain_ai.exam, 0) as exams'),
                DB::raw('COALESCE(tblassmain_ai.exam70, 0) as exams70'),
                DB::raw('COALESCE(tblassmain_ai.total_grade, 0) as total_grade'),
                DB::raw('COALESCE(tblassmain_ai.grade, "Ungraded") as grade'),
                DB::raw('COALESCE(tblassmain_ai.t_remarks, "No Remarks") as t_remarks')
            )
            ->orderBy('tblstudent.fname', 'asc')
            ->get();

        return response()->json([
            'ok' => true,
            'students' => $students,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_code' => 'required',
            'subcode' => 'required',
            'term' => 'required',
            'paper1' => 'required|numeric',
            'paper2' => 'required|numeric',
            'student_no' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'msg' => 'Validation failed. Please complete all required fields.',
            ]);
        }

        $studentDetails = DB::table('tblstudent')
            ->where('deleted', '0')
            ->where('school_code', $request->school_code)
            ->where('student_no', $request->student_no)
            ->first();

        if (! $studentDetails) {
            return response()->json([
                'ok' => false,
                'msg' => 'Student not found.',
            ]);
        }

        // Calculate total score
        // $totalScore = $request->paper1 + $request->paper2;

        $totalClassScore = round((($request->paper1 + $request->paper2 + $request->class_score) / 300) * 30);
        $exam70 = round($request->exam * 0.7);
        $totalGrade = $totalClassScore + $exam70;

        // Get grade and remarks
        ['grade' => $grade, 'remarks' => $remarks] = $this->calculateGrade($totalGrade);

        try {
            $existingAssessment = DB::table('tblassmain_ai')
                ->where('student_no', $request->student_no)
                ->where('class_code', $request->class_code)
                ->where('term', $request->term)
                ->where('subcode', $request->subcode)
                ->first();

            if ($existingAssessment) {
                // Update the existing assessment
                $user = auth()->user();
                DB::table('tblassmain_ai')
                    ->where('transid', $existingAssessment->transid)
                    ->update([
                        'class_score' => $request->class_score,
                        'paper1' => $request->paper1,
                        'paper2' => $request->paper2,
                        'total_class_score' => $totalClassScore,
                        'exam' => $request->exam,
                        'exam70' => $exam70,
                        'total_grade' => $totalGrade,
                        'grade' => $grade,
                        't_remarks' => $remarks,
                        'deleted' => '0',
                        'modifyuser' => $user->userid,
                        'modifydate' => now(),
                    ]);

                return response()->json([
                    'ok' => true,
                    'msg' => 'Assessment updated successfully',
                ]);
            } else {
                // Create a new assessment
                $newTransId = strtoupper(bin2hex(random_bytes(5)));
                $user = auth()->user();
                DB::table('tblassmain_ai')->insert([
                    'transid' => $newTransId,
                    'school_code' => $request->school_code,
                    'acyear' => $studentDetails->admyear,
                    'term' => $request->term,
                    'student_no' => $request->student_no,
                    'subcode' => $request->subcode,
                    'class_code' => $request->class_code,
                    'paper1' => $request->paper1,
                    'paper2' => $request->paper2,
                    'total_class_score' => $totalClassScore,
                    'exam' => $request->exam,
                    'exam70' => $exam70,
                    'total_grade' => $totalGrade,
                    'grade' => $grade,
                    't_remarks' => $remarks,
                    'deleted' => '0',
                    'createuser' => $user->userid,
                    'createdate' => now(),
                ]);

                return response()->json([
                    'ok' => true,
                    'msg' => 'Assessment added successfully',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'msg' => 'An internal error occurred. Please try again.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Calculate grade and corresponding remarks based on score
     */
    private function calculateGrade($score)
    {
        if ($score >= 90 && $score <= 100) {
            return ['grade' => 'A*', 'remarks' => 'Excellent'];
        } elseif ($score >= 80 && $score <= 89) {
            return ['grade' => 'A', 'remarks' => 'Excellent'];
        } elseif ($score >= 70 && $score <= 79) {
            return ['grade' => 'B', 'remarks' => 'Very Good'];
        } elseif ($score >= 60 && $score <= 69) {
            return ['grade' => 'C', 'remarks' => 'Good'];
        } elseif ($score >= 50 && $score <= 59) {
            return ['grade' => 'D', 'remarks' => 'Credit'];
        } elseif ($score >= 40 && $score <= 49) {
            return ['grade' => 'E', 'remarks' => 'Pass'];
        } elseif ($score >= 30 && $score <= 39) {
            return ['grade' => 'F', 'remarks' => 'Fail'];
        } else {
            return ['grade' => 'U', 'remarks' => 'Ungraded'];
        }
    }

public function getAssessment(Request $request, $id)
{
    // Get filter values from the request
    // $classCode = $request->input('class_code', '');
    // $subcode = $request->input('subcode', '');
    // $term = $request->input('term', '');
    $classCode = $request->class_code;
    $subcode = $request->subcode;
    $term = $request->term;
    public function deestroy(Request $request)
    {
        $assessment = DB::table('tblassmain_ai')->where('transid', $request->transid)->first();

        if (! $assessment) {
            return response()->json(['error' => 'Assessment not found'], 404);
        }

        $assessment->delete();

        return response()->json(['success' => 'Assessment deleted successfully']);
    }

    public function destroy(Request $request)
    {
        try {
            $deleted = DB::table('tblassmain_ai')
                ->where('transid', $request->transid)
                ->update(['deleted' => 1]);

            if (! $deleted) {
                return response()->json(['error' => 'Delete failed'], 400);
            }

            return response()->json(['ok' => true, 'message' => 'Assessment deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getAssessment(Request $request, $id)
    {
        // Get filter values from the request
        $classCode = $request->input('class_code', '');
        $subcode = $request->input('subcode', '');
        $term = $request->input('term', '');

        $assessment = DB::table('tblstudent')
            ->leftJoin('tblassmain_ai', function ($join) use ($subcode, $classCode, $term) {
                $join->on('tblstudent.student_no', '=', 'tblassmain_ai.student_no')
                    ->on('tblstudent.current_class', '=', 'tblassmain_ai.class_code')
                    ->where(function ($query) use ($subcode, $classCode, $term) {
                        $query->where('tblassmain_ai.subcode', $subcode)
                            ->where('tblassmain_ai.class_code', $classCode)
                            ->where('tblassmain_ai.term', $term)
                            ->orWhereNull('tblassmain_ai.subcode');
                    });
            })
            ->leftJoin('tblclass', 'tblstudent.current_class', '=', 'tblclass.class_code')
            ->select(
                'tblstudent.student_no',
                'tblstudent.transid',
                'tblstudent.school_code',
                DB::raw("CONCAT(tblstudent.fname, ' ', COALESCE(tblstudent.mname, ''), ' ', tblstudent.lname) AS student_name"),
                DB::raw("COALESCE(tblclass.class_desc, '') AS course_name"),
                DB::raw("COALESCE(tblassmain_ai.transid, '') AS assessment_transid"),
                DB::raw('IFNULL(tblassmain_ai.class_code, "'.$classCode.'") AS class_code'),
                DB::raw('"'.$subcode.'" AS subcode'),
                DB::raw('IFNULL(tblassmain_ai.term, "'.$term.'") AS term'),
                DB::raw("COALESCE(tblassmain_ai.deleted, '0') AS deleted"),
                DB::raw('COALESCE(tblassmain_ai.transid, tblstudent.transid) as transid'),
                DB::raw("
                CASE
                    WHEN tblassmain_ai.student_no IS NULL OR tblassmain_ai.subcode != '$subcode'
                         OR tblassmain_ai.class_code != '$classCode' OR tblassmain_ai.term != '$term'
                    THEN '0'
                    ELSE COALESCE(tblassmain_ai.total_class_score, '0')
                END AS total_score
            "),
                DB::raw("
                CASE
                    WHEN tblassmain_ai.student_no IS NULL OR tblassmain_ai.subcode != '$subcode'
                         OR tblassmain_ai.class_code != '$classCode' OR tblassmain_ai.term != '$term'
                    THEN '0'
                    ELSE COALESCE(tblassmain_ai.class_score, '0')
                END AS class_score
            "),
                DB::raw("
                CASE
                    WHEN tblassmain_ai.student_no IS NULL OR tblassmain_ai.subcode != '$subcode'
                         OR tblassmain_ai.class_code != '$classCode' OR tblassmain_ai.term != '$term'
                    THEN '0'
                    ELSE COALESCE(tblassmain_ai.sat1, '0')
                END AS sat1
            "),
                DB::raw("
                CASE
                    WHEN tblassmain_ai.student_no IS NULL OR tblassmain_ai.subcode != '$subcode'
                         OR tblassmain_ai.class_code != '$classCode' OR tblassmain_ai.term != '$term'
                    THEN '0'
                    ELSE COALESCE(tblassmain_ai.sat1_paper1, '0')
                END AS sat1_paper1
            "),
            DB::raw("
            CASE
                WHEN tblassmain_ai.student_no IS NULL OR tblassmain_ai.subcode != '$subcode' 
                     OR tblassmain_ai.class_code != '$classCode' OR tblassmain_ai.term != '$term' 
                THEN '0'
                ELSE COALESCE(tblassmain_ai.sat1_paper2, '0')
            END AS sat1_paper2
           "),
           DB::raw("
                CASE
                    WHEN tblassmain_ai.student_no IS NULL OR tblassmain_ai.subcode != '$subcode' 
                         OR tblassmain_ai.class_code != '$classCode' OR tblassmain_ai.term != '$term' 
                    THEN '0'
                    ELSE COALESCE(tblassmain_ai.sat2_paper1, '0')
                END AS sat2_paper1
            "),
            DB::raw("
            CASE
                WHEN tblassmain_ai.student_no IS NULL OR tblassmain_ai.subcode != '$subcode' 
                     OR tblassmain_ai.class_code != '$classCode' OR tblassmain_ai.term != '$term' 
                THEN '0'
                ELSE COALESCE(tblassmain_ai.sat2_paper2, '0')
            END AS sat2_paper2
           "),
            DB::raw("
                CASE
                    WHEN tblassmain_ai.student_no IS NULL OR tblassmain_ai.subcode != '$subcode' 
                         OR tblassmain_ai.class_code != '$classCode' OR tblassmain_ai.term != '$term' 
                    THEN '0'
                    ELSE COALESCE(tblassmain_ai.sat2, '0')
                END AS sat2
            "),
                DB::raw("
                CASE
                    WHEN tblassmain_ai.student_no IS NULL OR tblassmain_ai.subcode != '$subcode'
                         OR tblassmain_ai.class_code != '$classCode' OR tblassmain_ai.term != '$term'
                    THEN '0'
                    ELSE COALESCE(tblassmain_ai.total_class_score, '0')
                END AS total_score
            "),
                DB::raw("
                CASE
                    WHEN tblassmain_ai.student_no IS NULL OR tblassmain_ai.subcode != '$subcode'
                         OR tblassmain_ai.class_code != '$classCode' OR tblassmain_ai.term != '$term'
                    THEN '0'
                    ELSE COALESCE(tblassmain_ai.exam, '0')
                END AS exams
            ")
            )
            ->where(function ($query) use ($id) {
                $query->where('tblstudent.transid', $id)
                    ->orWhere('tblassmain_ai.transid', $id);
            })
            ->first();

        if (! $assessment) {
            return response()->json(['error' => 'Assessment not found', 'query_id' => $id], 404);
        }

        return response()->json($assessment);
    }

    return response()->json($assessment);
}



public function fetchAssessments(Request $request)
{
    $class_code = $request->input('class_code');
    $term = $request->input('term');
    $student_no = $request->input('student_no');

    $assessments = DB::table('tblassmain_ai')
        ->join('tblsubject', 'tblassmain_ai.subcode', '=', 'tblsubject.subcode')
        ->join('tblstudent', 'tblassmain_ai.student_no', '=', 'tblstudent.student_no')
        ->leftJoin('tblclass', 'tblclass.class_code', '=', DB::raw("'$class_code'"))
        ->leftJoin('tblcomment_ia', 'tblassmain_ai.student_no', '=', 'tblcomment_ia.student_no') 
        ->where('tblassmain_ai.class_code', $class_code)
        ->where('tblassmain_ai.term', $term)
        ->where('tblassmain_ai.student_no', $student_no)
        ->where('tblassmain_ai.deleted', '0')
        ->select(
            'tblsubject.subname',
            'tblclass.class_desc as class_name',
            DB::raw("TRIM(CONCAT(COALESCE(tblstudent.fname, ''), ' ', COALESCE(tblstudent.mname, ''), ' ', COALESCE(tblstudent.lname, ''))) AS student_name"),
            DB::raw('COALESCE(tblassmain_ai.class_score, 0) as class_score'),
            DB::raw('COALESCE(tblassmain_ai.sat1, 0) as sat1'),
            DB::raw('COALESCE(tblassmain_ai.sat2, 0) as sat2'),
            DB::raw('COALESCE(tblassmain_ai.sat1_paper1, 0) as sat1_paper1'),
            DB::raw('COALESCE(tblassmain_ai.sat1_paper2, 0) as sat1_paper2'),
            DB::raw('COALESCE(tblassmain_ai.sat2_paper1, 0) as sat2_paper1'),
            DB::raw('COALESCE(tblassmain_ai.sat2_paper2, 0) as sat2_paper2'),
            DB::raw('COALESCE(tblassmain_ai.total_class_score, 0) as total_score'),
            DB::raw('COALESCE(tblassmain_ai.exam, 0) as exams'),
            DB::raw('COALESCE(tblassmain_ai.exam70, 0) as exams70'),
            DB::raw('COALESCE(tblassmain_ai.total_grade, 0) as total_grade'),
            DB::raw('COALESCE(tblassmain_ai.grade, "N/A") as grade'),
            DB::raw('COALESCE(tblassmain_ai.t_remarks, "No Remarks") as t_remarks'),
            DB::raw('COALESCE(tblcomment_ia.ct_remarks, "No Comment") as ct_remarks')
        )
        ->distinct()
        ->get();

    return response()->json($assessments);
}

    public function fetchAssessments(Request $request)
    {
        $class_code = $request->input('class_code');
        $term = $request->input('term');
        $student_no = $request->input('student_no');

        $assessments = DB::table('tblassmain_ai')
            ->join('tblsubject', 'tblassmain_ai.subcode', '=', 'tblsubject.subcode')
            ->join('tblstudent', 'tblassmain_ai.student_no', '=', 'tblstudent.student_no')
            ->leftJoin('tblclass', 'tblclass.class_code', '=', DB::raw("'$class_code'"))
            ->leftJoin('tblcomment_ia', 'tblassmain_ai.student_no', '=', 'tblcomment_ia.student_no')
            ->where('tblassmain_ai.class_code', $class_code)
            ->where('tblassmain_ai.term', $term)
            ->where('tblassmain_ai.student_no', $student_no)
            ->where('tblassmain_ai.deleted', '0')
            ->select(
                'tblsubject.subname',
                'tblclass.class_desc as class_name',
                DB::raw("TRIM(CONCAT(COALESCE(tblstudent.fname, ''), ' ', COALESCE(tblstudent.mname, ''), ' ', COALESCE(tblstudent.lname, ''))) AS student_name"),
                DB::raw('COALESCE(tblassmain_ai.class_score, 0) as class_score'),
                DB::raw('COALESCE(tblassmain_ai.paper1, 0) as paper1'),
                DB::raw('COALESCE(tblassmain_ai.paper2, 0) as paper2'),
                DB::raw('COALESCE(tblassmain_ai.total_class_score, 0) as total_score'),
                DB::raw('COALESCE(tblassmain_ai.exam, 0) as exams'),
                DB::raw('COALESCE(tblassmain_ai.exam70, 0) as exams70'),
                DB::raw('COALESCE(tblassmain_ai.total_grade, 0) as total_grade'),
                DB::raw('COALESCE(tblassmain_ai.grade, "N/A") as grade'),
                DB::raw('COALESCE(tblassmain_ai.t_remarks, "No Remarks") as t_remarks'),
                DB::raw('COALESCE(tblcomment_ia.ct_remarks, "No Comment") as ct_remarks')
            )
            ->distinct()
            ->get();

        return response()->json($assessments);
    }
}
