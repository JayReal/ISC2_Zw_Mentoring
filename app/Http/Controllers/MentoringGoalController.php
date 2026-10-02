<?php

namespace App\Http\Controllers;

use App\Models\MatchActivity;
use App\Models\MentoringGoal;
use App\Models\MentoringMatch;
use App\Notifications\MatchActionNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MentoringGoalController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(['mentoring_match_id' => ['required', 'exists:mentoring_matches,id'], 'title' => ['required', 'string', 'max:180'], 'description' => ['nullable', 'string', 'max:3000'], 'target_date' => ['nullable', 'date']]);
        $match = MentoringMatch::findOrFail($validated['mentoring_match_id']);
        $this->authoriseParticipant($request, $match);
        $goal = $match->goals()->create([...$validated, 'created_by' => $request->user()->id, 'last_updated_by' => $request->user()->id, 'status' => 'proposed']);
        MatchActivity::create(['mentoring_match_id' => $match->id, 'mentoring_goal_id' => $goal->id, 'user_id' => $request->user()->id, 'type' => 'goal_created', 'body' => 'Added goal: '.$goal->title]);
        $this->notifyCounterpart($request, $match, 'added a mentoring goal', $goal->title);

        return back()->with('status', 'Goal added to the shared plan.');
    }

    public function update(Request $request, MentoringGoal $goal): RedirectResponse
    {
        $goal->load('mentoringMatch');
        $this->authoriseParticipant($request, $goal->mentoringMatch);
        $validated = $request->validate(['status' => ['required', 'in:proposed,discussed,agreed,in-progress,completed,paused'], 'note' => ['nullable', 'string', 'max:2000']]);
        $updates = ['status' => $validated['status'], 'last_updated_by' => $request->user()->id];
        if ($validated['status'] === 'discussed') {
            $updates['discussed_at'] = now();
        }
        if ($validated['status'] === 'agreed') {
            $updates['discussed_at'] ??= now();
            $updates['agreed_at'] = now();
        }
        if ($validated['status'] === 'completed') {
            $updates['completed_at'] = now();
        }
        $goal->update($updates);
        $message = 'Marked “'.$goal->title.'” as '.str_replace('-', ' ', $validated['status']).'.';
        MatchActivity::create(['mentoring_match_id' => $goal->mentoring_match_id, 'mentoring_goal_id' => $goal->id, 'user_id' => $request->user()->id, 'type' => 'goal_status', 'body' => $validated['note'] ?: $message]);
        $this->notifyCounterpart($request, $goal->mentoringMatch, 'updated a mentoring goal', $validated['note'] ?: $message);

        return back()->with('status', 'Goal status updated.');
    }

    private function authoriseParticipant(Request $request, MentoringMatch $match): void
    {
        abort_unless($match->involves($request->user()) && $match->status === 'active', 403);
    }

    private function notifyCounterpart(Request $request, MentoringMatch $match, string $action, string $message): void
    {
        $match->counterpartFor($request->user())->notify(new MatchActionNotification($match->id, $request->user()->name, $action, $message));
        $match->update(['last_activity_at' => now()]);
    }
}
