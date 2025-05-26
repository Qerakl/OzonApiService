<?php
namespace App\Http\Controllers;

use App\Models\MarketplaceForecast;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Ограничение вывода по умолчанию
        $limit = $request->input('limit', 10);
        $forecasts = MarketplaceForecast::query()
            ->orderBy('forecast', 'desc')
            ->limit($limit)
            ->get();

        return Inertia::render('Dashboard', [
            'forecasts' => $forecasts,
            'limit' => $limit,
            'hasMore' => MarketplaceForecast::count() > $limit,
        ]);
    }
}
