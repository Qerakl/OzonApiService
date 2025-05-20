<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceApiKey;
use App\Models\MarketplaceForecast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MarketplaceForecastController extends Controller
{
    public function index()
    {
        // Получаем все маркетплейсы
        $marketplaces = MarketplaceApiKey::select('id', 'name', 'client_id')->get();

        // Получаем прогнозы с привязкой к маркетплейсам
        $forecasts = MarketplaceForecast::with('marketplaceApiKey:id,name')
            ->select('id', 'marketplace_api_key_id', 'article', 'name', 'current_stock', 'forecast', 'recommendations')
            ->get();

        return Inertia::render('MarketplaceForecasts/Index', [
            'marketplaces' => $marketplaces,
            'forecasts' => $forecasts,
        ]);
    }

    protected function sendToForecastApi(string $filePath)
    {
        try {
            if (!Storage::exists($filePath)) {
                Log::error('Файл не найден перед отправкой в API', ['path' => $filePath]);
                return null;
            }

            Log::info('Отправка файла в Forecast API', ['path' => $filePath]);

            $response = Http::attach(
                'file',
                Storage::get($filePath),
                basename($filePath)
            )->post(env('FORECAST_API_URL', 'http://localhost:8000/analyze/file'));

            Log::info('Ответ от Forecast API', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $json = $response->json();

                Log::info('Успешно получен JSON из Forecast API', [
                    'results_count' => count($json['results'] ?? []),
                    'summary' => $json['summary'] ?? []
                ]);

                return $json;
            } else {
                Log::error('Ошибка от Forecast API', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }
        } catch (\Throwable $e) {
            Log::error('Исключение при обращении к Forecast API', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }


    public function calculate(Request $request)
    {
        $request->validate([
            'delivery_date' => 'nullable|date_format:Y-m-d',
            'stock_days' => 'nullable|integer|min:1',
            'data_source' => 'nullable|in:csv,marketplace,ozon',
        ]);

        if ($request->data_source === 'csv' || $request->data_source === 'ozon') {
            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt',
            ]);
        } elseif ($request->data_source === 'marketplace') {
            $request->validate([
                'marketplace_id' => 'required|exists:marketplace_api_keys,id',
            ]);
        }

        $forecasts = [];

        // --- Получение данных из API ---
        $apiResponse = null;
        if ($request->hasFile('csv_file')) {
            $path = $request->file('csv_file')->store('temp');
            $apiResponse = $this->sendToForecastApi($path);
        }

        // --- Обработка ответа и сохранение в БД ---
        if (!empty($apiResponse) && isset($apiResponse['results'])) {
            $forecasts = collect($apiResponse['results'])->map(function ($item, $index) {
                // Сохраняем каждый прогноз в БД
                try {
                    MarketplaceForecast::create([
                        'article' => $item['Артикул'],
                        'name' => $item['Название'] ?? 'N/A',
                        'current_stock' => $item['Текущий остаток'] ?? 0,
                        'forecast' => $item['Прогноз'] ?? 0,
                        'recommendations' => $item['Рекомендации'] ?? 0,
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Ошибка при сохранении прогноза: ' . $e->getMessage());
                }



                return [
                    'id' => $index + 1,
                    'article' => $item['Артикул'] ?? 'N/A',
                    'name' => $item['Название'] ?? 'N/A',
                    'current_stock' => $item['Текущий остаток'] ?? 0,
                    'forecast' => $item['Прогноз'] ?? 0,
                    'recommendations' => $item['Рекомендации'] ?? 0,
                ];
            })->toArray();
        }

        $marketplaces = MarketplaceApiKey::select('id', 'name', 'client_id')->get();

        return Inertia::render('MarketplaceForecasts/Index', [
            'marketplaces' => $marketplaces,
            'forecasts' => $forecasts,
        ]);
    }

}
