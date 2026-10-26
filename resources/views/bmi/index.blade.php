<x-app-layout>
    <x-slot name="title">{{ __('healo.bmi_calculator') }}</x-slot>
    <x-slot name="header"><p class="text-xs font-medium text-slate-500">{{ __('healo.health') }}</p><h1 class="text-lg font-bold text-slate-900">{{ __('healo.bmi_calculator') }}</h1></x-slot>

    <div class="mx-auto max-w-7xl space-y-6">
        @if(session('status'))<div role="status" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ __('healo.'.session('status')) }}</div>@endif
        <div class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
            <form method="POST" action="{{ route('bmi.store') }}" x-data="{ saving: false }" @submit="saving = true" class="healo-card p-6 sm:p-8">@csrf
                <p class="healo-kicker">{{ __('healo.general_indicator') }}</p><h2 class="mt-2 text-2xl font-black text-slate-950">{{ __('healo.calculate_your_bmi') }}</h2><p class="mt-2 text-sm leading-6 text-slate-500">{{ __('healo.bmi_form_helper') }}</p>
                <div class="mt-7 space-y-5">
                    <div><x-input-label for="weight_kg" :value="__('healo.weight_kg')" /><x-text-input id="weight_kg" name="weight_kg" type="number" min="20" max="400" step="0.1" required class="mt-1 block w-full" :value="old('weight_kg', $profile?->weight_kg)" /><x-input-error :messages="$errors->get('weight_kg')" class="mt-2" /></div>
                    <div><x-input-label for="height_cm" :value="__('healo.height_cm')" /><x-text-input id="height_cm" name="height_cm" type="number" min="80" max="250" step="0.1" required class="mt-1 block w-full" :value="old('height_cm', $profile?->height_cm)" /><x-input-error :messages="$errors->get('height_cm')" class="mt-2" /></div>
                    <div><x-input-label for="measured_at" :value="__('healo.measurement_time')" /><x-text-input id="measured_at" name="measured_at" type="datetime-local" max="{{ now()->format('Y-m-d\TH:i') }}" class="mt-1 block w-full" :value="old('measured_at', now()->format('Y-m-d\TH:i'))" /><x-input-error :messages="$errors->get('measured_at')" class="mt-2" /></div>
                    @if($profile)
                        <label class="flex items-center gap-3 text-sm text-slate-600"><input type="checkbox" name="update_health_profile" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" @checked(old('update_health_profile', true))>{{ __('healo.update_profile_measurements') }}</label>
                    @else
                        <a href="{{ route('health-profile.edit') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-700 hover:text-emerald-800"><i data-lucide="user-round-check" class="size-4"></i>{{ __('healo.complete_health_profile') }}</a>
                    @endif
                </div>
                <button class="healo-button-primary mt-7 w-full" :disabled="saving"><i data-lucide="calculator" class="size-4"></i><span x-text="saving ? '{{ __('healo.calculating') }}' : '{{ __('healo.calculate_bmi') }}'"></span></button>
            </form>

            <div class="space-y-6">
                @if($latestRecord)
                    <section class="relative overflow-hidden rounded-3xl bg-slate-950 p-7 text-white shadow-soft sm:p-8"><div class="absolute -right-16 -top-16 size-48 rounded-full bg-emerald-400/20 blur-3xl"></div><div class="relative flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-300">{{ __('healo.your_bmi') }}</p><p class="mt-2 text-6xl font-black tracking-tight">{{ number_format((float) $latestRecord->bmi, 1) }}</p><p class="mt-2 text-sm text-slate-400">{{ $latestRecord->measured_at->translatedFormat('d M Y, H:i') }}</p></div><div class="max-w-sm rounded-2xl border border-white/10 bg-white/10 p-4"><p class="font-bold text-emerald-200">{{ __('healo.bmi_category_'.$latestRecord->category->value) }}</p><p class="mt-2 text-sm leading-6 text-slate-300">{{ __('healo.bmi_interpretation_'.$latestRecord->category->value) }}</p></div></div></section>
                @else
                    <section class="healo-card grid min-h-64 place-items-center p-8 text-center"><div><span class="mx-auto grid size-14 place-items-center rounded-3xl bg-emerald-50 text-emerald-700"><i data-lucide="gauge" class="size-7"></i></span><h2 class="mt-5 font-bold text-slate-900">{{ __('healo.no_bmi') }}</h2><p class="mt-2 text-sm text-slate-500">{{ __('healo.bmi_empty_copy') }}</p></div></section>
                @endif

                @if($chartRecords->count() > 1)
                    <section class="healo-card p-6" x-data='{ labels: @json($chartRecords->map(fn($record) => $record->measured_at->format('d M'))), values: @json($chartRecords->pluck('bmi')) }' x-init="const Chart = await window.Healo.chart(); new Chart($refs.canvas, { type: 'line', data: { labels, datasets: [{ label: 'BMI', data: values, borderColor: '#059669', backgroundColor: 'rgba(5,150,105,.12)', fill: true, tension: .35 }] }, options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { suggestedMin: 15, suggestedMax: 35 } } } })"><div><p class="healo-kicker">{{ __('healo.trend') }}</p><h2 class="mt-1 font-bold text-slate-900">{{ __('healo.bmi_history_chart') }}</h2></div><div class="mt-5 h-64"><canvas x-ref="canvas" aria-label="{{ __('healo.bmi_history_chart') }}"></canvas></div></section>
                @endif
            </div>
        </div>

        <section class="healo-card overflow-hidden"><div class="flex items-center justify-between border-b border-slate-100 p-5 sm:p-6"><div><p class="healo-kicker">{{ __('healo.history') }}</p><h2 class="mt-1 text-xl font-bold text-slate-900">{{ __('healo.bmi_history') }}</h2></div></div>
            @if($records->isEmpty())<div class="p-10 text-center text-sm text-slate-500">{{ __('healo.bmi_empty_copy') }}</div>@else
                <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-6 py-3">{{ __('healo.date') }}</th><th class="px-6 py-3">BMI</th><th class="px-6 py-3">{{ __('healo.category') }}</th><th class="px-6 py-3">{{ __('healo.measurements') }}</th><th class="px-6 py-3"><span class="sr-only">{{ __('healo.actions') }}</span></th></tr></thead><tbody class="divide-y divide-slate-100">@foreach($records as $record)<tr><td class="whitespace-nowrap px-6 py-4">{{ $record->measured_at->translatedFormat('d M Y') }}</td><td class="px-6 py-4 font-bold text-slate-900">{{ number_format((float) $record->bmi, 1) }}</td><td class="px-6 py-4">{{ __('healo.bmi_category_'.$record->category->value) }}</td><td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ (float) $record->weight_kg }} kg · {{ (float) $record->height_cm }} cm</td><td class="px-6 py-4 text-right"><form method="POST" action="{{ route('bmi.destroy', $record) }}" onsubmit="return confirm('{{ __('healo.confirm_delete_bmi') }}')">@csrf @method('DELETE')<button class="text-rose-600 hover:text-rose-800" aria-label="{{ __('healo.delete') }}"><i data-lucide="trash-2" class="size-4"></i></button></form></td></tr>@endforeach</tbody></table></div><div class="border-t border-slate-100 p-4">{{ $records->links() }}</div>
            @endif
        </section>
        <div class="flex gap-3 rounded-2xl border border-sky-200 bg-sky-50 p-4 text-sm leading-6 text-sky-900"><i data-lucide="info" class="mt-0.5 size-5 shrink-0"></i><p>{{ __('healo.bmi_disclaimer') }}</p></div>
    </div>
</x-app-layout>
