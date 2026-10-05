<x-layouts.app :title="$title.' | Programme administration'">
    <div class="bg-isc2-dark-green text-white">
        <div class="page-shell pt-7 sm:pt-9">
            <div><p class="text-xs leading-5 font-medium tracking-[0.06em] text-[#9ac23c]">Restricted programme workspace</p><h1 class="mt-1 text-2xl leading-tight font-semibold tracking-[-0.01em] sm:text-3xl">{{ $title }}</h1></div>
            <nav class="-mx-2 mt-5 flex gap-1 overflow-x-auto px-2 pb-0 text-sm font-bold sm:mt-7" aria-label="Administration">
                @php($adminLinks = ['admin.dashboard'=>'Overview'])
                @if(auth()->user()->hasAnyRole(['admin','programme-lead','matching-team'])) @php($adminLinks['admin.pulse'] = 'Programme pulse') @endif
                @php($adminLinks['admin.attention'] = 'Attention queue')
                @php($adminLinks['admin.participants.index'] = 'Participants')
                @if(auth()->user()->hasAnyRole(['admin','programme-lead','matching-team'])) @php($adminLinks['admin.matches.index'] = 'Matching') @endif
                @if(auth()->user()->hasAnyRole(['admin','programme-lead'])) @php($adminLinks['admin.cycles.index'] = 'Cycles') @endif
                @if(auth()->user()->hasAnyRole(['admin','programme-lead','cluster-lead','technical-guild-lead'])) @php($adminLinks['admin.clusters.index'] = 'Clusters') @endif
                @foreach($adminLinks as $route=>$label)
                    <a href="{{ route($route) }}" @if(request()->routeIs($route) || request()->routeIs(str_replace('.index','.*',$route))) aria-current="page" @endif class="whitespace-nowrap rounded-t-lg border-b-2 px-3.5 py-3 transition {{ request()->routeIs($route) || request()->routeIs(str_replace('.index','.*',$route)) ? 'border-[#9ac23c] bg-white/10 text-white' : 'border-transparent text-neutral-300 hover:bg-white/5 hover:text-white' }}">{{ $label }}</a>
                @endforeach
            </nav>
        </div>
    </div>
    <div class="page-shell py-6 sm:py-8">
        @if(session('status'))<div class="notice-success mb-6" role="status">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900" role="alert"><p class="font-bold">Please correct the following:</p><ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        {{ $slot }}
    </div>
</x-layouts.app>
