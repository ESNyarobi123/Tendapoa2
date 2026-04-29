<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Delete Your Account - Tendapoa</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #1a1a1a;
            background: #f8fafc;
        }

        .header {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            padding: 20px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .header-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: white;
        }

        .header-logo span {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        h1 {
            font-size: 28px;
            font-weight: 800;
            color: #dc2626;
            margin-bottom: 10px;
        }

        h2 {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            margin-top: 30px;
            margin-bottom: 12px;
        }

        p {
            color: #475569;
            margin-bottom: 14px;
            font-size: 15px;
        }

        .warning-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 4px solid #dc2626;
            border-radius: 8px;
            padding: 20px;
            margin: 24px 0;
        }

        .warning-box p {
            color: #991b1b;
            font-weight: 600;
        }

        .steps {
            counter-reset: step;
            list-style: none;
            padding: 0;
        }

        .steps li {
            counter-increment: step;
            position: relative;
            padding: 14px 14px 14px 56px;
            margin-bottom: 10px;
            background: white;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            font-size: 15px;
            color: #334155;
        }

        .steps li::before {
            content: counter(step);
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 30px;
            height: 30px;
            background: #6366f1;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }

        .app-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f1f5f9;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin: 4px;
        }

        .app-badge svg {
            width: 16px;
            height: 16px;
        }

        /* Delete form for logged-in users */
        .delete-form-wrapper {
            background: white;
            border: 2px solid #fecaca;
            border-radius: 16px;
            padding: 30px;
            margin-top: 30px;
        }

        .delete-form-wrapper h3 {
            color: #dc2626;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #1e293b;
            font-size: 14px;
        }

        .form-group input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 15px;
            font-family: inherit;
            transition: border-color 0.2s;
        }

        .form-group input[type="password"]:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        .btn-danger {
            background: #dc2626;
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .btn-cancel {
            background: #e2e8f0;
            color: #475569;
            border: none;
            padding: 12px 28px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-left: 10px;
            transition: background 0.2s;
        }

        .btn-cancel:hover {
            background: #cbd5e1;
        }

        .error-msg {
            color: #dc2626;
            font-size: 13px;
            margin-top: 4px;
        }

        .success-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-left: 4px solid #16a34a;
            border-radius: 8px;
            padding: 20px;
            margin: 24px 0;
        }

        .success-box p {
            color: #166534;
            font-weight: 600;
        }

        .footer {
            text-align: center;
            padding: 30px 20px;
            color: #94a3b8;
            font-size: 13px;
            border-top: 1px solid #e2e8f0;
            margin-top: 40px;
        }

        .footer a {
            color: #6366f1;
            text-decoration: none;
        }

        @media (max-width: 600px) {
            .container { padding: 20px 16px; }
            h1 { font-size: 22px; }
            .delete-form-wrapper { padding: 20px; }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <a href="/" class="header-logo">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
            <span>Tendapoa</span>
        </a>
    </div>

    <div class="container">
        <h1>Delete Your Account</h1>
        <p>You can delete your Tendapoa account at any time. This page explains how to request account deletion and what happens when your account is deleted.</p>

        <div class="warning-box">
            <p>⚠️ Warning: Account deletion is permanent and cannot be undone. All your data will be erased.</p>
        </div>

        <h2>What Happens When You Delete Your Account</h2>
        <p>When you delete your Tendapoa account, the following data is permanently removed:</p>
        <ul class="steps">
            <li>Your profile information (name, email, phone, photo)</li>
            <li>Your wallet balance and transaction history</li>
            <li>Your job postings and application records</li>
            <li>Your chat messages and notifications</li>
            <li>Your saved location data</li>
            <li>Your FCM device tokens (push notifications will stop)</li>
        </ul>

        <h2>How to Delete Your Account</h2>

        <p>You can delete your account through the app or on this web page:</p>

        <div class="app-badge">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.6 11.4c0-1.8-.8-3.4-2.2-4.5l1.2-1.6c1.8 1.4 3 3.6 3 6.1s-1.2 4.7-3 6.1l-1.2-1.6c1.4-1.1 2.2-2.7 2.2-4.5zm-3.2 0c0-1-.4-1.8-1.1-2.4l1.2-1.6c1.2 1 2 2.4 2 4s-.8 3-2 4l-1.2-1.6c.7-.6 1.1-1.4 1.1-2.4zM7 8v8h3.2l5-4-5-4H7z"/></svg>
            Mobile App: Settings → Delete Account
        </div>
        <div class="app-badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            Website: Profile → Delete Account
        </div>

        @if(session('deleted'))
            <div class="success-box">
                <p>✅ Your account has been successfully deleted. All data has been permanently removed.</p>
            </div>
        @else
            @auth
                <div class="delete-form-wrapper">
                    <h3>Delete Your Account Now</h3>
                    <p style="color:#64748b; margin-bottom:18px;">Enter your password to confirm account deletion.</p>

                    <form method="POST" action="{{ route('account.delete') }}">
                        @csrf
                        @method('DELETE')

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required
                                   placeholder="Enter your password">
                            @error('password', 'accountDeletion')
                                <div class="error-msg">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn-danger"
                                onclick="return confirm('Are you sure? This action cannot be undone.')">
                            Delete My Account
                        </button>
                        <a href="{{ url()->previous() }}" class="btn-cancel">Cancel</a>
                    </form>
                </div>
            @endauth

            @guest
                <div class="delete-form-wrapper" style="border-color: #e2e8f0;">
                    <h3 style="color: #475569;">Log In to Delete Your Account</h3>
                    <p style="color:#64748b;">You must be logged in to delete your account. Please log in first, then return to this page.</p>
                    <a href="{{ route('login') }}?redirect={{ urlencode(url()->current()) }}" class="btn-danger" style="background:#6366f1; text-decoration:none; display:inline-block;">
                        Log In
                    </a>
                </div>
            @endguest
        @endif

        <h2>Data Retention</h2>
        <p>After account deletion, some anonymized data may be retained for legal and regulatory compliance, but it will no longer be linked to your identity. Any active job escrow funds will be processed according to our dispute resolution policy before deletion.</p>

        <h2>Need Help?</h2>
        <p>If you have trouble deleting your account, contact us at <a href="mailto:support@tendapoa.com" style="color:#6366f1;">support@tendapoa.com</a> or via WhatsApp at <a href="https://api.whatsapp.com/send/?phone=255626957138" style="color:#6366f1;">+255 626 957 138</a>.</p>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Tendapoa. All rights reserved.</p>
            <p>
                <a href="{{ route('policy.privacy') }}">Privacy Policy</a> ·
                <a href="{{ route('policy.terms') }}">Terms & Conditions</a> ·
                <a href="{{ route('policy.fees-payments') }}">Fees & Payments</a>
            </p>
        </div>
    </div>
</body>

</html>
