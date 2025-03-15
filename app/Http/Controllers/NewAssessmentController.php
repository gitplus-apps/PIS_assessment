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

class NewAssessmentController extends Controller{



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

        return view('modules.newassessment.index', compact('classes', 'subjects', 'academicYears', 'assessments', 'students', 'acyearr'));
    }


//     public function filter(Request $request)
// {
//     $request->validate([
//         'class_code' => 'required',
//         'subcode' => 'required',
//         'term' => 'required',
//     ]);

//     $classCode = $request->class_code;
//     $subcode = $request->subcode;
//     $term = $request->term;

//     // Fetch students from tblstudent with a LEFT JOIN on tblassmain_ai
//     $students = DB::table('tblstudent')
//         ->leftJoin('tblassmain_ai', function ($join) {
//             $join->on('tblassmain_ai.student_no', '=', 'tblstudent.student_no');
//         })
//         ->where('tblstudent.current_class', $classCode)
//         ->where('tblstudent.deleted', '0') // Exclude deleted students
//         ->where(function ($query) use ($classCode, $subcode, $term) {
//             $query->where('tblassmain_ai.class_code', $classCode)
//                   ->where('tblassmain_ai.subcode', $subcode)
//                   ->where('tblassmain_ai.term', $term)
//                   ->where('tblassmain_ai.deleted', '0')
//                   ->orWhereNull('tblassmain_ai.student_no'); // Ensure all students are included
//         })
//         ->select(
//             'tblstudent.student_no',
//             'tblstudent.fname',
//             'tblstudent.mname',
//             'tblstudent.lname',
//             'tblstudent.current_class',
//             DB::raw('COALESCE(tblassmain_ai.transid, tblstudent.transid) as transid'),
//             DB::raw('IFNULL(tblassmain_ai.class_code, "' . $classCode . '") as class_code'),
//             DB::raw('IFNULL(tblassmain_ai.subcode, "' . $subcode . '") as subcode'),
//             DB::raw('IFNULL(tblassmain_ai.term, "' . $term . '") as term'),
//             DB::raw('IFNULL(tblassmain_ai.deleted, "0") as deleted'),
//             DB::raw('COALESCE(tblassmain_ai.paper1, "0") as paper1'),
//             DB::raw('COALESCE(tblassmain_ai.paper2, "0") as paper2'),
//             DB::raw('COALESCE(tblassmain_ai.total_score, "0") as total_score'),
//             DB::raw('COALESCE(tblassmain_ai.grade, "Ungraded") as grade')
//         )
//         ->orderBy("tblstudent.fname", "asc")
//         ->get();

//     return response()->json([
//         'ok' => true,
//         'students' => $students,
//     ]);
// }

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
            DB::raw('COALESCE(tblassmain_ai.paper1, 0) as paper1'),
            DB::raw('COALESCE(tblassmain_ai.paper2, 0) as paper2'),
            DB::raw('COALESCE(tblassmain_ai.total_score, 0) as total_score'),
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
        "paper1" => "required|numeric",
        "paper2" => "required|numeric",
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

    // Calculate total score
    $totalScore = $request->paper1 + $request->paper2;

    // Get grade and remarks
    ["grade" => $grade, "remarks" => $remarks] = $this->calculateGrade($totalScore);

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
                    "paper1" => $request->paper1,
                    "paper2" => $request->paper2,
                    "total_score" => $totalScore,
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
                "paper1" => $request->paper1,
                "paper2" => $request->paper2,
                "total_score" => $totalScore,
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
        }
    } catch (\Exception $e) {
        return response()->json([
            "ok" => false,
            "msg" => "An internal error occurred. Please try again.",
            "error" => $e->getMessage(),
        ]);
    }
}

/**
 * Calculate grade and corresponding remarks based on score
 */
private function calculateGrade($score)
{
    if ($score >= 90 && $score <= 100) {
        return ["grade" => "A*", "remarks" => "Excellent"];
    } elseif ($score >= 80 && $score <= 89) {
        return ["grade" => "A", "remarks" => "Excellent"];
    } elseif ($score >= 70 && $score <= 79) {
        return ["grade" => "B", "remarks" => "Very Good"];
    } elseif ($score >= 60 && $score <= 69) {
        return ["grade" => "C", "remarks" => "Good"];
    } elseif ($score >= 50 && $score <= 59) {
        return ["grade" => "D", "remarks" => "Credit"];
    } elseif ($score >= 40 && $score <= 49) {
        return ["grade" => "E", "remarks" => "Pass"];
    } elseif ($score >= 30 && $score <= 39) {
        return ["grade" => "F", "remarks" => "Fail"];
    } else {
        return ["grade" => "U", "remarks" => "Ungraded"];
    }
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
        $deleted = DB::table("tblassmain_ai")
            ->where("transid", $request->transid)
            ->update(["deleted" => 1]);

        if (!$deleted) {
            return response()->json(["error" => "Delete failed"], 400);
        }

        return response()->json(["ok" => true, "message" => "Assessment deleted successfully"]);
    } catch (\Exception $e) {
        return response()->json(["error" => $e->getMessage()], 500);
    }
}


