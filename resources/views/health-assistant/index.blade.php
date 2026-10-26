<x-app-layout>
    <x-slot name="title">{{ __('health_ai.assistant_title') }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div><p class="healo-kicker">{{ config('health_ai.provider') === 'groq' && filled(config('services.groq.api_key')) ? __('health_ai.groq_badge') : __('health_ai.demo_badge') }}</p><h1 class="text-lg font-bold text-slate-900">{{ __('health_ai.assistant_title') }}</h1></div>
            <a href="{{ route('symptom-checker.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700"><i data-lucide="stethoscope" class="size-4"></i>{{ __('health_ai.symptom_checker') }}</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-4">
        <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900">
            <p class="font-semibold">{{ config('health_ai.provider') === 'groq' && filled(config('services.groq.api_key')) ? __('health_ai.groq_notice') : __('health_ai.demo_notice') }}</p>
            <p class="mt-1 text-sky-800">{{ __('health_ai.not_doctor') }}</p>
        </div>

        <div class="grid min-h-[68vh] gap-5 lg:grid-cols-[20rem_1fr]">
            <aside class="healo-card flex flex-col p-4">
                <form method="POST" action="{{ route('health-assistant.store') }}" class="space-y-3">
                    @csrf
                    <label for="title" class="text-sm font-semibold text-slate-700">{{ __('health_ai.new_conversation') }}</label>
                    <input id="title" name="title" maxlength="160" placeholder="{{ __('health_ai.conversation_title_optional') }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <button class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-800"><i data-lucide="plus" class="size-4"></i>{{ __('health_ai.start_conversation') }}</button>
                </form>

                <div class="mt-5 border-t border-slate-100 pt-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('health_ai.conversations') }}</h2>
                    <div class="mt-3 space-y-1">
                        @forelse($conversations as $conversation)
                            <a href="{{ route('health-assistant.show', $conversation) }}" @class(['block rounded-xl px-3 py-3 text-sm transition', 'bg-emerald-50 text-emerald-900' => $selected?->is($conversation), 'text-slate-600 hover:bg-slate-50' => ! $selected?->is($conversation)])>
                                <span class="block truncate font-semibold">{{ $conversation->title }}</span>
                                <span class="mt-1 block text-xs text-slate-400">{{ $conversation->messages_count }} pesan</span>
                            </a>
                        @empty
                            <p class="py-4 text-sm text-slate-500">{{ __('health_ai.no_conversations') }}</p>
                        @endforelse
                    </div>
                    <div class="mt-3">{{ $conversations->links() }}</div>
                </div>
            </aside>

            <section class="healo-card flex min-h-[34rem] flex-col overflow-hidden">
                @if($selected)
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                        <div class="min-w-0"><h2 class="truncate font-bold text-slate-900">{{ $selected->title }}</h2><p class="text-xs text-slate-500">{{ __('health_ai.not_doctor') }}</p></div>
                        <form method="POST" action="{{ route('health-assistant.destroy', $selected) }}" onsubmit="return confirm(@js(__('health_ai.delete_confirmation')))" class="ml-3">@csrf @method('DELETE')<button class="grid size-9 place-items-center rounded-xl text-slate-400 hover:bg-rose-50 hover:text-rose-600" aria-label="{{ __('health_ai.delete_conversation') }}"><i data-lucide="trash-2" class="size-4"></i></button></form>
                    </div>

                    <div class="flex-1 space-y-4 overflow-y-auto bg-slate-50/60 p-4 sm:p-6" id="conversation-messages">
                        @forelse($selected->messages as $message)
                            @php $assistant = $message->role->value === 'assistant'; $emergency = data_get($message->metadata, 'urgency') === 'emergency'; @endphp
                            <div class="flex {{ $assistant ? 'justify-start' : 'justify-end' }}">
                                <div @class(['max-w-[88%] rounded-2xl px-4 py-3 text-sm leading-6 sm:max-w-[75%]', 'bg-white text-slate-700 shadow-sm ring-1 ring-slate-200' => $assistant && !$emergency, 'bg-rose-50 text-rose-900 ring-1 ring-rose-200' => $assistant && $emergency, 'bg-slate-900 text-white' => !$assistant])>
                                    @if($assistant)<div class="mb-1 flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider {{ $emergency ? 'text-rose-600' : 'text-emerald-700' }}"><i data-lucide="sparkles" class="size-3.5"></i>{{ data_get($message->metadata, 'provider') === 'groq' ? __('health_ai.groq_badge') : __('health_ai.demo_badge') }}</div>@endif
                                    <p class="whitespace-pre-line">{{ $message->content }}</p>
                                    <p class="mt-2 text-[11px] opacity-60">{{ $message->created_at->format('H:i') }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="grid h-full place-items-center text-center"><div><span class="mx-auto grid size-14 place-items-center rounded-2xl bg-emerald-50 text-emerald-700"><i data-lucide="message-circle-heart" class="size-7"></i></span><p class="mt-4 font-semibold text-slate-900">{{ __('health_ai.assistant_intro') }}</p></div></div>
                        @endforelse
                    </div>

                    <form method="POST" action="{{ route('health-assistant.messages.store', $selected) }}" class="sticky bottom-0 border-t border-slate-100 bg-white p-4">
                        @csrf
                        <div class="flex items-end gap-3">
                            <textarea name="message" rows="2" maxlength="{{ config('health_ai.message_max_length') }}" required placeholder="{{ __('health_ai.message_placeholder') }}" class="min-h-12 flex-1 resize-none rounded-2xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('message') }}</textarea>
                            <button class="grid size-12 shrink-0 place-items-center rounded-2xl bg-emerald-600 text-white hover:bg-emerald-700" aria-label="{{ __('health_ai.send') }}"><i data-lucide="send" class="size-5"></i></button>
                        </div>
                        @error('message')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </form>
                @else
                    <div class="grid flex-1 place-items-center p-8 text-center"><div><span class="mx-auto grid size-16 place-items-center rounded-3xl bg-sky-50 text-sky-700"><i data-lucide="sparkles" class="size-8"></i></span><h2 class="mt-5 text-xl font-bold text-slate-900">{{ __('health_ai.assistant_title') }}</h2><p class="mt-2 max-w-md text-sm leading-6 text-slate-500">{{ __('health_ai.select_or_start') }}</p></div></div>
                @endif
            </section>
        </div>
    </div>

    @if($selected)
        <script>document.getElementById('conversation-messages')?.scrollTo(0, document.getElementById('conversation-messages').scrollHeight);</script>
    @endif
</x-app-layout>
