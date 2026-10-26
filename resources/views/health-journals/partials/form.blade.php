@php
    $isEditing = isset($journal);
    $complaint = $isEditing ? $journal->entryContent('complaint') : null;
    $notes = $isEditing ? $journal->entryContent('note') : null;
    $symptoms = $isEditing ? implode("\n", $journal->symptomList()) : '';
    $symptomsValue = old('symptoms', $symptoms);
    $symptomsValue = is_array($symptomsValue) ? implode("\n", $symptomsValue) : $symptomsValue;
@endphp

<form method="POST" action="{{ $isEditing ? route('health-journals.update', $journal) : route('health-journals.store') }}" x-data="{ saving: false }" @submit="saving = true" class="healo-card p-5 sm:p-8">
    @csrf @if($isEditing) @method('PUT') @endif
    <div class="grid gap-5 sm:grid-cols-[1fr_13rem]">
        <div><x-input-label for="title" :value="__('healo.journal_title')" /><x-text-input id="title" name="title" type="text" maxlength="160" required class="mt-1 block w-full" :value="old('title', $journal->title ?? '')" placeholder="{{ __('healo.journal_title_placeholder') }}" /><x-input-error :messages="$errors->get('title')" class="mt-2" /></div>
        <div><x-input-label for="journal_date" :value="__('healo.date')" /><x-text-input id="journal_date" name="journal_date" type="date" max="{{ now()->toDateString() }}" required class="mt-1 block w-full" :value="old('journal_date', isset($journal) ? $journal->journal_date->toDateString() : now()->toDateString())" /><x-input-error :messages="$errors->get('journal_date')" class="mt-2" /></div>
    </div>

    <div class="mt-5"><x-input-label for="mood" :value="__('healo.mood')" /><select id="mood" name="mood" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"><option value="">{{ __('healo.choose_optional') }}</option>@foreach($moods as $mood)<option value="{{ $mood->value }}" @selected(old('mood', isset($journal) ? $journal->mood?->value : null) === $mood->value)>{{ __('healo.mood_'.$mood->value) }}</option>@endforeach</select><x-input-error :messages="$errors->get('mood')" class="mt-2" /></div>

    <section class="mt-6 rounded-3xl border border-emerald-100 bg-emerald-50/50 p-4 sm:p-5" aria-labelledby="daily-metrics-heading">
        <div class="flex items-start gap-3">
            <span class="grid size-10 shrink-0 place-items-center rounded-2xl bg-white text-emerald-700 shadow-sm"><i data-lucide="activity" class="size-5"></i></span>
            <div><h2 id="daily-metrics-heading" class="font-bold text-slate-900">{{ __('journal.daily_metrics') }}</h2><p class="mt-1 text-sm leading-5 text-slate-500">{{ __('journal.daily_metrics_copy') }}</p></div>
        </div>
        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div><x-input-label for="weight_kg" :value="__('journal.weight_kg')" /><x-text-input id="weight_kg" name="weight_kg" type="number" min="10" max="350" step="0.1" inputmode="decimal" class="mt-1 block w-full" :value="old('weight_kg', $journal->weight_kg ?? '')" placeholder="70.5" /><x-input-error :messages="$errors->get('weight_kg')" class="mt-2" /></div>
            <div><x-input-label for="systolic" :value="__('journal.systolic_mmHg')" /><x-text-input id="systolic" name="systolic" type="number" min="60" max="260" step="1" inputmode="numeric" class="mt-1 block w-full" :value="old('systolic', $journal->systolic ?? '')" placeholder="120" /><x-input-error :messages="$errors->get('systolic')" class="mt-2" /></div>
            <div><x-input-label for="diastolic" :value="__('journal.diastolic_mmHg')" /><x-text-input id="diastolic" name="diastolic" type="number" min="30" max="160" step="1" inputmode="numeric" class="mt-1 block w-full" :value="old('diastolic', $journal->diastolic ?? '')" placeholder="80" /><x-input-error :messages="$errors->get('diastolic')" class="mt-2" /></div>
            <div><x-input-label for="sleep_hours" :value="__('journal.sleep_hours_field')" /><x-text-input id="sleep_hours" name="sleep_hours" type="number" min="0" max="24" step="0.1" inputmode="decimal" class="mt-1 block w-full" :value="old('sleep_hours', $journal->sleep_hours ?? '')" placeholder="8" /><x-input-error :messages="$errors->get('sleep_hours')" class="mt-2" /></div>
            <div><x-input-label for="water_liters" :value="__('journal.water_liters_field')" /><x-text-input id="water_liters" name="water_liters" type="number" min="0" max="15" step="0.1" inputmode="decimal" class="mt-1 block w-full" :value="old('water_liters', $journal->water_liters ?? '')" placeholder="2" /><x-input-error :messages="$errors->get('water_liters')" class="mt-2" /></div>
            <div><x-input-label for="exercise_minutes" :value="__('journal.exercise_minutes_field')" /><x-text-input id="exercise_minutes" name="exercise_minutes" type="number" min="0" max="1440" step="1" inputmode="numeric" class="mt-1 block w-full" :value="old('exercise_minutes', $journal->exercise_minutes ?? '')" placeholder="30" /><x-input-error :messages="$errors->get('exercise_minutes')" class="mt-2" /></div>
        </div>
    </section>

    <div class="mt-6 grid gap-5">
        <div class="rounded-2xl border border-amber-200 bg-amber-50/60 p-4"><div class="flex items-center gap-2"><i data-lucide="message-circle-warning" class="size-5 text-amber-700"></i><x-input-label for="complaint" :value="__('healo.complaint')" /></div><textarea id="complaint" name="complaint" rows="3" maxlength="3000" class="mt-3 block w-full rounded-xl border-amber-200 bg-white shadow-sm focus:border-amber-500 focus:ring-amber-500" placeholder="{{ __('healo.complaint_placeholder') }}">{{ old('complaint', $complaint) }}</textarea><x-input-error :messages="$errors->get('complaint')" class="mt-2" /></div>
        <div class="rounded-2xl border border-rose-200 bg-rose-50/60 p-4"><div class="flex items-center gap-2"><i data-lucide="activity" class="size-5 text-rose-700"></i><x-input-label for="symptoms" :value="__('healo.symptoms')" /></div><textarea id="symptoms" name="symptoms" rows="3" class="mt-3 block w-full rounded-xl border-rose-200 bg-white shadow-sm focus:border-rose-500 focus:ring-rose-500" placeholder="{{ __('healo.one_per_line') }}">{{ $symptomsValue }}</textarea><p class="mt-1 text-xs text-slate-500">{{ __('healo.symptoms_helper') }}</p><x-input-error :messages="$errors->get('symptoms')" class="mt-2" /></div>
        <div class="rounded-2xl border border-sky-200 bg-sky-50/60 p-4"><div class="flex items-center gap-2"><i data-lucide="notebook-pen" class="size-5 text-sky-700"></i><x-input-label for="notes" :value="__('healo.daily_notes')" /></div><textarea id="notes" name="notes" rows="5" maxlength="5000" class="mt-3 block w-full rounded-xl border-sky-200 bg-white shadow-sm focus:border-sky-500 focus:ring-sky-500" placeholder="{{ __('healo.notes_placeholder') }}">{{ old('notes', $notes) }}</textarea><x-input-error :messages="$errors->get('notes')" class="mt-2" /></div>
    </div>

    <p class="mt-5 flex items-center gap-2 text-xs text-slate-500"><i data-lucide="lock-keyhole" class="size-4"></i>{{ __('healo.journal_encrypted_hint') }}</p>
    <div class="mt-7 flex flex-wrap justify-end gap-3"><a href="{{ $isEditing ? route('health-journals.show', $journal) : route('health-journals.index') }}" class="healo-button-secondary">{{ __('healo.cancel') }}</a><button class="healo-button-primary" :disabled="saving"><i data-lucide="save" class="size-4"></i><span x-text="saving ? '{{ __('healo.saving') }}' : '{{ $isEditing ? __('healo.update_journal') : __('healo.save_journal') }}'"></span></button></div>
</form>

