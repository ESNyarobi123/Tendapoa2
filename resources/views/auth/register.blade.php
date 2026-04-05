<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Jisajili — TendaPoa</title>
    <x-auth-head-assets />
</head>
@php
    $wizardStep = 1;
    if ($errors->any()) {
        if ($errors->has('name') || $errors->has('email')) {
            $wizardStep = 1;
        } elseif ($errors->has('password')) {
            $wizardStep = 2;
        } elseif ($errors->has('role')) {
            $wizardStep = 3;
        } else {
            $wizardStep = 4;
        }
    }
@endphp
<body class="min-h-full bg-slate-100 font-sans text-sm text-slate-800 antialiased">
    <a href="{{ route('home') }}" class="absolute left-4 top-4 z-10 inline-flex min-h-[44px] min-w-[44px] items-center gap-1.5 rounded-lg px-3 py-2 text-[13px] font-medium text-slate-600 transition hover:bg-white hover:text-brand-700">
        ← Rudi Nyumbani
    </a>

    <div class="mx-auto max-w-5xl px-4 py-16 pt-20 md:flex md:min-h-screen md:items-center md:py-12">
        {{-- Panel ya brand: inaonekana kutoka md+ (iPad portrait pamoja) --}}
        <div class="mb-6 hidden w-full flex-col justify-center rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 p-8 text-white shadow-sm sm:p-10 md:mb-0 md:mr-0 md:flex md:w-[38%] md:rounded-r-none">
            <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-xl bg-white/15 text-lg font-bold">T</div>
            <h1 class="text-2xl font-bold tracking-tight">Fungua akaunti</h1>
            <p id="brandStepHint" class="mt-3 text-[13px] leading-relaxed text-white/85"></p>
            <ul id="brandBullets" class="mt-6 space-y-2 text-[12px] text-white/80">
                <li class="brand-bullet flex gap-2" data-for-step="1"><span class="text-white/60">1.</span> Jina na barua pepe</li>
                <li class="brand-bullet flex gap-2" data-for-step="2"><span class="text-white/60">2.</span> Neno siri salama</li>
                <li class="brand-bullet flex gap-2" data-for-step="3"><span class="text-white/60">3.</span> Chagua nafasi yako</li>
                <li class="brand-bullet flex gap-2" data-for-step="4"><span class="text-white/60">4.</span> Simu na eneo (hiari)</li>
            </ul>
        </div>

        <div class="w-full rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm sm:p-8 md:max-h-[calc(100vh-4rem)] md:overflow-y-auto md:rounded-l-none md:border-l-0 md:min-h-0">
            <div class="mb-5">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-brand-700">Jisajili</p>
                <h2 id="stepTitle" class="mt-1 text-xl font-semibold text-slate-900">Hatua 1 kati ya 4</h2>
                <p id="stepSubtitle" class="mt-1 text-[13px] text-slate-500"></p>
            </div>

            {{-- Maendeleo --}}
            <nav class="mb-8" aria-label="Hatua za jisajili">
                <ol class="flex items-center gap-1 sm:gap-2" id="stepIndicators">
                    @for ($s = 1; $s <= 4; $s++)
                        <li class="flex flex-1 items-center gap-1 sm:gap-2 min-w-0">
                            <button type="button"
                                class="step-dot flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 text-[12px] font-bold transition sm:h-10 sm:w-10 {{ $s <= $wizardStep ? 'border-brand-600 bg-brand-600 text-white' : 'border-slate-200 bg-white text-slate-400' }} {{ $s < $wizardStep ? 'ring-2 ring-brand-200' : '' }}"
                                data-step="{{ $s }}"
                                aria-current="{{ $s === $wizardStep ? 'step' : 'false' }}"
                                @if($s > $wizardStep) disabled @endif
                            >{{ $s }}</button>
                            @if($s < 4)
                                <div class="step-connector h-0.5 flex-1 min-w-[4px] rounded-full {{ $s < $wizardStep ? 'bg-brand-500' : 'bg-slate-200' }}" aria-hidden="true"></div>
                            @endif
                        </li>
                    @endfor
                </ol>
                <p class="mt-2 text-center text-[12px] text-slate-500 sm:hidden" id="stepMobileLabel"></p>
            </nav>

            @if($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-[13px] text-red-800" role="alert">
                    <p class="font-semibold">Angalia makosa</p>
                    <ul class="mt-2 list-inside list-disc space-y-0.5">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div id="stepError" class="mb-4 hidden rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-[13px] text-amber-900 outline-none" role="status" aria-live="polite" tabindex="-1"></div>

            <form method="post" action="{{ route('register.post') }}" id="registerForm" class="space-y-5" novalidate>
                @csrf

                {{-- Hatua 1: Wasiliano --}}
                <div class="step-panel space-y-4" data-step="1" @if($wizardStep !== 1) hidden @endif>
                    <div>
                        <label for="name" class="mb-1.5 block text-[13px] font-medium text-slate-700">Jina kamili</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required autocomplete="name"
                            @if($wizardStep === 1) autofocus @endif
                            class="block w-full rounded-lg border border-slate-200 px-3 py-2.5 text-[13px] text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                            placeholder="Jina lako kamili">
                    </div>
                    <div>
                        <label for="email" class="mb-1.5 block text-[13px] font-medium text-slate-700">Barua pepe</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                            class="block w-full rounded-lg border border-slate-200 px-3 py-2.5 text-[13px] text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                            placeholder="jina@example.com">
                        <p class="mt-1.5 text-[12px] text-slate-500">Tutatumia barua hii kwa arifa na kuingia akaunti.</p>
                    </div>
                </div>

                {{-- Hatua 2: Neno siri --}}
                <div class="step-panel space-y-4" data-step="2" @if($wizardStep !== 2) hidden @endif>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="password" class="mb-1.5 block text-[13px] font-medium text-slate-700">Neno siri</label>
                            <div class="relative">
                                <input type="password" id="password" name="password" required autocomplete="new-password" minlength="8"
                                    @if($wizardStep === 2) autofocus @endif
                                    class="block w-full rounded-lg border border-slate-200 px-3 py-2.5 pr-11 text-[13px] text-slate-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                                    placeholder="Angalau herufi 8">
                                <button type="button" class="toggle-pw absolute right-2 top-1/2 -translate-y-1/2 rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600" data-target="password" aria-label="Onyesha neno siri">👁</button>
                            </div>
                            <div class="mt-2 h-1 overflow-hidden rounded-full bg-slate-100">
                                <div id="strengthFill" class="h-full w-0 rounded-full transition-all duration-300"></div>
                            </div>
                            <p id="strengthLabel" class="mt-1 text-[11px] text-slate-500"></p>
                        </div>
                        <div>
                            <label for="password_confirmation" class="mb-1.5 block text-[13px] font-medium text-slate-700">Rudia neno siri</label>
                            <div class="relative">
                                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" minlength="8"
                                    class="block w-full rounded-lg border border-slate-200 px-3 py-2.5 pr-11 text-[13px] text-slate-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                                    placeholder="Rudia neno siri">
                                <button type="button" class="toggle-pw absolute right-2 top-1/2 -translate-y-1/2 rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600" data-target="password_confirmation" aria-label="Onyesha neno siri">👁</button>
                            </div>
                            <p id="pwMatchHint" class="mt-2 text-[12px] text-slate-500"></p>
                        </div>
                    </div>
                    <ul class="rounded-xl border border-slate-100 bg-slate-50/90 px-3 py-2.5 text-[12px] text-slate-600">
                        <li>• Angalau herufi 8</li>
                        <li>• Changanya herufi, nambari au alama kwa usalama bora</li>
                    </ul>
                </div>

                {{-- Hatua 3: Jukumu --}}
                <div class="step-panel space-y-4" data-step="3" @if($wizardStep !== 3) hidden @endif>
                    <p class="text-[13px] font-medium text-slate-700">Wewe ni nani kwenye TendaPoa?</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="role-card flex cursor-pointer items-start gap-3 rounded-xl border-2 p-4 transition {{ old('role', 'muhitaji') === 'muhitaji' ? 'border-brand-500 bg-brand-50/50 shadow-sm' : 'border-slate-200 hover:border-slate-300' }}">
                            <input type="radio" name="role" value="muhitaji" class="mt-1 text-brand-600 focus:ring-brand-500" {{ old('role', 'muhitaji') === 'muhitaji' ? 'checked' : '' }} required>
                            <span>
                                <span class="block text-[13px] font-semibold text-slate-900">Muhitaji</span>
                                <span class="text-[12px] text-slate-500">Ninatafuta wafanyakazi kwa kazi zangu</span>
                            </span>
                        </label>
                        <label class="role-card flex cursor-pointer items-start gap-3 rounded-xl border-2 p-4 transition {{ old('role') === 'mfanyakazi' ? 'border-brand-500 bg-brand-50/50 shadow-sm' : 'border-slate-200 hover:border-slate-300' }}">
                            <input type="radio" name="role" value="mfanyakazi" class="mt-1 text-brand-600 focus:ring-brand-500" {{ old('role') === 'mfanyakazi' ? 'checked' : '' }}>
                            <span>
                                <span class="block text-[13px] font-semibold text-slate-900">Mfanyakazi</span>
                                <span class="text-[12px] text-slate-500">Ninafanya kazi na kuomba kazi</span>
                            </span>
                        </label>
                    </div>
                </div>

                {{-- Hatua 4: Ziada --}}
                <div class="step-panel space-y-4" data-step="4" @if($wizardStep !== 4) hidden @endif>
                    <div>
                        <label for="phone" class="mb-1.5 block text-[13px] font-medium text-slate-700">Nambari ya simu <span class="font-normal text-slate-400">(hiari)</span></label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" inputmode="numeric" autocomplete="tel"
                            class="block w-full rounded-lg border border-slate-200 px-3 py-2.5 text-[13px] text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                            placeholder="07xxxxxxxx au 2557xxxxxxxx">
                        <p class="mt-1 text-[12px] text-slate-500">Mfumo: 07xxxxxxxx au 2557xxxxxxxx</p>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                        <p class="mb-1 text-[13px] font-medium text-slate-800">Eneo la GPS <span class="font-normal text-slate-400">(hiari)</span></p>
                        <p class="mb-3 text-[12px] text-slate-500">Tunasaidia kuonyesha kazi karibu na wewe. Unaweza kuruka hatua hii.</p>
                        <div class="grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
                            <div>
                                <label for="lat" class="mb-1 block text-[11px] font-medium text-slate-600">Latitudo</label>
                                <input type="text" id="lat" name="lat" value="{{ old('lat') }}" inputmode="decimal"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[13px] focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="-6.xxx">
                            </div>
                            <div>
                                <label for="lng" class="mb-1 block text-[11px] font-medium text-slate-600">Longitudo</label>
                                <input type="text" id="lng" name="lng" value="{{ old('lng') }}" inputmode="decimal"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[13px] focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="39.xxx">
                            </div>
                            <button type="button" id="gps" class="rounded-lg bg-brand-600 px-4 py-2.5 text-[13px] font-semibold text-white shadow-sm transition hover:bg-brand-700 sm:shrink-0 min-h-[44px]">
                                Tumia eneo langu
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <button type="button" id="btnPrev" class="hidden min-h-[44px] rounded-lg border border-slate-200 px-4 py-2.5 text-[13px] font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300">
                        ← Nyuma
                    </button>
                    <div class="flex flex-1 justify-end gap-3 sm:justify-end">
                        <button type="button" id="btnNext" class="min-h-[44px] w-full rounded-lg bg-brand-600 px-4 py-2.5 text-[13px] font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 sm:w-auto sm:min-w-[140px]">
                            Endelea →
                        </button>
                        <button type="submit" id="btnSubmit" class="hidden min-h-[44px] w-full rounded-lg bg-brand-600 px-4 py-2.5 text-[13px] font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 sm:w-auto sm:min-w-[180px]">
                            Maliza jisajili
                        </button>
                    </div>
                </div>
            </form>

            <p class="mt-6 border-t border-slate-100 pt-6 text-center text-[13px] text-slate-600">
                Tayari una akaunti?
                <a href="{{ route('login') }}" class="font-semibold text-brand-700 hover:text-brand-800">Ingia</a>
            </p>
        </div>
    </div>

    <script>
(function () {
    const TOTAL = 4;
    let current = {{ (int) $wizardStep }};
    const form = document.getElementById('registerForm');
    const btnPrev = document.getElementById('btnPrev');
    const btnNext = document.getElementById('btnNext');
    const btnSubmit = document.getElementById('btnSubmit');
    const stepTitle = document.getElementById('stepTitle');
    const stepSubtitle = document.getElementById('stepSubtitle');
    const brandHint = document.getElementById('brandStepHint');
    const mobileLabel = document.getElementById('stepMobileLabel');

    const copy = {
        1: {
            title: 'Taarifa za wasiliano',
            subtitle: 'Jina lako na barua pepe ya kuingia.',
            brand: 'Anza kwa taarifa za msingi. Hatua hii inachukua sekunde chache tu.'
        },
        2: {
            title: 'Unda neno siri',
            subtitle: 'Chagua neno siri lenye nguvu na thibitisha.',
            brand: 'Akaunti yako inalindwa kwa neno siri salama.'
        },
        3: {
            title: 'Chagua nafasi yako',
            subtitle: 'Muhitaji au mfanyakazi — unaweza kubadilisha baadaye ikiwa inahitajika.',
            brand: 'Tunabadilisha dashibodi kulingana na jukumu lako.'
        },
        4: {
            title: 'Maelezo ya ziada',
            subtitle: 'Simu na eneo ni hiari — lakini zinasaidia uzoefu bora.',
            brand: 'Karibu kumaliza! Baada ya hii utaingia moja kwa moja.'
        }
    };

    const phoneRe = /^(0[6-7]\d{8}|255[6-7]\d{8})$/;
    const stepErrorEl = document.getElementById('stepError');

    function showStepError(msg) {
        stepErrorEl.textContent = msg;
        stepErrorEl.classList.remove('hidden');
        stepErrorEl.focus();
    }

    function clearStepError() {
        stepErrorEl.textContent = '';
        stepErrorEl.classList.add('hidden');
    }

    function showStep(n) {
        current = Math.min(Math.max(1, n), TOTAL);
        document.querySelectorAll('.step-panel').forEach(function (el) {
            const s = parseInt(el.getAttribute('data-step'), 10);
            el.hidden = s !== current;
        });

        const c = copy[current];
        stepTitle.textContent = 'Hatua ' + current + ' kati ya ' + TOTAL + ': ' + c.title;
        stepSubtitle.textContent = c.subtitle;
        brandHint.textContent = c.brand;
        mobileLabel.textContent = c.title + ' (' + current + '/' + TOTAL + ')';

        btnPrev.classList.toggle('hidden', current === 1);
        btnNext.classList.toggle('hidden', current === TOTAL);
        btnSubmit.classList.toggle('hidden', current !== TOTAL);

        document.querySelectorAll('.step-dot').forEach(function (btn) {
            const s = parseInt(btn.getAttribute('data-step'), 10);
            const done = s < current;
            const active = s === current;
            btn.disabled = s > current;
            btn.setAttribute('aria-current', active ? 'step' : 'false');
            btn.className = 'step-dot flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 text-[12px] font-bold transition sm:h-10 sm:w-10 ' +
                (active ? 'border-brand-600 bg-brand-600 text-white ring-2 ring-brand-200 ring-offset-2 ring-offset-white ' : '') +
                (done ? 'border-brand-600 bg-brand-600 text-white ' : '') +
                (!active && !done ? 'border-slate-200 bg-white text-slate-400 ' : '');
        });

        const connectors = document.querySelectorAll('.step-connector');
        connectors.forEach(function (line, i) {
            line.classList.toggle('bg-brand-500', i < current - 1);
            line.classList.toggle('bg-slate-200', i >= current - 1);
        });

        document.querySelectorAll('.brand-bullet').forEach(function (li) {
            const forStep = parseInt(li.getAttribute('data-for-step'), 10);
            li.classList.toggle('opacity-100', forStep === current);
            li.classList.toggle('opacity-50', forStep !== current);
        });

        syncRoleCardStyles();
    }

    function syncRoleCardStyles() {
        document.querySelectorAll('.role-card').forEach(function (label) {
            const input = label.querySelector('input[type="radio"]');
            const on = input && input.checked;
            label.classList.toggle('border-brand-500', on);
            label.classList.toggle('bg-brand-50/50', on);
            label.classList.toggle('shadow-sm', on);
            label.classList.toggle('border-slate-200', !on);
        });
    }

    document.querySelectorAll('input[name="role"]').forEach(function (r) {
        r.addEventListener('change', syncRoleCardStyles);
    });

    function validateStep(s) {
        if (s === 1) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            if (name.length < 2) {
                showStepError('Tafadhali weka jina kamili.');
                document.getElementById('name').focus();
                return false;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showStepError('Tafadhali weka barua pepe halali.');
                document.getElementById('email').focus();
                return false;
            }
            return true;
        }
        if (s === 2) {
            const p = document.getElementById('password').value;
            const c = document.getElementById('password_confirmation').value;
            if (p.length < 8) {
                showStepError('Neno siri lazima liwe na angalau herufi 8.');
                document.getElementById('password').focus();
                return false;
            }
            if (p !== c) {
                showStepError('Maneno siri hayalingani. Rudia tena.');
                document.getElementById('password_confirmation').focus();
                return false;
            }
            return true;
        }
        if (s === 3) {
            const picked = document.querySelector('input[name="role"]:checked');
            if (!picked) {
                showStepError('Chagua muhitaji au mfanyakazi.');
                return false;
            }
            return true;
        }
        if (s === 4) {
            const phone = document.getElementById('phone').value.trim();
            if (phone && !phoneRe.test(phone)) {
                showStepError('Nambari ya simu si sahihi. Tumia 07xxxxxxxx au 2557xxxxxxxx, au acha tupu.');
                document.getElementById('phone').focus();
                return false;
            }
            const lat = document.getElementById('lat').value.trim();
            const lng = document.getElementById('lng').value.trim();
            if (lat || lng) {
                const la = parseFloat(lat);
                const ln = parseFloat(lng);
                if (isNaN(la) || la < -90 || la > 90 || isNaN(ln) || ln < -180 || ln > 180) {
                    showStepError('Latitudo au longitudo si sahihi. Futa sehemu hizi au tumia kitufe cha GPS.');
                    return false;
                }
            }
            return true;
        }
        return true;
    }

    btnNext.addEventListener('click', function () {
        clearStepError();
        if (!validateStep(current)) return;
        showStep(current + 1);
    });

    btnPrev.addEventListener('click', function () {
        clearStepError();
        showStep(current - 1);
    });

    document.querySelectorAll('.step-dot').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const target = parseInt(btn.getAttribute('data-step'), 10);
            if (target >= current) return;
            clearStepError();
            showStep(target);
        });
    });

    document.querySelectorAll('.toggle-pw').forEach(function (b) {
        b.addEventListener('click', function () {
            const id = b.getAttribute('data-target');
            const input = document.getElementById(id);
            if (!input) return;
            input.type = input.type === 'password' ? 'text' : 'password';
            b.textContent = input.type === 'password' ? '👁' : '🙈';
        });
    });

    const strengthFill = document.getElementById('strengthFill');
    const strengthLabel = document.getElementById('strengthLabel');
    const pwMatchHint = document.getElementById('pwMatchHint');

    function checkStrength(val) {
        let score = 0;
        if (val.length >= 8) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;
        const colors = ['#ef4444', '#f97316', '#eab308', '#059669'];
        const labels = ['Dhaifu sana', 'Dhaifu', 'Wastani', 'Imara'];
        const widths = ['25%', '50%', '75%', '100%'];
        if (!val) {
            strengthFill.style.width = '0';
            strengthLabel.textContent = '';
            return;
        }
        const i = Math.max(0, Math.min(score - 1, 3));
        strengthFill.style.width = widths[i];
        strengthFill.style.backgroundColor = colors[i];
        strengthLabel.textContent = labels[i];
        strengthLabel.style.color = colors[i];
    }

    document.getElementById('password').addEventListener('input', function () {
        checkStrength(this.value);
    });
    document.getElementById('password_confirmation').addEventListener('input', function () {
        const p = document.getElementById('password').value;
        const c = this.value;
        if (!c) {
            pwMatchHint.textContent = '';
            pwMatchHint.className = 'mt-2 text-[12px] text-slate-500';
            return;
        }
        if (p === c) {
            pwMatchHint.textContent = '✓ Maneno siri yalingana';
            pwMatchHint.className = 'mt-2 text-[12px] font-medium text-emerald-600';
        } else {
            pwMatchHint.textContent = 'Bado hayalingani';
            pwMatchHint.className = 'mt-2 text-[12px] font-medium text-amber-600';
        }
    });

    window.getGPSLocation = function () {
        const latInput = document.getElementById('lat');
        const lngInput = document.getElementById('lng');
        const gpsBtn = document.getElementById('gps');
        if (!navigator.geolocation) {
            showStepError('Kivinjari hakiungi mkono GPS.');
            return;
        }
        gpsBtn.disabled = true;
        const prev = gpsBtn.textContent;
        gpsBtn.textContent = 'Inapakia…';
        navigator.geolocation.getCurrentPosition(
            function (pos) {
                latInput.value = pos.coords.latitude.toFixed(6);
                lngInput.value = pos.coords.longitude.toFixed(6);
                gpsBtn.textContent = prev;
                gpsBtn.disabled = false;
            },
            function () {
                showStepError('Haiwezekani kupata eneo. Angalia ruhusa za eneo kwenye kifaa.');
                gpsBtn.textContent = prev;
                gpsBtn.disabled = false;
            }
        );
    };
    document.getElementById('gps').addEventListener('click', window.getGPSLocation);

    form.addEventListener('submit', function (e) {
        clearStepError();
        if (!validateStep(4)) {
            e.preventDefault();
            showStep(4);
            return;
        }
    });

    showStep(current);
    checkStrength(document.getElementById('password').value);
})();
    </script>
</body>
</html>
