<?php

namespace Tests\Feature;

use App\Models\MentoringMatch;
use App\Models\MentoringSupportRequest;
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

    public function test_reporting_staff_do_not_receive_confidential_request_content(): void
    {
        $reporter = User::factory()->create(['roles' => ['reporting-lead']]);
        $mentor = User::factory()->create();
        $mentee = User::factory()->create();
        $match = MentoringMatch::create(['mentor_id' => $mentor->id, 'mentee_id' => $mentee->id, 'status' => 'active', 'tier' => 'matched', 'rationale' => 'Suitable test match with aligned development goals.']);
        MentoringSupportRequest::create(['mentoring_match_id' => $match->id, 'requested_by' => $mentee->id, 'type' => 'support', 'reason' => 'Private details that reporting staff must never be able to access.']);

        $this->actingAs($reporter)->get(route('admin.attention'))->assertOk()->assertDontSee('Private details');
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
}
