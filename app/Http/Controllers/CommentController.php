<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $staffNo = auth()->user()->userid;

        $classes = DB::table('tblclass')
            ->join('tblclass_teacher', 'tblclass.class_code', '=', 'tblclass_teacher.class_code')
            ->where('tblclass_teacher.staff_no', $staffNo)
            ->where('tblclass.deleted', '0')
            ->get();

        $acyear = date('Y');

        $acyearr = DB::table('tblacyear')
            ->where('current_term', '1')
            ->select('acyear_desc', 'acterm')
            ->first(); // ✅ Fix

        $comments = DB::table('tblcomment_ia')
            ->where('deleted', '0')
            ->get();

        $subjects = DB::table('tblsubject_assignment')
            ->join('tblsubject', 'tblsubject_assignment.subcode', '=', 'tblsubject.subcode')
            ->where('tblsubject_assignment.staffno', $staffNo)
            ->where('tblsubject_assignment.deleted', '0')
            ->select('tblsubject.subname', 'tblsubject.subcode')
            ->get();

        $students = DB::table('tblclass')
            ->join('tblclass_teacher', 'tblclass.class_code', '=', 'tblclass_teacher.class_code')
            ->join('tblsubject_assignment', 'tblclass.class_code', '=', 'tblsubject_assignment.class_code')
            ->join('tblstudent', 'tblstudent.current_class', '=', 'tblclass.class_code')
            ->where('tblclass_teacher.staff_no', $staffNo)
            ->select('tblstudent.student_no', 'tblstudent.fname', 'tblstudent.mname', 'tblstudent.lname', 'tblclass.class_code')
            ->get();

        $academicYears = DB::table('tblacyear')
            ->where('deleted', '0')
            ->get();

        $query = DB::table('tblassmain_ai as a')
            ->join('tblstudent as s', 'a.student_no', '=', 's.student_no')
            ->leftJoin('tblsubject_assignment as sa', function ($join) use ($staffNo, $acyear) {
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

        return view('modules.comment.index', compact(
            'classes',
            'subjects',
            'academicYears',
            'assessments',
            'students',
            'acyearr',
            'comments'
        ));
    }

    // store comment
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

    public function update(Request $request)
    {
        $request->validate([
            'transid' => 'required|string',
            'ct_remarks' => 'required|string|max:2000',
        ]);

        $comment = DB::table('tblcomment_ia')->where('transid', $request->transid)->first();

        if (! $comment) {
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

    public function delete(Request $request)
    {
        try {
            $deleted = DB::table('tblcomment_ia')
                ->where('transid', $request->transid)
                ->update(['deleted' => 1]);

            if (! $deleted) {
                return response()->json(['error' => 'Delete failed'], 400);
            }

            return response()->json(['ok' => true, 'message' => 'Comment deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function fetchAllComments(Request $request)
    {
        $class_code = $request->input('class_code');
        $term = $request->input('term');

        // Get all students in the class for the selected term
        $students = DB::table('tblstudent')
            ->join('tblassmain_ai', 'tblstudent.student_no', '=', 'tblassmain_ai.student_no')
            ->where('tblassmain_ai.class_code', $class_code)
            ->where('tblassmain_ai.term', $term)
            ->where('tblassmain_ai.deleted', '0')
            ->select(
                'tblstudent.student_no',
                DB::raw("TRIM(CONCAT(COALESCE(tblstudent.fname, ''), ' ', COALESCE(tblstudent.mname, ''), ' ', COALESCE(tblstudent.lname, ''))) AS student_name")
            )
            ->distinct()
            ->get();
    }

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

        return response()->json([
            'assessments' => $assessments,
            'student' => $student,
        ]);
    }
}
