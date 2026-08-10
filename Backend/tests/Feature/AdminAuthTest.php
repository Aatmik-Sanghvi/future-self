<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test login page loads and shows Google Sign-In button.
     */
    public function test_admin_login_page_renders_google_sso_option(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertSee('Sign in with Google');
    }

    /**
     * Test direct password login is rejected.
     */
    public function test_direct_password_login_is_disabled(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test non-admin user Google callback is rejected.
     */
    public function test_non_admin_google_login_is_rejected(): void
    {
        config(['admin.email' => 'regularuser@example.com']);

        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser->shouldReceive('getId')->andReturn('google-123456');
        $abstractUser->shouldReceive('getEmail')->andReturn('regularuser@example.com');

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        User::factory()->create([
            'email' => 'regularuser@example.com',
            'is_admin' => false,
        ]);

        $response = $this->get('/admin/auth/google/callback');

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHas('error');
    }

    /**
     * Test admin user Google callback redirects to 2FA setup when 2FA is not enabled.
     */
    public function test_admin_google_login_redirects_to_2fa_setup_when_disabled(): void
    {
        config(['admin.email' => 'admin@example.com']);

        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser->shouldReceive('getId')->andReturn('google-999');
        $abstractUser->shouldReceive('getEmail')->andReturn('admin@example.com');

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
            'google2fa_enabled' => false,
        ]);

        $response = $this->get('/admin/auth/google/callback');

        $response->assertRedirect(route('admin.2fa.setup'));
        $this->assertAuthenticatedAs($admin);
    }

    /**
     * Test admin 2FA setup confirmation with valid TOTP code.
     */
    public function test_admin_can_confirm_2fa_setup(): void
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
            'google2fa_secret' => $secret,
            'google2fa_enabled' => false,
        ]);

        $validOtp = $google2fa->getCurrentOtp($secret);

        $response = $this->actingAs($admin)
            ->withSession(['admin_2fa_passed' => false])
            ->post('/admin/2fa/setup', [
                'one_time_password' => $validOtp,
            ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertTrue(session('admin_2fa_passed'));
        $this->assertTrue($admin->fresh()->google2fa_enabled);
    }

    /**
     * Test 2FA challenge verification with valid TOTP code.
     */
    public function test_admin_can_verify_2fa_challenge(): void
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
            'google2fa_secret' => $secret,
            'google2fa_enabled' => true,
        ]);

        $validOtp = $google2fa->getCurrentOtp($secret);

        $response = $this->actingAs($admin)
            ->withSession(['admin_2fa_passed' => false])
            ->post('/admin/2fa/challenge', [
                'one_time_password' => $validOtp,
            ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertTrue(session('admin_2fa_passed'));
    }

    /**
     * Test unverified 2FA user cannot access dashboard directly.
     */
    public function test_unverified_2fa_admin_blocked_from_dashboard(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
            'google2fa_enabled' => true,
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['admin_2fa_passed' => false])
            ->get('/admin/dashboard');

        $response->assertRedirect(route('admin.2fa.challenge'));
    }
}
