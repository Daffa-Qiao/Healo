<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'HEALO') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-slate-900 antialiased">
    <main class="grid min-h-screen bg-white lg:grid-cols-[0.9fr_1.1fr]">
        <section class="flex items-center justify-center px-5 py-10 sm:px-10 lg:px-16">
            <div class="w-full max-w-md">
                <div class="flex items-center justify-between">
                    <a href="/"><x-application-logo /></a>
                    <form method="POST" action="{{ route('locale.update') }}">@csrf @method('PATCH')
                        <input type="hidden" name="locale" value="{{ app()->getLocale() === 'id' ? 'en' : 'id' }}">
                        <button class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50">{{ app()->getLocale() === 'id' ? 'EN' : 'ID' }}</button>
                    </form>
                </div>
                <div class="mt-10">{{ $slot }}</div>
            </div>
        </section>

        <aside class="relative hidden overflow-hidden bg-slate-950 p-12 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="absolute -right-24 -top-24 size-96 rounded-full bg-emerald-400/20 blur-3xl"></div>
            <div class="absolute -bottom-32 left-12 size-96 rounded-full bg-sky-400/10 blur-3xl"></div>
            <div class="relative"><span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold text-emerald-200"><i data-lucide="shield-check" class="size-4"></i>{{ __('healo.independent') }}</span></div>
            <div class="relative max-w-xl">
                <p class="text-sm font-bold uppercase tracking-[0.22em] text-emerald-300">{{ __('healo.welcome_kicker') }}</p>
                <p class="mt-5 text-4xl font-black leading-tight tracking-tight xl:text-5xl">{{ __('healo.welcome_title') }}</p>
                <p class="mt-5 max-w-lg text-base leading-7 text-slate-300">{{ __('healo.welcome_copy') }}</p>
            </div>
            <div class="relative rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur"><div class="flex gap-3"><i data-lucide="info" class="mt-0.5 size-5 shrink-0 text-sky-300"></i><p class="text-sm leading-6 text-slate-300">{{ __('healo.educational_disclaimer') }}</p></div></div>
        </aside>
    </main>
</body>
</html>

