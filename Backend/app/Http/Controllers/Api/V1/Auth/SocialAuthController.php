<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Api\ResponseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends ResponseController
{
    /**
     * Redirect to Google OAuth provider.
     */
    public function redirectToGoogle(Request $request)
    {
        try {
            $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();

            if ($request->wantsJson()) {
                return ResponseHelper::send(200, 'Google redirect URL generated.', ['url' => $url]);
            }

            return redirect()->away($url);
        } catch (\Exception $e) {
            Log::error('Socialite Google Redirect Error: ' . $e->getMessage());
            if ($request->wantsJson()) {
                return ResponseHelper::send(500, 'Failed to initiate Google authentication: ' . $e->getMessage());
            }

            $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
            return redirect()->away($frontendUrl . '/login?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Handle callback from Google OAuth.
     */
    public function handleGoogleCallback(Request $request)
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            if (!$googleUser || !$googleUser->getEmail()) {
                return redirect()->away($frontendUrl . '/login?error=' . urlencode('Unable to retrieve email from Google profile.'));
            }

            // Check if user exists by google_id
            $user = User::where('google_id', $googleUser->getId())->first();

            if (!$user) {
                // Check if user exists by email
                $user = User::where('email', $googleUser->getEmail())->first();

                if ($user) {
                    // Link existing account to Google
                    $user->update([
                        'google_id'     => $googleUser->getId(),
                        'provider'      => 'google',
                        'profile_image' => $user->profile_image ?: $googleUser->getAvatar(),
                    ]);
                }
            }

            // Create new user if not found
            if (!$user) {
                $user = User::create([
                    'name'          => $googleUser->getName() ?: $googleUser->getNickname() ?: 'User',
                    'email'         => $googleUser->getEmail(),
                    'google_id'     => $googleUser->getId(),
                    'provider'      => 'google',
                    'profile_image' => $googleUser->getAvatar(),
                    'daily_limit'   => config('constants.message_limit', 1),
                    'is_onboarded'  => false,
                ]);
            }

            Auth::login($user);

            $token = $user->createToken('api-token')->plainTextToken;

            $redirectUrl = $frontendUrl . '/auth/callback?token=' . urlencode($token) . '&is_onboarded=' . ($user->is_onboarded ? '1' : '0');

            return redirect()->away($redirectUrl);
        } catch (\Exception $e) {
            Log::error('Socialite Google Callback Error: ' . $e->getMessage());

            return redirect()->away($frontendUrl . '/login?error=' . urlencode('Google authentication failed. Please try again.'));
        }
    }
}
