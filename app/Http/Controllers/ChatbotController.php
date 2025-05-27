<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\SalesData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function index()
    {
        return view('chatbot');
    }

    public function response(Request $request)
    {
        $userMessage = strtolower($request->input('message', ''));

        $bulanMap = [
            'januari' => '01',
            'februari' => '02',
            'maret' => '03',
            'april' => '04',
            'mei' => '05',
            'juni' => '06',
            'juli' => '07',
            'agustus' => '08',
            'september' => '09',
            'oktober' => '10',
            'november' => '11',
            'desember' => '12',
        ];

        try {
            // 1. Tangani pertanyaan spesifik: "data [tanggal] dept [x] store [y]"
            if (preg_match('/data\s*(\d{4}-\d{2}-\d{2}).*dept\s*(\d+).*store\s*(\d+)/', $userMessage, $matches)) {
                [, $date, $dept, $store] = $matches;

                // Tidak ada penyimpanan session lagi
                // session([
                //     'context_date' => $date,
                //     'context_dept' => $dept,
                //     'context_store' => $store,
                //     'context_type' => 'date_specific',
                // ]);

                $data = SalesData::where('date', $date)
                    ->where('dept', $dept)
                    ->where('store', $store)
                    ->first();

                if ($data) {
                    $summary = "Data penjualan ditemukan:\n" .
                        "Tanggal: " . Carbon::parse($data->date)->format('d M Y') . "\n" .
                        "Store: {$data->store}\n" .
                        "Department: {$data->dept}\n" .
                        "Daily Sales: $" . number_format($data->daily_sales, 2);
                    return response()->json(['response' => nl2br(htmlspecialchars($summary))]);
                } else {
                    return response()->json(['response' => 'Data tidak ditemukan untuk tanggal, departemen, dan toko tersebut.']);
                }
            }

            // 2. Tangani pertanyaan bulanan spesifik + dept + store: "bulan [namaBulan] [tahun] dept [x] store [y]"
            if (preg_match('/bulan\s*(\w+)\s*(\d{4}).*dept\s*(\d+).*store\s*(\d+)/', $userMessage, $matches)) {
                [, $bulanName, $tahun, $dept, $store] = $matches;
                $bulanName = strtolower($bulanName);

                if (isset($bulanMap[$bulanName])) {
                    $bulanAngka = $bulanMap[$bulanName];
                    // Tidak ada penyimpanan session lagi
                    // session([
                    //     'context_month' => $bulanAngka,
                    //     'context_year' => $tahun,
                    //     'context_dept' => $dept,
                    //     'context_store' => $store,
                    //     'context_type' => 'month_specific',
                    // ]);

                    $totalSales = SalesData::whereYear('date', $tahun)
                        ->whereMonth('date', $bulanAngka)
                        ->where('dept', $dept)
                        ->where('store', $store)
                        ->sum('daily_sales');

                    return response()->json([
                        'response' => "Total penjualan pada bulan " . ucfirst($bulanName) . " $tahun untuk Dept $dept dan Store $store adalah $" . number_format($totalSales, 2)
                    ]);
                } else {
                    return response()->json(['response' => "Nama bulan tidak dikenali. Silakan gunakan nama bulan lengkap (e.g., Januari, Februari)."]);
                }
            }

            // 3. Tangani pertanyaan bulanan umum (tanpa dept/store): "bulan [namaBulan] [tahun]"
            if (preg_match('/bulan\s*(\w+)\s*(\d{4})/', $userMessage, $matches)) {
                if (str_contains($userMessage, 'dept') || str_contains($userMessage, 'store')) {
                    // Biarkan jatuh
                } else {
                    [, $bulanName, $tahun] = $matches;
                    $bulanName = strtolower($bulanName);

                    if (isset($bulanMap[$bulanName])) {
                        $bulanAngka = $bulanMap[$bulanName];
                        // Tidak ada penyimpanan session lagi
                        // session([
                        //     'context_month' => $bulanAngka,
                        //     'context_year' => $tahun,
                        //     'context_dept' => null,
                        //     'context_store' => null,
                        //     'context_type' => 'month_general',
                        // ]);

                        $totalSales = SalesData::whereYear('date', $tahun)
                            ->whereMonth('date', $bulanAngka)
                            ->sum('daily_sales');

                        return response()->json([
                            'response' => "Total penjualan pada bulan " . ucfirst($bulanName) . " $tahun (seluruh departemen dan toko) adalah $" . number_format($totalSales, 2)
                        ]);
                    } else {
                        return response()->json(['response' => "Nama bulan tidak dikenali. Silakan gunakan nama bulan lengkap."]);
                    }
                }
            }

            // 4. Tangani pertanyaan tahun + dept + store: "tahun [tahun] dept [x] store [y]"
            if (preg_match('/tahun\s*(\d{4}).*dept\s*(\d+).*store\s*(\d+)/', $userMessage, $matches)) {
                [, $tahun, $dept, $store] = $matches;

                // Tidak ada penyimpanan session lagi
                // session([
                //     'context_year' => $tahun,
                //     'context_dept' => $dept,
                //     'context_store' => $store,
                //     'context_type' => 'year_specific',
                // ]);

                $data = SalesData::whereYear('date', $tahun)
                    ->where('dept', $dept)
                    ->where('store', $store)
                    ->orderBy('date', 'desc')
                    ->limit(10)
                    ->get(['date', 'daily_sales']);

                if ($data->isEmpty()) {
                    return response()->json(['response' => "Data tidak ditemukan untuk tahun $tahun, Dept $dept, Store $store."]);
                }

                $sortedData = $data->sortBy('date');

                $tableHtml = '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: auto; margin-top: 10px;">';
                $tableHtml .= '<thead><tr style="background-color: #f2f2f2;"><th>Tanggal</th><th>Daily Sales</th></tr></thead><tbody>';
                foreach ($sortedData as $row) {
                    $dateFormatted = Carbon::parse($row->date)->format('d M Y');
                    $salesFormatted = "$" . number_format($row->daily_sales, 2);
                    $tableHtml .= "<tr><td>{$dateFormatted}</td><td style='text-align: right;'>{$salesFormatted}</td></tr>";
                }
                $tableHtml .= '</tbody></table>';

                return response()->json([
                    'response' => "Berikut 10 data penjualan terakhir (diurutkan berdasarkan tanggal) di tahun $tahun untuk Dept $dept dan Store $store:<br>" . $tableHtml
                ]);
            }

            // 5. Tampilkan sales data terbaru jika diminta: "sales data" atau "data penjualan"
            if (str_contains($userMessage, 'sales data') || str_contains($userMessage, 'data penjualan')) {
                $isSimpleDataRequest = true;
                $specificPatterns = [
                    '/data\s*(\d{4}-\d{2}-\d{2}).*dept\s*(\d+).*store\s*(\d+)/',
                    '/bulan\s*(\w+)\s*(\d{4}).*dept\s*(\d+).*store\s*(\d+)/',
                    '/bulan\s*(\w+)\s*(\d{4})(?!\s*dept)(?!\s*store)/',
                    '/tahun\s*(\d{4}).*dept\s*(\d+).*store\s*(\d+)/',
                ];
                foreach ($specificPatterns as $pattern) {
                    if (preg_match($pattern, $userMessage)) {
                        $isSimpleDataRequest = false;
                        break;
                    }
                }

                if ($isSimpleDataRequest) {
                    $salesData = SalesData::select('date', 'store', 'dept', 'daily_sales')
                        ->orderBy('date', 'desc')
                        ->limit(5)
                        ->get();

                    if ($salesData->isEmpty()) {
                        return response()->json(['response' => 'Tidak ada data penjualan terbaru untuk ditampilkan.']);
                    }

                    $tableHtml = '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: auto; margin-top: 10px;">';
                    $tableHtml .= '<thead><tr style="background-color: #f2f2f2;"><th>Tanggal</th><th>Store</th><th>Dept</th><th>Daily Sales</th></tr></thead><tbody>';
                    foreach ($salesData as $row) {
                        $date = Carbon::parse($row->date)->format('d M Y');
                        $tableHtml .= "<tr>
                            <td>{$date}</td>
                            <td>{$row->store}</td>
                            <td>{$row->dept}</td>
                            <td style='text-align: right;'>$" . number_format($row->daily_sales, 2) . "</td>
                        </tr>";
                    }
                    $tableHtml .= '</tbody></table>';

                    return response()->json([
                        'response' => 'Berikut 5 data penjualan terbaru:<br>' . $tableHtml
                    ]);
                }
            }

            // 6. Respon sapaan
            if (str_contains($userMessage, 'halo') || str_contains($userMessage, 'hai') || str_contains($userMessage, 'hi')) {
                return response()->json(['response' => 'Halo! Ada yang bisa saya bantu terkait analisis penjualan?']);
            }

            // 6.1. Contoh penggunaan extractNumber: "status toko [nomor]"
            if (str_contains($userMessage, 'status toko')) {
                $storeNumber = $this->extractNumber($userMessage, 'toko');
                if ($storeNumber !== null) {
                    $latestSale = SalesData::where('store', $storeNumber)
                        ->orderBy('date', 'desc')
                        ->first();
                    if ($latestSale) {
                        return response()->json([
                            'response' => "Info: Data penjualan terakhir untuk toko $storeNumber pada tanggal " .
                                Carbon::parse($latestSale->date)->format('d M Y') .
                                " adalah $" . number_format($latestSale->daily_sales, 2)
                        ]);
                    } else {
                        return response()->json(['response' => "Info: Tidak ada data penjualan ditemukan untuk toko $storeNumber."]);
                    }
                }
            }

            // 6.2. Contoh penggunaan extractPeriodType dan extractPeriodNumber: "cek periode [minggu/bulan/hari] ke [nomor]"
            $periodKeywordFound = false;
            if (
                str_contains($userMessage, 'cek periode minggu ke') ||
                str_contains($userMessage, 'cek periode bulan ke') ||
                str_contains($userMessage, 'cek periode hari ke')
            ) {
                $periodKeywordFound = true;
            }

            if ($periodKeywordFound) {
                $periodType = $this->extractPeriodType($userMessage);
                $periodNumber = $this->extractPeriodNumber($userMessage);

                if ($periodType && $periodNumber) {
                    $responseText = "Info: Pengecekan diminta untuk periode Tipe='{$periodType}', Nomor='{$periodNumber}'. ";
                    if ($periodType === 'monthly') {
                        $responseText .= "Ini bisa merujuk pada bulan ke-{$periodNumber} dalam suatu tahun. ";
                    }
                    $responseText .= "Fitur detail untuk ini sedang dalam pengembangan.";
                    return response()->json(['response' => $responseText]);
                }
            }

            // 7. Tangani pertanyaan prediksi / forecast (menggunakan Gemini)
            $forecastKeywords = ['prediksi', 'forecast', 'ramalan', 'peramalan', 'proyeksi', 'tren'];
            $foundForecastKeyword = false;
            foreach ($forecastKeywords as $kw) {
                if (str_contains($userMessage, $kw)) {
                    $foundForecastKeyword = true;
                    break;
                }
            }

            if ($foundForecastKeyword) {
                // Konteks dari session dihilangkan.
                // $contextInfo akan kosong kecuali kita parsing dari $userMessage saat ini.
                // Untuk saat ini, kita buat sederhana dan tidak mencoba parsing konteks dari pesan saat ini untuk prediksi.
                $contextInfo = "";

                // Contoh jika ingin mencoba parsing konteks dari pesan saat ini (bisa kompleks):
                // if (preg_match('/tahun\s*(\d{4}).*dept\s*(\d+).*store\s*(\d+)/', $userMessage, $matchesCtx)) {
                //     $contextInfo = "Berdasarkan data penjualan tahun {$matchesCtx[1]} untuk Dept {$matchesCtx[2]} dan Store {$matchesCtx[3]}, ";
                // } elseif (preg_match('/bulan\s*(\w+)\s*(\d{4})/', $userMessage, $matchesCtx)) {
                //     // ... logika parsing bulan dan tahun ...
                // }

                $prompt = $contextInfo . "Tolong berikan " . $userMessage;
                $geminiResponse = $this->askGemini($prompt);
                return response()->json(['response' => $this->formatGeminiResponse($geminiResponse)]);
            }

            // 8. Jika tidak ada pola spesifik di atas, cek kata kunci umum untuk Gemini
            $allowedGeneralKeywords = [
                'analisis penjualan',
                'analisis tren',
                'pertumbuhan penjualan',
                'penurunan penjualan',
                'evaluasi forecast',
                'akurasi forecast',
                'perbandingan forecast',
                'faktor penjualan',
                'musiman',
                'musim',
                'libur',
                'cuaca',
                'model forecasting',
                'metode forecasting',
                'algoritma forecasting',
                'linear regression',
                'arima',
                'lstm',
                'machine learning penjualan',
                'apa itu forecasting',
                'apa itu sales forecasting',
                'forecasting adalah',
                'sales forecasting adalah',
                'pengertian forecasting',
                'definisi forecasting',
                'tujuan forecasting',
                'manfaat forecasting',
                'penjelasan forecasting',
                'walmart',
                'penjualan walmart',
                'data walmart',
                'forecast walmart',
                'strategi walmart',
                'performa walmart',
                'walmart sales',
                'toko walmart',
            ];

            $isGeneralSalesQuery = false;
            foreach ($allowedGeneralKeywords as $keyword) {
                if (str_contains($userMessage, $keyword)) {
                    $isGeneralSalesQuery = true;
                    break;
                }
            }

            if ($isGeneralSalesQuery) {
                $prompt = "Sebagai AI asisten analisis penjualan, jawab pertanyaan berikut: " . $userMessage;
                $geminiResponse = $this->askGemini($prompt);
                return response()->json(['response' => $this->formatGeminiResponse($geminiResponse)]);
            }

            // 9. Jika tidak ada yang cocok, default response
            return response()->json([
                'response' => 'Maaf, saya tidak mengerti pertanyaan Anda. Anda bisa bertanya tentang data penjualan spesifik (berdasarkan tanggal, bulan, tahun, departemen, atau toko), meminta ringkasan data penjualan terbaru, atau menanyakan prediksi penjualan.'
            ]);
        } catch (\Exception $e) {
            Log::error('ChatbotController Error: ' . $e->getMessage() . ' StackTrace: ' . $e->getTraceAsString());
            return response()->json([
                'response' => 'Maaf, terjadi kesalahan internal di server saat memproses permintaan Anda. Tim kami telah diberitahu.'
            ], 500); // Mengirim status HTTP 500 untuk error server
        }
    }

    /**
     * Helper function untuk mengekstrak angka setelah keyword tertentu.
     */
    private function extractNumber($text, $keyword)
    {
        if (preg_match("/" . preg_quote($keyword, '/') . "\s*(\d+)/i", $text, $matches)) {
            return intval($matches[1]);
        }
        return null;
    }

    /**
     * Helper function untuk mengekstrak tipe periode (daily, weekly, monthly).
     */
    private function extractPeriodType($text)
    {
        $textLower = Str::lower($text);
        if (Str::contains($textLower, ['minggu', 'mingguan'])) {
            return 'weekly';
        } elseif (Str::contains($textLower, ['bulan', 'bulanan'])) {
            return 'monthly';
        } elseif (Str::contains($textLower, ['hari', 'harian'])) {
            return 'daily';
        }
        return null;
    }

    /**
     * Helper function untuk mengekstrak nomor periode.
     */
    private function extractPeriodNumber($text)
    {
        if (preg_match('/(?:minggu|bulan|hari)(?:\s+ke)?\s*(\d+)/i', $text, $matches)) {
            return intval($matches[1]);
        }
        return null;
    }

    /**
     * Memformat teks respons dari Gemini ke HTML.
     */
    private function formatGeminiResponse($text)
    {
        $text = trim(htmlspecialchars($text));

        $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);
        $text = preg_replace('/(?<!\*)\*(?!\s|\*)(.*?)(?<!\s|\*)\*(?!\*)/s', '<em>$1</em>', $text);
        $text = preg_replace('/(?<![a-zA-Z0-9])_(.*?)_(?![a-zA-Z0-9])/s', '<em>$1</em>', $text);

        $lines = explode("\n", $text);
        $formattedLines = [];
        $inList = false;
        $listType = null;

        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            if (preg_match('/^(\*|-)\s+(.*)/', $trimmedLine, $matches)) {
                if (!$inList || $listType !== 'ul') {
                    if ($inList) $formattedLines[] = '</' . $listType . '>';
                    $formattedLines[] = '<ul>';
                    $inList = true;
                    $listType = 'ul';
                }
                $formattedLines[] = '<li>' . trim($matches[2]) . '</li>';
            } elseif (preg_match('/^(\d+\.)\s+(.*)/', $trimmedLine, $matches)) {
                if (!$inList || $listType !== 'ol') {
                    if ($inList) $formattedLines[] = '</' . $listType . '>';
                    $formattedLines[] = '<ol>';
                    $inList = true;
                    $listType = 'ol';
                }
                $formattedLines[] = '<li>' . trim($matches[2]) . '</li>';
            } else {
                if ($inList) {
                    $formattedLines[] = '</' . $listType . '>';
                    $inList = false;
                    $listType = null;
                }
                $formattedLines[] = $line;
            }
        }
        if ($inList) {
            $formattedLines[] = '</' . $listType . '>';
        }

        $text = implode("\n", $formattedLines);
        return nl2br($text, false);
    }


    /**
     * Mengirim prompt ke Gemini API dan mengembalikan respons teks.
     */
    private function askGemini($prompt)
    {
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            Log::error('GEMINI_API_KEY tidak ditemukan di .env');
            return 'Maaf, konfigurasi API untuk AI sedang bermasalah.';
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(30)
                ->post($url, [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [['text' => $prompt]]
                        ]
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    return $data['candidates'][0]['content']['parts'][0]['text'];
                } else {
                    Log::warning('Struktur respons Gemini tidak sesuai harapan: ' . $response->body());
                    return 'Maaf, saya mendapat respons yang tidak terduga dari AI.';
                }
            } else {
                Log::error('Error saat menghubungi Gemini: ' . $response->status() . ' - ' . $response->body());
                return 'Maaf, terjadi kesalahan saat menghubungi layanan AI. Status: ' . $response->status() . ". Detail: " . $response->body();
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Koneksi ke Gemini gagal: ' . $e->getMessage());
            return 'Maaf, saya tidak dapat terhubung ke layanan AI saat ini. Periksa koneksi internet Anda atau coba lagi nanti.';
        } catch (\Exception $e) {
            Log::error('Exception umum saat memproses permintaan Gemini: ' . $e->getMessage());
            return 'Maaf, terjadi kesalahan umum saat memproses permintaan Anda ke AI.';
        }
    }
}
