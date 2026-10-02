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
        ParticipantProfile::factory()->create(['user_id' => $mentor->id, 'participation_type' => 'mentor', 'intake_status' => 'approved', 'mentor_orientation_completed_at' => now(), 'mentor_availability_status' => 'available', 'mentor_capacity' => 1]);
        ParticipantProfile::factory()->create(['user_id' => $mentee->id, 'intake_status' => 'approved']);
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

    public function test_staff_cannot_propose_a_mentor_who_is_not_ready(): void
    {
        $staff = User::factory()->create(['roles' => ['matching-team']]);
        $mentor = User::factory()->create(['roles' => ['mentor']]);
        $mentee = User::factory()->create(['roles' => ['mentee']]);
        ParticipantProfile::factory()->create(['user_id' => $mentor->id, 'participation_type' => 'mentor', 'mentor_orientation_completed_at' => null, 'mentor_availability_status' => 'available']);

        $this->actingAs($staff)->post(route('admin.matches.store'), [
            'mentor_id' => $mentor->id, 'mentee_id' => $mentee->id, 'tier' => 'matched',
            'rationale' => 'The profiles appear aligned, but readiness requirements are not complete.',
        ])->assertSessionHasErrors('mentor_id');

        $this->assertDatabaseCount('mentoring_matches', 0);
    }

    public function test_staff_cannot_activate_a_match_before_both_confirmations(): void
    {
        $staff = User::factory()->create(['roles' => ['programme-lead']]);
        $mentor = User::factory()->create(['roles' => ['mentor']]);
        $mentee = User::factory()->create(['roles' => ['mentee']]);
        $match = MentoringMatch::create([
            'mentor_id' => $mentor->id, 'mentee_id' => $mentee->id, 'status' => 'pending-confirmation',
            'tier' => 'matched', 'rationale' => 'A reviewed match that still requires both participant confirmations.',
        ]);

        $this->actingAs($staff)->put(route('admin.matches.update', $match), [
            'status' => 'active', 'reason' => 'Attempting to activate before confirmations.',
        ])->assertSessionHasErrors('status');

        $this->assertSame('pending-confirmation', $match->fresh()->status);
    }

    public function test_staff_cannot_create_overlapping_proposals_for_a_mentee(): void
    {
        $staff = User::factory()->create(['roles' => ['matching-team']]);
        $firstMentor = User::factory()->create(['roles' => ['mentor']]);
        $secondMentor = User::factory()->create(['roles' => ['mentor']]);
        $mentee = User::factory()->create(['roles' => ['mentee']]);
        foreach ([$firstMentor, $secondMentor] as $mentor) {
            ParticipantProfile::factory()->create(['user_id' => $mentor->id, 'participation_type' => 'mentor', 'intake_status' => 'approved', 'mentor_orientation_completed_at' => now(), 'mentor_availability_status' => 'available']);
        }
        ParticipantProfile::factory()->create(['user_id' => $mentee->id, 'intake_status' => 'approved']);
        MentoringMatch::create(['mentor_id' => $firstMentor->id, 'mentee_id' => $mentee->id, 'status' => 'proposed', 'tier' => 'matched', 'rationale' => 'Existing proposal awaiting participant decisions.']);

        $this->actingAs($staff)->post(route('admin.matches.store'), ['mentor_id' => $secondMentor->id, 'mentee_id' => $mentee->id, 'tier' => 'matched', 'rationale' => 'A second proposal that should be rejected while the first remains current.'])
            ->assertSessionHasErrors('mentee_id');

        $this->assertDatabaseCount('mentoring_matches', 1);
    }
}
