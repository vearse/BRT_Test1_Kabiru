<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Api\BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class VerifyEmailController extends BaseController
{
    public function __invoke(Request $request)
    {
        // Validate the signed URL
        if (!URL::hasValidSignature($request)) {
            return $this->responseApi(false, __("Invalid verification link"), null, 400);
        }

        // Find user by ID from the signed URL
        $user = User::findOrFail($request->route('id'));

        // Check if email is already verified
        if ($user->hasVerifiedEmail()) {
            return $this->responseApi(true, __("Email has been verified"), null, 200);
            // return redirect()->intended('/dashboard?verified=1');
        }

        // Mark email as verified
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Log the user in
        Auth::login($user);

        return $this->responseApi(true, __("Email has been verified"), null, 200);
    }

    // Optional: Resend verification email
    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->responseApi(true, __("No user found with this email address"), null, 404);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->responseApi(false, __("No user found with this email address"), null, 400);
        }

        $user->sendEmailVerificationNotification();

        return $this->responseApi(true, __("'Verification email resent'"), null, 200);
    }
}
