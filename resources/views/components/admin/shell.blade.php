<x-layouts.app :title="$title.' | Programme administration'">
    <div class="bg-isc2-dark-green text-white">
        <div class="page-shell pt-7 sm:pt-9">
            <div><p class="text-xs leading-5 font-bold tracking-[0.14em] text-[#9ac23c] uppercase">Restricted programme workspace</p><h1 class="mt-1.5 text-3xl leading-tight font-extrabold tracking-[-0.02em] sm:text-4xl">{{ $title }}</h1></div>
            <nav class="-mx-2 mt-5 flex gap-1 overflow-x-auto px-2 pb-0 text-sm font-bold sm:mt-7" aria-label="Administration">
                @php($adminLinks = ['admin.dashboard'=>'Overview','admin.attention'=>'Attention queue','admin.participants.index'=>'Participants'])
                @if(auth()->user()->hasAnyRole(['admin','programme-lead','matching-team'])) @php($adminLinks['admin.matches.index'] = 'Matching') @endif
                @if(auth()->user()->hasAnyRole(['admin','programme-lead'])) @php($adminLinks['admin.cycles.index'] = 'Cycles') @endif
                @if(auth()->user()->hasAnyRole(['admin','programme-lead','cluster-lead','technical-guild-lead'])) @php($adminLinks['admin.clusters.index'] = 'Clusters') @endif
                @foreach($adminLinks as $route=>$label)
                    <a href="{{ route($route) }}" class="whitespace-nowrap rounded-t-lg border-b-2 px-3.5 py-3 transition {{ request()->routeIs($route) || request()->routeIs(str_replace('.index','.*',$route)) ? 'border-[#9ac23c] bg-white/10 text-white' : 'border-transparent text-neutral-300 hover:bg-white/5 hover:text-white' }}">{{ $label }}</a>
                @endforeach
            </nav>
        </div>
    </div>
    <div class="page-shell py-7 sm:py-10">
        @if(session('status'))<div class="notice-success mb-6">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900"><p class="font-bold">Please correct the following:</p><ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        {{ $slot }}
    </div>
</x-layouts.app>
