<x-app-layout>
    <x-slot name="title">{{ __('healo.health_journal') }}</x-slot>
    <x-slot name="header">
        <p class="text-xs font-medium text-slate-500">{{ __('healo.health') }}</p>
        <h1 class="text-lg font-bold text-slate-900">{{ __('healo.health_journal') }}</h1>
    </x-slot>

    @php
        $charts = $dashboard['charts'];
        $hasWeight = collect($charts['weight'])->contains(fn ($value) => $value !== null);
        $hasBloodPressure = collect($charts['systolic'])->contains(fn ($value) => $value !== null)
            && collect($charts['diastolic'])->contains(fn ($value) => $value !== null);
        $hasSleepWater = collect($charts['sleep'])->contains(fn ($value) => $value !== null)
            || collect($charts['water'])->contains(fn ($value) => $value !== null);
        $hasMoodExercise = collect($charts['mood'])->contains(fn ($value) => $value !== null)
            || collect($charts['exercise'])->contains(fn ($value) => $value !== null);
    @endphp

    <div class="mx-auto max-w-7xl space-y-6">
        @if(session('status'))
            <div role="status" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ __('healo.'.session('status')) }}</div>
        @endif

        @include('health-journals.partials.dashboard-overview')
        @include('health-journals.partials.dashboard-trends')
        @include('health-journals.partials.dashboard-history')
    </div>
</x-app-layout>
