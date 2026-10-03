<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MentoringMatch;
use App\Models\ParticipantProfile;
use App\Models\ProgrammeCycle;
use App\Support\OperationsHealth;
use Illuminate\Support\Facades\DB;
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
                'ready' => ParticipantProfile::whereIn('intake_status', ['complete', 'under_review', 'approved'])->count(),
                'activeMatches' => MentoringMatch::where('status', 'active')->count(),
                'queuedNotifications' => DB::table('jobs')->count(),
                'failedNotifications' => DB::table('failed_jobs')->count(),
            ],
            'activeCycle' => ProgrammeCycle::where('status', 'active')->first(),
            'recentParticipants' => ParticipantProfile::with(['user', 'primaryCluster'])->latest()->limit(8)->get(),
            'recentActivity' => AuditLog::with('actor')->latest()->limit(10)->get(),
            'operations' => OperationsHealth::checks(),
        ]);
    }
}
