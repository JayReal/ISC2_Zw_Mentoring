<?php

namespace Tests\Feature;

use App\Models\MentoringMatch;
use App\Models\ParticipantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ParticipantDashboardTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_completed_participant_is_told_what_happens_during_review(): void
    {
        $user = User::factory()->create(['roles' => ['mentee']]);
        ParticipantProfile::factory()->create(['user_id' => $user->id, 'intake_status' => 'complete']);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Your profile is in programme review')
            ->assertSee('No further action is required right now')
            ->assertSee('Review or update profile')
            ->assertSee('From intake to active mentoring');
    }

    public function test_participant_with_proposal_gets_a_clear_confirmation_action(): void
    {
        $mentor = User::factory()->create(['roles' => ['mentor']]);
        $mentee = User::factory()->create(['roles' => ['mentee']]);
        ParticipantProfile::factory()->create(['user_id' => $mentee->id, 'intake_status' => 'complete']);
        $match = MentoringMatch::create(['mentor_id' => $mentor->id, 'mentee_id' => $mentee->id, 'tier' => 'matched', 'status' => 'proposed', 'rationale' => 'Goals and availability align for a structured mentoring relationship.']);

        $this->actingAs($mentee)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Review your match proposal')
            ->assertSee(route('matches.show', $match));
    }
}
