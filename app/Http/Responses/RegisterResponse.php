<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $user = $request->user();

        // Build welcome message
        $message = "Welcome to " . config('app.name', 'Gawis iHerbal') . ", {$user->fullname}!<br>Your account has been created successfully.";

        // Add email verification notice if email was provided
        if ($user->email && !$user->hasVerifiedEmail()) {
            $message .= "<br>A verification email has been sent to {$user->email}.";
        }

        return $request->wantsJson()
                    ? new JsonResponse('', 201)
                    : redirect()->route('dashboard')->with('success', $message);
    }
}