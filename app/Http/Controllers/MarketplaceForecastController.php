<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceApiKey;
use App\Models\MarketplaceForecast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
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
            )->post(env('FORECAST_API_URL', 'http://bot:8000/analyze/file'));

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
    public function forecastByPeriod(Request $request)
    {
        $validated = $request->validate([
            'marketplace_id' => 'required|exists:marketplace_api_keys,id',
            'delivery_date_from' => 'required|date|before_or_equal:delivery_date_to',
            'delivery_date_to' => 'required|date|after_or_equal:delivery_date_from',
        ]);
        $ozonKey = MarketplaceApiKey::find($request->marketplace_id);

        return response()->json([
            'success' => true,
            'data' => [
                'delivery_date_from' => $request->delivery_date_from,
                'delivery_date_to' => $request->delivery_date_to,
                'ClientId' => $ozonKey->client_id,
                'ApiKey' => $ozonKey->api_key,
            ],
        ]);
    }


    public function calculate(Request $request)
    {
        $request->validate([
            'delivery_date' => 'nullable|date_format:Y-m-d',
            'stock_days' => 'nullable|integer|min:1',
            'data_source' => 'required|in:csv,marketplace,ozon',
        ]);

        if ($request->data_source === 'csv') {
            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt',
            ]);
        }

        if (in_array($request->data_source, ['marketplace', 'ozon'])) {
            $request->validate([
                'marketplace_id' => 'required|exists:marketplace_api_keys,id',
                'delivery_date_from' => 'required|date',
                'delivery_date_to' => 'required|date|after_or_equal:delivery_date_from',
            ]);
        }

        $forecasts = [];
        $apiResponse = null;

        if ($request->data_source === 'csv' && $request->hasFile('csv_file')) {
            $path = $request->file('csv_file')->store('temp');
            $apiResponse = $this->sendToForecastApi($path);
        }

        if (in_array($request->data_source, ['marketplace', 'ozon'])) {
            // Здесь можно переключать поведение под разные маркетплейсы (сейчас только ozon)
            $ozonKey = MarketplaceApiKey::find($request->marketplace_id);
            $headers = [
                'Client-Id' => $ozonKey->client_id,
                'Api-Key' => $ozonKey->api_key,
                'Content-Type' => 'application/json',
            ];

            $reportRequestBody = [
                'filter' => [
                    'processed_at_from' => now()->subDays(30)->toIso8601String(),
                    'processed_at_to' => now()->toIso8601String(),
                    'delivery_schema' => ['fbo'],
                ],
                'language' => 'DEFAULT',
            ];

            $reportCreateResponse = Http::withHeaders($headers)
                ->post('https://api-seller.ozon.ru/v1/report/postings/create', $reportRequestBody);

            if (!$reportCreateResponse->successful()) {
                return response()->json(['error' => 'Ошибка создания отчета Ozon'], 500);
            }

            $reportCode = $reportCreateResponse->json('result.code');

            sleep(2); // дать отчёту немного времени
            $reportInfoResponse = Http::withHeaders($headers)
                ->post('https://api-seller.ozon.ru/v1/report/info', ['code' => $reportCode]);

            if (!$reportInfoResponse->successful()) {
                return response()->json(['error' => 'Ошибка получения отчета Ozon'], 500);
            }

            $reportStatus = $reportInfoResponse->json('result.status');
            $fileUrl = $reportInfoResponse->json('result.file');

            if ($reportStatus !== 'success' || empty($fileUrl)) {
                return response()->json(['error' => 'Отчет ещё не готов'], 400);
            }

            $csvContents = file_get_contents($fileUrl);
            if ($csvContents === false) {
                return response()->json(['error' => 'Не удалось скачать CSV'], 500);
            }

            $filename = 'ozon_reports/' . Str::uuid() . '.csv';
            Storage::put($filename, $csvContents);

            $apiResponse = $this->sendToForecastApi($filename);
        }

        // Обработка ответа
        if (!empty($apiResponse) && isset($apiResponse['results'])) {
            $forecasts = collect($apiResponse['results'])->map(function ($item, $index) {
                return [
                    'id' => $index + 1,
                    'article' => $item['Артикул'] ?? 'N/A',
                    'name' => $item['Название'] ?? 'N/A',
                    'current_stock' => $item['Текущий остаток'] ?? 0,
                    'forecast' => $item['Прогноз'] ?? 0,
                    'recommendations' => $item['Рекомендации'] ?? '',
                ];
            })->toArray();
        }

        return response()->json([
            'forecasts' => $forecasts,
        ]);
    }

}
