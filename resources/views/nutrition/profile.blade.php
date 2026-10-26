@php
    $preferences = old('food_preferences', $profile?->food_preferences ?? []);
    $preferences = is_array($preferences) ? implode("\n", $preferences) : $preferences;
    $healthComplete = $healthProfile?->birth_date && $healthProfile?->gender && $healthProfile?->height_cm && $healthProfile?->weight_kg;
@endphp
<x-app-layout>
    <x-slot name="title">{{ __('nutrition.profile') }}</x-slot>
    <x-slot name="header"><p class="text-xs font-medium text-slate-500">{{ __('nutrition.title') }}</p><h1 class="text-lg font-bold text-slate-900">{{ __('nutrition.profile') }}</h1></x-slot>
    <div class="mx-auto max-w-5xl space-y-6">
        @if(session('status'))<div role="status" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ __('nutrition.'.session('status')) }}</div>@endif
        @if($errors->has('calorie_target'))<div role="alert" class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ $errors->first('calorie_target') }}</div>@endif
        <div class="grid gap-6 lg:grid-cols-[1fr_19rem]">
            <form method="POST" action="{{ route('nutrition.profile.update') }}" class="healo-card p-6 sm:p-8" x-data="{ saving: false }" @submit="saving = true">@csrf @method('PUT')
                <p class="healo-kicker">{{ __('nutrition.profile') }}</p><h2 class="mt-2 text-2xl font-black text-slate-950">{{ __('nutrition.profile_copy') }}</h2>
                <div class="mt-7 grid gap-5 sm:grid-cols-2">
                    <div><x-input-label for="activity_level" :value="__('nutrition.activity_level')"/><select id="activity_level" name="activity_level" required class="mt-1 w-full rounded-xl border-slate-300">@foreach($activityLevels as $level)<option value="{{ $level->value }}" @selected(old('activity_level', $profile?->activity_level?->value ?? $healthProfile?->activity_level?->value ?? 'moderate') === $level->value)>{{ __('nutrition.activity_'.$level->value) }}</option>@endforeach</select><x-input-error :messages="$errors->get('activity_level')" class="mt-2"/></div>
                    <div><x-input-label for="goal" :value="__('nutrition.goal')"/><select id="goal" name="goal" required class="mt-1 w-full rounded-xl border-slate-300">@foreach($goals as $goal)<option value="{{ $goal->value }}" @selected(old('goal', $profile?->goal?->value ?? 'maintain') === $goal->value)>{{ __('nutrition.goal_'.$goal->value) }}</option>@endforeach</select><x-input-error :messages="$errors->get('goal')" class="mt-2"/></div>
                    <div><x-input-label for="diet_preference" :value="__('nutrition.diet_preference')"/><select id="diet_preference" name="diet_preference" required class="mt-1 w-full rounded-xl border-slate-300">@foreach($dietPreferences as $diet)<option value="{{ $diet->value }}" @selected(old('diet_preference', $profile?->diet_preference?->value ?? 'balanced') === $diet->value)>{{ __('nutrition.diet_'.$diet->value) }}</option>@endforeach</select></div>
                    <div><x-input-label for="daily_calorie_target" :value="__('nutrition.manual_target')"/><x-text-input id="daily_calorie_target" name="daily_calorie_target" type="number" min="1200" max="4500" class="mt-1 w-full" :value="old('daily_calorie_target', $profile?->daily_calorie_target)"/><p class="mt-1 text-xs text-slate-500">{{ __('nutrition.manual_target_hint') }}</p><x-input-error :messages="$errors->get('daily_calorie_target')" class="mt-2"/></div>
                </div>
                <div class="mt-5"><x-input-label for="food_preferences" :value="__('nutrition.food_preferences')"/><textarea id="food_preferences" name="food_preferences" rows="4" class="mt-1 w-full rounded-xl border-slate-300">{{ $preferences }}</textarea><p class="mt-1 text-xs text-slate-500">{{ __('nutrition.food_preferences_hint') }}</p></div>
                <label class="mt-5 flex items-center gap-3 text-sm text-slate-700"><input type="checkbox" name="recalculate" value="1" class="rounded border-slate-300 text-emerald-600" @checked(old('recalculate', !$profile)) @disabled(!$healthComplete)>{{ __('nutrition.recalculate') }}</label>
                <div class="mt-7 flex flex-wrap gap-3"><button class="healo-button-primary" :disabled="saving"><i data-lucide="save" class="size-4"></i>{{ __('nutrition.save_profile') }}</button><a href="{{ route('nutrition.index') }}" class="healo-button-secondary">{{ __('nutrition.cancel') }}</a></div>
            </form>
            <aside class="space-y-4"><div class="healo-card p-5"><p class="font-bold text-slate-900">{{ __('nutrition.health_data_used') }}</p>@if($healthComplete)<dl class="mt-4 space-y-2 text-sm text-slate-600"><div class="flex justify-between"><dt>{{ __('healo.height_cm') }}</dt><dd class="font-semibold">{{ (float) $healthProfile->height_cm }}</dd></div><div class="flex justify-between"><dt>{{ __('healo.weight_kg') }}</dt><dd class="font-semibold">{{ (float) $healthProfile->weight_kg }}</dd></div><div class="flex justify-between"><dt>{{ __('healo.birth_date') }}</dt><dd class="font-semibold">{{ $healthProfile->birth_date->translatedFormat('d M Y') }}</dd></div></dl>@else<p class="mt-3 text-sm text-amber-700">{{ __('nutrition.health_profile_required') }}</p><a href="{{ route('health-profile.edit') }}" class="mt-3 inline-flex text-sm font-semibold text-emerald-700">{{ __('healo.complete_health_profile') }}</a>@endif</div><div class="rounded-2xl border border-sky-200 bg-sky-50 p-4 text-sm leading-6 text-sky-900">{{ __('nutrition.estimation_disclaimer') }}</div></aside>
        </div>
    </div>
</x-app-layout>
