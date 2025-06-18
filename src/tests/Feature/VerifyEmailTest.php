<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use App\Models\User;

class VerifyEmailTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_send_email_after_registering()
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'テストユーザ',
            'email' => 'test@test.com',
            'password' => 'password0123',
            'password_confirmation' => 'password0123',
        ]);
        $user = User::where('name', 'テストユーザ')->first();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_can_see_view_verification_notice()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);
        $this->actingAs($user);
        $response = $this->get('/email/verify');
        $response->assertSee('認証はこちらから');
    }

    public function test_verify_email_success()
    {
        Event::fake();
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);
        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)],
        );
        $this->actingAs($user);

        $response = $this->get($verifyUrl);
        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertNotNull($user->email_verified_at);
        $response->assertRedirect('/attendance');
    }
}
