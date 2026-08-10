<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Two-Factor Challenge — {{ config('app.name') }} Admin</title>

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
            padding: 20px;
            overflow: hidden;
            -webkit-font-smoothing: antialiased;
        }

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

        .challenge-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .challenge-card {
            background: var(--bg-glass);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 44px 36px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5),
                        0 0 80px rgba(124, 58, 237, 0.1);
        }

        .challenge-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .icon-badge {
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

        .challenge-header h1 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .user-email-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 6px;
        }

        .otp-input {
            width: 100%;
            padding: 16px;
            background: rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 14px;
            color: var(--text-primary);
            font-family: 'JetBrains Mono', monospace;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 8px;
            text-align: center;
            outline: none;
            transition: all 0.2s;
            margin-bottom: 20px;
        }

        .otp-input:focus {
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.2);
            background: rgba(0, 0, 0, 0.5);
        }

        .recovery-input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            color: var(--text-primary);
            font-family: 'JetBrains Mono', monospace;
            font-size: 15px;
            font-weight: 600;
            text-align: center;
            outline: none;
            transition: all 0.2s;
            margin-bottom: 20px;
        }

        .recovery-input:focus {
            border-color: var(--accent-secondary);
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.2);
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

        .toggle-link {
            text-align: center;
            margin-top: 16px;
        }

        .toggle-link button {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 13px;
            cursor: pointer;
            text-decoration: underline;
            transition: color 0.2s;
        }

        .toggle-link button:hover {
            color: var(--accent-primary-light);
        }

        .footer-action {
            text-align: center;
            margin-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            padding-top: 20px;
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

    <div class="challenge-container">
        <div class="challenge-card">
            <div class="challenge-header">
                <div class="icon-badge">
                    <i data-lucide="shield-alert" style="width:30px;height:30px;color:white;"></i>
                </div>
                <h1>Two-Factor Verification</h1>
                <div class="user-email-badge">
                    <i data-lucide="user" style="width:14px;height:14px;"></i>
                    {{ $user->email }}
                </div>
            </div>

            @if ($errors->any())
                <div class="error-message">
                    <i data-lucide="alert-circle" style="width:18px;height:18px;flex-shrink:0;"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.2fa.verify') }}" id="challengeForm">
                @csrf

                <div id="totpSection">
                    <p style="text-align:center;font-size:13px;color:var(--text-secondary);margin-bottom:14px;">
                        Enter the 6-digit code from your authenticator app
                    </p>
                    <input
                        type="text"
                        name="one_time_password"
                        id="totpInput"
                        class="otp-input"
                        placeholder="000000"
                        maxlength="6"
                        pattern="[0-9]{6}"
                        inputmode="numeric"
                        autofocus
                        autocomplete="one-time-code"
                    >
                </div>

                <div id="recoverySection" style="display:none;">
                    <p style="text-align:center;font-size:13px;color:var(--text-secondary);margin-bottom:14px;">
                        Enter one of your emergency recovery codes
                    </p>
                    <input
                        type="text"
                        name="recovery_code"
                        id="recoveryInput"
                        class="recovery-input"
                        placeholder="xxxxx-xxxxx"
                        disabled
                    >
                </div>

                <button type="submit" class="btn-submit">
                    <i data-lucide="key-round" style="width:18px;height:18px;"></i>
                    Verify Authenticator
                </button>
            </form>

            <div class="toggle-link">
                <button type="button" id="toggleBtn" onclick="toggleMode()">
                    Use a recovery code instead
                </button>
            </div>

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

        let useRecovery = false;

        function toggleMode() {
            useRecovery = !useRecovery;

            const totpSec = document.getElementById('totpSection');
            const recSec = document.getElementById('recoverySection');
            const totpInp = document.getElementById('totpInput');
            const recInp = document.getElementById('recoveryInput');
            const toggleBtn = document.getElementById('toggleBtn');

            if (useRecovery) {
                totpSec.style.display = 'none';
                totpInp.disabled = true;

                recSec.style.display = 'block';
                recInp.disabled = false;
                recInp.focus();

                toggleBtn.innerText = 'Use Authenticator app code instead';
            } else {
                recSec.style.display = 'none';
                recInp.disabled = true;

                totpSec.style.display = 'block';
                totpInp.disabled = false;
                totpInp.focus();

                toggleBtn.innerText = 'Use a recovery code instead';
            }
        }
    </script>
</body>
</html>
