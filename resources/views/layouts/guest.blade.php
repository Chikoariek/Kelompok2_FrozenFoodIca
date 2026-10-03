<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $pageTitle ?? ($title ?? 'Masuk Akun') }} - Ica Frozen Food</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="/images/ica_logo.png">

        <!-- Google Fonts: Plus Jakarta Sans -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">



        <style>
            * { font-family: 'Plus Jakarta Sans', sans-serif; box-sizing: border-box; margin: 0; padding: 0; }

            html, body {
                height: 100%;
                overflow: hidden;
                background: #F1F0E8;
            }

            .login-wrapper {
                display: flex;
                height: 100vh;
                width: 100vw;
                overflow: hidden;
            }

            /* LEFT — Freezer Photo */
            .login-left {
                flex: 0 0 50%;
                position: relative;
                overflow: hidden;
                background: #2C3E50;
                display: flex;
                flex-direction: column;
                justify-content: flex-end;
            }
            .login-left img.freezer-bg {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
                position: absolute;
                inset: 0;
            }
            /* Subtle gradient overlay on image */
            .login-left::after {
                content: '';
                position: absolute;
                inset: 0;
                background: linear-gradient(to bottom, rgba(44,62,80,0.15) 0%, rgba(44,62,80,0.6) 100%);
                pointer-events: none;
            }

            /* Transisi halus & lembut gambar freezer melebur ke latar belakang form (#F1F0E8) di desktop */
            .login-left::before {
                content: '';
                position: absolute;
                top: 0;
                right: 0;
                bottom: 0;
                width: 56px;
                background: linear-gradient(to right, rgba(241, 240, 232, 0) 0%, rgba(241, 240, 232, 0.3) 35%, rgba(241, 240, 232, 0.75) 75%, #F1F0E8 100%);
                z-index: 2;
                pointer-events: none;
            }

            .login-left-text-overlay {
                position: relative;
                padding: 2.25rem 2.5rem;
                color: #FFFFFF;
                z-index: 10;
                pointer-events: none;
                background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.3) 65%, transparent 100%);
            }
            .login-left-headline {
                font-size: 1.35rem;
                font-weight: 900;
                line-height: 1.25;
                margin-bottom: 0.5rem;
                text-shadow: 0 2px 8px rgba(0,0,0,0.6);
                color: #FFFFFF;
            }
            .login-left-subhead {
                font-size: 0.8125rem;
                color: #E2E8F0;
                line-height: 1.5;
                text-shadow: 0 1px 4px rgba(0,0,0,0.6);
            }

            /* RIGHT — Form Panel */
            .login-right {
                flex: 0 0 50%;
                background: #F1F0E8;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 1.5rem 2.5rem;
                overflow-y: auto;
                max-height: 100vh;
            }

            .login-card {
                width: 100%;
                max-width: 520px;
                text-align: center;
                margin: auto 0;
            }

            .login-logo-link {
                display: inline-block;
                text-decoration: none;
                transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), filter 0.2s ease;
            }
            .login-logo-link:hover {
                transform: scale(1.04);
            }
            .login-logo-link:active {
                transform: scale(0.98);
            }

            .login-logo {
                width: 200px;
                height: 200px;
                object-fit: contain;
                margin: 0 auto 0.75rem;
                display: block;
                filter: drop-shadow(0 2px 10px rgba(103,125,158,0.2));
                cursor: pointer;
            }

            .login-title {
                color: #2C3E50;
                font-size: 1.85rem;
                font-weight: 800;
                margin: 0 0 0.35rem;
                letter-spacing: -0.02em;
            }

            .login-subtitle {
                color: #677D9E;
                font-size: 0.82rem;
                font-weight: 400;
                margin: 0 0 1rem;
            }

            .form-group {
                margin-bottom: 0.85rem;
                text-align: left;
            }

            .form-label {
                display: block;
                font-size: 0.78rem;
                font-weight: 700;
                color: #2C3E50;
                margin-bottom: 0.35rem;
                letter-spacing: 0.01em;
            }

            .form-input-wrap {
                position: relative;
            }

            .form-input {
                width: 100%;
                padding: 0.7rem 2.5rem 0.7rem 1rem;
                border: 1.5px solid #A6B1C3;
                border-radius: 10px;
                background: #fff;
                font-size: 0.85rem;
                color: #2C3E50;
                outline: none;
                transition: border-color 0.2s, box-shadow 0.2s;
                font-family: 'Plus Jakarta Sans', sans-serif;
            }
            .form-input::placeholder { color: #A6B1C3; }
            .form-input:focus {
                border-color: #677D9E;
                box-shadow: 0 0 0 3px rgba(103,125,158,0.15);
            }

            .form-icon {
                position: absolute;
                right: 1.1rem;
                top: 50%;
                transform: translateY(-50%);
                color: #677D9E;
                font-size: 1.15rem;
                pointer-events: none;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .form-icon svg {
                stroke: #677D9E;
            }

            .form-toggle-btn {
                position: absolute;
                right: 0.85rem;
                top: 50%;
                transform: translateY(-50%);
                background: transparent;
                border: none;
                padding: 0.25rem;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #677D9E;
                border-radius: 6px;
                transition: color 0.15s, background-color 0.15s;
            }
            .form-toggle-btn:hover {
                color: #2C3E50;
                background-color: rgba(103, 125, 158, 0.1);
            }
            .form-toggle-btn svg {
                stroke: currentColor;
            }

            .input-error {
                color: #e53e3e;
                font-size: 0.75rem;
                margin-top: 0.3rem;
            }

            .btn-login {
                width: 100%;
                padding: 0.8rem;
                background: #677D9E;
                color: #fff;
                border: none;
                border-radius: 10px;
                font-size: 0.9rem;
                font-weight: 700;
                cursor: pointer;
                margin-top: 1.25rem;
                margin-bottom: 0.75rem;
                transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
                letter-spacing: 0.01em;
                font-family: 'Plus Jakarta Sans', sans-serif;
                box-shadow: 0 4px 14px rgba(103,125,158,0.3);
            }
            .btn-login:hover { background: #546885; box-shadow: 0 6px 18px rgba(103,125,158,0.45); }
            .btn-login:active { transform: scale(0.98); }

            .login-register {
                font-size: 0.9rem;
                color: #677D9E;
                margin-top: 0.5rem;
            }
            .login-register a {
                color: #677D9E;
                font-weight: 700;
                text-decoration: underline;
                text-underline-offset: 2px;
            }

            .login-back {
                margin-top: 1.5rem;
                font-size: 0.85rem;
                color: #A6B1C3;
                text-decoration: none;
                display: block;
                transition: color 0.2s;
            }
            .login-back:hover { color: #677D9E; }

            .alert-error {
                background: #fff0f0;
                border: 1px solid #fed7d7;
                color: #c53030;
                padding: 0.65rem 1rem;
                border-radius: 10px;
                font-size: 0.8rem;
                margin-bottom: 1rem;
                text-align: left;
            }

            @media (max-width: 768px) {
                html, body {
                    overflow-y: auto;
                    height: auto;
                    min-height: 100%;
                }
                .login-wrapper {
                    height: auto;
                    min-height: 100vh;
                    flex-direction: column;
                    justify-content: center;
                    align-items: center;
                }
                .login-left {
                    display: none !important;
                }
                .login-right {
                    flex: none;
                    width: 100%;
                    min-height: 100vh;
                    max-height: none;
                    padding: 2.5rem 1.25rem;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    align-items: center;
                }
                .login-card {
                    width: 100%;
                    max-width: 420px;
                    padding: 0 0.5rem;
                    margin: auto 0;
                }
                .login-logo {
                    width: 185px;
                    height: 185px;
                    max-width: 80vw;
                    margin: 0 auto 1.5rem;
                }
                .login-title {
                    font-size: 1.5rem;
                    margin-bottom: 0.25rem;
                }
                .login-subtitle {
                    font-size: 0.82rem;
                    margin-bottom: 1.25rem;
                }
            }
        </style>
    </head>
    <body>
        <div class="login-wrapper">

            <!-- LEFT: Freezer Photo -->
            <div class="login-left">
                <img
                    class="freezer-bg"
                    src="/images/freezer_showcase.jpg"
                    alt="Ica Frozen Food Showcase"
                    onerror="this.onerror=null; this.style.display='none'; this.parentElement.style.background='linear-gradient(160deg,#677D9E,#2C3E50)';"
                >
            </div>

            <!-- RIGHT: Login Form (slot from child view) -->
            <div class="login-right">
                <div class="login-card">

                    <!-- Logo (klik untuk ke homepage) -->
                    <a href="{{ route('home') }}" class="login-logo-link" title="Kembali ke Homepage">
                        <img
                            src="/images/ica_logo.png"
                            alt="Ica Frozen Food"
                            class="login-logo"
                            onerror="this.style.display='none'"
                        >
                    </a>

                    <h1 class="login-title">{{ $title ?? 'Selamat Datang Kembali!' }}</h1>
                    <p class="login-subtitle">{{ $subtitle ?? 'Silakan masuk ke akun Anda untuk melanjutkan' }}</p>

                    {{ $slot }}
                </div>
            </div>

        </div>
    </body>
</html>
