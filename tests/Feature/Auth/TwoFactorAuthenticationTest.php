<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function enrolledUser(): User
    {
        $user = User::factory()->create();
        $user->forceFill([
            'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-one', 'recovery-code-two'])),
            'two_factor_confirmed_at' => now(),
        ])->save();

        return $user;
    }

    public function test_password_alone_does_not_authenticate_an_enrolled_user(): void
    {
        $user = $this->enrolledUser();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/two-factor-challenge');
        $this->assertGuest();
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_valid_second_factor_completes_login(): void
    {
        $user = $this->enrolledUser();
        $this->mock(TwoFactorAuthenticationProvider::class)
            ->shouldReceive('verify')->once()->with('JBSWY3DPEHPK3PXP', '123456')->andReturn(true);
        $this->withSession(['login.id' => $user->id])->post('/two-factor-challenge', ['code' => '123456'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_second_factor_does_not_authenticate(): void
    {
        $user = $this->enrolledUser();
        $this->mock(TwoFactorAuthenticationProvider::class)
            ->shouldReceive('verify')->with('JBSWY3DPEHPK3PXP', '000000')->andReturn(false);
        $this->withSession(['login.id' => $user->id])->post('/two-factor-challenge', ['code' => '000000'])
            ->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_recovery_code_is_consumed(): void
    {
        $user = $this->enrolledUser();
        $this->withSession(['login.id' => $user->id])->post('/two-factor-challenge', ['recovery_code' => 'recovery-code-one'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertNotContains('recovery-code-one', $user->fresh()->recoveryCodes());
        $this->post('/logout');
        $this->withSession(['login.id' => $user->id])->post('/two-factor-challenge', ['recovery_code' => 'recovery-code-one'])
            ->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_security_settings_require_password_confirmation(): void
    {
        $this->actingAs($this->enrolledUser())->get('/account/security')->assertRedirect('/confirm-password');
    }

    public function test_setup_is_not_enabled_until_confirmed(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()])
            ->post('/user/two-factor-authentication')->assertRedirect();
        $this->assertNotNull($user->fresh()->two_factor_secret);
        $this->assertNull($user->fresh()->two_factor_confirmed_at);
        $this->assertStringNotContainsString(decrypt($user->fresh()->two_factor_secret), $user->fresh()->getRawOriginal('two_factor_secret'));
    }

    public function test_settings_are_not_cacheable_and_secrets_are_hidden(): void
    {
        $user = $this->enrolledUser();
        $response = $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()])->get('/account/security');
        $response->assertOk();
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertArrayNotHasKey('two_factor_secret', $user->toArray());
        $this->assertArrayNotHasKey('two_factor_recovery_codes', $user->toArray());
    }

    public function test_two_factor_attempts_are_throttled(): void
    {
        $user = $this->enrolledUser();
        $this->mock(TwoFactorAuthenticationProvider::class)->shouldReceive('verify')->andReturn(false);
        for ($i = 0; $i < 5; $i++) {
            $this->withSession(['login.id' => $user->id])->post('/two-factor-challenge', ['code' => '000000']);
        }
        $this->post('/two-factor-challenge', ['code' => '000000'])->assertStatus(429);
        $this->assertGuest();
    }

    public function test_real_authenticator_code_confirms_setup_and_pending_screen_renders(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()])
            ->post('/user/two-factor-authentication')->assertRedirect();
        $this->get('/account/security')->assertOk()->assertSee('Confirm and enable');
        $secret = decrypt($user->fresh()->two_factor_secret);
        $code = (new \PragmaRX\Google2FA\Google2FA)->getCurrentOtp($secret);
        $this->post('/user/confirmed-two-factor-authentication', ['code' => $code])->assertRedirect();
        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);
    }

    public function test_replacing_codes_invalidates_old_codes_and_disable_clears_secrets(): void
    {
        $user = $this->enrolledUser();
        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()])
            ->post('/user/two-factor-recovery-codes')->assertRedirect();
        $this->assertNotContains('recovery-code-one', $user->fresh()->recoveryCodes());
        $this->delete('/user/two-factor-authentication')->assertRedirect();
        $this->assertNull($user->fresh()->two_factor_secret);
        $this->assertNull($user->fresh()->two_factor_recovery_codes);
        $this->assertNull($user->fresh()->two_factor_confirmed_at);
    }

    public function test_challenge_screen_renders_for_pending_login(): void
    {
        $user = $this->enrolledUser();
        $this->withSession(['login.id' => $user->id])->get('/two-factor-challenge')
            ->assertOk()->assertSee('Verify your sign-in');
        $this->assertGuest();
    }

}
