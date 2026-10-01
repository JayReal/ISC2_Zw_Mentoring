<?php

use App\Http\Controllers\Admin\ClusterController as AdminClusterController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MatchController as AdminMatchController;
use App\Http\Controllers\Admin\ParticipantController as AdminParticipantController;
use App\Http\Controllers\Admin\ProgrammeCycleController as AdminProgrammeCycleController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoalMilestoneController;
use App\Http\Controllers\IntakeController;
use App\Http\Controllers\MatchActivityController;
use App\Http\Controllers\MatchConfirmationController;
use App\Http\Controllers\MatchWorkspaceController;
use App\Http\Controllers\MentoringGoalController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:6,1');
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:6,1');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'programme.staff'])->group(function (): void {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::resource('participants', AdminParticipantController::class)->only(['index', 'show']);
    Route::put('participants/{participant}', [AdminParticipantController::class, 'update'])->middleware('role:admin,programme-lead,matching-team')->name('participants.update');
    Route::resource('matches', AdminMatchController::class)->except(['edit'])->middleware('role:admin,programme-lead,matching-team');
    Route::resource('cycles', AdminProgrammeCycleController::class)->except(['show', 'destroy'])->middleware('role:admin,programme-lead');
    Route::resource('clusters', AdminClusterController::class)->except(['show', 'destroy'])->middleware('role:admin,programme-lead,cluster-lead,technical-guild-lead');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/intake', [IntakeController::class, 'edit'])->name('intake.edit');
    Route::put('/intake', [IntakeController::class, 'update'])->name('intake.update');
    Route::get('/mentoring', [MatchWorkspaceController::class, 'index'])->name('matches.index');
    Route::get('/mentoring/{match}', [MatchWorkspaceController::class, 'show'])->name('matches.show');
    Route::put('/mentoring/{match}/confirmation', [MatchConfirmationController::class, 'update'])->name('matches.confirmation');
    Route::post('/mentoring/{match}/activities', [MatchActivityController::class, 'store'])->name('matches.activities.store');
    Route::post('/mentoring-goals', [MentoringGoalController::class, 'store'])->name('goals.store');
    Route::put('/mentoring-goals/{goal}', [MentoringGoalController::class, 'update'])->name('goals.update');
    Route::post('/mentoring-goals/{goal}/milestones', [GoalMilestoneController::class, 'store'])->name('milestones.store');
    Route::put('/mentoring-milestones/{milestone}', [GoalMilestoneController::class, 'update'])->name('milestones.update');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::put('/notifications/{notification}', [NotificationController::class, 'update'])->name('notifications.update');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
