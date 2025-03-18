<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AssLoginController extends Controller
{
    public function login(Request $request): RedirectResponse|Redirector
{
    $validator = Validator::make($request->all(), [
        'email' => 'required', // Can be email or username
        'password' => 'required',
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    // Attempt to find user by either email or userid
    $user = User::query()->where('email', strtolower($request->email))
                ->orWhere('userid', $request->email)
                ->where('deleted', '0')
                ->first();

    if (!$user) {
        return back()->with('fail', 'This email or username is not registered, or your account has been blocked. Contact your administrator.');
    }

    // Try authentication using both possibilities
    $credentials = [
        'password' => $request->password
    ];

    if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
        // If input is an email, authenticate with email
        $credentials['email'] = strtolower($request->email);
    } else {
        // Otherwise, authenticate with userid
        $credentials['userid'] = $request->email;
    }

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate(); // Prevent session fixation attacks

        Log::info('User logged in', [
            'user_id' => Auth::id(),
            'email' => Auth::user()->email ?? 'N/A',
            'userid' => Auth::user()->userid ?? 'N/A'
        ]);

        return redirect()->intended('/newassessment'); // Redirect to intended page
    }

    return back()->with('fail', 'Login failed. Wrong email/username or password');
}}

