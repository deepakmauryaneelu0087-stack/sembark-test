@extends('layouts.app')

@section('title', 'Accept invitation')

@section('content')
<section class="panel auth-card">
    <h1>Join {{ $invitation->company->name }}</h1>
    <p class="muted">Create your account as a {{ $invitation->role }} using {{ $invitation->email }}.</p>
    <form method="POST" action="{{ route('invitations.complete', $invitation->token) }}">
        @csrf
        <label for="name">Your name</label>
        <input id="name" name="name" value="{{ old('name') }}" required autofocus>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" minlength="8" required autocomplete="new-password">
        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" required autocomplete="new-password">
        <button class="button" type="submit">Accept invitation</button>
    </form>
</section>
@endsection
