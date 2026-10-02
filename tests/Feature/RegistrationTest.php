<?php

namespace Tests\Feature;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_adult_with_required_consents_can_register(): void
    {
        Notification::fake();
        $response = $this->post('/register', [
            'name' => 'Tatenda Moyo',
            'email' => 'tatenda@example.test',
            'date_of_birth' => now()->subYears(25)->toDateString(),
            'participation_type' => 'both',
            'password' => 'A long secure password!',
            'password_confirmation' => 'A long secure password!',
            'matching_consent' => '1',
            'privacy_acknowledgement' => '1',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('participant_profiles', ['participation_type' => 'both']);
        $this->assertDatabaseCount('consents', 2);
        Notification::assertSentTo(auth()->user(), VerifyEmail::class);
    }

    public function test_person_under_18_cannot_register(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Young Participant',
            'email' => 'young@example.test',
            'date_of_birth' => now()->subYears(17)->toDateString(),
            'participation_type' => 'mentee',
            'password' => 'A long secure password!',
            'password_confirmation' => 'A long secure password!',
            'matching_consent' => '1',
            'privacy_acknowledgement' => '1',
        ]);

        $response->assertRedirect('/register')->assertSessionHasErrors('date_of_birth');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }
}
