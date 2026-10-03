<?php

namespace Tests\Feature;

use App\Models\MentoringMatch;
use App\Models\User;
use App\Notifications\MatchActionNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
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

    public function test_routine_activity_can_be_in_app_only(): void
    {
        $notification = new MatchActionNotification(10, 'A participant', 'updated a milestone', 'Progress recorded.', false, 'milestone', 'plan');

        $this->assertSame(['database'], $notification->via(new User));
        $this->assertStringEndsWith('/mentoring/10#plan', $notification->toArray(new User)['url']);
    }

    public function test_participant_can_mark_all_updates_as_read(): void
    {
        $user = User::factory()->create();
        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => MatchActionNotification::class,
            'data' => ['actor_name' => 'Programme', 'action' => 'sent an update', 'message' => 'Review it.', 'url' => route('dashboard')],
        ]);

        $this->actingAs($user)->put(route('notifications.read-all'))->assertRedirect();

        $this->assertSame(0, $user->unreadNotifications()->count());
    }
}
