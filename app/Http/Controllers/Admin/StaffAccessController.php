<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateStaffAccessRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        return view('admin.staff-access.index', [
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
        $beforeRoles = $user->roles ?? [];
        $beforeProgrammeRoles = array_values(array_intersect(User::programmeRoles(), $beforeRoles));
        $participantRoles = array_values(array_diff($beforeRoles, User::programmeRoles()));
        $afterProgrammeRoles = array_values(array_intersect(User::programmeRoles(), $validated['roles']));
        $afterRoles = array_values(array_unique([...$participantRoles, ...$afterProgrammeRoles]));

        if ($beforeProgrammeRoles === $afterProgrammeRoles) {
            return back()->with('status', "No staff access changes were needed for {$user->name}.");
        }

        DB::transaction(function () use ($request, $user, $beforeProgrammeRoles, $afterProgrammeRoles, $afterRoles): void {
            $user->update(['roles' => $afterRoles]);
            AuditLog::record($request, 'staff.access_updated', $user, [
                'before' => $beforeProgrammeRoles,
                'after' => $afterProgrammeRoles,
            ]);
        });

        return back()->with('status', "Staff access updated for {$user->name}.");
    }
}
