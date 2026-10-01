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
                'incomplete' => ParticipantProfile::where('intake_status', '!=', 'complete')->count(),
                'ready' => ParticipantProfile::where('intake_status', 'complete')->count(),
                'proposedMatches' => MentoringMatch::where('status', 'proposed')->count(),
                'activeMatches' => MentoringMatch::where('status', 'active')->count(),
            ],
            'activeCycle' => ProgrammeCycle::where('status', 'active')->first(),
            'recentParticipants' => ParticipantProfile::with(['user', 'primaryCluster'])->latest()->limit(8)->get(),
            'recentActivity' => AuditLog::with('actor')->latest()->limit(10)->get(),
        ]);
    }
}
