<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login — {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            --border-color: rgba(255, 255, 255, 0.06);
            --bg-glass: rgba(255, 255, 255, 0.03);
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
            overflow: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Animated background orbs */
        .bg-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
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

        .bg-orb-3 {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.15), transparent);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: float3 18s ease-in-out infinite;
        }

        @keyframes float1 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(40px, 30px) scale(1.1); }
        }
        @keyframes float2 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-30px, -40px) scale(1.05); }
        }
        @keyframes float3 {
            0%, 100% { transform: translate(-50%, -50%) scale(1); }
            50% { transform: translate(-45%, -55%) scale(1.15); }
        }

        /* Grid pattern overlay */
        .grid-pattern {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            padding: 20px;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card {
            background: rgba(18, 18, 28, 0.8);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 44px 36px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5),
                        0 0 80px rgba(124, 58, 237, 0.1);
        }

        .login-brand {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-brand .brand-icon {
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

        .login-brand h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: -0.02em;
        }

        .login-brand p {
            font-size: 14px;
            color: var(--text-muted);
        }

        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(124, 58, 237, 0.12);
            border: 1px solid rgba(124, 58, 237, 0.25);
            color: var(--accent-primary-light);
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 12px;
        }

        .btn-google {
            width: 100%;
            padding: 14px 20px;
            background: rgba(255, 255, 255, 0.06);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 14px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
            margin-top: 24px;
        }

        .btn-google:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(124, 58, 237, 0.25);
        }

        .btn-google:active {
            transform: translateY(0);
        }

        .google-icon {
            width: 22px;
            height: 22px;
            flex-shrink: 0;
        }

        .auth-note {
            margin-top: 24px;
            padding: 14px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.04);
            border-radius: 12px;
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.25);
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: #f87171;
            animation: shake 0.4s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    </style>
</head>
<body>
    {{-- Background Effects --}}
    <div class="bg-orb bg-orb-1"></div>
    <div class="bg-orb bg-orb-2"></div>
    <div class="bg-orb bg-orb-3"></div>
    <div class="grid-pattern"></div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-brand">
                <div class="brand-icon">
                    <i data-lucide="shield-check" style="width:30px;height:30px;color:white;"></i>
                </div>
                <h1>Admin Portal</h1>
                <p>Secure System Management Console</p>
                <div class="security-badge">
                    <i data-lucide="lock" style="width:14px;height:14px;"></i>
                    Google OAuth + Mandatory 2FA
                </div>
            </div>

            {{-- Error Messages --}}
            @if ($errors->any())
                <div class="error-message">
                    <i data-lucide="alert-circle" style="width:18px;height:18px;flex-shrink:0;"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="error-message">
                    <i data-lucide="alert-circle" style="width:18px;height:18px;flex-shrink:0;"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if (session('success'))
                <div style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.25);border-radius:12px;padding:14px 16px;margin-bottom:20px;display:flex;align-items:center;gap:10px;font-size:13px;color:#34d399;">
                    <i data-lucide="check-circle" style="width:18px;height:18px;flex-shrink:0;"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <a href="{{ route('admin.auth.google') }}" class="btn-google">
                <svg class="google-icon" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                Sign in with Google
            </a>

            <div class="auth-note">
                <i data-lucide="shield-alert" style="width:14px;height:14px;margin-bottom:4px;color:var(--accent-primary-light);"></i><br>
                Admin panel access is restricted exclusively to authorized Google Workspace accounts with Two-Factor Authentication.
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
