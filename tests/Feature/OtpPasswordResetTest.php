<?php

namespace Tests\Feature;

use App\Models\PasswordOtpReset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * End-to-end tests for the OTP password reset API flow:
 *   POST /api/auth/password/send-otp     → email + OTP
 *   POST /api/auth/password/verify-otp   → reset_token (64 chars)
 *   POST /api/auth/password/reset        → password updated, tokens revoked
 */
class OtpPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        // Disable throttle in tests
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    public function test_full_happy_path_works_end_to_end(): void
    {
        $user = User::factory()->create([
            'email' => 'happy@test.com',
            'password' => Hash::make('oldpassword'),
        ]);

        // Step 1: send-otp
        $r1 = $this->postJson('/api/auth/password/send-otp', ['email' => 'happy@test.com']);
        $r1->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('password_otp_resets', ['email' => 'happy@test.com']);

        $rec = PasswordOtpReset::where('email', 'happy@test.com')->latest('id')->first();
        $this->assertNotNull($rec);
        $this->assertEquals(6, strlen($rec->otp));

        // Step 2: verify-otp
        $r2 = $this->postJson('/api/auth/password/verify-otp', [
            'email' => 'happy@test.com',
            'otp' => $rec->otp,
        ]);
        $r2->assertOk()
            ->assertJsonStructure(['success', 'reset_token', 'expires_in_minutes']);
        $resetToken = $r2->json('reset_token');
        $this->assertEquals(64, strlen($resetToken));

        // Step 3: reset password
        $r3 = $this->postJson('/api/auth/password/reset', [
            'email' => 'happy@test.com',
            'reset_token' => $resetToken,
            'password' => 'brandnewpass',
            'password_confirmation' => 'brandnewpass',
        ]);
        $r3->assertOk()->assertJson(['success' => true]);

        $this->assertTrue(Hash::check('brandnewpass', $user->fresh()->password));
        $this->assertDatabaseMissing('password_otp_resets', ['email' => 'happy@test.com']);
    }

    public function test_send_otp_returns_generic_success_for_unknown_email_no_enumeration(): void
    {
        $r = $this->postJson('/api/auth/password/send-otp', ['email' => 'nobody@nowhere.com']);
        $r->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseMissing('password_otp_resets', ['email' => 'nobody@nowhere.com']);
    }

    public function test_wrong_otp_increments_attempts_and_blocks_after_5(): void
    {
        $user = User::factory()->create(['email' => 'brute@test.com']);
        $this->postJson('/api/auth/password/send-otp', ['email' => 'brute@test.com']);

        for ($i = 1; $i <= PasswordOtpReset::MAX_ATTEMPTS; $i++) {
            $r = $this->postJson('/api/auth/password/verify-otp', [
                'email' => 'brute@test.com',
                'otp' => '000000',
            ]);
            $r->assertStatus(422);
        }

        // 6th attempt → record should be invalidated (deleted) → "no pending OTP" error
        $r6 = $this->postJson('/api/auth/password/verify-otp', [
            'email' => 'brute@test.com',
            'otp' => '000000',
        ]);
        $r6->assertStatus(429);
        $this->assertDatabaseMissing('password_otp_resets', ['email' => 'brute@test.com']);
    }

    public function test_expired_otp_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'expired@test.com']);
        $rec = PasswordOtpReset::create([
            'email' => 'expired@test.com',
            'otp' => '111111',
            'expires_at' => now()->subMinute(),
        ]);

        $r = $this->postJson('/api/auth/password/verify-otp', [
            'email' => 'expired@test.com',
            'otp' => '111111',
        ]);

        $r->assertStatus(422);
        $this->assertDatabaseMissing('password_otp_resets', ['id' => $rec->id]);
    }

    public function test_reset_without_verified_otp_fails(): void
    {
        User::factory()->create(['email' => 'noverify@test.com']);

        $r = $this->postJson('/api/auth/password/reset', [
            'email' => 'noverify@test.com',
            'reset_token' => str_repeat('a', 64),
            'password' => 'whatever1',
            'password_confirmation' => 'whatever1',
        ]);

        $r->assertStatus(422);
    }

    public function test_reset_window_expires_after_15_minutes(): void
    {
        $user = User::factory()->create(['email' => 'window@test.com']);
        PasswordOtpReset::create([
            'email' => 'window@test.com',
            'otp' => '222222',
            'reset_token' => str_repeat('b', 64),
            'verified_at' => now()->subMinutes(20), // verified 20 min ago — window closed
            'expires_at' => now()->addMinutes(10),
        ]);

        $r = $this->postJson('/api/auth/password/reset', [
            'email' => 'window@test.com',
            'reset_token' => str_repeat('b', 64),
            'password' => 'newpass1234',
            'password_confirmation' => 'newpass1234',
        ]);

        $r->assertStatus(422);
    }

    public function test_password_confirmation_required(): void
    {
        $user = User::factory()->create(['email' => 'confirm@test.com']);
        PasswordOtpReset::create([
            'email' => 'confirm@test.com',
            'otp' => '333333',
            'reset_token' => str_repeat('c', 64),
            'verified_at' => now(),
            'expires_at' => now()->addMinutes(10),
        ]);

        $r = $this->postJson('/api/auth/password/reset', [
            'email' => 'confirm@test.com',
            'reset_token' => str_repeat('c', 64),
            'password' => 'mypass1234',
            // missing password_confirmation
        ]);

        $r->assertStatus(422);
    }

    public function test_email_lookup_is_case_insensitive(): void
    {
        $user = User::factory()->create(['email' => 'CaseTest@test.com']);

        $r1 = $this->postJson('/api/auth/password/send-otp', ['email' => 'casetest@test.com']);
        $r1->assertOk();

        $rec = PasswordOtpReset::latest('id')->first();
        $this->assertNotNull($rec);

        $r2 = $this->postJson('/api/auth/password/verify-otp', [
            'email' => 'CASETEST@test.com',
            'otp' => $rec->otp,
        ]);
        $r2->assertOk();
    }
}
