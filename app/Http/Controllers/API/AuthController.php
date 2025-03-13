<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Gitplus\Arkesel as Sms;
use App\Mail\ForgotPasswordMail;
use App\Models\Program;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function login(Request $request)
    {

        // Validate the request
        $validator = Validator::make(
            $request->all(),
            [
        "email" => ["required", function ($attribute, $value, $fails) {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL) && !preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
                $fails("The $attribute must be a valid email or a user ID.");
            }
        }],
        "password" => "required",
    ]

        );

        // Payload to be sent with response
        $payload = [
            "ok" => false,
        ];

        // If validation fails, return error response
        if ($validator->fails()) {
            $payload["msg"] = "Login failed. No email/user ID or password";
            $payload["error"] = [
                "msg" => join(" ", $validator->errors()->all()),
                "fix" => "Kindly fix the above errors",
            ];
            return response()->json($payload, 400); // Return HTTP 400 for bad request
        }

        // Fetch the user by either email or user ID
        $authenticatedUser = User::where(function ($query) use ($request) {
            $query->where("email", $request->email) // Check for email
                  ->orWhere("userid", strtoupper($request->email)); // Check for user ID
        })
        ->where("deleted", "0")
        ->first();

        // Return if no user is found
        if (empty($authenticatedUser)) {
            $payload["msg"] = "Login failed. Wrong email/user ID or password";
            return response()->json($payload, 401); // Return HTTP 401 for unauthorized
        }

        // Return if password is invalid
        if (!Hash::check($request->password, $authenticatedUser->password)) {
            $payload["msg"] = "Login failed. Wrong email/user ID or password";
            return response()->json($payload, 401); // Return HTTP 401 for unauthorized
        }

        // Fetch school details
        $schoolDetails = [
            "name" => $authenticatedUser->school->school_name,
            "logo" => $authenticatedUser->school->logo,
            "long" => $authenticatedUser->school->longitude,
            "lat" => $authenticatedUser->school->latitude,
            "distance" => $authenticatedUser->school->distance,
            "streetAddress" => $authenticatedUser->school->street_address,
            "PostalAddress" => $authenticatedUser->school->postal_address,
            "email" => $authenticatedUser->school->email,
            "phone" => $authenticatedUser->school->phone_main,
            "motto" => $authenticatedUser->school->school_motto,
            "schoolType" => $authenticatedUser->school->type,
            "schoolPrefix" => $authenticatedUser->school->school_prefix,
        ];

        // Determine the usertype to know the appropriate data to send back
        $usertype = strtoupper($authenticatedUser->usertype);

        switch ($usertype) {
            case "STU":
                $student = Student::where("student_no", $authenticatedUser->userid)
                    ->where("deleted", "0")
                    ->first();

                // Make sure the student exists in the system. If not, return an error response
                if (empty($student)) {
                    $payload["msg"] = "Login failed. Unknown student, please try again later";
                    $payload["error"] = [
                        "msg" => "Details of this user were not found. Perhaps this student does not exist or has been deleted",
                        "fix" => "Check the system to correct such errors",
                    ];
                    return response()->json($payload, 404); // Return HTTP 404 for not found
                }

                $programDetails = Program::where("prog_code", $student->prog)
                    ->where("deleted", "0")
                    ->first();
                $semester = DB::table("tblsemester")->where("deleted", 0)->get();
                $payload["ok"] = true;
                $payload["msg"] = "Login successful";
                $payload["data"] = [
                    "usertype" => $usertype,
                    "user" => $student,
                    "sch" => $schoolDetails,
                    "program" => $programDetails,
                    "semester" => $semester,
                ];
                break;

            case "STA":
                $staff = Staff::where("email", $authenticatedUser->email)
                    ->where("deleted", "0")
                    ->first();

                if (empty($staff)) {
                    $payload["msg"] = "Login failed. Unknown staff, report this to your school if this keeps happening";
                    $payload["error"] = [
                        "msg" => "Details of this staff were not found. Perhaps this staff does not exist or has been deleted",
                        "fix" => "Check the system to correct such errors",
                    ];
                    return response()->json($payload, 404); // Return HTTP 404 for not found
                }

                $assignedClasses = DB::table("tblclass")
                    ->join("tblclass_teacher", "tblclass.class_code", "=", "tblclass_teacher.class_code")
                    ->select("tblclass_teacher.class_code", "tblclass.class_desc")
                    ->where("tblclass_teacher.school_code", "=", $staff->school_code)
                    ->where("tblclass.school_code", "=", $staff->school_code)
                    ->where("tblclass_teacher.staff_no", "=", $staff->staffno)
                    ->where("tblclass_teacher.deleted", "0")
                    ->where("tblclass.deleted", "0")
                    ->get();

                $classes = [];
                foreach ($assignedClasses->toArray() as $class) {
                    $classes[] = $class;
                }

                $subjects = DB::table("tblstaff")
                    ->join("tblsubject_assignment", "tblsubject_assignment.staffno", "=", "tblstaff.staffno")
                    ->join("tblsubject", "tblsubject.subcode", "=", "tblsubject_assignment.subcode")
                    ->where("tblstaff.staffno", "=", $staff->staffno)
                    ->where("tblstaff.deleted", "=", "0")
                    ->select(
                        DB::raw("CONCAT_WS(' ', tblstaff.fname, tblstaff.mname, tblstaff.lname) as staff"),
                        "tblstaff.phone",
                        "tblstaff.email",
                        "tblsubject_assignment.subcode",
                        "tblsubject.subname"
                    )->get();

                $payload["ok"] = true;
                $payload["msg"] = "Login successful";
                $payload["data"] = [
                    "usertype" => $usertype,
                    "user" => $staff,
                    "sch" => $schoolDetails,
                    "classes" => $classes,
                    "subjects" => $subjects,
                ];
                break;

            default:
                $payload["msg"] = "Login failed. An internal error occurred. Report this to your school if this keeps happening";
                return response()->json($payload, 500); // Return HTTP 500 for server error
        }

        return response()->json($payload, 200); // Return HTTP 200 for success

        // $validator = Validator::make(
        //     $request->all(),
        //     [
        //         "email" => "required",
        //         "password" => "required",
        //     ]
        // );
        //
        // // Payload to be sent with response
        // $payload = [
        //     "ok" => false,
        // ];
        //
        // if ($validator->fails()) {
        //     $payload["msg"] = "Login failed. No user id or password";
        //     $payload["error"] = [
        //         "msg" => join(" ", $validator->errors()->all()),
        //         "fix" => "Kindly fix the above errors",
        //     ];
        //     return response($payload);
        // }
        //
        // $authenticatedUser = User::where("userid", strtoupper($request->userid))
        //     ->where("deleted", "0")
        //     ->first();
        //
        //
        // // Return if no user is found
        // if (empty($authenticatedUser)) {
        //     $payload["msg"] = "Login failed. Wrong userid or password";
        //     return response()->json($payload);
        // }
        //
        // // Return if password is invalid
        // if (!Hash::check($request->password, $authenticatedUser->password)) {
        //     $payload["msg"] = "Login failed. Wrong userid or password";
        //     return response()->json($payload);
        // }
        //
        //
        // // The assumption here is that a parent will always have his/her children
        // // in the same school. So when the parent logs in, we get the details of
        // // the school and add it to the response.
        //
        // // $school = DB::table("tblacyear")
        // //     ->where([
        // //         "school_code" => $authenticatedUser->school_code,
        // //         "current_term" => "1",
        // //     ])->first();
        //
        // $schoolDetails = [
        //     "name" => $authenticatedUser->school->school_name,
        //     // "term" => $school->acterm,
        //     // "year" => $school->acyear_desc,
        //     "logo" => $authenticatedUser->school->logo,
        //     "long" => $authenticatedUser->school->longitude,
        //     "lat" => $authenticatedUser->school->latitude,
        //     "distance" => $authenticatedUser->school->distance,
        //     "streetAddress" => $authenticatedUser->school->street_address,
        //     "PostalAddress" => $authenticatedUser->school->postal_address,
        //     "email" => $authenticatedUser->school->email,
        //     "phone" => $authenticatedUser->school->phone_main,
        //     "motto" => $authenticatedUser->school->school_motto,
        //     "schoolType" => $authenticatedUser->school->type,
        //     "schoolPrefix" => $authenticatedUser->school->school_prefix,
        // ];
        //
        // // Determine the usertype to know the appropriate data to send back
        // $usertype = strtoupper($authenticatedUser->usertype);
        //
        // switch ($usertype) {
        //     case "STU":
        //         $student = Student::where("student_no", $authenticatedUser->userid)
        //             ->where("deleted", "0")
        //             ->first();
        //
        //
        //         // Make sure the student exists in the system. If not return an error response
        //         if (empty($student)) {
        //             $payload["msg"] = "Login failed. Unknown student, please try again later";
        //             $payload["error"] = [
        //                 "msg" => "Details of this user was not found. Perhaps this student does not exist or has been deleted",
        //                 "fix" => "Check the system to correct such errors",
        //             ];
        //             return response($payload);
        //         }
        //
        //         $programDetails = Program::where("prog_code", $student->prog)
        //             ->where("deleted", "0")
        //             ->first();
        //         $semester = DB::table("tblsemester")->where("deleted", 0)->get();
        //         $payload["ok"] = true;
        //         $payload["msg"] = "Login successful";
        //         $payload["data"] = [
        //             "usertype" => $usertype,
        //             "user" => $student,
        //             "sch" => $schoolDetails,
        //             "program" => $programDetails,
        //             "semester" => $semester,
        //         ];
        //         break;
        //
        //
        //     case "STA":
        //         $staff = Staff::where("email", $authenticatedUser->email)
        //             ->where("deleted", "0")
        //             ->first();
        //
        //         if (empty($staff)) {
        //             $payload["msg"] = "Login failed. Unknown staff, report this to your school if this keeps happening";
        //             $payload["error"] = [
        //                 "msg" => "Details of this staff was not found. Perhaps this staff does not exist or has been deleted",
        //                 "fix" => "Check the system to correct such errors",
        //             ];
        //             return response($payload);
        //         }
        //
        //         "select ct.class_code, c.class_desc from tblclass_teacher ct
        //         join tblclass c on c.class_code = ct.class_code
        //         where  ct.school_code = '1000001'
        //         and  ct.staff_no = 'S001'
        //         and  ct.acyear = '2019/2020'";
        //
        //         $assignedClasses = DB::table("tblclass")
        //             ->join("tblclass_teacher", "tblclass.class_code", "=", "tblclass_teacher.class_code")
        //             ->select("tblclass_teacher.class_code", "tblclass.class_desc")
        //             ->where("tblclass_teacher.school_code", "=", $staff->school_code)
        //             ->where("tblclass.school_code", "=", $staff->school_code)
        //             ->where("tblclass_teacher.staff_no", "=", $staff->staffno)
        //             // ->where("tblclass_teacher.acyear", "=", $school->acyear_desc)
        //             ->where("tblclass_teacher.deleted", "0")
        //             ->where("tblclass.deleted", "0")
        //             // ->where("tblclass_teacher.term", "=", $school->acterm)
        //             ->get();
        //
        //         $classes =  [];
        //         foreach ($assignedClasses->toArray() as $class) {
        //             $classes[] = $class;
        //         }
        //
        //         $subjects = DB::table("tblstaff")
        //             ->join("tblsubject_assignment", "tblsubject_assignment.staffno", "=", "tblstaff.staffno")
        //             ->join("tblsubject", "tblsubject.subcode", "=", "tblsubject_assignment.subcode")
        //             ->where("tblstaff.staffno", "=", $staff->staffno)
        //             ->where("tblstaff.deleted", "=", "0")
        //             // ->where("tblsubject_assignment.acyear", "=", $school->acyear_desc)
        //             // ->where("tblsubject_assignment.semester", "=", $school->acterm)
        //             ->select(
        //                 DB::raw("CONCAT_WS(' ', tblstaff.fname, tblstaff.mname, tblstaff.lname) as staff"),
        //                 "tblstaff.phone",
        //                 "tblstaff.email",
        //                 // "tblsubject_assignment.class_code",
        //                 "tblsubject_assignment.subcode",
        //                 "tblsubject.subname"
        //             )->get();
        //
        //
        //         $payload["ok"] = true;
        //         $payload["msg"] = "Login successful";
        //         $payload["data"] = [
        //             "usertype" => $usertype,
        //             "user" => $staff,
        //             "sch" => $schoolDetails,
        //             "classes" => $classes,
        //             "subjects" => $subjects,
        //         ];
        //         break;
        //     default:
        //         $payload["msg"] = "Login failed. An internal error occured. Report this to your school if this keeps happening";
        // }
        //
        // return response($payload);
    }


    public function passwordReset(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                "password" => "required|confirmed|min:8",
                "current_password" => "required",
                "email" => "required",
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                "ok" => false,
                "msg" => "Reset failed. " . join(" ", $validator->errors()->all()),
            ], 400);
        }

        $authenticatedUser = User::where("email", strtoupper($request->email))
            ->where("deleted", "0")
            ->first();
        // Return if user not found
        if (empty($authenticatedUser)) {
            return response()->json([
                "ok" => false,
                "msg" => "Sorry account not found"
            ], 400);
        }


        // Return if old password is invalid
        if (!Hash::check($request->current_password, $authenticatedUser->password)) {
            return response()->json([
                "ok" => false,
                "msg" => "Sorry your current password is incorrect"
            ], 400);
        }

        //create new password
        $password = Hash::make($request->password);


        //update new password with the authenticated user
        try {
            $authenticatedUser->update([
                'password' => $password,
                'modifydate' => date("Y-m-d"),
                'modifyuser' => $authenticatedUser->email,
            ]);

            return response()->json([
                "ok" => true,
                "msg" => "Password successfully changed",
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                "ok" => false,
                "msg" => "An internal error occured. Reset failed",
            ], 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                "email" => "required",
            ]
        );
        // Payload to be sent with response
        $payload = [
            "ok" => false,
        ];
        if ($validator->fails()) {
            $payload["msg"] = "Reset failed. No email provided";
            $payload["error"] = [
                "msg" => join(" ", $validator->errors()->all()),
                "fix" => "Kindly fix the above errors",
            ];
            return response($payload);
        }

        //Fetch user details based on email provided
        $authenticatedUser = User::where("email", strtoupper($request->email))
            ->where("deleted", "0")
            ->first();

        // Return if no user is found
        if (empty($authenticatedUser)) {
            $payload["msg"] = "Sorry account not found";
            return response()->json($payload);
        }

        //create new password
        $random = uniqid(); //TODO: Send generated password via email to user
        $password = password_hash($random, PASSWORD_DEFAULT);


        //update new password with the authenticated user
        try {
            $authenticatedUser->update([
                'password' => $password,
                'modifydate' => date("Y-m-d"),
                'modifyuser' => $authenticatedUser->email,
            ]);

            // $authenticatedUser->newPassword = $random;

            // event(new PasswordReset($authenticatedUser));

            $msg = " Here is your new password: {$random}";

            $newsms = new Sms(env('ARKESEL_SMS_SENDER_ID'), env('ARKESEL_SMS_API_KEY'));

            $response = $newsms->send($authenticatedUser->phone, $msg);

            $receipt = [

                "password" => $random,
            ];

            Mail::to($request->email)->send(new ForgotPasswordMail($receipt));

            return response()->json([
                "ok" => true,
                "msg" => "Reset successful.",
            ]);
        } catch (\Exception $th) {
            return response()->json([
                "ok" => false,
                "msg" => "An internal error occured. Reset failed",
                "error" => [
                    "msg" => $th->__toString(),
                    "fix" => "Error is explained in fix",
                ]
            ]);
        }
    }
}
