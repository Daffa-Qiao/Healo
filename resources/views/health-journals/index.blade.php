<x-app-layout>
    <x-slot name="title">{{ __('healo.health_journal') }}</x-slot>
    <x-slot name="header"><p class="text-xs font-medium text-slate-500">{{ __('healo.health') }}</p><h1 class="text-lg font-bold text-slate-900">{{ __('healo.health_journal') }}</h1></x-slot>

    <div class="mx-auto max-w-7xl space-y-6">
        @if(session('status'))<div role="status" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ __('healo.'.session('status')) }}</div>@endif
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><p class="healo-kicker">{{ __('healo.personal_timeline') }}</p><h2 class="mt-2 text-3xl font-black tracking-tight text-slate-950">{{ __('healo.your_health_notes') }}</h2><p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">{{ __('healo.journal_index_copy') }}</p></div><a href="{{ route('health-journals.create') }}" class="healo-button-primary shrink-0"><i data-lucide="plus" class="size-4"></i>{{ __('healo.add_journal') }}</a></div>

        <form method="GET" class="healo-card grid gap-3 p-4 sm:grid-cols-[1fr_12rem_11rem_auto]">
            <div><label for="q" class="sr-only">{{ __('healo.search') }}</label><input id="q" name="q" type="search" value="{{ request('q') }}" placeholder="{{ __('healo.search_journal_title') }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500"></div>
            <select name="mood" aria-label="{{ __('healo.filter_mood') }}" class="rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500"><option value="">{{ __('healo.all_moods') }}</option>@foreach($moods as $mood)<option value="{{ $mood->value }}" @selected(request('mood') === $mood->value)>{{ __('healo.mood_'.$mood->value) }}</option>@endforeach</select>
            <input name="month" type="month" value="{{ request('month') }}" aria-label="{{ __('healo.filter_month') }}" class="rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            <button class="healo-button-secondary px-4 py-2"><i data-lucide="search" class="size-4"></i>{{ __('healo.filter') }}</button>
        </form>

        @if($journals->isEmpty())
            <section class="healo-card grid min-h-80 place-items-center p-8 text-center"><div><span class="mx-auto grid size-16 place-items-center rounded-3xl bg-emerald-50 text-emerald-700"><i data-lucide="notebook-pen" class="size-8"></i></span><h2 class="mt-5 text-xl font-bold text-slate-900">{{ __('healo.no_journals') }}</h2><p class="mt-2 text-sm text-slate-500">{{ __('healo.journal_empty_copy') }}</p><a href="{{ route('health-journals.create') }}" class="healo-button-primary mt-6"><i data-lucide="plus" class="size-4"></i>{{ __('healo.add_journal') }}</a></div></section>
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($journals as $journal)
                    <article class="healo-card group flex flex-col p-5"><div class="flex items-start justify-between gap-4"><div><p class="text-xs font-semibold text-slate-500">{{ $journal->journal_date->translatedFormat('l, d M Y') }}</p><h2 class="mt-2 text-lg font-bold text-slate-900">{{ $journal->title }}</h2></div>@if($journal->mood)<span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800">{{ __('healo.mood_'.$journal->mood->value) }}</span>@endif</div>
                        <div class="mt-5 flex flex-wrap gap-2">@foreach($journal->entries->groupBy(fn($entry) => $entry->type->value) as $type => $entries)<span class="rounded-full border border-slate-200 px-2.5 py-1 text-xs text-slate-600">{{ __('healo.entry_type_'.$type) }} · {{ $entries->count() }}</span>@endforeach</div>
                        <a href="{{ route('health-journals.show', $journal) }}" class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-emerald-700">{{ __('healo.view_detail') }}<i data-lucide="arrow-right" class="size-4 transition group-hover:translate-x-1"></i></a>
                    </article>
                @endforeach
            </div>
            <div>{{ $journals->links() }}</div>
        @endif
    </div>
</x-app-layout>

