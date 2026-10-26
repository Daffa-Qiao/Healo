<x-app-layout>
    <x-slot name="title">{{ __('notifications.preferences') }}</x-slot>
    <x-slot name="header"><h1 class="text-base font-bold text-[#17345C] sm:text-lg">{{ __('notifications.preferences') }}</h1><p class="hidden text-xs text-slate-500 sm:block">{{ __('notifications.preferences_subtitle') }}</p></x-slot>

    @php $toggles = [
        'health_plan_enabled' => ['calendar-check-2', 'health_plan'], 'meal_reminder_enabled' => ['utensils', 'meal_reminder'],
        'journal_reminder_enabled' => ['notebook-pen', 'journal_reminder'], 'wellness_enabled' => ['heart-pulse', 'wellness'],
        'health_goal_enabled' => ['target', 'health_goal'], 'health_insight_enabled' => ['lightbulb', 'health_insight'],
        'system_enabled' => ['bell', 'system'],
    ]; @endphp
    <div class="mx-auto max-w-3xl" x-data="{ enabled: {{ old('notifications_enabled', $preference->notifications_enabled) ? 'true' : 'false' }} }">
        <form method="POST" action="{{ route('notification-preferences.update') }}" class="space-y-5">
            @csrf @method('PUT')
            @if(session('status') === 'notification-preferences-saved')<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ __('notifications.saved') }}</div>@endif
            @if($errors->any())<div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>@endif

            <section class="healo-card p-5 sm:p-6"><div class="flex items-center justify-between gap-4"><div><h2 class="font-bold text-[#17345C]">{{ __('notifications.general') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('notifications.general_copy') }}</p></div><label class="relative inline-flex cursor-pointer items-center"><input type="hidden" name="notifications_enabled" value="0"><input x-model="enabled" type="checkbox" name="notifications_enabled" value="1" class="peer sr-only"><span class="h-7 w-12 rounded-full bg-slate-200 transition after:absolute after:left-1 after:top-1 after:size-5 after:rounded-full after:bg-white after:transition peer-checked:bg-emerald-600 peer-checked:after:translate-x-5"></span></label></div></section>

            <section class="healo-card divide-y divide-slate-100" :class="enabled ? '' : 'opacity-50'">
                <div class="p-5 sm:p-6"><h2 class="font-bold text-[#17345C]">{{ __('notifications.daily_wellness') }}</h2></div>
                @foreach($toggles as $field => [$icon, $label])
                    @if($loop->index === 4)<div class="bg-slate-50 px-5 py-3 text-xs font-bold uppercase tracking-wider text-slate-500 sm:px-6">{{ __('notifications.health_progress') }}</div>@endif
                    @if($loop->index === 6)<div class="bg-slate-50 px-5 py-3 text-xs font-bold uppercase tracking-wider text-slate-500 sm:px-6">{{ __('notifications.system_group') }}</div>@endif
                    <label class="flex items-center justify-between gap-4 px-5 py-4 sm:px-6"><span class="flex items-center gap-3 text-sm font-semibold text-[#17345C]"><span class="grid size-9 place-items-center rounded-full bg-emerald-50 text-emerald-600"><i data-lucide="{{ $icon }}" class="size-4"></i></span>{{ __('notifications.'.$label) }}</span><span class="relative inline-flex cursor-pointer items-center"><input type="hidden" name="{{ $field }}" value="0"><input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $preference->{$field})) class="peer sr-only"><span class="h-6 w-11 rounded-full bg-slate-200 transition after:absolute after:left-1 after:top-1 after:size-4 after:rounded-full after:bg-white after:transition peer-checked:bg-emerald-600 peer-checked:after:translate-x-5"></span></span></label>
                @endforeach
            </section>

            <section class="healo-card p-5 sm:p-6" :class="enabled ? '' : 'opacity-50'"><h2 class="font-bold text-[#17345C]">{{ __('notifications.schedule') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('notifications.schedule_copy') }}</p><div class="mt-5 grid gap-4 sm:grid-cols-3"><label class="text-sm font-semibold text-slate-700">{{ __('notifications.quiet_start') }}<input type="time" name="quiet_hours_start" value="{{ old('quiet_hours_start', $preference->quiet_hours_start ? substr($preference->quiet_hours_start, 0, 5) : '') }}" class="mt-2 block w-full rounded-xl border-slate-200"></label><label class="text-sm font-semibold text-slate-700">{{ __('notifications.quiet_end') }}<input type="time" name="quiet_hours_end" value="{{ old('quiet_hours_end', $preference->quiet_hours_end ? substr($preference->quiet_hours_end, 0, 5) : '') }}" class="mt-2 block w-full rounded-xl border-slate-200"></label><label class="text-sm font-semibold text-slate-700">{{ __('notifications.reminder_time') }}<input type="time" required name="reminder_time" value="{{ old('reminder_time', substr($preference->reminder_time, 0, 5)) }}" class="mt-2 block w-full rounded-xl border-slate-200"></label></div></section>

            <div class="flex justify-end gap-3"><a href="{{ route('notifications.index') }}" class="healo-button-secondary">{{ __('healo.cancel') }}</a><button class="healo-button-primary"><i data-lucide="save" class="size-4"></i>{{ __('notifications.save') }}</button></div>
        </form>
    </div>
</x-app-layout>
