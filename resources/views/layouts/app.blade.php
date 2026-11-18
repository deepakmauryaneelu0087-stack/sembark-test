<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Linkboard') · Linkboard</title>
    <style>
        :root { color-scheme: light; font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; color: #17212b; background: #f4f6f8; }
        * { box-sizing: border-box; }
        body { margin: 0; }
        header { background: #fff; border-bottom: 1px solid #e3e8ed; }
        nav, main { width: min(960px, calc(100% - 32px)); margin: 0 auto; }
        nav { min-height: 64px; display: flex; align-items: center; justify-content: space-between; gap: 20px; }
        nav a, a { color: #1769aa; text-decoration: none; }
        nav a:hover, a:hover { text-decoration: underline; }
        .brand { color: #17212b; font-weight: 700; font-size: 19px; }
        .nav-actions { display: flex; align-items: center; gap: 18px; }
        main { padding: 38px 0 64px; }
        h1 { margin: 0 0 8px; font-size: 28px; letter-spacing: -.03em; }
        h2 { font-size: 18px; margin: 0 0 18px; }
        p { line-height: 1.55; }
        .muted { color: #667482; }
        .panel { background: #fff; border: 1px solid #e3e8ed; border-radius: 10px; padding: 24px; margin-top: 24px; box-shadow: 0 2px 6px #14253608; }
        .intro { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .button { display: inline-block; border: 0; border-radius: 6px; background: #1769aa; color: #fff; padding: 10px 15px; font: inherit; font-weight: 600; cursor: pointer; }
        .button:hover { background: #105687; color: #fff; text-decoration: none; }
        .button.secondary { background: #edf2f6; color: #263746; }
        label { display: block; font-weight: 600; margin: 16px 0 6px; }
        input, select { width: 100%; padding: 10px 11px; border: 1px solid #cbd4dc; border-radius: 6px; font: inherit; background: #fff; }
        input:focus, select:focus { outline: 2px solid #9cc9eb; border-color: #1769aa; }
        form .button { margin-top: 20px; }
        .notice { padding: 12px 14px; border-radius: 6px; background: #eaf5ee; color: #205b35; margin-bottom: 18px; overflow-wrap: anywhere; }
        .errors { padding: 12px 14px; border-radius: 6px; background: #fff0ef; color: #963c35; margin-bottom: 18px; }
        .errors ul { margin: 0; padding-left: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 13px 10px; text-align: left; border-bottom: 1px solid #edf0f2; vertical-align: top; }
        th { color: #667482; font-size: 12px; text-transform: uppercase; letter-spacing: .05em; }
        td { overflow-wrap: anywhere; }
        .empty { color: #667482; margin: 0; }
        .auth-card { max-width: 440px; margin: 30px auto; }
        @media (max-width: 650px) {
            .intro { flex-direction: column; }
            .panel { padding: 18px; overflow-x: auto; }
            nav { align-items: flex-start; padding: 16px 0; }
            .nav-actions { gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
        }
    </style>
</head>
<body>
<header>
    <nav>
        <a class="brand" href="{{ auth()->check() ? route('dashboard') : route('login') }}">Linkboard</a>
        @auth
            <div class="nav-actions">
                <span class="muted">{{ auth()->user()->name }} · {{ auth()->user()->role }}</span>
                <form method="POST" action="{{ route('logout') }}" style="margin:0">
                    @csrf
                    <button class="button secondary" type="submit">Log out</button>
                </form>
            </div>
        @endauth
    </nav>
</header>
<main>
    @if (session('status'))
        <div class="notice">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="errors">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    @yield('content')
</main>
</body>
</html>
