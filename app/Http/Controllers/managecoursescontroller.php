<?php

namespace App\Http\Controllers;

use App\Http\Resources\courseRegisteredStuentsResourceController;
use App\Http\Resources\managecoursesResourcecontroller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class managecoursescontroller extends Controller
{
    //retrieving courses
    public function index($school_code){
        $courses=managecoursesResourcecontroller::collection(
              DB::table("tblsubject")
              ->where("school_code", $school_code)
              ->where('deleted', '0')
              ->get()
        );
        return response()->json([
          "data"=>$courses
        ]);
    }

    public function filterByProgram($school_code,$progNo){
        $courses=managecoursesResourcecontroller::collection(
              DB::table("tblsubject")
              ->where("school_code", $school_code)
              ->where("prog", $progNo)
              ->where('deleted', '0')
              ->get()
        );
        return response()->json([
          "data"=>$courses
        ]);
    }
    
    //deleting course
    public function delete($coursecode, $school_code){
          
    $course=DB::table('tblsubject')
    ->where('subcode',$coursecode)
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
    public function add(Request $request){
        $validator=validator($request->all(),
        [
         "subname"=>"required",
         "subcode"=>"required",
         "course_desc"=>"required",
         "credit"=>"required",
         "semester"=>"required"
        ],
        [
            "subname.required" =>"No course title provided",
            "subcode.required" =>"No course code provided", 
            "course_desc.required" =>"No course description provided",
            "credit.required"=>"No course credit provided",
            "semester.required"=>"No semester chosen "
        ]
    );
    if($validator->fails()){
        return response()->json([
          "ok"=>false,
          "msg"=>"Adding course failed".join('.', $validator->errors()->all())
        ]);
    }
    //checking if staff already exists.
    $checkcourse=DB::table('tblsubject')
    ->where('school_code', $request->school_code)
    ->where('subname', $request->subname)
    ->get()
    ->count();
    if ($checkcourse>0) {
        return response()->json([
            "ok" => false,
            "msg" => "Adding course failed.Course already exists"
            
        ]);
    }
    try {
        $transactionResult=DB::transaction(function () use ($request){
            
            DB::table('tblsubject')->insert([
                "transid"=>strtoupper(strtoupper(bin2hex(random_bytes(5)))),
                "school_code"=>$request->school_code,
                "branch_code"=>null,
                "subname"=>$request->subname,
                "subcode"=>$request->subcode,
                "course_desc"=>$request->course_desc,
                "credit"=>$request->credit,
                "semester"=>$request->semester,
                "source"=>null,
                "export"=>null,
                "deleted"=>"0",
                'createuser' =>  $request->school_name,
               'createdate' => date('Y-m-d'),
               'modifyuser' => $request->school_name,
               'modifydate' => date('Y-m-d'),
            ]);

        });
        if (!empty($transactionResult)) {
            throw new Exception($transactionResult);
        }
        return response()->json([
            "ok"=>true,
            "msg"=>"course successfully added"
        ]);
        
    } catch (\Throwable $e) {
        Log::error("Failed adding course: " . $e->getMessage());
        return response()->json([
            "ok" => false,
            "msg" => "Adding course failed!",
            
        ]);
    }
    }
    //updating courses
    public function update(Request $request){
        $validator=validator($request->all(),
        [
         "subname"=>"required",
         "subcode"=>"required",
         "course_desc"=>"required",
         "credit"=>"required",
         "semester"=>"required"
        ],
        [
            "subname.required" =>"No course title provided",
            "subcode.required" =>"No course code provided", 
            "course_desc.required" =>"No course description provided",
            "credit.required"=>"No course credit provided",
            "semester.required"=>"No semester chosen "
        ]
    );
    if($validator->fails()){
        return response()->json([
          "ok"=>false,
          "msg"=>"Updating course failed".join('.', $validator->errors()->all())
        ]);
    }
    //checking if staff already exists.
    $checkcourse=DB::table('tblsubject')
    ->where('school_code', $request->school_code)
    ->where('subname', $request->subname)
    ->get()
    ->count();
    if ($checkcourse>0) {
        return response()->json([
            "ok" => false,
            "msg" => "Adding course failed.Course already exists"
            
        ]);
       
    }
    $updatingCourse=DB::table('tblsubject')
    ->where('subcode',$request->subcode )
    ->where('school_code', $request->school_code) 
   ->where('deleted', '0');
   
    if (empty($updatingCourse)) {
        return response()->json([
            "ok" => false,
            "msg" => "updating course failed!",
            
            
            
        ]);
    }
    try {
        $transactionResult=DB::transaction(function () use ($request, $updatingCourse ){
            
            $updatingCourse->update([
                
                "school_code"=>$request->school_code,
                "subname"=>$request->subname,
                "subcode"=>$request->subcode,
                "course_desc"=>$request->course_desc,
                "credit"=>$request->credit,
                "semester"=>$request->semester,
                
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
    public function fetch_students($school_code, $coursecode){
       $studentLIST=courseRegisteredStuentsResourceController::collection(
        DB::table('tblgrade')
        ->select("tblgrade.acyear", "tblgrade.semester","tblstudent.fname", "tblstudent.lname")
        ->join('tblstudent','tblstudent.student_no',"=", "tblgrade.grade_code")
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
     public function filtercourse($studentprog, $school_code){
        $courses=DB::table('tblsubject')
         ->where('school_code', $school_code)
        ->where('prog', $studentprog)
        ->where('deleted','0')
        //->where('student_code', $student_no)
        ->get();
        return response()->json([
              "ok"=>true,
              "data"=>$courses
        ]);
    }
}
