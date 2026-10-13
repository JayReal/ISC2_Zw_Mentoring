<x-admin.shell :title="$participant->user->name">
    <div class="grid gap-5 lg:grid-cols-[1.4fr_.6fr]">
        <div class="space-y-5">
            <section class="panel p-5">
                <div class="flex flex-wrap items-start justify-between gap-3"><div><p class="section-kicker">Participant record</p><p class="mt-1 text-sm text-neutral-500">{{ $participant->user->email }} · Joined {{ $participant->created_at->format('j M Y') }}</p>@if($participant->profile_review_requested_at)<div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-950"><p class="font-medium">Matching information updated {{ $participant->profile_review_requested_at->diffForHumans() }}.</p><p class="mt-1">Review before creating a new proposal.</p>@if($reviewChangedFields !== [])<p class="mt-2 text-xs leading-5"><span class="font-medium">Updated areas:</span> {{ collect($reviewChangedFields)->join(', ') }}. Confidential values are not shown in this summary.</p>@endif</div>@endif</div><span class="status-badge">{{ str($participant->intake_status)->replace('_', ' ')->title() }}</span></div>
                <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div><dt class="text-xs font-medium text-neutral-500 uppercase">Participation</dt><dd class="mt-1 capitalize">{{ $participant->participation_type }}</dd></div>
                    <div><dt class="text-xs font-medium text-neutral-500 uppercase">Pathway</dt><dd class="mt-1">{{ config('mentoring.pathways.'.$participant->pathway.'.label', str($participant->pathway ?? 'Not selected')->replace('-', ' ')->title()) }}</dd><dd class="mt-1 text-xs leading-5 text-neutral-500">{{ config('mentoring.pathways.'.$participant->pathway.'.description') }}</dd></div>
                    <div><dt class="text-xs font-medium text-neutral-500 uppercase">Primary cluster</dt><dd class="mt-1">{{ $participant->primaryCluster?->name ?? 'Not selected' }}</dd></div>
                    <div><dt class="text-xs font-medium text-neutral-500 uppercase">Other communities</dt><dd class="mt-1">{{ $secondaryClusters->pluck('name')->join(', ') ?: 'None selected' }}</dd></div>
                    <div><dt class="text-xs font-medium text-neutral-500 uppercase">Format and language</dt><dd class="mt-1">{{ ucfirst($participant->preferred_format) }} · {{ $participant->preferred_language }}</dd></div>
                    <div><dt class="text-xs font-medium text-neutral-500 uppercase">University or college context</dt><dd class="mt-1">{{ $participant->university_context ?: 'Not applicable' }}</dd></div>
                </dl>
            </section>

            <section class="panel p-5">
                <h2 class="text-lg font-semibold">Goals and matching context</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div><dt class="font-medium">Goals</dt><dd class="mt-1 whitespace-pre-line text-neutral-600">{{ $participant->goals ?? 'Not supplied' }}</dd></div>
                    <div><dt class="font-medium">Experience</dt><dd class="mt-1 whitespace-pre-line text-neutral-600">{{ $participant->experience_summary ?? 'Not supplied' }}</dd></div>
                    <div><dt class="font-medium">Availability</dt><dd class="mt-1 text-neutral-600">{{ collect($participant->availability ?? [])->map(fn ($item) => str($item)->replace('-', ' ')->title())->join(', ') ?: 'Not supplied' }}</dd></div>
                    <div><dt class="font-medium">Preferred way of working</dt><dd class="mt-1 text-neutral-600">{{ collect($participant->mentoring_styles ?? [])->map(fn ($style) => config('mentoring.styles.'.$style.'.label', str($style)->replace('-', ' ')->title()))->join(', ') ?: 'No structured preferences selected' }}</dd>@if($participant->mentoring_style)<dd class="mt-1 whitespace-pre-line text-neutral-600">Additional note: {{ $participant->mentoring_style }}</dd>@endif</div>
                </dl>
            </section>

            @if(auth()->user()->hasAnyRole(['admin', 'programme-lead', 'safeguarding']))
                <section class="rounded-xl border border-amber-200 bg-amber-50 p-5"><p class="text-xs font-medium uppercase text-amber-800">Restricted information</p><dl class="mt-3 space-y-3 text-sm"><div><dt class="font-medium">Accessibility or participation needs</dt><dd>{{ $participant->accessibility_needs ?? 'None declared' }}</dd></div><div><dt class="font-medium">Conflict declarations</dt><dd>{{ $participant->conflict_declarations ?? 'None declared' }}</dd></div></dl></section>
            @endif
        </div>

        <aside>
            @if(auth()->user()->hasAnyRole(['admin', 'programme-lead', 'matching-team']))
                <form class="panel p-5" method="POST" action="{{ route('admin.participants.update', $participant) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="review_requested_at" value="{{ $participant->profile_review_requested_at?->format('Y-m-d H:i:s') }}">
                    <input type="hidden" name="review_audit_id" value="{{ $reviewAuditId }}">
                    <h2 class="text-lg font-semibold">Review decision</h2>
                    @error('review_requested_at')<p class="error-text mt-3" role="alert">{{ $message }}</p>@enderror
                    <div class="mt-4 grid gap-2"><label for="intake_status">Status</label><select id="intake_status" name="intake_status">@foreach(['not_started', 'in_progress', 'complete', 'under_review', 'approved', 'on_hold', 'closed'] as $status)<option value="{{ $status }}" @selected($participant->intake_status === $status)>{{ str($status)->replace('_', ' ')->title() }}</option>@endforeach</select></div>
                    <input type="hidden" name="programme_cycle_id" value="{{ $participant->programme_cycle_id }}">
                    @if(in_array($participant->participation_type, ['mentor', 'both'], true))<div class="mt-4 field"><label for="orientation_completed">Mentor orientation</label><select id="orientation_completed" name="orientation_completed"><option value="0" @selected(!$participant->mentor_orientation_completed_at)>Not completed</option><option value="1" @selected($participant->mentor_orientation_completed_at)>Completed</option></select></div>@endif
                    <div class="mt-4 grid gap-2"><label for="reason">Decision reason</label><textarea id="reason" name="reason" rows="4" required placeholder="Record why this decision was made"></textarea></div>
                    <button class="button-primary mt-4 w-full">Save review</button><p class="mt-3 text-xs leading-5 text-neutral-500">This decision and its reason are written to the audit trail.</p>
                </form>
            @else
                <div class="panel p-5 text-sm text-neutral-600">Your role has read-only access to this participant record.</div>
            @endif
        </aside>
    </div>
</x-admin.shell>
