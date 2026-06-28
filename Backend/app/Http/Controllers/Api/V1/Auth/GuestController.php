<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Api\ResponseController;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\ValidationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
}
