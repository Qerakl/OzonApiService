<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\MarketplaceForecast;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ForecastController extends Controller
{
    // ForecastController.php
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20); // значение по умолчанию
        $forecasts = \App\Models\MarketplaceForecast::where('user_id', Auth::id())
            ->select('article', 'name', 'current_stock', 'forecast')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(['per_page' => $perPage]); // сохраняем параметр в ссылках

        return Inertia::render('Grafics/ForecastsCharts', [
            'forecasts' => $forecasts->items(),
            'pagination' => [
                'current_page' => $forecasts->currentPage(),
                'last_page' => $forecasts->lastPage(),
                'next_page_url' => $forecasts->nextPageUrl(),
                'per_page' => $forecasts->perPage(),
            ],
        ]);
    }

}
