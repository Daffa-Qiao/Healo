<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ __('landing.meta_description') }}">
    <title>HEALO · {{ __('landing.meta_title') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white font-sans text-[#17345C] antialiased">
    <div x-data="{ mobileOpen: false, languageOpen: false }" class="min-h-screen overflow-hidden">
        <header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/95 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-5 sm:px-8">
                <a href="{{ url('/') }}" aria-label="HEALO — {{ __('landing.home') }}"><x-application-logo /></a>

                <nav class="hidden h-full items-center gap-9 lg:flex" aria-label="{{ __('landing.main_navigation') }}">
                    <a href="#home" class="flex h-full items-center border-b-2 border-emerald-600 text-sm font-bold text-[#08744F]">{{ __('landing.home') }}</a>
                    <a href="#features" class="flex h-full items-center border-b-2 border-transparent text-sm font-medium text-[#496687] transition hover:text-emerald-700">{{ __('landing.features') }}</a>
                    <a href="#about" class="flex h-full items-center border-b-2 border-transparent text-sm font-medium text-[#496687] transition hover:text-emerald-700">{{ __('landing.about') }}</a>
                    <a href="#help" class="flex h-full items-center border-b-2 border-transparent text-sm font-medium text-[#496687] transition hover:text-emerald-700">{{ __('landing.help') }}</a>
                </nav>

                <div class="hidden items-center gap-3 sm:flex">
                    <div class="relative" @click.outside="languageOpen = false">
                        <button @click="languageOpen = !languageOpen" type="button" class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-slate-200 px-3 text-xs font-bold text-[#496687] transition hover:border-emerald-200 hover:bg-emerald-50" :aria-expanded="languageOpen">
                            <span aria-hidden="true">{{ app()->getLocale() === 'id' ? '🇮🇩' : '🇬🇧' }}</span>{{ strtoupper(app()->getLocale()) }}<i data-lucide="chevron-down" class="size-3.5"></i>
                        </button>
                        <div x-cloak x-show="languageOpen" x-transition class="absolute right-0 mt-2 w-40 rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
                            @foreach(['id' => 'Indonesia', 'en' => 'English'] as $locale => $label)
                                <form method="POST" action="{{ route('locale.update') }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="locale" value="{{ $locale }}">
                                    <button class="flex min-h-9 w-full items-center justify-between rounded-lg px-3 text-sm font-semibold text-slate-700 hover:bg-emerald-50">{{ $label }}@if(app()->getLocale() === $locale)<i data-lucide="check" class="size-4 text-emerald-600"></i>@endif</button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                    @auth
                        <a href="{{ route('dashboard') }}" class="healo-button-primary !rounded-full !px-5 !py-2.5">{{ __('healo.dashboard') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex min-h-10 items-center rounded-full border border-emerald-200 px-5 text-sm font-bold text-emerald-700 transition hover:bg-emerald-50">{{ __('healo.sign_in') }}</a>
                        <a href="{{ route('register') }}" class="inline-flex min-h-10 items-center rounded-full bg-[#098B63] px-5 text-sm font-bold text-white shadow-lg shadow-emerald-700/15 transition hover:bg-emerald-700">{{ __('landing.register') }}</a>
                    @endauth
                </div>

                <button @click="mobileOpen = !mobileOpen" type="button" class="grid size-10 place-items-center rounded-xl text-[#17345C] hover:bg-slate-100 sm:hidden" aria-label="{{ __('landing.toggle_navigation') }}">
                    <i x-show="!mobileOpen" data-lucide="menu" class="size-5"></i>
                    <i x-cloak x-show="mobileOpen" data-lucide="x" class="size-5"></i>
                </button>
            </div>

            <div x-cloak x-show="mobileOpen" x-transition class="border-t border-slate-100 bg-white px-5 py-5 sm:hidden">
                <nav class="grid gap-1">
                    @foreach([['#home', 'home'], ['#features', 'features'], ['#about', 'about'], ['#help', 'help']] as [$href, $label])
                        <a @click="mobileOpen = false" href="{{ $href }}" class="rounded-xl px-3 py-3 text-sm font-bold text-[#496687] hover:bg-emerald-50 hover:text-emerald-700">{{ __('landing.'.$label) }}</a>
                    @endforeach
                </nav>
                <div class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="healo-button-primary col-span-2 !rounded-full">{{ __('healo.dashboard') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="healo-button-secondary !rounded-full">{{ __('healo.sign_in') }}</a>
                        <a href="{{ route('register') }}" class="healo-button-primary !rounded-full">{{ __('landing.register') }}</a>
                    @endauth
                </div>
            </div>
        </header>

        <main>
            <section id="home" class="relative scroll-mt-20 overflow-hidden bg-gradient-to-br from-white via-[#F8FCFB] to-[#EAF8F2]">
                <div class="pointer-events-none absolute -right-28 top-14 size-[34rem] rounded-full bg-emerald-100/50 blur-3xl"></div>
                <div class="mx-auto grid min-h-[540px] max-w-7xl items-center gap-8 px-5 pb-12 pt-14 sm:px-8 lg:grid-cols-[0.9fr_1.1fr] lg:pb-0 lg:pt-10">
                    <div class="relative z-10 max-w-xl py-5 lg:py-16">
                        <p class="mb-4 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white/80 px-3 py-1.5 text-xs font-bold text-emerald-700 shadow-sm"><i data-lucide="heart-pulse" class="size-4"></i>{{ __('landing.eyebrow') }}</p>
                        <h1 class="text-4xl font-black leading-[1.13] tracking-[-0.035em] text-[#10365D] sm:text-5xl lg:text-[3.45rem]">{{ __('landing.hero_title') }}</h1>
                        <p class="mt-5 max-w-lg text-base leading-7 text-[#587393] sm:text-lg">{{ __('landing.hero_copy') }}</p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="inline-flex min-h-12 items-center gap-2 rounded-full bg-[#098B63] px-7 text-sm font-bold text-white shadow-xl shadow-emerald-700/20 transition hover:-translate-y-0.5 hover:bg-emerald-700">{{ __('healo.start_now') }}<i data-lucide="arrow-right" class="size-4"></i></a>
                            <a href="#features" class="inline-flex min-h-12 items-center rounded-full border border-emerald-300 bg-white/80 px-7 text-sm font-bold text-emerald-700 transition hover:bg-emerald-50">{{ __('landing.explore_features') }}</a>
                        </div>
                        <div class="mt-7 flex flex-wrap gap-x-6 gap-y-2 text-xs font-semibold text-[#587393]">
                            <span class="inline-flex items-center gap-2"><i data-lucide="shield-check" class="size-4 text-emerald-600"></i>{{ __('landing.private_data') }}</span>
                            <span class="inline-flex items-center gap-2"><i data-lucide="sparkles" class="size-4 text-emerald-600"></i>{{ __('landing.personal_insight') }}</span>
                        </div>
                    </div>

                    <div class="relative flex min-h-[390px] items-end justify-center self-end lg:min-h-[500px]">
                        <div class="absolute bottom-8 left-1/2 h-72 w-[90%] -translate-x-1/2 rounded-[50%] bg-emerald-100/60 blur-2xl"></div>
                        <img src="{{ asset('images/healo-hero-wellness.png') }}" alt="{{ __('landing.hero_image_alt') }}" class="relative z-10 max-h-[500px] w-full object-contain object-bottom drop-shadow-[0_24px_36px_rgba(15,118,83,0.12)]">
                    </div>
                </div>
            </section>

            <section id="features" class="scroll-mt-20 bg-white py-20 sm:py-24">
                <div class="mx-auto max-w-7xl px-5 sm:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">{{ __('landing.feature_eyebrow') }}</p>
                        <h2 class="mt-3 text-3xl font-black tracking-tight text-[#10365D]">{{ __('landing.feature_title') }}</h2>
                        <p class="mt-3 text-sm leading-6 text-[#68809E] sm:text-base">{{ __('landing.feature_subtitle') }}</p>
                    </div>

                    @php
                        $features = [
                            ['user-round', 'profile', 'bg-emerald-50 text-emerald-600'], ['gauge', 'bmi', 'bg-sky-50 text-sky-600'],
                            ['notebook-pen', 'journal', 'bg-violet-50 text-violet-600'], ['utensils', 'nutrition', 'bg-amber-50 text-amber-600'],
                            ['message-circle-heart', 'assistant', 'bg-violet-50 text-violet-600'], ['shield-check', 'symptom', 'bg-rose-50 text-rose-500'],
                            ['lightbulb', 'insights', 'bg-sky-50 text-sky-600'], ['target', 'plan', 'bg-emerald-50 text-emerald-600'],
                        ];
                    @endphp
                    <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach($features as [$icon, $key, $colors])
                            <article class="group flex min-h-32 items-start gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_12px_35px_-28px_rgba(15,63,93,0.5)] transition hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg">
                                <span class="grid size-11 shrink-0 place-items-center rounded-full {{ $colors }}"><i data-lucide="{{ $icon }}" class="size-5"></i></span>
                                <div class="min-w-0 flex-1"><h3 class="text-sm font-bold text-[#173E65]">{{ __('landing.feature_'.$key) }}</h3><p class="mt-2 text-xs leading-5 text-[#68809E]">{{ __('landing.feature_'.$key.'_copy') }}</p><span class="mt-2 flex justify-end text-emerald-600 transition group-hover:translate-x-1"><i data-lucide="arrow-right" class="size-4"></i></span></div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="about" class="scroll-mt-20 border-y border-emerald-100 bg-gradient-to-r from-[#F5FCF9] via-white to-[#F3FAFF] py-20 sm:py-24">
                <div class="mx-auto grid max-w-7xl items-center gap-12 px-5 sm:px-8 lg:grid-cols-[0.72fr_1.28fr]">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">{{ __('landing.why_eyebrow') }}</p>
                        <h2 class="mt-3 text-3xl font-black tracking-tight text-[#10365D]">{{ __('landing.why_title') }}</h2>
                        <ul class="mt-7 space-y-4">
                            @foreach(['secure', 'evidence', 'safe_ai', 'simple'] as $reason)
                                <li class="flex items-start gap-3 text-sm font-medium leading-6 text-[#496687]"><span class="mt-0.5 grid size-5 shrink-0 place-items-center rounded-full bg-emerald-600 text-white"><i data-lucide="check" class="size-3"></i></span>{{ __('landing.why_'.$reason) }}</li>
                            @endforeach
                        </ul>
                        <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="mt-8 inline-flex min-h-11 items-center gap-2 rounded-full bg-[#098B63] px-6 text-sm font-bold text-white shadow-lg shadow-emerald-700/15 transition hover:bg-emerald-700">{{ __('healo.start_now') }}<i data-lucide="arrow-right" class="size-4"></i></a>
                    </div>

                    <div class="relative">
                        <div class="absolute -inset-6 rounded-[2.5rem] bg-emerald-200/25 blur-3xl"></div>
                        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-300/40">
                            <div class="flex h-10 items-center justify-between border-b border-slate-100 px-4"><div class="flex items-center gap-2"><span class="size-2 rounded-full bg-rose-300"></span><span class="size-2 rounded-full bg-amber-300"></span><span class="size-2 rounded-full bg-emerald-300"></span></div><div class="h-4 w-32 rounded-full bg-slate-50"></div></div>
                            <div class="grid min-h-[310px] grid-cols-[92px_1fr] bg-[#F8FAFC] sm:grid-cols-[130px_1fr]">
                                <div class="border-r border-slate-100 bg-white p-3">
                                    <div class="flex items-center gap-2 text-[9px] font-black text-emerald-700"><i data-lucide="heart-pulse" class="size-4"></i>HEALO</div>
                                    <div class="mt-6 space-y-2">@foreach(['layout-dashboard', 'user-round', 'gauge', 'notebook-pen', 'utensils', 'lightbulb'] as $index => $icon)<div @class(['flex h-7 items-center gap-2 rounded-lg px-2', 'bg-emerald-50 text-emerald-700' => $index === 0, 'text-slate-400' => $index !== 0])><i data-lucide="{{ $icon }}" class="size-3"></i><span class="hidden h-1.5 rounded-full bg-current opacity-30 sm:block" style="width: {{ 26 + ($index % 3) * 8 }}px"></span></div>@endforeach</div>
                                </div>
                                <div class="p-4 sm:p-6">
                                    <div class="rounded-xl bg-emerald-50 p-4"><div class="h-2 w-28 rounded-full bg-[#17345C]/70"></div><div class="mt-2 h-1.5 w-44 max-w-full rounded-full bg-emerald-600/20"></div></div>
                                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">@foreach(['gauge', 'flame', 'notebook-pen', 'utensils'] as $icon)<div class="rounded-xl border border-slate-100 bg-white p-3"><i data-lucide="{{ $icon }}" class="size-4 text-emerald-600"></i><div class="mt-4 h-2 w-10 rounded bg-[#17345C]/70"></div><div class="mt-2 h-1 w-14 rounded bg-slate-200"></div></div>@endforeach</div>
                                    <div class="mt-4 grid gap-3 sm:grid-cols-[1.35fr_0.65fr]"><div class="rounded-xl border border-slate-100 bg-white p-4"><div class="h-2 w-24 rounded bg-[#17345C]/70"></div><svg viewBox="0 0 320 95" class="mt-4 w-full" aria-hidden="true"><path d="M0 68 C42 58 55 74 93 55 S150 48 188 42 S245 57 320 30" fill="none" stroke="#159A70" stroke-width="3"/><path d="M0 68 C42 58 55 74 93 55 S150 48 188 42 S245 57 320 30 L320 95 L0 95Z" fill="#DFF5EC" opacity=".8"/><g fill="#159A70"><circle cx="0" cy="68" r="4"/><circle cx="93" cy="55" r="4"/><circle cx="188" cy="42" r="4"/><circle cx="320" cy="30" r="4"/></g></svg></div><div class="rounded-xl border border-slate-100 bg-white p-4"><div class="h-2 w-20 rounded bg-[#17345C]/70"></div><div class="mt-4 space-y-3">@foreach([70, 52, 82] as $width)<div class="h-1.5 rounded-full bg-slate-100"><div class="h-full rounded-full bg-emerald-500" style="width: {{ $width }}%"></div></div>@endforeach</div></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="help" class="scroll-mt-20 bg-white py-20">
                <div class="mx-auto max-w-5xl px-5 sm:px-8"><div class="overflow-hidden rounded-[2rem] bg-[#10365D] px-6 py-12 text-center text-white shadow-2xl shadow-slate-300/50 sm:px-12"><span class="mx-auto grid size-12 place-items-center rounded-2xl bg-white/10"><i data-lucide="heart-pulse" class="size-6 text-emerald-300"></i></span><h2 class="mt-5 text-3xl font-black tracking-tight">{{ __('landing.cta_title') }}</h2><p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-slate-300 sm:text-base">{{ __('landing.cta_copy') }}</p><a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="mt-7 inline-flex min-h-12 items-center gap-2 rounded-full bg-emerald-500 px-7 text-sm font-bold text-white transition hover:bg-emerald-400">{{ __('healo.start_now') }}<i data-lucide="arrow-right" class="size-4"></i></a></div></div>
            </section>
        </main>

        <footer class="border-t border-slate-100 bg-white"><div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-5 py-8 text-center sm:flex-row sm:px-8 sm:text-left"><x-application-logo class="scale-90" /><p class="text-xs text-slate-500">{{ __('landing.footer_copy', ['year' => now()->year]) }}</p></div></footer>
    </div>
</body>
</html>
