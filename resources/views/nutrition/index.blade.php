<x-app-layout>
    <x-slot name="title">{{ __('nutrition.dashboard') }}</x-slot>
    <x-slot name="header"><p class="text-xs font-medium text-slate-500">{{ __('nutrition.title') }}</p><h1 class="text-lg font-bold text-slate-900">{{ __('nutrition.dashboard') }}</h1></x-slot>

    @php
        $target = (int) $today->calorie_target;
        $consumed = (float) $today->calories_consumed;
        $remaining = (float) $today->calories_remaining;
        $progress = $target > 0 ? min(100, round(($consumed / $target) * 100)) : 0;
    @endphp
    <div class="mx-auto max-w-7xl space-y-6">
        @if(session('status'))<div role="status" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ __('nutrition.'.session('status')) }}</div>@endif

        <section class="relative overflow-hidden rounded-[2rem] bg-slate-950 p-6 text-white shadow-soft sm:p-8">
            <div class="absolute -right-20 -top-24 size-64 rounded-full bg-emerald-400/20 blur-3xl"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div><p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-300">{{ __('nutrition.today') }}</p><h2 class="mt-3 text-3xl font-black tracking-tight">{{ number_format($consumed) }} / {{ number_format($target) }} {{ __('nutrition.kcal') }}</h2><p class="mt-3 max-w-xl text-sm leading-6 text-slate-300">{{ __('nutrition.dashboard_copy') }}</p></div>
                <div class="flex flex-wrap gap-3"><a href="{{ route('meals.create') }}" class="healo-button-primary bg-emerald-400 text-emerald-950 hover:bg-emerald-300"><i data-lucide="plus" class="size-4"></i>{{ __('nutrition.add_meal') }}</a><a href="{{ route('food-analysis.create') }}" class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-semibold hover:bg-white/15"><i data-lucide="scan-line" class="size-4"></i>{{ __('nutrition.scan_food') }}</a></div>
            </div>
            <div class="relative mt-7 h-2 overflow-hidden rounded-full bg-white/10"><div class="h-full rounded-full bg-emerald-400 transition-all" style="width: {{ $progress }}%"></div></div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['icon' => 'flame', 'label' => __('nutrition.today_calories'), 'value' => number_format($consumed), 'note' => __('nutrition.kcal')],
                ['icon' => 'target', 'label' => __('nutrition.daily_target'), 'value' => $target ? number_format($target) : '—', 'note' => $target ? __('nutrition.kcal') : __('nutrition.no_target')],
                ['icon' => $remaining < 0 ? 'alert-triangle' : 'activity', 'label' => $remaining < 0 ? __('nutrition.target_exceeded') : __('nutrition.remaining'), 'value' => number_format(abs($remaining)), 'note' => __('nutrition.kcal')],
                ['icon' => 'utensils', 'label' => __('nutrition.meals_today'), 'value' => $today->meals_logged, 'note' => __('nutrition.meals_logged')],
            ] as $metric)
                <div class="healo-card p-5"><span class="grid size-11 place-items-center rounded-2xl bg-emerald-50 text-emerald-700"><i data-lucide="{{ $metric['icon'] }}" class="size-5"></i></span><p class="mt-4 text-sm font-medium text-slate-500">{{ $metric['label'] }}</p><p class="mt-1 text-3xl font-black text-slate-950">{{ $metric['value'] }}</p><p class="mt-1 text-xs text-slate-500">{{ $metric['note'] }}</p></div>
            @endforeach
        </section>

        @unless($profile)
            <section class="flex flex-col gap-4 rounded-3xl border border-amber-200 bg-amber-50 p-5 sm:flex-row sm:items-center sm:justify-between"><div><p class="font-bold text-amber-950">{{ __('nutrition.no_target') }}</p><p class="mt-1 text-sm text-amber-800">{{ __('nutrition.estimation_disclaimer') }}</p></div><a href="{{ route('nutrition.profile.edit') }}" class="healo-button-primary shrink-0">{{ __('nutrition.set_target') }}</a></section>
        @endunless

        <section class="grid gap-6 xl:grid-cols-[1.25fr_0.75fr]">
            <div class="healo-card p-6">
                <div class="flex items-center justify-between"><div><p class="healo-kicker">{{ __('nutrition.progress') }}</p><h2 class="mt-1 text-xl font-bold text-slate-900">{{ __('nutrition.meals_today') }}</h2></div><a href="{{ route('meals.index') }}" class="text-sm font-semibold text-emerald-700">{{ __('nutrition.all_meals') }}</a></div>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    @foreach($mealTypes as $type)
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4"><div class="flex items-center justify-between"><p class="font-semibold text-slate-800">{{ __('nutrition.meal_'.$type->value) }}</p><span class="text-sm font-bold text-emerald-700">{{ number_format($byType[$type->value]) }} kcal</span></div></div>
                    @endforeach
                </div>
                <div class="mt-5 space-y-3">
                    @forelse($todayMeals as $meal)
                        <a href="{{ route('meals.show', $meal) }}" class="flex items-center justify-between rounded-2xl border border-slate-100 p-4 hover:border-emerald-200"><div><p class="font-semibold text-slate-900">{{ __('nutrition.meal_'.$meal->meal_type->value) }}</p><p class="mt-1 text-xs text-slate-500">{{ $meal->items->pluck('food_name')->take(3)->join(', ') ?: __('nutrition.no_food_items') }}</p></div><div class="flex items-center gap-3"><span class="font-bold text-slate-900">{{ number_format((float) $meal->total_calories) }} kcal</span><i data-lucide="chevron-right" class="size-4 text-slate-400"></i></div></a>
                    @empty
                        <p class="rounded-2xl bg-slate-50 p-5 text-center text-sm text-slate-500">{{ __('nutrition.no_meals_today') }}</p>
                    @endforelse
                </div>
            </div>

            <div class="healo-card p-6" @if($target > 0) x-data='{ consumed: {{ $consumed }}, remaining: {{ max(0, $remaining) }} }' x-init="const Chart = await window.Healo.chart(); new Chart($refs.canvas, { type: 'doughnut', data: { labels: ['{{ __('nutrition.today_calories') }}', '{{ __('nutrition.remaining') }}'], datasets: [{ data: [consumed, remaining], backgroundColor: ['#059669', '#e2e8f0'], borderWidth: 0 }] }, options: { responsive: true, cutout: '72%', plugins: { legend: { position: 'bottom' } } } })" @endif>
                <p class="healo-kicker">{{ __('nutrition.progress') }}</p><h2 class="mt-1 text-xl font-bold text-slate-900">{{ __('nutrition.today_calories') }}</h2>
                @if($target > 0)<div class="mx-auto mt-5 max-w-64"><canvas x-ref="canvas" aria-label="{{ __('nutrition.progress') }}"></canvas></div>@else<div class="mt-8 text-center text-sm text-slate-500"><a href="{{ route('nutrition.profile.edit') }}" class="font-semibold text-emerald-700">{{ __('nutrition.set_target') }}</a></div>@endif
            </div>
        </section>

        <section class="healo-card p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"><div><p class="healo-kicker">{{ __('nutrition.weekly_summary') }}</p><h2 class="mt-1 text-xl font-bold text-slate-900">{{ __('nutrition.weekly_trend') }}</h2></div><div class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-4"><div><p class="text-slate-500">{{ __('nutrition.average_daily') }}</p><p class="font-bold">{{ number_format($weeklyStats['average']) }} kcal</p></div><div><p class="text-slate-500">{{ __('nutrition.highest_day') }}</p><p class="font-bold">{{ $weeklyStats['highest'] ? $weeklyStats['highest']['date']->translatedFormat('D') : '—' }}</p></div><div><p class="text-slate-500">{{ __('nutrition.lowest_day') }}</p><p class="font-bold">{{ $weeklyStats['lowest'] ? $weeklyStats['lowest']['date']->translatedFormat('D') : '—' }}</p></div><div><p class="text-slate-500">{{ __('nutrition.meals_logged') }}</p><p class="font-bold">{{ $weeklyStats['meals'] }}</p></div></div></div>
            @if($weeklyStats['logged_days'] >= 2)
                <div class="mt-6 h-72" x-data='{ labels: @json($weekly->map(fn($day) => $day['date']->translatedFormat('D'))), consumed: @json($weekly->pluck('consumed')), target: @json($weekly->pluck('target')) }' x-init="const Chart = await window.Healo.chart(); new Chart($refs.weekly, { type: 'line', data: { labels, datasets: [{ label: '{{ __('nutrition.today_calories') }}', data: consumed, borderColor: '#059669', backgroundColor: 'rgba(5,150,105,.12)', fill: true, tension: .35 }, { label: '{{ __('nutrition.daily_target') }}', data: target, borderColor: '#64748b', borderDash: [6,6], pointRadius: 0 }] }, options: { responsive: true, maintainAspectRatio: false } })"><canvas x-ref="weekly" aria-label="{{ __('nutrition.weekly_trend') }}"></canvas></div>
            @else
                <p class="mt-6 rounded-2xl bg-slate-50 p-6 text-center text-sm text-slate-500">{{ __('nutrition.not_enough_chart_data') }}</p>
            @endif
        </section>

        <section class="healo-card p-6"><div class="flex items-end justify-between"><div><p class="healo-kicker">{{ __('nutrition.recommendations') }}</p><h2 class="mt-1 text-xl font-bold text-slate-900">{{ __('nutrition.recommendations_copy') }}</h2></div><a href="{{ route('healthy-menus.index') }}" class="text-sm font-semibold text-emerald-700">{{ __('nutrition.browse_menus') }}</a></div><div class="mt-5 grid gap-4 md:grid-cols-3">@foreach($recommendations as $menu)<article class="rounded-2xl border border-slate-100 p-4"><p class="font-bold text-slate-900">{{ $menu->name }}</p><p class="mt-2 text-sm text-slate-500">{{ number_format((float) $menu->calories) }} kcal · {{ __('nutrition.meal_'.$menu->meal_type->value) }}</p></article>@endforeach</div></section>

        <div class="flex gap-3 rounded-2xl border border-sky-200 bg-sky-50 p-4 text-sm leading-6 text-sky-900"><i data-lucide="info" class="mt-0.5 size-5 shrink-0"></i><p>{{ __('nutrition.estimation_disclaimer') }}</p></div>
    </div>
</x-app-layout>
