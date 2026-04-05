@extends('layouts.app')
@section('title', 'Chapisha Kazi')

@php
  $categories = $categories ?? \App\Models\Category::all();
  $errStep = 1;
  if ($errors->any()) {
    if ($errors->has('lat') || $errors->has('lng')) {
      $errStep = 3;
    } elseif ($errors->has('price') || $errors->has('image')) {
      $errStep = 2;
    } elseif ($errors->has('title') || $errors->has('category_id')) {
      $errStep = 1;
    }
  }
@endphp

@section('content')
<div class="flex min-h-screen bg-slate-100/90">
  @include('components.user-sidebar')

  <main class="tp-main w-full min-w-0 p-4 pt-16 sm:p-6 lg:pt-6">
    <div class="mx-auto w-full max-w-[min(100%,1400px)] space-y-5 pb-10">

      <header class="text-center sm:text-left">
        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-brand-700/80">Chapisha kazi</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Unda tangazo lako</h1>
        <p class="mt-2 max-w-2xl text-[12px] leading-relaxed text-slate-600">Hatua 4 za fomu hapa. <strong class="text-slate-800">Kuchapisha ni bure.</strong> Baada ya kuchagua mfanyakazi utaombwa <strong class="text-slate-800">kulipa escrow</strong> kwa wallet au simu — ongeza salio mapema ikiwa unahitaji.</p>
      </header>

      <div class="grid gap-6 lg:grid-cols-12 lg:items-start">
        <div class="min-w-0 space-y-6 lg:col-span-7 xl:col-span-8">

      {{-- Stepper --}}
      <nav class="mb-0" aria-label="Hatua">
        <ol class="grid grid-cols-4 gap-1.5 sm:gap-3" id="stepper">
          @foreach ([
            ['n' => 1, 'label' => 'Maelezo', 'emoji' => '📋'],
            ['n' => 2, 'label' => 'Bei', 'emoji' => '💰'],
            ['n' => 3, 'label' => 'Eneo', 'emoji' => '📍'],
            ['n' => 4, 'label' => 'Thibitisha', 'emoji' => '✓'],
          ] as $s)
            <li>
              <button type="button" data-goto-step="{{ $s['n'] }}" class="step-pill group flex w-full flex-col items-center gap-1 rounded-xl border border-slate-200 bg-white px-1 py-2.5 text-center shadow-sm transition hover:border-brand-200 sm:flex-row sm:justify-center sm:gap-2 sm:px-2" data-step="{{ $s['n'] }}">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-[15px] leading-none transition group-[.is-active]:bg-brand-600 group-[.is-active]:text-white group-[.is-done]:bg-emerald-500 group-[.is-done]:text-white">{{ $s['emoji'] }}</span>
                <span class="text-[9px] font-bold uppercase leading-tight tracking-wide text-slate-500 group-[.is-active]:text-brand-800 sm:text-[10px]">{{ $s['label'] }}</span>
              </button>
            </li>
          @endforeach
        </ol>
        <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-200">
          <div id="stepProgress" class="h-full rounded-full bg-gradient-to-r from-brand-600 to-teal-500 transition-all duration-500 ease-out" style="width: 25%"></div>
        </div>
      </nav>

      <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-lg ring-1 ring-slate-100/80">
        @if($errors->any())
          <div class="border-b border-red-100 bg-red-50/90 px-5 py-4">
            <p class="text-[12px] font-bold text-red-800">Tafadhali angalia makosa</p>
            <ul class="mt-2 list-inside list-disc text-[12px] text-red-700">
              @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <div id="workerCheckNotification" class="hidden border-b border-slate-100 px-5 py-4">
          <div class="flex gap-3">
            <div id="workerCheckNotifIcon" class="text-2xl"></div>
            <div>
              <h4 id="workerCheckNotifTitle" class="text-[13px] font-bold text-slate-900"></h4>
              <p id="workerCheckNotifMessage" class="mt-0.5 text-[12px] text-slate-600"></p>
            </div>
          </div>
        </div>

        <form method="post" action="{{ route('jobs.store') }}" enctype="multipart/form-data" id="jobCreateForm" class="p-5 sm:p-8">
          @csrf

          {{-- Step 1 --}}
          <div class="job-step space-y-5" data-step-panel="1">
            <div class="rounded-xl bg-gradient-to-br from-brand-50 to-teal-50/40 px-4 py-3 ring-1 ring-brand-100/80">
              <p class="text-[12px] font-semibold text-brand-900">Hatua 1 — Eleza kazi</p>
              <p class="mt-0.5 text-[11px] text-brand-800/80">Kichwa kinafaa kiwe wazi ili wafanyakazi waelewe haraka.</p>
            </div>
            <div>
              <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wide text-slate-600" for="title">Kichwa cha kazi</label>
              <input type="text" id="title" name="title" maxlength="120" required
                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-[15px] font-medium text-slate-900 shadow-inner transition focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                placeholder="Mf: Usafi wa sebuleni na vyoo"
                value="{{ old('title') }}">
            </div>
            <div>
              <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wide text-slate-600" for="category_id">Aina ya huduma</label>
              <select name="category_id" id="category_id" required
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-[14px] text-slate-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                <option value="">Chagua kategoria</option>
                @foreach($categories as $c)
                  <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wide text-slate-600" for="description">Maelezo ya ziada <span class="font-normal normal-case text-slate-400">(hiari)</span></label>
              <textarea name="description" id="description" rows="4"
                class="w-full resize-y rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-[14px] text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                placeholder="Muda, ukubwa wa nyumba, vifaa mteja atatoa, n.k.">{{ old('description') }}</textarea>
            </div>
          </div>

          {{-- Step 2 --}}
          <div class="job-step hidden space-y-5" data-step-panel="2">
            <div class="rounded-xl bg-gradient-to-br from-violet-50 to-indigo-50/30 px-4 py-3 ring-1 ring-violet-100">
              <p class="text-[12px] font-semibold text-violet-900">Hatua 2 — Bei na picha</p>
              <p class="mt-0.5 text-[11px] text-violet-800/80">Bei ya chini TZS 1,000. Picha inasaidia kupata maombi bora.</p>
            </div>
            <div>
              <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wide text-slate-600" for="price">Bei (TZS)</label>
              <input type="number" id="price" name="price" min="1000" step="100" required
                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-[15px] font-semibold tabular-nums text-slate-900 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                placeholder="Mf. 25000"
                value="{{ old('price') }}">
            </div>
            <div class="rounded-xl border-2 border-emerald-200/80 bg-gradient-to-r from-emerald-50 to-teal-50/50 p-4">
              <div class="flex gap-3">
                <span class="text-2xl">🆓</span>
                <div>
                  <p class="text-[13px] font-bold text-emerald-900">Kuchapisha ni bure</p>
                  <p class="mt-1 text-[12px] leading-relaxed text-emerald-800/90">Kazi itaonekana mara moja. Malipo ya escrow ni baada ya kuchagua mfanyakazi.</p>
                </div>
              </div>
            </div>
            <div>
              <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wide text-slate-600">Picha ya kazi <span class="font-normal normal-case text-slate-400">(hiari)</span></label>
              <div class="cursor-pointer rounded-xl border-2 border-dashed border-slate-200 bg-slate-50/80 px-4 py-8 text-center transition hover:border-brand-300 hover:bg-brand-50/30" id="image-upload-area" onclick="document.getElementById('image').click()" ondrop="handleDrop(event)" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)">
                <input type="file" id="image" name="image" accept="image/*" class="hidden" onchange="handleImageSelect(event)">
                <div id="image-upload-placeholder">
                  <div class="text-4xl">📷</div>
                  <p class="mt-2 text-[13px] font-semibold text-slate-700">Bofya au vuta picha hapa</p>
                  <p class="mt-1 text-[11px] text-slate-500">PNG, JPG, WEBP · max 5MB</p>
                  <button type="button" onclick="event.stopPropagation(); document.getElementById('image').click();" class="mt-4 inline-flex rounded-lg border border-brand-200 bg-white px-4 py-2 text-[12px] font-bold text-brand-800 shadow-sm hover:bg-brand-50">Chagua picha</button>
                </div>
                <div id="image-preview" class="hidden">
                  <img id="image-preview-img" src="" alt="" class="mx-auto max-h-56 rounded-xl object-contain shadow-md">
                  <div class="mt-4 flex flex-wrap justify-center gap-2">
                    <button type="button" onclick="event.stopPropagation(); document.getElementById('image').click();" class="rounded-lg border border-slate-200 px-3 py-1.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">Badilisha</button>
                    <button type="button" onclick="event.stopPropagation(); removeImage();" class="rounded-lg border border-red-200 px-3 py-1.5 text-[11px] font-semibold text-red-700 hover:bg-red-50">Ondoa</button>
                  </div>
                </div>
              </div>
              @error('image')
                <p class="mt-2 text-[12px] font-medium text-red-600">{{ $message }}</p>
              @enderror
            </div>
          </div>

          {{-- Step 3 --}}
          <div class="job-step hidden space-y-5" data-step-panel="3">
            <div class="rounded-xl bg-gradient-to-br from-sky-50 to-blue-50/40 px-4 py-3 ring-1 ring-sky-100">
              <p class="text-[12px] font-semibold text-sky-900">Hatua 3 — Eneo la kazi</p>
              <p class="mt-0.5 text-[11px] text-sky-800/80">Weka pini kwenye ramani au tumia GPS. Hii ni lazima.</p>
            </div>
            <div class="overflow-hidden rounded-xl border border-slate-200 shadow-inner">
              <div class="relative h-64 w-full sm:h-72" id="mapWrap">
                <div id="map" class="absolute inset-0 z-[1] h-full w-full bg-slate-100"></div>
              </div>
              <div class="space-y-3 border-t border-slate-100 bg-slate-50/80 p-4">
                <div class="flex flex-wrap gap-2">
                  <button type="button" id="geo" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-[12px] font-bold text-white shadow-md shadow-emerald-600/25 hover:bg-emerald-700">
                    <span>🎯</span> Tumia GPS
                  </button>
                </div>
                <div>
                  <label class="mb-1 block text-[11px] font-bold uppercase text-slate-500" for="address_text">Maelezo ya anwani (hiari)</label>
                  <input type="text" name="address_text" id="address_text"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-[14px] focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                    placeholder="Mf. Mtaa, jengo, dirisha"
                    value="{{ old('address_text') }}">
                </div>
                <div id="location-status" class="hidden rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[12px] font-medium text-red-800">
                  Lazima uweke eneo la kazi kwenye ramani au kwa GPS.
                </div>
              </div>
            </div>
            <input type="hidden" name="lat" id="lat" value="{{ old('lat') }}">
            <input type="hidden" name="lng" id="lng" value="{{ old('lng') }}">
          </div>

          {{-- Step 4 --}}
          <div class="job-step hidden space-y-5" data-step-panel="4">
            <div class="rounded-xl bg-gradient-to-br from-amber-50 to-orange-50/30 px-4 py-3 ring-1 ring-amber-100">
              <p class="text-[12px] font-semibold text-amber-900">Hatua 4 — Hakiki na chapisha</p>
              <p class="mt-0.5 text-[11px] text-amber-800/80">Hakikisha maelezo ni sahihi. Malipo ya escrow hayatahitajika moja kwa moja hapa.</p>
            </div>
            <dl class="divide-y divide-slate-100 rounded-xl border border-slate-200 bg-slate-50/50">
              <div class="flex justify-between gap-4 px-4 py-3">
                <dt class="text-[12px] font-medium text-slate-500">Kichwa</dt>
                <dd class="max-w-[60%] text-right text-[13px] font-semibold text-slate-900" id="sumTitle">—</dd>
              </div>
              <div class="flex justify-between gap-4 px-4 py-3">
                <dt class="text-[12px] font-medium text-slate-500">Huduma</dt>
                <dd class="text-right text-[13px] font-semibold text-slate-900" id="sumCategory">—</dd>
              </div>
              <div class="flex justify-between gap-4 px-4 py-3">
                <dt class="text-[12px] font-medium text-slate-500">Bei</dt>
                <dd class="text-right text-[13px] font-bold text-brand-700 tabular-nums" id="sumPrice">—</dd>
              </div>
              <div class="flex justify-between gap-4 px-4 py-3">
                <dt class="text-[12px] font-medium text-slate-500">Picha</dt>
                <dd class="text-right text-[13px] font-semibold text-slate-900" id="sumImage">—</dd>
              </div>
              <div class="flex justify-between gap-4 px-4 py-3">
                <dt class="text-[12px] font-medium text-slate-500">Eneo</dt>
                <dd class="text-right text-[12px] font-medium text-slate-800" id="sumLocation">—</dd>
              </div>
            </dl>
            <p class="text-center text-[11px] text-slate-500">Ukibofya “Chapisha kazi”, kazi itachapishwa <strong class="text-slate-700">bure</strong> na kuonekana kwa wafanyakazi. Malipo ya escrow yatahitajika tu baada ya kumchagua mfanyakazi.</p>
          </div>

          <div class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('dashboard') }}" class="inline-flex justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-center text-[13px] font-semibold text-slate-700 shadow-sm hover:bg-slate-50">Rudi dashboard</a>
            <div class="flex flex-1 flex-wrap justify-end gap-2 sm:flex-none">
              <button type="button" id="btnPrev" class="hidden rounded-xl border border-slate-200 bg-white px-5 py-3 text-[13px] font-bold text-slate-700 shadow-sm hover:bg-slate-50">← Nyuma</button>
              <button type="button" id="btnNext" class="rounded-xl bg-gradient-to-r from-brand-600 to-teal-600 px-6 py-3 text-[13px] font-bold text-white shadow-lg shadow-brand-600/25 hover:from-brand-700 hover:to-teal-700">Endelea →</button>
              <button type="submit" id="btnSubmit" class="hidden rounded-xl bg-gradient-to-r from-emerald-600 to-brand-600 px-6 py-3 text-[13px] font-bold text-white shadow-lg shadow-emerald-600/20 hover:from-emerald-700 hover:to-brand-700">🚀 Chapisha kazi</button>
            </div>
          </div>
        </form>
      </div>
        </div>

        @php
          $wCreate = $wallet ?? auth()->user()->ensureWallet();
        @endphp
        <aside class="min-w-0 lg:col-span-5 xl:col-span-4">
          <div class="sticky top-20 space-y-4">
            <section class="rounded-2xl border border-brand-200 bg-gradient-to-br from-brand-50 to-teal-50/30 p-4 shadow-sm sm:p-5">
              <h2 class="text-[13px] font-bold text-brand-900">Mtiririko kamili (mteja)</h2>
              <ol class="mt-3 space-y-3 text-[11px] leading-relaxed text-slate-700">
                <li class="flex gap-2.5">
                  <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-600 text-[10px] font-bold text-white">1</span>
                  <span><strong class="text-slate-900">Chapisha kazi</strong> — bure; kazi inakuwa <em>wazi</em> mara moja.</span>
                </li>
                <li class="flex gap-2.5">
                  <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-200 text-[10px] font-bold text-slate-700">2</span>
                  <span><strong class="text-slate-900">Maombi</strong> — pitia na uchague mfanyakazi kwenye <a href="{{ route('my.applications') }}" class="font-semibold text-brand-700 underline decoration-brand-300 underline-offset-2">Maombi yangu</a>.</span>
                </li>
                <li class="flex gap-2.5">
                  <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-200 text-[10px] font-bold text-slate-700">3</span>
                  <span><strong class="text-slate-900">Lipa escrow</strong> — ukurasa wa malipo: <strong>wallet</strong> (salio linalopatikana) au <strong>simu</strong> (USSD).</span>
                </li>
                <li class="flex gap-2.5">
                  <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-200 text-[10px] font-bold text-slate-700">4</span>
                  <span><strong class="text-slate-900">Kazi &amp; uhakiki</strong> — fedha zinabaki escrow hadi uthibitishe kukamilika.</span>
                </li>
              </ol>
            </section>
            <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
              <h2 class="text-[13px] font-bold text-slate-900">Wallet</h2>
              <p class="mt-1 text-[10px] font-semibold uppercase tracking-wide text-slate-500">Linalopatikana kulipa escrow</p>
              <p class="mt-0.5 text-2xl font-extrabold tabular-nums text-emerald-700">{{ number_format($wCreate->available_balance) }} <span class="text-sm font-bold text-emerald-600/90">TZS</span></p>
              @if((int) $wCreate->held_balance > 0)
                <p class="mt-2 text-[11px] text-slate-500">Imeshikiliwa (escrow nyingine): <span class="font-semibold tabular-nums text-slate-700">{{ number_format($wCreate->held_balance) }} TZS</span></p>
              @endif
              <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('wallet.deposit') }}" class="inline-flex flex-1 items-center justify-center rounded-xl bg-brand-600 px-3 py-2.5 text-[12px] font-bold text-white shadow-sm hover:bg-brand-700">Weka pesa</a>
                <a href="{{ route('withdraw.form') }}" class="inline-flex flex-1 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-[12px] font-semibold text-slate-700 shadow-sm hover:bg-slate-50">Toa pesa</a>
              </div>
            </section>
            <p class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-[11px] leading-relaxed text-slate-600">Kiungo <strong class="text-slate-800">Lipia Escrow</strong> kipo kwenye menyu ikiwa kuna kazi zinazosubiri malipo.</p>
          </div>
        </aside>
      </div>
    </div>
  </main>
