<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\SalesData;
use Carbon\Carbon;

class ChatbotController extends Controller
{
    public function index()
    {
        return view('chatbot');
    }

    public function response(Request $request)
    {
        $userMessage = $request->input('message');

        $store = $this->extractNumber($userMessage, 'store');
        $periodType = $this->extractPeriodType($userMessage);
        $periodNumber = $this->extractPeriodNumber($userMessage);

        $salesMessage = 'Tidak ditemukan data penjualan.';

        if ($store && $periodType && $periodNumber) {
            // Tentukan tanggal awal dataset (disesuaikan dengan data kamu)
            $startDate = Carbon::create(2010, 1, 1);

            switch ($periodType) {
                case 'daily':
                    $dateFrom = $startDate->copy()->addDays($periodNumber - 1)->startOfDay();
                    $dateTo = $dateFrom->copy()->endOfDay();
                    break;
                case 'weekly':
                    $dateFrom = $startDate->copy()->addWeeks($periodNumber - 1)->startOfWeek();
                    $dateTo = $dateFrom->copy()->endOfWeek();
                    break;
                case 'monthly':
                    $dateFrom = $startDate->copy()->addMonths($periodNumber - 1)->startOfMonth();
                    $dateTo = $dateFrom->copy()->endOfMonth();
                    break;
                default:
                    $dateFrom = null;
                    $dateTo = null;
            }

            if ($dateFrom && $dateTo) {
                $totalSales = SalesData::where('store', $store)
                    ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
                    ->sum('daily_sales');

                if ($totalSales > 0) {
                    $salesMessage = "Penjualan store $store untuk periode $periodType ke-$periodNumber (" .
                        $dateFrom->format('Y-m-d') . " sampai " . $dateTo->format('Y-m-d') . ") adalah " .
                        number_format($totalSales, 0, ',', '.') . " unit.";
                } else {
                    $salesMessage = "Data penjualan tidak ditemukan untuk store $store pada periode $periodType ke-$periodNumber.";
                }
            }
        }

        $prompt = "Kamu adalah asisten analisis penjualan. Berdasarkan data berikut: \"$salesMessage\", jawablah pertanyaan berikut dengan bahasa yang jelas dan to the point. Pertanyaan: \"$userMessage\"";

        $reply = $this->askGemini($prompt);

        return response()->json(['reply' => $reply]);
    }

    private function extractNumber($text, $keyword)
    {
        preg_match("/$keyword\s*(\d+)/i", $text, $matches);
        return isset($matches[1]) ? intval($matches[1]) : null;
    }

    private function extractPeriodType($text)
    {
        $text = Str::lower($text);
        if (Str::contains($text, ['minggu', 'mingguan'])) {
            return 'weekly';
        } elseif (Str::contains($text, ['bulan', 'bulanan'])) {
            return 'monthly';
        } elseif (Str::contains($text, ['hari', 'harian'])) {
            return 'daily';
        }
        return null;
    }

    private function extractPeriodNumber($text)
    {
        preg_match('/(?:minggu|bulan|hari)[^\d]*(\d+)/i', $text, $matches);
        return isset($matches[1]) ? intval($matches[1]) : null;
    }

    private function askGemini($prompt)
    {
        $apiKey = env('GEMINI_API_KEY');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=$apiKey";

        try {
            $response = Http::post($url, [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            $data = $response->json();
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Maaf, saya tidak bisa menjawab sekarang.';
        } catch (\Exception $e) {
            return 'Maaf, terjadi kesalahan saat mengambil jawaban.';
        }
    }
}
