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
        $validated = $request->validate(['status' => ['required', 'in:proposed,discussed,agreed,in-progress,completed,paused'], 'title' => ['sometimes', 'required', 'string', 'max:180'], 'description' => ['sometimes', 'nullable', 'string', 'max:3000'], 'target_date' => ['sometimes', 'nullable', 'date'], 'note' => ['nullable', 'string', 'max:2000']]);
        $contentChanged = array_key_exists('title', $validated) || array_key_exists('description', $validated) || array_key_exists('target_date', $validated);
        $updates = ['status' => $validated['status'], 'last_updated_by' => $request->user()->id, ...array_intersect_key($validated, array_flip(['title', 'description', 'target_date']))];
        if ($contentChanged) {
            $updates = [...$updates, 'status' => 'proposed', 'mentor_agreed_at' => null, 'mentee_agreed_at' => null, 'agreed_at' => null];
        }
        if (! $contentChanged && $validated['status'] === 'discussed') {
            $updates['discussed_at'] = now();
        }
        if (! $contentChanged && $validated['status'] === 'agreed') {
            $updates['discussed_at'] ??= now();
            $updates[(int) $request->user()->id === (int) $goal->mentoringMatch->mentor_id ? 'mentor_agreed_at' : 'mentee_agreed_at'] = now();
        }
        if (! $contentChanged && $validated['status'] === 'completed') {
            $updates['completed_at'] = now();
        }
        $goal->update($updates);
        if (! $contentChanged && $validated['status'] === 'agreed' && $goal->mentor_agreed_at && $goal->mentee_agreed_at) {
            $goal->update(['status' => 'agreed', 'agreed_at' => now()]);
        } elseif (! $contentChanged && $validated['status'] === 'agreed') {
            $goal->update(['status' => 'discussed']);
        }
        $message = 'Marked “'.$goal->title.'” as '.str_replace('-', ' ', $validated['status']).'.';
        MatchActivity::create(['mentoring_match_id' => $goal->mentoring_match_id, 'mentoring_goal_id' => $goal->id, 'user_id' => $request->user()->id, 'type' => 'goal_status', 'body' => ($validated['note'] ?? null) ?: $message]);
        $this->notifyCounterpart($request, $goal->mentoringMatch, 'updated a mentoring goal', ($validated['note'] ?? null) ?: $message);

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
