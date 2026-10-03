<?php

namespace Tests\Feature;

use App\Models\MentoringGoal;
use App\Models\MentoringMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MatchWorkspaceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_only_match_participants_can_open_workspace(): void
    {
        [$match, $mentor, $mentee] = $this->activeMatch();
        $outsider = User::factory()->create();

        $this->actingAs($mentor)->get(route('matches.show', $match))->assertOk()->assertSee($mentee->name)->assertSee('Your mentoring command centre')->assertSee('Create your mentoring charter');
        $this->actingAs($outsider)->get(route('matches.show', $match))->assertForbidden();
    }

    public function test_both_participants_must_accept_before_match_becomes_active(): void
    {
        $mentor = User::factory()->create(['roles' => ['mentor']]);
        $mentee = User::factory()->create(['roles' => ['mentee']]);
        $match = MentoringMatch::create(['mentor_id' => $mentor->id, 'mentee_id' => $mentee->id, 'tier' => 'matched', 'status' => 'proposed', 'rationale' => 'Goals and availability align for a structured mentoring relationship.']);

        $this->actingAs($mentor)->put(route('matches.confirmation', $match), ['decision' => 'accept'])->assertRedirect();
        $this->assertSame('pending-confirmation', $match->fresh()->status);
        $this->actingAs($mentee)->put(route('matches.confirmation', $match), ['decision' => 'accept'])->assertRedirect();
        $this->assertSame('active', $match->fresh()->status);
    }

    public function test_participant_can_create_agree_and_complete_shared_plan_items(): void
    {
        [$match, $mentor, $mentee] = $this->activeMatch();

        $this->actingAs($mentor)->post(route('goals.store'), ['mentoring_match_id' => $match->id, 'title' => 'Build a six-month leadership development plan', 'description' => 'Agree practical steps and evidence of progress.'])->assertRedirect();
        $goal = $match->goals()->firstOrFail();
        $this->actingAs($mentor)->put(route('goals.update', $goal), ['status' => 'agreed', 'note' => 'Discussed and agreed during our first meeting.'])->assertRedirect();
        $this->assertSame('discussed', $goal->fresh()->status);
        $this->actingAs($mentee)->put(route('goals.update', $goal), ['status' => 'agreed', 'note' => 'I confirm this goal reflects our discussion.'])->assertRedirect();
        $this->actingAs($mentor)->post(route('milestones.store', $goal), ['title' => 'Draft development plan'])->assertRedirect();
        $milestone = $goal->milestones()->firstOrFail();
        $this->actingAs($mentor)->put(route('milestones.update', $milestone), ['status' => 'completed', 'note' => 'Draft reviewed together.'])->assertRedirect();

        $this->assertSame('agreed', $goal->fresh()->status);
        $this->assertSame('completed', $milestone->fresh()->status);
        $this->assertDatabaseCount('match_activities', 5);
    }

    public function test_goal_cannot_be_completed_while_a_milestone_remains_open(): void
    {
        [$match, $mentor] = $this->activeMatch();
        $goal = MentoringGoal::create(['mentoring_match_id' => $match->id, 'created_by' => $mentor->id, 'title' => 'Build practical leadership confidence']);
        $goal->milestones()->create(['created_by' => $mentor->id, 'title' => 'Lead a structured retrospective']);

        $this->actingAs($mentor)->put(route('goals.update', $goal), ['status' => 'completed'])
            ->assertRedirect()
            ->assertSessionHasErrors('status');

        $this->assertNotSame('completed', $goal->fresh()->status);
    }

    public function test_participant_can_update_action_ownership_date_and_status(): void
    {
        [$match, $mentor, $mentee] = $this->activeMatch();
        $goal = MentoringGoal::create(['mentoring_match_id' => $match->id, 'created_by' => $mentor->id, 'title' => 'Strengthen practical delivery skills']);
        $milestone = $goal->milestones()->create(['created_by' => $mentor->id, 'owner_id' => $mentor->id, 'title' => 'Complete a guided practice task']);

        $this->actingAs($mentee)->put(route('milestones.update', $milestone), [
            'status' => 'in-progress',
            'owner_id' => '',
            'due_on' => today()->addWeek()->toDateString(),
            'note' => 'We agreed to treat this as a shared action.',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $milestone->refresh();
        $this->assertSame('in-progress', $milestone->status);
        $this->assertNull($milestone->owner_id);
        $this->assertTrue($milestone->due_on->isSameDay(today()->addWeek()));
    }

    public function test_blocked_action_requires_an_explanatory_note(): void
    {
        [$match, $mentor] = $this->activeMatch();
        $goal = MentoringGoal::create(['mentoring_match_id' => $match->id, 'created_by' => $mentor->id, 'title' => 'Prepare for a practical assessment']);
        $milestone = $goal->milestones()->create(['created_by' => $mentor->id, 'title' => 'Complete the first practice assessment']);

        $this->actingAs($mentor)->put(route('milestones.update', $milestone), ['status' => 'blocked'])
            ->assertSessionHasErrors('note');
    }

    public function test_workspace_shows_progress_and_accountability_summary(): void
    {
        [$match, $mentor] = $this->activeMatch();
        $goal = MentoringGoal::create(['mentoring_match_id' => $match->id, 'created_by' => $mentor->id, 'title' => 'Develop a measurable mentoring outcome']);
        $goal->milestones()->create(['created_by' => $mentor->id, 'owner_id' => $mentor->id, 'title' => 'Document evidence of progress', 'due_on' => today()->addDays(3)]);

        $this->actingAs($mentor)->get(route('matches.show', $match))
            ->assertOk()
            ->assertSee('Progress and accountability')
            ->assertSee('Assigned to you')
            ->assertSee('Document evidence of progress')
            ->assertSee('0% complete');
    }

    private function activeMatch(): array
    {
        $mentor = User::factory()->create(['roles' => ['mentor']]);
        $mentee = User::factory()->create(['roles' => ['mentee']]);
        $match = MentoringMatch::create(['mentor_id' => $mentor->id, 'mentee_id' => $mentee->id, 'tier' => 'matched', 'status' => 'active', 'rationale' => 'Goals and availability align for a structured mentoring relationship.', 'mentor_confirmed_at' => now(), 'mentee_confirmed_at' => now(), 'started_at' => now()]);

        return [$match, $mentor, $mentee];
    }
}
