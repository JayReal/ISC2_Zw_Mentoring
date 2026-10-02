<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MentoringMatch;
use App\Models\ParticipantProfile;
use App\Models\ProgrammeCycle;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'metrics' => [
                'participants' => ParticipantProfile::count(),
                'incomplete' => ParticipantProfile::whereIn('intake_status', ['not_started', 'in_progress'])->count(),
                'ready' => ParticipantProfile::whereIn('intake_status', ['complete', 'under_review', 'approved'])->count(),
                'proposedMatches' => MentoringMatch::where('status', 'proposed')->count(),
                'activeMatches' => MentoringMatch::where('status', 'active')->count(),
                'pendingConfirmations' => MentoringMatch::where('status', 'pending-confirmation')->count(),
                'inactiveMatches' => MentoringMatch::where('status', 'active')->where(function ($query) {
                    $query->where('last_activity_at', '<', now()->subDays(30))
                        ->orWhere(fn ($inactive) => $inactive->whereNull('last_activity_at')->where('started_at', '<', now()->subDays(30)));
                })->count(),
            ],
            'activeCycle' => ProgrammeCycle::where('status', 'active')->first(),
            'recentParticipants' => ParticipantProfile::with(['user', 'primaryCluster'])->latest()->limit(8)->get(),
            'recentActivity' => AuditLog::with('actor')->latest()->limit(10)->get(),
        ]);
    }
}
