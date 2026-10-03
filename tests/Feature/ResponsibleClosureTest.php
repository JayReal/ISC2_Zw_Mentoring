<?php

namespace Tests\Feature;

use App\Models\MentoringMatch;
use App\Models\User;
use App\Notifications\MatchActionNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ResponsibleClosureTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_both_participants_confirm_before_relationship_closes(): void
    {
        Notification::fake();
        [$match, $mentor, $mentee] = $this->activeMatch();

        $this->actingAs($mentor)->put(route('matches.closure.update', $match), [
            'reason' => 'goal-achieved',
            'summary' => 'We completed the agreed learning plan and reviewed the evidence together.',
            'next_steps' => 'Continue independent practice and reconnect through chapter activities.',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $closure = $match->closure()->firstOrFail();
        $this->assertNotNull($closure->mentor_confirmed_at);
        $this->assertNull($closure->mentee_confirmed_at);
        $this->assertSame('active', $match->fresh()->status);
        Notification::assertSentTo($mentee, MatchActionNotification::class);

        $this->actingAs($mentee)->post(route('matches.closure.confirm', $match))->assertRedirect();

        $this->assertSame('closed', $match->fresh()->status);
        $this->assertNotNull($match->fresh()->closed_at);
        $this->assertNotNull($closure->fresh()->mentee_confirmed_at);
    }

    public function test_revising_summary_resets_the_other_participants_confirmation(): void
    {
        Notification::fake();
        [$match, $mentor, $mentee] = $this->activeMatch();
        $match->closure()->create([
            'initiated_by' => $mentor->id,
            'last_updated_by' => $mentor->id,
            'reason' => 'planned',
            'summary' => 'The planned mentoring period is complete and both participants reviewed progress.',
            'mentor_confirmed_at' => now(),
            'mentee_confirmed_at' => now(),
        ]);

        $this->actingAs($mentee)->put(route('matches.closure.update', $match), [
            'reason' => 'planned',
            'summary' => 'The planned period is complete and we clarified the final learning outcomes together.',
            'next_steps' => 'Maintain the development plan independently.',
        ])->assertRedirect();

        $closure = $match->closure()->firstOrFail();
        $this->assertNull($closure->mentor_confirmed_at);
        $this->assertNotNull($closure->mentee_confirmed_at);
        $this->assertSame('active', $match->fresh()->status);
    }

    public function test_non_participant_cannot_prepare_or_confirm_closure(): void
    {
        [$match] = $this->activeMatch();
        $outsider = User::factory()->create();

        $this->actingAs($outsider)->put(route('matches.closure.update', $match), [
            'reason' => 'other',
            'summary' => 'This request must not be accepted because the user is not part of the match.',
        ])->assertForbidden();
        $this->actingAs($outsider)->post(route('matches.closure.confirm', $match))->assertForbidden();
    }

    private function activeMatch(): array
    {
        $mentor = User::factory()->create(['roles' => ['mentor']]);
        $mentee = User::factory()->create(['roles' => ['mentee']]);
        $match = MentoringMatch::create([
            'mentor_id' => $mentor->id,
            'mentee_id' => $mentee->id,
            'tier' => 'matched',
            'status' => 'active',
            'rationale' => 'The participants have compatible development goals and availability.',
            'mentor_confirmed_at' => now(),
            'mentee_confirmed_at' => now(),
            'started_at' => now()->subMonths(6),
        ]);

        return [$match, $mentor, $mentee];
    }
}
