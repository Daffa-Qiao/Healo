<x-app-layout>
    <x-slot name="title">{{ __('healo.'.$feature['title']) }}</x-slot>
    <x-slot name="header"><h1 class="text-lg font-bold text-slate-900">{{ __('healo.'.$feature['title']) }}</h1></x-slot>

    <div class="mx-auto max-w-3xl">
        <div class="healo-card overflow-hidden">
            <div class="bg-gradient-to-br from-emerald-50 via-white to-sky-50 p-8 sm:p-12">
                <span class="grid size-16 place-items-center rounded-3xl bg-emerald-600 text-white shadow-xl shadow-emerald-600/20"><i data-lucide="{{ $feature['icon'] }}" class="size-8"></i></span>
                <p class="healo-kicker mt-8">{{ __('healo.phase_number', ['phase' => $feature['phase']]) }}</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">{{ __('healo.'.$feature['title']) }}</h1>
                <p class="mt-4 max-w-xl leading-7 text-slate-600">{{ __('healo.feature_planned_copy') }}</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('dashboard') }}" class="healo-button-primary"><i data-lucide="arrow-left" class="size-4"></i>{{ __('healo.back_dashboard') }}</a>
                    <a href="{{ route('profile.edit') }}" class="healo-button-secondary"><i data-lucide="user-round" class="size-4"></i>{{ __('healo.complete_profile') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

