@extends('layouts.app')

@section('title', 'Create short link')

@section('content')
<section class="panel auth-card">
    <h1>Create a short link</h1>
    <p class="muted">Only Sales and Managers can create links. Anyone opening one must be signed in.</p>
    <form method="POST" action="{{ route('short-urls.store') }}">
        @csrf
        <label for="title">Link name <span class="muted">(optional)</span></label>
        <input id="title" name="title" value="{{ old('title') }}" maxlength="120">
        <label for="destination_url">Destination URL</label>
        <input id="destination_url" name="destination_url" type="url" value="{{ old('destination_url') }}" placeholder="https://example.com/page" required>
        <button class="button" type="submit">Create link</button>
        <a class="button secondary" href="{{ route('dashboard') }}">Cancel</a>
    </form>
</section>
@endsection
