<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Api\ResponseController;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class GeneralController extends ResponseController
{
    protected $user;

    public function __construct(ValidationService $validationService){
        $this->user = new User;
        $this->validationService = $validationService;
    }

    public function profile(){
        
        return ResponseHelper::send(200, 'Your profile had been got successfully.', $this->get_user_data());
    }

    public function updateProfile(Request $request){
        $request->validate([
            'name' => $this->validationService->loginInputRules(),
            'email' => $this->validationService->emailValidationRules(),
            'mobile' => $this->validationService->mobileOnlyRules(),
        ]);

        $this->user->whereId(auth()->id())->update($request->all());
        return ResponseHelper::send(200, 'Profile updated successfully.', $this->get_user_data());
    }

    public function updatePassword(Request $request){
        $request->validate([
            'old_password' => $this->validationService->passwordRules(),
            'new_password' => $this->validationService->passwordRules(),
            'confirm_password' => 'same:new_password',
        ]);

        if (!Hash::check($request->old_password, auth()->user()->password)) {
            return ResponseHelper::send(422, 'Incorrect old password.');
        }

        auth()->user()->password = $request->new_password;
        auth()->user()->save();

        return ResponseHelper::send(200, 'Password updated successfully.');
    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return ResponseHelper::send(200, 'Logged out successfully.');
    }

    public function deleteAccount(){
        auth()->user()->delete();
        return ResponseHelper::send(200, 'Account deleted successfully.');
    }
}
