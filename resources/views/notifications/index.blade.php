<x-layouts.app title="Updates">
    <div class="page-shell py-7 sm:py-10">
        <div class="content-shell">
            @if(session('status'))<div class="notice-success mb-6" role="status">{{ session('status') }}</div>@endif
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div><p class="section-kicker">Mentoring activity</p><h1 class="page-heading mt-1.5">Updates</h1><p class="lede mt-3">Decisions, reminders and shared activity that may need your attention.</p></div>
                @if(auth()->user()->unreadNotifications()->exists())<form method="POST" action="{{ route('notifications.read-all') }}">@csrf @method('PUT')<button class="button-secondary">Mark all as read</button></form>@endif
            </div>

            <div class="panel mt-7 divide-y divide-neutral-200 overflow-hidden">
                @forelse($notifications as $notification)
                    @php($category = $notification->data['category'] ?? 'activity')
                    <form method="POST" action="{{ route('notifications.update', $notification->id) }}">
                        @csrf @method('PUT')
                        <button class="w-full p-5 text-left transition hover:bg-neutral-50 focus-visible:bg-neutral-50 sm:p-6">
                            <div class="flex items-start gap-4">
                                <span class="mt-1 grid size-9 shrink-0 place-items-center rounded-full {{ $notification->read_at ? 'bg-neutral-100 text-neutral-500' : 'bg-green-50 text-isc2-green' }} text-xs font-extrabold" aria-hidden="true">{{ $category === 'reminder' ? '!' : strtoupper(substr($category, 0, 1)) }}</span>
                                <div class="min-w-0 flex-1"><div class="flex flex-wrap items-start justify-between gap-2"><p class="font-extrabold {{ $notification->read_at ? 'text-neutral-700' : 'text-neutral-950' }}">{{ $notification->data['actor_name'] }} {{ $notification->data['action'] }}</p><span class="text-xs font-semibold text-neutral-500">{{ $notification->created_at->diffForHumans() }}</span></div><p class="mt-1 text-sm leading-6 text-neutral-600">{{ $notification->data['message'] }}</p><p class="mt-2 text-xs font-bold text-isc2-green">Open mentoring workspace &rarr;</p></div>
                                @unless($notification->read_at)<span class="mt-1 size-2.5 shrink-0 rounded-full bg-isc2-green" aria-label="Unread"></span>@endunless
                            </div>
                        </button>
                    </form>
                @empty
                    <div class="p-8 text-center"><h2 class="font-extrabold">You are up to date</h2><p class="mt-2 text-sm text-neutral-500">New mentoring decisions, reminders and shared activity will appear here.</p></div>
                @endforelse
            </div>
            <div class="mt-6">{{ $notifications->links() }}</div>
        </div>
    </div>
</x-layouts.app>
