<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="healo-kicker">{{ __('journal.dashboard_kicker') }}</p>
        <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-950">{{ __('journal.dashboard_title') }}</h2>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">{{ __('journal.dashboard_copy') }}</p>
    </div>
    <a href="{{ route('health-journals.create') }}" class="healo-button-primary shrink-0"><i data-lucide="plus" class="size-4"></i>{{ __('healo.add_journal') }}</a>
</div>

<section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-700 via-teal-800 to-indigo-600 p-5 text-white shadow-soft sm:p-7" aria-labelledby="health-insight-heading">
    <div class="absolute -right-20 -top-20 size-64 rounded-full bg-white/10 blur-3xl"></div>
    <div class="absolute -bottom-28 left-1/3 size-56 rounded-full bg-cyan-300/10 blur-3xl"></div>
    <div class="relative">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-3">
                <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-white/10 ring-1 ring-white/20"><i data-lucide="brain-circuit" class="size-6"></i></span>
                <div><h2 id="health-insight-heading" class="text-xl font-black">{{ __('journal.insight_title') }}</h2><p class="mt-1 text-xs font-medium text-emerald-100">{{ __('journal.analyzed_days', ['count' => $dashboard['days_analyzed']]) }}</p></div>
            </div>
            <div class="flex min-w-32 flex-col items-center rounded-3xl border border-white/30 bg-white/15 px-5 py-4 text-center backdrop-blur"><strong class="text-4xl font-black leading-none">{{ $dashboard['score'] ?? '—' }}</strong><span class="mt-2 text-xs font-bold uppercase tracking-widest text-white/80">{{ $dashboard['score_label'] }}</span></div>
        </div>

        <p class="mt-5 max-w-5xl text-sm font-medium leading-7 text-white/90">{{ $dashboard['score'] !== null && $dashboard['days_analyzed'] >= 2 ? __('journal.summary_ready', ['days' => $dashboard['days_analyzed'], 'score' => $dashboard['score'], 'label' => $dashboard['score_label']]) : __('journal.insufficient_summary') }}</p>

        <div class="mt-5 grid gap-3 md:grid-cols-2">
            <article class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/10"><p class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-emerald-100"><i data-lucide="circle-check" class="size-4"></i>{{ __('journal.top_achievement') }}</p><p class="mt-2 text-sm font-semibold leading-6">{{ $dashboard['top_achievement'] }}</p></article>
            <article class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/10"><p class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-amber-100"><i data-lucide="triangle-alert" class="size-4"></i>{{ __('journal.main_concern') }}</p><p class="mt-2 text-sm font-semibold leading-6">{{ $dashboard['main_concern'] }}</p></article>
        </div>

        @if($dashboard['highlights'] !== [])
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach($dashboard['highlights'] as $highlight)
                    <span @class(['inline-flex items-center gap-2 rounded-full border px-3 py-2 text-xs font-semibold', 'border-emerald-200/40 bg-emerald-300/20 text-emerald-50' => $highlight['status'] === 'good', 'border-amber-200/40 bg-amber-300/20 text-amber-50' => $highlight['status'] === 'attention', 'border-white/20 bg-white/10 text-white/90' => $highlight['status'] === 'neutral'])><i data-lucide="{{ $highlight['status'] === 'good' ? 'check' : ($highlight['status'] === 'attention' ? 'circle-alert' : 'info') }}" class="size-3.5"></i><strong>{{ $highlight['label'] }}</strong><span class="text-white/60">·</span><span>{{ $highlight['insight'] }}</span></span>
                @endforeach
            </div>
        @endif

        <p class="mt-5 flex items-center gap-2 text-xs leading-5 text-white/65"><i data-lucide="shield-check" class="size-4 shrink-0"></i>{{ __('journal.educational_note') }}</p>
    </div>
</section>

