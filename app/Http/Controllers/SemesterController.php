<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\semester;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SemesterController extends Controller
{
    public function index()
    {
        $semesteres = Semester::where('deleted', '!=', '1')->get();
        return view('modules.semester.index', compact('semesteres'));
    }

    public function store(Request $request)
    {
        try{
        $request->validate(['sem_desc' => 'required|string|max:50']);
        $schoolCode = Auth::user()->school->school_code;

        // semester::create([
            DB::table("tblsemester")->insert([
            'transid' => Str::uuid(),
            //'school_code' => $schoolCode, 
            'sem_code' => Semester::generateSemesterCode(),
            'sem_desc' => $request->sem_desc,
            'createuser' => Auth::user()->name ?? 'Admin',
            'createdate' => now(),
            'deleted' => '0'
        ]);
        
        return response()->json([
            "success" => true,
            "msg" => "Semester created successfully!",
        ]);
    }catch (\Exception $e) {
        return response()->json([
            "ok" => false,
            "msg" => "An internal error occurred. Please try again.",
            "error" => $e->getMessage(),
        ]);
    }
}



    public function update(Request $request, $id)
    {
        $request->validate(['sem_desc' => 'required|string|max:50']);

        $semester = Semester::findOrFail($id);
        $semester->update([
            'sem_desc' => $request->sem_desc,
            'modifyuser' => Auth::user()->name ?? 'Admin',
            'modifydate' => now(),
        ]);

        return response()->json(['success' => 'Semester updated successfully!']);
    }

    public function delete($id)
    {
        DB::table('tblsemester')->where('transid', $id)->update(['deleted' => '1']);
        return response()->json(['success' => true]);
    }
}