</div>

{{-- Worker modal --}}
<div class="fixed inset-0 z-[1050] hidden items-center justify-center bg-black/50 p-4 backdrop-blur-sm" id="workerCheckModal" aria-hidden="true">
  <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-slate-200">
    <div class="text-center">
      <div id="workerCheckIcon" class="text-5xl">🔍</div>
      <h3 id="workerCheckTitle" class="mt-3 text-lg font-bold text-slate-900">Inatafuta wafanyakazi...</h3>
      <p id="workerCheckMessage" class="mt-2 text-[13px] text-slate-600">Tafadhali subiri.</p>
    </div>
    <div class="mt-6 flex flex-wrap justify-center gap-2">
      <button type="button" id="btnCancelLocation" class="hidden rounded-xl border border-slate-200 px-4 py-2.5 text-[12px] font-bold text-slate-700 hover:bg-slate-50">Badilisha eneo</button>
      <button type="button" id="btnConfirmLocation" class="hidden rounded-xl bg-brand-600 px-4 py-2.5 text-[12px] font-bold text-white hover:bg-brand-700">Sawa, endelea</button>
    </div>
  </div>
</div>

@push('scripts')
<script>
(function () {
  const TOTAL = 4;
  let currentStep = {{ (int) $errStep }};
  let map = null;
  let marker = null;
  let isLocationSet = false;

  const form = document.getElementById('jobCreateForm');
  const latInput = document.getElementById('lat');
  const lngInput = document.getElementById('lng');

  function syncLocationFlag() {
    isLocationSet = !!(latInput.value && lngInput.value && !isNaN(parseFloat(latInput.value)) && !isNaN(parseFloat(lngInput.value)));
  }
  syncLocationFlag();

  function updateStepperUI() {
    document.querySelectorAll('.step-pill').forEach((btn) => {
      const n = parseInt(btn.dataset.step, 10);
      btn.classList.remove('is-active', 'is-done', 'ring-2', 'ring-brand-500', 'border-brand-200');
      btn.classList.add('border-slate-200', 'bg-white');
      if (n === currentStep) {
        btn.classList.add('is-active', 'ring-2', 'ring-brand-500', 'border-brand-200');
      } else if (n < currentStep) {
        btn.classList.add('is-done');
      }
    });
    const pct = (currentStep / TOTAL) * 100;
    document.getElementById('stepProgress').style.width = pct + '%';

    document.querySelectorAll('[data-step-panel]').forEach((panel) => {
      const n = parseInt(panel.dataset.stepPanel, 10);
      panel.classList.toggle('hidden', n !== currentStep);
    });

    document.getElementById('btnPrev').classList.toggle('hidden', currentStep <= 1);
    document.getElementById('btnNext').classList.toggle('hidden', currentStep >= TOTAL);
    document.getElementById('btnSubmit').classList.toggle('hidden', currentStep !== TOTAL);

    if (currentStep === 3) {
      setTimeout(initMap, 80);
    }
    if (currentStep === 4) {
      fillSummary();
    }
  }

  function initMap() {
    const mapEl = document.getElementById('map');
    if (!mapEl) return;
    if (!map) {
      map = L.map('map').setView([-6.7924, 39.2083], 12);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
      }).addTo(map);
      map.on('click', function (e) {
        setMarker(e.latlng.lat, e.latlng.lng);
      });
      const olat = parseFloat(latInput.value);
      const olng = parseFloat(lngInput.value);
      if (!isNaN(olat) && !isNaN(olng)) {
        setMarker(olat, olng);
        map.setView([olat, olng], 14);
      }
      const geoBtn = document.getElementById('geo');
      if (geoBtn) {
        geoBtn.addEventListener('click', function () {
          if (!navigator.geolocation) {
            alert('GPS haijasaidiwa na kivinjari.');
            return;
          }
          geoBtn.disabled = true;
          geoBtn.innerHTML = '<span>⏳</span> Inasubiri...';
          navigator.geolocation.getCurrentPosition(
            function (position) {
              const lat = position.coords.latitude;
              const lng = position.coords.longitude;
              setMarker(lat, lng);
              map.setView([lat, lng], 15);
              geoBtn.disabled = false;
              geoBtn.innerHTML = '<span>🎯</span> Tumia GPS';
            },
            function () {
              alert('GPS haijapatikana. Weka eneo kwa mkono kwenye ramani.');
              geoBtn.disabled = false;
              geoBtn.innerHTML = '<span>🎯</span> Tumia GPS';
            }
          );
        });
      }
    } else {
      setTimeout(function () {
        map.invalidateSize();
        const olat = parseFloat(latInput.value);
        const olng = parseFloat(lngInput.value);
        if (!isNaN(olat) && !isNaN(olng)) {
          map.setView([olat, olng], map.getZoom());
        }
      }, 200);
    }
  }

  function setMarker(lat, lng) {
    if (!map) return;
    if (marker) map.removeLayer(marker);
    marker = L.marker([lat, lng]).addTo(map);
    latInput.value = lat;
    lngInput.value = lng;
    syncLocationFlag();
    document.getElementById('location-status').classList.add('hidden');
    checkNearbyWorkers(lat, lng);
  }

  function fillSummary() {
    const title = document.getElementById('title').value.trim() || '—';
    const sel = document.getElementById('category_id');
    const cat = sel.options[sel.selectedIndex]?.text?.trim() || '—';
    const price = document.getElementById('price').value;
    const priceFmt = price ? Number(price).toLocaleString('sw-TZ') + ' TZS' : '—';
    const img = document.getElementById('image').files.length ? document.getElementById('image').files[0].name : 'Hakuna';
    const addr = document.getElementById('address_text').value.trim();
    const loc = isLocationSet
      ? (addr ? addr + ' (ramani)' : 'Imewekwa kwenye ramani')
      : 'Haijawekwa';
    document.getElementById('sumTitle').textContent = title;
    document.getElementById('sumCategory').textContent = cat;
    document.getElementById('sumPrice').textContent = priceFmt;
    document.getElementById('sumImage').textContent = img;
    document.getElementById('sumLocation').textContent = loc;
  }

  function validateStep(step) {
    if (step === 1) {
      const t = document.getElementById('title').value.trim();
      const c = document.getElementById('category_id').value;
      if (!t) { alert('Andika kichwa cha kazi.'); document.getElementById('title').focus(); return false; }
      if (!c) { alert('Chagua aina ya huduma.'); return false; }
      return true;
    }
    if (step === 2) {
      const p = parseInt(document.getElementById('price').value, 10);
      if (!p || p < 1000) { alert('Bei lazima iwe angalau TZS 1,000.'); document.getElementById('price').focus(); return false; }
      return true;
    }
    if (step === 3) {
      syncLocationFlag();
      if (!isLocationSet) {
        document.getElementById('location-status').classList.remove('hidden');
        document.getElementById('mapWrap')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
      }
      return true;
    }
    return true;
  }

  document.getElementById('btnNext').addEventListener('click', function () {
    if (!validateStep(currentStep)) return;
    if (currentStep < TOTAL) {
      currentStep++;
      updateStepperUI();
    }
  });

  document.getElementById('btnPrev').addEventListener('click', function () {
    if (currentStep > 1) {
      currentStep--;
      updateStepperUI();
    }
  });

  document.querySelectorAll('.step-pill').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const target = parseInt(btn.dataset.gotoStep, 10);
      if (target === currentStep) return;
      if (target < currentStep) {
        currentStep = target;
        updateStepperUI();
        return;
      }
      for (let s = currentStep; s < target; s++) {
        if (!validateStep(s)) return;
      }
      currentStep = target;
      updateStepperUI();
    });
  });

  form.addEventListener('submit', function (e) {
    syncLocationFlag();
    if (!isLocationSet) {
      e.preventDefault();
      currentStep = 3;
      updateStepperUI();
      document.getElementById('location-status').classList.remove('hidden');
      return false;
    }
  });

  function checkNearbyWorkers(lat, lng) {
    const modal = document.getElementById('workerCheckModal');
    const icon = document.getElementById('workerCheckIcon');
    const title = document.getElementById('workerCheckTitle');
    const message = document.getElementById('workerCheckMessage');
    const btnCancel = document.getElementById('btnCancelLocation');
    const btnConfirm = document.getElementById('btnConfirmLocation');

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    icon.textContent = '🔍';
    title.textContent = 'Inatafuta wafanyakazi...';
    message.textContent = 'Tafadhali subiri.';
    title.classList.remove('text-red-600', 'text-emerald-600');
    btnCancel.classList.add('hidden');
    btnConfirm.classList.add('hidden');

    fetch('/api/workers/nearby?lat=' + lat + '&lng=' + lng)
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data.success) {
          modal.classList.add('hidden');
          modal.classList.remove('flex');
          return;
        }
        const result = data.data;
        if (result.status === 'no_workers') {
          icon.textContent = '⚠️';
          title.textContent = 'Hakuna wafanyakazi eneo hili';
          message.textContent = result.message;
          title.classList.add('text-red-600');
          showNotification('error', 'Hakuna wafanyakazi', result.message);
        } else {
          icon.textContent = '✅';
          title.textContent = 'Wafanyakazi wamepatikana';
          message.textContent = result.message;
          title.classList.add('text-emerald-600');
          showNotification('success', 'Wafanyakazi wapo', result.message);
        }
        btnCancel.classList.remove('hidden');
        btnConfirm.classList.remove('hidden');
        btnCancel.onclick = function () {
          modal.classList.add('hidden');
          modal.classList.remove('flex');
        };
        btnConfirm.onclick = function () {
          modal.classList.add('hidden');
          modal.classList.remove('flex');
          document.getElementById('location-status').classList.add('hidden');
        };
      })
      .catch(function () {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      });
  }

  function showNotification(type, t, msg) {
    const notif = document.getElementById('workerCheckNotification');
    const icon = document.getElementById('workerCheckNotifIcon');
    const titleEl = document.getElementById('workerCheckNotifTitle');
    const msgEl = document.getElementById('workerCheckNotifMessage');
    notif.classList.remove('hidden');
    titleEl.textContent = t;
    msgEl.textContent = msg;
    if (type === 'success') {
      notif.className = 'border-b border-emerald-100 bg-emerald-50/90 px-5 py-4';
      icon.textContent = '✅';
      titleEl.className = 'text-[13px] font-bold text-emerald-800';
    } else {
      notif.className = 'border-b border-red-100 bg-red-50/90 px-5 py-4';
      icon.textContent = '⚠️';
      titleEl.className = 'text-[13px] font-bold text-red-800';
    }
  }

  window.handleImageSelect = function (event) {
    const file = event.target.files[0];
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) {
      alert('Picha ni kubwa sana (max 5MB).');
      event.target.value = '';
      return;
    }
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    if (!validTypes.includes(file.type)) {
      alert('Chagua picha JPEG, PNG, au WEBP.');
      event.target.value = '';
      return;
    }
    const reader = new FileReader();
    reader.onload = function (e) {
      document.getElementById('image-preview-img').src = e.target.result;
      document.getElementById('image-upload-placeholder').classList.add('hidden');
      document.getElementById('image-preview').classList.remove('hidden');
      const area = document.getElementById('image-upload-area');
      area.classList.add('border-emerald-300', 'bg-emerald-50/50');
      area.classList.remove('border-slate-200', 'bg-slate-50/80');
    };
    reader.readAsDataURL(file);
  };

  window.removeImage = function () {
    document.getElementById('image').value = '';
    document.getElementById('image-upload-placeholder').classList.remove('hidden');
    document.getElementById('image-preview').classList.add('hidden');
    const area = document.getElementById('image-upload-area');
    area.classList.remove('border-emerald-300', 'bg-emerald-50/50');
    area.classList.add('border-slate-200', 'bg-slate-50/80');
  };

  window.handleDragOver = function (event) {
    event.preventDefault();
    event.stopPropagation();
    const area = document.getElementById('image-upload-area');
    area.classList.add('border-brand-400', 'bg-brand-50/40');
  };

  window.handleDragLeave = function (event) {
    event.preventDefault();
    event.stopPropagation();
    const area = document.getElementById('image-upload-area');
    if (!document.getElementById('image').files.length) {
      area.classList.remove('border-brand-400', 'bg-brand-50/40');
    }
  };

  window.handleDrop = function (event) {
    event.preventDefault();
    event.stopPropagation();
    const area = document.getElementById('image-upload-area');
    area.classList.remove('border-brand-400', 'bg-brand-50/40');
    const files = event.dataTransfer.files;
    if (files.length) {
      document.getElementById('image').files = files;
      window.handleImageSelect({ target: { files: files } });
    }
  };

  updateStepperUI();
})();
</script>
@endpush
@endsection
