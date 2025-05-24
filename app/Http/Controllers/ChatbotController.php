<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesData;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ChatbotController extends Controller
{
    public function index()
    {
        return view('chatbot');
    }

    public function response(Request $request)
    {
        $userMessage = strtolower($request->input('message'));

        // Mapping nama bulan Indonesia ke angka
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

        // 1. Tangani pertanyaan spesifik: "data [tanggal] dept [x] store [y]"
        if (preg_match('/(\d{4}-\d{2}-\d{2}).*dept\s*(\d+).*store\s*(\d+)/', $userMessage, $matches)) {
            [, $date, $dept, $store] = $matches;

            // Simpan konteks session
            session([
                'context_date' => $date,
                'context_dept' => $dept,
                'context_store' => $store,
                'context_type' => 'date', // jenis konteks, nanti bisa pakai ini untuk logika
            ]);

            $data = SalesData::where('date', $date)
                ->where('dept', $dept)
                ->where('store', $store)
                ->first();

            if ($data) {
                $summary = "Data penjualan ditemukan:\n" .
                    "Tanggal: {$data->date}\n" .
                    "Store: {$data->store}\n" .
                    "Department: {$data->dept}\n" .
                    "Daily Sales: $" . number_format($data->daily_sales, 2);

                return response()->json(['response' => nl2br($summary)]);
            } else {
                return response()->json(['response' => 'Data tidak ditemukan untuk kombinasi tersebut.']);
            }
        }

        // 2. Tangani pertanyaan bulanan spesifik + dept + store
        if (preg_match('/bulan (\w+) (\d{4}) dept (\d+) store (\d+)/', $userMessage, $matches)) {
            [, $bulan, $tahun, $dept, $store] = $matches;

            if (isset($bulanMap[$bulan])) {
                $bulanAngka = $bulanMap[$bulan];

                // Simpan konteks session
                session([
                    'context_month' => $bulanAngka,
                    'context_year' => $tahun,
                    'context_dept' => $dept,
                    'context_store' => $store,
                    'context_type' => 'month',
                ]);

                $totalSales = SalesData::whereYear('date', $tahun)
                    ->whereMonth('date', $bulanAngka)
                    ->where('dept', $dept)
                    ->where('store', $store)
                    ->sum('daily_sales');

                return response()->json([
                    'response' => "Total penjualan pada bulan " . ucfirst($bulan) . " $tahun untuk Dept $dept dan Store $store adalah $" . number_format($totalSales, 2)
                ]);
            } else {
                return response()->json([
                    'response' => "Nama bulan tidak dikenali. Silakan gunakan nama bulan seperti Januari, Februari, dll."
                ]);
            }
        }

        // 3. Tangani pertanyaan bulanan umum (tanpa dept/store)
        if (preg_match('/bulan (\w+) (\d{4})/', $userMessage, $matches)) {
            [, $bulan, $tahun] = $matches;

            if (isset($bulanMap[$bulan])) {
                $bulanAngka = $bulanMap[$bulan];

                // Simpan konteks session tapi tanpa dept/store
                session([
                    'context_month' => $bulanAngka,
                    'context_year' => $tahun,
                    'context_dept' => null,
                    'context_store' => null,
                    'context_type' => 'month',
                ]);

                $totalSales = SalesData::whereYear('date', $tahun)
                    ->whereMonth('date', $bulanAngka)
                    ->sum('daily_sales');

                return response()->json([
                    'response' => "Total penjualan pada bulan " . ucfirst($bulan) . " $tahun adalah $" . number_format($totalSales, 2)
                ]);
            } else {
                return response()->json([
                    'response' => "Nama bulan tidak dikenali. Silakan gunakan nama bulan seperti Januari, Februari, dll."
                ]);
            }
        }

        // 4. Tampilkan sales data terbaru
        if (str_contains($userMessage, 'sales data')) {
            $salesData = SalesData::select('date', 'store', 'dept', 'daily_sales')
                ->orderBy('date', 'desc')
                ->limit(5)
                ->get();

            $tableHtml = '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse;">';
            $tableHtml .= '<thead><tr><th>Date</th><th>Store</th><th>Dept</th><th>Daily Sales</th></tr></thead><tbody>';

            foreach ($salesData as $row) {
                $date = \Carbon\Carbon::parse($row->date)->format('Y-m-d');
                $tableHtml .= "<tr>
                <td>{$date}</td>
                <td>{$row->store}</td>
                <td>{$row->dept}</td>
                <td>$" . number_format($row->daily_sales, 2) . "</td>
            </tr>";
            }

            $tableHtml .= '</tbody></table>';

            return response()->json([
                'response' => 'Berikut data penjualan terbaru:<br>' . $tableHtml
            ]);
        }

        // 5. Tangani pertanyaan tahun + dept + store
        if (preg_match('/tahun\s*(\d{4}).*dept\s*(\d+).*store\s*(\d+)/', $userMessage, $matches)) {
            [, $tahun, $dept, $store] = $matches;

            // Simpan konteks session
            session([
                'context_year' => $tahun,
                'context_dept' => $dept,
                'context_store' => $store,
                'context_type' => 'year',
            ]);

            // Mengambil 10 data terakhir di tahun tersebut
            $data = SalesData::whereYear('date', $tahun)
                ->where('dept', $dept)
                ->where('store', $store)
                ->orderBy('date', 'desc')
                ->limit(10)
                ->get(['date', 'daily_sales']);
            $sortedData = $data->sortBy('date');

            if ($sortedData->isEmpty()) {
                return response()->json([
                    'response' => "Data tidak ditemukan untuk tahun $tahun, Dept $dept, Store $store."
                ]);
            }

            $tableHtml = '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse;">';
            $tableHtml .= '<thead><tr><th>Date</th><th>Daily Sales</th></tr></thead><tbody>';

            foreach ($sortedData as $row) { // Iterasi menggunakan $sortedData
                $dateFormatted = Carbon::parse($row->date)->format('Y-m-d'); // Menggunakan Carbon
                $salesFormatted = "$" . number_format($row->sales_sales, 2);
                $tableHtml .= "<tr><td>{$dateFormatted}</td><td>{$salesFormatted}</td></tr>";
            }

            $tableHtml .= '</tbody></table>';

            return response()->json([
                'response' => "Berikut 10 data penjualan terakhir di tahun $tahun untuk Dept $dept dan Store $store:<br>" . $tableHtml
            ]);
        }

        // 6. Respon sapaan
        if (str_contains($userMessage, 'halo') || str_contains($userMessage, 'hai')) {
            return response()->json([
                'response' => 'Halo juga! Ada yang bisa saya bantu?'
            ]);
        }

        // 7. Tangani pertanyaan prediksi / forecast
        $forecastKeywords = ['prediksi', 'forecast', 'ramalan', 'peramalan', 'proyeksi', 'tren'];
        $foundForecastKeyword = false;
        foreach ($forecastKeywords as $kw) {
            if (str_contains($userMessage, $kw)) {
                $foundForecastKeyword = true;
                break;
            }
        }

        if ($foundForecastKeyword) {
            // Ambil konteks session jika ada
            $contextType = session('context_type');
            $contextYear = session('context_year');
            $contextMonth = session('context_month');
            $contextDate = session('context_date');
            $contextDept = session('context_dept');
            $contextStore = session('context_store');

            // Buat tambahan konteks di prompt Gemini
            $contextInfo = "";
            if ($contextType === 'date' && $contextDate && $contextDept && $contextStore) {
                $contextInfo = "Berdasarkan data penjualan tanggal $contextDate untuk Dept $contextDept dan Store $contextStore, ";
            } elseif ($contextType === 'month' && $contextYear && $contextMonth) {
                $bulanNama = array_search($contextMonth, $bulanMap);
                $contextDeptStore = ($contextDept && $contextStore) ? "Dept $contextDept dan Store $contextStore" : "seluruh store dan departemen";
                $contextInfo = "Berdasarkan data penjualan bulan $bulanNama $contextYear untuk $contextDeptStore, ";
            } elseif ($contextType === 'year' && $contextYear && $contextDept && $contextStore) {
                $contextInfo = "Berdasarkan data penjualan tahun $contextYear untuk Dept $contextDept dan Store $contextStore, ";
            }

            $prompt = $contextInfo . $userMessage;

            // Panggil Gemini dengan prompt lengkap ini
            $geminiResponse = $this->queryGeminiFlash($prompt);

            // Format respons Gemini sesuai kebutuhan (misal hapus newline, tambah HTML, dll)
            $formattedGeminiResponse = $this->formatGeminiResponse($geminiResponse);

            return response()->json(['response' => $formattedGeminiResponse]);
        }

        // 8. Jika tidak ada yang cocok, default response
        return response()->json([
            'response' => 'Maaf, saya tidak mengerti pertanyaan Anda. Bisa coba tanyakan data penjualan atau prediksi.'
        ]);


        // Kirim ke Gemini Flash untuk pertanyaan umum atau prediksi
        // Cek apakah user message mengandung kata-kata yang relevan
        $allowedKeywords = [
            'prediksi',
            'forecast',
            'ramalan',
            'peramalan',
            'proyeksi',
            'tren',
            'trend',
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

        $allowed = false;
        foreach ($allowedKeywords as $keyword) {
            if (preg_match("/\b" . preg_quote($keyword, '/') . "\b/i", $userMessage)) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            return response()->json([
                'response' => 'Maaf, saya hanya bisa menjawab pertanyaan berkaitan dengan sales forecasting saja.'
            ]);
        }

        $prompt = $userMessage;
        if ($tahun && $dept && $store) {
            $prompt = "Berdasarkan data penjualan tahun $tahun untuk Dept $dept dan Store $store, " . $userMessage;
        }

        $geminiResponse = $this->queryGeminiFlash($userMessage);
        $formattedGeminiResponse = $this->formatGeminiResponse($geminiResponse);
        return response()->json(['response' => $formattedGeminiResponse]);
    }

    private function formatGeminiResponse($text)
    {
        $text = trim($text);

        // Format paragraf dan baris baru
        $paragraphs = preg_split("/\n\s*\n/", $text); // Deteksi paragraf berdasarkan 2 baris baru
        $formatted = '';

        foreach ($paragraphs as $para) {
            $lines = explode("\n", $para);
            foreach ($lines as &$line) {
                // Bullet manual yang diawali *, - atau angka
                if (preg_match('/^(\*|-|\d+\.)\s+/', $line)) {
                    $line = '<li>' . htmlspecialchars(trim($line)) . '</li>';
                } else {
                    $line = htmlspecialchars($line);
                }
            }

            $joined = implode('<br>', $lines);

            // Jika ada list item di paragraf, bungkus dengan <ul>
            if (str_contains($joined, '<li>')) {
                $joined = '<ul>' . $joined . '</ul>';
            } else {
                $joined = "<p>{$joined}</p>";
            }

            $formatted .= $joined;
        }

        return $formatted;
    }


    private function queryGeminiFlash($prompt)
    {
        $apiKey = env('GEMINI_API_KEY');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ]);

        if ($response->successful()) {
            return $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? 'Tidak ada jawaban dari Gemini.';
        } else {
            return 'Terjadi kesalahan saat menghubungi Gemini Flash.';
        }
    }
}
