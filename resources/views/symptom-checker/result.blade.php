@php
    $stored = $assessment !== null;
    $urgency = $result->urgency->value;
    $symptoms = $result->symptoms;
    $categories = $stored ? $result->possible_categories : $result->possibleCategories;
    $differential = $result->differential ?? [];
    $recommendedSpecialty = $stored ? $result->recommended_specialty : $result->recommendedSpecialty;
    $referralNeeded = (bool) ($stored ? $result->referral_needed : $result->referralNeeded);
    $redFlags = $stored ? $result->red_flags : $result->redFlags;
    $emergency = $urgency === 'emergency';
    $urgencyPresentation = match ($urgency) {
        'emergency' => [
            'label' => __('health_ai.urgency_banner_emergency'),
            'wrapper' => 'border-rose-300 bg-rose-50 text-rose-950',
            'icon' => 'bg-rose-100 text-rose-700',
        ],
        'urgent' => [
            'label' => __('health_ai.urgency_banner_urgent'),
            'wrapper' => 'border-orange-300 bg-orange-50 text-orange-950',
            'icon' => 'bg-orange-100 text-orange-700',
        ],
        'soon' => [
            'label' => __('health_ai.urgency_banner_soon'),
            'wrapper' => 'border-amber-300 bg-amber-50 text-amber-950',
            'icon' => 'bg-amber-100 text-amber-700',
        ],
        default => [
            'label' => __('health_ai.urgency_banner_routine'),
            'wrapper' => 'border-emerald-200 bg-emerald-50 text-emerald-950',
            'icon' => 'bg-emerald-100 text-emerald-700',
        ],
    };
