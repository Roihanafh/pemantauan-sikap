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
        * { box-sizing: border-box; }

        body {
            background: #eef3fb;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 24px;
            font-family: 'Montserrat', sans-serif;
            color: #1f2937;
        }

        .auth-card {
            width: 920px;
            max-width: 100%;
            min-height: 520px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.24), 0 10px 10px rgba(15, 23, 42, 0.18);
        }

        .auth-panel {
            padding: 54px 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }

        .brand-panel {
            background: linear-gradient(to right, #0b2f5b, #061a33);
            color: #ffffff;
        }

        h1 {
            margin: 0;
            font-size: 30px;
            font-weight: 800;
        }

        p {
            font-size: 14px;
            line-height: 20px;
            letter-spacing: 0.4px;
            margin: 18px 0 26px;
        }

        .auth-input {
            background-color: #eef2f7;
            border: none;
            border-radius: 6px;
            padding: 13px 14px;
            margin: 8px 0;
            width: 100%;
            font-size: 13px;
        }

        .auth-button {
            border-radius: 20px;
            border: 1px solid #0b2f5b;
            background-color: #0b2f5b;
            color: #ffffff;
            font-size: 12px;
            font-weight: 800;
            padding: 12px 34px;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            margin-top: 12px;
        }

        .auth-button.ghost {
            display: inline-block;
            background: transparent;
            border-color: #ffffff;
            color: #ffffff;
            text-decoration: none;
        }

        .auth-error {
            color: #b91c1c;
            font-size: 12px;
            text-align: left;
            margin-top: 2px;
        }

        .auth-status {
            color: #0b6b3a;
            font-size: 13px;
            margin-bottom: 14px;
        }

        @media (max-width: 760px) {
            .auth-card {
                grid-template-columns: 1fr;
            }

            .brand-panel {
                min-height: 220px;
            }
        }
    </style>
</head>
<body>
    <main class="auth-card">
        <section class="auth-panel">
            <h1>Lupa Password</h1>
            <p>Masukkan email akun Anda. Kami akan mengirimkan link untuk membuat password baru.</p>

            @if (session('status'))
                <div class="auth-status">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <input class="auth-input" type="email" name="email" value="{{ old('email') }}" placeholder="Email" required autofocus autocomplete="username">
                @error('email')
                    <div class="auth-error">{{ $message }}</div>
                @enderror

                <button class="auth-button" type="submit">Kirim Link Reset</button>
            </form>
        </section>

        <section class="auth-panel brand-panel">
            <h1>Pemantauan Sikap</h1>
            <p>Kembali masuk jika Anda sudah mengingat password akun.</p>
            <a class="auth-button ghost" href="{{ route('login') }}">Masuk</a>
        </section>
    </main>
</body>
</html>
