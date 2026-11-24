@extends('layouts.app')

@section('title', 'New website')

@section('content')
    <h1>New website</h1>
    <p class="lead">Give me whatever the business owner shared. Anything left blank is left blank — nothing is invented.</p>

    <form method="POST" action="{{ route('sites.store') }}" enctype="multipart/form-data" class="card">
        @csrf

        <label for="business_name">Business name *</label>
        <input type="text" id="business_name" name="business_name" value="{{ old('business_name') }}" required maxlength="120">
        @error('business_name') <div class="hint" style="color:#b91c1c">{{ $message }}</div> @enderror

        <label for="city">City</label>
        <input type="text" id="city" name="city" value="{{ old('city') }}" maxlength="80">

        <label for="links">Links (one per line)</label>
        <textarea id="links" name="links" placeholder="https://instagram.com/...&#10;https://g.page/...&#10;https://business.example.com">{{ old('links') }}</textarea>
        <div class="hint">Instagram, Facebook, Google Business listing, existing website. Blocked pages are reported, not guessed.</div>

        <label for="files">Files (PDF price list, photos)</label>
        <input type="file" id="files" name="files[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp,.txt,.csv">
        <div class="hint">Max 10 MB each.</div>

        <label for="note">Anything else the owner told you</label>
        <textarea id="note" name="note" maxlength="5000" placeholder="Open 10am-8pm, home visits in Nagpur, no online payment...">{{ old('note') }}</textarea>

        <div class="row" style="margin-top:18px">
            <button type="submit">Generate website</button>
            <a class="muted" href="{{ route('home') }}">Cancel</a>
        </div>
    </form>
@endsection
