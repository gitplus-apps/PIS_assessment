<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\homework;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class HomeworkController extends Controller
{
    public function index()
    {
        // Fetch all courses
        $courses = DB::table('tblsubject')->select('subcode', 'subname')->get();

        $homeworks = DB::table('tblhomework')
            ->where('deleted', '0') // Only show homeworks that are not deleted
            ->orderBy('date_posted', 'desc')
            ->get();

        $user = auth()->user();
        $school_code = $user->school_code;

         // Fetch homeworks created by the logged-in user
        $user_homeworks = DB::table('tblhomework')
        ->where('school_code', $school_code)
        ->where('posted_by', $user->id)
        ->where('deleted', '0')
        ->orderBy('date_posted', 'desc')
        ->get();

    
    $submitedhomeworks = DB::table('tblsubmit_homework')
        ->where('tblsubmit_homework.school_code', $school_code)
        ->where('tblsubmit_homework.deleted', '0')
        ->where('tblsubmit_homework.submit_to', $user->id)
        ->join('tblhomework', function ($join) {
            $join->on('tblsubmit_homework.submit_to', '=', 'tblhomework.posted_by');
        })
        ->select('tblsubmit_homework.*') // Select only homework fields
        ->distinct()
        ->orderBy('tblsubmit_homework.date_posted', 'desc')
        ->get();

        return view('modules.homework.index', compact('homeworks', 'courses', 'user_homeworks', 'submitedhomeworks'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'homework_title' => 'required|string|max:255',
            'homework_details' => 'required|string',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'recipient_type' => 'required|string',
            'subcode' => 'nullable|string',
            'file' => 'required|mimes:pdf,doc,docx,odt,jpg,png|max:2048'
        ]);
    
        try {
            $user = auth()->user();
            $school_code = $user->school_code;
            $recipient_type = $request->recipient_type;
            $subcode = $request->subcode;
            $file = $request->file('file');
            $filePath = $file->store('document', 'public');
    
            DB::transaction(function () use ($school_code, $request, $recipient_type, $subcode, $filePath, $user) {
                $homeworkData = [
                    'transid' => uniqid(),
                    'school_code' => $school_code,
                    'acyear' => date('Y'),
                    'term' => '1',
                    'homework_code' => strtoupper(uniqid('N')),
                    'homework_type' => 'General',
                    'homework_recipient' => $recipient_type,
                    'homework_title' => $request->homework_title,
                    'homework_details' => $request->homework_details,
                    'course_recipient' => $subcode ?? '',
                    'file_path' => $filePath,
                    'posted_by' => $user->id,
                    'date_posted' => now(),
                    'date_start' => $request->date_start,
                    'date_end' => $request->date_end,
                    'deleted' => '0',
                    'createuser' => $user->id,
                    'createdate' => now(),
                ];
    
                DB::table('tblhomework')->insert($homeworkData);
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
    


    public function edit($transid)
{
    $homework = DB::table('tblhomework')->where('transid', $transid)->first();

    if (!$homework) {
        return redirect()->route('homework.index')->with('error', 'homework not found.');
    }

    $courses = DB::table('tblsubject')->select('subcode', 'subname')->get();

    return view('modules.homework.modals.edit', compact('homework', 'courses'));
}




public function update(Request $request, $id)
{
    $request->validate([
        'homework_title' => 'required|string|max:255',
        'homework_details' => 'required|string',
        'date_start' => 'required|date',
        'date_end' => 'required|date|after_or_equal:date_start',
        'subcode' => 'nullable|string',
    ]);

    $user_id = auth()->user()->id;

    // Find the homework and ensure the logged-in user is the creator
    $homework = DB::table('tblhomework')->where('transid', $id)->where('posted_by', $user_id)->first();

    if (!$homework) {
        return redirect()->route('homework.index')->with('error', 'homework not found or access denied.');
    }

    // Update the homework
    DB::table('tblhomework')->where('transid', $id)->update([
        'homework_title' => $request->homework_title,
        'homework_details' => $request->homework_details,
        'course_recipient' => $request->subcode ?? '',
        'date_start' => $request->date_start,
        'date_end' => $request->date_end,
        'modifydate' => now(),
    ]);

    return redirect()->route('homework.index')->with('success', 'homework updated successfully.');
}


    // Delete homework
    public function delete($id)
    {
        DB::table('tblhomework')->where('transid', $id)->update(['deleted' => '1']);
        return response()->json(['success' => true]);
    }



    public function userhomeworks()
{
    $user = auth()->user();
    $school_code = $user->school_code;

    // Fetch all homeworks that are not deleted
    $homeworksQuery = DB::table('tblhomework')
        ->where('school_code', $school_code)
        ->where('deleted', '0')
        ->orderBy('date_posted', 'desc');

    if ($user->is_staff) {
        // Show homeworks meant for staff or everyone
        $homeworksQuery->whereIn('homework_recipient', ['All Staff', 'Everyone']);
    } else {
        // Show homeworks meant for students, everyone, or registered course
        $studentCourses = DB::table('tblgrade')
            ->where('student_code', $user->student_no)
            ->pluck('subcode');

        $homeworksQuery->where(function ($query) use ($studentCourses) {
            $query->whereIn('homework_recipient', ['All Students', 'Everyone'])
                  ->orWhereIn('course_recipient', $studentCourses);
        });
    }

    $homeworks = $homeworksQuery->get();

    return view('modules.homework.notifications', compact('homeworks'));
}

}
