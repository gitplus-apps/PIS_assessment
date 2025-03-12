<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::where('deleted', '!=', '1')->get();
        return view('modules.branch.index', compact('branches'));
    }

    public function store(Request $request)
    {
        try{
        $request->validate(['branch_desc' => 'required|string|max:50']);
        $schoolCode = Auth::user()->school->school_code;

        // Branch::create([
            DB::table("tblbranch")->insert([
            'transid' => Str::uuid(),
            'school_code' => $schoolCode, // Update with actual school code logic
            'branch_code' => Branch::generateBranchCode(),
            'branch_desc' => $request->branch_desc,
            'createuser' => Auth::user()->name ?? 'Admin',
            'createdate' => now(),
            'deleted' => '0'
        ]);
        
        return response()->json([
            "success" => true,
            "msg" => "Branch created successfully!",
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
        $request->validate(['branch_desc' => 'required|string|max:50']);

        $branch = Branch::findOrFail($id);
        $branch->update([
            'branch_desc' => $request->branch_desc,
            'modifyuser' => Auth::user()->name ?? 'Admin',
            'modifydate' => now(),
        ]);

        return response()->json(['success' => 'Branch updated successfully!']);
    }

    public function delete($id)
    {
        DB::table('tblbranch')->where('transid', $id)->update(['deleted' => '1']);
        return response()->json(['success' => true]);
    }
}
