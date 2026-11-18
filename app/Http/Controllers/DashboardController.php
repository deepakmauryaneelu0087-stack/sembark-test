<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $urls = ShortUrl::query()->with(['creator', 'company']);

        if ($user->isRole('Admin')) {
            $urls->where('company_id', '!=', $user->company_id);
        } elseif ($user->isRole('Member')) {
            $urls->where('created_by', '!=', $user->id);
        } elseif ($user->isRole('Sales', 'Manager')) {
            $urls->where('company_id', $user->company_id);
        } else {
            $urls->whereRaw('1 = 0');
        }

        return view('dashboard', [
            'shortUrls' => $urls->latest()->get(),
            'user' => $user,
        ]);
    }
}
