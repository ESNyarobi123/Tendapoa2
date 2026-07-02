@extends('layouts.app')
@section('title', $listing->title . ' — Huduma')

@section('content')
<div class="flex min-h-screen bg-slate-50">
  @include('components.user-sidebar')

  <main class="tp-main w-full min-w-0 p-4 pt-16 sm:p-6 lg:pt-6">
    <div class="mx-auto max-w-4xl space-y-6">

      <div class="flex flex-wrap items-center gap-2 text-[13px]">
        <a href="{{ route('services.index') }}" class="font-semibold text-brand-600 hover:underline">← Huduma</a>
      </div>

      <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        @if($listing->image_url)
          <img src="{{ $listing->image_url }}" alt="" class="max-h-80 w-full object-cover">
        @endif

        <div class="space-y-5 p-5 sm:p-6">
          <div>
            <span class="inline-flex rounded-lg bg-brand-50 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-brand-700">
              {{ $listing->category->name ?? 'Huduma' }}
            </span>
            <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900">{{ $listing->title }}</h1>
            <p class="mt-2 text-2xl font-bold text-brand-700">TZS {{ number_format($listing->price) }}</p>
          </div>

          <div class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 p-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-100 text-lg font-bold text-brand-700">
              {{ strtoupper(substr($listing->user->name ?? 'M', 0, 1)) }}
            </div>
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Mtoa huduma</p>
              <p class="font-semibold text-slate-900">{{ $listing->user->name ?? 'Mfanyakazi' }}</p>
            </div>
          </div>

          @if($listing->address_text)
            <p class="text-[13px] text-slate-600">📍 {{ $listing->address_text }}</p>
          @endif

          @if(!empty($listing->distance_info['label']))
            <p class="text-[13px] font-medium text-slate-600">Umbali: {{ $listing->distance_info['label'] }}</p>
          @endif

          <div>
            <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Maelezo</h2>
            <p class="mt-2 whitespace-pre-line text-[14px] leading-relaxed text-slate-700">{{ $listing->description }}</p>
          </div>

          <form method="POST" action="{{ route('services.book', $listing) }}" class="space-y-4 border-t border-slate-100 pt-5">
            @csrf
            <div>
              <label for="message" class="mb-1.5 block text-[12px] font-semibold text-slate-600">Ujumbe kwa mtoa huduma (si lazima)</label>
              <textarea id="message" name="message" rows="3"
                class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-[13px] focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                placeholder="Eleza mahitaji yako kwa ufupi…">{{ old('message') }}</textarea>
              @error('message')<p class="mt-1 text-[12px] text-red-600">{{ $message }}</p>@enderror
            </div>

            @error('error')<p class="rounded-lg bg-red-50 px-3 py-2 text-[13px] text-red-700">{{ $message }}</p>@enderror

            <button type="submit"
              class="w-full rounded-xl bg-orange-500 px-4 py-3 text-[14px] font-bold text-white shadow-sm hover:bg-orange-600 sm:w-auto sm:px-8">
              Chagua Mtoa Huduma &amp; Lipa Escrow
            </button>
            <p class="text-[12px] text-slate-500">Baada ya kuchagua, utaelekezwa kwenye ukurasa wa malipo ya escrow.</p>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>
@endsection
