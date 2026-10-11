<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\ParticipantRequestNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class ParticipantRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:withdrawal,data-copy,deletion'],
            'details' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $alreadyOpen = $request->user()->participantRequests()
            ->where('type', $validated['type'])
            ->whereIn('status', ['open', 'in-review'])
            ->exists();

        if ($alreadyOpen) {
            throw ValidationException::withMessages([
                'type' => 'You already have an open request of this type. Programme staff will respond to it.',
            ]);
        }

        $participantRequest = $request->user()->participantRequests()->create($validated);
        $staff = User::query()
            ->where(fn ($query) => $query->whereJsonContains('roles', 'admin')->orWhereJsonContains('roles', 'programme-lead'))
            ->get();

        Notification::send($staff, (new ParticipantRequestNotification(
            $request->user()->name,
            $participantRequest->type,
        ))->afterCommit());

        return back()->with('status', 'Your request has been sent to authorised programme staff.');
    }
}
