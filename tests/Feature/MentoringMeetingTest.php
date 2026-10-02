<?php

namespace Tests\Feature;

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

    private function activeMatch(): array
    {
        $mentor = User::factory()->create(['roles' => ['mentor']]);
        $mentee = User::factory()->create(['roles' => ['mentee']]);
        $match = MentoringMatch::create(['mentor_id' => $mentor->id, 'mentee_id' => $mentee->id, 'status' => 'active', 'tier' => 'matched', 'rationale' => 'A suitable active mentoring relationship.', 'started_at' => now()]);

        return [$match, $mentor, $mentee];
    }
}
