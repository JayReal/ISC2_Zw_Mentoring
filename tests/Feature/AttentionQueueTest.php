<?php

namespace Tests\Feature;

use App\Models\MentoringMatch;
use App\Models\MentoringSupportRequest;
use App\Models\ParticipantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AttentionQueueTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authorised_staff_see_confidential_requests_in_attention_queue(): void
    {
        $lead = User::factory()->create(['roles' => ['programme-lead']]);
        $mentor = User::factory()->create(['roles' => ['mentor']]);
        $mentee = User::factory()->create(['roles' => ['mentee']]);
        $match = MentoringMatch::create(['mentor_id' => $mentor->id, 'mentee_id' => $mentee->id, 'status' => 'active', 'tier' => 'matched', 'rationale' => 'Suitable test match with aligned development goals.']);
        MentoringSupportRequest::create(['mentoring_match_id' => $match->id, 'requested_by' => $mentee->id, 'type' => 'support', 'reason' => 'I need confidential assistance to reset the mentoring relationship expectations.']);

        $this->actingAs($lead)->get(route('admin.attention'))->assertOk()->assertSee('confidential assistance');
    }

    public function test_reporting_staff_cannot_open_identifiable_attention_queue(): void
    {
        $reporter = User::factory()->create(['roles' => ['reporting-lead']]);
        $mentor = User::factory()->create();
        $mentee = User::factory()->create();
        $match = MentoringMatch::create(['mentor_id' => $mentor->id, 'mentee_id' => $mentee->id, 'status' => 'active', 'tier' => 'matched', 'rationale' => 'Suitable test match with aligned development goals.']);
        MentoringSupportRequest::create(['mentoring_match_id' => $match->id, 'requested_by' => $mentee->id, 'type' => 'support', 'reason' => 'Private details that reporting staff must never be able to access.']);

        $this->actingAs($reporter)->get(route('admin.attention'))->assertForbidden();
    }

    public function test_only_genuinely_inactive_matches_appear_in_attention_queue(): void
    {
        $lead = User::factory()->create(['roles' => ['programme-lead']]);
        $mentor = User::factory()->create(['name' => 'Active Mentor']);
        $freshMentee = User::factory()->create(['name' => 'Fresh Mentee']);
        $staleMentee = User::factory()->create(['name' => 'Stale Mentee']);
        MentoringMatch::create(['mentor_id' => $mentor->id, 'mentee_id' => $freshMentee->id, 'status' => 'active', 'tier' => 'matched', 'rationale' => 'Recently activated mentoring relationship.', 'started_at' => now()]);
        MentoringMatch::create(['mentor_id' => $mentor->id, 'mentee_id' => $staleMentee->id, 'status' => 'active', 'tier' => 'matched', 'rationale' => 'Mentoring relationship without activity for over thirty days.', 'started_at' => now()->subDays(40)]);

        $this->actingAs($lead)->get(route('admin.attention'))
            ->assertOk()
            ->assertSee('Stale Mentee')
            ->assertDontSee('Fresh Mentee');
    }

    public function test_completed_and_updated_profiles_appear_for_staff_review(): void
    {
        $lead = User::factory()->create(['roles' => ['programme-lead']]);
        $completedParticipant = User::factory()->create(['name' => 'Completed Profile']);
        $updatedParticipant = User::factory()->create(['name' => 'Updated After Approval']);
        ParticipantProfile::factory()->create(['user_id' => $completedParticipant->id, 'intake_status' => 'complete']);
        ParticipantProfile::factory()->create(['user_id' => $updatedParticipant->id, 'intake_status' => 'approved', 'profile_review_requested_at' => now()]);

        $this->actingAs($lead)->get(route('admin.attention'))
            ->assertOk()
            ->assertSee('Profiles awaiting review')
            ->assertSee('Completed Profile')
            ->assertSee('Updated After Approval')
            ->assertSee('Updated after approval');
    }
}
