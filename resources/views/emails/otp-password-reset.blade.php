<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Msimbo wa OTP - TendaPoa</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f7fb;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .wrapper {
            max-width: 560px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 32px;
            text-align: center;
        }
        .header .logo {
            font-size: 48px;
            margin-bottom: 8px;
        }
        .header h1 {
            color: #ffffff;
            font-size: 26px;
            font-weight: 800;
            margin: 0 0 4px 0;
        }
        .header p {
            color: rgba(255,255,255,0.85);
            font-size: 14px;
            margin: 0;
        }
        .body {
            padding: 36px 32px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 700;
            color: #1e1b4b;
            margin-bottom: 12px;
        }
        .message {
            font-size: 15px;
            color: #4b5563;
            line-height: 1.7;
            margin-bottom: 28px;
        }
        .otp-box {
            background: linear-gradient(135deg, #f0f4ff 0%, #f5f0ff 100%);
            border: 2px dashed #6366f1;
            border-radius: 14px;
            padding: 28px;
            text-align: center;
            margin-bottom: 28px;
        }
        .otp-label {
            font-size: 13px;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        .otp-code {
            font-size: 48px;
            font-weight: 900;
            color: #4f46e5;
            letter-spacing: 10px;
        }
        .otp-expires {
            font-size: 13px;
            color: #9ca3af;
            margin-top: 10px;
        }
        .warning {
            background: #fff7ed;
            border-left: 4px solid #f97316;
            border-radius: 8px;
            padding: 14px 16px;
            font-size: 13px;
            color: #92400e;
            margin-bottom: 24px;
            line-height: 1.6;
        }
        .footer {
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            padding: 24px 32px;
            text-align: center;
        }
        .footer p {
            font-size: 12px;
            color: #9ca3af;
            margin: 4px 0;
        }
        .footer a {
            color: #6366f1;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <div class="logo">🧹</div>
            <h1>TendaPoa</h1>
            <p>Usafi wa Kuegemea, Kazi za Kuegemea</p>
        </div>

        <div class="body">
            <div class="greeting">Habari {{ $userName }},</div>

            <div class="message">
                Tunapokea ombi lako la kubadilisha neno siri la akaunti yako ya TendaPoa.
                Tumia msimbo huu wa OTP kubadilisha neno siri lako:
            </div>

            <div class="otp-box">
                <div class="otp-label">Msimbo Wako wa OTP</div>
                <div class="otp-code">{{ $otp }}</div>
                <div class="otp-expires">⏱️ Msimbo huu utaisha baada ya dakika 10</div>
            </div>

            <div class="warning">
                ⚠️ <strong>Tahadhari:</strong> Usiomwambie mtu yeyote msimbo huu.
                TendaPoa haitawahi kukuomba msimbo wako wa OTP kwa njia nyingine yoyote.
                Kama hukuomba kubadilisha neno siri, puuza barua hii.
            </div>

            <div class="message">
                Kama hukuomba mabadiliko haya, akaunti yako iko salama. Hakuna hatua inayohitajika.
            </div>
        </div>

        <div class="footer">
            <p>Barua hii imetumwa na <a href="{{ config('app.url') }}">TendaPoa</a></p>
            <p>© {{ date('Y') }} TendaPoa. Haki zote zimehifadhiwa.</p>
        </div>
    </div>
</body>
</html>
