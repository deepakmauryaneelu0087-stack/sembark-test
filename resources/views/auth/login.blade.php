@extends('layouts.app')

@section('title', 'Sign in')

@section('content')
<section class="panel auth-card">
    <h1>Welcome back</h1>
    <p class="muted">Sign in to manage your company's links.</p>
    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <label for="email">Email address</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" required autocomplete="current-password">
        <label style="font-weight:400"><input type="checkbox" name="remember" value="1" style="width:auto"> Keep me signed in</label>
        <button class="button" type="submit">Sign in</button>
    </form>
</section>
@endsection
