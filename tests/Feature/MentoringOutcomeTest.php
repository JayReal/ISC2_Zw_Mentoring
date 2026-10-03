<?php

namespace Tests\Feature;

use App\Models\MentoringGoal;
use App\Models\MentoringMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MentoringOutcomeTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_participant_can_view_confirmed_outcome_record(): void
    {
        [$match, $mentor, $mentee] = $this->closedMatch();
        $goal = MentoringGoal::create(['mentoring_match_id' => $match->id, 'created_by' => $mentor->id, 'title' => 'Strengthen leadership communication', 'status' => 'completed', 'completed_at' => now()]);
        $goal->milestones()->create(['created_by' => $mentor->id, 'owner_id' => $mentee->id, 'title' => 'Lead a team retrospective', 'status' => 'completed', 'completed_at' => now()]);

        $this->actingAs($mentee)->get(route('matches.outcome', $match))
            ->assertOk()
            ->assertSee('Mentoring outcome record')
            ->assertSee('ISC2-ZW-MENT-'.str_pad((string) $match->id, 6, '0', STR_PAD_LEFT))
            ->assertSee('Strengthen leadership communication')
            ->assertSee('Lead a team retrospective')
            ->assertSee('Confirmed by both participants');
    }

    public function test_outcome_record_excludes_private_and_confidential_information(): void
    {
        [$match, $mentor, $mentee] = $this->closedMatch();
        $match->checkIns()->create(['user_id' => $mentee->id, 'meeting_held' => true, 'usefulness_score' => 2, 'needs_support' => true, 'comment' => 'Private check-in content must not appear.', 'period_month' => now()->startOfMonth()]);
        $match->supportRequests()->create(['requested_by' => $mentee->id, 'type' => 'support', 'reason' => 'Confidential support content must not appear.']);

        $this->actingAs($mentor)->get(route('matches.outcome', $match))
            ->assertOk()
            ->assertDontSee('Private check-in content')
            ->assertDontSee('Confidential support content');
    }

    public function test_outsider_cannot_view_outcome_record(): void
    {
        [$match] = $this->closedMatch();
        $outsider = User::factory()->create();

        $this->actingAs($outsider)->get(route('matches.outcome', $match))->assertForbidden();
    }

    public function test_outcome_record_is_unavailable_until_closure_is_complete(): void
    {
        [$match, $mentor] = $this->closedMatch();
        $match->update(['status' => 'active', 'closed_at' => null]);
        $match->closure()->update(['mentee_confirmed_at' => null]);

        $this->actingAs($mentor)->get(route('matches.outcome', $match))->assertNotFound();
    }

    private function closedMatch(): array
    {
        $mentor = User::factory()->create(['roles' => ['mentor']]);
        $mentee = User::factory()->create(['roles' => ['mentee']]);
        $match = MentoringMatch::create([
            'mentor_id' => $mentor->id,
            'mentee_id' => $mentee->id,
            'tier' => 'matched',
            'status' => 'closed',
            'rationale' => 'The participants had compatible professional development goals.',
            'mentor_confirmed_at' => now()->subMonths(6),
            'mentee_confirmed_at' => now()->subMonths(6),
            'started_at' => now()->subMonths(6),
            'closed_at' => now(),
        ]);
        $match->closure()->create([
            'initiated_by' => $mentor->id,
            'last_updated_by' => $mentee->id,
            'reason' => 'goal-achieved',
            'summary' => 'The participants completed the agreed plan and reviewed the learning evidence together.',
            'next_steps' => 'Continue applying the development plan independently.',
            'mentor_confirmed_at' => now(),
            'mentee_confirmed_at' => now(),
        ]);

        return [$match, $mentor, $mentee];
    }
}
