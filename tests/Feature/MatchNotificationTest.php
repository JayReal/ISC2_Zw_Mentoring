<?php

namespace Tests\Feature;

use App\Models\MentoringMatch;
use App\Models\User;
use App\Notifications\MatchActionNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MatchNotificationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_counterpart_is_notified_when_shared_note_is_added(): void
    {
        Notification::fake();
        $mentor = User::factory()->create(['roles' => ['mentor']]);
        $mentee = User::factory()->create(['roles' => ['mentee']]);
        $match = MentoringMatch::create(['mentor_id' => $mentor->id, 'mentee_id' => $mentee->id, 'tier' => 'matched', 'status' => 'active', 'rationale' => 'Goals and availability align for a structured mentoring relationship.']);

        $this->actingAs($mentor)->post(route('matches.activities.store', $match), ['body' => 'Please review the draft objective before our next session.'])->assertRedirect();

        Notification::assertSentTo($mentee, MatchActionNotification::class);
        Notification::assertNotSentTo($mentor, MatchActionNotification::class);
        $this->assertDatabaseHas('match_activities', ['mentoring_match_id' => $match->id, 'user_id' => $mentor->id, 'type' => 'comment']);
    }
}
