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
        $validated = $request->validate([
            'status' => ['required', 'in:open,in-progress,completed,blocked'],
            'owner_id' => ['sometimes', 'nullable', Rule::in([$match->mentor_id, $match->mentee_id])],
            'title' => ['sometimes', 'required', 'string', 'max:180'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'due_on' => ['sometimes', 'nullable', 'date'],
            'note' => ['nullable', 'required_if:status,blocked', 'string', 'max:2000'],
        ]);
        $wasCompleted = $milestone->status === 'completed';
        $updates = array_intersect_key($validated, array_flip(['status', 'owner_id', 'title', 'description', 'due_on']));
        if (array_key_exists('due_on', $validated) && $milestone->due_on?->toDateString() !== ($validated['due_on'] ?: null)) {
            $updates['reminder_sent_at'] = null;
        }
        if ($wasCompleted && $validated['status'] !== 'completed') {
            $updates['reminder_sent_at'] = null;
        }
        $updates['completed_by'] = $validated['status'] === 'completed' ? ($wasCompleted ? $milestone->completed_by : $request->user()->id) : null;
        $updates['completed_at'] = $validated['status'] === 'completed' ? ($wasCompleted ? $milestone->completed_at : now()) : null;
        $milestone->update($updates);
        $message = ($validated['note'] ?? null) ?: 'Updated “'.$milestone->title.'” and marked it as '.str_replace('-', ' ', $validated['status']).'.';
        MatchActivity::create(['mentoring_match_id' => $match->id, 'mentoring_goal_id' => $milestone->mentoring_goal_id, 'goal_milestone_id' => $milestone->id, 'user_id' => $request->user()->id, 'type' => 'milestone_status', 'body' => $message]);
        $match->counterpartFor($request->user())->notify(new MatchActionNotification($match->id, $request->user()->name, 'updated a milestone', $message, false, 'milestone', 'plan'));
        $match->update(['last_activity_at' => now()]);

        $statusMessage = match ($milestone->status) {
            'completed' => 'Milestone marked complete.',
            'in-progress' => 'Milestone is now in progress.',
            'blocked' => 'Blocker recorded for this milestone.',
            default => 'Milestone updated.',
        };

        return back()->with('status', $statusMessage.' The other participant was notified.');
    }
}
