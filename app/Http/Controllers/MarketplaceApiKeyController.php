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

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'client_id' => 'nullable|string|max:255',
            'api_key' => 'required|string|max:255',
        ]);

        MarketplaceApiKey::create([
            'user_id' => Auth::id(),
            'name' => $data['name'],
            'client_id' => $data['client_id'],
            'api_key' => $data['api_key'],
        ]);

        return redirect()->route('marketplace.api-keys.index')
            ->with('success', 'Сервис успешно добавлен.');
    }

    public function update(Request $request, MarketplaceApiKey $apiKey)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'client_id' => 'nullable|string|max:255',
            'api_key' => 'required|string|max:255',
        ]);

        $apiKey->update($data);

        return redirect()->route('marketplace.api-keys.index')
            ->with('success', 'Сервис обновлён.');
    }

    public function destroy(MarketplaceApiKey $apiKey)
    {
        $apiKey->delete();

        return redirect()->route('marketplace.api-keys.index')
            ->with('success', 'Сервис удалён.');
    }

}
