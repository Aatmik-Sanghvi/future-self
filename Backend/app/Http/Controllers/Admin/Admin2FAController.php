<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class Admin2FAController extends Controller
{
    /**
     * Show 2FA Setup page for admin user.
     */
    public function showSetup()
    {
        $user = Auth::user();

        if ($user->google2fa_enabled && session('admin_2fa_passed')) {
            return redirect()->route('admin.dashboard');
        }

        $google2fa = new Google2FA();

        if (empty($user->google2fa_secret)) {
            $user->google2fa_secret = $google2fa->generateSecretKey();
            $user->save();
        }

        $secretKey = $user->google2fa_secret;
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name', 'FutureSelf') . ' Admin',
            $user->email,
            $secretKey
        );

        $qrCodeSvg = '';
        try {
            $renderer = new ImageRenderer(
                new RendererStyle(200, 1),
                new SvgImageBackEnd()
            );
            $writer = new Writer($renderer);
            $qrCodeSvg = $writer->writeString($qrCodeUrl);
        } catch (\Exception $e) {
            Log::error('QR Code generation error: ' . $e->getMessage());
        }

        return view('admin.auth.2fa-setup', [
            'secretKey' => $secretKey,
            'qrCodeSvg' => $qrCodeSvg,
            'qrCodeUrl' => $qrCodeUrl,
            'user' => $user,
        ]);
    }

    /**
     * Confirm 2FA setup by validating initial 6-digit TOTP code.
     */
    public function confirmSetup(Request $request)
    {
        $request->validate([
            'one_time_password' => ['required', 'numeric', 'digits:6'],
        ]);

        $user = Auth::user();
        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->input('one_time_password'));

        if (!$valid) {
            return back()->withErrors(['one_time_password' => 'Invalid verification code. Please make sure the code matches your authenticator app.']);
        }

        // Generate 8 recovery codes
        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = Str::random(5) . '-' . Str::random(5);
        }

        $user->google2fa_enabled = true;
        $user->two_factor_recovery_codes = $recoveryCodes;
        $user->save();

        session(['admin_2fa_passed' => true]);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Two-Factor Authentication enabled successfully! Please save your recovery codes.')
            ->with('recoveryCodes', $recoveryCodes);
    }

    /**
     * Show 2FA Challenge page.
     */
    public function showChallenge()
    {
        $user = Auth::user();

        if (session('admin_2fa_passed')) {
            return redirect()->route('admin.dashboard');
        }

        if (!$user->google2fa_enabled) {
            return redirect()->route('admin.2fa.setup');
        }

        return view('admin.auth.2fa-challenge', [
            'user' => $user,
        ]);
    }

    /**
     * Verify 2FA code or recovery code.
     */
    public function verifyChallenge(Request $request)
    {
        $user = Auth::user();
        $google2fa = new Google2FA();

        if ($request->filled('recovery_code')) {
            $recoveryCode = trim($request->input('recovery_code'));
            $codes = $user->two_factor_recovery_codes ?: [];

            if (in_array($recoveryCode, $codes)) {
                // Remove used code
                $codes = array_values(array_diff($codes, [$recoveryCode]));
                $user->two_factor_recovery_codes = $codes;
                $user->save();

                session(['admin_2fa_passed' => true]);

                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Authenticated using recovery code. Remaining recovery codes: ' . count($codes));
            }

            return back()->withErrors(['recovery_code' => 'Invalid or already used recovery code.']);
        }

        $request->validate([
            'one_time_password' => ['required', 'numeric', 'digits:6'],
        ]);

        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->input('one_time_password'));

        if (!$valid) {
            return back()->withErrors(['one_time_password' => 'Invalid verification code. Please try again.']);
        }

        session(['admin_2fa_passed' => true]);

        return redirect()->intended(route('admin.dashboard'));
    }
}
