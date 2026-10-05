<?php

namespace App\Http\Controllers;

use App\Models\MentoringMatch;
use Illuminate\View\View;

class MatchWorkspaceController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $matches = MentoringMatch::with(['mentor', 'mentee', 'cluster'])
            ->where(fn ($query) => $query->where('mentor_id', $user->id)->orWhere('mentee_id', $user->id))
            ->latest()->get();

        return view('matches.index', compact('matches'));
    }

    public function show(MentoringMatch $match): View
    {
        abort_unless($match->involves(auth()->user()) || auth()->user()->hasAnyRole(['admin', 'programme-lead']), 403);
        $match->load(['mentor', 'mentee', 'cluster', 'goals.milestones.owner', 'goals.creator', 'meetings.recorder', 'meetings.updater', 'charter', 'closure.initiator', 'checkIns' => fn ($query) => $query->where('user_id', auth()->id())->latest(), 'activities' => fn ($query) => $query->with('user')->latest()->limit(50)]);
        $workspace = $this->workspaceSummary($match);

        return view('matches.show', compact('match', 'workspace'));
    }

    /** @return array<string, mixed> */
    private function workspaceSummary(MentoringMatch $match): array
    {
        $milestones = $match->goals->flatMap->milestones;
        $completedMilestones = $milestones->where('status', 'completed')->count();
        $overdueMilestones = $milestones->filter(fn ($milestone) => $milestone->due_on?->isPast() && $milestone->status !== 'completed')->count();
        $openMilestones = $milestones->where('status', '!=', 'completed');
        $dueSoonMilestones = $openMilestones->filter(fn ($milestone) => $milestone->due_on?->between(today(), today()->addDays(7), true))->count();
        $ownedByMeMilestones = $openMilestones->where('owner_id', auth()->id())->count();
        $blockedMilestones = $milestones->where('status', 'blocked')->count();
        $progressPercentage = $milestones->isEmpty() ? 0 : (int) round(($completedMilestones / $milestones->count()) * 100);
        $nextMeeting = $match->meetings->pluck('next_meeting_on')->filter()->filter(fn ($date) => $date->isToday() || $date->isFuture())->sort()->first();
        $charterAgreed = $match->charter?->mentor_confirmed_at && $match->charter?->mentee_confirmed_at;
        $ownCharterConfirmed = (int) auth()->id() === (int) $match->mentor_id ? $match->charter?->mentor_confirmed_at : $match->charter?->mentee_confirmed_at;

        $nextAction = match (true) {
            $match->status !== 'active' => ['title' => 'Review the match proposal', 'description' => 'Read the matching rationale and record your decision.', 'anchor' => 'confirmation', 'label' => 'Review proposal'],
            $match->closure && ! ($match->closure->mentor_confirmed_at && $match->closure->mentee_confirmed_at) => ['title' => 'Review the closure summary', 'description' => 'Confirm the shared record if it reflects your final mentoring discussion.', 'anchor' => 'closure', 'label' => 'Review closure'],
            ! $match->charter => ['title' => 'Create your mentoring charter', 'description' => 'Agree the meeting rhythm, communication method and boundaries.', 'anchor' => 'charter', 'label' => 'Create charter'],
            ! $ownCharterConfirmed => ['title' => 'Confirm the current charter', 'description' => 'Review the working agreement and confirm that it reflects your discussion.', 'anchor' => 'charter', 'label' => 'Review charter'],
            $match->goals->isEmpty() => ['title' => 'Add your first shared goal', 'description' => 'Choose one practical outcome to give the relationship direction.', 'anchor' => 'plan', 'label' => 'Add a goal'],
            $match->meetings->isEmpty() => ['title' => 'Record your first meeting', 'description' => 'Capture what you discussed, decided and will do next.', 'anchor' => 'meetings', 'label' => 'Add meeting record'],
            $match->checkIns->isEmpty() => ['title' => 'Complete this month’s check-in', 'description' => 'Provide a short private pulse for programme support.', 'anchor' => 'charter', 'label' => 'Complete check-in'],
            $overdueMilestones > 0 => ['title' => 'Review overdue milestones', 'description' => 'Update dates, ownership or status so the shared plan remains realistic.', 'anchor' => 'plan', 'label' => 'Review milestones'],
            default => ['title' => 'Continue the shared plan', 'description' => 'Update an action or post a shared progress update after your next discussion.', 'anchor' => 'plan', 'label' => 'Open shared plan'],
        };
        $nextAction['responsibility'] = match (true) {
            $match->status !== 'active' => 'Your decision is needed',
            $match->closure && ! ($match->closure->mentor_confirmed_at && $match->closure->mentee_confirmed_at) => 'Your confirmation may be needed',
            ! $match->charter, $match->goals->isEmpty() => 'Complete this together',
            ! $ownCharterConfirmed => 'Your confirmation is needed',
            $match->meetings->isEmpty() => 'Either participant can record it',
            $match->checkIns->isEmpty() => 'Your private check-in is due',
            $overdueMilestones > 0 && $ownedByMeMilestones > 0 => 'Check actions assigned to you',
            default => 'Keep the plan current together',
        };
        $nextAction['description'] = $nextAction['responsibility'].'. '.$nextAction['description'];

        return [
            'nextAction' => $nextAction,
            'charterAgreed' => (bool) $charterAgreed,
            'goalCount' => $match->goals->count(),
            'completedGoalCount' => $match->goals->where('status', 'completed')->count(),
            'milestoneCount' => $milestones->count(),
            'completedMilestoneCount' => $completedMilestones,
            'overdueMilestoneCount' => $overdueMilestones,
            'openMilestoneCount' => $openMilestones->count(),
            'dueSoonMilestoneCount' => $dueSoonMilestones,
            'ownedByMeMilestoneCount' => $ownedByMeMilestones,
            'blockedMilestoneCount' => $blockedMilestones,
            'progressPercentage' => $progressPercentage,
            'nextMeeting' => $nextMeeting,
            'lastMeeting' => $match->meetings->first()?->meeting_on,
            'monthlyCheckInComplete' => $match->checkIns->isNotEmpty(),
        ];
    }
}
