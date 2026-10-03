<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoalMilestone;
use App\Models\MentoringMatch;
use App\Models\MentoringSupportRequest;
use Illuminate\View\View;

class ProgrammePulseController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): View
    {
        $matchRelations = ['mentor', 'mentee', 'charter'];
        $pendingConfirmations = MentoringMatch::with($matchRelations)->whereIn('status', ['proposed', 'pending-confirmation'])->oldest()->limit(12)->get();
        $charterIncomplete = MentoringMatch::with($matchRelations)->where('status', 'active')->where(function ($query) {
            $query->whereDoesntHave('charter')->orWhereHas('charter', fn ($charter) => $charter->whereNull('mentor_confirmed_at')->orWhereNull('mentee_confirmed_at'));
        })->oldest('started_at')->limit(12)->get();
        $withoutGoals = MentoringMatch::with($matchRelations)->where('status', 'active')->doesntHave('goals')->oldest('started_at')->limit(12)->get();
        $withoutMeetings = MentoringMatch::with($matchRelations)->where('status', 'active')->doesntHave('meetings')->oldest('started_at')->limit(12)->get();
        $checkInsOutstanding = MentoringMatch::with($matchRelations)
            ->withCount(['checkIns as current_check_ins_count' => fn ($query) => $query->whereDate('period_month', now()->startOfMonth())])
            ->where('status', 'active')->having('current_check_ins_count', '<', 2)->oldest('started_at')->limit(12)->get();
        $inactiveMatches = MentoringMatch::with($matchRelations)->where('status', 'active')->where(function ($query) {
            $query->where('last_activity_at', '<', now()->subDays(30))
                ->orWhere(fn ($inactive) => $inactive->whereNull('last_activity_at')->where('started_at', '<', now()->subDays(30)));
        })->oldest('last_activity_at')->limit(12)->get();
        $overdueMilestones = GoalMilestone::with(['owner', 'goal.mentoringMatch.mentor', 'goal.mentoringMatch.mentee'])
            ->whereHas('goal.mentoringMatch', fn ($query) => $query->where('status', 'active'))
            ->whereNotIn('status', ['completed'])->whereDate('due_on', '<', today())->oldest('due_on')->limit(12)->get();
        $canSeeConfidentialRequests = auth()->user()->hasAnyRole(['admin', 'programme-lead', 'safeguarding']);
        $supportRequests = $canSeeConfidentialRequests
            ? MentoringSupportRequest::with(['requester', 'mentoringMatch.mentor', 'mentoringMatch.mentee'])->whereIn('status', ['open', 'in-review'])->oldest()->limit(12)->get()
            : collect();

        $queues = compact('pendingConfirmations', 'charterIncomplete', 'withoutGoals', 'withoutMeetings', 'checkInsOutstanding', 'inactiveMatches', 'overdueMilestones', 'supportRequests');

        return view('admin.pulse.index', compact('queues', 'canSeeConfidentialRequests'));
    }
}
