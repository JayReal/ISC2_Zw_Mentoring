<?php

namespace Tests\Feature;

use App\Models\ParticipantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_sent_to_login(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_participant_cannot_access_programme_administration(): void
    {
        $participant = User::factory()->create(['roles' => ['mentee']]);
        $this->actingAs($participant)->get('/admin')->assertForbidden();
    }

    public function test_programme_staff_can_access_administration(): void
    {
        $staff = User::factory()->create(['roles' => ['programme-lead']]);

        $this->actingAs($staff)->get('/admin')->assertOk()->assertSee('Programme overview');
    }

    public function test_reporting_role_cannot_change_programme_configuration(): void
    {
        $staff = User::factory()->create(['roles' => ['reporting-lead']]);

        $this->actingAs($staff)->get(route('admin.cycles.create'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.matches.create'))->assertForbidden();
    }

    public function test_reporting_role_sees_aggregate_dashboard_without_participant_details(): void
    {
        $staff = User::factory()->create(['roles' => ['reporting-lead']]);
        $participant = User::factory()->create(['name' => 'Private Participant', 'roles' => ['mentee']]);
        ParticipantProfile::factory()->create(['user_id' => $participant->id]);

        $response = $this->actingAs($staff)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('aggregate programme visibility')
            ->assertDontSee('Private Participant')
            ->assertDontSee('Service readiness');
        $this->actingAs($staff)->get(route('admin.participants.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.attention'))->assertForbidden();
    }

    public function test_safeguarding_role_can_review_participants_and_attention_queue(): void
    {
        $staff = User::factory()->create(['roles' => ['safeguarding']]);

        $this->actingAs($staff)->get(route('admin.participants.index'))->assertOk();
        $this->actingAs($staff)->get(route('admin.attention'))->assertOk();
    }

    public function test_initial_programme_role_can_be_granted_safely(): void
    {
        $user = User::factory()->create(['email' => 'lead@example.test', 'roles' => ['mentee']]);

        $this->artisan('programme:grant-role', ['email' => $user->email, 'role' => 'programme-lead'])
            ->expectsOutputToContain('Granted programme-lead')
            ->assertSuccessful();

        $this->assertTrue($user->fresh()->hasAnyRole(['programme-lead']));
    }
}
