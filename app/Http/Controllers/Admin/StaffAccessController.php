<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RemoveStaffAccessRequest;
use App\Http\Requests\UpdateStaffAccessRequest;
use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\StaffAccessChangedNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StaffAccessController extends Controller
{
    /**
     * Display programme staff or matching registered users.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($validated['search'] ?? '');

        $users = User::query()
            ->select(['id', 'name', 'email', 'email_verified_at', 'roles'])
            ->when(
                $search !== '',
                fn (Builder $query): Builder => $query->where(function (Builder $users) use ($search): void {
                    $users->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                }),
                fn (Builder $query): Builder => $query->where(function (Builder $users): void {
                    foreach (User::programmeRoles() as $role) {
                        $users->orWhereJsonContains('roles', $role);
                    }
                }),
            )
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        $accessChanges = AuditLog::query()
            ->with(['actor:id,name', 'subject:id,name,email'])
            ->where('action', 'staff.access_updated')
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.staff-access.index', [
            'accessChanges' => $accessChanges,
            'roleOptions' => User::PROGRAMME_ROLE_OPTIONS,
            'search' => $search,
            'users' => $users,
        ]);
    }

    /**
     * Replace a user's programme roles while preserving participant roles.
     */
    public function update(UpdateStaffAccessRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();
        $afterProgrammeRoles = array_values(array_intersect(User::programmeRoles(), $validated['roles']));

        if (! $this->replaceProgrammeRoles($request, $user, $afterProgrammeRoles)) {
            return back()->with('status', "No staff access changes were needed for {$user->name}.");
        }

        return back()->with('status', "Staff access updated for {$user->name}.");
    }

    /**
     * Remove all programme roles while preserving participant roles.
     */
    public function destroy(RemoveStaffAccessRequest $request, User $user): RedirectResponse
    {
        if (! $this->replaceProgrammeRoles($request, $user, [])) {
            return back()->with('status', "{$user->name} does not have programme staff access.");
        }

        return back()->with('status', "Staff access removed for {$user->name}.");
    }

    /**
     * @param  array<int, string>  $afterProgrammeRoles
     */
    private function replaceProgrammeRoles(Request $request, User $user, array $afterProgrammeRoles): bool
    {
        $change = DB::transaction(function () use ($request, $user, $afterProgrammeRoles): ?array {
            $administrators = User::query()->whereJsonContains('roles', 'admin')->lockForUpdate()->get(['id']);
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $beforeRoles = $lockedUser->roles ?? [];
            $beforeProgrammeRoles = array_values(array_intersect(User::programmeRoles(), $beforeRoles));

            if ($beforeProgrammeRoles === $afterProgrammeRoles) {
                return null;
            }

            if (in_array('admin', $beforeProgrammeRoles, true)
                && ! in_array('admin', $afterProgrammeRoles, true)
                && $administrators->count() <= 1) {
                throw ValidationException::withMessages([
                    'roles' => 'The final administrator cannot be removed. Grant administrator access to another verified user first.',
                ]);
            }

            $participantRoles = array_values(array_diff($beforeRoles, User::programmeRoles()));
            $afterRoles = array_values(array_unique([...$participantRoles, ...$afterProgrammeRoles]));
            $lockedUser->update(['roles' => $afterRoles]);
            AuditLog::record($request, 'staff.access_updated', $lockedUser, [
                'before' => $beforeProgrammeRoles,
                'after' => $afterProgrammeRoles,
            ]);

            return ['before' => $beforeProgrammeRoles, 'after' => $afterProgrammeRoles];
        });

        if ($change === null) {
            return false;
        }

        $user->notify((new StaffAccessChangedNotification(
            $request->user()->name,
            $change['before'],
            $change['after'],
        ))->afterCommit());

        return true;
    }
}
