@extends('layouts.app')
@section('title', 'Weka pesa — wallet')

@section('content')
@php
  $avail = (int) $wallet->available_balance;
  $bal = (int) $wallet->balance;
  $held = (int) $wallet->held_balance;
@endphp
<div class="flex min-h-screen bg-slate-50">
  @include('components.user-sidebar')

  <main class="tp-main w-full min-w-0 p-4 pt-16 sm:p-6 lg:pt-6">
    <div class="mx-auto w-full max-w-lg space-y-5">

      <div>
        <h1 class="text-lg font-bold tracking-tight text-slate-900 sm:text-xl">Weka pesa kwenye wallet</h1>
        <p class="mt-0.5 text-[12px] leading-relaxed text-slate-500">
          Malipo kupitia ClickPesa (M-Pesa, TigoPesa, Airtel Money, n.k.). Salio linalopatikana ndilo unaloweza kutumia kwa escrow au kutoa — pesa zilizoshikiliwa kwenye escrow hazihesabiwi hapa.
        </p>
      </div>

      <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="grid gap-3 sm:grid-cols-2">
          <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-3">
            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Salio jumla</p>
            <p class="mt-0.5 text-lg font-extrabold tabular-nums text-slate-900">{{ number_format($bal) }} TZS</p>
          </div>
          <div class="rounded-xl border border-emerald-100 bg-emerald-50/80 px-3 py-3">
            <p class="text-[10px] font-bold uppercase tracking-wide text-emerald-800">Linalopatikana</p>
            <p class="mt-0.5 text-lg font-extrabold tabular-nums text-emerald-800">{{ number_format($avail) }} TZS</p>
          </div>
        </div>
        @if($held > 0)
          <p class="mt-3 text-[11px] text-slate-500">Imeshikiliwa (escrow): <span class="font-semibold tabular-nums text-slate-700">{{ number_format($held) }} TZS</span></p>
        @endif
      </section>

      @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-[12px] text-red-900">
          <ul class="list-inside list-disc space-y-0.5">
            @foreach($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
        </div>
      @endif
      @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[12px] font-medium text-emerald-900">{{ session('success') }}</div>
      @endif

      <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
        <form method="POST" action="{{ route('wallet.deposit.submit') }}" class="space-y-4">
          @csrf
          <div>
            <label for="amount" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-slate-600">Kiasi (TZS)</label>
            <input type="number" id="amount" name="amount" min="1000" value="{{ old('amount') }}" required placeholder="Mf. 10000"
              class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-[13px] tabular-nums text-slate-900 shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
            <p class="mt-1 text-[11px] text-slate-500">Chini ya TZS 1,000 hairuhusiwi.</p>
          </div>
          <div>
            <label for="phone_number" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-slate-600">Namba ya simu</label>
            <input type="tel" id="phone_number" name="phone_number" value="{{ old('phone_number', auth()->user()->phone) }}" required placeholder="07xxxxxxxx"
              class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-[13px] text-slate-900 shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
            <p class="mt-1 text-[11px] text-slate-500">06/07xxxxxxxx au 2556/2557xxxxxxxx</p>
          </div>
          <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-center text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Rudi dashboard</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-600 px-4 py-2.5 text-[12px] font-bold text-white shadow-sm hover:bg-brand-700">Tuma ombi la malipo</button>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
@endsection
