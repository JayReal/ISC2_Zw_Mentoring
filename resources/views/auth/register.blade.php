<x-layouts.app title="Register">
    <div class="mx-auto max-w-2xl px-4 py-12 sm:px-6"><h1 class="text-3xl font-extrabold">Register for the pilot</h1><p class="mt-3 text-neutral-600">For participants aged 18 or older. You may take part as a mentor, mentee or both.</p>
        <form method="POST" action="{{ route('register') }}" class="mt-8 grid gap-5 rounded-2xl border border-neutral-200 p-6 shadow-sm">@csrf
            <div class="grid gap-2"><label for="name">Full name</label><input id="name" name="name" value="{{ old('name') }}" required autocomplete="name">@error('name')<p class="text-sm text-red-700">{{ $message }}</p>@enderror</div>
            <div class="grid gap-2"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">@error('email')<p class="text-sm text-red-700">{{ $message }}</p>@enderror</div>
            <div class="grid gap-2"><label for="date_of_birth">Date of birth</label><input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required><p class="text-sm text-neutral-500">Used to confirm eligibility and kept private.</p>@error('date_of_birth')<p class="text-sm text-red-700">{{ $message }}</p>@enderror</div>
            <div class="grid gap-2"><label for="participation_type">I want to participate as</label><select id="participation_type" name="participation_type" required><option value="mentee">Mentee</option><option value="mentor">Mentor</option><option value="both">Both mentor and mentee</option></select></div>
            <div class="grid gap-2"><label for="password">Password</label><input id="password" type="password" name="password" required autocomplete="new-password"><p class="text-sm text-neutral-500">Use at least 12 characters.</p>@error('password')<p class="text-sm text-red-700">{{ $message }}</p>@enderror</div>
            <div class="grid gap-2"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"></div>
            <label class="flex items-start gap-3 font-normal"><input class="mt-1" type="checkbox" name="matching_consent" value="1" required><span>I consent to the Chapter using my profile to suggest and administer mentoring matches.</span></label>
            <label class="flex items-start gap-3 font-normal"><input class="mt-1" type="checkbox" name="privacy_acknowledgement" value="1" required><span>I have read the pilot privacy information and understand that safeguarding or lawful exceptions may require escalation.</span></label>
            @if($errors->any())<p class="text-sm font-bold text-red-700">Please correct the highlighted information.</p>@endif
            <button class="rounded-lg bg-isc2-green px-5 py-3 font-bold text-white hover:bg-isc2-dark-green">Create account</button>
        </form>
    </div>
</x-layouts.app>
