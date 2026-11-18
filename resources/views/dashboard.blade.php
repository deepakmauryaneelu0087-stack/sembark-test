@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<section class="intro">
    <div>
        <h1>Good to see you, {{ $user->name }}</h1>
        <p class="muted">Your links and access are based on your {{ strtolower($user->role) }} role.</p>
    </div>
    <div class="actions">
        @if ($user->canCreateShortUrls())
            <a class="button" href="{{ route('short-urls.create') }}">Create short link</a>
        @endif
        @if ($user->canInviteUsers())
            <a class="button secondary" href="{{ route('invitations.create') }}">Invite someone</a>
        @endif
    </div>
</section>

<section class="panel">
    <h2>Short links</h2>
    @if ($user->isRole('SuperAdmin'))
        <p class="empty">Short-link listings are not available to SuperAdmins.</p>
    @elseif ($shortUrls->isEmpty())
        <p class="empty">There are no short links in your view yet.</p>
    @else
        <table>
            <thead>
            <tr><th>Name</th><th>Short link</th><th>Destination</th><th>Company</th><th>Created by</th></tr>
            </thead>
            <tbody>
            @foreach ($shortUrls as $shortUrl)
                <tr>
                    <td>{{ $shortUrl->title ?: 'Untitled link' }}</td>
                    <td><a href="{{ route('short-urls.resolve', $shortUrl->slug) }}">{{ route('short-urls.resolve', $shortUrl->slug) }}</a></td>
                    <td>{{ $shortUrl->destination_url }}</td>
                    <td>{{ $shortUrl->company->name }}</td>
                    <td>{{ $shortUrl->creator->name }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</section>
@endsection
