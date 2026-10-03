<?php

use App\Http\Controllers\Admin\AttentionQueueController as AdminAttentionQueueController;
use App\Http\Controllers\Admin\ClusterController as AdminClusterController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MatchController as AdminMatchController;
use App\Http\Controllers\Admin\ParticipantController as AdminParticipantController;
use App\Http\Controllers\Admin\ProgrammeCycleController as AdminProgrammeCycleController;
use App\Http\Controllers\Admin\ProgrammePulseController as AdminProgrammePulseController;
use App\Http\Controllers\Admin\SupportRequestController as AdminSupportRequestController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoalMilestoneController;
use App\Http\Controllers\IntakeController;
use App\Http\Controllers\MatchActivityController;
use App\Http\Controllers\MatchConfirmationController;
use App\Http\Controllers\MatchWorkspaceController;
use App\Http\Controllers\MentoringCharterController;
use App\Http\Controllers\MentoringCheckInController;
use App\Http\Controllers\MentoringClosureController;
use App\Http\Controllers\MentoringGoalController;
use App\Http\Controllers\MentoringMeetingController;
use App\Http\Controllers\MentoringOutcomeController;
use App\Http\Controllers\MentoringSupportController;
use App\Http\Controllers\MentorReadinessController;
use App\Http\Controllers\NotificationController;
use App\Support\OperationsHealth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::get('/health', function () {
    $checks = OperationsHealth::checks();
    $healthy = OperationsHealth::healthy($checks);

    return response()->json(['status' => $healthy ? 'ok' : 'unavailable', 'checks' => $checks], $healthy ? 200 : 503);
})->name('health');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:6,1');
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:6,1');
    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->middleware('throttle:5,1')->name('password.update');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'programme.staff'])->group(function (): void {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('/attention', AdminAttentionQueueController::class)->name('attention');
    Route::get('/pulse', AdminProgrammePulseController::class)->middleware('role:admin,programme-lead,matching-team')->name('pulse');
    Route::put('/support-requests/{supportRequest}', [AdminSupportRequestController::class, 'update'])->middleware('role:admin,programme-lead,safeguarding')->name('support-requests.update');
    Route::resource('participants', AdminParticipantController::class)->only(['index', 'show']);
    Route::put('participants/{participant}', [AdminParticipantController::class, 'update'])->middleware('role:admin,programme-lead,matching-team')->name('participants.update');
    Route::resource('matches', AdminMatchController::class)->except(['edit'])->middleware('role:admin,programme-lead,matching-team');
    Route::resource('cycles', AdminProgrammeCycleController::class)->except(['show', 'destroy'])->middleware('role:admin,programme-lead');
    Route::resource('clusters', AdminClusterController::class)->except(['show', 'destroy'])->middleware('role:admin,programme-lead,cluster-lead,technical-guild-lead');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/intake', [IntakeController::class, 'edit'])->name('intake.edit');
    Route::put('/intake', [IntakeController::class, 'update'])->name('intake.update');
    Route::get('/mentor-readiness', [MentorReadinessController::class, 'edit'])->name('mentor-readiness.edit');
    Route::put('/mentor-readiness', [MentorReadinessController::class, 'update'])->name('mentor-readiness.update');
    Route::get('/mentoring', [MatchWorkspaceController::class, 'index'])->name('matches.index');
    Route::get('/mentoring/{match}', [MatchWorkspaceController::class, 'show'])->name('matches.show');
    Route::get('/mentoring/{match}/outcome', MentoringOutcomeController::class)->name('matches.outcome');
    Route::put('/mentoring/{match}/confirmation', [MatchConfirmationController::class, 'update'])->name('matches.confirmation');
    Route::put('/mentoring/{match}/charter', [MentoringCharterController::class, 'update'])->name('matches.charter.update');
    Route::post('/mentoring/{match}/charter/confirm', [MentoringCharterController::class, 'confirm'])->name('matches.charter.confirm');
    Route::post('/mentoring/{match}/check-in', [MentoringCheckInController::class, 'store'])->middleware('throttle:10,1')->name('matches.check-in.store');
    Route::put('/mentoring/{match}/closure', [MentoringClosureController::class, 'update'])->name('matches.closure.update');
    Route::post('/mentoring/{match}/closure/confirm', [MentoringClosureController::class, 'confirm'])->name('matches.closure.confirm');
    Route::post('/mentoring/{match}/support', [MentoringSupportController::class, 'store'])->middleware('throttle:5,1')->name('matches.support.store');
    Route::post('/mentoring/{match}/meetings', [MentoringMeetingController::class, 'store'])->name('matches.meetings.store');
    Route::put('/mentoring-meetings/{meeting}', [MentoringMeetingController::class, 'update'])->name('meetings.update');
    Route::post('/mentoring-meetings/{meeting}/milestone', [MentoringMeetingController::class, 'createMilestone'])->name('meetings.milestone.store');
    Route::post('/mentoring/{match}/activities', [MatchActivityController::class, 'store'])->name('matches.activities.store');
    Route::post('/mentoring-goals', [MentoringGoalController::class, 'store'])->name('goals.store');
    Route::put('/mentoring-goals/{goal}', [MentoringGoalController::class, 'update'])->name('goals.update');
    Route::post('/mentoring-goals/{goal}/milestones', [GoalMilestoneController::class, 'store'])->name('milestones.store');
    Route::put('/mentoring-milestones/{milestone}', [GoalMilestoneController::class, 'update'])->name('milestones.update');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::put('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::put('/notifications/{notification}', [NotificationController::class, 'update'])->name('notifications.update');
});
