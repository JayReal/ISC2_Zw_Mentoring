<?php

namespace Tests\Feature;

use App\Models\MentoringMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MentoringOperationsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_both_participants_can_agree_the_charter(): void
    {
        Notification::fake();
        [$mentor, $mentee, $match] = $this->activeMatch();

        $this->actingAs($mentor)->put(route('matches.charter.update', $match), [
            'meeting_cadence' => 'Monthly', 'communication_method' => 'Email and Teams',
            'response_expectations' => 'Respond within three working days.', 'cancellation_expectations' => 'Give at least one working day notice.',
            'confidentiality_boundaries' => 'Keep personal discussions within the mentoring relationship.', 'escalation_route' => 'Contact the programme lead for support.',
            'review_on' => now()->addMonth()->toDateString(), 'closure_on' => now()->addMonths(6)->toDateString(),
        ])->assertRedirect();

        $this->actingAs($mentee)->post(route('matches.charter.confirm', $match))->assertRedirect();
        $this->assertNotNull($match->charter()->firstOrFail()->mentor_confirmed_at);
        $this->assertNotNull($match->charter()->firstOrFail()->mentee_confirmed_at);
    }

    public function test_monthly_check_in_is_updated_once_and_can_raise_support(): void
    {
        Notification::fake();
        [$mentor, , $match] = $this->activeMatch();
        $payload = ['meeting_held' => true, 'usefulness_score' => 4, 'needs_support' => true, 'comment' => 'Please help us reset expectations and agree a better meeting rhythm.'];

        $this->actingAs($mentor)->post(route('matches.check-in.store', $match), $payload)->assertRedirect();
        $this->actingAs($mentor)->post(route('matches.check-in.store', $match), [...$payload, 'usefulness_score' => 5])->assertRedirect();

        $this->assertDatabaseCount('mentoring_check_ins', 1);
        $this->assertDatabaseCount('mentoring_support_requests', 1);
        $this->assertSame(5, $match->checkIns()->firstOrFail()->usefulness_score);
    }

    public function test_rematch_request_is_confidential_and_moves_match_to_attention(): void
    {
        Notification::fake();
        [, $mentee, $match] = $this->activeMatch();

        $this->actingAs($mentee)->post(route('matches.support.store', $match), ['type' => 'rematch', 'reason' => 'Our working styles are not aligning and I would like confidential help finding another match.'])->assertRedirect();

        $this->assertDatabaseHas('mentoring_matches', ['id' => $match->id, 'status' => 'rematch-requested']);
        $supportRequest = $match->supportRequests()->firstOrFail();
        $this->assertSame('rematch', $supportRequest->type);
        $this->assertNotSame($supportRequest->reason, $supportRequest->getRawOriginal('reason'));
    }

    private function activeMatch(): array
    {
        $mentor = User::factory()->create(['roles' => ['mentor']]);
        $mentee = User::factory()->create(['roles' => ['mentee']]);
        $match = MentoringMatch::create(['mentor_id' => $mentor->id, 'mentee_id' => $mentee->id, 'tier' => 'matched', 'status' => 'active', 'rationale' => 'Compatible goals and availability for this mentoring relationship.', 'started_at' => now()]);

        return [$mentor, $mentee, $match];
    }
}
