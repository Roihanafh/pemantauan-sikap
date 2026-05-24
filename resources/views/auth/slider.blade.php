<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'PEMANTAUAN SIKAP') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            background: #eef3fb;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            font-family: 'Montserrat', sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 24px;
        }

        h1 {
            font-weight: 800;
            margin: 0;
        }

        p {
            font-size: 14px;
            font-weight: 400;
            line-height: 20px;
            letter-spacing: 0.4px;
            margin: 20px 0 30px;
        }

        a {
            color: #1f2937;
            font-size: 13px;
            text-decoration: none;
            margin: 12px 0;
        }

        .auth-button {
            border-radius: 20px;
            border: 1px solid #0b2f5b;
            background-color: #0b2f5b;
            color: #ffffff;
            font-size: 12px;
            font-weight: 800;
            padding: 12px 42px;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: transform 80ms ease-in;
            cursor: pointer;
        }

        .auth-button:active {
            transform: scale(0.95);
        }

        .auth-button:focus {
            outline: none;
        }

        .auth-button.ghost {
            background-color: transparent;
            border-color: #ffffff;
        }

        .auth-form {
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 44px;
            height: 100%;
            text-align: center;
        }

        .auth-input,
        .auth-select {
            background-color: #eef2f7;
            border: none;
            border-radius: 6px;
            padding: 12px 14px;
            margin: 7px 0;
            width: 100%;
            font-size: 13px;
        }

        .auth-row {
            width: 100%;
        }

        .auth-error {
            color: #b91c1c;
            font-size: 11px;
            text-align: left;
            width: 100%;
            margin: 0 0 4px;
        }

        .auth-status {
            color: #0b6b3a;
            font-size: 12px;
            margin-bottom: 12px;
        }

        .auth-container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.24), 0 10px 10px rgba(15, 23, 42, 0.18);
            position: relative;
            overflow: hidden;
            width: 920px;
            max-width: 100%;
            min-height: 580px;
        }

        .form-container {
            position: absolute;
            top: 0;
            height: 100%;
            transition: all 0.6s ease-in-out;
        }

        .sign-in-container {
            left: 0;
            width: 50%;
            z-index: 2;
        }

        .auth-container.right-panel-active .sign-in-container {
            transform: translateX(100%);
        }

        .sign-up-container {
            left: 0;
            width: 50%;
            opacity: 0;
            z-index: 1;
        }

        .auth-container.right-panel-active .sign-up-container {
            transform: translateX(100%);
            opacity: 1;
            z-index: 5;
            animation: show 0.6s;
        }

        @keyframes show {
            0%, 49.99% {
                opacity: 0;
                z-index: 1;
            }

            50%, 100% {
                opacity: 1;
                z-index: 5;
            }
        }

        .overlay-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: transform 0.6s ease-in-out;
            z-index: 100;
        }

        .auth-container.right-panel-active .overlay-container {
            transform: translateX(-100%);
        }

        .overlay {
            background: #061a33;
            background: linear-gradient(to right, #0b2f5b, #061a33);
            background-repeat: no-repeat;
            background-size: cover;
            background-position: 0 0;
            color: #ffffff;
            position: relative;
            left: -100%;
            height: 100%;
            width: 200%;
            transform: translateX(0);
            transition: transform 0.6s ease-in-out;
        }

        .auth-container.right-panel-active .overlay {
            transform: translateX(50%);
        }

        .overlay-panel {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 44px;
            text-align: center;
            top: 0;
            height: 100%;
            width: 50%;
            transform: translateX(0);
            transition: transform 0.6s ease-in-out;
        }

        .overlay-left {
            transform: translateX(-20%);
        }

        .auth-container.right-panel-active .overlay-left {
            transform: translateX(0);
        }

        .overlay-right {
            right: 0;
            transform: translateX(0);
        }

        .auth-container.right-panel-active .overlay-right {
            transform: translateX(20%);
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            margin: 10px 0 12px;
            color: #1f2937;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-align: left;
        }

        .remember-row input {
            appearance: none;
            width: 18px;
            height: 18px;
            flex: 0 0 18px;
            border: 2px solid #0b2f5b;
            border-radius: 5px;
            background-color: #ffffff;
            display: inline-grid;
            place-content: center;
            cursor: pointer;
            margin: 0;
        }

        .remember-row input::before {
            content: "";
            width: 9px;
            height: 9px;
            border-radius: 3px;
            transform: scale(0);
            transition: transform 120ms ease-in-out;
            background-color: #0b2f5b;
        }

        .remember-row input:checked::before {
            transform: scale(1);
        }

        .remember-row input:focus-visible {
            outline: 3px solid rgba(11, 47, 91, 0.22);
            outline-offset: 2px;
        }

        @media (max-width: 760px) {
            body {
                align-items: stretch;
                padding: 14px;
            }

            .auth-container {
                min-height: auto;
                overflow: visible;
            }

            .form-container,
            .sign-in-container,
            .sign-up-container {
                position: relative;
                width: 100%;
                height: auto;
                opacity: 1;
                transform: none !important;
            }

            .sign-up-container {
                display: none;
            }

            .auth-container.right-panel-active .sign-in-container {
                display: none;
            }

            .auth-container.right-panel-active .sign-up-container {
                display: block;
                animation: none;
            }

            .auth-form {
                min-height: 560px;
                padding: 32px 22px;
            }

            .overlay-container {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container {{ $activePanel === 'register' || $errors->has('name') || $errors->has('sekolah_nama') || $errors->has('password_confirmation') ? 'right-panel-active' : '' }}" id="container">
        <div class="form-container sign-up-container">
            <form class="auth-form" method="POST" action="{{ route('register') }}">
                @csrf
                <h1>Buat Akun</h1>

                <div class="auth-row">
                    <input class="auth-input" type="text" name="name" value="{{ old('name') }}" placeholder="Nama" required autocomplete="name">
                    @error('name') <div class="auth-error">{{ $message }}</div> @enderror
                </div>

                <div class="auth-row">
                    <input class="auth-input" type="email" name="email" value="{{ old('email') }}" placeholder="Email" required autocomplete="username">
                    @error('email') <div class="auth-error">{{ $message }}</div> @enderror
                </div>

                <div class="auth-row">
                    <input class="auth-input" type="text" name="sekolah_nama" id="register_sekolah_nama" value="{{ old('sekolah_nama') }}" list="register_sekolah_list" placeholder="Cari atau ketik nama sekolah" required autocomplete="organization">
                    <datalist id="register_sekolah_list">
                        @foreach ($sekolahs as $sekolah)
                            <option value="{{ $sekolah->nama }}"></option>
                        @endforeach
                    </datalist>
                    @error('sekolah_nama') <div class="auth-error">{{ $message }}</div> @enderror
                </div>

                <div class="auth-row">
                    <input class="auth-input" type="password" name="password" placeholder="Password" required autocomplete="new-password">
                    @error('password') <div class="auth-error">{{ $message }}</div> @enderror
                </div>

                <div class="auth-row">
                    <input class="auth-input" type="password" name="password_confirmation" placeholder="Konfirmasi Password" required autocomplete="new-password">
                    @error('password_confirmation') <div class="auth-error">{{ $message }}</div> @enderror
                </div>

                <button class="auth-button" type="submit">Daftar</button>
            </form>
        </div>

        <div class="form-container sign-in-container">
            <form class="auth-form" method="POST" action="{{ route('login') }}">
                @csrf
                <h1>Masuk</h1>

                @if (session('status'))
                    <div class="auth-status">{{ session('status') }}</div>
                @endif

                <div class="auth-row">
                    <input class="auth-input" type="email" name="email" value="{{ old('email') }}" placeholder="Email" required autofocus autocomplete="username">
                    @if (!($errors->has('name') || $errors->has('sekolah_nama') || $errors->has('password_confirmation')))
                        @error('email') <div class="auth-error">{{ $message }}</div> @enderror
                    @endif
                </div>

                <div class="auth-row">
                    <input class="auth-input" type="password" name="password" placeholder="Password" required autocomplete="current-password">
                    @if (!($errors->has('name') || $errors->has('sekolah_nama') || $errors->has('password_confirmation')))
                        @error('password') <div class="auth-error">{{ $message }}</div> @enderror
                    @endif
                </div>

                <label class="remember-row">
                    <input type="checkbox" name="remember">
                    Ingat saya
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">Lupa password?</a>
                @endif

                <button class="auth-button" type="submit">Masuk</button>
            </form>
        </div>

        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h1>Sudah punya akun?</h1>
                    <p>Masuk untuk melanjutkan pemantauan sikap siswa.</p>
                    <button class="auth-button ghost" id="signIn" type="button">Masuk</button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h1>Belum punya akun?</h1>
                    <p>Daftarkan akun guru dan hubungkan dengan sekolah Anda.</p>
                    <button class="auth-button ghost" id="signUp" type="button">Daftar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const signUpButton = document.getElementById('signUp');
        const signInButton = document.getElementById('signIn');
        const container = document.getElementById('container');
        signUpButton.addEventListener('click', () => {
            container.classList.add('right-panel-active');
        });

        signInButton.addEventListener('click', () => {
            container.classList.remove('right-panel-active');
        });

    </script>
</body>
</html>
