<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class StaffRouteOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $modName = Request::route()->getName();

        // $currMod = DB::table("tblmodule")
        //     ->where('mod_url', "teacher/".strtolower($modName))
        //     ->first();

        // $permissions = DB::table("tbluser_module_privileges")
        //     ->where('userid', Auth::user()->email)
        //     ->where('mod_id', $currMod->mod_id)
        //     ->first();
        if (!Auth::check()) {
            Auth::logout();
            return redirect('login');
        }
        if (Auth::user()->usertype !== "STA") {
            Auth::logout();
            return redirect("error");
        }

        return $next($request);
    }

}
