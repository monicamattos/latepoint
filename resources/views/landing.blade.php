<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Vista Booking</title>
    <link href="{{ asset('assets/img/short-logo.png') }}" rel="icon">
    <style>
        :root {
            color-scheme: light;
            font-family: "Poppins", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top, #f0f7ff 0%, #e4ecff 55%, #dbe3ff 100%);
            color: #0f1a3a;
        }

        .wrapper {
            width: min(90vw, 540px);
            background: rgba(255, 255, 255, 0.85);
            border-radius: 32px;
            padding: 48px clamp(24px, 6vw, 56px);
            text-align: center;
            box-shadow: 0 35px 80px rgba(15, 26, 58, 0.18);
        }

        .logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            border-radius: 24px;
            background: #0f1a3a;
            color: #fff;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 24px;
        }

        h1 {
            margin: 0 0 12px;
            font-size: clamp(28px, 5vw, 40px);
            letter-spacing: -0.02em;
        }

        p {
            margin: 0 0 32px;
            font-size: clamp(16px, 3.5vw, 18px);
            color: #3a4a7a;
            line-height: 1.6;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            justify-content: center;
        }

        .actions a {
            padding: 14px 28px;
            border-radius: 999px;
            font-weight: 600;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .actions a.primary {
            background: #0f66ff;
            color: #fff;
            box-shadow: 0 15px 30px rgba(15, 102, 255, 0.35);
        }

        .actions a.secondary {
            background: rgba(15, 26, 58, 0.08);
            color: #0f1a3a;
        }

        .actions a:hover,
        .actions a:focus-visible {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(15, 26, 58, 0.25);
        }

        footer {
            margin-top: 40px;
            font-size: 14px;
            color: rgba(15, 26, 58, 0.55);
        }

        @media (max-width: 480px) {
            .wrapper {
                padding: 40px 20px;
            }
        }
    </style>
</head>

<body>
    <main class="wrapper">
        <div class="logo">VB</div>
        <h1>Sua agenda começa aqui</h1>
        <p>Escolha a melhor forma de continuar: agende um atendimento agora ou acesse sua área exclusiva.</p>
        <div class="actions">
            <a class="primary" href="{{ route('app-appointments') }}">Agendar</a>
            <a class="secondary" href="{{ route('login') }}">Acessar minha área</a>
        </div>
        <footer>
            &copy; {{ date('Y') }} Vista Booking. Todos os direitos reservados.
        </footer>
    </main>
</body>

</html>
