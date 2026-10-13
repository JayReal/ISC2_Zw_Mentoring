<?php

namespace Tests\Feature;

use App\Models\AuditLog;
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
        $profile = ParticipantProfile::factory()->create(['profile_review_requested_at' => now()]);

        $this->actingAs($staff)->put(route('admin.participants.update', $profile), [
            'intake_status' => 'approved', 'programme_cycle_id' => null, 'reason' => 'Intake is complete and appropriate for matching.',
            'review_requested_at' => $profile->profile_review_requested_at->format('Y-m-d H:i:s'),
        ])->assertRedirect();

        $this->assertDatabaseHas('participant_profiles', ['id' => $profile->id, 'intake_status' => 'approved']);
        $this->assertNull($profile->fresh()->profile_review_requested_at);
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

    public function test_matching_screen_shows_context_needed_for_a_human_review(): void
    {
        $staff = User::factory()->create(['roles' => ['matching-team']]);
        $cluster = Cluster::factory()->create(['name' => 'Governance and Risk']);
        $mentor = User::factory()->create(['name' => 'Context Mentor', 'roles' => ['mentor']]);
        $mentee = User::factory()->create(['name' => 'Context Mentee', 'roles' => ['mentee']]);
        ParticipantProfile::factory()->create([
            'user_id' => $mentor->id,
            'participation_type' => 'mentor',
            'intake_status' => 'approved',
            'primary_cluster_id' => $cluster->id,
            'mentor_orientation_completed_at' => now(),
            'mentor_availability_status' => 'available',
            'mentor_capacity' => 1,
            'mentor_expertise' => 'Risk leadership and security governance.',
            'goals' => 'Help emerging professionals build practical governance judgement.',
            'availability' => ['weekday-evening'],
            'mentoring_styles' => ['structured'],
        ]);
        ParticipantProfile::factory()->create([
            'user_id' => $mentee->id,
            'intake_status' => 'approved',
            'primary_cluster_id' => $cluster->id,
            'pathway' => 'professional-growth',
            'goals' => 'Build confidence leading a practical risk assessment programme.',
            'experience_summary' => 'Three years of operational security experience.',
            'availability' => ['weekday-evening'],
            'mentoring_styles' => ['structured'],
        ]);

        $this->actingAs($staff)->get(route('admin.matches.create'))
            ->assertOk()
            ->assertSee('Compare the participants')
            ->assertSee('Risk leadership and security governance.')
            ->assertSee('Build confidence leading a practical risk assessment programme.')
            ->assertSee('Structured and accountable')
            ->assertSee('Practical overlap')
            ->assertSee('do not recommend, rank or score a match');
    }

    public function test_staff_cannot_clear_a_review_requested_after_the_page_was_opened(): void
    {
        $staff = User::factory()->create(['roles' => ['programme-lead']]);
        $profile = ParticipantProfile::factory()->create([
            'intake_status' => 'under_review',
            'profile_review_requested_at' => now()->subMinutes(5),
        ]);
        $staleAudit = AuditLog::factory()->create([
            'action' => 'participant.profile_updated',
            'subject_type' => $profile->getMorphClass(),
            'subject_id' => $profile->id,
        ]);
        $staleToken = $profile->profile_review_requested_at->format('Y-m-d H:i:s');
        $newReviewTime = now();
        $profile->update(['profile_review_requested_at' => $newReviewTime]);
        AuditLog::factory()->create([
            'action' => 'participant.profile_updated',
            'subject_type' => $profile->getMorphClass(),
            'subject_id' => $profile->id,
        ]);

        $this->actingAs($staff)->put(route('admin.participants.update', $profile), [
            'intake_status' => 'approved',
            'programme_cycle_id' => null,
            'reason' => 'Reviewed an older version of the participant profile.',
            'review_requested_at' => $staleToken,
            'review_audit_id' => $staleAudit->id,
        ])->assertSessionHasErrors('review_requested_at');

        $profile->refresh();
        $this->assertSame('under_review', $profile->intake_status);
        $this->assertSame($newReviewTime->format('Y-m-d H:i:s'), $profile->profile_review_requested_at->format('Y-m-d H:i:s'));
    }

    public function test_staff_cannot_propose_a_match_until_updated_profile_information_is_reviewed(): void
    {
        $staff = User::factory()->create(['roles' => ['matching-team']]);
        $mentor = User::factory()->create(['roles' => ['mentor']]);
        $mentee = User::factory()->create(['roles' => ['mentee']]);
        ParticipantProfile::factory()->create([
            'user_id' => $mentor->id,
            'participation_type' => 'mentor',
            'intake_status' => 'approved',
            'mentor_orientation_completed_at' => now(),
            'mentor_availability_status' => 'available',
            'mentor_capacity' => 2,
            'profile_review_requested_at' => now(),
        ]);
        ParticipantProfile::factory()->create(['user_id' => $mentee->id, 'intake_status' => 'approved']);

        $this->actingAs($staff)->post(route('admin.matches.store'), [
            'mentor_id' => $mentor->id,
            'mentee_id' => $mentee->id,
            'tier' => 'matched',
            'rationale' => 'A proposal that must wait for the mentor profile update to be reviewed.',
        ])->assertSessionHasErrors('mentor_id');

        $this->assertDatabaseCount('mentoring_matches', 0);
    }
}
