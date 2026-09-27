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
    <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-slate-50">
        @include('layouts.navigation')

        <div class="lg:pl-72">
            <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 px-4 py-3 backdrop-blur sm:px-6 lg:px-8">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <button @click="sidebarOpen = true" class="grid size-10 place-items-center rounded-xl text-slate-600 hover:bg-slate-100 lg:hidden" aria-label="{{ __('healo.open_navigation') }}">
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

                    <div class="flex items-center gap-2">
                        <a href="{{ route('features.show', 'notifications') }}" class="grid size-10 place-items-center rounded-xl text-slate-500 hover:bg-slate-100" aria-label="{{ __('healo.notifications') }}">
                            <i data-lucide="bell" class="size-5"></i>
                        </a>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-2xl p-1.5 pe-3 hover:bg-slate-100">
                            <span class="grid size-9 place-items-center rounded-xl bg-emerald-100 text-sm font-bold text-emerald-800">{{ str(Auth::user()->name)->substr(0, 1)->upper() }}</span>
                            <span class="hidden text-left sm:block">
                                <span class="block max-w-32 truncate text-sm font-semibold text-slate-800">{{ Auth::user()->name }}</span>
                                <span class="block text-xs text-slate-500">{{ Auth::user()->isAdmin() ? 'Admin' : __('healo.user') }}</span>
                            </span>
                        </a>
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

