@php
    $isMentor = in_array($profile->participation_type, ['mentor', 'both'], true);
    $isMentee = in_array($profile->participation_type, ['mentee', 'both'], true);
    $selectedSecondaryClusters = array_map('intval', old('secondary_cluster_ids', $profile->secondary_cluster_ids ?? []));
    $selectedStyles = old('mentoring_styles', $profile->mentoring_styles ?? []);
    $universityStatus = old('university_context_status', filled($profile->university_context) ? 'applies' : 'not-applicable');
    $conflictStatus = old('conflict_status', filled($profile->conflict_declarations) ? 'declare' : 'none');
    $hasSubmittedProfile = ! in_array($profile->intake_status, ['not_started', 'in_progress'], true);
    $pageTitle = match ($profile->participation_type) {
        'mentor' => 'Tell us how you can contribute',
        'both' => 'Shape both sides of your mentoring experience',
        default => 'Tell us what useful support looks like',
    };
@endphp

<x-layouts.app title="Complete Your Profile">
    <div class="page-shell py-8 sm:py-12">
        <div class="content-shell">
            <p class="section-kicker">Private participant profile</p>
            <h1 class="page-heading mt-1.5">{{ $pageTitle }}</h1>
            <p class="lede mt-3">Programme staff use these answers to prepare an explainable suggestion. You can update them as your priorities change.</p>

            <form method="POST" action="{{ route('intake.update') }}" class="mt-7 grid gap-6">
                @csrf
                @method('PUT')

                <section class="panel overflow-hidden" aria-labelledby="focus-heading">
                    <div class="panel-header"><div><p class="section-kicker">Step 1 of 3</p><h2 id="focus-heading" class="mt-1 text-xl font-semibold">Choose the focus for this cycle</h2></div></div>
                    <div class="grid gap-6 p-5 sm:p-7">
                        <fieldset class="grid gap-3">
                            <legend>Primary pathway</legend>
                            <p class="field-help">Choose the single outcome that matters most over the next 3–6 months. This is a starting point, not a permanent label. Choose Explore if you are still deciding.</p>
                            <div class="grid gap-3 sm:grid-cols-2">
                                @foreach(config('mentoring.pathways') as $value => $pathway)
                                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-neutral-200 bg-neutral-50 p-4 font-normal transition hover:border-isc2-green/40 has-[:checked]:border-isc2-green has-[:checked]:bg-green-50/60">
                                        <input class="mt-1" type="radio" name="pathway" value="{{ $value }}" required @checked(old('pathway', $profile->pathway) === $value)>
                                        <span><span class="block text-sm font-semibold text-neutral-950">{{ $pathway['label'] }}</span><span class="mt-1 block text-sm leading-5 text-neutral-600">{{ $pathway['description'] }}</span></span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <div class="grid gap-2">
                            <label for="primary_cluster_id">Primary professional cluster</label>
                            <p class="field-help">Select the community of practice most closely connected to your main goal.</p>
                            <select id="primary_cluster_id" name="primary_cluster_id" data-primary-selection="secondary-clusters" required>@foreach($clusters as $cluster)<option value="{{ $cluster->id }}" @selected((int) old('primary_cluster_id', $profile->primary_cluster_id) === $cluster->id)>{{ $cluster->name }}</option>@endforeach</select>
                        </div>

                        <fieldset class="grid gap-3" aria-describedby="secondary-clusters-help secondary-clusters-count">
                            <legend>Other communities of interest <span class="font-normal text-neutral-500">(optional)</span></legend>
                            <div class="flex flex-wrap items-center justify-between gap-2"><p id="secondary-clusters-help" class="field-help">Choose up to three additional communities. Your primary cluster is unavailable here.</p><p id="secondary-clusters-count" class="text-xs font-medium text-neutral-500" data-selection-status="secondary-clusters" aria-live="polite">{{ count($selectedSecondaryClusters) }} of 3 selected</p></div>
                            <div class="grid gap-2 sm:grid-cols-2">@foreach($clusters as $cluster)<label class="flex items-center gap-2 rounded-lg border border-neutral-200 p-3 font-normal has-[:disabled]:cursor-not-allowed has-[:disabled]:bg-neutral-100 has-[:disabled]:text-neutral-400"><input type="checkbox" name="secondary_cluster_ids[]" value="{{ $cluster->id }}" data-limited-group="secondary-clusters" data-selection-limit="3" data-primary-sensitive="secondary-clusters" @checked(in_array($cluster->id, $selectedSecondaryClusters, true))>{{ $cluster->name }}</label>@endforeach</div>
                            @error('secondary_cluster_ids')<p id="secondary-clusters-error" class="error-text text-sm text-red-700">{{ $message }}</p>@enderror
                        </fieldset>
                    </div>
                </section>

                <section class="panel overflow-hidden" aria-labelledby="relationship-heading">
                    <div class="panel-header"><div><p class="section-kicker">Step 2 of 3</p><h2 id="relationship-heading" class="mt-1 text-xl font-semibold">Describe a useful working relationship</h2></div></div>
                    <div class="grid gap-6 p-5 sm:p-7">
                        <div class="grid gap-2">
                            <label for="goals">@if($isMentor && !$isMentee) What outcomes would you be most useful in helping someone achieve? @elseif($isMentor && $isMentee) What do you want to achieve, and what outcomes can you help someone else achieve? @else What do you want to achieve in the next 3–6 months? @endif</label>
                            <p class="field-help">Be specific enough for staff to understand what a useful match would work on.</p>
                            <textarea id="goals" name="goals" rows="4" required>{{ old('goals', $profile->goals) }}</textarea>
                            @error('goals')<p class="error-text text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid gap-2">
                            <label for="experience_summary">@if($isMentor && !$isMentee) What experience, perspective or practical knowledge can you share? @elseif($isMentor && $isMentee) What experience do you bring, and where would you value support? @else What experience and perspective do you bring? @endif</label>
                            <textarea id="experience_summary" name="experience_summary" rows="4" required>{{ old('experience_summary', $profile->experience_summary) }}</textarea>
                        </div>

                        <fieldset class="grid gap-3">
                            <legend>Availability</legend><p class="field-help">Select every period in which you can usually meet.</p>
                            <div class="grid gap-2 sm:grid-cols-2">@foreach(['weekday-morning' => 'Weekday mornings', 'weekday-afternoon' => 'Weekday afternoons', 'weekday-evening' => 'Weekday evenings', 'weekend' => 'Weekends'] as $value => $label)<label class="flex items-center gap-2 rounded-lg border border-neutral-200 p-3 font-normal"><input type="checkbox" name="availability[]" value="{{ $value }}" @checked(in_array($value, old('availability', $profile->availability ?? []), true))>{{ $label }}</label>@endforeach</div>
                        </fieldset>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2"><label for="preferred_language">Preferred language</label><input id="preferred_language" name="preferred_language" value="{{ old('preferred_language', $profile->preferred_language) }}" required></div>
                            <div class="grid gap-2"><label for="preferred_format">Meeting format</label><select id="preferred_format" name="preferred_format">@foreach(['virtual' => 'Virtual', 'in-person' => 'In person', 'hybrid' => 'Hybrid'] as $value => $label)<option value="{{ $value }}" @selected(old('preferred_format', $profile->preferred_format) === $value)>{{ $label }}</option>@endforeach</select></div>
                        </div>

                        <fieldset class="grid gap-3" aria-describedby="mentoring-styles-help mentoring-styles-count">
                            <legend>Preferred way of working <span class="font-normal text-neutral-500">(choose up to three)</span></legend>
                            <div class="flex flex-wrap items-center justify-between gap-2"><p id="mentoring-styles-help" class="field-help">This helps both people begin with compatible expectations. A match can still adapt its approach together.</p><p id="mentoring-styles-count" class="text-xs font-medium text-neutral-500" data-selection-status="mentoring-styles" aria-live="polite">{{ count($selectedStyles) }} of 3 selected</p></div>
                            <div class="grid gap-3 sm:grid-cols-2">@foreach(config('mentoring.styles') as $value => $style)<label class="flex cursor-pointer items-start gap-3 rounded-xl border border-neutral-200 bg-neutral-50 p-4 font-normal has-[:checked]:border-isc2-green has-[:checked]:bg-green-50/60 has-[:disabled]:cursor-not-allowed has-[:disabled]:opacity-60"><input class="mt-1" type="checkbox" name="mentoring_styles[]" value="{{ $value }}" data-limited-group="mentoring-styles" data-selection-limit="3" @if($value === 'no-preference') data-exclusive-option @endif @checked(in_array($value, $selectedStyles, true))><span><span class="block text-sm font-semibold">{{ $style['label'] }}</span><span class="mt-1 block text-sm leading-5 text-neutral-600">{{ $style['description'] }}</span></span></label>@endforeach</div>
                            @error('mentoring_styles')<p class="error-text text-sm text-red-700">{{ $message }}</p>@enderror
                        </fieldset>

                        <div class="grid gap-2"><label for="mentoring_style">Anything else about how you prefer to work? <span class="font-normal text-neutral-500">(optional)</span></label><textarea id="mentoring_style" name="mentoring_style" rows="3">{{ old('mentoring_style', $profile->mentoring_style) }}</textarea></div>
                    </div>
                </section>

                <section class="panel overflow-hidden" aria-labelledby="context-heading">
                    <div class="panel-header"><div><p class="section-kicker">Step 3 of 3</p><h2 id="context-heading" class="mt-1 text-xl font-semibold">Share context staff should consider</h2></div></div>
                    <div class="grid gap-6 p-5 sm:p-7">
                        <fieldset class="grid gap-3">
                            <legend>Does a university or college context affect what you need?</legend>
                            <p class="field-help">For example: a course, final-year project, internship requirement, student timetable or transition from study to work.</p>
                            <div class="grid gap-2 sm:grid-cols-2">
                                <label class="flex items-center gap-2 rounded-lg border border-neutral-200 p-3 font-normal"><input type="radio" name="university_context_status" value="not-applicable" data-controls="university-context-panel" data-show-when="applies" aria-controls="university-context-panel" @checked($universityStatus === 'not-applicable')>No, this is not relevant</label>
                                <label class="flex items-center gap-2 rounded-lg border border-neutral-200 p-3 font-normal"><input type="radio" name="university_context_status" value="applies" data-controls="university-context-panel" data-show-when="applies" aria-controls="university-context-panel" @checked($universityStatus === 'applies')>Yes, staff should consider it</label>
                            </div>
                        </fieldset>
                        <div id="university-context-panel" class="grid gap-2"><label for="university_context">What should staff know about your study context?</label><textarea id="university_context" name="university_context" rows="3">{{ old('university_context', $profile->university_context) }}</textarea></div>

                        <div class="grid gap-2"><label for="accessibility_needs">Accessibility or participation needs <span class="font-normal text-neutral-500">(optional)</span></label><textarea id="accessibility_needs" name="accessibility_needs" rows="3">{{ old('accessibility_needs', $profile->accessibility_needs) }}</textarea><p class="field-help">Examples include captions, a preferred communication method, scheduling adjustments or another practical accommodation. This is restricted to staff who need it to arrange participation.</p></div>

                        <fieldset class="grid gap-3">
                            <legend>Are there any relationships or conflicts that should prevent a match?</legend>
                            <p class="field-help">For example: a current manager or direct report, close relative, active commercial dispute, or another relationship that could affect independence or comfort.</p>
                            <div class="grid gap-2 sm:grid-cols-2">
                                <label class="flex items-center gap-2 rounded-lg border border-neutral-200 p-3 font-normal"><input type="radio" name="conflict_status" value="none" data-controls="conflict-panel" data-show-when="declare" aria-controls="conflict-panel" @checked($conflictStatus === 'none')>No conflicts to declare</label>
                                <label class="flex items-center gap-2 rounded-lg border border-neutral-200 p-3 font-normal"><input type="radio" name="conflict_status" value="declare" data-controls="conflict-panel" data-show-when="declare" aria-controls="conflict-panel" @checked($conflictStatus === 'declare')>Yes, I need to tell staff</label>
                            </div>
                        </fieldset>
                        <div id="conflict-panel" class="grid gap-2"><label for="conflict_declarations">Who or what should staff avoid when considering a match?</label><textarea id="conflict_declarations" name="conflict_declarations" rows="3">{{ old('conflict_declarations', $profile->conflict_declarations) }}</textarea><p class="field-help">This is not shown to mentors or other participants.</p></div>
                    </div>
                </section>

                @if($errors->any())<div class="rounded-lg bg-red-50 p-4 text-red-900" role="alert"><p class="font-semibold">Please correct the form.</p><ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between"><p class="text-sm leading-6 text-neutral-500">@if($hasSubmittedProfile) Material changes made after approval are flagged for staff review without interrupting an active mentoring relationship. @else Submitting marks your profile ready for human review. It does not create a match automatically. @endif</p><button class="button-primary shrink-0">{{ $hasSubmittedProfile ? 'Save profile changes' : 'Save and submit for review' }}</button></div>
            </form>
        </div>
    </div>
</x-layouts.app>
