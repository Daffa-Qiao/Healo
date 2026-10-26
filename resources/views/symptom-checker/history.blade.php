<x-app-layout>
    <x-slot name="title">{{ __('health_ai.history') }}</x-slot>
    <x-slot name="header"><div class="flex items-center justify-between gap-4"><h1 class="text-lg font-bold text-slate-900">{{ __('health_ai.history') }}</h1><a href="{{ route('symptom-checker.create') }}" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white">{{ __('health_ai.analyze') }}</a></div></x-slot>
    <div class="mx-auto max-w-5xl">
        <div class="healo-card divide-y divide-slate-100 overflow-hidden">
            @forelse($assessments as $assessment)
                <a href="{{ route('symptom-checker.show', $assessment) }}" class="flex items-start justify-between gap-5 p-5 hover:bg-slate-50"><div><div class="flex items-center gap-2"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">{{ __('health_ai.urgency_'.$assessment->urgency->value) }}</span><span class="text-xs text-slate-400">{{ $assessment->created_at->translatedFormat('d M Y, H:i') }}</span></div><p class="mt-3 line-clamp-2 text-sm leading-6 text-slate-600">{{ $assessment->summary }}</p></div><i data-lucide="chevron-right" class="mt-2 size-5 shrink-0 text-slate-300"></i></a>
            @empty
                <div class="p-10 text-center text-sm text-slate-500">{{ __('health_ai.no_assessments') }}</div>
            @endforelse
        </div>
        <div class="mt-5">{{ $assessments->links() }}</div>
    </div>
</x-app-layout>
