<?php

namespace App\Http\Controllers;

use App\Http\Resources\AssignedCoursesResource;
use Illuminate\Http\Request;
use App\Http\Resources\courseRegisteredStuentsResourceController;
use App\Http\Resources\CourseStudentResource;
use App\Http\Resources\managecoursesResourcecontroller;
use App\Http\Resources\ProgramResource;
use App\Http\Resources\ProgramStudentResource;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class CourseController extends Controller
{
    //retrieving courses
    public function index($school_code)
    {
        $courses = managecoursesResourcecontroller::collection(
            DB::table("tblsubject")->select("tblsubject.*","tblsemester.sem_desc")
            ->leftJoin("tblsemester","tblsubject.semester","tblsemester.sem_code")
                ->where("tblsubject.school_code", $school_code)
                ->where('tblsubject.deleted', '0')
                ->get()
        );
        return response()->json([
            "data" => $courses
        ]);
    }


    //fetching sttudents and their programs
    public function fetchProgramStudents($school_code)
    {
        $studentsProgramData = ProgramStudentResource::collection(
            DB::table('tblstudent')
                ->select('tblstudent.*', 'tblprog.*')
                ->join('tblprog', 'tblstudent.prog', 'tblprog.prog_code')
                ->where('tblstudent.deleted', 0)
                ->where('tblprog.deleted', 0)
                ->where('tblstudent.school_code', $school_code)
                ->where('tblprog.school_code', $school_code)
                ->get()
        );
        return response()->json([
            "ok" => true,
            "data" => $studentsProgramData
        ]);
    }

    //fetching students and their courses
    public function fetchCourseStudents($school_code)
    {
        $studentsProgramData = CourseStudentResource::collection(
            DB::table('tblstudent')
                ->select('tblstudent.*', 'tblsubject.*')
                ->join('tblsubject', 'tblstudent.prog', 'tblsubject.prog')
                ->where('tblstudent.deleted', 0)
                ->where('tblsubject.deleted', 0)
                ->where('tblstudent.school_code', $school_code)
                ->where('tblsubject.school_code', $school_code)
                ->get()
        );
        return response()->json([
            "ok" => true,
            "data" => $studentsProgramData
        ]);
    }


    //deleting course
    public function destroy($coursecode, $school_code)
    {

        $course = DB::table('tblsubject')
            ->where('subcode', $coursecode)
            ->where('school_code', $school_code);
        if (empty($course)) {
            return response()->json([
                "ok" => false,
                "msg" => "Unknown code supplied ",


            ]);
        }


        $updated = $course->update([
            "deleted" => "1",
        ]);

        if (!$updated) {
            return response()->json([
                "ok" => false,
                "msg" => "An internal error ocurred",
            ]);
        }

        return response()->json([
            "ok" => true,
        ]);
    }

    //adding courses
    public function store(Request $request)
    {
        $validator = validator(
            $request->all(),
            [
                "subname" => "required",
                "subcode" => "required|unique:tblsubject",
                "course_desc" => "required",
                "credit" => "required",
                "program" => "required",
                "semester" => "required",
                "level" => "required"
            ],
            [
                "subname.required" => "No course title provided",
                "subcode.required" => "No course code provided",
                "course_desc.required" => "No course description provided",
                "credit.required" => "No course credit provided",
                "semester.required" => "No semester chosen ",
                "level.required" => "No level chosen ",

            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "ok" => false,
                "msg" => "Adding course failed" . join('.', $validator->errors()->all())
            ]);
        }

        $checkcourse = DB::table('tblsubject')
            ->where('school_code', $request->school_code)
            ->where('subname', $request->subname)
            ->get()
            ->count();
        if ($checkcourse > 0) {
            return response()->json([
                "ok" => false,
                "msg" => "Adding course failed.Course already exists"
            ]);
        }
        try {
            $transactionResult = DB::transaction(function () use ($request) {

                DB::table('tblsubject')->insert([
                    "transid" => strtoupper(strtoupper(bin2hex(random_bytes(5)))),
                    "school_code" => $request->school_code,
                    "branch_code" => "001",
                    "subname" => $request->subname,
                    "subcode" => $request->subcode,
                    "course_desc" => $request->course_desc,
                    "credit" => $request->credit,
                    "semester" => $request->semester,
                    "prog" => $request->program,
                    "level_code" => $request->level,
                    "source" => null,
                    "export" => null,
                    "deleted" => "0",
                    'createuser' =>  $request->createuser,
                    'createdate' => date('Y-m-d'),
                ]);
            });
            if (!empty($transactionResult)) {
                throw new Exception($transactionResult);
            }
            return response()->json([
                "ok" => true,
                "msg" => "course successfully added"
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed adding course: " . $e->getMessage());
            return response()->json([
                "ok" => false,
                "msg" => "Adding course failed!",
                "error" => [
                    "msg" => "Could not add staff. {$e->getMessage()}",
                    "fix" => "Check the error message for clues",
                ]
            ], 500);
        }
    }


    //updating courses
    public function update(Request $request)
    {
        $validator = validator(
            $request->all(),
            [
                "subname" => "required",
                "subcode" => "required",
                // "course_desc" => "required",
                "credit" => "required",
                "semester" => "required",
                "level" => "required"
            ],
            [
                "subname.required" => "No course title provided",
                "subcode.required" => "No course code provided",
                // "course_desc.required" => "No course description provided",
                "credit.required" => "No course credit provided",
                "semester.required" => "No semester chosen ",
                "level.required" => "No level chosen "
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "ok" => false,
                "msg" => "Updating course failed" . join('.', $validator->errors()->all())
            ]);
        }

        try {
            $transactionResult = DB::transaction(function () use ($request) {

                DB::table('tblsubject')
                    ->where('transid', $request->transid)->update([
                        "subname" => $request->subname,
                        "subcode" => $request->subcode,
                        "course_desc" => $request->course_desc,
                        "credit" => $request->credit,
                        "semester" => $request->semester,
                        "level_code" => $request->level
                    ]);
            });
            if (!empty($transactionResult)) {
                throw new Exception($transactionResult);
            }
            return response()->json([
                "ok" => true,
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed adding course: " . $e->getMessage());
            return response()->json([
                "ok" => false,
                "msg" => "Updating course failed!",
                "error" => [
                    "msg" => $e->__toString(),
                    "err_msg" => $e->getMessage(),
                    "fix" => "Please complete all required fields",

                ]
            ]);
        }
    }


    //fething students per course
    public function fetch_students($school_code, $coursecode)
    {
        $studentLIST = courseRegisteredStuentsResourceController::collection(
            DB::table('tblgrade')
                ->select("tblgrade.acyear", "tblgrade.semester", "tblstudent.fname", "tblstudent.lname")
                ->join('tblstudent', 'tblstudent.student_no', "=", "tblgrade.grade_code")
                ->where('tblstudent.school_code', $school_code)
                ->where('tblgrade.school_code', $school_code)
                ->where('tblstudent.deleted', '0')
                ->where('tblgrade.deleted', '0')
                ->where('tblsubject.subcode', $coursecode)
                ->get()
        );
        return response()->json([
            "data" => $studentLIST
        ]);
    }


    //filtering  courses per student
    public function filtercourse($coursedata)
    {
        try {
            $coursedata = json_decode(html_entity_decode(stripslashes($coursedata)));
            //Filtering per program, semester and acyear
            if ($coursedata->school != "" and  $coursedata->program != "" and  $coursedata->level != ""  and  $coursedata->semester != "") {

                $courses = managecoursesResourcecontroller::collection(
                    DB::table("tblsubject")
                        ->where("school_code", $coursedata->school)
                        ->where("semester", $coursedata->semester)
                        ->where("prog", $coursedata->program)
                        ->where("level_code", $coursedata->level)
                        ->where('deleted', '0')
                        ->get()
                );
                return response()->json([
                    "data" => $courses
                ]);
            }
            //Filtering by program
            if ($coursedata->school != "" and $coursedata->program != "" and $coursedata->level == ""  and $coursedata->semester == "") {
                $courses = managecoursesResourcecontroller::collection(
                    DB::table("tblsubject")
                        ->where("school_code", $coursedata->school)
                        ->where("prog", $coursedata->program)
                        ->where('deleted', '0')
                        ->get()
                );
                return response()->json([
                    "data" => $courses
                ]);
            }
            //Filtering by semester
            if ($coursedata->school != "" and $coursedata->program == "" and $coursedata->semester != ""  and $coursedata->level == "") {
                $courses = managecoursesResourcecontroller::collection(
                    DB::table("tblsubject")
                        ->where("school_code", $coursedata->school)
                        ->where("semester", $coursedata->semester)
                        ->where('deleted', '0')
                        ->get()
                );
                return response()->json([
                    "data" => $courses
                ]);
            }
            //Filtering by level
            if ($coursedata->school != "" and $coursedata->program == "" and $coursedata->semester == ""  and $coursedata->level != "") {
                $courses = managecoursesResourcecontroller::collection(
                    DB::table("tblsubject")
                        ->where("school_code", $coursedata->school)
                        ->where("level_code", $coursedata->level)
                        ->where('deleted', '0')
                        ->get()
                );
                return response()->json([
                    "data" => $courses
                ]);
            }
            //filtering per program and semster
            if ($coursedata->school != "" and $coursedata->program != "" and $coursedata->semester != ""  and $coursedata->level == "") {
                $courses = managecoursesResourcecontroller::collection(
                    DB::table("tblsubject")
                        ->where("school_code", $coursedata->school)
                        ->where("prog", $coursedata->program)
                        ->where("semester", $coursedata->semester)
                        ->where('deleted', '0')
                        ->get()
                );
                return response()->json([
                    "data" => $courses
                ]);
            }
            //filtering per program and level
            if ($coursedata->school != "" and $coursedata->program != "" and $coursedata->semester == ""  and $coursedata->level != "") {
                $courses = managecoursesResourcecontroller::collection(
                    DB::table("tblsubject")
                        ->where("school_code", $coursedata->school)
                        ->where("prog", $coursedata->program)
                        ->where("level_code", $coursedata->level)
                        ->where('deleted', '0')
                        ->get()
                );
                return response()->json([
                    "data" => $courses
                ]);
            }
            //Filtering by semester and level
            if ($coursedata->school != "" and $coursedata->program == "" and $coursedata->semester != ""  and $coursedata->level != "") {
                $courses = managecoursesResourcecontroller::collection(
                    DB::table("tblsubject")
                        ->where("school_code", $coursedata->school)
                        ->where("semester", $coursedata->semester)
                        ->where("level_code", $coursedata->level)
                        ->where('deleted', '0')
                        ->get()
                );
                return response()->json([
                    "data" => $courses
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("Filtering courses failed" . $e->getMessage());
            return response()->json([
                "ok" => false,
                "msg" => "Couldn't filter  courses",

            ]);
        }
    }


    //filter student
    public function filterStudentPerCourse($data)
    {
        $coursedata = json_decode(html_entity_decode(stripslashes($data)));

        $studentsCourseData = CourseStudentResource::collection(
            DB::table('tblstudent')
                ->select('tblstudent.*', 'tblsubject.*')
                ->join('tblsubject', 'tblstudent.prog', 'tblsubject.prog')
                ->where('tblstudent.school_code',  $coursedata->school)
                ->where('tblsubject.school_code',  $coursedata->school)
                ->where('tblsubject.subcode', $coursedata->course)
                ->where('tblstudent.deleted', 0)
                ->where('tblsubject.deleted', 0)
                ->get()
        );
        return response()->json([
            "ok" => true,
            "data" => $studentsCourseData
        ]);
    }

    //filter student per program
    public function filterStudentPerProgram($data)
    {
        $coursedata = json_decode(html_entity_decode(stripslashes($data)));

        $studentsProgramData = ProgramStudentResource::collection(
            DB::table('tblstudent')
                ->select('tblstudent.*', 'tblprog.*')
                ->join('tblprog', 'tblstudent.prog', 'tblprog.prog_code')
                ->where('tblstudent.school_code', $coursedata->school)
                ->where('tblprog.school_code', $coursedata->school)
                ->where('tblprog.prog_code', $coursedata->program)
                ->where('tblstudent.prog', $coursedata->program)
                ->where('tblstudent.deleted', 0)
                ->where('tblprog.deleted', 0)
                ->get()
        );
        return response()->json([
            "ok" => true,
            "data" => $studentsProgramData
        ]);
    }


    public function assignCourse(Request $request)
    {
        $validator = validator(
            $request->all(),
            [
                "subcode" => "required",
                "staff" => "required",
                "branch" => "required"
            ],
            [
                "subcode.required" => "No course selected",
                "staff.required" => "No lecturer selected",
                "branch.required" => "No branch selected ",
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "ok" => false,
                "msg" => "Assigning course failed" . join('.', $validator->errors()->all())
            ]);
        }

        $checkcourse = DB::table('tblsubject_assignment')
            ->where('school_code', $request->school_code)
            ->where('subcode', $request->subcode)
            ->where('branch_code', $request->branch)
            ->where('staffno', $request->staff)
            ->first();

        if (!empty($checkcourse)) {
            return response()->json([
                "ok" => false,
                "msg" => "Assigning course failed. This course has already been assigned"
            ]);
        }
        try {
            $transactionResult = DB::transaction(function () use ($request) {

                DB::table('tblsubject_assignment')->insert([
                    "transid" => strtoupper(strtoupper(bin2hex(random_bytes(5)))),
                    "school_code" => $request->school_code,
                    "subcode" => $request->subcode,
                    "branch_code" => $request->branch,
                    "staffno" => $request->staff,
                    "date_assigned" => date('Y-m-d'),
                    "deleted" => "0",
                    "source" => "O",
                    'createuser' =>  $request->createuser,
                    'createdate' => date('Y-m-d'),
                ]);
            });
            if (!empty($transactionResult)) {
                throw new Exception($transactionResult);
            }
            return response()->json([
                "ok" => true,
                "msg" => "course successfully assigned"
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed assigning course: " . $e->getMessage());
            return response()->json([
                "ok" => false,
                "msg" => "Assigning course failed!",
                "error" => [
                    "msg" => "Could not assign course. {$e->getMessage()}",
                    "fix" => "Check the error message for clues",
                ]
            ], 500);
        }
    }


    public function registerStudentCourse(Request $request)
    {
        $validator = validator(
            $request->all(),
            [
                "subcode" => "required",
                "student_no" => "required",
                "branch" => "required"
            ],
            [
                "subcode.required" => "No course selected",
                "student_no.required" => "No student selected",
                "branch.required" => "No branch selected",
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "ok" => false,
                "msg" => "Assigning course failed" . join('.', $validator->errors()->all())
            ]);
        }

        $checkcourse = DB::table('tblgrade')
            ->where('school_code', $request->school_code)
            ->where('subcode', $request->subcode)
            ->where('branch_code', $request->branch)
            ->where('student_code', $request->student_no)
            ->first();

        if (!empty($checkcourse)) {
            return response()->json([
                "ok" => false,
                "msg" => "Course registration failed. This course has already been registered by this student"
            ]);
        }
        try {
            $transactionResult = DB::transaction(function () use ($request) {

                DB::table('tblgrade')->insert([
                    "transid" => strtoupper(strtoupper(bin2hex(random_bytes(5)))),
                    "school_code" => $request->school_code,
                    "subcode" => $request->subcode,
                    "branch_code" => $request->branch,
                    "semester" => $request->semester,
                    "student_code" => $request->student_no,
                    "reg_date" => date('Y-m-d'),
                    "deleted" => "0",
                    "source" => "O",
                    'createuser' =>  $request->createuser,
                    'createdate' => date('Y-m-d'),
                ]);
            });
            if (!empty($transactionResult)) {
                throw new Exception($transactionResult);
            }
            return response()->json([
                "ok" => true,
                "msg" => "course successfully assigned"
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed assigning course: " . $e->getMessage());
            return response()->json([
                "ok" => false,
                "msg" => "Assigning course failed!",
                "error" => [
                    "msg" => "Could not assign course. {$e->getMessage()}",
                    "fix" => "Check the error message for clues",
                ]
            ], 500);
        }
    }



    public function fetchAssignedCourses($school_code)
    {
        $assignedCourses = AssignedCoursesResource::collection(
            DB::table("tblsubject_assignment")
                ->select(
                    'tblstaff.fname',
                    'tblstaff.mname',
                    'tblstaff.lname',
                    'tblsubject.subcode',
                    'tblsubject.subname',
                    'tblsubject_assignment.date_assigned',
                    'tblsemester.sem_desc',
                    'tblsubject_assignment.transid' 
                )
                ->join('tblstaff', 'tblstaff.staffno', 'tblsubject_assignment.staffno')
                ->join('tblsubject', 'tblsubject.subcode', 'tblsubject_assignment.subcode')
                ->leftJoin('tblsemester', 'tblsubject.semester', 'tblsemester.sem_code')
                ->where("tblsubject_assignment.school_code", $school_code)
                ->where("tblstaff.school_code", $school_code)
                ->where("tblsubject.school_code", $school_code)
                ->where('tblsubject_assignment.deleted', '0')
                ->where('tblsubject.deleted', '0')
                ->where('tblstaff.deleted', '0')
                ->get()
        );
        return response()->json([
            "data" => $assignedCourses
        ]);
    }

    //updating courses
    public function updateAssignedCourses(Request $request)
    {
        $validator = validator(
            $request->all(),
            [
                'transid' => 'required',
                'subcode' => 'required',
                'staff' => 'required',
                'branch' => 'required',
            ],
            [
                'subcode.required' => 'No course selected',
                'staff.required' => 'No lecturer selected',
                'branch.required' => 'No branch selected',
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "ok" => false,
                "msg" => "Updating course failed" . join('.', $validator->errors()->all())
            ]);
        }

        try {
            $transactionResult = DB::transaction(function () use ($request) {

                DB::table('tblsubject_assignment')
                    ->where('transid', $request->transid)->update([
                       
                        'subcode' => $request->subcode,
                        'staffno' => $request->staff,
                        'branch_code' => $request->branch,
                        // 'updated_at' => now()
                    ]);
            });
            if (!empty($transactionResult)) {
                throw new Exception($transactionResult);
            }
            return response()->json([
                "ok" => true,
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed adding course: " . $e->getMessage());
            return response()->json([
                "ok" => false,
                "msg" => "Updating course failed!",
                "error" => [
                    "msg" => $e->__toString(),
                    "err_msg" => $e->getMessage(),
                    "fix" => "Please complete all required fields",

                ]
            ]);
        }
    }



    public function assignedcoursesdelete($id)
    {
        $dept = DB::table("tblsubject_assignment")
            ->where("transid", $id)->update([
                "deleted" => 1
            ]);


        if (!$dept) {
            return response()->json([
                "ok" => false,
                "msg" => "An internal error ocurred",
            ]);
        }

        return response()->json([
            "ok" => true,
        ], 200);
    }




}


