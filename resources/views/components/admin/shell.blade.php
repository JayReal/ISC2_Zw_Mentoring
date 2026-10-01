<x-layouts.app :title="$title.' | Programme administration'">
    <div class="bg-neutral-950 text-white">
        <div class="page-shell flex flex-col gap-4 py-5 lg:flex-row lg:items-center lg:justify-between">
            <div><p class="text-xs font-bold tracking-widest text-[#9ac23c] uppercase">Restricted programme workspace</p><h1 class="mt-1 text-2xl font-extrabold">{{ $title }}</h1></div>
            <nav class="flex gap-1 overflow-x-auto pb-1 text-sm font-bold" aria-label="Administration">
                @php($adminLinks = ['admin.dashboard'=>'Overview','admin.participants.index'=>'Participants'])
                @if(auth()->user()->hasAnyRole(['admin','programme-lead','matching-team'])) @php($adminLinks['admin.matches.index'] = 'Matching') @endif
                @if(auth()->user()->hasAnyRole(['admin','programme-lead'])) @php($adminLinks['admin.cycles.index'] = 'Cycles') @endif
                @if(auth()->user()->hasAnyRole(['admin','programme-lead','cluster-lead','technical-guild-lead'])) @php($adminLinks['admin.clusters.index'] = 'Clusters') @endif
                @foreach($adminLinks as $route=>$label)
                    <a href="{{ route($route) }}" class="whitespace-nowrap rounded-md px-3 py-2 {{ request()->routeIs($route) || request()->routeIs(str_replace('.index','.*',$route)) ? 'bg-white text-neutral-950' : 'text-neutral-300 hover:bg-white/10 hover:text-white' }}">{{ $label }}</a>
                @endforeach
            </nav>
        </div>
    </div>
    <div class="page-shell py-6 sm:py-9">
        @if(session('status'))<div class="mb-5 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm font-bold text-green-900">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900"><p class="font-bold">Please correct the following:</p><ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        {{ $slot }}
    </div>
</x-layouts.app>
