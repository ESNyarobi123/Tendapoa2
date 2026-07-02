@extends('layouts.app')
@section('title', 'Huduma — TendaPoa')

@section('content')
<div class="flex min-h-screen bg-slate-50">
  @include('components.user-sidebar')

  <main class="tp-main w-full min-w-0 p-4 pt-16 sm:p-6 lg:pt-6">
    <div class="mx-auto max-w-6xl space-y-6">

      <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Huduma za Watoa Huduma</h1>
        <p class="mt-1 max-w-2xl text-[13px] leading-relaxed text-slate-500">
          Vinjari huduma zilizochapishwa na wafanyakazi, chagua mtoa huduma, kisha lipa escrow kwa usalama.
        </p>
        <p class="mt-3 inline-flex items-center gap-2 rounded-lg bg-brand-50 px-3 py-1.5 text-[12px] font-semibold text-brand-800">
          📋 {{ $listings->total() }} huduma zinapatikana
        </p>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <form method="GET" action="{{ route('services.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div class="sm:col-span-2">
            <label for="search" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Tafuta</label>
            <input type="search" id="search" name="search" value="{{ request('search') }}"
              placeholder="Jina, maelezo, eneo…"
              class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-[13px] focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
          </div>
          <div>
            <label for="category" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Aina</label>
            <select id="category" name="category" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-[13px] focus:border-brand-500 focus:outline-none">
              <option value="">Zote</option>
              @foreach($categories as $cat)
                <option value="{{ $cat->slug }}" @selected(request('category') === $cat->slug)>{{ $cat->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 rounded-xl bg-brand-600 px-4 py-2.5 text-[13px] font-semibold text-white hover:bg-brand-700">Chuja</button>
            <a href="{{ route('services.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-[13px] font-semibold text-slate-600 hover:bg-slate-50">Safisha</a>
          </div>
        </form>
      </div>

      @if($listings->count())
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
          @foreach($listings as $listing)
            <a href="{{ route('services.show', $listing) }}"
              class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md">
              <div class="relative aspect-[16/10] bg-slate-100">
                @if($listing->image_url)
                  <img src="{{ $listing->image_url }}" alt="" class="h-full w-full object-cover">
                @else
                  <div class="flex h-full items-center justify-center text-4xl text-slate-300">🛠️</div>
                @endif
                @if(!empty($listing->distance_info['label']))
                  <span class="absolute right-2 top-2 rounded-lg px-2 py-1 text-[10px] font-bold text-white"
                    style="background: {{ $listing->distance_info['color'] ?? '#64748b' }}">
                    {{ $listing->distance_info['label'] }}
                  </span>
                @endif
              </div>
              <div class="p-4">
                <h2 class="line-clamp-2 text-[15px] font-bold text-slate-900 group-hover:text-brand-700">{{ $listing->title }}</h2>
                <p class="mt-1 text-[12px] text-slate-500">{{ $listing->user->name ?? 'Mtoa huduma' }}</p>
                <div class="mt-3 flex items-center justify-between gap-2">
                  <span class="text-[14px] font-bold text-brand-700">TZS {{ number_format($listing->price) }}</span>
                  <span class="text-[11px] font-semibold text-slate-400">{{ $listing->category->name ?? '' }}</span>
                </div>
              </div>
            </a>
          @endforeach
        </div>

        <div class="pt-2">{{ $listings->withQueryString()->links() }}</div>
      @else
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-12 text-center">
          <div class="text-4xl">🔍</div>
          <p class="mt-3 font-semibold text-slate-700">Hakuna huduma zilizopatikana</p>
          <p class="mt-1 text-[13px] text-slate-500">Jaribu kubadilisha vichujio au rudi baadaye.</p>
        </div>
      @endif
    </div>
  </main>
</div>
@endsection
