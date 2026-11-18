<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function create(Request $request): View
    {
        abort_unless($request->user()->canInviteUsers(), 403);

        return view('invitations.create', [
            'companies' => $request->user()->isRole('SuperAdmin') ? Company::orderBy('name')->get() : collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $inviter = $request->user();
        abort_unless($inviter->canInviteUsers(), 403);

        $allowedRoles = $inviter->isRole('SuperAdmin')
            ? ['Member', 'Sales', 'Manager']
            : ['Sales', 'Manager'];

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in($allowedRoles)],
            'company_id' => [
                'required',
                'integer',
                Rule::exists('companies', 'id'),
            ],
        ]);

        if ($inviter->isRole('Admin')) {
            abort_unless((int) $validated['company_id'] === $inviter->company_id, 403);
        }

        $invitation = Invitation::create([
            'company_id' => $inviter->isRole('Admin') ? $inviter->company_id : $validated['company_id'],
            'invited_by' => $inviter->id,
            'email' => $validated['email'],
            'role' => $validated['role'],
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);

        return redirect()
            ->route('dashboard')
            ->with('status', 'Invitation created. Share this link with the new user: '.route('invitations.accept', $invitation->token));
    }

    public function accept(string $token): View
    {
        return view('invitations.accept', ['invitation' => $this->activeInvitation($token)]);
    }

    public function complete(Request $request, string $token): RedirectResponse
    {
        $invitation = $this->activeInvitation($token);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($invitation, $validated): void {
            $lockedInvitation = Invitation::whereKey($invitation->id)->lockForUpdate()->firstOrFail();
            abort_if($lockedInvitation->accepted_at || $lockedInvitation->expires_at->isPast(), 404);
            abort_if(User::where('email', $lockedInvitation->email)->exists(), 409);

            User::create([
                'name' => $validated['name'],
                'email' => $lockedInvitation->email,
                'password' => Hash::make($validated['password']),
                'company_id' => $lockedInvitation->company_id,
                'role' => $lockedInvitation->role,
            ]);

            $lockedInvitation->update(['accepted_at' => now()]);
        });

        return redirect()->route('login')->with('status', 'Your account is ready. You can now sign in.');
    }

    private function activeInvitation(string $token): Invitation
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();
        abort_if($invitation->accepted_at || $invitation->expires_at->isPast(), 404);

        return $invitation;
    }
}
