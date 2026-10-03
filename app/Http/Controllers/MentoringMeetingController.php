<?php

namespace App\Http\Controllers;

use App\Models\MatchActivity;
use App\Models\MentoringMatch;
use App\Models\MentoringMeeting;
use App\Notifications\MatchActionNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MentoringMeetingController extends Controller
{
    public function store(Request $request, MentoringMatch $match): RedirectResponse
    {
        $this->authorise($request, $match);
        $validated = $this->validated($request);
        $meeting = $match->meetings()->create([...$validated, 'recorded_by' => $request->user()->id, 'last_updated_by' => $request->user()->id]);
        $this->recordUpdate($request, $match, 'Recorded mentoring meeting on '.$meeting->meeting_on->format('j M Y').'.');

        return back()->with('status', 'Meeting record added to the shared workspace.');
    }

    public function update(Request $request, MentoringMeeting $meeting): RedirectResponse
    {
        $meeting->load('mentoringMatch');
        $this->authorise($request, $meeting->mentoringMatch);
        $meeting->update([...$this->validated($request), 'last_updated_by' => $request->user()->id]);
        $this->recordUpdate($request, $meeting->mentoringMatch, 'Updated the mentoring meeting record for '.$meeting->meeting_on->format('j M Y').'.');

        return back()->with('status', 'Meeting record updated.');
    }

    public function createMilestone(Request $request, MentoringMeeting $meeting): RedirectResponse
    {
        $meeting->load('mentoringMatch');
        $match = $meeting->mentoringMatch;
        $this->authorise($request, $match);
        $validated = $request->validate([
            'mentoring_goal_id' => ['required', Rule::exists('mentoring_goals', 'id')->where('mentoring_match_id', $match->id)],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'due_on' => ['nullable', 'date'],
            'owner_id' => ['nullable', Rule::in([$match->mentor_id, $match->mentee_id])],
        ]);
        $goal = $match->goals()->findOrFail($validated['mentoring_goal_id']);
        $milestone = $goal->milestones()->create([...$validated, 'created_by' => $request->user()->id]);
        $message = 'Created milestone "'.$milestone->title.'" from the '.$meeting->meeting_on->format('j M Y').' meeting.';
        MatchActivity::create(['mentoring_match_id' => $match->id, 'mentoring_goal_id' => $goal->id, 'goal_milestone_id' => $milestone->id, 'user_id' => $request->user()->id, 'type' => 'meeting_action', 'body' => $message]);
        $match->update(['last_activity_at' => now()]);
        $match->counterpartFor($request->user())->notify(new MatchActionNotification($match->id, $request->user()->name, 'converted a meeting action into a milestone', $message));

        return back()->with('status', 'Meeting action added to the shared plan.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'meeting_on' => ['required', 'date', 'before_or_equal:today'],
            'duration_minutes' => ['nullable', 'integer', 'between:1,600'],
            'topics_discussed' => ['required', 'string', 'min:10', 'max:3000'],
            'decisions' => ['nullable', 'string', 'max:3000'],
            'next_actions' => ['nullable', 'string', 'max:3000'],
            'next_meeting_on' => ['nullable', 'date', 'after:meeting_on'],
        ]);
    }

    private function authorise(Request $request, MentoringMatch $match): void
    {
        abort_unless($match->involves($request->user()) && $match->status === 'active', 403);
    }

    private function recordUpdate(Request $request, MentoringMatch $match, string $message): void
    {
        MatchActivity::create(['mentoring_match_id' => $match->id, 'user_id' => $request->user()->id, 'type' => 'meeting', 'body' => $message]);
        $match->update(['last_activity_at' => now()]);
        $match->counterpartFor($request->user())->notify(new MatchActionNotification($match->id, $request->user()->name, 'updated a shared meeting record', $message, false, 'meeting', 'meetings'));
    }
}
