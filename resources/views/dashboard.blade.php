<x-layouts.app title="Dashboard">
    <div class="page-shell py-7 sm:py-10">
        @if(session('status'))<div class="notice-success mb-6" role="status">{{ session('status') }}</div>@endif

        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div><p class="section-kicker">Participant workspace</p><h1 class="page-heading mt-1.5">Welcome, {{ auth()->user()->name }}</h1><p class="mt-3 text-base text-neutral-600">Here is where your application and mentoring activity stand today.</p></div>
            <span class="status-badge">Intake: {{ str($profile->intake_status)->replace('_',' ')->title() }}</span>
        </div>

        <div class="mt-7 grid gap-5 lg:grid-cols-[1.4fr_.6fr]">
            <section class="panel overflow-hidden">
                <div class="panel-header"><div><p class="section-kicker">Your next step</p><h2 class="mt-1 text-xl font-extrabold">@if(!$intakeComplete) Complete your participant profile @elseif($actionMatch && in_array($actionMatch->status,['proposed','pending-confirmation'],true)) Review your match proposal @elseif($nextAction) {{ $nextAction['title'] }} @else Your profile is in programme review @endif</h2></div></div>
                <div class="p-5 sm:p-6">
                    @if(!$intakeComplete)
                        <p class="max-w-3xl text-sm leading-6 text-neutral-600">Add your goals, interests, availability and preferred working style. Programme staff cannot prepare a suitable match until this information is complete.</p><a class="button-primary mt-5" href="{{ route('intake.edit') }}">Complete profile</a>
                    @elseif($actionMatch && in_array($actionMatch->status,['proposed','pending-confirmation'],true))
                        <p class="max-w-3xl text-sm leading-6 text-neutral-600">A proposed {{ $profile->participation_type === 'mentor' ? 'mentee' : 'mentor' }} is waiting for your review. Open the proposal to read the rationale and accept or decline it.</p><a class="button-primary mt-5" href="{{ route('matches.show',$actionMatch) }}">Review match proposal</a>
                    @elseif($nextAction)
                        <p class="max-w-3xl text-sm leading-6 text-neutral-600">{{ $nextAction['description'] }}</p><a class="button-primary mt-5" href="{{ route('matches.show',$actionMatch) }}">{{ $nextAction['label'] }}</a>
                    @else
                        <p class="max-w-3xl text-sm leading-6 text-neutral-600">No further action is required right now. Programme staff are checking pathway fit, mentor capacity, availability and potential conflicts. You will receive an update when a proposal is ready.</p>
                        <div class="mt-5 flex flex-col gap-3 sm:flex-row"><a class="button-secondary" href="{{ route('intake.edit') }}">Review or update profile</a><a class="button-secondary" href="{{ route('matches.index') }}">View mentoring area</a></div>
                    @endif
                </div>
            </section>

            <aside class="rounded-2xl bg-isc2-dark-green p-5 text-white shadow-sm sm:p-6">
                <p class="text-xs font-bold tracking-[0.14em] text-[#9ac23c] uppercase">Your participation</p><h2 class="mt-2 text-xl font-extrabold">{{ $profile->participation_type === 'both' ? 'Mentor and mentee' : ucfirst($profile->participation_type) }}</h2>
                <p class="mt-3 text-sm leading-6 text-neutral-200">@if($profile->participation_type === 'mentor') You may receive a proposal when participant goals align with your experience, availability and capacity. @elseif($profile->participation_type === 'both') The programme may consider you separately as a mentor and as a mentee. Each proposal requires your confirmation. @else You may receive a mentor proposal after staff complete suitability, capacity and conflict checks. @endif</p>
                <dl class="mt-5 space-y-4 border-t border-white/15 pt-4 text-sm"><div><dt class="text-xs font-bold text-neutral-400 uppercase">Pathway</dt><dd class="mt-1 font-semibold">{{ config('mentoring.pathways.'.$profile->pathway.'.label', str($profile->pathway)->replace('-',' ')->title()) }}</dd><dd class="mt-1 text-xs leading-5 text-neutral-300">{{ config('mentoring.pathways.'.$profile->pathway.'.description') }}</dd></div><div><dt class="text-xs font-bold text-neutral-400 uppercase">Primary cluster</dt><dd class="mt-1 font-semibold">{{ $profile->primaryCluster?->name ?? 'Not selected' }}</dd></div></dl>
            </aside>
        </div>

        @php($hasActiveRelationship = $matches->where('status','active')->isNotEmpty())
        @unless($hasActiveRelationship)
        <section class="mt-7"><div><p class="section-kicker">How the process works</p><h2 class="mt-1 text-2xl font-extrabold">Your mentoring journey</h2></div><ol class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">@foreach([['Intake','Complete','Your profile and consent are recorded.'],['Review',$matches->isEmpty() ? 'Current' : 'Complete','Staff check fit, capacity and conflicts.'],['Confirmation',$matches->whereIn('status',['proposed','pending-confirmation'])->isNotEmpty() ? 'Current' : ($matches->whereIn('status',['active','closed'])->isNotEmpty() ? 'Complete' : 'Upcoming'),'Both participants approve the proposal.'],['Shared plan',$matches->where('status','closed')->isNotEmpty() ? 'Complete' : 'Upcoming','Agree goals, milestones and progress notes.'],['Close and reflect',$matches->where('status','closed')->isNotEmpty() ? 'Complete' : 'Upcoming','Confirm outcomes and keep a concise record.']] as [$title,$state,$description])<li class="panel p-4"><div class="flex items-center justify-between gap-3"><span class="grid size-8 place-items-center rounded-full {{ $state==='Complete' ? 'bg-isc2-green text-white' : ($state==='Current' ? 'bg-isc2-dark-green text-white' : 'bg-neutral-100 text-neutral-500') }} text-sm font-extrabold">{{ $loop->iteration }}</span><span class="text-[10px] font-bold tracking-wide text-neutral-500 uppercase">{{ $state }}</span></div><h3 class="mt-4 font-extrabold">{{ $title }}</h3><p class="mt-1 text-sm leading-6 text-neutral-600">{{ $description }}</p></li>@endforeach</ol></section>
        @endunless

        @if($matches->isNotEmpty())<section class="mt-7"><div class="flex items-end justify-between gap-4"><div><p class="section-kicker">Mentoring relationships</p><h2 class="mt-1 text-2xl font-extrabold">Your shared workspaces</h2></div><a class="text-sm font-bold text-isc2-green hover:underline" href="{{ route('matches.index') }}">View all</a></div><div class="mt-4 grid gap-4 md:grid-cols-2">@foreach($matches->take(4) as $match)@php($counterpart=$match->counterpartFor(auth()->user()))<a class="panel p-5 transition hover:border-isc2-green/40 hover:shadow-md" href="{{ route('matches.show',$match) }}"><div class="flex items-start justify-between gap-3"><div><p class="text-xs font-bold text-neutral-500 uppercase">{{ (int) auth()->id()===(int) $match->mentor_id ? 'Mentee' : 'Mentor' }}</p><h3 class="mt-1 text-lg font-extrabold">{{ $counterpart->name }}</h3></div><span class="status-badge">{{ str($match->status)->replace('-',' ')->title() }}</span></div>@if($match->last_activity_at)<p class="mt-3 text-xs text-neutral-500">Last activity {{ $match->last_activity_at->diffForHumans() }}</p>@endif</a>@endforeach</div></section>@endif

        <section class="mt-7 grid gap-4 sm:grid-cols-3"><a class="panel p-5 transition hover:border-isc2-green/40" href="{{ in_array($profile->participation_type,['mentor','both'],true) ? route('mentor-readiness.edit') : route('intake.edit') }}"><p class="text-sm font-extrabold">{{ in_array($profile->participation_type,['mentor','both'],true) ? 'Mentor readiness' : 'Update profile' }}</p><p class="mt-1 text-sm text-neutral-600">{{ in_array($profile->participation_type,['mentor','both'],true) ? 'Manage expertise, capacity and availability.' : 'Keep availability, goals and preferences current.' }}</p></a><a class="panel p-5 transition hover:border-isc2-green/40" href="{{ route('notifications.index') }}"><p class="text-sm font-extrabold">View updates @if(auth()->user()->unreadNotifications()->count())<span class="ml-1 text-isc2-green">({{ auth()->user()->unreadNotifications()->count() }})</span>@endif</p><p class="mt-1 text-sm text-neutral-600">Read match decisions, notes and actions.</p></a><div class="panel p-5"><p class="text-sm font-extrabold">Need support?</p><p class="mt-1 text-sm text-neutral-600">Open an active mentoring workspace to request confidential programme support or rematching.</p></div></section>
    </div>
</x-layouts.app>
