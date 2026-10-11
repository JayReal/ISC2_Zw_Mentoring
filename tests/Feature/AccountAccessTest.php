<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AccountAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_unverified_user_is_directed_to_verification(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('verification.notice'));
    }

    public function test_password_reset_link_can_be_requested(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHasNoErrors();
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_can_be_reset_with_a_valid_token(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->post(route('password.email'), ['email' => $user->email]);
        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token): bool {
            $token = $notification->token;

            return true;
        });

        $this->post(route('password.update'), ['token' => $token, 'email' => $user->email, 'password' => 'A new secure password!', 'password_confirmation' => 'A new secure password!'])
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('A new secure password!', $user->fresh()->password));
    }

    public function test_authenticated_user_can_update_name_without_reverification(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)->put(route('account.update'), [
            'name' => 'Updated Name',
            'email' => $user->email,
        ]);

        $response->assertRedirect()->assertSessionHas('status', 'Account details saved.');
        $this->assertSame('Updated Name', $user->fresh()->name);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_email_change_requires_current_password_and_reverification(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('account.update'), [
            'name' => $user->name,
            'email' => 'new-address@example.org',
        ])->assertSessionHasErrors('current_password');

        $response = $this->put(route('account.update'), [
            'name' => $user->name,
            'email' => 'new-address@example.org',
            'current_password' => 'password',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertSame('new-address@example.org', $user->fresh()->email);
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_authenticated_user_can_change_password_with_current_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('account.password.update'), [
            'current_password' => 'password',
            'password' => 'A different secure password!',
            'password_confirmation' => 'A different secure password!',
        ]);

        $response->assertRedirect()->assertSessionHas('status', 'Password updated.');
        $this->assertTrue(Hash::check('A different secure password!', $user->fresh()->password));
    }
}
