<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'AI Builds My Website')</title>
    <style>
        :root { --ink:#111827; --muted:#6b7280; --line:#e5e7eb; --brand:#4f46e5; --bg:#f9fafb; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; color:var(--ink); background:var(--bg); }
        header.bar { background:#fff; border-bottom:1px solid var(--line); padding:14px 20px; display:flex; justify-content:space-between; align-items:center; }
        header.bar a { color:var(--ink); text-decoration:none; font-weight:600; }
        main { max-width:900px; margin:0 auto; padding:24px 20px 60px; }
        h1 { font-size:1.5rem; margin:0 0 6px; }
        p.lead { color:var(--muted); margin:0 0 22px; }
        .card { background:#fff; border:1px solid var(--line); border-radius:12px; padding:18px; margin-bottom:18px; }
        label { display:block; font-weight:600; font-size:.9rem; margin:12px 0 6px; }
        input[type=text], input[type=file], textarea { width:100%; padding:10px 12px; border:1px solid var(--line); border-radius:8px; font:inherit; background:#fff; }
        textarea { min-height:90px; resize:vertical; }
        .hint { color:var(--muted); font-size:.82rem; margin-top:4px; }
        button { font:inherit; border:0; border-radius:8px; padding:10px 16px; background:var(--brand); color:#fff; font-weight:600; cursor:pointer; }
        button.ghost { background:#fff; color:var(--ink); border:1px solid var(--line); }
        .alert { border-radius:10px; padding:12px 14px; margin-bottom:16px; font-size:.92rem; }
        .alert.ok { background:#ecfdf5; border:1px solid #a7f3d0; }
        .alert.err { background:#fef2f2; border:1px solid #fecaca; }
        .pill { display:inline-block; font-size:.75rem; padding:3px 8px; border-radius:999px; background:#eef2ff; color:#3730a3; }
        .row { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
        .muted { color:var(--muted); }
        table { width:100%; border-collapse:collapse; font-size:.9rem; }
        td, th { text-align:left; padding:8px 6px; border-bottom:1px solid var(--line); vertical-align:top; }
    </style>
</head>
<body>
<header class="bar">
    <a href="{{ route('home') }}">AI Builds My Website</a>
    <a href="{{ route('sites.create') }}">+ New site</a>
</header>
<main>
    @if (session('status'))
        <div class="alert ok">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert err">{{ session('error') }}</div>
    @endif

    @yield('content')
</main>
</body>
</html>
