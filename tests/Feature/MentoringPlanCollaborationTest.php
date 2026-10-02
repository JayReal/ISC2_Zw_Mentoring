<?php

namespace Tests\Feature;

use App\Models\MentoringGoal;
use App\Models\MentoringMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MentoringPlanCollaborationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_editing_goal_details_resets_both_agreements(): void
    {
        [$match, $mentor] = $this->activeMatch();
        $goal = MentoringGoal::create(['mentoring_match_id' => $match->id, 'created_by' => $mentor->id, 'title' => 'Original goal', 'status' => 'agreed', 'mentor_agreed_at' => now(), 'mentee_agreed_at' => now(), 'agreed_at' => now()]);

        $this->actingAs($mentor)->put(route('goals.update', $goal), ['status' => 'agreed', 'title' => 'Revised shared goal', 'description' => 'The scope changed after discussion.'])->assertRedirect();

        $goal->refresh();
        $this->assertSame('proposed', $goal->status);
        $this->assertNull($goal->mentor_agreed_at);
        $this->assertNull($goal->mentee_agreed_at);
    }

    public function test_milestone_owner_must_be_a_match_participant(): void
    {
        [$match, $mentor] = $this->activeMatch();
        $outsider = User::factory()->create();
        $goal = MentoringGoal::create(['mentoring_match_id' => $match->id, 'created_by' => $mentor->id, 'title' => 'Shared goal']);

        $this->actingAs($mentor)->post(route('milestones.store', $goal), ['title' => 'Owned milestone', 'owner_id' => $outsider->id])->assertSessionHasErrors('owner_id');
        $this->assertDatabaseCount('goal_milestones', 0);
    }

    private function activeMatch(): array
    {
        $mentor = User::factory()->create(['roles' => ['mentor']]);
        $mentee = User::factory()->create(['roles' => ['mentee']]);
        $match = MentoringMatch::create(['mentor_id' => $mentor->id, 'mentee_id' => $mentee->id, 'tier' => 'matched', 'status' => 'active', 'rationale' => 'A suitable active mentoring relationship.', 'started_at' => now()]);

        return [$match, $mentor, $mentee];
    }
}
