<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HEALO · {{ __('healo.tagline') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white font-sans antialiased">
    <div class="relative min-h-screen overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-[42rem] bg-gradient-to-b from-emerald-50 via-sky-50/30 to-white"></div>
        <div class="absolute left-1/2 top-16 size-[34rem] -translate-x-1/2 rounded-full bg-emerald-200/25 blur-3xl"></div>

        <header class="relative mx-auto flex max-w-7xl items-center justify-between px-5 py-6 sm:px-8">
            <x-application-logo />
            <nav class="flex items-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="healo-button-primary">{{ __('healo.dashboard') }}</a>
                @else
                    <a href="{{ route('login') }}" class="hidden rounded-xl px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-white sm:inline-flex">{{ __('healo.sign_in') }}</a>
                    <a href="{{ route('register') }}" class="healo-button-primary">{{ __('healo.start_now') }}<i data-lucide="arrow-right" class="size-4"></i></a>
                @endauth
            </nav>
        </header>

        <main class="relative">
            <section class="mx-auto grid max-w-7xl items-center gap-14 px-5 pb-20 pt-14 sm:px-8 lg:grid-cols-[1.05fr_0.95fr] lg:pb-28 lg:pt-20">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white/80 px-4 py-2 text-xs font-bold text-emerald-800 shadow-sm"><i data-lucide="sparkles" class="size-4"></i>{{ __('healo.independent') }}</span>
                    <p class="healo-kicker mt-8">{{ __('healo.welcome_kicker') }}</p>
                    <h1 class="mt-4 max-w-3xl text-5xl font-black leading-[1.04] tracking-[-0.055em] text-slate-950 sm:text-6xl lg:text-7xl">{{ __('healo.welcome_title') }}</h1>
                    <p class="mt-6 max-w-xl text-lg leading-8 text-slate-600">{{ __('healo.welcome_copy') }}</p>
                    <div class="mt-8 flex flex-wrap gap-3"><a href="{{ route('register') }}" class="healo-button-primary">{{ __('healo.start_now') }}<i data-lucide="arrow-right" class="size-4"></i></a><a href="{{ route('login') }}" class="healo-button-secondary">{{ __('healo.sign_in') }}</a></div>
                </div>

                <div class="relative mx-auto w-full max-w-lg">
                    <div class="absolute -inset-6 rounded-[3rem] bg-gradient-to-br from-emerald-200/50 to-sky-200/40 blur-2xl"></div>
                    <div class="relative rounded-[2.25rem] border border-white bg-white/90 p-4 shadow-soft backdrop-blur sm:p-6">
                        <div class="rounded-[1.75rem] bg-slate-950 p-6 text-white">
                            <div class="flex items-center justify-between"><div><p class="text-xs text-slate-400">{{ __('healo.today_health') }}</p><p class="mt-1 font-bold">{{ __('healo.hello_name', ['name' => 'Rani']) }}</p></div><span class="grid size-10 place-items-center rounded-2xl bg-emerald-400 text-emerald-950"><i data-lucide="heart-pulse" class="size-5"></i></span></div>
                            <div class="mt-8 rounded-2xl bg-white/10 p-4"><div class="flex justify-between text-sm"><span class="text-slate-300">{{ __('healo.calories') }}</span><strong>1.350 / 2.000</strong></div><div class="mt-3 h-2 rounded-full bg-white/10"><div class="h-2 w-2/3 rounded-full bg-emerald-400"></div></div></div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3"><div class="rounded-2xl bg-emerald-50 p-4"><i data-lucide="scan-line" class="size-5 text-emerald-700"></i><p class="mt-5 text-sm font-bold text-slate-900">{{ __('healo.scan_food') }}</p></div><div class="rounded-2xl bg-sky-50 p-4"><i data-lucide="stethoscope" class="size-5 text-sky-700"></i><p class="mt-5 text-sm font-bold text-slate-900">{{ __('healo.check_symptoms') }}</p></div></div>
                    </div>
                </div>
            </section>

            <section class="mx-auto max-w-7xl px-5 pb-24 sm:px-8">
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach([
                        ['scan-line', __('healo.food_scanner'), __('healo.feature_food_copy')],
                        ['activity', __('healo.health_journal'), __('healo.feature_health_copy')],
                        ['brain-circuit', __('healo.symptom_checker'), __('healo.feature_ai_copy')],
                    ] as [$icon, $title, $copy])
                        <article class="healo-card p-6"><span class="grid size-12 place-items-center rounded-2xl bg-emerald-50 text-emerald-700"><i data-lucide="{{ $icon }}" class="size-6"></i></span><h2 class="mt-5 text-lg font-bold text-slate-900">{{ $title }}</h2><p class="mt-2 text-sm leading-6 text-slate-600">{{ $copy }}</p></article>
                    @endforeach
                </div>
            </section>
        </main>
    </div>
</body>
</html>