<section class="healo-card p-5 sm:p-6" aria-labelledby="actions-heading">
    <h2 id="actions-heading" class="flex items-center gap-2 font-bold text-slate-900"><i data-lucide="list-checks" class="size-5 text-emerald-600"></i>{{ __('journal.action_title') }}</h2>
    <ol class="mt-4 space-y-3">@foreach($dashboard['action_items'] as $action)<li class="flex items-start gap-3 text-sm leading-6 text-slate-700"><span class="grid size-6 shrink-0 place-items-center rounded-full bg-emerald-100 text-xs font-black text-emerald-700">{{ $loop->iteration }}</span><span>{{ $action }}</span></li>@endforeach</ol>
</section>

<section class="healo-card overflow-hidden" aria-labelledby="score-heading">
    <div class="border-b border-slate-100 px-5 py-4 sm:px-6"><h2 id="score-heading" class="font-bold text-slate-900">{{ __('journal.score') }}</h2><p class="mt-1 text-xs text-slate-500">{{ __('journal.score_basis') }}</p></div>
    <div class="grid gap-px bg-slate-100 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white p-5"><p class="text-xs font-semibold text-slate-500">{{ __('journal.score') }}</p><p class="mt-2 text-3xl font-black text-slate-950">{{ $dashboard['score'] !== null ? $dashboard['score'].'/100' : '—' }}</p><p class="mt-1 text-xs font-semibold text-emerald-700">{{ $dashboard['score_label'] }}</p></div>
        <div class="bg-white p-5"><p class="text-xs font-semibold text-slate-500">{{ __('journal.metric_weight') }}</p><p class="mt-2 text-2xl font-black text-slate-950">{{ $dashboard['latest']['weight'] !== null ? number_format($dashboard['latest']['weight'], 1).' kg' : '—' }}</p><p class="mt-1 text-xs text-slate-400">{{ __('journal.latest_log') }}</p></div>
        <div class="bg-white p-5"><p class="text-xs font-semibold text-slate-500">{{ __('journal.metric_blood_pressure') }}</p><p class="mt-2 text-2xl font-black text-slate-950">{{ $dashboard['latest']['systolic'] !== null && $dashboard['latest']['diastolic'] !== null ? $dashboard['latest']['systolic'].'/'.$dashboard['latest']['diastolic'] : '—' }}</p><p class="mt-1 text-xs text-slate-400">mmHg · {{ __('journal.latest_log') }}</p></div>
        <div class="bg-white p-5"><p class="text-xs font-semibold text-slate-500">{{ __('journal.recorded_days') }}</p><p class="mt-2 text-2xl font-black text-slate-950">{{ $dashboard['days_recorded'] }}</p><p class="mt-1 text-xs text-slate-400">{{ __('journal.latest_log') }}</p></div>
    </div>
    <div class="border-t border-slate-100 px-5 py-4 sm:px-6"><h3 class="text-sm font-bold text-slate-800">{{ __('journal.seven_day_average') }}</h3></div>
    <div class="grid gap-px bg-slate-100 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-slate-50/60 p-5"><p class="text-xs font-semibold text-slate-500">{{ __('journal.metric_sleep') }}</p><p class="mt-1 text-xl font-black text-slate-900">{{ $dashboard['averages']['sleep'] !== null ? number_format($dashboard['averages']['sleep'], 1).' jam' : '—' }}</p></div>
        <div class="bg-slate-50/60 p-5"><p class="text-xs font-semibold text-slate-500">{{ __('journal.metric_water') }}</p><p class="mt-1 text-xl font-black text-slate-900">{{ $dashboard['averages']['water'] !== null ? number_format($dashboard['averages']['water'], 1).' L' : '—' }}</p></div>
        <div class="bg-slate-50/60 p-5"><p class="text-xs font-semibold text-slate-500">{{ __('journal.metric_exercise') }}</p><p class="mt-1 text-xl font-black text-slate-900">{{ $dashboard['averages']['exercise'] !== null ? number_format($dashboard['averages']['exercise'], 0).' mnt' : '—' }}</p></div>
        <div class="bg-slate-50/60 p-5"><p class="text-xs font-semibold text-slate-500">{{ __('journal.metric_mood') }}</p><p class="mt-1 text-xl font-black text-slate-900">{{ $dashboard['averages']['mood'] !== null ? number_format($dashboard['averages']['mood'], 1).'/5' : '—' }}</p></div>
    </div>
</section>
