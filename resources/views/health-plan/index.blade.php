<x-app-layout>
    <x-slot name="title">{{ __('wellness.title') }}</x-slot>
    <x-slot name="header">
        <p class="text-xs font-medium text-slate-500">{{ now()->translatedFormat('l, d F Y') }}</p>
        <h1 class="text-lg font-bold text-slate-900">{{ __('wellness.your_health_today') }}</h1>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6">
        @if(session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first() }}</div>
        @endif

        <section class="relative overflow-hidden rounded-[2rem] bg-slate-950 p-6 text-white shadow-soft sm:p-8">
            <div class="absolute -right-20 -top-24 size-64 rounded-full bg-emerald-400/20 blur-3xl"></div>
            <div class="relative grid gap-7 lg:grid-cols-[1fr_280px] lg:items-center">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-300">{{ __('wellness.title') }}</p>
                    <h2 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl">{{ __('wellness.your_health_today') }}</h2>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">{{ __('wellness.subtitle') }}</p>
                </div>
                @if($plan)
                    <div class="rounded-3xl border border-white/10 bg-white/10 p-5 backdrop-blur">
                        <div class="flex items-end justify-between"><span class="text-sm font-semibold text-slate-200">{{ __('wellness.today_progress') }}</span><span class="text-3xl font-black text-emerald-300">{{ $metrics['percentage'] }}%</span></div>
                        <div class="mt-4 h-3 overflow-hidden rounded-full bg-white/10" role="progressbar" aria-valuenow="{{ $metrics['percentage'] }}" aria-valuemin="0" aria-valuemax="100"><div class="h-full rounded-full bg-emerald-400 transition-all" style="width: {{ $metrics['percentage'] }}%"></div></div>
                        <p class="mt-3 text-xs text-slate-300">{{ __('wellness.completed_count', ['completed' => $metrics['completed'], 'total' => $metrics['total']]) }} · {{ __('wellness.remaining_count', ['count' => $metrics['remaining']]) }}</p>
                    </div>
                @endif
            </div>
        </section>

        @if(!$plan)
            <section class="healo-card p-8 text-center sm:p-12">
                <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-emerald-50 text-emerald-700"><i data-lucide="calendar-check-2" class="size-7"></i></span>
                <h2 class="mt-5 text-xl font-black text-slate-900">{{ __('wellness.empty_title') }}</h2>
                <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500">{{ __('wellness.empty_body') }}</p>
                <form method="POST" action="{{ route('health-plan.generate') }}" class="mt-6">@csrf
                    <button class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-700"><i data-lucide="sparkles" class="size-5"></i>{{ __('wellness.generate_plan') }}</button>
                </form>
            </section>
        @else
            <section class="grid gap-6 xl:grid-cols-[1.45fr_0.8fr]">
                <div class="healo-card p-5 sm:p-6">
                    <div class="flex items-center justify-between"><div><p class="healo-kicker">{{ __('wellness.today_progress') }}</p><h2 class="mt-1 text-xl font-black text-slate-900">{{ __('wellness.today_tasks') }}</h2></div><span class="grid size-11 place-items-center rounded-2xl bg-emerald-50 text-emerald-700"><i data-lucide="list-checks" class="size-5"></i></span></div>
                    @foreach(['pending', 'completed'] as $group)
                        @php $groupTasks = $plan->tasks->filter(fn($task) => $task->status->value === $group); @endphp
                        @if($groupTasks->isNotEmpty())
                            <div class="mt-6">
                                <h3 class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('wellness.'.$group) }}</h3>
                                <div class="mt-3 space-y-3">
                                    @foreach($groupTasks as $task)
                                        <article class="rounded-2xl border border-slate-200 p-4 sm:flex sm:items-center sm:justify-between sm:gap-4">
                                            <div class="flex min-w-0 items-start gap-3">
                                                <span @class(['mt-0.5 grid size-9 shrink-0 place-items-center rounded-xl', 'bg-emerald-100 text-emerald-700' => $group === 'completed', 'bg-slate-100 text-slate-500' => $group === 'pending'])><i data-lucide="{{ $group === 'completed' ? 'check' : 'circle' }}" class="size-4"></i></span>
                                                <div><h4 @class(['font-bold', 'text-slate-900' => $group === 'pending', 'text-slate-500 line-through' => $group === 'completed'])>{{ __($task->title) }}</h4><p class="mt-1 text-xs leading-5 text-slate-500">{{ __($task->description) }}</p><p class="mt-1 text-[11px] font-semibold text-slate-400">{{ $task->completion_mode->value === 'data' ? __('wellness.automatic_task') : __('wellness.manual_task') }}</p></div>
                                            </div>
                                            <div class="mt-3 shrink-0 sm:mt-0">
                                                @if($task->completion_mode->value === 'manual')
                                                    <form method="POST" action="{{ $group === 'completed' ? route('health-plan.tasks.uncomplete', $task) : route('health-plan.tasks.complete', $task) }}">@csrf
                                                        <button class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-800">{{ $group === 'completed' ? __('wellness.mark_uncomplete') : __('wellness.mark_complete') }}</button>
                                                    </form>
                                                @elseif($task->type->value === 'nutrition_log')
                                                    <a href="{{ route('meals.create') }}" class="inline-flex w-full justify-center rounded-xl bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-800 hover:bg-emerald-100">{{ __('wellness.add_meal') }}</a>
                                                @else
                                                    <a href="{{ route('health-journals.create') }}" class="inline-flex w-full justify-center rounded-xl bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-800 hover:bg-emerald-100">{{ __('wellness.write_journal') }}</a>
                                                @endif
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="space-y-6">
                    <section class="healo-card p-5 sm:p-6"><div class="flex items-center gap-3"><span class="grid size-10 place-items-center rounded-2xl bg-sky-50 text-sky-700"><i data-lucide="activity" class="size-5"></i></span><h2 class="font-black text-slate-900">{{ __('wellness.health_summary') }}</h2></div><dl class="mt-5 grid grid-cols-2 gap-3">
                        @foreach([
                            [__('wellness.current_bmi'), $latestBmi ? number_format((float) $latestBmi->bmi, 1) : __('wellness.no_data')],
                            [__('wellness.today_calories'), number_format((float) $todayNutrition->calories_consumed).' kcal'],
                            [__('wellness.active_goals'), $activeGoalCount],
                            [__('wellness.journal_status'), $hasJournalToday ? __('wellness.journal_done') : __('wellness.journal_pending')],
                        ] as [$label, $value])<div class="rounded-2xl bg-slate-50 p-3"><dt class="text-xs text-slate-500">{{ $label }}</dt><dd class="mt-1 text-sm font-black text-slate-900">{{ $value }}</dd></div>@endforeach
                    </dl></section>
                    <section class="rounded-3xl border border-violet-100 bg-violet-50/70 p-5 sm:p-6"><div class="flex items-center justify-between gap-3"><div class="flex items-center gap-3"><span class="grid size-10 place-items-center rounded-2xl bg-violet-100 text-violet-700"><i data-lucide="sparkles" class="size-5"></i></span><h2 class="font-black text-slate-900">{{ __('wellness.ai_insight') }}</h2></div><span class="text-[10px] font-bold uppercase tracking-wide text-violet-600">{{ __('wellness.'.($personalization->provider === 'groq' ? 'provider_groq' : 'provider_local')) }}</span></div><p class="mt-4 text-sm font-semibold leading-6 text-slate-800">{{ $personalization->summary }}</p><p class="mt-2 text-sm leading-6 text-slate-600">{{ $personalization->suggestion }}</p><p class="mt-3 text-xs font-semibold text-violet-700">{{ $personalization->motivation }}</p><p class="mt-4 border-t border-violet-100 pt-3 text-[11px] leading-5 text-slate-500">{{ $personalization->disclaimer }}</p></section>
                </div>
            </section>
        @endif

        <section class="healo-card p-5 sm:p-6"><p class="healo-kicker">{{ __('wellness.quick_actions') }}</p><div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            @foreach([
                [route('meals.create'), 'utensils', __('wellness.add_meal')], [route('health-journals.create'), 'notebook-pen', __('wellness.write_journal')], [route('bmi.index'), 'gauge', __('wellness.view_bmi')], [route('health-goals.index'), 'target', __('wellness.view_goals')], [route('nutrition.index'), 'salad', __('wellness.view_nutrition')], [route('health-assistant.index'), 'sparkles', __('wellness.ask_assistant')],
            ] as [$url, $icon, $label])<a href="{{ $url }}" class="rounded-2xl border border-slate-200 p-3 text-center text-xs font-bold text-slate-700 hover:border-emerald-200 hover:bg-emerald-50"><i data-lucide="{{ $icon }}" class="mx-auto mb-2 size-5 text-emerald-700"></i>{{ $label }}</a>@endforeach
        </div></section>
    </div>
</x-app-layout>
