<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class AssLoginController extends Controller
{
    public function login(Request $request)
    {
         Log::info('Login attempt received', [
        'email' => $request->email,
        'all_data' => $request->all()
    ]);

        $validator = Validator::make($request->all(), [
            "email" => "required",
            "password" => "required",
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $authenticatedUser = User::where(function ($query) use ($request) {
            $query->where('userid', $request->email)
                  ->orWhere('email', strtolower($request->email));
        })
        ->where("deleted", "0")
        ->first();

        if ($authenticatedUser) {
        Log::info('User found', [
            'user_id' => $authenticatedUser->id,
            'email' => $authenticatedUser->email,
            'userid' => $authenticatedUser->userid
        ]);

        // Check password
        if ($request->password == $authenticatedUser->password) {
            Log::info('Password matched, redirecting to home');
            $request->session()->put('loggedinuser', $authenticatedUser->email);
            return redirect('/');
        }
    } else {
            return back()->with('fail', 'Hey, sorry, this email is not registered, or we have to block you at this time! Too many attempts. Please contact your administrator!');
        }
    }
}
