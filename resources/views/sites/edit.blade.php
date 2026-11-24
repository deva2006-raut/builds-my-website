@extends('layouts.app')

@section('title', $site->business_name)

@section('content')
    <h1>{{ $site->business_name }} <span class="pill">{{ $site->status }}</span></h1>
    <p class="lead">
        {{ $site->city ?: 'City not provided' }} · model: {{ $site->ai_model ?: 'not generated yet' }}
    </p>

    <div class="card">
        <div class="row">
            <form method="POST" action="{{ route('sites.generate', $site) }}">@csrf
                <button class="ghost" type="submit">Regenerate everything</button>
            </form>

            @if ($site->isPublished())
                <form method="POST" action="{{ route('sites.unpublish', $site) }}">@csrf
                    <button class="ghost" type="submit">Unpublish</button>
                </form>
                <a href="{{ $site->publicUrl() }}" target="_blank" rel="noopener">Open live site</a>
            @else
                <form method="POST" action="{{ route('sites.publish', $site) }}">@csrf
                    <button type="submit">Publish</button>
                </form>
            @endif
        </div>
    </div>

    @if (! empty($site->conflicts))
        <div class="card" style="border-color:#fcd34d;background:#fffbeb">
            <strong>Sources disagree — confirm before publishing</strong>
            <ul>
                @foreach ($site->conflicts as $conflict)
                    <li>
                        <strong>{{ $conflict['field'] ?? 'field' }}:</strong>
                        {{ implode(' / ', (array) ($conflict['values'] ?? [])) }}
                        @if (! empty($conflict['sources']))
                            <span class="muted">(from {{ implode(', ', (array) $conflict['sources']) }})</span>
                        @endif
                    </li>
                @endforeach
            </ul>
            <div class="hint">Nothing was auto-picked. Edit the section below with the correct value.</div>
        </div>
    @endif

    @if (! empty($site->profile['missing']))
        <div class="card">
            <strong>Not found in the sources</strong>
            <p class="muted">{{ implode(', ', (array) $site->profile['missing']) }}</p>
        </div>
    @endif

    @foreach ($site->sections as $section)
        <div class="card">
            <div class="row" style="justify-content:space-between">
                <strong>{{ ucfirst($section->key) }}</strong>
                <span class="muted">#{{ $section->position + 1 }}</span>
            </div>

            <form method="POST" action="{{ route('sections.update', [$site, $section]) }}">
                @csrf @method('PUT')

                <label>Heading</label>
                <input type="text" name="heading" value="{{ $section->content['heading'] ?? '' }}">

                <label>Subheading</label>
                <input type="text" name="subheading" value="{{ $section->content['subheading'] ?? '' }}">

                <label>Body</label>
                <textarea name="body">{{ $section->content['body'] ?? '' }}</textarea>

                <label>Items (one per line)</label>
                <textarea name="items">{{ implode("\n", $section->items()) }}</textarea>

                <div class="row" style="margin-top:12px">
                    <button type="submit">Save section</button>
                </div>
            </form>

            <form method="POST" action="{{ route('sections.regenerate', [$site, $section]) }}" style="margin-top:10px">
                @csrf
                <label>Regenerate this section only (optional instruction)</label>
                <textarea name="instruction" placeholder="Make it shorter and mention home visits"></textarea>
                <div class="row" style="margin-top:10px">
                    <button class="ghost" type="submit">Regenerate section</button>
                </div>
            </form>
        </div>
    @endforeach

    <div class="card">
        <strong>Sources</strong>
        @if ($site->sources->isEmpty())
            <p class="muted">No sources were provided.</p>
        @else
            <table>
                <thead><tr><th>Type</th><th>Origin</th><th>Status</th></tr></thead>
                <tbody>
                @foreach ($site->sources as $source)
                    <tr>
                        <td>{{ $source->type }}</td>
                        <td>{{ $source->origin }}</td>
                        <td>
                            {{ $source->status }}
                            @if ($source->error) <div class="muted">{{ $source->error }}</div> @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
