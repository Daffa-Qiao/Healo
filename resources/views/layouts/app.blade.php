@php
    $headerNotificationPayload = $headerNotifications->map(fn ($notification) => [
        'id' => $notification->id,
        'type' => $notification->type->value,
        'title' => $notification->title,
        'message' => $notification->body,
        'action_url' => $notification->action_url,
        'read_at' => $notification->read_at?->toISOString(),
        'created_label' => $notification->created_at->diffForHumans(),
    ])->values();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' · ' : '' }}{{ config('app.name', 'HEALO') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div x-data="window.Healo.notificationCenter({{ Illuminate\Support\Js::from($headerNotificationPayload) }}, {{ $headerUnreadCount }}, {{ Auth::id() }})" class="min-h-screen bg-[#F8FAFC]">
        @include('layouts.navigation')

        <div :class="sidebarCollapsed ? 'lg:pl-24' : 'lg:pl-64'" class="transition-[padding] duration-200">
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white px-4 py-3 sm:px-6 lg:px-8">
                <div class="mx-auto flex max-w-[1400px] items-center justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <button @click="sidebarOpen = true" class="grid size-11 place-items-center rounded-xl text-slate-600 hover:bg-slate-100 lg:hidden" aria-label="{{ __('healo.open_navigation') }}">
                            <i data-lucide="menu" class="size-5"></i>
                        </button>
                        <div class="min-w-0">
                            @isset($header)
                                {{ $header }}
                            @else
                                <p class="truncate text-sm font-semibold text-slate-900">{{ __('healo.dashboard') }}</p>
                            @endisset
                        </div>
                    </div>

                    <div class="flex items-center gap-1 sm:gap-2">
                        <div class="relative" @click.outside="notificationOpen = false">
                            <button @click="notificationOpen = !notificationOpen" class="relative grid size-11 place-items-center rounded-xl text-slate-500 hover:bg-slate-100" aria-label="{{ __('healo.notifications') }}" :aria-expanded="notificationOpen"><i data-lucide="bell" class="size-5"></i><span x-cloak x-show="unreadCount > 0" x-text="unreadCount > 99 ? '99+' : unreadCount" class="absolute right-0.5 top-0.5 min-w-4 rounded-full bg-rose-500 px-1 text-center text-[9px] font-black leading-4 text-white"></span></button>
                            <div x-cloak x-show="notificationOpen" x-transition class="absolute right-0 mt-2 w-[min(22rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
                                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3"><div><p class="text-sm font-bold text-[#17345C]">{{ __('notifications.title') }}</p><p class="mt-0.5 text-[11px] text-slate-500" x-text="unreadCount ? `${unreadCount} {{ __('notifications.unread') }}` : '{{ __('notifications.all_caught_up') }}'"></p></div><a href="{{ route('notification-preferences.edit') }}" class="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-50 hover:text-emerald-700" aria-label="{{ __('notifications.settings') }}"><i data-lucide="settings" class="size-4"></i></a></div>
                                <div class="max-h-80 overflow-y-auto">
                                    <template x-for="notification in notifications" :key="notification.id"><form method="POST" :action="`/notifications/${notification.id}/read`" class="border-b border-slate-100 last:border-0">@csrf<button class="flex w-full gap-3 px-4 py-3 text-left transition hover:bg-slate-50"><span class="relative mt-1 grid size-9 shrink-0 place-items-center rounded-full bg-emerald-50 text-emerald-600"><i data-lucide="bell" class="size-4"></i><span x-show="!notification.read_at" class="absolute -right-0.5 -top-0.5 size-2.5 rounded-full border-2 border-white bg-emerald-500"></span></span><span class="min-w-0"><span class="block truncate text-xs font-bold text-[#17345C]" x-text="notification.title"></span><span class="mt-1 line-clamp-2 block text-xs leading-5 text-slate-500" x-text="notification.message"></span><span class="mt-1 block text-[10px] text-slate-400" x-text="notification.created_label || '{{ __('notifications.just_now') }}'"></span></span></button></form></template>
                                    <div x-show="notifications.length === 0" class="px-5 py-8 text-center"><span class="mx-auto grid size-10 place-items-center rounded-full bg-emerald-50 text-emerald-600"><i data-lucide="bell" class="size-5"></i></span><p class="mt-3 text-sm font-semibold text-[#17345C]">{{ __('notifications.empty_title') }}</p></div>
                                </div>
                                <div class="grid grid-cols-2 border-t border-slate-100 bg-slate-50/70 p-2"><form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="min-h-9 w-full rounded-lg text-[11px] font-bold text-slate-600 hover:bg-white" :disabled="unreadCount === 0">{{ __('notifications.mark_all_read') }}</button></form><a href="{{ route('notifications.index') }}" class="flex min-h-9 items-center justify-center gap-1 rounded-lg text-[11px] font-bold text-emerald-700 hover:bg-white">{{ __('notifications.view_all') }}<i data-lucide="arrow-right" class="size-3"></i></a></div>
                            </div>
                        </div>
                        <div class="relative" @click.outside="languageOpen = false">
                            <button @click="languageOpen = !languageOpen" class="flex min-h-11 items-center gap-2 rounded-xl px-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100" aria-label="{{ __('healo.choose_language') }}" :aria-expanded="languageOpen"><i data-lucide="languages" class="size-4"></i><span class="hidden sm:inline">{{ strtoupper(app()->getLocale()) }}</span><i data-lucide="chevron-down" class="size-3"></i></button>
                            <div x-cloak x-show="languageOpen" x-transition class="absolute right-0 mt-2 w-40 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">@foreach(['id' => 'Indonesia', 'en' => 'English'] as $locale => $label)<form method="POST" action="{{ route('locale.update') }}">@csrf @method('PATCH')<input type="hidden" name="locale" value="{{ $locale }}"><button class="flex min-h-10 w-full items-center justify-between rounded-xl px-3 text-sm font-semibold text-slate-700 hover:bg-emerald-50">{{ $label }}@if(app()->getLocale() === $locale)<i data-lucide="check" class="size-4 text-emerald-600"></i>@endif</button></form>@endforeach</div>
                        </div>
                        <div class="relative" @click.outside="userOpen = false">
                            <button @click="userOpen = !userOpen" class="flex min-h-11 items-center gap-3 rounded-xl p-1.5 pe-2 hover:bg-slate-100" :aria-expanded="userOpen" aria-label="{{ __('healo.account_menu') }}"><span class="grid size-9 place-items-center rounded-full bg-slate-100 text-sm font-bold text-[#17345C]">{{ str(Auth::user()->name)->substr(0, 1)->upper() }}</span><span class="hidden text-left md:block"><span class="block max-w-36 truncate text-sm font-semibold text-[#17345C]">{{ Auth::user()->name }}</span><span class="block text-[11px] text-slate-500">{{ Auth::user()->isAdmin() ? 'Admin' : __('healo.user') }}</span></span><i data-lucide="chevron-down" class="hidden size-4 text-slate-400 md:block"></i></button>
                            <div x-cloak x-show="userOpen" x-transition class="absolute right-0 mt-2 w-52 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl"><a href="{{ route('profile.edit') }}" class="flex min-h-10 items-center gap-2 rounded-xl px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"><i data-lucide="settings" class="size-4"></i>{{ __('healo.settings') }}</a><form method="POST" action="{{ route('logout') }}">@csrf<button class="flex min-h-10 w-full items-center gap-2 rounded-xl px-3 text-sm font-semibold text-rose-700 hover:bg-rose-50"><i data-lucide="log-out" class="size-4"></i>{{ __('healo.sign_out') }}</button></form></div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="px-4 pb-28 pt-6 sm:px-6 lg:px-8 lg:pb-10">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>

