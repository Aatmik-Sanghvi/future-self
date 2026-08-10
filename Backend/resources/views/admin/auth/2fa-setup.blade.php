<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Set Up 2FA — {{ config('app.name') }} Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    <style>
        :root {
            --bg-primary: #0a0a0f;
            --accent-primary: #7c3aed;
            --accent-primary-light: #a78bfa;
            --accent-secondary: #06b6d4;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --border-color: rgba(255, 255, 255, 0.08);
            --bg-glass: rgba(18, 18, 28, 0.8);
            --gradient-primary: linear-gradient(135deg, #7c3aed 0%, #06b6d4 100%);
        }

        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 30px 20px;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Animated background orbs */
        .bg-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0.35;
            pointer-events: none;
        }

        .bg-orb-1 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(124, 58, 237, 0.3), transparent);
            top: -150px;
            left: -100px;
            animation: float1 12s ease-in-out infinite;
        }

        .bg-orb-2 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(6, 182, 212, 0.25), transparent);
            bottom: -100px;
            right: -80px;
            animation: float2 15s ease-in-out infinite;
        }

        @keyframes float1 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(40px, 30px) scale(1.1); }
        }
        @keyframes float2 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-30px, -40px) scale(1.05); }
        }

        .grid-pattern {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        .setup-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 520px;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .setup-card {
            background: var(--bg-glass);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 40px 36px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5),
                        0 0 80px rgba(124, 58, 237, 0.1);
        }

        .setup-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .setup-header .icon-wrapper {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            background: var(--gradient-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            box-shadow: 0 8px 24px rgba(124, 58, 237, 0.35);
        }

        .setup-header h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .setup-header p {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .step-box {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .step-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 600;
            color: var(--accent-primary-light);
            margin-bottom: 14px;
        }

        .step-badge {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: rgba(124, 58, 237, 0.2);
            border: 1px solid rgba(124, 58, 237, 0.4);
            color: var(--accent-primary-light);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
        }

        .qr-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            margin-top: 10px;
        }

        .qr-box {
            background: #ffffff;
            padding: 16px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-box svg {
            display: block;
            max-width: 180px;
            height: auto;
        }

        .secret-key-box {
            width: 100%;
            background: rgba(0, 0, 0, 0.3);
            border: 1px dashed rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .secret-key-code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 14px;
            font-weight: 600;
            color: var(--accent-secondary);
            letter-spacing: 2px;
            word-break: break-all;
        }

        .btn-copy {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: var(--text-primary);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }

        .btn-copy:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }

        .otp-input {
            width: 100%;
            padding: 14px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            color: var(--text-primary);
            font-family: 'JetBrains Mono', monospace;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 8px;
            text-align: center;
            outline: none;
            transition: all 0.2s;
        }

        .otp-input:focus {
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
            background: rgba(0, 0, 0, 0.4);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 4px 16px rgba(124, 58, 237, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(124, 58, 237, 0.45);
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.25);
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: #f87171;
        }

        .footer-action {
            text-align: center;
            margin-top: 20px;
        }

        .btn-logout {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 13px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s;
        }

        .btn-logout:hover {
            color: #f87171;
        }
    </style>
</head>
<body>
    <div class="bg-orb bg-orb-1"></div>
    <div class="bg-orb bg-orb-2"></div>
    <div class="grid-pattern"></div>

    <div class="setup-container">
        <div class="setup-card">
            <div class="setup-header">
                <div class="icon-wrapper">
                    <i data-lucide="qr-code" style="width:30px;height:30px;color:white;"></i>
                </div>
                <h1>Set Up Two-Factor Auth</h1>
                <p>Protect your Admin account with Google Authenticator</p>
            </div>

            @if ($errors->any())
                <div class="error-message">
                    <i data-lucide="alert-circle" style="width:18px;height:18px;flex-shrink:0;"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <!-- Step 1: Scan QR Code -->
            <div class="step-box">
                <div class="step-title">
                    <span class="step-badge">1</span>
                    Scan with Authenticator App
                </div>
                <div class="qr-wrapper">
                    @if(!empty($qrCodeSvg))
                        <div class="qr-box">
                            {!! $qrCodeSvg !!}
                        </div>
                    @else
                        <div style="padding:20px;text-align:center;color:var(--text-muted);font-size:13px;">
                            QR Code rendering unavailable. Use manual secret key below.
                        </div>
                    @endif

                    <div class="secret-key-box">
                        <div>
                            <div style="font-size:11px;color:var(--text-muted);margin-bottom:2px;">Manual Secret Key</div>
                            <div class="secret-key-code" id="secretKeyText">{{ $secretKey }}</div>
                        </div>
                        <button type="button" class="btn-copy" onclick="copySecretKey()">
                            <i data-lucide="copy" style="width:14px;height:14px;"></i>
                            <span id="copyBtnText">Copy</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 2: Verify OTP -->
            <form method="POST" action="{{ route('admin.2fa.confirm') }}">
                @csrf
                <div class="step-box">
                    <div class="step-title">
                        <span class="step-badge">2</span>
                        Enter 6-Digit Verification Code
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <input
                            type="text"
                            name="one_time_password"
                            class="otp-input"
                            placeholder="000000"
                            maxlength="6"
                            pattern="[0-9]{6}"
                            inputmode="numeric"
                            required
                            autofocus
                            autocomplete="one-time-code"
                        >
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i data-lucide="shield-check" style="width:18px;height:18px;"></i>
                    Verify & Enable 2FA
                </button>
            </form>

            <div class="footer-action">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="btn-logout">
                        <i data-lucide="log-out" style="width:14px;height:14px;"></i>
                        Sign out and return to login
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function copySecretKey() {
            const keyText = document.getElementById('secretKeyText').innerText;
            navigator.clipboard.writeText(keyText).then(() => {
                const btnText = document.getElementById('copyBtnText');
                btnText.innerText = 'Copied!';
                setTimeout(() => { btnText.innerText = 'Copy'; }, 2000);
            });
        }
    </script>
</body>
</html>
