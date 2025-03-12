<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Resources\Staff\StaffStudentResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffStudentController extends Controller
{
    public function index($courseCode)
    {
        // return $courseCode;
        $student = DB::table("tblgrade")->select(
            "tblstudent.*",
            "tblgrade.semester",
            "tblsubject.subname",
            "tblsubject.subcode"
        )   
            ->join("tblstudent", "tblstudent.current_grade", "tblgrade.grade_code")
            ->join("tblsubject", "tblsubject.subcode", "tblsubject.subcode")
            ->where("tblsubject.subcode", $courseCode)
            ->where("tblstudent.deleted", 0)
            ->where("tblsubject.deleted", 0)
            ->where("tblgrade.deleted", 0)
            ->get();

        return response()->json([
            "data1" => $student,
            "data" => StaffStudentResource::collection($student)
        ]);
    }
}
