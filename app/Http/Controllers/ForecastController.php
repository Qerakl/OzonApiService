<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\MarketplaceForecast;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ForecastController extends Controller
{
    public function index()
    {
        $forecasts = MarketplaceForecast::where('user_id', Auth::id())
            ->select('article', 'name', 'current_stock', 'forecast')
            ->get();

        return Inertia::render('Grafics/ForecastsCharts', [
            'forecasts' => $forecasts,
        ]);
    }

}
