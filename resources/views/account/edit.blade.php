<x-layouts.app title="Account settings">
    <div class="page-shell py-9 sm:py-14">
        <div class="content-shell">
            <p class="section-kicker">Your account</p>
            <h1 class="page-heading mt-1.5">Account and privacy settings</h1>
            <p class="lede mt-3">Keep your sign-in details current and send sensitive participation or data requests directly to authorised programme staff.</p>

            @if(session('status'))<div class="notice-success mt-6" role="status">{{ session('status') }}</div>@endif
            @if($errors->any())<div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900" role="alert"><p class="font-semibold">Please correct the highlighted information.</p></div>@endif

            <div class="mt-6 grid gap-5 lg:grid-cols-2 lg:items-start">
                <form method="POST" action="{{ route('account.update') }}" class="panel grid gap-4 p-5 sm:p-6">
                    @csrf @method('PUT')
                    <div><h2 class="text-lg font-semibold">Account details</h2><p class="mt-1 text-sm text-neutral-600">Changing your email address requires your password and a new email verification.</p></div>
                    <div class="field"><label for="name">Full name</label><input id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required maxlength="120" autocomplete="name">@error('name')<p class="error-text">{{ $message }}</p>@enderror</div>
                    <div class="field"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required autocomplete="email">@error('email')<p class="error-text">{{ $message }}</p>@enderror</div>
                    <div class="field"><label for="details-current-password">Current password <span class="font-normal text-neutral-500">(only when changing email)</span></label><input id="details-current-password" type="password" name="current_password" autocomplete="current-password">@error('current_password')<p class="error-text">{{ $message }}</p>@enderror</div>
                    <button class="button-primary justify-self-start">Save account details</button>
                </form>

                <form method="POST" action="{{ route('account.password.update') }}" class="panel grid gap-4 p-5 sm:p-6">
                    @csrf @method('PUT')
                    <div><h2 class="text-lg font-semibold">Change password</h2><p class="mt-1 text-sm text-neutral-600">Use at least 12 characters and avoid passwords used on other services.</p></div>
                    <div class="field"><label for="password-current">Current password</label><input id="password-current" type="password" name="current_password" required autocomplete="current-password"></div>
                    <div class="field"><label for="password">New password</label><input id="password" type="password" name="password" required autocomplete="new-password"></div>
                    <div class="field"><label for="password-confirmation">Confirm new password</label><input id="password-confirmation" type="password" name="password_confirmation" required autocomplete="new-password"></div>
                    <button class="button-primary justify-self-start">Update password</button>
                </form>
            </div>

            <section class="panel mt-5 overflow-hidden">
                <div class="panel-header"><div><p class="section-kicker">Participation and personal data</p><h2 class="text-lg font-semibold">Request staff assistance</h2></div></div>
                <div class="grid gap-5 p-5 sm:p-6 lg:grid-cols-[1fr_.9fr]">
                    <form method="POST" action="{{ route('account.requests.store') }}" class="grid gap-4">@csrf
                        <div class="field"><label for="type">Request type</label><select id="type" name="type" required><option value="withdrawal">Withdraw or pause participation</option><option value="data-copy">Receive a copy of my personal data</option><option value="deletion">Request deletion or anonymisation</option></select>@error('type')<p class="error-text">{{ $message }}</p>@enderror</div>
                        <div class="field"><label for="details">What would you like staff to do?</label><textarea id="details" name="details" rows="4" required maxlength="2000" placeholder="Give enough context for staff to respond safely.">{{ old('details') }}</textarea>@error('details')<p class="error-text">{{ $message }}</p>@enderror</div>
                        <p class="text-xs leading-5 text-neutral-500">Requests are reviewed before records change. Shared mentoring, audit or safeguarding records may need to be retained where programme obligations require it.</p>
                        <button class="button-secondary justify-self-start">Send request</button>
                    </form>
                    <div>
                        <h3 class="text-sm font-semibold">Recent requests</h3>
                        <div class="mt-3 grid gap-2">@forelse($accountRequests as $accountRequest)<div class="rounded-lg border border-neutral-200 p-3"><div class="flex items-center justify-between gap-3"><p class="text-sm font-medium">{{ str($accountRequest->type)->replace('-', ' ')->title() }}</p><span class="status-badge">{{ str($accountRequest->status)->replace('-', ' ')->title() }}</span></div><p class="mt-1 text-xs text-neutral-500">Submitted {{ $accountRequest->created_at->diffForHumans() }}</p>@if($accountRequest->resolution)<p class="mt-2 text-sm text-neutral-600">{{ $accountRequest->resolution }}</p>@endif</div>@empty<p class="text-sm text-neutral-500">You have not submitted an account or privacy request.</p>@endforelse</div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-layouts.app>
