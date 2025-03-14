<?php

namespace App\Http\Controllers;

use App\Http\Resources\AssessmentResource;
use App\Http\Resources\TranscriptResource;
use App\Models\Student;
use Exception;
use DataTables;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\TblClass;
use Illuminate\Support\Str;

class CommentController extends Controller{



    public function index(Request $request)
    {
        $classes = DB::table('tblclass')->where('deleted', '0')->get();
        $acyear = date("Y");
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

        $query = DB::table("tblassmain_ai as a")
        ->join("tblstudent as s", "a.student_no", "=", "s.student_no")
        ->join("tblsubject_assignment as sa", function ($join) use ($staffNo, $acyear) {
            $join->on("a.subcode", "=", "sa.subcode")
                ->where("sa.staffno", $staffNo)
                ->where("sa.acyear", $acyear);
        })
        ->select("a.*", "s.fname", "s.lname", "s.current_class")
        ->where("a.deleted", "0");

    if ($request->filled("class_code")) {
        $query->where("a.class_code", $request->class_code);
    }

    if ($request->filled("subcode")) {
        $query->where("a.subcode", $request->subcode);
    }

    if ($request->filled("term")) {
        $query->where("a.term", $request->term);
    }

    $assessments = $query->get();

        return view('modules.comment.index', compact('classes', 'subjects', 'academicYears', 'assessments', 'students', 'acyearr'));
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

    // Fetch students from tblstudent with a LEFT JOIN on tblassmain_ai
    $students = DB::table('tblstudent')
        ->leftJoin('tblassmain_ai', function ($join) {
            $join->on('tblassmain_ai.student_no', '=', 'tblstudent.student_no');
        })
        ->where('tblstudent.current_class', $classCode)
        ->where('tblstudent.deleted', '0') // Exclude deleted students
        ->where(function ($query) use ($classCode, $subcode, $term) {
            $query->where('tblassmain_ai.class_code', $classCode)
                  ->where('tblassmain_ai.subcode', $subcode)
                  ->where('tblassmain_ai.term', $term)
                  ->where('tblassmain_ai.deleted', '0')
                  ->orWhereNull('tblassmain_ai.student_no'); // Ensure all students are included
        })
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
            DB::raw('COALESCE(tblassmain_ai.paper1, "0") as paper1'),
            DB::raw('COALESCE(tblassmain_ai.paper2, "0") as paper2'),
            DB::raw('COALESCE(tblassmain_ai.total_score, "0") as total_score'),
            DB::raw('COALESCE(tblassmain_ai.grade, "Ungraded") as grade')
        )
        ->orderBy("tblstudent.fname", "asc")
        ->get();

    return response()->json([
        'ok' => true,
        'students' => $students,
    ]);
}



// public function store(Request $request)
// {
//     $transid = Str::uuid()->toString(); // Generate a unique transaction ID
//     $school_code = auth()->user()->school_code; // Replace with the actual school code
//     $acyear = date('Y'); // Current academic year
//     $createuser = auth()->user()->userid; // Logged-in user

//     DB::table('tblcomment_ia')->insert([
//         'transid' => $transid,
//         'school_code' => $school_code,
//         'acyear' => $acyear,
//         'term' => $request->term,
//         'student_no' => $request->student_no,
//         'class_code' => $request->class_code,
//         'ct_remarks' => $request->comment,
//         'source' => 'M', // M = Manual entry
//         'import' => '0',
//         'export' => '0',
//         'deleted' => '0',
//         'createuser' => $createuser,
//         'createdate' => now(),
//     ]);

//     return response()->json(['success' => true, 'message' => 'Comment saved successfully.']);
// }

public function store(Request $request)
{
    $transid = Str::uuid()->toString(); // Generate a unique transaction ID
    $school_code = auth()->user()->school_code; // Get school code from authenticated user
    $acyear = date('Y'); // Current academic year
    $createuser = auth()->user()->userid; // Logged-in user

    // Check if a comment already exists for the same student, term, and class
    $exists = DB::table('tblcomment_ia')
        ->where('school_code', $school_code)
        ->where('acyear', $acyear)
        ->where('term', $request->term)
        ->where('student_no', $request->student_no)
        ->where('class_code', $request->class_code)
        ->where('deleted', '0') // Ensure it’s not a deleted record
        ->exists();

    if ($exists) {
        return response()->json(['success' => false, 'message' => 'Comment already exists for this student.']);
    }

    // Insert new comment
    DB::table('tblcomment_ia')->insert([
        'transid' => $transid,
        'school_code' => $school_code,
        'acyear' => $acyear,
        'term' => $request->term,
        'student_no' => $request->student_no,
        'class_code' => $request->class_code,
        'ct_remarks' => $request->comment,
        'source' => 'M', // M = Manual entry
        'import' => '0',
        'export' => '0',
        'deleted' => '0',
        'createuser' => $createuser,
        'createdate' => now(),
    ]);

    return response()->json(['success' => true, 'message' => 'Comment saved successfully.']);
}



