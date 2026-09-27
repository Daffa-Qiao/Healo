@php
    $userNav = [
        ['route' => 'dashboard', 'icon' => 'layout-dashboard', 'label' => __('healo.dashboard')],
        ['feature' => 'bmi', 'icon' => 'heart-pulse', 'label' => __('healo.health')],
        ['feature' => 'food-scanner', 'icon' => 'utensils', 'label' => __('healo.food')],
        ['feature' => 'health-journal', 'icon' => 'notebook-pen', 'label' => __('healo.journal')],
    ];
@endphp

<div x-cloak x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-slate-950/40 backdrop-blur-sm lg:hidden"></div>

<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-slate-200 bg-white px-5 py-6 transition-transform duration-200 lg:translate-x-0">
    <div class="flex items-center justify-between">
        <a href="{{ route('dashboard') }}" aria-label="HEALO"><x-application-logo /></a>
        <button @click="sidebarOpen = false" class="grid size-9 place-items-center rounded-xl text-slate-500 hover:bg-slate-100 lg:hidden" aria-label="{{ __('healo.close_navigation') }}"><i data-lucide="x" class="size-5"></i></button>
    </div>
    <p class="mt-2 text-xs text-slate-500">{{ __('healo.tagline') }}</p>

    <nav class="mt-8 flex-1 space-y-1" aria-label="{{ __('healo.main_navigation') }}">
        @if(Auth::user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}" @class(['flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition', 'bg-emerald-50 text-emerald-800' => request()->routeIs('admin.*'), 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => !request()->routeIs('admin.*')])>
                <i data-lucide="shield-check" class="size-5"></i>{{ __('healo.admin_dashboard') }}
            </a>
        @else
            @foreach($userNav as $item)
                @php
                    $href = isset($item['route']) ? route($item['route']) : route('features.show', $item['feature']);
                    $active = isset($item['route']) ? request()->routeIs($item['route']) : request()->routeIs('features.show') && request()->route('feature') === $item['feature'];
                @endphp
                <a href="{{ $href }}" @class(['flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition', 'bg-emerald-50 text-emerald-800' => $active, 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => !$active])>
                    <i data-lucide="{{ $item['icon'] }}" class="size-5"></i>{{ $item['label'] }}
                </a>
            @endforeach

            <p class="px-4 pb-1 pt-6 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">{{ __('healo.explore') }}</p>
            <a href="{{ route('features.show', 'symptom-checker') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50"><i data-lucide="stethoscope" class="size-5"></i>{{ __('healo.symptom_checker') }}</a>
            <a href="{{ route('features.show', 'healthy-menu') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50"><i data-lucide="salad" class="size-5"></i>{{ __('healo.healthy_menu') }}</a>
        @endif
    </nav>

    <div class="space-y-1 border-t border-slate-100 pt-4">
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50"><i data-lucide="settings" class="size-5"></i>{{ __('healo.settings') }}</a>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-rose-50 hover:text-rose-700"><i data-lucide="log-out" class="size-5"></i>{{ __('healo.sign_out') }}</button>
        </form>
    </div>
</aside>

@unless(Auth::user()->isAdmin())
<nav class="fixed inset-x-3 bottom-3 z-40 rounded-3xl border border-slate-200 bg-white/95 px-2 py-2 shadow-2xl shadow-slate-900/15 backdrop-blur lg:hidden" aria-label="{{ __('healo.mobile_navigation') }}">
    <div class="grid grid-cols-5">
        @foreach($userNav as $item)
            @php $href = isset($item['route']) ? route($item['route']) : route('features.show', $item['feature']); @endphp
            <a href="{{ $href }}" class="flex flex-col items-center gap-1 rounded-2xl py-2 text-[11px] font-semibold text-slate-500 hover:bg-emerald-50 hover:text-emerald-700"><i data-lucide="{{ $item['icon'] }}" class="size-5"></i><span>{{ $item['label'] }}</span></a>
        @endforeach
        <a href="{{ route('profile.edit') }}" class="flex flex-col items-center gap-1 rounded-2xl py-2 text-[11px] font-semibold text-slate-500 hover:bg-emerald-50 hover:text-emerald-700"><i data-lucide="user-round" class="size-5"></i><span>{{ __('healo.profile') }}</span></a>
    </div>
</nav>
@endunless
