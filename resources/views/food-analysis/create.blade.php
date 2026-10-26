@php $usesCloudVision = config('nutrition.food_analysis_provider') === 'groq' && filled(config('services.groq.api_key')); @endphp
<x-app-layout>
    <x-slot name="title">{{ __('nutrition.scanner_title') }}</x-slot>
    <x-slot name="header"><p class="text-xs font-medium text-slate-500">{{ __('nutrition.title') }}</p><h1 class="text-lg font-bold text-slate-900">{{ __('nutrition.scanner_title') }}</h1></x-slot>
    <div class="mx-auto max-w-5xl space-y-6">
        @if(session('status'))<div role="status" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ __('nutrition.'.session('status')) }}</div>@endif
        <section class="text-center"><p class="healo-kicker">{{ __('nutrition.ai_estimate') }}</p><h2 class="mt-2 text-3xl font-black tracking-tight text-slate-950">{{ __('nutrition.scanner_title') }}</h2><p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-slate-500">{{ __('nutrition.scanner_copy') }}</p></section>
        <div class="grid gap-6 md:grid-cols-2">
            @foreach([
                ['capture' => true, 'icon' => 'camera', 'label' => __('nutrition.take_photo')],
                ['capture' => false, 'icon' => 'upload', 'label' => __('nutrition.upload_gallery')],
            ] as $option)
                <form method="POST" action="{{ route('food-analysis.store') }}" enctype="multipart/form-data" class="healo-card p-6" x-data="{ preview: null, loading: false }" @submit="loading = true">@csrf
                    <label class="block cursor-pointer rounded-3xl border-2 border-dashed border-slate-200 p-8 text-center transition hover:border-emerald-300 hover:bg-emerald-50/40"><input type="file" name="image" accept="image/jpeg,image/png,image/webp" @if($option['capture']) capture="environment" @endif required class="sr-only" @change="const file=$event.target.files[0]; if(file){ preview=URL.createObjectURL(file) }"><span class="mx-auto grid size-14 place-items-center rounded-3xl bg-emerald-50 text-emerald-700"><i data-lucide="{{ $option['icon'] }}" class="size-7"></i></span><span class="mt-4 block font-bold text-slate-900">{{ $option['label'] }}</span><span class="mt-1 block text-xs text-slate-500">JPEG, PNG, WEBP · max 5 MB</span></label>
                    <template x-if="preview"><img :src="preview" alt="{{ __('nutrition.image_preview') }}" class="mt-4 h-56 w-full rounded-2xl object-cover"></template>
                    <button class="healo-button-primary mt-4 w-full" :disabled="loading"><i data-lucide="scan-line" class="size-4"></i><span x-text="loading ? '{{ __('nutrition.analyzing') }}' : '{{ __('nutrition.analyze_food') }}'"></span></button>
                </form>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('image')" class="text-center"/>
        <div @class(['flex gap-3 rounded-2xl border p-4 text-sm leading-6', 'border-amber-200 bg-amber-50 text-amber-950' => $usesCloudVision, 'border-sky-200 bg-sky-50 text-sky-900' => ! $usesCloudVision])><i data-lucide="{{ $usesCloudVision ? 'cloud-upload' : 'shield-check' }}" class="mt-0.5 size-5 shrink-0"></i><div><p class="font-bold">{{ $usesCloudVision ? __('nutrition.cloud_vision_active') : __('nutrition.private_image') }}</p><p>{{ $usesCloudVision ? __('nutrition.cloud_analysis_privacy') : __('nutrition.analysis_privacy') }}</p></div></div>
        <section class="healo-card p-6"><h2 class="font-bold text-slate-900">{{ __('nutrition.recent_drafts') }}</h2><div class="mt-4 space-y-3">@forelse($recentAnalyses as $analysis)<a href="{{ route('food-analysis.show', $analysis) }}" class="flex items-center justify-between rounded-2xl border border-slate-100 p-4 hover:border-emerald-200"><div><p class="font-semibold text-slate-900">{{ __('nutrition.ai_estimate') }} #{{ $analysis->id }}</p><p class="text-xs text-slate-500">{{ $analysis->created_at->diffForHumans() }}</p></div><span class="text-sm font-semibold text-emerald-700">{{ __('nutrition.continue_review') }}</span></a>@empty<p class="text-sm text-slate-500">{{ __('nutrition.no_drafts') }}</p>@endforelse</div></section>
    </div>
</x-app-layout>
