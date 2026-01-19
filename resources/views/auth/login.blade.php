<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ingia - Tendapoa</title>
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
            overflow: hidden;
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

        .login-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1000px;
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
        }

        /* Right Form Panel */
        .form-panel {
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
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

        .form-input {
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

        .form-input:focus {
            outline: none;
            border-color: #6366f1;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

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

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-input {
            width: 18px;
            height: 18px;
            accent-color: #6366f1;
            cursor: pointer;
        }

        .checkbox-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.875rem;
            cursor: pointer;
        }

        .forgot-link {
            color: #6366f1;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .forgot-link:hover {
            color: #818cf8;
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
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.6);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .form-footer {
            margin-top: 2rem;
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-footer-text {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .register-link {
            color: #6366f1;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .register-link:hover {
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

            .login-container {
                grid-template-columns: 1fr;
                margin-top: 3rem;
                border-radius: 16px;
                max-width: 100%;
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
            }

            .form-panel {
                padding: 2rem 1.5rem;
                order: 1;
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

            .form-input {
                padding: 0.75rem 0.875rem;
                font-size: 1rem;
            }

            .form-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .btn-submit {
                padding: 0.875rem;
                font-size: 0.95rem;
            }

            .form-footer {
                margin-top: 1.5rem;
                padding-top: 1.5rem;
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

            .login-container {
                margin-top: 2.5rem;
                border-radius: 12px;
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
            }

            .form-panel {
                padding: 1.5rem 1rem;
            }

            .form-header {
                margin-bottom: 1.5rem;
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

            .form-input {
                padding: 0.7rem 0.8rem;
                font-size: 0.95rem;
            }

            .password-toggle {
                font-size: 1.1rem;
                right: 0.75rem;
            }

            .checkbox-label,
            .forgot-link,
            .form-footer-text {
                font-size: 0.8rem;
            }

            .btn-submit {
                padding: 0.8rem;
                font-size: 0.9rem;
            }

            .alert-error {
                padding: 0.875rem;
                font-size: 0.8rem;
            }
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
    </style>
</head>
<body>
    <a href="{{ route('home') }}" class="back-home">
        <span>←</span> Rudi Nyumbani
    </a>

    <div class="login-container">
        <!-- Left Brand Panel -->
        <div class="brand-panel">
            <div class="brand-content">
                <div class="brand-logo">🧹</div>
                <h1 class="brand-title">Tendapoa</h1>
                <p class="brand-subtitle">
                    Usafi wa Kuegemea, Kazi za Kuegemea.<br>
                    Ingia kwa akaunti yako na anza kutumia huduma zetu za kisasa.
                </p>
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="form-panel">
            <div class="form-header">
                <h2 class="form-title">Karibu Tena!</h2>
                <p class="form-subtitle">Ingia kwa akaunti yako ili kuendelea</p>
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

            <form method="post" action="{{ route('login.post') }}" class="form-card">
                @csrf

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
                        autofocus
                    >
                </div>

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
                            onclick="togglePassword()"
                            aria-label="Toggle password visibility"
                        >
                            👁️
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <div class="checkbox-wrapper">
                        <input 
                            type="checkbox" 
                            id="remember"
                            name="remember" 
                            value="1" 
                            class="checkbox-input"
                            {{ old('remember') ? 'checked' : '' }}
                        >
                        <label for="remember" class="checkbox-label">Kumbuka mimi</label>
                    </div>
                    <a href="#" class="forgot-link">Umesahau neno siri?</a>
                </div>

                <button type="submit" class="btn-submit">
                    🔑 Ingia
                </button>

                <div class="form-footer">
                    <p class="form-footer-text">
                        Huna akaunti?
                    </p>
                    <a href="{{ route('register') }}" class="register-link">
                        Fungua Akaunti Mpya →
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleButton.textContent = '👁️';
            }
        }

        // Add focus animation
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>
