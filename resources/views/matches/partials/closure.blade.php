@php
    $closure = $match->closure;
    $ownConfirmation = (int) auth()->id() === (int) $match->mentor_id ? $closure?->mentor_confirmed_at : $closure?->mentee_confirmed_at;
    $reasonLabels = ['planned' => 'Planned end date reached', 'goal-achieved' => 'Main goal achieved', 'changed-circumstances' => 'Circumstances changed', 'other' => 'Another agreed reason'];
@endphp

<section id="closure" class="panel mt-7 scroll-mt-32 overflow-hidden">
    <div class="panel-header"><div><p class="section-kicker">Responsible closure</p><h2 class="mt-1 text-xl font-extrabold">Close the relationship well</h2><p class="mt-1 text-sm leading-6 text-neutral-600">Record one shared summary and the next step. Both participants confirm before the relationship closes.</p></div>@if($match->status === 'closed')<span class="status-badge">Closed</span>@elseif($closure)<span class="status-badge">Awaiting confirmation</span>@endif</div>
    <div class="grid gap-5 p-5 lg:grid-cols-[1fr_.75fr] sm:p-6">
        <div>
            @if($closure)
                <dl class="grid gap-4 text-sm"><div><dt class="font-bold">Reason</dt><dd class="mt-1 text-neutral-600">{{ $reasonLabels[$closure->reason] ?? str($closure->reason)->replace('-', ' ')->title() }}</dd></div><div><dt class="font-bold">Shared summary</dt><dd class="mt-1 whitespace-pre-line leading-6 text-neutral-600">{{ $closure->summary }}</dd></div>@if($closure->next_steps)<div><dt class="font-bold">Next steps</dt><dd class="mt-1 whitespace-pre-line leading-6 text-neutral-600">{{ $closure->next_steps }}</dd></div>@endif</dl>
                <div class="mt-5 flex flex-wrap gap-2 text-xs"><span class="status-badge">Mentor: {{ $closure->mentor_confirmed_at ? 'confirmed' : 'awaiting' }}</span><span class="status-badge">Mentee: {{ $closure->mentee_confirmed_at ? 'confirmed' : 'awaiting' }}</span></div>
                @if($match->status === 'active' && ! $ownConfirmation)<form method="POST" action="{{ route('matches.closure.confirm', $match) }}" class="mt-5">@csrf<button class="button-primary">Confirm closure summary</button></form>@endif
            @else
                <p class="text-sm leading-6 text-neutral-600">Use this after your final discussion or when you both agree that the current mentoring period has reached a natural end.</p>
            @endif
        </div>

        @if($match->status === 'active')
            <details class="rounded-xl border border-neutral-200 bg-neutral-50 p-4"><summary class="cursor-pointer text-sm font-extrabold text-isc2-green">{{ $closure ? 'Revise closure summary' : 'Prepare closure summary' }}</summary><form method="POST" action="{{ route('matches.closure.update', $match) }}" class="mt-4 grid gap-3">@csrf @method('PUT')<div class="field"><label for="closure_reason">Reason for closing</label><select id="closure_reason" name="reason" required>@foreach($reasonLabels as $value => $label)<option value="{{ $value }}" @selected(old('reason', $closure?->reason)===$value)>{{ $label }}</option>@endforeach</select></div><div class="field"><label for="closure_summary">What did you accomplish or learn?</label><textarea id="closure_summary" name="summary" rows="4" required minlength="20" maxlength="2000">{{ old('summary', $closure?->summary) }}</textarea></div><div class="field"><label for="closure_next_steps">What happens next?</label><textarea id="closure_next_steps" name="next_steps" rows="3" maxlength="1500" placeholder="Optional practical next step">{{ old('next_steps', $closure?->next_steps) }}</textarea></div><p class="text-xs leading-5 text-neutral-500">Saving confirms your version and asks {{ $counterpart->name }} to review it. Editing later resets previous confirmations.</p><button class="button-secondary justify-self-start">Save and share</button></form></details>
        @endif
    </div>
</section>
