<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MarketplaceApiKeyController extends Controller
{
    public function index(Request $request)
    {
        $services = MarketplaceApiKey::where('user_id', Auth::user()->id)
            ->select('id', 'name', 'client_id', 'api_key', 'created_at')
            ->latest()
            ->get();

        return Inertia::render('MarketplaceApiKeys/Index', [
            'services' => $services,
        ]);
    }
}
