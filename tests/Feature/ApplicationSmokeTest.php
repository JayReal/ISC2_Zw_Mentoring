<?php

namespace Tests\Feature;

use App\Models\ParticipantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ApplicationSmokeTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_all_guest_pages_render_without_server_errors(): void
    {
        $this->get('/')->assertOk();
        $this->get('/register')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/intake')->assertRedirect(route('login'));
    }

    public function test_all_participant_pages_render_without_server_errors(): void
    {
        $user = User::factory()->create();
        ParticipantProfile::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->get('/')->assertOk();
        $this->actingAs($user)->get('/dashboard')->assertOk();
        $this->actingAs($user)->get('/intake')->assertOk();
        $this->actingAs($user)->get('/login')->assertRedirect(route('dashboard'));
        $this->actingAs($user)->get('/register')->assertRedirect(route('dashboard'));
    }

    public function test_participant_can_log_in_and_log_out_without_server_errors(): void
    {
        $user = User::factory()->create(['password' => 'A long secure password!']);
        ParticipantProfile::factory()->create(['user_id' => $user->id]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'A long secure password!',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
