<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $site->business_name }}{{ $site->city ? ' — ' . $site->city : '' }}</title>
    <style>
        :root { --ink:#111827; --muted:#6b7280; --brand:#0f766e; }
        * { box-sizing:border-box; }
        body { margin:0; font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; color:var(--ink); }
        .wrap { max-width:1000px; margin:0 auto; padding:0 22px; }
        .hero { background:linear-gradient(135deg,#0f766e,#0e7490); color:#fff; padding:90px 0 70px; }
        .hero h1 { font-size:2.6rem; margin:0 0 12px; line-height:1.15; }
        .hero p { font-size:1.15rem; opacity:.92; margin:0; max-width:620px; }
        section { padding:56px 0; border-bottom:1px solid #e5e7eb; }
        h2 { font-size:1.6rem; margin:0 0 14px; }
        p { line-height:1.65; color:#374151; }
        ul.items { display:grid; gap:10px; padding:0; list-style:none; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); }
        ul.items li { background:#f0fdfa; border:1px solid #ccfbf1; border-radius:10px; padding:12px 14px; }
        footer { padding:30px 0; color:var(--muted); font-size:.9rem; }
        .contact a { color:var(--brand); }
    </style>
</head>
<body>
@php
    $sections = $site->sections->keyBy('key');
    $hero = $sections->get('hero');
    $contact = $sections->get('contact');
    $profile = $site->profile ?? [];
@endphp

<div class="hero">
    <div class="wrap">
        <h1>{{ $hero?->content['heading'] ?: $site->business_name }}</h1>
        <p>{{ $hero?->content['subheading'] ?: ($profile['tagline'] ?? $site->city) }}</p>
        @if (! empty($profile['phone']))
            <p style="margin-top:18px"><a style="color:#fff;font-weight:600" href="tel:{{ $profile['phone'] }}">{{ $profile['phone'] }}</a></p>
        @endif
    </div>
</div>

<div class="wrap">
    @foreach (['about', 'services', 'gallery'] as $key)
        @php $section = $sections->get($key); @endphp
        @if ($section && ($section->heading() || $section->body() || $section->items()))
            <section>
                @if ($section->heading()) <h2>{{ $section->heading() }}</h2> @endif
                @if ($section->body()) <p>{{ $section->body() }}</p> @endif
                @if ($section->items())
                    <ul class="items">
                        @foreach ($section->items() as $item) <li>{{ $item }}</li> @endforeach
                    </ul>
                @endif
            </section>
        @endif
    @endforeach

    <section class="contact">
        <h2>{{ $contact?->content['heading'] ?: 'Contact' }}</h2>
        @if ($contact?->body()) <p>{{ $contact->body() }}</p> @endif
        <p>
            @if (! empty($profile['address'])) {{ $profile['address'] }}<br> @endif
            @if (! empty($profile['phone'])) Phone: <a href="tel:{{ $profile['phone'] }}">{{ $profile['phone'] }}</a><br> @endif
            @if (! empty($profile['email'])) Email: <a href="mailto:{{ $profile['email'] }}">{{ $profile['email'] }}</a><br> @endif
            @if (! empty($profile['hours'])) Hours: {{ $profile['hours'] }}<br> @endif
        </p>
        @if (! empty($profile['socials']))
            <p>
                @foreach (array_filter($profile['socials']) as $network => $url)
                    <a href="{{ $url }}" target="_blank" rel="noopener nofollow">{{ ucfirst($network) }}</a>@if (! $loop->last) · @endif
                @endforeach
            </p>
        @endif
    </section>

    <footer>Built with AI from information supplied by the business. Facts are not invented; missing details are left out.</footer>
</div>
</body>
</html>
