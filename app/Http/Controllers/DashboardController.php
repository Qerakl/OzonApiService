<?php
namespace App\Http\Controllers;

use App\Models\MarketplaceForecast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Ограничение вывода по умолчанию
        $limit = $request->input('limit', 10);
        $forecasts = MarketplaceForecast::query()
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(100); // или $limit


        return Inertia::render('Dashboard', [
            'forecasts' => $forecasts->items(),
            'pagination' => [
                'current_page' => $forecasts->currentPage(),
                'last_page' => $forecasts->lastPage(),
                'per_page' => $forecasts->perPage(),
                'next_page_url' => $forecasts->nextPageUrl(),
            ],
        ]);

    }
}
