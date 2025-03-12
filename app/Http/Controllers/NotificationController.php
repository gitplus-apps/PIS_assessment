<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function index()
{
    $user = Auth::user();
    $school_code = $user->school_code;

    $currentDateTime = Carbon::now()->timezone('Africa/Accra')->format('Y-m-d H:i:s');

DB::table('tblnotice')
    ->where('date_end', '<', $currentDateTime)
    ->update(['deleted' => 1]);

DB::table('tblnotice')
    ->where('date_start', '>', $currentDateTime)
    ->update(['deleted' => 1]);

DB::table('tblnotice')
    ->where('date_start', '<=', $currentDateTime)
    ->where('date_end', '>=', $currentDateTime)
    ->update(['deleted' => 0]);


    if ($user->usertype === 'STA') {
        // Staff should see notices meant for "Staff" or "All"
        $notices = DB::table('tblnotice')
            ->select('transid', 'notice_title', 'notice_details', 'date_posted')
            ->where('school_code', $school_code)
            ->where(function ($query) {
                $query->where('notice_recipient', 'Staff')
                      ->orWhere('notice_recipient', 'All');
            })
            ->where('deleted', '0')
            ->orderBy('date_posted', 'desc')
            ->get();
    } else {
        // Students should see only their assigned notices
        $student_code = $user->student_code;

        $notices = DB::table('tblnotice')
            ->select('tblnotice.transid', 'tblnotice.notice_title', 'tblnotice.notice_details', 'tblnotice.date_posted')
            ->where('tblnotice.school_code', $school_code)
            ->where('tblnotice.deleted', '0')
            ->where(function ($query) use ($student_code) {
                $query->where('tblnotice.notice_recipient', 'Students')
                      ->orWhere('tblnotice.notice_recipient', 'All')
                      ->orWhereExists(function ($subquery) use ($student_code) {
                          $subquery->select(DB::raw(1))
                              ->from('tblgrade')
                              ->whereRaw('tblsubject.subcode = tblnotice.course_recipient');
                            //   ->where('tblgrade.grade_code', $student_code);
                      });
            })
            ->orderBy('tblnotice.date_posted', 'desc')
            ->get();
    }

    return view('modules.notifications.index', compact('notices'));
}

    
}
