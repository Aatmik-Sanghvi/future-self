<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Api\ResponseController;
use App\Models\UserEmailLog;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\ValidationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GuestController extends ResponseController
{
    protected $user, $validationService;

    public function __construct()
    {
        $this->user = new User;
        $this->validationService = new ValidationService;
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => $this->validationService->loginInputRules(),
            'email' => $this->validationService->emailUniqueRules(),
            'mobile' => $this->validationService->mobileOnlyRules(),
            'password' => $this->validationService->passwordRules(),
        ]);

        $request['daily_limit'] = config('constants.message_limit');
        $user = $this->user->create($request->all());
        Auth::login($user);

        $token = $user->createToken('api-token')->plainTextToken;

        return ResponseHelper::send(200, 'User registered successfully', $this->get_user_data($token));
    }

    public function login(Request $request){
        $request->validate([
            'email' => $this->validationService->emailExistsRules(),
            'password' => $this->validationService->passwordRules()
        ]);

        $checkUser = User::where('email',$request->email)->first();

        if(!$checkUser || !Hash::check($request->password, $checkUser->password)){
            return ResponseHelper::send(401, 'User is not authorized to login.');
        }
        
        Auth::login($checkUser);

        // Create token
        $token = $checkUser->createToken('api-token')->plainTextToken;
        
        return ResponseHelper::send(200, 'User logged in successfully.',$this->get_user_data($token));
    }

    public function forgotPassword(Request $request){
        $request->validate([
            'email' => $this->validationService->emailExistsRules()
        ]);

        $user = $this->user->where('email', $request->email)->first();

        register_user_email($user->email, 'Forgot password');

        return ResponseHelper::send(200, 'Password reset link sent successfully.');
    }

    public function verifyOtp(Request $request){
        $request->validate([
            'email' => $this->validationService->emailExistsRules(),
            'otp'   => ['required', 'digits:4'],
        ]);

        $emailLog = UserEmailLog::where('email', $request->email)->first();

        if (!$emailLog) {
            return ResponseHelper::send(412, 'No OTP request found for this email.');
        }

        // Check if OTP has expired
        if ($emailLog->otp_expires_at && now()->gt($emailLog->otp_expires_at)) {
            return ResponseHelper::send(412, 'OTP has expired. Please request a new one.');
        }

        // Check if OTP matches
        if ((string) $emailLog->otp !== (string) $request->otp) {
            return ResponseHelper::send(412, 'Invalid OTP. Please try again.');
        }

        // OTP is valid — generate a temporary reset token
        $resetToken = Str::random(64);
        Cache::put('password_reset_' . $request->email, $resetToken, now()->addMinutes(10));

        // Clear the OTP so it can't be reused
        $emailLog->update(['otp' => null, 'otp_expires_at' => null]);

        return ResponseHelper::send(200, 'OTP verified successfully.', [
            'reset_token' => $resetToken,
        ]);
    }

    public function resetPassword(Request $request){
        $request->validate([
            'email'                 => $this->validationService->emailExistsRules(),
            'password'              => $this->validationService->passwordRules(),
            'password_confirmation' => ['required', 'same:password'],
            'reset_token'           => ['required', 'string'],
        ]);

        // Verify the reset token from cache
        $cachedToken = Cache::get('password_reset_' . $request->email);

        if (!$cachedToken || $cachedToken !== $request->reset_token) {
            return ResponseHelper::send(412, 'Invalid or expired reset token. Please restart the process.');
        }

        // Update the user's password
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => $request->password,
        ]);

        // Remove the reset token from cache
        Cache::forget('password_reset_' . $request->email);

        return ResponseHelper::send(200, 'Password reset successfully.');
    }
}
