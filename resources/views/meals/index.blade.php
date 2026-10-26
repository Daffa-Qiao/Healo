<x-app-layout>
    <x-slot name="title">{{ __('nutrition.meal_history') }}</x-slot>
    <x-slot name="header"><p class="text-xs font-medium text-slate-500">{{ __('nutrition.title') }}</p><h1 class="text-lg font-bold text-slate-900">{{ __('nutrition.meal_history') }}</h1></x-slot>
    <div class="mx-auto max-w-6xl space-y-6">
        @if(session('status'))<div role="status" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ __('nutrition.'.session('status')) }}</div>@endif
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><p class="healo-kicker">{{ __('nutrition.meal_history') }}</p><h2 class="mt-1 text-2xl font-black text-slate-950">{{ __('nutrition.meal_history_copy') }}</h2></div><div class="flex gap-2"><a href="{{ route('food-analysis.create') }}" class="healo-button-secondary"><i data-lucide="scan-line" class="size-4"></i>{{ __('nutrition.scan_food') }}</a><a href="{{ route('meals.create') }}" class="healo-button-primary"><i data-lucide="plus" class="size-4"></i>{{ __('nutrition.add_meal') }}</a></div></div>
        <form method="GET" class="healo-card grid gap-4 p-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end"><div><x-input-label for="date" :value="__('nutrition.filter_date')"/><x-text-input id="date" name="date" type="date" class="mt-1 w-full" :value="request('date')"/></div><div><x-input-label for="meal_type" :value="__('nutrition.filter_type')"/><select id="meal_type" name="meal_type" class="mt-1 w-full rounded-xl border-slate-300"><option value="">{{ __('nutrition.all_types') }}</option>@foreach($mealTypes as $type)<option value="{{ $type->value }}" @selected(request('meal_type') === $type->value)>{{ __('nutrition.meal_'.$type->value) }}</option>@endforeach</select></div><button class="healo-button-primary">{{ __('nutrition.filter') }}</button></form>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($meals as $meal)
                <a href="{{ route('meals.show', $meal) }}" class="healo-card group p-5 transition hover:-translate-y-0.5 hover:shadow-soft"><div class="flex items-start justify-between"><span class="grid size-11 place-items-center rounded-2xl bg-emerald-50 text-emerald-700"><i data-lucide="utensils" class="size-5"></i></span><span class="text-sm font-black text-slate-900">{{ number_format((float) $meal->total_calories) }} kcal</span></div><p class="mt-4 font-bold text-slate-900">{{ __('nutrition.meal_'.$meal->meal_type->value) }}</p><p class="mt-1 text-sm text-slate-500">{{ $meal->meal_date->translatedFormat('l, d M Y') }}</p><p class="mt-3 line-clamp-2 text-sm text-slate-600">{{ $meal->items->pluck('food_name')->join(', ') ?: __('nutrition.no_food_items') }}</p><div class="mt-4 flex items-center justify-between text-xs text-slate-500"><span>{{ $meal->items->count() }} {{ __('nutrition.items') }}</span><span class="font-semibold text-emerald-700">{{ __('nutrition.view_meal') }}</span></div></a>
            @empty
                <div class="healo-card col-span-full p-10 text-center"><p class="text-sm text-slate-500">{{ __('nutrition.no_meals') }}</p><a href="{{ route('meals.create') }}" class="healo-button-primary mt-4">{{ __('nutrition.add_meal') }}</a></div>
            @endforelse
        </div>
        {{ $meals->links() }}
    </div>
</x-app-layout>
