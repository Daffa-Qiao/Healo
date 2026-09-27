<x-app-layout>
    <x-slot name="title">{{ __('healo.admin_dashboard') }}</x-slot>
    <x-slot name="header"><p class="text-xs font-medium text-slate-500">HEALO Admin</p><h1 class="text-lg font-bold text-slate-900">{{ __('healo.system_overview') }}</h1></x-slot>

    <div class="mx-auto max-w-7xl space-y-6">
        <div><p class="healo-kicker">{{ __('healo.administration') }}</p><h2 class="mt-2 text-3xl font-black tracking-tight text-slate-950">{{ __('healo.admin_welcome') }}</h2><p class="mt-2 text-slate-600">{{ __('healo.admin_privacy_note') }}</p></div>
        <div class="grid gap-4 sm:grid-cols-3">
            @foreach([
                ['label' => __('healo.total_users'), 'value' => $totalUsers, 'icon' => 'users'],
                ['label' => __('healo.new_users_30'), 'value' => $newUsers, 'icon' => 'user-plus'],
                ['label' => __('healo.audit_events_24'), 'value' => $auditEvents, 'icon' => 'scroll-text'],
            ] as $metric)
                <div class="healo-card p-6"><span class="grid size-11 place-items-center rounded-2xl bg-emerald-50 text-emerald-700"><i data-lucide="{{ $metric['icon'] }}" class="size-5"></i></span><p class="mt-5 text-sm text-slate-500">{{ $metric['label'] }}</p><p class="mt-1 text-3xl font-black text-slate-950">{{ number_format($metric['value']) }}</p></div>
            @endforeach
        </div>
        <div class="healo-card p-6"><div class="flex items-start gap-4"><span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-sky-50 text-sky-700"><i data-lucide="lock-keyhole" class="size-5"></i></span><div><h3 class="font-bold text-slate-900">{{ __('healo.health_data_boundary') }}</h3><p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">{{ __('healo.admin_boundary_copy') }}</p></div></div></div>
    </div>
</x-app-layout>
