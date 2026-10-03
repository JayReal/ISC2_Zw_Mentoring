<x-admin.shell title="Programme pulse">
    @php
        $matchQueues = [
            ['key'=>'charterIncomplete','title'=>'Charter incomplete','description'=>'The working agreement is missing or awaits confirmation.','tone'=>'amber'],
            ['key'=>'withoutGoals','title'=>'No shared goal','description'=>'The relationship is active but has no outcome to guide it.','tone'=>'neutral'],
            ['key'=>'withoutMeetings','title'=>'No meeting recorded','description'=>'No shared mentoring conversation has been recorded.','tone'=>'neutral'],
            ['key'=>'checkInsOutstanding','title'=>'Monthly pulse outstanding','description'=>'One or both participants have not checked in this month.','tone'=>'neutral'],
            ['key'=>'closurePending','title'=>'Closure awaiting confirmation','description'=>'One participant still needs to acknowledge the shared closure summary.','tone'=>'neutral'],
        ];
        $flagCount = collect($matchQueues)->sum(fn($queue) => $queues[$queue['key']]->count()) + $queues['overdueMilestones']->count();
    @endphp

    <div class="grid gap-5 xl:grid-cols-[1.3fr_.7fr]"><div><p class="lede">A relationship-health view showing gaps in charters, goals, meetings, check-ins, milestones and closure. Participant administration and confidential requests remain in the attention queue.</p></div><div class="rounded-2xl bg-isc2-dark-green p-5 text-white"><p class="text-xs font-bold tracking-[0.14em] text-[#9ac23c] uppercase">Relationship flags</p><p class="mt-2 text-3xl font-extrabold">{{ $flagCount }}</p><p class="mt-1 text-sm text-neutral-300">Health indicators across the visible queues. A relationship can appear in more than one queue.</p></div></div>

    @if($flagCount===0)<section class="panel mt-7 p-8 text-center"><div class="mx-auto grid size-12 place-items-center rounded-full bg-green-50 text-xl text-isc2-green">✓</div><h2 class="mt-4 text-xl font-extrabold">No relationships currently need attention</h2><p class="mt-2 text-sm text-neutral-600">The programme pulse will surface confirmation, activity, goal, meeting and check-in gaps here.</p></section>@endif

    <div class="mt-7 grid gap-5 xl:grid-cols-2">
        @foreach($matchQueues as $queue)
            @php($items=$queues[$queue['key']])
            <section class="panel overflow-hidden"><div class="panel-header"><div><h2 class="font-extrabold">{{ $queue['title'] }}</h2><p class="text-sm text-neutral-500">{{ $queue['description'] }}</p></div><span class="status-badge">{{ $items->count() }}</span></div><div class="divide-y divide-neutral-200">
                @forelse($items as $match)<a href="{{ route('admin.matches.show',$match) }}" class="group flex items-start justify-between gap-4 p-4 transition hover:bg-neutral-50"><div><p class="font-bold group-hover:text-isc2-green">{{ $match->mentor->name }} and {{ $match->mentee->name }}</p><p class="mt-1 text-xs text-neutral-500">{{ str($match->status)->replace('-',' ')->title() }}@if($match->started_at) · Started {{ $match->started_at->format('j M Y') }}@endif</p>@if($queue['key']==='checkInsOutstanding')<p class="mt-1 text-xs font-semibold text-neutral-600">{{ $match->current_check_ins_count }} of 2 monthly check-ins complete</p>@endif</div><span class="shrink-0 text-sm font-bold text-isc2-green">Review →</span></a>@empty<p class="p-4 text-sm text-neutral-500">No relationships in this queue.</p>@endforelse
            </div></section>
        @endforeach

        <section class="panel overflow-hidden"><div class="panel-header"><div><h2 class="font-extrabold">Overdue milestones</h2><p class="text-sm text-neutral-500">An agreed action has passed its due date.</p></div><span class="status-badge">{{ $queues['overdueMilestones']->count() }}</span></div><div class="divide-y divide-neutral-200">@forelse($queues['overdueMilestones'] as $milestone)@php($relationship=$milestone->goal->mentoringMatch)<a href="{{ route('admin.matches.show',$relationship) }}" class="group flex items-start justify-between gap-4 p-4 transition hover:bg-neutral-50"><div><p class="font-bold group-hover:text-isc2-green">{{ $milestone->title }}</p><p class="mt-1 text-xs text-red-700">Due {{ $milestone->due_on->format('j M Y') }} · {{ $milestone->due_on->diffForHumans() }}</p><p class="mt-1 text-xs text-neutral-500">{{ $relationship->mentor->name }} and {{ $relationship->mentee->name }} · Owner: {{ $milestone->owner?->name ?? 'Shared' }}</p></div><span class="shrink-0 text-sm font-bold text-isc2-green">Review →</span></a>@empty<p class="p-4 text-sm text-neutral-500">No overdue milestones.</p>@endforelse</div></section>

    </div>
</x-admin.shell>
