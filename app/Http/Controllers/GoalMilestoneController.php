<?php

namespace App\Http\Controllers;

use App\Models\GoalMilestone;
use App\Models\MatchActivity;
use App\Models\MentoringGoal;
use App\Notifications\MatchActionNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GoalMilestoneController extends Controller
{
    public function store(Request $request, MentoringGoal $goal): RedirectResponse
    {
        $goal->load('mentoringMatch');
        abort_unless($goal->mentoringMatch->involves($request->user()) && $goal->mentoringMatch->status === 'active', 403);
        $validated = $request->validate(['title' => ['required', 'string', 'max:180'], 'description' => ['nullable', 'string', 'max:2000'], 'due_on' => ['nullable', 'date'], 'owner_id' => ['nullable', Rule::in([$goal->mentoringMatch->mentor_id, $goal->mentoringMatch->mentee_id])]]);
        $milestone = $goal->milestones()->create([...$validated, 'created_by' => $request->user()->id]);
        MatchActivity::create(['mentoring_match_id' => $goal->mentoring_match_id, 'mentoring_goal_id' => $goal->id, 'goal_milestone_id' => $milestone->id, 'user_id' => $request->user()->id, 'type' => 'milestone_created', 'body' => 'Added milestone: '.$milestone->title]);
        $goal->mentoringMatch->counterpartFor($request->user())->notify(new MatchActionNotification($goal->mentoring_match_id, $request->user()->name, 'added a milestone', $milestone->title));
        $goal->mentoringMatch->update(['last_activity_at' => now()]);

        return back()->with('status', 'Milestone added.');
    }

    public function update(Request $request, GoalMilestone $milestone): RedirectResponse
    {
        $milestone->load('goal.mentoringMatch');
        $match = $milestone->goal->mentoringMatch;
        abort_unless($match->involves($request->user()) && $match->status === 'active', 403);
        $validated = $request->validate(['status' => ['required', 'in:open,in-progress,completed,blocked'], 'owner_id' => ['sometimes', 'nullable', Rule::in([$match->mentor_id, $match->mentee_id])], 'note' => ['nullable', 'string', 'max:2000']]);
        $milestone->update(['status' => $validated['status'], 'owner_id' => $validated['owner_id'] ?? $milestone->owner_id, 'completed_by' => $validated['status'] === 'completed' ? $request->user()->id : null, 'completed_at' => $validated['status'] === 'completed' ? now() : null]);
        $message = $validated['note'] ?: 'Marked “'.$milestone->title.'” as '.str_replace('-', ' ', $validated['status']).'.';
        MatchActivity::create(['mentoring_match_id' => $match->id, 'mentoring_goal_id' => $milestone->mentoring_goal_id, 'goal_milestone_id' => $milestone->id, 'user_id' => $request->user()->id, 'type' => 'milestone_status', 'body' => $message]);
        $match->counterpartFor($request->user())->notify(new MatchActionNotification($match->id, $request->user()->name, 'updated a milestone', $message));
        $match->update(['last_activity_at' => now()]);

        return back()->with('status', 'Milestone updated.');
    }
}