// public function getAssessment(Request $request, $id)
// {
//     // Get filter values from the request
//     $classCode = $request->input('class_code', '');
//     $subcode = $request->input('subcode', '');
//     $term = $request->input('term', '');

//     $assessment = DB::table("tblstudent")
//         ->leftJoin("tblassmain_ai", function ($join) use ($subcode) {
//             $join->on("tblstudent.student_no", "=", "tblassmain_ai.student_no")
//                  //->on("tblstudent.admterm", "=", "tblassmain_ai.term")
//                  ->on("tblstudent.current_class", "=", "tblassmain_ai.class_code")
//                  ->on(DB::raw("COALESCE(tblassmain_ai.subcode, '')"), "=", DB::raw("'$subcode'"));
//         })
//         ->leftJoin("tblclass", "tblstudent.current_class", "=", "tblclass.class_code")
//         ->select(
//             "tblstudent.student_no",
//             "tblstudent.transid",
//             "tblstudent.school_code",
//             DB::raw("CONCAT(tblstudent.fname, ' ', COALESCE(tblstudent.mname, ''), ' ', tblstudent.lname) AS student_name"),
//             DB::raw("COALESCE(tblclass.class_desc, '') AS course_name"),
//             DB::raw("COALESCE(tblassmain_ai.transid, '') AS assessment_transid"),
//             DB::raw('IFNULL(tblassmain_ai.class_code, "' . $classCode . '") AS class_code'),
//             DB::raw('"' . $subcode . '" AS subcode'),
//             DB::raw('IFNULL(tblassmain_ai.term, "' . $term . '") AS term'),
//             DB::raw("COALESCE(tblassmain_ai.deleted, '0') AS deleted"),
//             DB::raw('COALESCE(tblassmain_ai.transid, tblstudent.transid) as transid'),

//             // Ensure paper1 and paper2 are 0 if there is no exact match in tblassmain_ai
//             DB::raw("
//                 CASE
//                     WHEN tblassmain_ai.student_no IS NULL THEN '0'
//                     WHEN tblassmain_ai.subcode IS NULL OR tblassmain_ai.subcode = '' THEN '0'
//                     ELSE COALESCE(tblassmain_ai.paper1, '0')
//                 END AS paper1
//             "),
//             DB::raw("
//                 CASE
//                     WHEN tblassmain_ai.student_no IS NULL THEN '0'
//                     WHEN tblassmain_ai.subcode IS NULL OR tblassmain_ai.subcode = '' THEN '0'
//                     ELSE COALESCE(tblassmain_ai.paper2, '0')
//                 END AS paper2
//             "),
//             DB::raw("
//                 CASE
//                     WHEN tblassmain_ai.student_no IS NULL THEN '0'
//                     WHEN tblassmain_ai.subcode IS NULL OR tblassmain_ai.subcode = '' THEN '0'
//                     ELSE COALESCE(tblassmain_ai.total_score, '0')
//                 END AS total_score
//             ")
//         )
//         ->where(function ($query) use ($id) {
//             $query->where("tblstudent.transid", $id)
//                   ->orWhere("tblassmain_ai.transid", $id);
//             })
//         ->first();

//     if (!$assessment) {
//         return response()->json(['error' => 'Assessment not found', 'query_id' => $id], 404);
//     }

//     return response()->json($assessment);
// }

