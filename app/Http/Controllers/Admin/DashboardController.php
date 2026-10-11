<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MentoringMatch;
use App\Models\ParticipantProfile;
use App\Models\ProgrammeCycle;
use App\Support\OperationsHealth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): View
    {
        $user = auth()->user();
        $metrics = [
            'participants' => ParticipantProfile::count(),
            'ready' => ParticipantProfile::whereIn('intake_status', ['complete', 'under_review', 'approved'])->count(),
            'activeMatches' => MentoringMatch::where('status', 'active')->count(),
        ];
        $metricLabels = [
            'participants' => 'Participants',
            'ready' => 'Ready for review',
            'activeMatches' => 'Active matches',
        ];

        if ($user->canViewProgrammeOperations()) {
            $metrics['queuedNotifications'] = DB::table('jobs')->count();
            $metrics['failedNotifications'] = DB::table('failed_jobs')->count();
            $metricLabels['queuedNotifications'] = 'Queued emails';
            $metricLabels['failedNotifications'] = 'Failed jobs';
        }

        return view('admin.dashboard', [
            'canReviewParticipants' => $user->canReviewParticipants(),
            'canUseAttentionQueue' => $user->canUseAttentionQueue(),
            'canViewOperations' => $user->canViewProgrammeOperations(),
            'metricLabels' => $metricLabels,
            'metrics' => $metrics,
            'activeCycle' => ProgrammeCycle::where('status', 'active')->first(),
            'recentParticipants' => $user->canReviewParticipants()
                ? ParticipantProfile::with(['user', 'primaryCluster'])->latest()->limit(8)->get()
                : new Collection,
            'recentActivity' => $user->canViewProgrammeOperations()
                ? AuditLog::with('actor')->latest()->limit(10)->get()
                : new Collection,
            'operations' => $user->canViewProgrammeOperations() ? OperationsHealth::checks() : [],
        ]);
    }
}
