<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ParticipantRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ParticipantRequestController extends Controller
{
    public function update(Request $request, ParticipantRequest $participantRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:in-review,resolved,closed'],
            'resolution' => ['required', 'string', 'max:3000'],
        ]);

        $participantRequest->update([
            ...$validated,
            'handled_by' => $request->user()->id,
            'resolved_at' => in_array($validated['status'], ['resolved', 'closed'], true) ? now() : null,
        ]);
        AuditLog::record($request, 'participant_request.updated', $participantRequest, ['status' => $validated['status']]);

        return back()->with('status', 'Participant request updated.');
    }
}