public function deestroy(Request $request)
{
    $assessment = DB::table("tblassmain_ai")->where('transid', $request->transid)->first();

    if (!$assessment) {
        return response()->json(['error' => 'Assessment not found'], 404);
    }

    $assessment->delete();
    return response()->json(['success' => 'Assessment deleted successfully']);
}


public function destroy(Request $request) {
    try {
        $deleted = DB::table("tblcomment_ia")
            ->where("transid", $request->transid)
            ->update(["deleted" => 1]);

        if (!$deleted) {
            return response()->json(["error" => "Delete failed"], 400);
        }

        return response()->json(["ok" => true, "message" => "Comment deleted successfully"]);
    } catch (\Exception $e) {
        return response()->json(["error" => $e->getMessage()], 500);
    }
}


public function getComment(Request $request, $id)
{
    // Get filter values from the request
    $classCode = $request->input('class_code', '');
    $subcode = $request->input('subcode', '');
    $term = $request->input('term', '');

    $assessment = DB::table("tblstudent")
        ->leftJoin("tblassmain_ai", function ($join) use ($subcode) {
            $join->on("tblstudent.student_no", "=", "tblassmain_ai.student_no")
                 ->on("tblstudent.admterm", "=", "tblassmain_ai.term")
                 ->on("tblstudent.current_class", "=", "tblassmain_ai.class_code")
                 ->on(DB::raw("COALESCE(tblassmain_ai.subcode, '')"), "=", DB::raw("'$subcode'"));
        })
        ->leftJoin("tblclass", "tblstudent.current_class", "=", "tblclass.class_code")
        ->select(
            "tblstudent.student_no",
            "tblstudent.transid AS student_transid",
            "tblstudent.school_code",
            DB::raw("CONCAT(tblstudent.fname, ' ', COALESCE(tblstudent.mname, ''), ' ', tblstudent.lname) AS student_name"),
            DB::raw("COALESCE(tblclass.class_desc, '') AS course_name"),
            DB::raw("COALESCE(tblassmain_ai.transid, '') AS assessment_transid"),
            DB::raw('IFNULL(tblassmain_ai.class_code, "' . $classCode . '") AS class_code'),
            DB::raw('"' . $subcode . '" AS subcode'),
            DB::raw('IFNULL(tblassmain_ai.term, "' . $term . '") AS term'),
            DB::raw("COALESCE(tblassmain_ai.deleted, '0') AS deleted"),
            DB::raw('COALESCE(tblassmain_ai.transid, tblstudent.transid) as transid'),

            // Ensure paper1 and paper2 are 0 if there is no exact match in tblassmain_ai
            DB::raw("
                CASE
                    WHEN tblassmain_ai.student_no IS NULL THEN '0'
                    WHEN tblassmain_ai.subcode IS NULL OR tblassmain_ai.subcode = '' THEN '0'
                    ELSE COALESCE(tblassmain_ai.paper1, '0')
                END AS paper1
            "),
            DB::raw("
                CASE
                    WHEN tblassmain_ai.student_no IS NULL THEN '0'
                    WHEN tblassmain_ai.subcode IS NULL OR tblassmain_ai.subcode = '' THEN '0'
                    ELSE COALESCE(tblassmain_ai.paper2, '0')
                END AS paper2
            "),
            DB::raw("
                CASE
                    WHEN tblassmain_ai.student_no IS NULL THEN '0'
                    WHEN tblassmain_ai.subcode IS NULL OR tblassmain_ai.subcode = '' THEN '0'
                    ELSE COALESCE(tblassmain_ai.total_score, '0')
                END AS total_score
            ")
        )
        ->where(function ($query) use ($id) {
            $query->where("tblstudent.transid", $id)
                  ->orWhere("tblassmain_ai.transid", $id);
            })
        ->first();

    if (!$assessment) {
        return response()->json(['error' => 'Assessment not found', 'query_id' => $id], 404);
    }

    return response()->json($assessment);
}



// public function fetchComment(Request $request)
// {
//     $class_code = $request->input('class_code');
//     $term = $request->input('term');
//     $student_no = $request->input('student_no');

//     $assessments = DB::table('tblassmain_ai')
//         ->join('tblsubject', 'tblassmain_ai.subcode', '=', 'tblsubject.subcode') // Only subjects with assessments
//         ->join('tblstudent', 'tblassmain_ai.student_no', '=', 'tblstudent.student_no')
//         ->leftJoin('tblclass', 'tblclass.class_code', '=', DB::raw("'$class_code'"))
//         ->where('tblassmain_ai.class_code', $class_code)
//         ->where('tblassmain_ai.term', $term)
//         ->where('tblassmain_ai.student_no', $student_no)
//         ->where('tblassmain_ai.deleted', '0')
//         ->select(
//             'tblsubject.subname',
//             'tblclass.class_desc as class_name',
//             DB::raw("TRIM(CONCAT(COALESCE(tblstudent.fname, ''), ' ', COALESCE(tblstudent.mname, ''), ' ', COALESCE(tblstudent.lname, ''))) AS student_name"),
//             DB::raw('COALESCE(tblassmain_ai.paper1, 0) as paper1'),
//             DB::raw('COALESCE(tblassmain_ai.paper2, 0) as paper2'),
//             DB::raw('COALESCE(tblassmain_ai.total_score, 0) as total_score'),
//             DB::raw('COALESCE(tblassmain_ai.grade, "N/A") as grade'),
//             DB::raw('COALESCE(tblassmain_ai.t_remarks, "No Remarks") as t_remarks')
//         )
//         ->distinct()
//         ->get();

//     return response()->json($assessments);
// }


public function fetchComment(Request $request)
{
    $class_code = $request->input('class_code');
    $term = $request->input('term');
    $student_no = $request->input('student_no');

    $assessments = DB::table('tblassmain_ai')
        ->join('tblsubject', 'tblassmain_ai.subcode', '=', 'tblsubject.subcode') // Only subjects with assessments
        ->join('tblstudent', 'tblassmain_ai.student_no', '=', 'tblstudent.student_no')
        ->leftJoin('tblclass', 'tblclass.class_code', '=', DB::raw("'$class_code'"))
        ->leftJoin('tblcomment_ia', 'tblassmain_ai.student_no', '=', 'tblcomment_ia.student_no')
        ->where('tblassmain_ai.class_code', $class_code)
        ->where('tblassmain_ai.term', $term)
        ->where('tblassmain_ai.student_no', $student_no)
        ->where('tblassmain_ai.deleted', '0')
        ->select(
            'tblcomment_ia.transid',
            'tblsubject.subname',
            'tblclass.class_desc as class_name',
            'tblstudent.student_no',
            DB::raw("TRIM(CONCAT(COALESCE(tblstudent.fname, ''), ' ', COALESCE(tblstudent.mname, ''), ' ', COALESCE(tblstudent.lname, ''))) AS student_name"),
            DB::raw('COALESCE(tblassmain_ai.paper1, 0) as paper1'),
            DB::raw('COALESCE(tblassmain_ai.paper2, 0) as paper2'),
            DB::raw('COALESCE(tblassmain_ai.total_score, 0) as total_score'),
            DB::raw('COALESCE(tblassmain_ai.grade, "N/A") as grade'),
            DB::raw('COALESCE(tblassmain_ai.t_remarks, "No Remarks") as t_remarks')
        )
        ->distinct()
        ->get();

    // Fetch student details
    $student = DB::table('tblstudent')
        ->where('student_no', $student_no)
        ->select(
            'student_no',
            DB::raw("TRIM(CONCAT(COALESCE(fname, ''), ' ', COALESCE(mname, ''), ' ', COALESCE(lname, ''))) AS student_name")
        )
        ->first();

        $student = DB::table('tblstudent')
        ->where('student_no', $student_no)
        ->select(
            'student_no',
            DB::raw("TRIM(CONCAT(COALESCE(fname, ''), ' ', COALESCE(mname, ''), ' ', COALESCE(lname, ''))) AS student_name")
        )
        ->first();

    return response()->json([
        'assessments' => $assessments,
        'student' => $student
    ]);
}



}
