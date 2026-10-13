<x-admin.shell title="Propose a mentoring match">
    <form class="mx-auto grid max-w-5xl gap-5" method="POST" action="{{ route('admin.matches.store') }}">
        @csrf

        @if($errors->any())<div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-900" role="alert"><p class="font-semibold">Please review the proposal.</p><ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <section class="panel overflow-hidden" aria-labelledby="participants-heading">
            <div class="panel-header"><div><p class="section-kicker">Human-reviewed selection</p><h2 id="participants-heading" class="mt-1 text-xl font-semibold">Compare the participants</h2><p class="mt-1 text-sm leading-6 text-neutral-600">Review the goal, relevant experience, working preferences and practical availability before writing the rationale.</p></div></div>
            <div class="grid gap-6 p-5 sm:p-7">
                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="field"><label for="mentor_id">Eligible mentor</label><select id="mentor_id" name="mentor_id" data-profile-select="mentor" required><option value="">Select mentor</option>@foreach($mentors as $mentor)<option value="{{ $mentor->id }}" @selected((int) old('mentor_id') === $mentor->id)>{{ $mentor->name }} — {{ $mentor->participantProfile->primaryCluster?->name ?? 'Cross-cluster' }} — {{ $mentor->reserved_matches_count }}/{{ $mentor->participantProfile->mentor_capacity }} places used</option>@endforeach</select><p class="field-help">Approved, oriented and available mentors only. Capacity is checked again when saved.</p></div>
                    <div class="field"><label for="mentee_id">Approved mentee</label><select id="mentee_id" name="mentee_id" data-profile-select="mentee" required><option value="">Select mentee</option>@foreach($mentees as $mentee)<option value="{{ $mentee->id }}" @selected((int) old('mentee_id') === $mentee->id)>{{ $mentee->name }} — {{ config('mentoring.pathways.'.$mentee->participantProfile->pathway.'.label', 'Pathway not selected') }}</option>@endforeach</select><p class="field-help">Only participants currently approved for matching are listed.</p></div>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <div>
                        <div class="rounded-xl border border-dashed border-neutral-300 p-5 text-sm text-neutral-500" data-profile-placeholder="mentor">Select a mentor to review their matching context.</div>
                        @foreach($mentors as $mentor)
                            @php($profile = $mentor->participantProfile)
                            <article class="panel hidden h-full p-5" data-profile-card="mentor" data-user-id="{{ $mentor->id }}" data-availability="{{ collect($profile->availability ?? [])->join(',') }}" data-clusters="{{ collect([$profile->primary_cluster_id])->merge($profile->secondary_cluster_ids ?? [])->filter()->unique()->join(',') }}" data-styles="{{ collect($profile->mentoring_styles ?? [])->join(',') }}" data-format="{{ $profile->preferred_format }}" data-language="{{ str($profile->preferred_language)->lower() }}">
                                <div class="flex items-start justify-between gap-3"><div><p class="section-kicker">Mentor</p><h3 class="mt-1 text-lg font-semibold">{{ $mentor->name }}</h3></div><span class="status-badge">{{ $mentor->reserved_matches_count }}/{{ $profile->mentor_capacity }} places</span></div>
                                <dl class="mt-4 grid gap-4 text-sm">
                                    <div><dt class="font-medium text-neutral-900">Relevant experience</dt><dd class="mt-1 whitespace-pre-line leading-6 text-neutral-600">{{ $profile->mentor_expertise ?: $profile->experience_summary ?: 'Not supplied' }}</dd></div>
                                    <div><dt class="font-medium text-neutral-900">Useful outcomes</dt><dd class="mt-1 whitespace-pre-line leading-6 text-neutral-600">{{ $profile->goals ?: 'Not supplied' }}</dd></div>
                                    <div class="grid gap-3 sm:grid-cols-2"><div><dt class="font-medium text-neutral-900">Communities</dt><dd class="mt-1 text-neutral-600">{{ collect([$profile->primaryCluster?->name])->merge(collect($profile->secondary_cluster_ids ?? [])->map(fn ($id) => $clusterNames->get($id)))->filter()->unique()->join(', ') ?: 'Not supplied' }}</dd></div><div><dt class="font-medium text-neutral-900">Format and language</dt><dd class="mt-1 text-neutral-600">{{ ucfirst($profile->preferred_format) }} · {{ $profile->preferred_language }}</dd></div></div>
                                    <div class="grid gap-3 sm:grid-cols-2"><div><dt class="font-medium text-neutral-900">Availability</dt><dd class="mt-1 text-neutral-600">{{ collect($profile->availability ?? [])->map(fn ($item) => str($item)->replace('-', ' ')->title())->join(', ') ?: 'Not supplied' }}</dd></div><div><dt class="font-medium text-neutral-900">Working style</dt><dd class="mt-1 text-neutral-600">{{ collect($profile->mentoring_styles ?? [])->map(fn ($style) => config('mentoring.styles.'.$style.'.label', str($style)->replace('-', ' ')->title()))->join(', ') ?: 'No preference recorded' }}</dd></div></div>
                                </dl>
                            </article>
                        @endforeach
                    </div>

                    <div>
                        <div class="rounded-xl border border-dashed border-neutral-300 p-5 text-sm text-neutral-500" data-profile-placeholder="mentee">Select a mentee to review their matching context.</div>
                        @foreach($mentees as $mentee)
                            @php($profile = $mentee->participantProfile)
                            <article class="panel hidden h-full p-5" data-profile-card="mentee" data-user-id="{{ $mentee->id }}" data-availability="{{ collect($profile->availability ?? [])->join(',') }}" data-clusters="{{ collect([$profile->primary_cluster_id])->merge($profile->secondary_cluster_ids ?? [])->filter()->unique()->join(',') }}" data-styles="{{ collect($profile->mentoring_styles ?? [])->join(',') }}" data-format="{{ $profile->preferred_format }}" data-language="{{ str($profile->preferred_language)->lower() }}">
                                <div><p class="section-kicker">Mentee</p><h3 class="mt-1 text-lg font-semibold">{{ $mentee->name }}</h3><p class="mt-1 text-sm text-neutral-500">{{ config('mentoring.pathways.'.$profile->pathway.'.label', 'Pathway not selected') }}</p></div>
                                <dl class="mt-4 grid gap-4 text-sm">
                                    <div><dt class="font-medium text-neutral-900">Current goal</dt><dd class="mt-1 whitespace-pre-line leading-6 text-neutral-600">{{ $profile->goals ?: 'Not supplied' }}</dd></div>
                                    <div><dt class="font-medium text-neutral-900">Experience and perspective</dt><dd class="mt-1 whitespace-pre-line leading-6 text-neutral-600">{{ $profile->experience_summary ?: 'Not supplied' }}</dd></div>
                                    <div class="grid gap-3 sm:grid-cols-2"><div><dt class="font-medium text-neutral-900">Communities</dt><dd class="mt-1 text-neutral-600">{{ collect([$profile->primaryCluster?->name])->merge(collect($profile->secondary_cluster_ids ?? [])->map(fn ($id) => $clusterNames->get($id)))->filter()->unique()->join(', ') ?: 'Not supplied' }}</dd></div><div><dt class="font-medium text-neutral-900">Format and language</dt><dd class="mt-1 text-neutral-600">{{ ucfirst($profile->preferred_format) }} · {{ $profile->preferred_language }}</dd></div></div>
                                    <div class="grid gap-3 sm:grid-cols-2"><div><dt class="font-medium text-neutral-900">Availability</dt><dd class="mt-1 text-neutral-600">{{ collect($profile->availability ?? [])->map(fn ($item) => str($item)->replace('-', ' ')->title())->join(', ') ?: 'Not supplied' }}</dd></div><div><dt class="font-medium text-neutral-900">Working style</dt><dd class="mt-1 text-neutral-600">{{ collect($profile->mentoring_styles ?? [])->map(fn ($style) => config('mentoring.styles.'.$style.'.label', str($style)->replace('-', ' ')->title()))->join(', ') ?: 'No preference recorded' }}</dd></div></div>
                                </dl>
                            </article>
                        @endforeach
                    </div>
                </div>

                <section class="hidden rounded-xl border border-neutral-200 bg-neutral-50 p-4" data-overlap-panel aria-live="polite">
                    <div><p class="font-medium text-neutral-900">Practical overlap</p><p class="mt-1 text-xs leading-5 text-neutral-600">Review aids only. These factual indicators do not recommend, rank or score a match.</p></div>
                    <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
                        <p class="rounded-lg bg-white px-3 py-2 text-xs" data-overlap="availability">Availability not compared</p>
                        <p class="rounded-lg bg-white px-3 py-2 text-xs" data-overlap="format">Meeting format not compared</p>
                        <p class="rounded-lg bg-white px-3 py-2 text-xs" data-overlap="clusters">Communities not compared</p>
                        <p class="rounded-lg bg-white px-3 py-2 text-xs" data-overlap="styles">Working style not compared</p>
                        <p class="rounded-lg bg-white px-3 py-2 text-xs" data-overlap="language">Language not compared</p>
                    </div>
                </section>

                <p class="rounded-lg bg-neutral-50 p-3 text-xs leading-5 text-neutral-600">Conflict, accessibility and safeguarding information remains restricted and must be checked separately by an authorised staff member before a proposal is created.</p>
            </div>
        </section>

        <section class="panel grid gap-5 p-5 sm:p-7" aria-labelledby="proposal-heading">
            <div><p class="section-kicker">Proposal record</p><h2 id="proposal-heading" class="mt-1 text-xl font-semibold">Record the reviewed decision</h2></div>
            <div class="grid gap-4 sm:grid-cols-3"><div class="field"><label for="tier">Support level</label><select id="tier" name="tier">@foreach(['community', 'matched', 'specialist'] as $tier)<option value="{{ $tier }}" @selected(old('tier') === $tier)>{{ ucfirst($tier) }}</option>@endforeach</select></div><div class="field"><label for="cluster_id">Primary cluster for this match</label><select id="cluster_id" name="cluster_id"><option value="">Not assigned</option>@foreach($clusters as $cluster)<option value="{{ $cluster->id }}" @selected((int) old('cluster_id') === $cluster->id)>{{ $cluster->name }}</option>@endforeach</select></div><div class="field"><label for="compatibility_score">Optional administrative score</label><input id="compatibility_score" name="compatibility_score" value="{{ old('compatibility_score') }}" type="number" min="0" max="100" placeholder="0–100"><p class="field-help">For recording judgement only; it does not create or recommend a match.</p></div></div>
            <div class="field"><label for="programme_cycle_id">Programme cycle</label><select id="programme_cycle_id" name="programme_cycle_id"><option value="">Unassigned</option>@foreach($cycles as $cycle)<option value="{{ $cycle->id }}" @selected((int) old('programme_cycle_id') === $cycle->id)>{{ $cycle->name }}</option>@endforeach</select></div>
            <div class="field"><label for="rationale">Explainable matching rationale</label><textarea id="rationale" name="rationale" rows="6" required placeholder="Describe goal alignment, relevant expertise, availability, format, capacity and completed checks.">{{ old('rationale') }}</textarea><p class="field-help">Do not include confidential conflict or safeguarding details. The proposal expires after {{ config('mentoring.proposal_expiry_days') }} days if unanswered.</p></div>
            <button class="button-primary justify-self-end">Create proposal</button>
        </section>
    </form>
</x-admin.shell>
