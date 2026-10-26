<x-app-layout>
    <x-slot name="title">{{ __('health_ai.symptom_checker') }}</x-slot>
    <x-slot name="header"><div><p class="healo-kicker">{{ config('health_ai.provider') === 'groq' && filled(config('services.groq.api_key')) ? __('health_ai.groq_badge') : __('health_ai.demo_badge') }}</p><h1 class="text-lg font-bold text-slate-900">{{ __('health_ai.symptom_checker') }}</h1></div></x-slot>

    <div class="mx-auto grid max-w-6xl gap-6 lg:grid-cols-[1fr_22rem]">
        <section class="healo-card p-6 sm:p-8">
            <span class="grid size-14 place-items-center rounded-2xl bg-emerald-50 text-emerald-700"><i data-lucide="stethoscope" class="size-7"></i></span>
            <h2 class="mt-5 text-2xl font-black tracking-tight text-slate-900">{{ __('health_ai.check_your_symptoms') }}</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">{{ __('health_ai.symptom_intro') }}</p>

            <form method="POST" action="{{ route('symptom-checker.store') }}" class="mt-6 space-y-5">
                @csrf
                <div>
                    <textarea name="symptoms" rows="8" maxlength="{{ config('health_ai.message_max_length') }}" required autofocus placeholder="{{ __('health_ai.symptom_placeholder') }}" class="w-full rounded-2xl border-slate-200 text-sm leading-6 focus:border-emerald-500 focus:ring-emerald-500">{{ old('symptoms') }}</textarea>
                    @error('symptoms')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <label class="flex items-start gap-3 rounded-2xl border border-slate-200 p-4"><input type="checkbox" name="save" value="1" @checked(old('save')) class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"><span><span class="block text-sm font-semibold text-slate-800">{{ __('health_ai.save_history') }}</span><span class="mt-1 block text-xs text-slate-500">{{ __('health_ai.save_to_journal_copy') }}</span></span></label>
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3.5 text-sm font-bold text-white hover:bg-emerald-700"><i data-lucide="sparkles" class="size-5"></i>{{ __('health_ai.analyze') }}</button>
            </form>
        </section>

        <aside class="space-y-5">
            <div class="rounded-2xl border border-sky-200 bg-sky-50 p-5 text-sm text-sky-900"><p class="font-bold">{{ config('health_ai.provider') === 'groq' && filled(config('services.groq.api_key')) ? __('health_ai.groq_notice') : __('health_ai.demo_notice') }}</p><p class="mt-2 leading-6">{{ __('health_ai.not_doctor') }}</p></div>
            <div class="healo-card p-5">
                <div class="flex items-center justify-between"><h2 class="font-bold text-slate-900">{{ __('health_ai.history') }}</h2><a href="{{ route('symptom-checker.history') }}" class="text-xs font-semibold text-emerald-700">{{ __('health_ai.view_history') }}</a></div>
                <div class="mt-4 space-y-2">
                    @forelse($history as $item)
                        <a href="{{ route('symptom-checker.show', $item) }}" class="block rounded-xl border border-slate-100 p-3 hover:bg-slate-50"><div class="flex items-center justify-between gap-2"><span class="text-sm font-semibold text-slate-800">{{ __('health_ai.urgency_'.$item->urgency->value) }}</span><span class="text-xs text-slate-400">{{ $item->created_at->diffForHumans() }}</span></div><p class="mt-1 line-clamp-2 text-xs text-slate-500">{{ $item->summary }}</p></a>
                    @empty
                        <p class="text-sm text-slate-500">{{ __('health_ai.no_assessments') }}</p>
                    @endforelse
                </div>
            </div>
        </aside>
    </div>
</x-app-layout>
