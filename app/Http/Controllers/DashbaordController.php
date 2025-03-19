<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClassBreakdownResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashbaordController extends Controller
{

public function fetchTotalStudentByProg($schoolCode)
{
    $total = DB::table("tblstudent")
        ->selectRaw("COUNT(student_no) as total_students, current_grade")
        ->where("school_code", $schoolCode)
        ->where("deleted", "0")
        ->groupBy("current_grade")
        ->get();

    return response()->json([
        "data" => $total,
    ]);
}




public function fetchClassBreakdown($schoolCode)
{
    $breakdowns = DB::table("tblstudent")
        ->select([
            "tblstudent.current_grade",
            DB::raw("SUM(CASE WHEN tblstudent.gender = 'M' THEN 1 ELSE 0 END) AS males"),
            DB::raw("SUM(CASE WHEN tblstudent.gender = 'F' THEN 1 ELSE 0 END) AS females"),
        ])
        ->where("tblstudent.deleted", "0")
        ->where("tblstudent.school_code", $schoolCode)
        ->groupBy("tblstudent.current_grade") // ✅ Group by grade only
        ->orderBy("tblstudent.current_grade")
        ->get();

    return response()->json([
        "ok" => true,
        "msg" => "Request successful",
        "data" => ClassBreakdownResource::collection($breakdowns),
    ]);
}
}
