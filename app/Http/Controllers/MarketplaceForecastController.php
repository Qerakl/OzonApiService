<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceApiKey;
use App\Services\OzonReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Marketplace;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MarketplaceForecastController extends Controller
{
    public function index()
    {
        // Получаем все маркетплейсы
        $marketplaces = MarketplaceApiKey::select('id', 'name', 'client_id')->get();

        // Заглушка для прогнозов (пустой массив)
        $forecasts = [];

        return Inertia::render('MarketplaceForecasts/Index', [
            'marketplaces' => $marketplaces,
            'forecasts' => $forecasts,
        ]);
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'delivery_date' => 'required|date',
            'stock_days' => 'required|integer|min:1',
            'data_source' => 'required|in:csv,marketplace,ozon',
        ]);

        // Валидация в зависимости от источника данных
        if ($request->data_source === 'csv' || $request->data_source === 'ozon') {
            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt',
            ]);
        } elseif ($request->data_source === 'marketplace') {
            $request->validate([
                'marketplace_id' => 'required|exists:marketplaces,id',
            ]);
        }

        // Логика обработки CSV файла
        if ($request->hasFile('csv_file')) {
            $file = $request->file('csv_file');
            $path = $file->store('temp');

            // Здесь будет ваша логика обработки файла и расчета прогноза
            // ...

            // После обработки можно удалить временный файл
            Storage::delete($path);
        }

        // Логика получения данных с маркетплейса
        if ($request->data_source === 'marketplace') {
            $marketplace = MarketplaceApiKey::find($request->marketplace_id);
            // Здесь логика получения данных с выбранного маркетплейса
            // ...
        }

        // Здесь логика расчета прогноза
        // ...

        // Заглушка для прогнозов (тестовые данные)
        $forecasts = [
            [
                'id' => 1,
                'article' => 'ART-001',
                'name' => 'Тестовый товар 1',
                'current_stock' => 100,
                'forecast' => 150,
                'recommendations' => 'Увеличить запас на 50 единиц',
            ],
            [
                'id' => 2,
                'article' => 'ART-002',
                'name' => 'Тестовый товар 2',
                'current_stock' => 50,
                'forecast' => 30,
                'recommendations' => 'Уменьшить запас на 20 единиц',
            ],
        ];

        // Получаем все маркетплейсы для формы
        $marketplaces = MarketplaceApiKey::select('id', 'name', 'client_id')->get();

        return Inertia::render('MarketplaceForecasts/Index', [
            'marketplaces' => $marketplaces,
            'forecasts' => $forecasts,
        ]);
    }
}
