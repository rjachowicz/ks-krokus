<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <meta name="robots" content="noindex">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <title>@yield('code') — KS Krokus</title>
    <style>
        :root { color-scheme: light dark; font-family: Inter, system-ui, sans-serif; }
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; display: grid; place-items: center; padding: 24px; background: #f3f1eb; color: #181c22; }
        main { width: min(580px, 100%); padding: clamp(28px, 7vw, 56px); border: 1px solid #d7ccb8; border-radius: 18px; background: #fff; box-shadow: 0 18px 55px rgb(35 27 14 / 10%); text-align: center; }
        .code { margin: 0 0 12px; color: #9b6c1f; font: 700 clamp(3rem, 14vw, 6rem)/1 ui-monospace, monospace; letter-spacing: .04em; }
        h1 { margin: 0 0 14px; font-size: clamp(1.45rem, 5vw, 2rem); }
        p { margin: 0 auto 28px; max-width: 45ch; color: #5f5b54; line-height: 1.65; }
        a { display: inline-flex; min-height: 44px; align-items: center; padding: 10px 18px; border-radius: 9px; background: #9b6c1f; color: #fff; font-weight: 700; text-decoration: none; }
        a:hover { background: #7b5315; }
        a:focus-visible { outline: 3px solid #d8aa52; outline-offset: 4px; }
        @media (prefers-color-scheme: dark) {
            body { background: #0c1118; color: #f3f0e8; }
            main { border-color: #3b3428; background: #151b24; box-shadow: none; }
            p { color: #bcb6aa; }
            .code { color: #e0b660; }
            a { background: #d2a34b; color: #18130b; }
            a:hover { background: #e3bc72; }
        }
    </style>
</head>
<body>
<main>
    <p class="code" aria-hidden="true">@yield('code')</p>
    <h1>@yield('heading')</h1>
    <p>@yield('message')</p>
    <a href="{{ route('home') }}">Wróć na stronę główną</a>
</main>
</body>
</html>
