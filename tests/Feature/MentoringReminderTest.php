<?php

namespace Tests\Feature;

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

    private function proposal($expiresAt): array
    {
        $mentor = User::factory()->create(['roles' => ['mentor']]);
        $mentee = User::factory()->create(['roles' => ['mentee']]);
        $match = MentoringMatch::create(['mentor_id' => $mentor->id, 'mentee_id' => $mentee->id, 'status' => 'pending-confirmation', 'tier' => 'matched', 'rationale' => 'A suitable proposal awaiting confirmation.', 'expires_at' => $expiresAt]);

        return [$mentor, $mentee, $match];
    }
}