public function getAssessment(Request $request, $id)
{
    // Get filter values from the request
    $classCode = $request->input('class_code', '');
    $subcode = $request->input('subcode', '');
    $term = $request->input('term', '');

    $assessment = DB::table("tblstudent")
        ->leftJoin("tblassmain_ai", function ($join) use ($subcode, $classCode, $term) {
            $join->on("tblstudent.student_no", "=", "tblassmain_ai.student_no")
                 ->on("tblstudent.current_class", "=", "tblassmain_ai.class_code")
                 ->where(function ($query) use ($subcode, $classCode, $term) {
                     $query->where("tblassmain_ai.subcode", $subcode)
                           ->where("tblassmain_ai.class_code", $classCode)
                           ->where("tblassmain_ai.term", $term)
                           ->orWhereNull("tblassmain_ai.subcode"); // Ensure students with no exact match are included
                 });
        })
        ->leftJoin("tblclass", "tblstudent.current_class", "=", "tblclass.class_code")
        ->select(
            "tblstudent.student_no",
            "tblstudent.transid",
            "tblstudent.school_code",
            DB::raw("CONCAT(tblstudent.fname, ' ', COALESCE(tblstudent.mname, ''), ' ', tblstudent.lname) AS student_name"),
            DB::raw("COALESCE(tblclass.class_desc, '') AS course_name"),
            DB::raw("COALESCE(tblassmain_ai.transid, '') AS assessment_transid"),
            DB::raw('IFNULL(tblassmain_ai.class_code, "' . $classCode . '") AS class_code'),
            DB::raw('"' . $subcode . '" AS subcode'),
            DB::raw('IFNULL(tblassmain_ai.term, "' . $term . '") AS term'),
            DB::raw("COALESCE(tblassmain_ai.deleted, '0') AS deleted"),
            DB::raw('COALESCE(tblassmain_ai.transid, tblstudent.transid) as transid'),

            // Ensure paper1, paper2, and total_score are 0 if there is no exact match in tblassmain_ai
            DB::raw("
                CASE
                    WHEN tblassmain_ai.student_no IS NULL OR tblassmain_ai.subcode != '$subcode' 
                         OR tblassmain_ai.class_code != '$classCode' OR tblassmain_ai.term != '$term' 
                    THEN '0'
                    ELSE COALESCE(tblassmain_ai.paper1, '0')
                END AS paper1
            "),
            DB::raw("
                CASE
                    WHEN tblassmain_ai.student_no IS NULL OR tblassmain_ai.subcode != '$subcode' 
                         OR tblassmain_ai.class_code != '$classCode' OR tblassmain_ai.term != '$term' 
                    THEN '0'
                    ELSE COALESCE(tblassmain_ai.paper2, '0')
                END AS paper2
            "),
            DB::raw("
                CASE
                    WHEN tblassmain_ai.student_no IS NULL OR tblassmain_ai.subcode != '$subcode' 
                         OR tblassmain_ai.class_code != '$classCode' OR tblassmain_ai.term != '$term' 
                    THEN '0'
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



public function fetchAssessments(Request $request)
{
    $class_code = $request->input('class_code');
    $term = $request->input('term');
    $student_no = $request->input('student_no');

    $assessments = DB::table('tblassmain_ai')
        ->join('tblsubject', 'tblassmain_ai.subcode', '=', 'tblsubject.subcode') // Only subjects with assessments
        ->join('tblstudent', 'tblassmain_ai.student_no', '=', 'tblstudent.student_no')
        ->leftJoin('tblclass', 'tblclass.class_code', '=', DB::raw("'$class_code'"))
        ->leftJoin('tblcomment_ia', 'tblassmain_ai.student_no', '=', 'tblcomment_ia.student_no') // Join comments table
        ->where('tblassmain_ai.class_code', $class_code)
        ->where('tblassmain_ai.term', $term)
        ->where('tblassmain_ai.student_no', $student_no)
        ->where('tblassmain_ai.deleted', '0')
        ->select(
            'tblsubject.subname',
            'tblclass.class_desc as class_name',
            DB::raw("TRIM(CONCAT(COALESCE(tblstudent.fname, ''), ' ', COALESCE(tblstudent.mname, ''), ' ', COALESCE(tblstudent.lname, ''))) AS student_name"),
            DB::raw('COALESCE(tblassmain_ai.paper1, 0) as paper1'),
            DB::raw('COALESCE(tblassmain_ai.paper2, 0) as paper2'),
            DB::raw('COALESCE(tblassmain_ai.total_score, 0) as total_score'),
            DB::raw('COALESCE(tblassmain_ai.grade, "N/A") as grade'),
            DB::raw('COALESCE(tblassmain_ai.t_remarks, "No Remarks") as t_remarks'),
            DB::raw('COALESCE(tblcomment_ia.ct_remarks, "No Comment") as ct_remarks') // Selecting ct_remarks
        )
        ->distinct()
        ->get();

    return response()->json($assessments);
}

}