@endphp
<x-app-layout>
    <x-slot name="title">{{ __('health_ai.your_result') }}</x-slot>
    <x-slot name="header"><div class="flex items-center justify-between gap-4"><div><p class="healo-kicker">{{ __('health_ai.symptom_checker') }}</p><h1 class="text-lg font-bold text-slate-900">{{ __('health_ai.your_result') }}</h1></div><a href="{{ route('symptom-checker.create') }}" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white">{{ __('health_ai.analyze') }}</a></div></x-slot>

    <div class="mx-auto max-w-4xl space-y-5">
        @if($emergency)
            <section class="rounded-3xl border-2 border-rose-300 bg-rose-50 p-6 text-rose-950 sm:p-8" role="alert">
                <div class="flex gap-4"><span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-rose-600 text-white"><i data-lucide="triangle-alert" class="size-6"></i></span><div><h2 class="text-xl font-black">{{ __('health_ai.emergency_attention') }}</h2><p class="mt-2 font-semibold leading-6">{{ $result->recommendation }}</p><p class="mt-2 text-sm">{{ __('health_ai.emergency_local_help') }}</p></div></div>
                @if(config('health_ai.emergency.phone') || config('health_ai.emergency.facility_url'))
                    <div class="mt-5 flex flex-wrap gap-3">
                        @if(config('health_ai.emergency.phone'))<a href="tel:{{ config('health_ai.emergency.phone') }}" class="rounded-xl bg-rose-700 px-5 py-3 text-sm font-bold text-white">{{ __('health_ai.call_emergency') }}</a>@endif
                        @if(config('health_ai.emergency.facility_url'))<a href="{{ config('health_ai.emergency.facility_url') }}" rel="noopener noreferrer" class="rounded-xl border border-rose-300 bg-white px-5 py-3 text-sm font-bold text-rose-800">{{ __('health_ai.find_emergency_facility') }}</a>@endif
                    </div>
                @endif
            </section>
        @endif

        <section class="healo-card p-4 sm:p-8">
            @if(!$emergency)
                <div class="flex items-center gap-3 rounded-xl border px-3 py-3.5 {{ $urgencyPresentation['wrapper'] }}" role="status">
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg {{ $urgencyPresentation['icon'] }}"><i data-lucide="triangle-alert" class="size-4"></i></span>
                    <p class="text-sm font-black">{{ $urgencyPresentation['label'] }}</p>
                </div>
            @endif

            <div class="mt-6 flex items-end justify-between gap-3">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.08em] text-emerald-700">{{ __('health_ai.possible_conditions') }}</p>
                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('health_ai.differential_note') }}</p>
                </div>
                <span class="shrink-0 rounded-full bg-sky-50 px-2 py-1 text-[10px] font-bold text-sky-700">{{ $result->provider === 'groq' ? __('health_ai.groq_badge') : __('health_ai.demo_badge') }}</span>
            </div>

            <div class="mt-3 grid grid-cols-2 items-start gap-2.5 sm:gap-3 lg:grid-cols-3">
                @forelse($differential as $item)
                    @php
                        $probability = max(0, min(0.99, (float) ($item['probability'] ?? 0)));
                        $matchLevel = $probability >= 0.6 ? 'high' : ($probability >= 0.3 ? 'medium' : 'low');
                    @endphp
                    <article class="h-full rounded-xl border border-slate-200 bg-white p-3 shadow-sm sm:p-4">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="min-w-0 text-xs font-black leading-5 text-slate-950 sm:text-sm">{{ $item['condition'] }}</h3>
                            <span @class([
                                'shrink-0 rounded-full px-2 py-1 text-[8px] font-black uppercase tracking-wide',
                                'bg-rose-50 text-rose-700' => $matchLevel === 'high',
                                'bg-amber-50 text-amber-700' => $matchLevel === 'medium',
                                'bg-sky-50 text-sky-700' => $matchLevel === 'low',
                            ])>{{ __('health_ai.match_'.$matchLevel) }}</span>
                        </div>
                        <p class="mt-2 text-[11px] leading-[1.65] text-slate-500">{{ __('health_ai.probability_match', ['percent' => number_format($probability * 100)]) }}</p>
                        <p class="mt-1 text-[11px] leading-[1.65] text-slate-500">{{ __('health_ai.condition_follow_up', ['specialty' => $item['specialty'] ?: ($recommendedSpecialty ?: __('health_ai.general_practitioner'))]) }}</p>
                    </article>
                @empty
                    @forelse($categories as $category)
                        <article class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm sm:p-4">
                            <h3 class="text-xs font-black leading-5 text-slate-950 sm:text-sm">{{ $category }}</h3>
                            <p class="mt-2 text-[11px] leading-[1.65] text-slate-500">{{ __('health_ai.possible_categories_note') }}</p>
                        </article>
                    @empty
                        <p class="col-span-2 rounded-xl border border-dashed border-slate-200 p-5 text-center text-xs text-slate-500 lg:col-span-3">{{ __('health_ai.no_possible_conditions') }}</p>
                    @endforelse
                @endforelse
            </div>

            <div class="mt-7 border-t border-slate-100 pt-6">
                <h2 class="text-lg font-bold text-slate-900">{{ $result->summary }}</h2>
                <p class="mt-2 text-xs leading-5 text-slate-500">{{ __('health_ai.not_doctor') }}</p>

                <div class="mt-5 rounded-xl bg-slate-50 p-4">
                    <h3 class="text-sm font-bold text-slate-900">{{ __('health_ai.what_you_described') }}</h3>
                    <ul class="mt-3 flex flex-wrap gap-2">@forelse($symptoms as $symptom)<li class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-600"><span class="font-semibold">{{ $symptom['name'] }}</span> · {{ __('health_ai.match_'.$symptom['severity']) }}</li>@empty<li class="text-xs text-slate-500">—</li>@endforelse</ul>
                </div>

                @if($redFlags !== [])<div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 p-4"><h3 class="text-sm font-bold text-rose-900">{{ __('health_ai.red_flags') }}</h3><ul class="mt-2 list-inside list-disc space-y-1 text-xs leading-5 text-rose-800">@foreach($redFlags as $flag)<li>{{ str($flag)->replace('_', ' ')->headline() }}</li>@endforeach</ul></div>@endif
                @if($recommendedSpecialty)<div class="mt-4 rounded-xl border border-sky-200 bg-sky-50 p-4"><h3 class="text-sm font-bold text-sky-950">{{ __('health_ai.recommended_specialty') }}</h3><p class="mt-1 text-xs text-sky-900">{{ $recommendedSpecialty }}</p>@if($referralNeeded)<p class="mt-2 text-xs font-bold text-sky-800">{{ __('health_ai.referral_recommended') }}</p>@endif</div>@endif
                <div class="mt-4 rounded-xl bg-slate-50 p-4"><h3 class="text-sm font-bold text-slate-900">{{ __('health_ai.recommendation') }}</h3><p class="mt-2 whitespace-pre-line text-xs leading-5 text-slate-600">{{ $result->recommendation }}</p></div>
                <div class="mt-4 flex flex-wrap gap-4 text-[11px] text-slate-400"><span>{{ __('health_ai.confidence') }}: {{ __('health_ai.'.$result->confidence->value) }}</span><span>{{ __('health_ai.provider') }}: {{ $result->provider }}</span></div>
            </div>
        </section>

        @if($stored)
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-between">
                @if(!$assessment->saved_to_journal_at)
                    <form method="POST" action="{{ route('symptom-checker.journal.store', $assessment) }}" onsubmit="return confirm(@js(__('health_ai.save_to_journal_copy')))" class="flex items-center gap-2">@csrf<input type="hidden" name="confirm" value="1"><button class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white"><i data-lucide="notebook-pen" class="size-4"></i>{{ __('health_ai.save_to_journal') }}</button></form>
                @else
                    <a href="{{ route('health-journals.show', $assessment->health_journal_id) }}" class="inline-flex items-center gap-2 text-sm font-bold text-emerald-700"><i data-lucide="check" class="size-4"></i>{{ __('health_ai.save_to_journal') }}</a>
                @endif
                <form method="POST" action="{{ route('symptom-checker.destroy', $assessment) }}" onsubmit="return confirm(@js(__('health_ai.delete_confirmation')))">@csrf @method('DELETE')<button class="inline-flex items-center gap-2 rounded-xl border border-rose-200 px-5 py-3 text-sm font-bold text-rose-700"><i data-lucide="trash-2" class="size-4"></i>{{ __('health_ai.delete_assessment') }}</button></form>
            </div>
        @endif
    </div>
</x-app-layout>
