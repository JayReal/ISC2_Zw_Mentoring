<x-layouts.app title="Register">
    <div class="page-shell py-7 sm:py-12">
        <div class="mx-auto max-w-3xl">
            <div class="mb-6"><p class="section-kicker">Controlled pilot</p><h1 class="mt-1 text-3xl font-extrabold tracking-tight">Create your participant account</h1><p class="mt-2 text-sm leading-6 text-neutral-600">For mentors and mentees aged 18 or older. You can hold both roles with one account.</p></div>

            <form method="POST" action="{{ route('register') }}" class="panel grid gap-4 p-4 sm:gap-5 sm:p-7">@csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="field"><label for="name">Full name</label><input id="name" name="name" value="{{ old('name') }}" required autocomplete="name">@error('name')<p class="error-text">{{ $message }}</p>@enderror</div>
                    <div class="field"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">@error('email')<p class="error-text">{{ $message }}</p>@enderror</div>
                    <div class="field"><label for="date_of_birth">Date of birth</label><input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required><p class="field-help">Used only to confirm 18+ eligibility.</p>@error('date_of_birth')<p class="error-text">{{ $message }}</p>@enderror</div>
                    <div class="field"><label for="participation_type">Participation role</label><select id="participation_type" name="participation_type" required><option value="mentee" @selected(old('participation_type') === 'mentee')>Mentee</option><option value="mentor" @selected(old('participation_type') === 'mentor')>Mentor</option><option value="both" @selected(old('participation_type') === 'both')>Both mentor and mentee</option></select></div>
                    <div class="field"><label for="password">Password</label><input id="password" type="password" name="password" required autocomplete="new-password"><p class="field-help">At least 12 characters.</p>@error('password')<p class="error-text">{{ $message }}</p>@enderror</div>
                    <div class="field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"></div>
                </div>

                <div class="grid gap-3 border-t border-neutral-200 pt-5">
                    <label class="flex items-start gap-3 font-normal"><input class="mt-1" type="checkbox" name="matching_consent" value="1" required><span class="text-sm leading-6">I consent to the Chapter using my profile to suggest and administer mentoring matches.</span></label>
                    <label class="flex items-start gap-3 font-normal"><input class="mt-1" type="checkbox" name="privacy_acknowledgement" value="1" required><span class="text-sm leading-6">I understand that safeguarding or lawful exceptions may require confidential information to be escalated.</span></label>
                </div>

                @if($errors->any())<p class="error-text rounded-md bg-red-50 p-3">Please correct the highlighted information.</p>@endif
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between"><p class="text-center text-sm text-neutral-500 sm:text-left">Already registered? <a class="font-bold text-isc2-green underline underline-offset-2" href="{{ route('login') }}">Log in</a></p><button class="button-primary w-full sm:w-auto">Create account</button></div>
            </form>
        </div>
    </div>
</x-layouts.app>
