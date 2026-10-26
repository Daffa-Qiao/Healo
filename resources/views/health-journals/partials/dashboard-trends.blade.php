<section class="space-y-4" aria-labelledby="trends-heading">
    <div><p class="healo-kicker">{{ __('journal.trend_title') }}</p><h2 id="trends-heading" class="mt-1 text-xl font-bold text-slate-900">{{ __('journal.dashboard_copy') }}</h2></div>
    <div class="grid gap-5 xl:grid-cols-2">
        @foreach([
            ['id' => 'journal-weight-chart', 'title' => __('journal.chart_weight'), 'icon' => 'scale', 'tone' => 'emerald', 'hasData' => $hasWeight],
            ['id' => 'journal-bp-chart', 'title' => __('journal.chart_blood_pressure'), 'icon' => 'heart-pulse', 'tone' => 'rose', 'hasData' => $hasBloodPressure],
            ['id' => 'journal-sleep-water-chart', 'title' => __('journal.chart_sleep_water'), 'icon' => 'moon-star', 'tone' => 'violet', 'hasData' => $hasSleepWater],
            ['id' => 'journal-mood-exercise-chart', 'title' => __('journal.chart_mood_exercise'), 'icon' => 'smile', 'tone' => 'amber', 'hasData' => $hasMoodExercise],
        ] as $chart)
            <article class="healo-card overflow-hidden">
                <header @class(['flex items-center gap-3 border-b border-slate-100 px-5 py-4', 'bg-emerald-50/60' => $chart['tone'] === 'emerald', 'bg-rose-50/60' => $chart['tone'] === 'rose', 'bg-violet-50/60' => $chart['tone'] === 'violet', 'bg-amber-50/60' => $chart['tone'] === 'amber'])>
                    <i data-lucide="{{ $chart['icon'] }}" @class(['size-5', 'text-emerald-600' => $chart['tone'] === 'emerald', 'text-rose-600' => $chart['tone'] === 'rose', 'text-violet-600' => $chart['tone'] === 'violet', 'text-amber-600' => $chart['tone'] === 'amber'])></i>
                    <h3 class="font-bold text-slate-800">{{ $chart['title'] }}</h3>
                </header>
                @if($chart['hasData'])
                    <div class="h-72 p-4"><canvas id="{{ $chart['id'] }}" aria-label="{{ $chart['title'] }}"></canvas></div>
                @else
                    <div class="grid h-72 place-items-center p-8 text-center"><div><i data-lucide="chart-no-axes-column-increasing" class="mx-auto size-8 text-slate-300"></i><p class="mt-3 max-w-xs text-sm leading-6 text-slate-500">{{ __('journal.chart_empty') }}</p></div></div>
                @endif
            </article>
        @endforeach
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const Chart = await window.Healo.chart();
        const trends = @js($charts);
        const common = {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } } },
            scales: { x: { grid: { display: false } }, y: { beginAtZero: false, grid: { color: '#f1f5f9' } } },
        };

        const weight = document.getElementById('journal-weight-chart');
        if (weight) new Chart(weight, { type: 'line', data: { labels: trends.labels, datasets: [{ label: @js(__('journal.metric_weight')), data: trends.weight, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,.12)', fill: true, tension: .35, spanGaps: true }] }, options: common });

        const pressure = document.getElementById('journal-bp-chart');
        if (pressure) new Chart(pressure, { type: 'line', data: { labels: trends.labels, datasets: [{ label: @js(__('journal.systolic')), data: trends.systolic, borderColor: '#f43f5e', tension: .35, spanGaps: true }, { label: @js(__('journal.diastolic')), data: trends.diastolic, borderColor: '#f97316', tension: .35, spanGaps: true }] }, options: common });

        const sleepWater = document.getElementById('journal-sleep-water-chart');
        if (sleepWater) new Chart(sleepWater, { type: 'bar', data: { labels: trends.labels, datasets: [{ label: @js(__('journal.sleep_hours')), data: trends.sleep, backgroundColor: 'rgba(139,92,246,.65)', borderRadius: 6, yAxisID: 'y' }, { label: @js(__('journal.water_liters')), data: trends.water, backgroundColor: 'rgba(14,165,233,.65)', borderRadius: 6, yAxisID: 'y1' }] }, options: { ...common, scales: { ...common.scales, y1: { position: 'right', grid: { drawOnChartArea: false } } } } });

        const moodExercise = document.getElementById('journal-mood-exercise-chart');
        if (moodExercise) new Chart(moodExercise, { type: 'line', data: { labels: trends.labels, datasets: [{ label: @js(__('journal.mood_scale')), data: trends.mood, borderColor: '#eab308', backgroundColor: 'rgba(234,179,8,.12)', fill: true, tension: .35, spanGaps: true, yAxisID: 'y' }, { label: @js(__('journal.exercise_minutes')), data: trends.exercise, borderColor: '#f97316', tension: .35, spanGaps: true, yAxisID: 'y1' }] }, options: { ...common, scales: { x: common.scales.x, y: { min: 1, max: 5, ticks: { stepSize: 1 }, grid: { color: '#f1f5f9' } }, y1: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false } } } } });
    });
</script>
