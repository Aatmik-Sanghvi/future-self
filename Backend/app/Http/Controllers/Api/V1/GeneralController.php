<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Api\ResponseController;
use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\User;
use App\Services\ValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class GeneralController extends ResponseController
{
    protected $user, $feedback;

    public function __construct(ValidationService $validationService){
        $this->user = new User;
        $this->feedback = new Feedback;
        $this->validationService = $validationService;
    }

    public function profile(){
        
        return ResponseHelper::send(200, 'Your profile had been got successfully.', $this->get_user_data());
    }

    public function updateProfile(Request $request){
        $userId = auth()->id();
        $rules = [
            'name' => $this->validationService->loginInputRules(),
            'email' => $this->validationService->emailUniqueRules($userId),
            'mobile' => $this->validationService->mobileOnlyRules(),
            'country_code' => 'nullable|string|max:10',
        ];

        if ($request->hasFile('profile_image')) {
            $rules['profile_image'] = 'image|mimes:jpeg,png,jpg,gif,webp|max:2048';
        }

        $request->validate($rules);

        $user = auth()->user();
        $data = $request->only(['name', 'email', 'country_code', 'mobile']);

        if ($request->hasFile('profile_image')) {
            $rawProfileImage = $user->getRawOriginal('profile_image');
            if ($rawProfileImage && Storage::disk(config('constants.upload_type', 'public'))->exists($rawProfileImage)) {
                Storage::disk(config('constants.upload_type', 'public'))->delete($rawProfileImage);
            }
            $data['profile_image'] = upload_file('profile_image', 'profile_images');
        }

        $user->update($data);
        return ResponseHelper::send(200, 'Profile has been updated successfully.', $this->get_user_data());
    }

    public function updatePassword(Request $request){
        $request->validate([
            'old_password' => $this->validationService->passwordRules(),
            'new_password' => $this->validationService->passwordRules(),
            'confirm_password' => 'same:new_password',
        ], array_merge(
            $this->validationService->passwordMessages('old_password'),
            $this->validationService->passwordMessages('new_password')
        ));

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

    public function stats(){
        try {
            $users_count = $this->user->count();
            $messages_count = DB::table('agent_conversation_messages')
                ->where('role', 'user')
                ->count();
            $app_rating = $this->feedback->avg('helpful_rating') ?? 0;

            $total_feedbacks = $this->feedback->count();
            $recommend_to_other = 0;
            if ($total_feedbacks > 0) {
                $promoters = $this->feedback->where('nps_score', '>=', 7)->count();
                $recommend_to_other = round(($promoters / $total_feedbacks) * 100);
            }

            return ResponseHelper::send(200, 'Stats fetched successfully.', [
                'users_count' => $users_count,
                'messages_count' => $messages_count,
                'app_rating' => round($app_rating, 1),
                'recommend_to_other' => $recommend_to_other,
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::send(500, 'Failed to fetch stats.');
        }
    }
}
