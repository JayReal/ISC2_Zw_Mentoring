<?php

namespace Tests\Feature;

use App\Models\MentoringGoal;
use App\Models\MentoringMatch;
use App\Models\User;
use App\Notifications\MatchActionNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MentoringReminderTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_due_proposal_sends_one_confirmation_reminder(): void
    {
        Notification::fake();
        [$mentor, $mentee, $match] = $this->proposal(now()->addDay());

        $this->artisan('app:send-mentoring-reminders')->assertSuccessful();

        Notification::assertSentTo([$mentor, $mentee], MatchActionNotification::class);
        $this->assertNotNull($match->fresh()->confirmation_reminded_at);
    }

    public function test_unanswered_expired_proposal_is_closed(): void
    {
        Notification::fake();
        [, , $match] = $this->proposal(now()->subMinute());

        $this->artisan('app:send-mentoring-reminders')->assertSuccessful();

        $this->assertSame('expired', $match->fresh()->status);
    }

    public function test_due_milestone_reminder_is_sent_once_to_the_owner(): void
    {
        Notification::fake();
        $mentor = User::factory()->create(['roles' => ['mentor']]);
        $mentee = User::factory()->create(['roles' => ['mentee']]);
        $match = MentoringMatch::create(['mentor_id' => $mentor->id, 'mentee_id' => $mentee->id, 'status' => 'active', 'tier' => 'matched', 'rationale' => 'An active relationship with a due milestone.', 'started_at' => now()]);
        $goal = MentoringGoal::create(['mentoring_match_id' => $match->id, 'created_by' => $mentor->id, 'title' => 'Build practical leadership capability']);
        $milestone = $goal->milestones()->create(['created_by' => $mentor->id, 'owner_id' => $mentee->id, 'title' => 'Prepare the leadership reflection', 'due_on' => today()->addDay()]);

        $this->artisan('app:send-mentoring-reminders')->assertSuccessful();
        $this->artisan('app:send-mentoring-reminders')->assertSuccessful();

        Notification::assertSentToTimes($mentee, MatchActionNotification::class, 1);
        Notification::assertNotSentTo($mentor, MatchActionNotification::class);
        $this->assertNotNull($milestone->fresh()->reminder_sent_at);
    }

    private function proposal($expiresAt): array
    {
        $mentor = User::factory()->create(['roles' => ['mentor']]);
        $mentee = User::factory()->create(['roles' => ['mentee']]);
        $match = MentoringMatch::create(['mentor_id' => $mentor->id, 'mentee_id' => $mentee->id, 'status' => 'pending-confirmation', 'tier' => 'matched', 'rationale' => 'A suitable proposal awaiting confirmation.', 'expires_at' => $expiresAt]);

        return [$mentor, $mentee, $match];
    }
}
