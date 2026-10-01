<?php

namespace Tests\Feature;

use App\Models\Cluster;
use App\Models\MentoringMatch;
use App\Models\ParticipantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminWorkflowTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_staff_can_review_participant_with_audited_reason(): void
    {
        $staff = User::factory()->create(['roles' => ['programme-lead']]);
        $profile = ParticipantProfile::factory()->create();

        $this->actingAs($staff)->put(route('admin.participants.update', $profile), [
            'intake_status' => 'approved', 'programme_cycle_id' => null, 'reason' => 'Intake is complete and appropriate for matching.',
        ])->assertRedirect();

        $this->assertDatabaseHas('participant_profiles', ['id' => $profile->id, 'intake_status' => 'approved']);
        $this->assertDatabaseHas('audit_logs', ['actor_id' => $staff->id, 'action' => 'participant.reviewed', 'subject_id' => $profile->id]);
    }

    public function test_matching_team_can_create_and_advance_a_match_proposal(): void
    {
        $staff = User::factory()->create(['roles' => ['matching-team']]);
        $mentor = User::factory()->create(['roles' => ['mentor']]);
        $mentee = User::factory()->create(['roles' => ['mentee']]);
        $cluster = Cluster::factory()->create();

        $response = $this->actingAs($staff)->post(route('admin.matches.store'), [
            'mentor_id' => $mentor->id, 'mentee_id' => $mentee->id, 'cluster_id' => $cluster->id,
            'tier' => 'matched', 'compatibility_score' => 82,
            'rationale' => 'Goals, expertise, availability and preferred meeting format are aligned.',
        ]);
        $match = MentoringMatch::firstOrFail();
        $response->assertRedirect(route('admin.matches.show', $match));

        $this->actingAs($staff)->put(route('admin.matches.update', $match), ['status' => 'pending-confirmation', 'reason' => 'Conflict and capacity checks completed.'])->assertRedirect();

        $this->assertDatabaseHas('mentoring_matches', ['id' => $match->id, 'status' => 'pending-confirmation']);
        $this->assertDatabaseCount('audit_logs', 2);
    }
}
