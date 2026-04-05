@extends('layouts.app')
@section('title', 'Subiri malipo')

@section('content')
<div class="flex min-h-screen bg-slate-50">
  @include('components.user-sidebar')

  <main class="tp-main w-full min-w-0 p-4 pt-16 sm:p-6 lg:pt-6">
    <div class="mx-auto w-full max-w-md space-y-5 text-center">

      <div id="spinner" class="flex justify-center py-2">
        <div class="h-12 w-12 animate-spin rounded-full border-4 border-slate-200 border-t-emerald-600"></div>
      </div>

      <div>
        <h1 class="text-lg font-bold text-slate-900" id="title">Tunasubiri malipo…</h1>
        <p class="mt-2 text-[12px] leading-relaxed text-slate-500" id="subtitle">Thibitisha kwenye simu yako. Ukurasa utasasishwa moja kwa moja.</p>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-5 text-left shadow-sm">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Kazi</p>
        <p class="mt-0.5 text-[14px] font-bold text-slate-900">{{ $job->title }}</p>
        <p class="mt-3 text-center text-2xl font-extrabold tabular-nums text-emerald-700">{{ number_format($payment->amount) }} <span class="text-sm font-bold text-emerald-600/90">TZS</span></p>
        <p class="mt-2 text-center text-[10px] text-slate-400">Order: {{ $payment->order_id }}</p>
      </div>

      <div id="success-msg" class="hidden rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-5">
        <p class="text-2xl">✅</p>
        <p class="mt-2 text-[14px] font-bold text-emerald-900">Malipo yamefanikiwa</p>
        <p class="mt-1 text-[12px] text-emerald-800">Unaelekezwa kwenye kazi…</p>
      </div>

      <div id="fail-msg" class="hidden rounded-2xl border border-red-200 bg-red-50 px-4 py-5">
        <p class="text-2xl">❌</p>
        <p class="mt-2 text-[14px] font-bold text-red-900">Malipo hayajathibitishwa</p>
        <p class="mt-1 text-[12px] text-red-800">Jaribu tena au tumia wallet.</p>
        <a href="{{ route('jobs.fund', $job) }}" class="mt-4 inline-flex rounded-xl bg-brand-600 px-4 py-2.5 text-[12px] font-bold text-white hover:bg-brand-700">Rudi kwenye malipo</a>
      </div>

      <a href="{{ route('jobs.show', $job) }}" class="inline-block text-[12px] font-medium text-slate-500 hover:text-slate-800">Rudi kwenye kazi bila kusubiri</a>
    </div>
  </main>
</div>

@push('scripts')
<script>
(function () {
  let attempts = 0;
  const maxAttempts = 90;
  const pollUrl = @json(route('jobs.fund.poll', $job));
  const successUrl = @json(route('jobs.show', $job));

  function poll() {
    if (attempts++ > maxAttempts) {
      document.getElementById('title').textContent = 'Muda umepita';
      document.getElementById('subtitle').textContent = 'Hakuna uthibitisho. Jaribu tena.';
      document.getElementById('spinner').classList.add('hidden');
      document.getElementById('fail-msg').classList.remove('hidden');
      return;
    }

    fetch(pollUrl, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
      .then((r) => r.json())
      .then((data) => {
        if (data.done && data.status === 'COMPLETED') {
          document.getElementById('spinner').classList.add('hidden');
          document.getElementById('title').classList.add('hidden');
          document.getElementById('subtitle').classList.add('hidden');
          document.getElementById('success-msg').classList.remove('hidden');
          setTimeout(() => { window.location.href = successUrl; }, 2000);
          return;
        }
        if (data.status === 'FAILED') {
          document.getElementById('spinner').classList.add('hidden');
          document.getElementById('title').classList.add('hidden');
          document.getElementById('subtitle').classList.add('hidden');
          document.getElementById('fail-msg').classList.remove('hidden');
          return;
        }
        setTimeout(poll, 4000);
      })
      .catch(() => setTimeout(poll, 5000));
  }

  setTimeout(poll, 3000);
})();
</script>
@endpush
@endsection
