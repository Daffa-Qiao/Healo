<x-app-layout>
    <x-slot name="title">{{ __('notifications.title') }}</x-slot>
    <x-slot name="header"><h1 class="text-base font-bold text-[#17345C] sm:text-lg">{{ __('notifications.title') }}</h1><p class="hidden text-xs text-slate-500 sm:block">{{ __('notifications.subtitle') }}</p></x-slot>

    <div class="mx-auto max-w-4xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div><p class="healo-kicker">{{ __('notifications.center') }}</p><h2 class="mt-2 text-2xl font-black text-[#17345C]">{{ __('notifications.your_notifications') }}</h2><p class="mt-1 text-sm text-slate-500">{{ trans_choice('notifications.unread_count', $unreadCount, ['count' => $unreadCount]) }}</p></div>
            <div class="flex gap-2">
                @if($unreadCount > 0)<form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="healo-button-secondary !min-h-10 !px-4 !py-2">{{ __('notifications.mark_all_read') }}</button></form>@endif
                <a href="{{ route('notification-preferences.edit') }}" class="healo-button-secondary !min-h-10 !px-4 !py-2"><i data-lucide="settings" class="size-4"></i>{{ __('notifications.settings') }}</a>
            </div>
        </div>

        <nav class="mt-7 flex gap-2 overflow-x-auto pb-2" aria-label="{{ __('notifications.filters') }}">
            @foreach(['all', 'unread', 'health', 'wellness', 'system'] as $filter)
                <a href="{{ route('notifications.index', $filter === 'all' ? [] : ['filter' => $filter]) }}" @class(['shrink-0 rounded-full border px-4 py-2 text-xs font-bold transition', 'border-emerald-600 bg-emerald-600 text-white' => $activeFilter === $filter, 'border-slate-200 bg-white text-slate-600 hover:border-emerald-200 hover:text-emerald-700' => $activeFilter !== $filter])>{{ __('notifications.filter_'.$filter) }}</a>
            @endforeach
        </nav>

        <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @forelse($notifications as $notification)
                @php
                    $icon = match($notification->type) {
                        \App\Enums\NotificationType::HealthPlan => 'calendar-check-2',
                        \App\Enums\NotificationType::MealReminder => 'utensils',
                        \App\Enums\NotificationType::JournalReminder => 'notebook-pen',
                        \App\Enums\NotificationType::HealthGoal => 'target',
                        \App\Enums\NotificationType::HealthInsight => 'lightbulb',
                        \App\Enums\NotificationType::Wellness => 'heart-pulse',
                        default => 'bell',
                    };
                @endphp
                <article @class(['relative flex gap-4 border-b border-slate-100 p-5 last:border-0 sm:p-6', 'bg-emerald-50/35' => !$notification->read_at])>
                    @if(!$notification->read_at)<span class="absolute left-2 top-8 size-2 rounded-full bg-emerald-500" aria-label="{{ __('notifications.unread') }}"></span>@endif
                    <span class="grid size-11 shrink-0 place-items-center rounded-full bg-[#EAF8F2] text-[#159A70]"><i data-lucide="{{ $icon }}" class="size-5"></i></span>
                    <div class="min-w-0 flex-1"><div class="flex flex-wrap items-start justify-between gap-2"><h3 class="font-bold text-[#17345C]">{{ $notification->title }}</h3><time class="text-xs text-slate-400" datetime="{{ $notification->created_at->toISOString() }}">{{ $notification->created_at->diffForHumans() }}</time></div><p class="mt-1 text-sm leading-6 text-slate-600">{{ $notification->body }}</p>@if(!$notification->read_at || $notification->action_url)<form method="POST" action="{{ route('notifications.read', $notification) }}" class="mt-3">@csrf<button class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700">{{ $notification->action_url ? __('notifications.open') : __('notifications.mark_read') }}<i data-lucide="arrow-right" class="size-3"></i></button></form>@endif</div>
                </article>
            @empty
                <div class="px-6 py-16 text-center"><span class="mx-auto grid size-14 place-items-center rounded-full bg-emerald-50 text-emerald-600"><i data-lucide="bell" class="size-6"></i></span><h3 class="mt-4 font-bold text-[#17345C]">{{ $activeFilter === 'unread' ? __('notifications.all_caught_up') : __('notifications.empty_title') }}</h3><p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">{{ $activeFilter === 'unread' ? __('notifications.all_caught_up_copy') : __('notifications.empty_copy') }}</p></div>
            @endforelse
        </div>
        <div class="mt-6">{{ $notifications->withQueryString()->links() }}</div>
    </div>
</x-app-layout>
