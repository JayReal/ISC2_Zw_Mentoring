<section id="meetings" class="panel mt-7 scroll-mt-32 overflow-hidden" aria-labelledby="meeting-rhythm-heading">
    <div class="panel-header">
        <div><p class="section-kicker">Meeting rhythm</p><h2 id="meeting-rhythm-heading" class="mt-1 text-xl font-extrabold">Review and follow through</h2><p class="mt-1 text-sm text-neutral-600">Meeting records are shared with both participants. Keep them factual and avoid confidential safeguarding information.</p></div>
        @if($workspace['nextMeeting'])<div class="rounded-lg bg-green-50 px-4 py-2 text-sm text-green-900"><span class="block text-xs font-bold uppercase">Next meeting</span><strong>{{ $workspace['nextMeeting']->format('j M Y') }}</strong></div>
        @elseif($match->meetings->first()?->next_meeting_on?->isPast())<div class="rounded-lg bg-amber-50 px-4 py-2 text-sm text-amber-950"><span class="block text-xs font-bold uppercase">Schedule review</span><strong>Planned date has passed</strong></div>
        @else<span class="status-badge">No next meeting set</span>@endif
    </div>
    <div class="p-5 sm:p-6">
        <ol class="relative space-y-4 border-l-2 border-neutral-200 pl-5">
            @forelse($match->meetings as $meeting)
                <li class="relative"><span class="absolute -left-[1.65rem] top-1.5 size-3 rounded-full border-2 border-white bg-isc2-green ring-1 ring-neutral-300"></span>
                    <details class="rounded-xl border border-neutral-200 bg-white p-4"><summary class="cursor-pointer"><span class="font-extrabold">{{ $meeting->meeting_on->format('j M Y') }}</span><span class="ml-2 text-sm text-neutral-500">{{ $meeting->duration_minutes ? $meeting->duration_minutes.' minutes' : 'Duration not recorded' }}</span><span class="mt-1 block text-xs text-neutral-500">Last updated {{ $meeting->updated_at->diffForHumans() }} by {{ $meeting->updater?->name ?? $meeting->recorder->name }}</span></summary>
                        <div class="mt-4 grid gap-5 lg:grid-cols-2">
                            <form method="POST" action="{{ route('meetings.update',$meeting) }}" class="grid gap-3">@csrf @method('PUT')
                                <h3 class="font-extrabold">Edit shared record</h3><div class="grid gap-3 sm:grid-cols-2"><div class="field"><label>Meeting date</label><input type="date" name="meeting_on" max="{{ now()->toDateString() }}" value="{{ $meeting->meeting_on->format('Y-m-d') }}" required></div><div class="field"><label>Duration in minutes</label><input type="number" name="duration_minutes" min="1" max="600" value="{{ $meeting->duration_minutes }}"></div></div><div class="field"><label>Topics discussed</label><textarea name="topics_discussed" rows="3" required minlength="10">{{ $meeting->topics_discussed }}</textarea></div><div class="field"><label>Decisions</label><textarea name="decisions" rows="2">{{ $meeting->decisions }}</textarea></div><div class="field"><label>Next actions</label><textarea name="next_actions" rows="2">{{ $meeting->next_actions }}</textarea></div><div class="field"><label>Next meeting</label><input type="date" name="next_meeting_on" value="{{ $meeting->next_meeting_on?->format('Y-m-d') }}"></div><button class="button-secondary justify-self-start">Save corrections</button>
                            </form>
                            <div class="rounded-xl bg-neutral-50 p-4"><h3 class="font-extrabold">Turn an action into a milestone</h3>@if($match->goals->isEmpty())<p class="mt-2 text-sm text-neutral-600">Add a shared goal first, then meeting actions can be assigned and tracked beneath it.</p>@else<form method="POST" action="{{ route('meetings.milestone.store',$meeting) }}" class="mt-3 grid gap-3">@csrf<div class="field"><label>Goal</label><select name="mentoring_goal_id" required>@foreach($match->goals as $goal)<option value="{{ $goal->id }}">{{ $goal->title }}</option>@endforeach</select></div><div class="field"><label>Milestone or action</label><input name="title" maxlength="180" required placeholder="One clear, achievable action"></div><div class="field"><label>Context</label><textarea name="description" rows="2" placeholder="Optional detail from this meeting">{{ $meeting->next_actions }}</textarea></div><div class="grid gap-3 sm:grid-cols-2"><div class="field"><label>Owner</label><select name="owner_id"><option value="">Shared</option><option value="{{ $match->mentor_id }}">{{ $match->mentor->name }}</option><option value="{{ $match->mentee_id }}">{{ $match->mentee->name }}</option></select></div><div class="field"><label>Due date</label><input type="date" name="due_on"></div></div><button class="button-primary justify-self-start">Add to shared plan</button></form>@endif</div>
                        </div>
                    </details>
                </li>
            @empty
                <li class="text-sm text-neutral-600">Your meeting timeline will appear here after the first shared record is added.</li>
            @endforelse
        </ol>
    </div>
</section>
