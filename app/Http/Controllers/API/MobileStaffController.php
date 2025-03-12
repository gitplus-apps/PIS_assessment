<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileStaffController extends Controller
{
    public function allStaffCourses($StaffCode)
    {
        $courses = DB::table('tblsubject')->select('tblsubject.*')
        ->join('tblsubject_assignment', "tblsubject_assignment.subcode", 'tblsubject.subcode')
        ->where('tblsubject.deleted', '0')
        ->where('tblsubject_assignment.deleted', '0')
        ->where('tblsubject_assignment.staffno', $StaffCode)
        ->get()->toArray();

        return response()->json([
            "ok" => true,
            "msg" => "Request successful",
            "data" => $courses
        ]);
    }
}
