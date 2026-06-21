<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use App\Services\ValidationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
// use Stripe\Stripe;
use Stripe\StripeClient;
use Illuminate\Http\Request;
use Stripe\Webhook;

class ResponseController extends Controller
{
    public $errors;

    protected $validationService;

    public function __construct(ValidationService $validationService)
    {
        $this->errors = null;
        $this->validationService = $validationService;
    }

    public function directValidation(array $rules, array $messages = [], $data = null)
    {
        $data = $data ?? request()->all(); // Use null coalescing to get data.
        $validator = Validator::make($data, $rules, $messages);

        // Check if validation fails
        if ($validator->fails()) {
            $this->errors = $validator->errors(); // Capture all error messages

            return $this->sendError(); // Call sendError to handle the response
        }

        // Return validated data if successful.
        return $validator->validated();
    }

    public function sendError($message = null, $array = true)
    {
        // Use the captured errors if any
        $message = $this->errors ? $this->errors->first() : ($message ?: __('api.error_something_went_wrong'));

        // Send the response with the error messages
        return ResponseHelper::send(412, $message, ($array) ? [] : new \stdClass);
    }

    public function sendResponse($status, $message, $result = null, $extra = null)
    {
        return ResponseHelper::send($status, $message, $result, $extra);
    }

    public function get_user_data($token = null)
    {
        // Retrieve only the required fields (signup fields) from the user
        $user_data = User::where('id', Auth::id())->Details()->first();

        return [
            'id' => $user_data->id,
            'name' => $user_data->name ?? '',
            'username' => $user_data->username ?? '',
            'email'=>$user_data->email ??'',
            'country_code' => $user_data->country_code ?? '',
            'mobile' => $user_data->mobile ?? '',
            'profile_image' => $user_data->profile_image ?? '',
            'token' => $token ?? request()->bearerToken() ?? '',
        ];
    }


    // public function plans(){
    //     $response = $this->planService->getPlan();
    //     $response['kyc_status'] = auth()->user()->kyc_status;
    //     $response['is_premium'] = auth()->user()->is_premium;
    //     $response['current_period_end'] = auth()->user()->latestSubscription->current_period_end ?? null;
    //     return $this->sendResponse(200, __('api.success'), $response);
    // }
}
