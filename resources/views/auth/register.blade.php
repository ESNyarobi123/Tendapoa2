<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Jisajili - Tendapoa</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0a0a1a 0%, #1a1a3e 50%, #2d1b69 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .register-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1100px;
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Left Brand Panel */
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
        }

        .brand-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><circle cx="200" cy="200" r="100" fill="rgba(255,255,255,0.1)"/><circle cx="800" cy="300" r="150" fill="rgba(255,255,255,0.1)"/><circle cx="400" cy="700" r="120" fill="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.3;
        }

        .brand-content {
            position: relative;
            z-index: 2;
        }

        .brand-logo {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .brand-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .brand-subtitle {
            font-size: 1.125rem;
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .brand-features {
            text-align: left;
            font-size: 0.875rem;
        }

        .brand-features li {
            margin: 0.75rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Right Form Panel */
        .form-panel {
            padding: 4rem;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            max-height: 100vh;
        }

        .form-header {
            margin-bottom: 2rem;
        }

        .form-title {
            font-size: 2rem;
            font-weight: 800;
            color: white;
            margin-bottom: 0.5rem;
        }

        .form-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.875rem;
        }

        /* Glass Form Card */
        .form-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 0.875rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: #6366f1;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        .form-select option {
            background: #1a1a3e;
            color: white;
        }

        /* Role Selection Cards */
        .role-selection {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .role-card {
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .role-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .role-card input[type="radio"] {
            display: none;
        }

        .role-card input[type="radio"]:checked + .role-content {
            border-color: #6366f1;
            background: rgba(99, 102, 241, 0.2);
        }

        .role-content {
            padding: 1rem;
            border-radius: 8px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .role-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .role-name {
            font-weight: 700;
            color: white;
            margin-bottom: 0.25rem;
        }

        .role-desc {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.7);
        }

        /* Password Wrapper */
        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
            font-size: 1.25rem;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: rgba(255, 255, 255, 0.9);
        }

        /* Location Group */
        .location-group {
            background: rgba(255, 255, 255, 0.03);
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1.5rem;
        }

        .location-group-title {
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        .location-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 0.75rem;
            align-items: end;
        }

        .gps-btn {
            padding: 0.875rem 1.5rem;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .gps-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
        }

        .location-hint {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 0.5rem;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
            margin-bottom: 1rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.6);
        }

        .form-footer {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-footer-text {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .login-link {
            color: #6366f1;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link:hover {
            color: #818cf8;
        }

        /* Error Messages */
        .alert-error {
            background: rgba(244, 63, 94, 0.2);
            border: 1px solid rgba(244, 63, 94, 0.4);
            color: #f87171;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }

        .alert-error ul {
            list-style: none;
            margin-top: 0.5rem;
        }

        .alert-error li {
            margin: 0.25rem 0;
        }

        /* Back to home link */
        .back-home {
            position: absolute;
            top: 2rem;
            left: 2rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            z-index: 10;
            transition: color 0.3s ease;
        }

        .back-home:hover {
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
                align-items: flex-start;
                min-height: 100vh;
            }

            .back-home {
                position: fixed;
                top: 1rem;
                left: 1rem;
                background: rgba(0, 0, 0, 0.3);
                backdrop-filter: blur(10px);
                padding: 0.5rem 1rem;
                border-radius: 8px;
                border: 1px solid rgba(255, 255, 255, 0.1);
                z-index: 100;
            }

            .register-container {
                grid-template-columns: 1fr;
                margin-top: 3rem;
                border-radius: 16px;
                max-width: 100%;
                max-height: calc(100vh - 4rem);
                overflow-y: auto;
            }

            .brand-panel {
                padding: 2rem 1.5rem;
                order: 2;
            }

            .brand-logo {
                font-size: 2.5rem;
            }

            .brand-title {
                font-size: 1.75rem;
            }

            .brand-subtitle {
                font-size: 1rem;
                line-height: 1.5;
                margin-bottom: 1.5rem;
            }

            .brand-features {
                font-size: 0.8rem;
            }

            .brand-features li {
                margin: 0.5rem 0;
            }

            .form-panel {
                padding: 2rem 1.5rem;
                max-height: none;
                order: 1;
            }

            .form-header {
                margin-bottom: 1.5rem;
            }

            .form-title {
                font-size: clamp(1.5rem, 4vw, 1.75rem);
            }

            .form-subtitle {
                font-size: 0.8rem;
            }

            .form-card {
                padding: 1.5rem;
            }

            .form-group {
                margin-bottom: 1.25rem;
            }

            .form-input,
            .form-select {
                padding: 0.75rem 0.875rem;
                font-size: 1rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .form-grid .form-group {
                margin-bottom: 1.25rem;
            }

            .role-selection {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .role-card {
                padding: 1.25rem;
            }

            .role-icon {
                font-size: 2rem;
            }

            .role-name {
                font-size: 0.95rem;
            }

            .role-desc {
                font-size: 0.7rem;
            }

            .location-group {
                padding: 1.25rem;
            }

            .location-group-title {
                font-size: 0.8rem;
                margin-bottom: 0.75rem;
            }

            .location-inputs {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .location-inputs .form-group {
                margin: 0;
            }

            .gps-btn {
                padding: 0.75rem 1.25rem;
                width: 100%;
                font-size: 0.9rem;
            }

            .location-hint {
                font-size: 0.7rem;
                margin-top: 0.75rem;
            }

            .btn-submit {
                padding: 0.875rem;
                font-size: 0.95rem;
            }

            .form-footer {
                padding-top: 1.25rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 0.75rem;
            }

            .back-home {
                top: 0.75rem;
                left: 0.75rem;
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
            }

            .register-container {
                margin-top: 2.5rem;
                border-radius: 12px;
                max-height: calc(100vh - 3.5rem);
            }

            .brand-panel {
                padding: 1.5rem 1rem;
            }

            .brand-logo {
                font-size: 2rem;
                margin-bottom: 0.75rem;
            }

            .brand-title {
                font-size: 1.5rem;
                margin-bottom: 0.75rem;
            }

            .brand-subtitle {
                font-size: 0.9rem;
                margin-bottom: 1.25rem;
            }

            .brand-features {
                font-size: 0.75rem;
            }

            .brand-features li {
                margin: 0.4rem 0;
            }

            .form-panel {
                padding: 1.5rem 1rem;
            }

            .form-header {
                margin-bottom: 1.25rem;
            }

            .form-title {
                font-size: 1.5rem;
            }

            .form-subtitle {
                font-size: 0.75rem;
            }

            .form-card {
                padding: 1.25rem;
                border-radius: 12px;
            }

            .form-label {
                font-size: 0.8rem;
            }

            .form-input,
            .form-select {
                padding: 0.7rem 0.8rem;
                font-size: 0.95rem;
            }

            .password-toggle {
                font-size: 1.1rem;
                right: 0.75rem;
            }

            .role-card {
                padding: 1rem;
            }

            .role-icon {
                font-size: 1.75rem;
            }

            .role-name {
                font-size: 0.9rem;
            }

            .role-desc {
                font-size: 0.65rem;
            }

            .location-group {
                padding: 1rem;
            }

            .location-group-title {
                font-size: 0.75rem;
            }

            .gps-btn {
                padding: 0.7rem 1rem;
                font-size: 0.85rem;
            }

            .location-hint {
                font-size: 0.65rem;
            }

            .btn-submit {
                padding: 0.8rem;
                font-size: 0.9rem;
            }

            .form-footer-text,
            .login-link {
                font-size: 0.8rem;
            }

            .alert-error {
                padding: 0.875rem;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <a href="{{ route('home') }}" class="back-home">
        <span>←</span> Rudi Nyumbani
    </a>

    <div class="register-container">
        <!-- Left Brand Panel -->
        <div class="brand-panel">
            <div class="brand-content">
                <div class="brand-logo">🧹</div>
                <h1 class="brand-title">Fungua Akaunti</h1>
                <p class="brand-subtitle">
                    Jiunge na Tendapoa leo na ufurahie huduma bora za usafi na kazi za kuegemea.
                </p>
                <ul class="brand-features">
                    <li>✅ Huduma salama na ya kuegemea</li>
                    <li>✅ Malipo salama kupitia ZenoPay</li>
                    <li>✅ Wafanyakazi waliochunguzwa</li>
                    <li>✅ Msaada wa masaa 24</li>
                </ul>
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="form-panel">
            <div class="form-header">
                <h2 class="form-title">Anza Sasa</h2>
                <p class="form-subtitle">Jaza taarifa zako ili kufungua akaunti</p>
            </div>

@if($errors->any())
  <div class="alert-error">
    <b>Angalia makosa:</b>
                    <ul>
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
  </div>
@endif

            <form method="post" action="{{ route('register.post') }}" class="form-card">
  @csrf

                <div class="form-group">
                    <label class="form-label" for="name">Jina Kamili</label>
                    <input 
                        type="text" 
                        id="name"
                        name="name" 
                        class="form-input" 
                        value="{{ old('name') }}" 
                        placeholder="Jina lako kamili"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Barua Pepe</label>
                    <input 
                        type="email" 
                        id="email"
                        name="email" 
                        class="form-input" 
                        value="{{ old('email') }}" 
                        placeholder="jina@example.com"
                        required
                    >
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="password">Neno Siri</label>
                        <div class="password-wrapper">
                            <input 
                                type="password" 
                                id="password"
                                name="password" 
                                class="form-input" 
                                placeholder="••••••••"
                                required
                            >
                            <button 
                                type="button" 
                                class="password-toggle" 
                                onclick="togglePassword('password')"
                                aria-label="Toggle password visibility"
                            >
                                👁️
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Rudia Neno Siri</label>
                        <div class="password-wrapper">
                            <input 
                                type="password" 
                                id="password_confirmation"
                                name="password_confirmation" 
                                class="form-input" 
                                placeholder="••••••••"
                                required
                            >
                            <button 
                                type="button" 
                                class="password-toggle" 
                                onclick="togglePassword('password_confirmation')"
                                aria-label="Toggle password visibility"
                            >
                                👁️
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Nafasi (Chagua Jinsi Unavyotaka Kutumia)</label>
                    <div class="role-selection">
                        <label class="role-card">
                            <input 
                                type="radio" 
                                name="role" 
                                value="muhitaji" 
                                {{ old('role')==='muhitaji'?'checked':'' }}
                                required
                            >
                            <div class="role-content">
                                <div class="role-icon">👤</div>
                                <div class="role-name">Muhitaji (Mteja)</div>
                                <div class="role-desc">Nataka wafanyakazi</div>
                            </div>
    </label>
                        <label class="role-card">
                            <input 
                                type="radio" 
                                name="role" 
                                value="mfanyakazi" 
                                {{ old('role')==='mfanyakazi'?'checked':'' }}
                            >
                            <div class="role-content">
                                <div class="role-icon">👷</div>
                                <div class="role-name">Mfanyakazi</div>
                                <div class="role-desc">Ninafanya kazi</div>
                            </div>
    </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="phone">Namba ya Simu (Hiari)</label>
                    <input 
                        type="tel" 
                        id="phone"
                        name="phone" 
                        class="form-input" 
                        value="{{ old('phone') }}" 
                        placeholder="07xxxxxxxx au 2557xxxxxxxx"
                    >
                </div>

                <div class="location-group">
                    <div class="location-group-title">Mahali (Hiari - husaidia kuonyesha umbali wa kazi)</div>
                    <div class="location-inputs">
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" for="lat" style="font-size: 0.75rem;">Latitude</label>
                            <input 
                                type="text" 
                                id="lat"
                                name="lat" 
                                class="form-input" 
                                value="{{ old('lat') }}" 
                                placeholder="Lat"
                            >
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" for="lng" style="font-size: 0.75rem;">Longitude</label>
                            <input 
                                type="text" 
                                id="lng"
                                name="lng" 
                                class="form-input" 
                                value="{{ old('lng') }}" 
                                placeholder="Lng"
                            >
                        </div>
                        <button 
                            type="button" 
                            id="gps" 
                            class="gps-btn"
                            onclick="getGPSLocation()"
                        >
                            📍 GPS
                        </button>
                    </div>
                    <p class="location-hint">Bonyeza "GPS" ili kujaza eneo lako kiotomatiki</p>
                </div>

                <button type="submit" class="btn-submit">
                    🚀 Sajili Sasa
                </button>

                <div class="form-footer">
                    <p class="form-footer-text">
                        Tayari una akaunti?
                    </p>
                    <a href="{{ route('login') }}" class="login-link">
                        Ingia hapa →
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const toggleButtons = document.querySelectorAll(`button[onclick="togglePassword('${inputId}')"]`);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButtons.forEach(btn => btn.textContent = '🙈');
            } else {
                passwordInput.type = 'password';
                toggleButtons.forEach(btn => btn.textContent = '👁️');
            }
        }

        function getGPSLocation() {
            const latInput = document.getElementById('lat');
            const lngInput = document.getElementById('lng');
            const gpsBtn = document.getElementById('gps');
            
            if (navigator.geolocation) {
                gpsBtn.textContent = '⏳...';
                gpsBtn.disabled = true;
                
                navigator.geolocation.getCurrentPosition(
                    position => {
                        latInput.value = position.coords.latitude.toFixed(6);
                        lngInput.value = position.coords.longitude.toFixed(6);
                        gpsBtn.textContent = '✅ GPS';
                        gpsBtn.disabled = false;
                        
                        setTimeout(() => {
                            gpsBtn.textContent = '📍 GPS';
                        }, 2000);
                    },
                    error => {
                        alert('Hauwezi kupata eneo lako. Hakikisha umeongeza ruhusa ya eneo.');
                        gpsBtn.textContent = '📍 GPS';
                        gpsBtn.disabled = false;
                    }
                );
            } else {
                alert('Vifaa vyako havitaalamu GPS.');
            }
        }

        // Role card selection styling
        document.querySelectorAll('.role-card input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.role-card input[type="radio"]').forEach(r => {
                    const content = r.closest('.role-card').querySelector('.role-content');
                    content.style.borderColor = 'transparent';
                    content.style.background = 'transparent';
                });
                
                if (this.checked) {
                    const content = this.closest('.role-card').querySelector('.role-content');
                    content.style.borderColor = '#6366f1';
                    content.style.background = 'rgba(99, 102, 241, 0.2)';
                }
            });
        });

        // Set initial checked state
        document.querySelectorAll('.role-card input[type="radio"]').forEach(radio => {
            if (radio.checked) {
                const content = radio.closest('.role-card').querySelector('.role-content');
                content.style.borderColor = '#6366f1';
                content.style.background = 'rgba(99, 102, 241, 0.2)';
            }
        });

        // Add focus animation
        document.querySelectorAll('.form-input, .form-select').forEach(input => {
            input.addEventListener('focus', function() {
                this.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.style.transform = 'scale(1)';
            });
        });
</script>
</body>
</html>
