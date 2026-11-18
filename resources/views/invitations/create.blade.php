@extends('layouts.app')

@section('title', 'Invite a user')

@section('content')
<section class="panel auth-card">
    <h1>Invite someone</h1>
    <p class="muted">
        @if (auth()->user()->isRole('SuperAdmin'))
            SuperAdmins can invite Members, Sales, or Managers to an existing company.
        @else
            Company Admins can invite Sales or Managers to their own company.
        @endif
    </p>
    <form method="POST" action="{{ route('invitations.store') }}">
        @csrf
        <label for="email">Email address</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required>
        <label for="role">Role</label>
        <select id="role" name="role" required>
            @foreach (auth()->user()->isRole('SuperAdmin') ? ['Member', 'Sales', 'Manager'] : ['Sales', 'Manager'] as $role)
                <option value="{{ $role }}" @selected(old('role') === $role)>{{ $role }}</option>
            @endforeach
        </select>
        @if (auth()->user()->isRole('SuperAdmin'))
            <label for="company_id">Company</label>
            <select id="company_id" name="company_id" required>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}" @selected(old('company_id') == $company->id)>{{ $company->name }}</option>
                @endforeach
            </select>
        @else
            <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
        @endif
        <button class="button" type="submit">Create invitation</button>
        <a class="button secondary" href="{{ route('dashboard') }}">Cancel</a>
    </form>
</section>
@endsection
