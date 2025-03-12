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

class AssessmentController extends Controller{

    public function index(Request $request)
{
    $staffNo = auth()->user()->staff_no; // Get logged-in staff number
    $acyear = date("Y"); // Current academic year

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

    return view("modules.newassessment.index", compact("assessments"));
}


    public function filterFetchTerminalReport(Request $request, $schoolCode)
    {
        $studentArray = [];
        $assessment = [];
        $remark = [];

        $studentDetails = Student::distinct()->select("tblstudent.*")
            ->join("tblassmain", "tblstudent.student_no", "tblassmain.student_no")
            ->where("tblstudent.deleted", "0")
            ->where("tblassmain.deleted", "0")
            ->where("tblassmain.school_code", $schoolCode)
            ->where("tblstudent.school_code", $schoolCode)
            ->where("tblstudent.batch", $request->batch)
            ->where("tblassmain.semester", $request->semester)
            ->where("tblassmain.branch_code", $request->branch)
            ->where("tblassmain.prog_code", $request->prog)
            ->get();

        foreach ($studentDetails as $student) {
            $rows = DB::table("tblassmain")->select(
                "tblassmain.semester",
                "tblassmain.subcode",
                "tblassmain.pexam",
                "tblassmain.total_test",
                "tblassmain.total_exam",
                "tblassmain.total_score",
                "tblassmain.student_no",
                "tblsubject.course_desc",
                "tblprog.prog_desc"
            )
                ->join("tblprog", "tblassmain.prog_code", "tblprog.prog_code")
                ->join("tblsubject", "tblassmain.subcode", "tblsubject.subcode")
                ->where("tblassmain.deleted", "0")
                ->where("tblprog.deleted", "0")
                ->where("tblsubject.deleted", "0")
                ->where("tblsubject.school_code", $schoolCode)
                ->where("tblassmain.school_code", $schoolCode)
                ->where("tblprog.school_code", $schoolCode)
                ->where("tblassmain.student_no", $student->student_no)
                ->where("tblassmain.branch_code", $request->branch)
                ->where("tblassmain.semester", $request->semester)
                ->where("tblassmain.prog_code", $request->prog)
                ->get();


            $studentArray[] = $student;
            $student->assessment = $rows;
        } //End of if statement

        return response()->json([
            "data" => TranscriptResource::collection($studentArray),
        ]);
    }

public function staff(Request $request, $schoolCode)
{
    try {
        if (!Auth::check()) {
            return response()->json(["error" => "Unauthorized"], 401);
        }

        $user = Auth::user();
        if (!$user->school) {
            return response()->json(["error" => "School not found"], 404);
        }

        // Fetch staff number and school code
        $staffNo = $user->userid;
        $schoolCode = $user->school->school_code;

        // Fetch courses assigned to the lecturer
        $newCourses = DB::table('tblsubject')
            ->join('tblsubject_assignment', "tblsubject_assignment.subcode", '=', 'tblsubject.subcode')
            ->where('tblsubject.deleted', '0')
            ->where('tblsubject_assignment.deleted', '0')
            ->where('tblsubject_assignment.staffno', $staffNo)
            ->pluck('tblsubject.subcode')
            ->toArray();

        // If no courses are assigned
        if (empty($newCourses)) {
            return response()->json(["error" => "No assigned courses"], 404);
        }

        // Fetch students and assessments
        $assessment = DB::table("tblgrade")
            ->join("tblstudent", "tblstudent.student_no", "=", "tblgrade.grade_code")
            ->join("tblsubject", "tblsubject.subcode", "=", "tblsubject.subcode")
            ->leftJoin("tblassmain", function ($join) {
                $join->on("tblassmain.student_no", "=", "tblstudent.student_no")
                     ->on("tblassmain.subcode", "=", "tblsubject.subcode")
                     ->where("tblassmain.deleted", "0");
            })
            ->select(
                "tblstudent.student_no as student_id",
                "tblassmain.transid as assid",
                "tblsubject.subname as student_course",
                "tblsubject.subcode as course_id",
                DB::raw("CONCAT(tblstudent.fname, ' ', tblstudent.mname, ' ', tblstudent.lname) AS student_name"),
                DB::raw("COALESCE(tblassmain.total_test, 0) as pure_test"),
                DB::raw("COALESCE(tblassmain.total_exam, 0) as pure_exam"),
                DB::raw("COALESCE(tblassmain.total_score, 0) as total_score")
            )
            ->whereIn("tblsubject.subcode", $newCourses)
            ->where("tblsubject.deleted", "0")
            ->get();

        return response()->json(["ok" => true, "data" => $assessment]);

    } catch (\Exception $e) {
        return response()->json(["error" => $e->getMessage()], 500);
    }
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
            "msg" => "Registration failed. Please complete all required fields",
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

    $totalScore = $request->paper1 + $request->paper2;

    try {
        $assessment = DB::table("tblassmain_ai")
            ->where("student_no", $request->student_no)
            ->where("subcode", $request->subcode)
            ->where("class_code", $request->class_code)
            ->where("term", $request->term)
            ->first();

        if ($assessment) {
            DB::table("tblassmain_ai")
                ->where("transid", $assessment->transid)
                ->update([
                    "paper1" => $request->paper1,
                    "paper2" => $request->paper2,
                    "total_score" => $totalScore,
                    "deleted" => "0",
                    "modifyuser" => $request->createuser,
                    "modifydate" => now(),
                ]);

            return response()->json([
                "ok" => true,
                "msg" => "Assessment updated successfully",
            ]);
        } else {
            $newTransId = strtoupper(bin2hex(random_bytes(5)));
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
                "t_remarks" => "", // Add remarks logic if needed
                "deleted" => "0",
                "createuser" => $request->createuser,
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



    public function delete($code)
    {
        try {
            request()->merge([
                "assid" => $code
            ]);

            $validator = Validator::make(
                request()->all(),
                [
                    "assid" => "required|exists:tblassmain,transid"
                ],
                [
                    "assid.required" => "Assessment code is required",
                    "assid.exists" => "Assessment code cannot be found"
                ]
            );


            if ($validator->fails()) {
                return response()->json([
                    "ok" => false,
                    "msg" => "Deleting failed ," . join(" ,", $validator->errors()->all())
                ]);
            }

            $update =  DB::table("tblassmain")
                ->where("transid", $code)
                ->update(
                    [
                        "deleted" => 1
                    ]
                );
            if (!$update) {
                return response()->json([
                    "ok" => false,
                    "msg" => "Sorry! An i"
                ]);
            }


            return response()->json([
                "ok" => true,
                "msg" => "Student assessment deleted"
            ]);
        } catch (Exception $th) {
            Log::error($th->getMessage());
            return response()->json([
                "ok" => false,
                "msg" => "Sorry! An internal error occured"
            ]);
        }
    }
    

    public function update(Request  $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                "assessment_id" => "required",
                "course" => "required",
                "branch" => "required",
                "semester" => "required",
                "exam" => "required",
                "student" => "required",
                "test" => "required",
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "ok" => false,
                "msg" => "Registration failed. Please complete all require fields",
                "error" => [
                    "msg" => "Some required fields are missing: " . join(" ", $validator->errors()->all()),
                    "fix" => "Please complete all required fields",
                ]
            ]);
        }

        if (empty($request->test) && empty($request->exam)) {
            return response()->json([
                "ok" => false,
                "msg" => "Test score or Exam score must not be empty"
            ]);
        }
        $studentDetails = Student::where("deleted", "0")->where("school_code", $request->school_code)
            ->where("student_no", $request->student)->first();

        try {
            $transactionResult = DB::transaction(function () use ($request, $studentDetails) {

                // $classTotal = $request->class_work + $request->home_work + $request->class_test;
                $classTotal = $request->test;
                $percentageClass = 0.40 * ($classTotal);
                $percentageExams = 0.60 * ($request->exam);
                $totalScore = $percentageExams + $percentageClass;

                DB::table("tblassmain")
                    ->where("transid", $request->assessment_id)
                    ->update([
                        "school_code" => $request->school_code,
                        "student_code" => $request->student,
                        "branch_code" => $request->branch,
                        "acyear" => $studentDetails->batch,
                        "semester" => $request->semester,
                        "subcode" => $request->course,
                        "total_exam" => $percentageExams,
                        "total_test" => $percentageClass,
                        "total_score" => $totalScore,
                        "prog_code" => $studentDetails->prog,
                        "deleted" => "0",
                        "modifydate" => date("Y-m-d"),
                        "modifyuser" => $request->createuser,
                    ]);
            });

            // If the return value of DB::transaction is null (meaning it's empty)
            // then it means our transaction succeeded. If this is however not the
            // case, then we throw an exception here.
            if (!empty($transactionResult)) {
                throw new Exception($transactionResult);
            }

            return response()->json([
                "ok" => true,
                "msg" => "Assessment edit successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "ok" => false,
                "msg" => "Editing failed. An internal error occured. If this continues please contact your administrator",
                "error" => [
                    "msg" => "An internal error ocurred Could not update assessment. {$e->getMessage()}",
                    "fix" => "Check the error message for clues",
                ]
            ]);
        }
    }

    public function getAssessment($assid) {
        try {
            $assessment = DB::table("tblassmain")
                ->where("transid", $assid)
                ->where("deleted", "0")
                ->first();
    
            if (!$assessment) {
                return response()->json(["error" => "Assessment not found"], 404);
            }
    
            return response()->json(["ok" => true, "assessment" => $assessment]);
        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

}