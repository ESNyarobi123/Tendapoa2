<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Thibitisha OTP - Tendapoa</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0a0a1a 0%, #1a1a3e 50%, #2d1b69 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 2rem; position: relative; overflow: hidden;
        }

        body::before {
            content: ''; position: absolute; top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(99,102,241,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate { 0%{transform:rotate(0deg)} 100%{transform:rotate(360deg)} }

        @keyframes fadeInUp {
            from { opacity:0; transform:translateY(30px); }
            to   { opacity:1; transform:translateY(0); }
        }

        .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1000px; width: 100%;
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(20px);
            border-radius: 24px; overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            border: 1px solid rgba(255,255,255,0.1);
            position: relative; z-index: 1;
            animation: fadeInUp 0.6s ease-out;
        }

        /* Brand Panel */
        .brand-panel {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 4rem;
            display: flex; flex-direction: column;
            justify-content: center; align-items: center;
            text-align: center; color: white;
            position: relative; overflow: hidden;
            min-width: 0;
        }

        .brand-panel::before {
            content: ''; position: absolute; top:0; left:0; right:0; bottom:0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><circle cx="200" cy="200" r="100" fill="rgba(255,255,255,0.1)"/><circle cx="800" cy="300" r="150" fill="rgba(255,255,255,0.1)"/><circle cx="400" cy="700" r="120" fill="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.3;
        }

        .brand-content { position: relative; z-index: 2; }
        .brand-logo { font-size: 4rem; margin-bottom: 1rem; }
        .brand-title { font-size: 2.5rem; font-weight: 800; margin-bottom: 1rem; }
        .brand-subtitle { font-size: 1.125rem; opacity: 0.9; line-height: 1.6; }

        .email-sent-box {
            margin-top: 2rem;
            background: rgba(255,255,255,0.15);
            border-radius: 16px; padding: 1.25rem;
            font-size: 0.9rem; line-height: 1.7;
        }

        .email-sent-box .email-addr {
            font-weight: 700; font-size: 1rem;
            background: rgba(255,255,255,0.2);
            padding: 0.4rem 0.75rem;
            border-radius: 8px; display: inline-block;
            margin-top: 0.5rem; word-break: break-all;
        }

        /* Form Panel */
        .form-panel {
            padding: 4rem;
            display: flex; flex-direction: column; justify-content: center;
            min-width: 0;
        }

        .form-header { margin-bottom: 2rem; }
        .form-title { font-size: 2rem; font-weight: 800; color: white; margin-bottom: 0.5rem; }
        .form-subtitle { color: rgba(255,255,255,0.65); font-size: 0.9rem; line-height: 1.6; }

        .form-card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(20px);
            border-radius: 16px; padding: 2rem;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .form-label {
            display: block; color: rgba(255,255,255,0.9);
            font-weight: 600; margin-bottom: 0.75rem; font-size: 0.875rem;
        }

        .otp-inputs { display: flex; gap: 10px; justify-content: space-between; margin-bottom: 0.75rem; }

        .otp-input {
            flex: 1; height: 64px;
            min-width: 0; width: 0;
            background: rgba(255,255,255,0.05);
            border: 2px solid rgba(255,255,255,0.12);
            border-radius: 14px; color: white;
            font-size: 1.75rem; font-weight: 800;
            text-align: center; transition: all 0.25s ease;
        }

        .otp-input:focus {
            outline: none; border-color: #6366f1;
            background: rgba(99,102,241,0.1);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.25);
            transform: translateY(-2px);
        }

        .otp-input.filled {
            border-color: #8b5cf6;
            background: rgba(139,92,246,0.15);
        }

        .timer-row {
            display: flex; align-items: center; justify-content: center;
            gap: 0.4rem; margin-bottom: 1.5rem;
            color: rgba(255,255,255,0.45); font-size: 0.82rem;
        }

        .timer-row span { font-weight: 700; color: #a5b4fc; }

        .btn-submit {
            width: 100%; padding: 1rem;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white; border: none; border-radius: 12px;
            font-weight: 700; font-size: 1rem; cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 14px rgba(99,102,241,0.4);
        }

        .btn-submit:not(:disabled):hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99,102,241,0.6);
        }

        .btn-submit:disabled {
            opacity: 0.45; cursor: not-allowed;
        }

        .alert-error {
            background: rgba(244,63,94,0.2); border: 1px solid rgba(244,63,94,0.4);
            color: #f87171; padding: 1rem; border-radius: 12px;
            margin-bottom: 1.5rem; font-size: 0.875rem;
        }

        .alert-success {
            background: rgba(16,185,129,0.2); border: 1px solid rgba(16,185,129,0.4);
            color: #6ee7b7; padding: 1rem; border-radius: 12px;
            margin-bottom: 1.5rem; font-size: 0.875rem;
        }

        .dev-box {
            background: rgba(251,191,36,0.12);
            border: 2px dashed #fbbf24;
            border-radius: 12px; padding: 1rem;
            margin-bottom: 1.5rem; text-align: center;
        }

        .dev-box-label { color: #fbbf24; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
        .dev-box-otp { color: #fff; font-size: 2.25rem; font-weight: 900; letter-spacing: 8px; }
        .dev-box-note { color: rgba(255,255,255,0.4); font-size: 0.72rem; margin-top: 4px; }

        .form-footer {
            margin-top: 1.5rem; text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            display: flex; justify-content: space-between; align-items: center;
        }

        .footer-link {
            color: rgba(255,255,255,0.55); font-size: 0.875rem;
            text-decoration: none; transition: color 0.3s;
        }

        .footer-link:hover { color: #a5b4fc; }

        .resend-link {
            color: #6366f1; font-size: 0.875rem;
            font-weight: 600; text-decoration: none; transition: color 0.3s;
        }

        .resend-link:hover { color: #818cf8; }

        .back-home {
            position: absolute; top: 2rem; left: 2rem;
            color: rgba(255,255,255,0.8); text-decoration: none;
            display: flex; align-items: center; gap: 0.5rem;
            font-size: 0.875rem; z-index: 10; transition: color 0.3s;
        }

        .back-home:hover { color: white; }

        @media (max-width: 768px) {
            body { padding: 1rem; align-items: flex-start; }
            .back-home {
                position: fixed; top: 1rem; left: 1rem;
                background: rgba(0,0,0,0.3); backdrop-filter: blur(10px);
                padding: 0.5rem 1rem; border-radius: 8px;
                border: 1px solid rgba(255,255,255,0.1); z-index: 100;
            }
            .container { grid-template-columns: 1fr; margin-top: 3rem; }
            .brand-panel { padding: 2rem 1.5rem; order: 2; }
            .brand-logo { font-size: 2.5rem; }
            .brand-title { font-size: 1.75rem; }
            .email-sent-box { display: none; }
            .form-panel { padding: 2rem 1.5rem; order: 1; }
            .form-title { font-size: 1.5rem; }
            .form-card { padding: 1.5rem 1rem; }
            .otp-input { height: 54px; font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <a href="{{ route('home') }}" class="back-home">← Rudi Nyumbani</a>

    <div class="container">
        <!-- Brand Panel -->
        <div class="brand-panel">
            <div class="brand-content">
                <div class="brand-logo">�</div>
                <h1 class="brand-title">Angalia Barua Pepe</h1>
                <p class="brand-subtitle">Tumekutumia msimbo wa siri wa nambari 6.</p>

                <div class="email-sent-box">
                    Msimbo umetumwa kwa:
                    <div class="email-addr">{{ $email }}</div>
                    <br>
                    Angalia <strong>Inbox</strong> au <strong>Spam</strong> folder yako.
                </div>
            </div>
        </div>

        <!-- Form Panel -->
        <div class="form-panel">
            <div class="form-header">
                <h2 class="form-title">Weka Msimbo wa OTP</h2>
                <p class="form-subtitle">Weka tarakimu 6 ulizopokea kwenye barua pepe yako.</p>
            </div>

            @if(session('dev_otp'))
                <div class="dev-box">
                    <div class="dev-box-label">🛠️ Dev Mode — Barua pepe haikutumwa</div>
                    <div class="dev-box-otp">{{ session('dev_otp') }}</div>
                    <div class="dev-box-note">Production haioni hii</div>
                </div>
            @elseif(session('status'))
                <div class="alert-success">{{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="alert-error">
                    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.otp.verify') }}" id="otpForm" class="form-card">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                <input type="hidden" name="otp" id="otpHidden">

                <label class="form-label">Msimbo wa OTP (tarakimu 6)</label>
                <div class="otp-inputs" id="otpInputs">
                    @for($i = 0; $i < 6; $i++)
                        <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="{{ $i }}">
                    @endfor
                </div>

                <div class="timer-row" id="timerDisplay">
                    ⏱️ Msimbo utaisha baada ya: <span id="countdown">10:00</span>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn" disabled>
                    Thibitisha Msimbo →
                </button>
            </form>

            <div class="form-footer">
                <a href="{{ route('login') }}" class="footer-link">← Rudi Ingia</a>
                <a href="{{ route('password.otp.request') }}" class="resend-link">Omba Msimbo Mpya</a>
            </div>
        </div>
    </div>

    <script>
        const inputs = document.querySelectorAll('.otp-input');
        const hiddenInput = document.getElementById('otpHidden');
        const submitBtn = document.getElementById('submitBtn');

        inputs.forEach((input, idx) => {
            input.addEventListener('input', e => {
                const val = e.target.value.replace(/[^0-9]/g, '');
                e.target.value = val;
                e.target.classList.toggle('filled', !!val);
                if (val && idx < 5) inputs[idx + 1].focus();
                updateHidden();
            });
            input.addEventListener('keydown', e => {
                if (e.key === 'Backspace' && !e.target.value && idx > 0) {
                    inputs[idx - 1].value = '';
                    inputs[idx - 1].classList.remove('filled');
                    inputs[idx - 1].focus();
                    updateHidden();
                }
            });
            input.addEventListener('paste', e => {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
                [...pasted].forEach((c, i) => { if (i < 6) { inputs[i].value = c; inputs[i].classList.add('filled'); } });
                updateHidden();
                inputs[Math.min(pasted.length, 5)].focus();
            });
        });

        function updateHidden() {
            const otp = Array.from(inputs).map(i => i.value).join('');
            hiddenInput.value = otp;
            submitBtn.disabled = otp.length !== 6;
        }

        let totalSeconds = 600;
        const countdownEl = document.getElementById('countdown');
        const timerEl     = document.getElementById('timerDisplay');
        const timer = setInterval(() => {
            totalSeconds--;
            const m = String(Math.floor(totalSeconds / 60)).padStart(2,'0');
            const s = String(totalSeconds % 60).padStart(2,'0');
            countdownEl.textContent = m + ':' + s;
            if (totalSeconds <= 60) countdownEl.style.color = '#f87171';
            if (totalSeconds <= 0) {
                clearInterval(timer);
                timerEl.innerHTML = '⚠️ Msimbo umeisha muda. <a href="{{ route("password.otp.request") }}" style="color:#a5b4fc;font-weight:600">Omba Tena</a>';
                submitBtn.disabled = true;
            }
        }, 1000);

        inputs[0].focus();
    </script>
</body>
</html>
