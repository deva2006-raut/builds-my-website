@extends('layouts.app')

@section('title', 'My websites')

@section('content')
    <h1>My websites</h1>
    <p class="lead">Every site built from what a business owner shared.</p>

    <div class="card">
        @if ($sites->isEmpty())
            <p class="muted">No websites yet. <a href="{{ route('sites.create') }}">Build the first one</a>.</p>
        @else
            <table>
                <thead><tr><th>Business</th><th>City</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach ($sites as $site)
                    <tr>
                        <td>{{ $site->business_name }}</td>
                        <td>{{ $site->city ?: '—' }}</td>
                        <td>{{ $site->status }}</td>
                        <td><a href="{{ route('sites.edit', $site) }}">Open</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
