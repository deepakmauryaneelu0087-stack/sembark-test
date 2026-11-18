<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ShortUrlController extends Controller
{
    public function create(Request $request): View
    {
        abort_unless($request->user()->canCreateShortUrls(), 403);

        return view('short-urls.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->canCreateShortUrls(), 403);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:120'],
            'destination_url' => ['required', 'url:http,https', 'max:2048'],
        ]);

        do {
            $slug = Str::lower(Str::random(8));
        } while (ShortUrl::where('slug', $slug)->exists());

        $shortUrl = ShortUrl::create([
            ...$validated,
            'company_id' => $user->company_id,
            'created_by' => $user->id,
            'slug' => $slug,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('status', 'Short link created: '.route('short-urls.resolve', $shortUrl->slug));
    }

    public function resolve(string $slug): RedirectResponse
    {
        $shortUrl = ShortUrl::where('slug', $slug)->firstOrFail();

        return redirect()->away($shortUrl->destination_url);
    }
}
