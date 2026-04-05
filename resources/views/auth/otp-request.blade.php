<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Umesahau Neno Siri - Tendapoa</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0a0a1a 0%, #1a1a3e 50%, #2d1b69 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
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
            max-width: 1000px;
            width: 100%;
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            border: 1px solid rgba(255,255,255,0.1);
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.6s ease-out;
        }

        /* Brand Panel */
        .brand-panel {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
            min-width: 0;
        }

        .brand-panel::before {
            content: '';
            position: absolute; top:0; left:0; right:0; bottom:0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><circle cx="200" cy="200" r="100" fill="rgba(255,255,255,0.1)"/><circle cx="800" cy="300" r="150" fill="rgba(255,255,255,0.1)"/><circle cx="400" cy="700" r="120" fill="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.3;
        }

        .brand-content { position: relative; z-index: 2; }

        .brand-logo { font-size: 4rem; margin-bottom: 1rem; }

        .brand-title { font-size: 2.5rem; font-weight: 800; margin-bottom: 1rem; }

        .brand-subtitle { font-size: 1.125rem; opacity: 0.9; line-height: 1.6; }

        .steps {
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            text-align: left;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(255,255,255,0.15);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
        }

        .step-num {
            width: 28px; height: 28px;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 0.8rem;
            flex-shrink: 0;
        }

        /* Form Panel */
        .form-panel {
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-width: 0;
        }

        .form-header { margin-bottom: 2rem; }

        .form-title { font-size: 2rem; font-weight: 800; color: white; margin-bottom: 0.5rem; }

        .form-subtitle { color: rgba(255,255,255,0.65); font-size: 0.9rem; line-height: 1.6; }

        .form-card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 2rem;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .form-group { margin-bottom: 1.5rem; }

        .form-label {
            display: block;
            color: rgba(255,255,255,0.9);
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            background: rgba(255,255,255,0.05);
            border: 2px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input::placeholder { color: rgba(255,255,255,0.35); }

        .form-input:focus {
            outline: none;
            border-color: #6366f1;
            background: rgba(255,255,255,0.08);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.2);
        }

        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 14px rgba(99,102,241,0.4);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99,102,241,0.6);
        }

        .alert-error {
            background: rgba(244,63,94,0.2);
            border: 1px solid rgba(244,63,94,0.4);
            color: #f87171;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }

        .alert-success {
            background: rgba(16,185,129,0.2);
            border: 1px solid rgba(16,185,129,0.4);
            color: #6ee7b7;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }

        .form-footer {
            margin-top: 1.5rem;
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .form-footer-text { color: rgba(255,255,255,0.6); font-size: 0.875rem; margin-bottom: 0.5rem; }

        .back-link {
            color: #6366f1;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            transition: color 0.3s;
        }

        .back-link:hover { color: #818cf8; }

        .back-home {
            position: absolute;
            top: 2rem; left: 2rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            display: flex; align-items: center; gap: 0.5rem;
            font-size: 0.875rem;
            z-index: 10;
            transition: color 0.3s;
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
            .steps { display: none; }
            .form-panel { padding: 2rem 1.5rem; order: 1; }
            .form-title { font-size: 1.5rem; }
            .form-card { padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <a href="{{ route('home') }}" class="back-home">← Rudi Nyumbani</a>

    <div class="container">
        <!-- Brand Panel -->
        <div class="brand-panel">
            <div class="brand-content">
                <div class="brand-logo">🔐</div>
                <h1 class="brand-title">Tendapoa</h1>
                <p class="brand-subtitle">Badilisha neno siri lako kwa usalama kupitia msimbo wa OTP.</p>

                <div class="steps">
                    <div class="step">
                        <div class="step-num">1</div>
                        <span>Weka barua pepe yako</span>
                    </div>
                    <div class="step">
                        <div class="step-num">2</div>
                        <span>Pokea msimbo wa OTP</span>
                    </div>
                    <div class="step">
                        <div class="step-num">3</div>
                        <span>Weka neno siri jipya</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Panel -->
        <div class="form-panel">
            <div class="form-header">
                <h2 class="form-title">Umesahau Neno Siri?</h2>
                <p class="form-subtitle">Weka barua pepe uliyotumia kufungua akaunti yako na tutakutumia msimbo wa OTP.</p>
            </div>

            @if(session('status'))
                <div class="alert-success">{{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="alert-error">
                    @foreach($errors->all() as $e)
                        <div>{{ $e }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.otp.send') }}" class="form-card">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="email">📧 Barua Pepe</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-input"
                        value="{{ old('email') }}"
                        placeholder="jina@example.com"
                        required
                        autofocus
                    >
                </div>

                <button type="submit" class="btn-submit">
                    Tuma Msimbo wa OTP →
                </button>
            </form>

            <div class="form-footer">
                <p class="form-footer-text">Umekumbuka neno siri?</p>
                <a href="{{ route('login') }}" class="back-link">← Rudi kwenye Ingia</a>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() { this.parentElement.style.transform = 'scale(1.01)'; });
            input.addEventListener('blur', function()  { this.parentElement.style.transform = 'scale(1)'; });
        });
    </script>
</body>
</html>
