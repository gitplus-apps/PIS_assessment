<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Mail\StaffMessageCentreEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class StaffMessageController extends Controller
{
    public function sendEmail(Request $request)
    {
        // return $request->all();
        $recipients = [];

        if (!empty($request->student)) {
            switch ($request->student) {
                case 'all_students':
                    $newCourses = DB::table('tblsubject')->select('tblsubject.subcode')
                        ->join('tblsubject_assignment', "tblsubject_assignment.subcode", 'tblsubject.subcode')
                        ->where('tblsubject.deleted', '0')
                        ->where('tblsubject_assignment.deleted', '0')
                        ->where('tblsubject_assignment.staffno', $request->staff_code)
                        ->get()->toArray();

                    $courseCode = [];
                    foreach ($newCourses as  $value) {
                        $courseCode[] = $value->subcode;
                    }

                    $students = DB::table("tblgrade")->select(
                        "tblstudent.*",
                    )
                        ->join("tblstudent", "tblstudent.current_grade", "tblgrade.grade_code")
                        ->join("tblsubject", "tblsubject.subcode", "tblsubject.subcode")
                        ->whereIn("tblsubject.subcode", $courseCode)
                        ->where("tblstudent.deleted", 0)
                        ->where("tblsubject.deleted", 0)
                        ->where("tblgrade.deleted", 0)
                        ->get();

                    foreach ($students as $student) {
                        $recipients[] = $student->email;
                    }

                    $messageDetail = [
                        "subject" => $request->subject,
                        "body" => $request->email,
                    ];

                    if (!empty($recipients)) {
                        foreach ($recipients as $recipient) {
                            Mail::to($recipient)->send(new StaffMessageCentreEmail($messageDetail));
                        }
                    }


                    DB::table("tblemail_sent")->insert([
                        "transid" => strtoupper(bin2hex(random_bytes(5))),
                        "school_code" => $request->school_code,
                        "email_subject" => $request->subject,
                        "email_message" => $request->email,
                        "recipient" => 'All Students',
                        "deleted" => "0",
                        "createuser" => $request->createuser,
                        "createdate" => date("Y-m-d"),
                    ]);
                    return response()->json([
                        "ok" => true,
                        "msg" => "Email sent!",

                    ]);

                    break;

                default:
                    $student = DB::table("tblstudent")->select("email", "fname", "mname", "lname")
                        ->where("school_code", $request->school_code)
                        ->where("student_no", $request->student)
                        ->where("deleted", "0")
                        ->first();

                    $messageDetail = [
                        "subject" => $request->subject,
                        "body" => $request->email,
                    ];

                    if (!empty($student->email)) {
                        Mail::to($student->email)->send(new StaffMessageCentreEmail($messageDetail));
                    }


                    DB::table("tblemail_sent")->insert([
                        "transid" => strtoupper(bin2hex(random_bytes(5))),
                        "school_code" => $request->school_code,
                        "email_subject" => $request->subject,
                        "email_message" => $request->email,
                        "recipient" => $student->fname . '' . $student->mname . '' . $student->lname,
                        "deleted" => "0",
                        "createuser" => $request->createuser,
                        "createdate" => date("Y-m-d"),
                    ]);
                    return response()->json([
                        "ok" => true,
                        "msg" => "Email sent!",

                    ]);
                    break;
            }
        }
    }
}
