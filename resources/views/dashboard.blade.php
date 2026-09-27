<x-app-layout>
    <x-slot name="title">{{ __('healo.dashboard') }}</x-slot>
    <x-slot name="header">
        <p class="text-xs font-medium text-slate-500">{{ now()->translatedFormat('l, d F Y') }}</p>
        <h1 class="truncate text-lg font-bold text-slate-900">{{ __('healo.hello_name', ['name' => str(Auth::user()->name)->before(' ')]) }}</h1>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6">
        <section class="relative overflow-hidden rounded-[2rem] bg-slate-950 p-6 text-white shadow-soft sm:p-8">
            <div class="absolute -right-20 -top-24 size-64 rounded-full bg-emerald-400/20 blur-3xl"></div>
            <div class="absolute bottom-0 right-1/3 size-40 rounded-full bg-sky-400/10 blur-3xl"></div>
            <div class="relative grid gap-8 lg:grid-cols-[1fr_auto] lg:items-center">
                <div class="max-w-2xl">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-300">{{ __('healo.today_health') }}</p>
                    <h2 class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl">{{ __('healo.dashboard_empty_title') }}</h2>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-slate-300">{{ __('healo.dashboard_empty_body') }}</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row lg:flex-col">
                    <a href="{{ route('features.show', 'food-scanner') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-400 px-5 py-3.5 text-sm font-bold text-emerald-950 transition hover:bg-emerald-300"><i data-lucide="scan-line" class="size-5"></i>{{ __('healo.scan_food') }}</a>
                    <a href="{{ route('features.show', 'symptom-checker') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-5 py-3.5 text-sm font-semibold text-white hover:bg-white/15"><i data-lucide="stethoscope" class="size-5"></i>{{ __('healo.check_symptoms') }}</a>
                </div>
            </div>
        </section>

        <section aria-labelledby="summary-title">
            <div class="mb-4 flex items-end justify-between">
                <div><p class="healo-kicker">{{ __('healo.overview') }}</p><h2 id="summary-title" class="mt-1 text-xl font-bold text-slate-900">{{ __('healo.your_health_today') }}</h2></div>
                <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">{{ __('healo.getting_started') }}</span>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach([
                    ['icon' => 'gauge', 'label' => __('healo.bmi'), 'value' => '—', 'note' => __('healo.no_bmi'), 'feature' => 'bmi'],
                    ['icon' => 'flame', 'label' => __('healo.calories'), 'value' => '0', 'note' => __('healo.kcal_today'), 'feature' => 'nutrition'],
                    ['icon' => 'utensils', 'label' => __('healo.meals'), 'value' => '0', 'note' => __('healo.no_meals'), 'feature' => 'food-scanner'],
                    ['icon' => 'notebook-pen', 'label' => __('healo.journal'), 'value' => '0', 'note' => __('healo.no_journal'), 'feature' => 'health-journal'],
                ] as $metric)
                    <a href="{{ route('features.show', $metric['feature']) }}" class="healo-card group p-5 transition hover:-translate-y-0.5 hover:shadow-soft">
                        <div class="flex items-start justify-between"><span class="grid size-11 place-items-center rounded-2xl bg-emerald-50 text-emerald-700"><i data-lucide="{{ $metric['icon'] }}" class="size-5"></i></span><i data-lucide="arrow-up-right" class="size-4 text-slate-300 group-hover:text-emerald-600"></i></div>
                        <p class="mt-5 text-sm font-medium text-slate-500">{{ $metric['label'] }}</p>
                        <p class="mt-1 text-3xl font-black tracking-tight text-slate-900">{{ $metric['value'] }}</p>
                        <p class="mt-2 text-xs text-slate-500">{{ $metric['note'] }}</p>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
            <div class="healo-card p-6">
                <div class="flex items-center justify-between"><div><p class="healo-kicker">{{ __('healo.quick_actions') }}</p><h2 class="mt-1 text-xl font-bold text-slate-900">{{ __('healo.start_small') }}</h2></div><span class="grid size-11 place-items-center rounded-2xl bg-emerald-50 text-emerald-700"><i data-lucide="sparkles" class="size-5"></i></span></div>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <a href="{{ route('features.show', 'health-journal') }}" class="rounded-2xl border border-slate-200 p-4 hover:border-emerald-200 hover:bg-emerald-50/50"><i data-lucide="plus-circle" class="size-5 text-emerald-600"></i><p class="mt-3 font-semibold text-slate-900">{{ __('healo.add_journal') }}</p><p class="mt-1 text-sm text-slate-500">{{ __('healo.add_journal_hint') }}</p></a>
                    <a href="{{ route('features.show', 'bmi') }}" class="rounded-2xl border border-slate-200 p-4 hover:border-sky-200 hover:bg-sky-50/50"><i data-lucide="calculator" class="size-5 text-sky-600"></i><p class="mt-3 font-semibold text-slate-900">{{ __('healo.calculate_bmi') }}</p><p class="mt-1 text-sm text-slate-500">{{ __('healo.calculate_bmi_hint') }}</p></a>
                </div>
            </div>

            <div class="healo-card p-6">
                <div class="flex items-center gap-3"><span class="grid size-11 place-items-center rounded-2xl bg-sky-50 text-sky-700"><i data-lucide="shield-check" class="size-5"></i></span><div><p class="font-bold text-slate-900">{{ __('healo.privacy_first') }}</p><p class="text-xs text-slate-500">{{ __('healo.your_data_yours') }}</p></div></div>
                <p class="mt-5 text-sm leading-6 text-slate-600">{{ __('healo.privacy_dashboard_copy') }}</p>
                <a href="{{ route('profile.edit') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-emerald-700 hover:text-emerald-800">{{ __('healo.manage_account') }}<i data-lucide="arrow-right" class="size-4"></i></a>
            </div>
        </section>
    </div>
</x-app-layout>

