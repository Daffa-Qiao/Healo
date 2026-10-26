<x-app-layout>
    <x-slot name="title">{{ __('nutrition.new_meal') }}</x-slot>
    <x-slot name="header"><p class="text-xs font-medium text-slate-500">{{ __('nutrition.title') }}</p><h1 class="text-lg font-bold text-slate-900">{{ __('nutrition.new_meal') }}</h1></x-slot>
    <div class="mx-auto max-w-3xl"><form method="POST" action="{{ route('meals.store') }}" class="healo-card p-6 sm:p-8"><p class="healo-kicker">{{ __('nutrition.add_meal') }}</p><h2 class="mt-2 text-2xl font-black text-slate-950">{{ __('nutrition.new_meal') }}</h2><div class="mt-7">@include('meals.partials.form')</div></form></div>
</x-app-layout>
