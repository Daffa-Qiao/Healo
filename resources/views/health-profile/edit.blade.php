@php
    $conditionsValue = old('health_conditions', $profile?->health_conditions ?? []);
    $allergiesValue = old('allergies', $profile?->allergies ?? []);
    $conditionsValue = is_array($conditionsValue) ? implode("\n", $conditionsValue) : $conditionsValue;
    $allergiesValue = is_array($allergiesValue) ? implode("\n", $allergiesValue) : $allergiesValue;
@endphp
<x-app-layout>
    <x-slot name="title">{{ __('healo.health_profile') }}</x-slot>
    <x-slot name="header"><p class="text-xs font-medium text-slate-500">{{ __('healo.health') }}</p><h1 class="text-lg font-bold text-slate-900">{{ __('healo.health_profile') }}</h1></x-slot>

    <div class="mx-auto max-w-5xl space-y-6">
        @if(session('status') === 'health-profile-updated')
            <div role="status" class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800"><i data-lucide="circle-check" class="size-5"></i>{{ __('healo.health_profile_saved') }}</div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[1fr_18rem]">
            <form method="POST" action="{{ route('health-profile.update') }}" x-data="{ saving: false }" @submit="saving = true" class="healo-card p-5 sm:p-8">
                @csrf @method('PUT')
                <div><p class="healo-kicker">{{ __('healo.personal_health_data') }}</p><h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ __('healo.complete_health_profile') }}</h2><p class="mt-2 text-sm leading-6 text-slate-500">{{ __('healo.health_profile_helper') }}</p></div>

                <div class="mt-8 grid gap-5 sm:grid-cols-2">
                    <div><x-input-label for="height_cm" :value="__('healo.height_cm')" /><x-text-input id="height_cm" name="height_cm" type="number" min="80" max="250" step="0.1" class="mt-1 block w-full" :value="old('height_cm', $profile?->height_cm)" /><x-input-error :messages="$errors->get('height_cm')" class="mt-2" /></div>
                    <div><x-input-label for="weight_kg" :value="__('healo.weight_kg')" /><x-text-input id="weight_kg" name="weight_kg" type="number" min="20" max="400" step="0.1" class="mt-1 block w-full" :value="old('weight_kg', $profile?->weight_kg)" /><x-input-error :messages="$errors->get('weight_kg')" class="mt-2" /></div>
                    <div><x-input-label for="birth_date" :value="__('healo.birth_date')" /><x-text-input id="birth_date" name="birth_date" type="date" max="{{ now()->toDateString() }}" class="mt-1 block w-full" :value="old('birth_date', $profile?->birth_date?->toDateString())" /><x-input-error :messages="$errors->get('birth_date')" class="mt-2" /></div>
                    <div><x-input-label for="gender" :value="__('healo.gender')" /><select id="gender" name="gender" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"><option value="">{{ __('healo.choose_optional') }}</option>@foreach($genders as $gender)<option value="{{ $gender->value }}" @selected(old('gender', $profile?->gender?->value) === $gender->value)>{{ __('healo.gender_'.$gender->value) }}</option>@endforeach</select><x-input-error :messages="$errors->get('gender')" class="mt-2" /></div>
                    <div><x-input-label for="activity_level" :value="__('healo.activity_level')" /><select id="activity_level" name="activity_level" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"><option value="">{{ __('healo.choose_optional') }}</option>@foreach($activityLevels as $level)<option value="{{ $level->value }}" @selected(old('activity_level', $profile?->activity_level?->value) === $level->value)>{{ __('healo.activity_'.$level->value) }}</option>@endforeach</select><x-input-error :messages="$errors->get('activity_level')" class="mt-2" /></div>
                    <div><x-input-label for="health_goal" :value="__('healo.health_goal')" /><select id="health_goal" name="health_goal" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"><option value="">{{ __('healo.choose_optional') }}</option>@foreach($healthGoals as $goal)<option value="{{ $goal->value }}" @selected(old('health_goal', $profile?->health_goal?->value) === $goal->value)>{{ __('healo.goal_'.$goal->value) }}</option>@endforeach</select><x-input-error :messages="$errors->get('health_goal')" class="mt-2" /></div>
                </div>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div><x-input-label for="health_conditions" :value="__('healo.health_conditions')" /><textarea id="health_conditions" name="health_conditions" rows="5" class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="{{ __('healo.one_per_line') }}">{{ $conditionsValue }}</textarea><p class="mt-1 text-xs text-slate-500">{{ __('healo.encrypted_field_hint') }}</p><x-input-error :messages="$errors->get('health_conditions')" class="mt-2" /></div>
                    <div><x-input-label for="allergies" :value="__('healo.allergies')" /><textarea id="allergies" name="allergies" rows="5" class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="{{ __('healo.one_per_line') }}">{{ $allergiesValue }}</textarea><p class="mt-1 text-xs text-slate-500">{{ __('healo.encrypted_field_hint') }}</p><x-input-error :messages="$errors->get('allergies')" class="mt-2" /></div>
                </div>

                @unless($profile?->consented_at)
                    <label class="mt-6 flex items-start gap-3 rounded-2xl border border-sky-200 bg-sky-50 p-4"><input type="checkbox" name="consent" value="1" class="mt-1 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" @checked(old('consent'))><span><span class="block text-sm font-semibold text-slate-900">{{ __('healo.health_data_consent') }}</span><span class="mt-1 block text-xs leading-5 text-slate-600">{{ __('healo.health_data_consent_copy') }}</span></span></label><x-input-error :messages="$errors->get('consent')" class="mt-2" />
                @endunless

                <div class="mt-8 flex justify-end"><button type="submit" :disabled="saving" class="healo-button-primary disabled:cursor-wait disabled:opacity-70"><i data-lucide="save" class="size-4"></i><span x-text="saving ? '{{ __('healo.saving') }}' : '{{ __('healo.save_health_profile') }}'"></span></button></div>
            </form>

            <aside class="space-y-4">
                <div class="healo-card p-5"><span class="grid size-11 place-items-center rounded-2xl bg-emerald-50 text-emerald-700"><i data-lucide="shield-check" class="size-5"></i></span><h2 class="mt-4 font-bold text-slate-900">{{ __('healo.private_and_encrypted') }}</h2><p class="mt-2 text-sm leading-6 text-slate-600">{{ __('healo.private_profile_copy') }}</p></div>
                <a href="{{ route('bmi.index') }}" class="block rounded-3xl bg-slate-950 p-5 text-white shadow-soft"><i data-lucide="gauge" class="size-6 text-emerald-300"></i><p class="mt-6 font-bold">{{ __('healo.calculate_bmi') }}</p><p class="mt-1 text-sm text-slate-400">{{ __('healo.use_profile_for_bmi') }}</p></a>
            </aside>
        </div>
    </div>
</x-app-layout>

