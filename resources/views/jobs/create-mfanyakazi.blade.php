@extends('layouts.app')
@section('title', 'Chapisha kazi — mfanyakazi')

@section('content')
<div class="flex min-h-screen bg-slate-50">
  @include('components.user-sidebar')

  <main class="tp-main w-full min-w-0 flex-1 p-4 pt-16 sm:p-5 lg:p-6 lg:pt-6">
    <div class="w-full max-w-[min(100%,1400px)] space-y-4">

      <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0">
          <h1 class="text-lg font-bold tracking-tight text-slate-900 sm:text-xl">Chapisha huduma yako</h1>
          <p class="mt-0.5 max-w-2xl text-[12px] leading-relaxed text-slate-500">
            Jaza hatua kadri unavyoendelea — muundo unatumia nafasi nzima ya skrini ili uandike kwa urahisi.
          </p>
        </div>
        <div class="flex shrink-0 flex-wrap gap-2">
          <a href="{{ route('feed') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-[12px] font-medium text-slate-700 shadow-sm hover:bg-slate-50">Tazama kazi</a>
          <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-3 py-2 text-[12px] font-semibold text-white shadow-sm hover:bg-brand-700">Dashibodi</a>
        </div>
      </div>

      {{-- Stepper --}}
      <nav class="rounded-2xl border border-slate-200 bg-white px-3 py-3 shadow-sm sm:px-5" aria-label="Hatua za fomu">
        <ol class="flex flex-wrap items-center gap-2 sm:gap-0" id="mfanyakazi-stepper">
          @php
            $steps = [
              ['n' => 1, 'label' => 'Huduma'],
              ['n' => 2, 'label' => 'Bei & simu'],
              ['n' => 3, 'label' => 'Eneo'],
              ['n' => 4, 'label' => 'Hakiki'],
            ];
          @endphp
          @foreach($steps as $i => $s)
            <li class="flex min-w-0 items-center {{ $i > 0 ? 'sm:flex-1' : '' }}">
              @if($i > 0)
                <div class="mx-1 hidden h-px min-w-[12px] flex-1 bg-slate-200 sm:block" data-step-connector="{{ $s['n'] }}" aria-hidden="true"></div>
              @endif
              <button
                type="button"
                class="mfanyakazi-step-tab flex items-center gap-2 rounded-xl px-2.5 py-2 text-left transition sm:px-3 {{ $i === 0 ? 'bg-brand-50 ring-1 ring-brand-200' : 'hover:bg-slate-50' }}"
                data-go-step="{{ $s['n'] }}"
                aria-current="{{ $i === 0 ? 'step' : 'false' }}"
              >
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-200 text-[11px] font-bold text-slate-700 tabular-nums" data-step-circle="{{ $s['n'] }}">{{ $s['n'] }}</span>
                <span class="hidden text-[12px] font-semibold text-slate-800 sm:inline">{{ $s['label'] }}</span>
              </button>
            </li>
          @endforeach
        </ol>
        <p class="mt-2 text-[11px] text-slate-500 sm:hidden" id="mfanyakazi-step-mobile-label">Hatua 1 / 4 — Huduma</p>
      </nav>

      <div class="grid gap-5 lg:grid-cols-12 lg:items-start">
        <div class="min-w-0 space-y-4 lg:col-span-8 xl:col-span-8">

          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
            @if($errors->any())
              <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-[12px] text-red-900">
                <p class="font-semibold">Angalia makosa</p>
                <ul class="mt-2 list-disc space-y-0.5 pl-4">
                  @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <form id="mfanyakazi-create-form" method="post" action="{{ route('jobs.store-mfanyakazi') }}" class="space-y-0" novalidate>
              @csrf

              {{-- Hatua 1 --}}
              <div class="mfanyakazi-step-panel space-y-4" data-step-panel="1">
                <div>
                  <h2 class="text-[13px] font-bold text-slate-900">Hatua 1 — Maelezo ya huduma</h2>
                  <p class="mt-0.5 text-[11px] text-slate-500">Kichwa, aina, na maelezo kamili (angalau herufi 20).</p>
                </div>
                <div>
                  <label for="title" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-slate-600">Kichwa cha huduma</label>
                  <input
                    type="text"
                    id="title"
                    name="title"
                    maxlength="120"
                    placeholder="Mf. Usafi wa nyumba, ujenzi wa ukuta"
                    value="{{ old('title') }}"
                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-[13px] text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"
                  >
                </div>
                <div>
                  <label for="category_id" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-slate-600">Aina ya huduma</label>
                  <select
                    name="category_id"
                    id="category_id"
                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-[13px] text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"
                  >
                    <option value="">Chagua aina</option>
                    @foreach($categories as $c)
                      <option value="{{ $c->id }}" {{ (string) old('category_id') === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                  </select>
                </div>
                <div>
                  <label for="description" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-slate-600">Maelezo</label>
                  <textarea
                    id="description"
                    name="description"
                    rows="6"
                    placeholder="Eleza huduma, uzoefu, na mteja anapaswa kukutarajia nini…"
                    class="min-h-[140px] w-full resize-y rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-[13px] leading-relaxed text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"
                  >{{ old('description') }}</textarea>
                </div>
              </div>

              {{-- Hatua 2 --}}
              <div class="mfanyakazi-step-panel hidden space-y-4" data-step-panel="2">
                <div>
                  <h2 class="text-[13px] font-bold text-slate-900">Hatua 2 — Bei na mawasiliano</h2>
                  <p class="mt-0.5 text-[11px] text-slate-500">Weka bei na namba ya simu wateja watakayopigia.</p>
                </div>
                <div>
                  <label for="price" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-slate-600">Bei (TZS)</label>
                  <input
                    type="number"
                    id="price"
                    name="price"
                    min="1000"
                    step="500"
                    placeholder="Mf. 25000"
                    value="{{ old('price') }}"
                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-[13px] tabular-nums text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"
                  >
                  <p class="mt-1 text-[11px] text-slate-500">Chini ya TZS 1,000 hairuhusiwi.</p>
                </div>
                <div>
                  <label for="phone" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-slate-600">Namba ya simu</label>
                  <input
                    type="tel"
                    id="phone"
                    name="phone"
                    placeholder="07xxxxxxxx au 2557xxxxxxxx"
                    value="{{ old('phone', auth()->user()->phone) }}"
                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-[13px] text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"
                  >
                  <p class="mt-1 text-[11px] text-slate-500">Muundo: 06/07xxxxxxxx au 2556/2557xxxxxxxx.</p>
                </div>
              </div>

              {{-- Hatua 3 --}}
              <div class="mfanyakazi-step-panel hidden space-y-4" data-step-panel="3">
                <div>
                  <h2 class="text-[13px] font-bold text-slate-900">Hatua 3 — Eneo la huduma</h2>
                  <p class="mt-0.5 text-[11px] text-slate-500">Bonyeza ramani au tumia GPS. Lazima kuwe na alama kabla ya kutuma.</p>
                </div>
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 shadow-inner">
                  <div id="map" class="h-64 w-full sm:h-80 lg:h-[min(420px,50vh)]"></div>
                  <div class="space-y-3 border-t border-slate-200 bg-white p-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                      <button type="button" id="geo" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-emerald-600 px-3 py-2 text-[12px] font-semibold text-white shadow-sm hover:bg-emerald-700">
                        Tumia GPS
                      </button>
                      <input
                        type="text"
                        name="address_text"
                        placeholder="Maelezo ya eneo (hiari)"
                        value="{{ old('address_text') }}"
                        class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-[13px] text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"
                      >
                    </div>
                  </div>
                </div>
                <input type="hidden" name="lat" id="lat" value="{{ old('lat') }}">
                <input type="hidden" name="lng" id="lng" value="{{ old('lng') }}">
              </div>

              {{-- Hatua 4 --}}
              <div class="mfanyakazi-step-panel hidden space-y-4" data-step-panel="4">
                <div>
                  <h2 class="text-[13px] font-bold text-slate-900">Hatua 4 — Hakiki na tuma</h2>
                  <p class="mt-0.5 text-[11px] text-slate-500">Hakikisha maelezo ni sahihi. Utaendelea kwa malipo ya TZS 2,000 ya chapisho.</p>
                </div>
                <dl class="space-y-3 rounded-xl border border-slate-100 bg-slate-50/80 p-4 text-[12px]">
                  <div>
                    <dt class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Kichwa</dt>
                    <dd class="mt-0.5 font-semibold text-slate-900" id="summary-title">—</dd>
                  </div>
                  <div>
                    <dt class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Aina</dt>
                    <dd class="mt-0.5 text-slate-800" id="summary-category">—</dd>
                  </div>
                  <div>
                    <dt class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Maelezo</dt>
                    <dd class="mt-0.5 whitespace-pre-wrap text-slate-700" id="summary-description">—</dd>
                  </div>
                  <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                      <dt class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Bei</dt>
                      <dd class="mt-0.5 font-bold tabular-nums text-emerald-700" id="summary-price">—</dd>
                    </div>
                    <div>
                      <dt class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Simu</dt>
                      <dd class="mt-0.5 font-medium text-slate-800" id="summary-phone">—</dd>
                    </div>
                  </div>
                  <div>
                    <dt class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Eneo</dt>
                    <dd class="mt-0.5 text-slate-700" id="summary-location">—</dd>
                  </div>
                </dl>
              </div>

              <div class="mt-6 flex flex-col-reverse gap-2 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-center text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Rudi dashboard</a>
                <div class="flex flex-wrap justify-end gap-2">
                  <button type="button" id="mfanyakazi-btn-prev" class="hidden inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-[12px] font-semibold text-slate-700 hover:bg-slate-50">← Nyuma</button>
                  <button type="button" id="mfanyakazi-btn-next" class="inline-flex items-center justify-center rounded-xl bg-brand-600 px-4 py-2.5 text-[12px] font-bold text-white shadow-sm hover:bg-brand-700">Endelea →</button>
                  <button type="submit" id="mfanyakazi-btn-submit" class="hidden inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-[12px] font-bold text-white shadow-sm hover:bg-emerald-700">Malipo na chapisha</button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <aside class="min-w-0 lg:col-span-4 xl:col-span-4">
          <div class="sticky top-20 space-y-3">
            <section class="rounded-2xl border border-sky-200 bg-gradient-to-br from-sky-50 to-white p-4 shadow-sm sm:p-5">
              <div class="flex items-start gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sky-600 text-[11px] font-bold text-white shadow-sm">TZS</div>
                <div class="min-w-0 flex-1">
                  <h2 class="text-[13px] font-bold text-slate-900">Malipo ya kuchapisha</h2>
                  <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                    <div class="rounded-xl border border-slate-200/80 bg-white/90 px-3 py-2.5">
                      <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Ada ya chapisho</p>
                      <p class="mt-0.5 text-[15px] font-extrabold tabular-nums text-slate-900">2,000</p>
                    </div>
                    <div class="rounded-xl border border-slate-200/80 bg-white/90 px-3 py-2.5">
                      <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Salio lako</p>
                      <p class="mt-0.5 text-[15px] font-extrabold tabular-nums text-emerald-700">{{ number_format(optional(auth()->user()->wallet)->balance ?? 0) }}</p>
                    </div>
                  </div>
                  <p class="mt-3 rounded-lg border border-amber-200 bg-amber-50/90 px-3 py-2 text-[11px] leading-relaxed text-amber-950">
                    <span class="font-semibold text-amber-900">Kumbuka:</span> TZS 2,000 inakatwa kabla ya kuchapisha. Salio halitoshi → ClickPesa.
                  </p>
                </div>
              </div>
            </section>
            <p class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-[11px] leading-relaxed text-slate-600">
              Hatua <span id="mfanyakazi-progress-num" class="font-bold text-slate-900">1</span> kati ya 4. Unaweza kurudi nyuma kubadilisha chochote.
            </p>
          </div>
        </aside>
      </div>
    </div>
  </main>
</div>

@php
  $mfanyakaziFormStep = 1;
  if ($errors->any()) {
    if ($errors->has('lat') || $errors->has('lng')) {
      $mfanyakaziFormStep = 3;
    } elseif ($errors->has('price') || $errors->has('phone')) {
      $mfanyakaziFormStep = 2;
    } else {
      $mfanyakaziFormStep = 1;
    }
  }
@endphp

@push('scripts')
<script>
  (function () {
    const TOTAL = 4;
    const INITIAL_STEP = {{ (int) $mfanyakaziFormStep }};
    const STEP_LABELS = ['Huduma', 'Bei & simu', 'Eneo', 'Hakiki'];
    let currentStep = 1;
    let map;
    let marker;
    let isLocationSet = false;

    const form = document.getElementById('mfanyakazi-create-form');
    const latEl = document.getElementById('lat');
    const lngEl = document.getElementById('lng');

    function initMap() {
      if (map) return;
      map = L.map('map').setView([-6.7924, 39.2083], 12);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap',
      }).addTo(map);

      if (latEl.value && lngEl.value) {
        const la = parseFloat(latEl.value);
        const ln = parseFloat(lngEl.value);
        if (!isNaN(la) && !isNaN(ln)) {
          marker = L.marker([la, ln]).addTo(map);
          map.setView([la, ln], 14);
          isLocationSet = true;
        }
      }

      function setMarker(lat, lng) {
        if (marker) map.removeLayer(marker);
        marker = L.marker([lat, lng]).addTo(map);
        latEl.value = lat;
        lngEl.value = lng;
        isLocationSet = true;
      }

      map.on('click', function (e) {
        setMarker(e.latlng.lat, e.latlng.lng);
      });

      const geoBtn = document.getElementById('geo');
      geoBtn.addEventListener('click', function () {
        if (!navigator.geolocation) {
          alert('GPS haijasaidiwa na kivinjari hiki.');
          return;
        }
        const original = geoBtn.innerHTML;
        geoBtn.innerHTML = 'Inasubiri GPS…';
        geoBtn.disabled = true;
        navigator.geolocation.getCurrentPosition(
          function (position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            setMarker(lat, lng);
            map.setView([lat, lng], 15);
            geoBtn.innerHTML = 'GPS imewekwa';
            setTimeout(function () {
              geoBtn.innerHTML = original;
              geoBtn.disabled = false;
            }, 1600);
          },
          function () {
            alert('GPS haijapatikana. Weka eneo kwa kubonyeza ramani.');
            geoBtn.innerHTML = original;
            geoBtn.disabled = false;
          }
        );
      });
    }

    function invalidateMapLater() {
      if (!map) return;
      setTimeout(function () {
        map.invalidateSize();
      }, 120);
      setTimeout(function () {
        map.invalidateSize();
      }, 400);
    }

    function setMarkerFromOld() {
      if (!latEl.value || !lngEl.value || !map) return;
      const la = parseFloat(latEl.value);
      const ln = parseFloat(lngEl.value);
      if (isNaN(la) || isNaN(ln)) return;
      if (marker) map.removeLayer(marker);
      marker = L.marker([la, ln]).addTo(map);
      map.setView([la, ln], 14);
      isLocationSet = true;
    }

    function showStep(n) {
      currentStep = Math.min(Math.max(1, n), TOTAL);
      document.querySelectorAll('[data-step-panel]').forEach(function (el) {
        const sn = parseInt(el.getAttribute('data-step-panel'), 10);
        el.classList.toggle('hidden', sn !== currentStep);
      });

      document.querySelectorAll('.mfanyakazi-step-tab').forEach(function (btn) {
        const sn = parseInt(btn.getAttribute('data-go-step'), 10);
        const on = sn === currentStep;
        const circle = btn.querySelector('[data-step-circle]');
        btn.setAttribute('aria-current', on ? 'step' : 'false');
        btn.classList.toggle('bg-brand-50', on);
        btn.classList.toggle('ring-1', on);
        btn.classList.toggle('ring-brand-200', on);
        if (circle) {
          circle.classList.toggle('bg-brand-600', on);
          circle.classList.toggle('text-white', on);
          circle.classList.toggle('bg-slate-200', !on);
          circle.classList.toggle('text-slate-700', !on);
        }
      });

      for (let s = 2; s <= TOTAL; s++) {
        const line = document.querySelector('[data-step-connector="' + s + '"]');
        if (line) line.classList.toggle('bg-brand-300', currentStep >= s);
        if (line) line.classList.toggle('bg-slate-200', currentStep < s);
      }

      const mobileLbl = document.getElementById('mfanyakazi-step-mobile-label');
      if (mobileLbl) {
        mobileLbl.textContent = 'Hatua ' + currentStep + ' / ' + TOTAL + ' — ' + STEP_LABELS[currentStep - 1];
      }
      const prog = document.getElementById('mfanyakazi-progress-num');
      if (prog) prog.textContent = String(currentStep);

      const prev = document.getElementById('mfanyakazi-btn-prev');
      const next = document.getElementById('mfanyakazi-btn-next');
      const submit = document.getElementById('mfanyakazi-btn-submit');
      prev.classList.toggle('hidden', currentStep <= 1);
      next.classList.toggle('hidden', currentStep >= TOTAL);
      submit.classList.toggle('hidden', currentStep < TOTAL);

      if (currentStep === 3) {
        initMap();
        invalidateMapLater();
        setMarkerFromOld();
      }

      if (currentStep === 4) fillSummary();
    }

    function fillSummary() {
      const title = document.getElementById('title').value.trim() || '—';
      const sel = document.getElementById('category_id');
      const cat = sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : '—';
      const desc = document.getElementById('description').value.trim() || '—';
      const price = document.getElementById('price').value;
      const phone = document.getElementById('phone').value.trim() || '—';
      const addr = form.querySelector('[name="address_text"]').value.trim();

      document.getElementById('summary-title').textContent = title;
      document.getElementById('summary-category').textContent = cat;
      document.getElementById('summary-description').textContent = desc;
      document.getElementById('summary-price').textContent = price ? Number(price).toLocaleString() + ' TZS' : '—';
      document.getElementById('summary-phone').textContent = phone;

      let loc = '—';
      if (latEl.value && lngEl.value) {
        loc = parseFloat(latEl.value).toFixed(5) + ', ' + parseFloat(lngEl.value).toFixed(5);
        if (addr) loc += ' · ' + addr;
      } else if (addr) loc = addr;
      document.getElementById('summary-location').textContent = loc;
    }

    const phoneRe = /^(0[6-7]\d{8}|255[6-7]\d{8})$/;

    function validateStep(step) {
      if (step === 1) {
        const t = document.getElementById('title').value.trim();
        const c = document.getElementById('category_id').value;
        const d = document.getElementById('description').value.trim();
        if (!t) {
          alert('Weka kichwa cha huduma.');
          return false;
        }
        if (!c) {
          alert('Chagua aina ya huduma.');
          return false;
        }
        if (d.length < 20) {
          alert('Maelezo lazima yawe angalau herufi 20.');
          return false;
        }
        return true;
      }
      if (step === 2) {
        const p = parseInt(document.getElementById('price').value, 10);
        const ph = document.getElementById('phone').value.replace(/\s/g, '');
        if (!p || p < 1000) {
          alert('Weka bei ya angalau TZS 1,000.');
          return false;
        }
        if (!phoneRe.test(ph)) {
          alert('Weka namba sahihi: 06/07xxxxxxxx au 2556/2557xxxxxxxx.');
          return false;
        }
        return true;
      }
      if (step === 3) {
        if (!isLocationSet || !latEl.value || !lngEl.value) {
          alert('Weka eneo kwenye ramani (bonyeza ramani au Tumia GPS).');
          return false;
        }
        return true;
      }
      return true;
    }

    document.getElementById('mfanyakazi-btn-next').addEventListener('click', function () {
      if (!validateStep(currentStep)) return;
      showStep(currentStep + 1);
    });

    document.getElementById('mfanyakazi-btn-prev').addEventListener('click', function () {
      showStep(currentStep - 1);
    });

    document.querySelectorAll('.mfanyakazi-step-tab').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const target = parseInt(btn.getAttribute('data-go-step'), 10);
        if (target === currentStep) return;
        if (target < currentStep) {
          showStep(target);
          return;
        }
        let s = currentStep;
        while (s < target) {
          if (!validateStep(s)) return;
          s++;
        }
        showStep(target);
      });
    });

    form.addEventListener('submit', function (e) {
      if (currentStep !== TOTAL) {
        e.preventDefault();
        return;
      }
      if (!validateStep(3)) {
        e.preventDefault();
        showStep(3);
        return;
      }
      if (!isLocationSet || !latEl.value || !lngEl.value) {
        e.preventDefault();
        alert('Weka eneo la huduma kwenye ramani.');
        showStep(3);
      }
    });

    showStep(INITIAL_STEP);

    if (latEl.value && lngEl.value) {
      const la = parseFloat(latEl.value);
      const ln = parseFloat(lngEl.value);
      if (!isNaN(la) && !isNaN(ln)) isLocationSet = true;
    }
  })();
</script>
@endpush
@endsection
