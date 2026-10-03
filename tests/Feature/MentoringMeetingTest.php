<?php

namespace Tests\Feature;

use App\Models\MentoringGoal;
use App\Models\MentoringMatch;
use App\Models\User;
use App\Notifications\MatchActionNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MentoringMeetingTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_participant_can_record_a_shared_meeting_and_notify_counterpart(): void
    {
        Notification::fake();
        [$match, $mentor, $mentee] = $this->activeMatch();

        $this->actingAs($mentor)->post(route('matches.meetings.store', $match), [
            'meeting_on' => now()->toDateString(), 'duration_minutes' => 45,
            'topics_discussed' => 'Reviewed the agreed development goal and the first practical milestone.',
            'decisions' => 'Focus on one milestone before expanding the plan.',
            'next_actions' => 'Mentee prepares the initial draft for review.',
            'next_meeting_on' => now()->addWeeks(2)->toDateString(),
        ])->assertRedirect();

        $this->assertDatabaseHas('mentoring_meetings', ['mentoring_match_id' => $match->id, 'recorded_by' => $mentor->id, 'duration_minutes' => 45]);
        $this->assertNotNull($match->fresh()->last_activity_at);
        Notification::assertSentTo($mentee, MatchActionNotification::class);
    }

    public function test_outsider_cannot_update_a_meeting_record(): void
    {
        [$match, $mentor] = $this->activeMatch();
        $meeting = $match->meetings()->create(['recorded_by' => $mentor->id, 'meeting_on' => now(), 'topics_discussed' => 'A valid shared discussion record.']);
        $outsider = User::factory()->create();

        $this->actingAs($outsider)->put(route('meetings.update', $meeting), ['meeting_on' => now()->toDateString(), 'topics_discussed' => 'Attempted unauthorised change.'])->assertForbidden();
    }

    public function test_participant_can_correct_a_shared_meeting_record(): void
    {
        Notification::fake();
        [$match, , $mentee] = $this->activeMatch();
        $meeting = $match->meetings()->create(['recorded_by' => $mentee->id, 'meeting_on' => now()->subDay(), 'topics_discussed' => 'Initial shared meeting notes.']);

        $this->actingAs($mentee)->put(route('meetings.update', $meeting), [
            'meeting_on' => now()->subDay()->toDateString(), 'duration_minutes' => 60,
            'topics_discussed' => 'Corrected shared notes with enough context for both participants.',
            'next_meeting_on' => now()->addWeek()->toDateString(),
        ])->assertRedirect();

        $this->assertDatabaseHas('mentoring_meetings', ['id' => $meeting->id, 'last_updated_by' => $mentee->id, 'duration_minutes' => 60]);
    }

    public function test_meeting_action_can_become_an_owned_milestone(): void
    {
        Notification::fake();
        [$match, $mentor, $mentee] = $this->activeMatch();
        $meeting = $match->meetings()->create(['recorded_by' => $mentor->id, 'meeting_on' => now(), 'topics_discussed' => 'Agreed an action that should be tracked.']);
        $goal = MentoringGoal::create(['mentoring_match_id' => $match->id, 'created_by' => $mentor->id, 'title' => 'Develop practical leadership capability']);

        $this->actingAs($mentor)->post(route('meetings.milestone.store', $meeting), [
            'mentoring_goal_id' => $goal->id, 'title' => 'Prepare the first leadership reflection',
            'description' => 'Action agreed during the meeting.', 'owner_id' => $mentee->id,
            'due_on' => now()->addWeeks(2)->toDateString(),
        ])->assertRedirect();

        $this->assertDatabaseHas('goal_milestones', ['mentoring_goal_id' => $goal->id, 'owner_id' => $mentee->id, 'title' => 'Prepare the first leadership reflection']);
        Notification::assertSentTo($mentee, MatchActionNotification::class);
    }

    private function activeMatch(): array
    {
        $mentor = User::factory()->create(['roles' => ['mentor']]);
        $mentee = User::factory()->create(['roles' => ['mentee']]);
        $match = MentoringMatch::create(['mentor_id' => $mentor->id, 'mentee_id' => $mentee->id, 'status' => 'active', 'tier' => 'matched', 'rationale' => 'A suitable active mentoring relationship.', 'started_at' => now()]);

        return [$match, $mentor, $mentee];
    }
}
