<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — Zawaj Pro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:wght@400;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --ink:#1A1A1A; --clay:#C8956C; --bg:#FDFAF6; --muted:#6B665E; --faint:#9A958D; --outline:#D4CFC7; }
        * { box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background: var(--bg); color: var(--ink);
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; margin: 0; padding: 2rem;
        }
        .wrap { max-width: 540px; text-align: center; }
        .code {
            font-family: 'Noto Serif', Georgia, serif; font-weight: 700;
            font-size: clamp(4.5rem, 18vw, 8rem); line-height: 1; color: var(--clay); margin: 0;
        }
        h1 { font-family: 'Noto Serif', Georgia, serif; font-size: 1.75rem; margin: 0.5rem 0 0.75rem; color: var(--ink); }
        p { color: var(--muted); line-height: 1.65; font-size: 1rem; margin: 0 auto 2rem; max-width: 26rem; }
        .btn {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: var(--ink); color: #fff; text-decoration: none;
            font-weight: 600; font-size: 0.9rem; padding: 0.75rem 1.5rem; border-radius: 0.5rem;
            transition: background .2s;
        }
        .btn:hover { background: #000; }
        .brand {
            margin-top: 2.5rem; font-size: 0.7rem; text-transform: uppercase;
            letter-spacing: 0.18em; font-weight: 700; color: var(--faint);
        }
    </style>
</head>
<body>
    <div class="wrap">
        <p class="code">@yield('code')</p>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <a class="btn" href="{{ url('/') }}">Retour à l'accueil</a>
        <p class="brand">Zawaj Pro · Algérie</p>
    </div>
</body>
</html>
