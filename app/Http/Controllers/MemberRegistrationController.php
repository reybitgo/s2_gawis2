<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberRegistrationController extends Controller
{
    /**
     * Show the member registration form.
     */
    public function show()
    {
        $sponsor = Auth::user();

        return view('auth.register-member', [
            'sponsor' => $sponsor,
        ]);
    }

    /**
     * Register a new member with the logged-in user as default sponsor.
     */
    public function register(Request $request, CreateNewUser $creator)
    {
        $sponsor = Auth::user();

        // Get input data
        $input = $request->all();

        // If sponsor_name is empty, use logged-in user's username as default
        if (empty($input['sponsor_name'])) {
            $input['sponsor_name'] = $sponsor->username;
        }

        try {
            // Create the new user
            $newUser = $creator->create($input);

            // Build success message with HTML line breaks
            $message = "Member '{$newUser->fullname}' has been registered successfully!<br>Username: {$newUser->username}";

            // Add email verification notice if email was provided
            if (!empty($input['email'])) {
                $message .= "<br>A verification email has been sent to {$input['email']}.";
            }

            return redirect()
                ->route('member.register.show')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Registration failed: ' . $e->getMessage()]);
        }
    }
}
