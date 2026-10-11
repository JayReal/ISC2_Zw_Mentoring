<x-admin.shell title="Staff access">
    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
        <section>
            <p class="section-kicker">Step 1</p>
            <h2 class="section-heading mt-1">Add or find a staff member</h2>
            <p class="lede mt-2">Search for an existing registered account, select the programme roles they need, then save. Mentor and mentee participation will not be changed.</p>
            <form method="GET" action="{{ route('admin.staff-access.index') }}" class="mt-5 flex flex-col gap-3 rounded-xl border border-neutral-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end">
                <div class="field min-w-0 flex-1">
                    <label for="search">Name or registered email address</label>
                    <input id="search" name="search" value="{{ $search }}" placeholder="For example, member@example.org">
                </div>
                <div class="flex gap-2">
                    <button class="button-primary" type="submit">Find user</button>
                    @if($search !== '')<a class="button-secondary" href="{{ route('admin.staff-access.index') }}">Clear</a>@endif
                </div>
            </form>
        </section>

        <aside class="rounded-xl bg-isc2-dark-green p-5 text-white">
            <p class="text-sm font-semibold text-[#9ac23c]">Access safeguards</p>
            <ul class="mt-3 grid gap-2 text-sm leading-6 text-neutral-200">
                <li>Only administrators can make changes.</li>
                <li>Every change is recorded.</li>
                <li>Users are notified automatically.</li>
                <li>Unverified accounts cannot enter staff areas.</li>
            </ul>
        </aside>
    </div>

    <details class="panel mt-5 p-4">
        <summary class="cursor-pointer text-sm font-semibold text-isc2-green">Compare staff roles and access</summary>
        <dl class="mt-4 grid gap-4 border-t border-neutral-200 pt-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($roleOptions as $option)
                <div><dt class="text-sm font-semibold">{{ $option['label'] }}</dt><dd class="mt-1 text-sm leading-5 text-neutral-600">{{ $option['description'] }}</dd></div>
            @endforeach
        </dl>
    </details>

    <div class="mt-6 flex items-end justify-between gap-4">
        <div>
            <p class="section-kicker">Step 2</p>
            <h2 class="section-heading mt-1">{{ $search !== '' ? 'Select access for this user' : 'Current programme staff' }}</h2>
            <p class="mt-1 text-sm text-neutral-600">{{ $search !== '' ? 'Tick one or more roles, then select Save access.' : 'Search above to grant access to another registered user.' }}</p>
        </div>
        <span class="status-badge">{{ $users->total() }} {{ str('user')->plural($users->total()) }}</span>
    </div>

    <div class="mt-4 grid gap-4">
        @forelse($users as $staffUser)
            @php($staffRoles = array_values(array_intersect($staffUser->roles ?? [], array_keys($roleOptions))))
            <article class="panel p-4 sm:p-5">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                    <div class="min-w-0 xl:w-64 xl:shrink-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="truncate text-base font-semibold">{{ $staffUser->name }}</h3>
                            @if(auth()->user()->is($staffUser))<span class="status-badge">Your account</span>@endif
                        </div>
                        <p class="mt-1 break-all text-sm text-neutral-600">{{ $staffUser->email }}</p>
                        <p class="mt-2 text-xs font-medium {{ $staffUser->hasVerifiedEmail() ? 'text-isc2-green' : 'text-amber-700' }}">{{ $staffUser->hasVerifiedEmail() ? 'Email verified' : 'Verification pending — access starts after verification' }}</p>
                    </div>

                    @if(auth()->user()->is($staffUser))
                        <div class="w-full rounded-lg border border-neutral-200 bg-neutral-50 px-4 py-3 text-sm text-neutral-600 xl:max-w-3xl">For safety, another administrator must change your access. This prevents accidental self-lockout.</div>
                    @else
                        <div class="w-full xl:max-w-4xl">
                            <form method="POST" action="{{ route('admin.staff-access.update', $staffUser) }}">
                                @csrf
                                @method('PUT')
                                <fieldset>
                                    <legend class="sr-only">Programme roles for {{ $staffUser->name }}</legend>
                                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                                        @foreach($roleOptions as $role => $option)
                                            <label class="flex min-h-11 cursor-pointer items-center gap-2.5 rounded-lg border border-neutral-200 px-3 py-2 transition hover:border-neutral-300 hover:bg-neutral-50">
                                                <input type="checkbox" name="roles[]" value="{{ $role }}" @checked(in_array($role, $staffRoles, true))>
                                                <span class="text-sm font-medium">{{ $option['label'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </fieldset>
                                <div class="mt-3 grid gap-3 sm:grid-cols-[minmax(14rem,22rem)_auto] sm:items-end sm:justify-end">
                                    <div class="field"><label for="current-password-{{ $staffUser->id }}">Your current password</label><input id="current-password-{{ $staffUser->id }}" name="current_password" type="password" required autocomplete="current-password"><p class="field-help">Required to confirm this sensitive change.</p></div>
                                    <button class="button-primary" type="submit">Save access</button>
                                </div>
                            </form>

                            @if($staffRoles !== [])
                                <details class="mt-3 border-t border-neutral-200 pt-3">
                                    <summary class="cursor-pointer text-sm font-medium text-red-700">Remove all staff access</summary>
                                    <form method="POST" action="{{ route('admin.staff-access.destroy', $staffUser) }}" class="mt-3 grid gap-3 rounded-lg border border-red-200 bg-red-50 p-3 sm:grid-cols-[1fr_minmax(14rem,20rem)_auto] sm:items-end">
                                        @csrf
                                        @method('DELETE')
                                        <p class="text-sm leading-5 text-red-900">This removes programme staff roles only. Mentor or mentee participation is preserved.</p>
                                        <div class="field"><label for="remove-password-{{ $staffUser->id }}">Your current password</label><input id="remove-password-{{ $staffUser->id }}" name="current_password" type="password" required autocomplete="current-password"></div>
                                        <button class="inline-flex min-h-10 shrink-0 items-center justify-center rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-800 transition hover:bg-red-100" type="submit" name="confirm_removal" value="yes">Confirm removal</button>
                                    </form>
                                </details>
                            @endif
                        </div>
                    @endif
                </div>
            </article>
        @empty
            <div class="panel p-8 text-center">
                <h2 class="text-lg font-semibold">No registered user found</h2>
                <p class="mt-2 text-sm text-neutral-600">Check the spelling or email address. A person must register before programme access can be assigned.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $users->links() }}</div>

    <section class="panel mt-8 overflow-hidden">
        <div class="panel-header"><div><p class="section-kicker">Audit trail</p><h2 class="text-lg font-semibold">Recent access changes</h2></div><span class="status-badge">Latest {{ $accessChanges->count() }}</span></div>
        <div class="divide-y divide-neutral-200">
            @forelse($accessChanges as $change)
                @php($beforeLabels = collect($change->changes['before'] ?? [])->map(fn ($role) => $roleOptions[$role]['label'] ?? $role)->implode(', '))
                @php($afterLabels = collect($change->changes['after'] ?? [])->map(fn ($role) => $roleOptions[$role]['label'] ?? $role)->implode(', '))
                <div class="grid gap-1 px-4 py-3 text-sm sm:grid-cols-[minmax(12rem,.8fr)_minmax(0,1.5fr)_auto] sm:items-center sm:gap-4 sm:px-5">
                    <div><p class="font-semibold">{{ $change->subject?->name ?? 'Deleted user' }}</p><p class="text-xs text-neutral-500">Changed by {{ $change->actor?->name ?? 'System' }}</p></div>
                    <p class="text-neutral-600"><span class="text-neutral-500">From:</span> {{ $beforeLabels ?: 'No staff access' }} <span class="mx-1 text-neutral-400">→</span> <span class="text-neutral-500">To:</span> {{ $afterLabels ?: 'No staff access' }}</p>
                    <time class="text-xs text-neutral-500" datetime="{{ $change->created_at->toIso8601String() }}">{{ $change->created_at->diffForHumans() }}</time>
                </div>
            @empty
                <p class="p-5 text-sm text-neutral-500">No staff access changes have been recorded yet.</p>
            @endforelse
        </div>
    </section>
</x-admin.shell>
