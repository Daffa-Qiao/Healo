@php
    $userNav = [
        ['route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'layout-dashboard', 'label' => __('healo.home')],
        ['route' => 'health-profile.edit', 'active' => 'health-profile.*', 'icon' => 'user-round', 'label' => __('healo.health_profile')],
        ['route' => 'bmi.index', 'active' => 'bmi.*', 'icon' => 'chart-no-axes-combined', 'label' => __('healo.bmi')],
        ['route' => 'health-journals.index', 'active' => ['health-journals.*', 'health-journal-entries.*'], 'icon' => 'notebook-pen', 'label' => __('healo.journal')],
        ['route' => 'nutrition.index', 'active' => ['nutrition.*', 'meals.*', 'meal-items.*', 'food-analysis.*', 'healthy-menus.*'], 'icon' => 'utensils', 'label' => __('healo.nutrition')],
        ['route' => 'health-assistant.index', 'active' => 'health-assistant.*', 'icon' => 'message-circle-heart', 'label' => __('health_ai.assistant_short')],
        ['route' => 'symptom-checker.create', 'active' => 'symptom-checker.*', 'icon' => 'shield-check', 'label' => __('healo.symptom_checker_short')],
        ['route' => 'health-insights.index', 'active' => 'health-insights.*', 'icon' => 'lightbulb', 'label' => __('healo.insights_short')],
        ['route' => 'health-plan.index', 'active' => 'health-plan.*', 'icon' => 'calendar-check-2', 'label' => __('wellness.nav_title')],
        ['route' => 'health-goals.index', 'active' => 'health-goals.*', 'icon' => 'target', 'label' => __('healo.health_goals_short')],
    ];
    $mobileNav = collect($userNav)->only([0, 3, 4, 5, 8]);
@endphp

<div x-cloak x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-slate-950/35 lg:hidden"></div>

<aside :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', sidebarCollapsed ? 'lg:w-24' : 'lg:w-64']" class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-slate-200 bg-white px-3 py-5 transition-all duration-200 lg:translate-x-0">
    <div :class="sidebarCollapsed ? 'lg:flex-col' : ''" class="flex min-h-12 items-center gap-2 px-2">
        <a href="{{ route('dashboard') }}" class="min-w-0 flex-1" aria-label="HEALO"><span :class="sidebarCollapsed ? 'lg:hidden' : ''"><x-application-logo /></span><span :class="sidebarCollapsed ? 'lg:grid' : 'hidden'" class="size-10 place-items-center rounded-xl bg-emerald-600 text-white"><i data-lucide="heart-pulse" class="size-5"></i></span></a>
        <button @click="sidebarOpen = false" class="grid size-10 place-items-center rounded-xl text-slate-500 hover:bg-slate-100 lg:hidden" aria-label="{{ __('healo.close_navigation') }}"><i data-lucide="x" class="size-5"></i></button>
        <button @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('healo-sidebar', sidebarCollapsed ? 'collapsed' : 'expanded')" class="hidden size-10 place-items-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 lg:grid" aria-label="{{ __('healo.toggle_sidebar') }}"><i :data-lucide="sidebarCollapsed ? 'panel-left-open' : 'panel-left-close'" class="size-4"></i></button>
    </div>
    <p :class="sidebarCollapsed ? 'lg:hidden' : ''" class="px-3 text-xs text-slate-400">{{ __('healo.tagline') }}</p>

    <nav class="mt-6 flex-1 space-y-1 overflow-y-auto" aria-label="{{ __('healo.main_navigation') }}">
        @if(Auth::user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm font-semibold text-slate-600 hover:bg-slate-50"><i data-lucide="shield-check" class="size-5 shrink-0"></i><span :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('healo.admin_dashboard') }}</span></a>
        @else
            @foreach($userNav as $item)
                @php $active = request()->routeIs($item['active']); @endphp
                <a href="{{ route($item['route']) }}" title="{{ $item['label'] }}" @class(['group flex min-h-11 items-center gap-3 rounded-xl border-l-[3px] px-3 text-sm font-semibold transition', 'border-emerald-600 bg-[#EAF8F2] text-[#08744F]' => $active, 'border-transparent text-[#17345C]/80 hover:bg-slate-50 hover:text-[#08744F]' => !$active])>
                    <i data-lucide="{{ $item['icon'] }}" class="size-5 shrink-0"></i><span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate">{{ $item['label'] }}</span>
                </a>
            @endforeach
        @endif
    </nav>

    <div class="border-t border-slate-100 pt-3">
        <a href="{{ route('profile.edit') }}" title="{{ __('healo.settings') }}" class="flex min-h-11 items-center gap-3 rounded-xl border-l-[3px] border-transparent px-3 text-sm font-semibold text-slate-600 hover:bg-slate-50"><i data-lucide="settings" class="size-5 shrink-0"></i><span :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('healo.settings') }}</span></a>
    </div>
</aside>

@unless(Auth::user()->isAdmin())
<nav class="fixed inset-x-3 bottom-3 z-40 rounded-2xl border border-slate-200 bg-white/95 p-1.5 shadow-xl backdrop-blur lg:hidden" aria-label="{{ __('healo.mobile_navigation') }}">
    <div class="grid grid-cols-5">@foreach($mobileNav as $item)<a href="{{ route($item['route']) }}" @class(['flex min-h-12 flex-col items-center justify-center gap-1 rounded-xl text-[10px] font-semibold', 'bg-emerald-50 text-emerald-800' => request()->routeIs($item['active']), 'text-slate-500' => !request()->routeIs($item['active'])])><i data-lucide="{{ $item['icon'] }}" class="size-5"></i><span class="max-w-full truncate px-1">{{ $item['label'] }}</span></a>@endforeach</div>
</nav>
@endunless
