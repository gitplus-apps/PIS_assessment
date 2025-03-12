<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class StudentHomeworkController extends Controller
{
    public function index()
{
    $user = Auth::user();
    $school_code = $user->school_code;
    $student_code = $user->student_code;

$currentDateTime = Carbon::now()->timezone('Africa/Accra')->format('Y-m-d H:i:s');

DB::table('tblhomework')
    ->where('date_end', '<', $currentDateTime)
    ->update(['deleted' => 1]);

DB::table('tblhomework')
    ->where('date_start', '>', $currentDateTime)
    ->update(['deleted' => 1]);

DB::table('tblhomework')
    ->where('date_start', '<=', $currentDateTime)
    ->where('date_end', '>=', $currentDateTime)
    ->update(['deleted' => 0]);


    $studenthomeworks = DB::table('tblhomework')
    ->where('tblhomework.school_code', $school_code)
    ->where('tblhomework.deleted', '0')
    ->join('tblgrade', 'tblhomework.course_recipient', '=', 'tblsubject.subcode')
    ->leftJoin('tblsubmit_homework', function ($join) use ($user) {
        $join->on('tblhomework.transid', '=', 'tblsubmit_homework.transid')
             ->where('tblsubmit_homework.userid', '=', $user->userid);
    })
    ->where('tblgrade.grade_code', $user->userid)
    ->select('tblhomework.*', 'tblsubmit_homework.transid as submitted')
    ->distinct()
    ->orderBy('tblhomework.date_posted', 'desc')
    ->get();


return view('modules.studenthomework.index', compact('studenthomeworks'));
}


public function store(Request $request)
{
    $request->validate([
        'homework_title' => 'required|string|max:255',
        'subcode' => 'nullable|string',
        'file' => 'required|mimes:pdf,doc,docx,odt,jpg,png|max:2048'
    ]);

    try {
        $school_code = auth()->user()->school_code;
        $userid = auth()->user()->userid;
        $fname = auth()->user()->fname;
        $lname = auth()->user()->lname;
        $recipient_type = $request->recipient_type;
        $subcode = $request->subcode;
        $file = $request->file('file');
        $filePath = $file->store('document', 'public');
        $transid = $request->transid;

        DB::transaction(function () use ($school_code, $request, $recipient_type, $subcode, $filePath, $userid, $fname, $lname, $transid) {
            // Check if a record with the same transid already exists
            $existingHomework = DB::table('tblsubmit_homework')->where('transid', $transid)->first();

            if ($existingHomework) {
                // Delete the old record if it exists
                DB::table('tblsubmit_homework')->where('transid', $transid)->delete();
            }

            // Insert new homework into the database
            $homeworkData = [
                'transid' => $transid,
                'school_code' => $school_code,
                'userid' => $userid,
                'fname' => $fname,
                'lname' => $lname,
                'acyear' => date('Y'),
                'term' => '1',
                'homework_code' => strtoupper(uniqid('N')),
                'homework_type' => 'General',
                'homework_title' => $request->homework_title,
                'course_recipient' => $subcode ?? '',
                'file_path' => $filePath,
                'posted_by' => auth()->user()->id,
                'submit_to' => $request->submit_to,
                'date_posted' => now(),
                'deleted' => '0',
                'createuser' => auth()->user()->id,
                'createdate' => now(),
            ];

            DB::table('tblsubmit_homework')->insert($homeworkData);
        });

        return response()->json(['success' => true]);
    } catch (\Throwable $e) {
        Log::error("Failed adding homework: " . $e->getMessage());
        return response()->json([
            "ok" => false,
            "msg" => "Adding batch failed!",
            "error" => [
                "msg" => $e->__toString(),
                "err_msg" => $e->getMessage(),
                "fix" => "Please complete all required fields",
            ]
        ]);
    }
}

}
