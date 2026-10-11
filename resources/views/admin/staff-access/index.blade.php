<x-admin.shell title="Staff access">
    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
        <div>
            <p class="lede">Assign only the access a programme team member needs. Participant roles are preserved when staff access changes.</p>
            <form method="GET" action="{{ route('admin.staff-access.index') }}" class="mt-5 flex flex-col gap-3 rounded-xl border border-neutral-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end">
                <div class="field min-w-0 flex-1">
                    <label for="search">Find a registered user</label>
                    <input id="search" name="search" value="{{ $search }}" placeholder="Search by name or email">
                </div>
                <div class="flex gap-2">
                    <button class="button-primary" type="submit">Search</button>
                    @if($search !== '')<a class="button-secondary" href="{{ route('admin.staff-access.index') }}">Clear</a>@endif
                </div>
            </form>
        </div>

        <aside class="rounded-xl bg-isc2-dark-green p-5 text-white">
            <p class="text-sm font-semibold text-[#9ac23c]">Administrator control</p>
            <p class="mt-2 text-sm leading-6 text-neutral-200">Only full administrators can view or change staff access. Every change is recorded in the audit trail.</p>
        </aside>
    </div>

    <details class="panel mt-5 p-4">
        <summary class="cursor-pointer text-sm font-semibold text-isc2-green">What each staff role can do</summary>
        <dl class="mt-4 grid gap-4 border-t border-neutral-200 pt-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($roleOptions as $option)
                <div><dt class="text-sm font-semibold">{{ $option['label'] }}</dt><dd class="mt-1 text-sm leading-5 text-neutral-600">{{ $option['description'] }}</dd></div>
            @endforeach
        </dl>
    </details>

    <div class="mt-6 flex items-end justify-between gap-4">
        <div>
            <h2 class="section-heading">{{ $search !== '' ? 'Search results' : 'Current programme staff' }}</h2>
            <p class="mt-1 text-sm text-neutral-600">{{ $search !== '' ? 'Select roles and save to grant or update access.' : 'Search above to add another registered user.' }}</p>
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
                        <p class="mt-2 text-xs {{ $staffUser->hasVerifiedEmail() ? 'text-isc2-green' : 'text-amber-700' }}">{{ $staffUser->hasVerifiedEmail() ? 'Email verified' : 'Email verification pending' }}</p>
                    </div>

                    @if(auth()->user()->is($staffUser))
                        <div class="w-full rounded-lg border border-neutral-200 bg-neutral-50 px-4 py-3 text-sm text-neutral-600 xl:max-w-3xl">For safety, change your own administrator access through another administrator.</div>
                    @else
                        <form method="POST" action="{{ route('admin.staff-access.update', $staffUser) }}" class="w-full xl:max-w-4xl">
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
                            <div class="mt-3 flex justify-end"><button class="button-primary" type="submit">Save access</button></div>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="panel p-8 text-center">
                <h2 class="text-lg font-semibold">No users found</h2>
                <p class="mt-2 text-sm text-neutral-600">Check the name or email address and try again. A person must register before staff access can be assigned.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $users->links() }}</div>
</x-admin.shell>
