<?php

namespace App\Http\Controllers;

use App\Models\MentoringMatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): View|RedirectResponse
    {
        if (auth()->user()->isProgrammeStaff() && ! auth()->user()->participantProfile()->exists()) {
            return redirect()->route('admin.dashboard');
        }

        $profile = auth()->user()->participantProfile()->with('primaryCluster')->firstOrFail();
        $matches = MentoringMatch::with(['mentor', 'mentee', 'goals.milestones', 'charter', 'meetings', 'checkIns' => fn ($query) => $query->where('user_id', auth()->id())->whereDate('period_month', now()->startOfMonth())])
            ->where(fn ($query) => $query->where('mentor_id', auth()->id())->orWhere('mentee_id', auth()->id()))
            ->latest()->get();
        $actionMatch = $matches->first(fn (MentoringMatch $match) => in_array($match->status, ['proposed', 'pending-confirmation'], true) && ! ((int) $match->mentor_id === (int) auth()->id() ? $match->mentor_confirmed_at : $match->mentee_confirmed_at))
            ?? $matches->firstWhere('status', 'active');
        $intakeComplete = in_array($profile->intake_status, ['complete', 'under_review', 'approved'], true);
        $nextAction = $this->nextAction($actionMatch);

        return view('dashboard', compact('profile', 'matches', 'actionMatch', 'intakeComplete', 'nextAction'));
    }

    /** @return array{title: string, description: string, label: string}|null */
    private function nextAction(?MentoringMatch $match): ?array
    {
        if (! $match || $match->status !== 'active') {
            return null;
        }
        if (! $match->charter) {
            return ['title' => 'Agree how you will work together', 'description' => 'Create a short mentoring charter covering meeting cadence, communication and boundaries.', 'label' => 'Create mentoring charter'];
        }
        $ownConfirmation = (int) auth()->id() === (int) $match->mentor_id ? $match->charter->mentor_confirmed_at : $match->charter->mentee_confirmed_at;
        if (! $ownConfirmation) {
            return ['title' => 'Confirm the mentoring charter', 'description' => 'Review the current working agreement and confirm it when it reflects what you discussed.', 'label' => 'Review mentoring charter'];
        }
        if ($match->goals->isEmpty()) {
            return ['title' => 'Add your first shared goal', 'description' => 'Record one practical outcome so meetings and milestones have a clear direction.', 'label' => 'Add first goal'];
        }
        if ($match->meetings->isEmpty()) {
            return ['title' => 'Record your first mentoring meeting', 'description' => 'Capture the topics, decisions and next actions you agreed so both people have the same reference.', 'label' => 'Add meeting record'];
        }
        if ($match->checkIns->isEmpty()) {
            return ['title' => 'Complete this month’s check-in', 'description' => 'Record a short private pulse so the programme can notice when support may be useful.', 'label' => 'Complete monthly check-in'];
        }

        return ['title' => 'Continue your mentoring plan', 'description' => 'Update goals, milestones or shared notes after your latest discussion.', 'label' => 'Open mentoring workspace'];
    }
}
