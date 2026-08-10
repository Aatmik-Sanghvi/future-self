<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class AdminAuthController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()->is_admin) {
            if (session('admin_2fa_passed')) {
                return redirect()->route('admin.dashboard');
            }
            if (Auth::user()->google2fa_enabled) {
                return redirect()->route('admin.2fa.challenge');
            }
            return redirect()->route('admin.2fa.setup');
        }

        return view('admin.auth.login');
    }

    /**
     * Handle admin login request (Direct password login disabled).
     */
    public function login(Request $request)
    {
        return back()->withErrors([
            'email' => 'Direct email & password login is disabled. Please log in using Google OAuth with 2FA.',
        ]);
    }

    /**
     * Redirect admin user to Google OAuth page.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->redirectUrl(route('admin.auth.google.callback'))
            ->redirect();
    }

    /**
     * Handle callback from Google OAuth for admin login.
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl(route('admin.auth.google.callback'))
                ->user();
        } catch (\Exception $e) {
            Log::error('Admin Google OAuth error: ' . $e->getMessage());
            return redirect()->route('admin.login')
                ->with('error', 'Google authentication failed: ' . $e->getMessage());
        }

        $email = $googleUser->getEmail();
        $googleId = $googleUser->getId();

        // Only the configured admin email is allowed
        $allowedEmail = config('admin.email');
        if (!$allowedEmail || strtolower($email) !== strtolower($allowedEmail)) {
            return redirect()->route('admin.login')
                ->with('error', 'Access denied. Only the authorized admin account can log in.');
        }

        // Search user by google_id or email
        $user = User::where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();

        if (!$user || !$user->is_admin) {
            return redirect()->route('admin.login')
                ->with('error', 'Access denied. The account ' . ($email ?: 'Google User') . ' does not have admin privileges.');
        }

        // Connect google_id if missing
        if (empty($user->google_id)) {
            $user->google_id = $googleId;
            $user->provider = 'google';
            $user->save();
        }

        Auth::login($user, true);
        $request->session()->regenerate();
        $request->session()->put('admin_2fa_passed', false);

        if ($user->google2fa_enabled) {
            return redirect()->route('admin.2fa.challenge');
        }

        return redirect()->route('admin.2fa.setup');
    }

    /**
     * Handle admin logout.
     */
    public function logout(Request $request)
    {
        $request->session()->forget('admin_2fa_passed');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'You have been logged out successfully.');
    }
}
