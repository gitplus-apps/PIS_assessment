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
        $comments = DB::table('tblcomment_ia')->where('deleted', '0')->get();

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

        return view('modules.comment.index', compact('classes', 'subjects', 'academicYears', 'assessments', 'students', 'acyearr', 'comments'));
    }



//store comment
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
        'transid' => strtoupper(strtoupper(bin2hex(random_bytes(5)))),
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


// public function update(Request $request)
// {
//     try {
//         $request->validate([
//             'transid' => 'required|integer',
//             'comment' => 'required|string|max:255',
//         ]);

//         $comment = DB::table('tblcomment_ia')->find($request->transid);
//         if (!$comment) {
//             return response()->json(['success' => false, 'message' => 'Comment not found.']);
//         }

//         $comment->comment = $request->comment;
//         $comment->save();

//         return response()->json(['success' => true, 'message' => 'Comment updated successfully!']);
//     } catch (\Exception $e) {
//         return response()->json(['success' => false, 'message' => 'Error updating comment.']);
//     }
// }

public function update(Request $request)
{
    $request->validate([
        'transid' => 'required|string',
        'ct_remarks' => 'required|string|max:2000',
    ]);

    $comment = DB::table('tblcomment_ia')->where('transid', $request->transid)->first();

    if (!$comment) {
        return response()->json(['success' => false, 'message' => 'Comment not found.']);
    }

    $update = DB::table('tblcomment_ia')
        ->where('transid', $request->transid)
        ->update([
            'ct_remarks' => $request->ct_remarks,
            'modifyuser' => auth()->user()->id ?? 'System', // Track user making changes
            'modifydate' => now(),
        ]);

    if ($update) {
        return response()->json(['success' => true, 'message' => 'Comment updated successfully.']);
    } else {
        return response()->json(['success' => false, 'message' => 'Error updating comment.']);
    }
}




public function delete(Request $request) {
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


// public function fetchComment(Request $request)
// {
//     $class_code = $request->input('class_code');
//     $term = $request->input('term');
//     $student_no = $request->input('student_no');

//     $assessments = DB::table('tblassmain_ai')
//         ->join('tblsubject', 'tblassmain_ai.subcode', '=', 'tblsubject.subcode') // Only subjects with assessments
//         ->join('tblstudent', 'tblassmain_ai.student_no', '=', 'tblstudent.student_no')
//         ->leftJoin('tblclass', 'tblclass.class_code', '=', DB::raw("'$class_code'"))
//         ->leftJoin('tblcomment_ia', 'tblassmain_ai.student_no', '=', 'tblcomment_ia.student_no')
//         ->where('tblassmain_ai.class_code', $class_code)
//         ->where('tblassmain_ai.term', $term)
//         ->where('tblassmain_ai.student_no', $student_no)
//         ->where('tblassmain_ai.deleted', '0')
//         ->select(
//             'tblcomment_ia.transid as transid',
//             'tblcomment_ia.ct_remarks as comment',
//             'tblsubject.subname',
//             'tblclass.class_desc as class_name',
//             'tblstudent.student_no',
//             DB::raw("TRIM(CONCAT(COALESCE(tblstudent.fname, ''), ' ', COALESCE(tblstudent.mname, ''), ' ', COALESCE(tblstudent.lname, ''))) AS student_name"),
//             DB::raw('COALESCE(tblassmain_ai.paper1, 0) as paper1'),
//             DB::raw('COALESCE(tblassmain_ai.paper2, 0) as paper2'),
//             DB::raw('COALESCE(tblassmain_ai.total_score, 0) as total_score'),
//             DB::raw('COALESCE(tblassmain_ai.grade, "N/A") as grade'),
//             DB::raw('COALESCE(tblassmain_ai.t_remarks, "No Remarks") as t_remarks')
//         )
//         ->distinct()
//         ->get();

//     // Fetch student details
//     $student = DB::table('tblstudent')
//         ->where('student_no', $student_no)
//         ->select(
//             'student_no',
//             DB::raw("TRIM(CONCAT(COALESCE(fname, ''), ' ', COALESCE(mname, ''), ' ', COALESCE(lname, ''))) AS student_name")
//         )
//         ->first();

//         $student = DB::table('tblstudent')
//         ->where('student_no', $student_no)
//         ->select(
//             'student_no',
//             DB::raw("TRIM(CONCAT(COALESCE(fname, ''), ' ', COALESCE(mname, ''), ' ', COALESCE(lname, ''))) AS student_name")
//         )
//         ->first();

//     return response()->json([
//         'assessments' => $assessments,
//         'student' => $student
//     ]);
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
        ->leftJoin('tblcomment_ia', function ($join) {
            $join->on('tblassmain_ai.student_no', '=', 'tblcomment_ia.student_no')
                 ->where('tblcomment_ia.deleted', '0'); // Only fetch comments that are not deleted
        })
        ->where('tblassmain_ai.class_code', $class_code)
        ->where('tblassmain_ai.term', $term)
        ->where('tblassmain_ai.student_no', $student_no)
        ->where('tblassmain_ai.deleted', '0')
        ->select(
            'tblcomment_ia.transid as transid',
            'tblcomment_ia.ct_remarks as comment',
            'tblsubject.subname',
            'tblclass.class_desc as class_name',
            'tblstudent.student_no',
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

    // Fetch student details
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
