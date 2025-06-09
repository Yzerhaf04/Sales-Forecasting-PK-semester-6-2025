<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\SalesData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ChatbotController extends Controller
{
    private $bulanMap = [
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

    public function index()
    {
        return view('chatbot');
    }

    public function response(Request $request)
    {
        $userMessage = $request->input('message', '');
        $lowerUserMessage = strtolower($userMessage);

        try {

            if (preg_match('/data\s*(\d{4}-\d{2}-\d{2}).*dept\s*(\d+).*store\s*(\d+)/i', $lowerUserMessage, $matches)) {
                [, $date, $dept, $store] = $matches;
                $data = SalesData::where('date', $date)
                    ->where('dept', $dept)
                    ->where('store', $store)
                    ->first();

                if ($data) {

                    $summary = "### Data Penjualan Ditemukan\n\n" .
                        "- **Tanggal**: " . Carbon::parse($data->date)->format('d M Y') . "\n" .
                        "- **Store**: {$data->store}\n" .
                        "- **Department**: {$data->dept}\n" .
                        "- **Daily Sales**: $" . number_format($data->daily_sales, 2);
                    return response()->json(['response' => $this->formatGeminiResponse($summary)]);
                } else {
                    return response()->json(['response' => $this->formatGeminiResponse('Data tidak ditemukan untuk tanggal, departemen, dan toko tersebut.')]);
                }
            }


            if (preg_match('/bulan\s*(\w+)\s*(\d{4}).*dept\s*(\d+).*store\s*(\d+)/i', $lowerUserMessage, $matches)) {
                [, $bulanNameInput, $tahun, $dept, $store] = $matches;
                $bulanNameLower = strtolower($bulanNameInput);

                if (isset($this->bulanMap[$bulanNameLower])) {
                    $bulanAngka = $this->bulanMap[$bulanNameLower];
                    $totalSales = SalesData::whereYear('date', $tahun)
                        ->whereMonth('date', $bulanAngka)
                        ->where('dept', $dept)
                        ->where('store', $store)
                        ->sum('daily_sales');
                    $responseText = "Total penjualan pada bulan " . ucfirst($bulanNameLower) . " $tahun untuk Dept $dept dan Store $store adalah **$" . number_format($totalSales, 2) . "**";
                    return response()->json(['response' => $this->formatGeminiResponse($responseText)]);
                } else {
                    return response()->json(['response' => $this->formatGeminiResponse("Nama bulan tidak dikenali. Silakan gunakan nama bulan lengkap (e.g., Januari, Februari).")]);
                }
            }


            if (preg_match('/bulan\s*(\w+)\s*(\d{4})/i', $lowerUserMessage, $matches)) {
                if (!str_contains($lowerUserMessage, 'dept') && !str_contains($lowerUserMessage, 'store')) {
                    [, $bulanNameInput, $tahun] = $matches;
                    $bulanNameLower = strtolower($bulanNameInput);

                    if (isset($this->bulanMap[$bulanNameLower])) {
                        $bulanAngka = $this->bulanMap[$bulanNameLower];
                        $totalSales = SalesData::whereYear('date', $tahun)
                            ->whereMonth('date', $bulanAngka)
                            ->sum('daily_sales');
                        $responseText = "Total penjualan pada bulan " . ucfirst($bulanNameLower) . " $tahun (seluruh departemen dan toko) adalah **$" . number_format($totalSales, 2) . "**";
                        return response()->json(['response' => $this->formatGeminiResponse($responseText)]);
                    } else {
                        return response()->json(['response' => $this->formatGeminiResponse("Nama bulan tidak dikenali. Silakan gunakan nama bulan lengkap.")]);
                    }
                }
            }


            if (preg_match('/tahun\s*(\d{4}).*dept\s*(\d+).*store\s*(\d+)/i', $lowerUserMessage, $matches)) {
                [, $tahun, $dept, $store] = $matches;
                $baseQuery = SalesData::whereYear('date', $tahun)
                    ->where('dept', $dept)
                    ->where('store', $store);
                $totalCount = (clone $baseQuery)->count();
                if ($totalCount == 0) {
                    return response()->json(['response' => $this->formatGeminiResponse("Data tidak ditemukan untuk tahun $tahun, Dept $dept, Store $store.")]);
                }
                $totalSumSales = (clone $baseQuery)->sum('daily_sales');
                $latestSampleData = (clone $baseQuery)
                    ->orderBy('date', 'desc')
                    ->limit(10)
                    ->get(['date', 'daily_sales']);
                $sampleData = $latestSampleData->sortBy('date');

                $responseText = "Ditemukan total **" . number_format($totalCount) . "** data untuk tahun **$tahun**, Dept **$dept**, Store **$store**.\n";
                $responseText .= "Total keseluruhan penjualan untuk kriteria tersebut adalah **$" . number_format($totalSumSales, 2) . "**.\n\n";

                $tableMarkdown = "";
                if (!$sampleData->isEmpty()) {
                    $responseText .= "Berikut adalah sampel hingga 10 data penjualan terbaru untuk kriteria yang dipilih, ditampilkan dengan data terlama dari sampel ini di bagian atas:\n";
                    $tableMarkdown .= "| Tanggal       | Daily Sales   |\n";
                    $tableMarkdown .= "|:--------------|:--------------|\n";
                    foreach ($sampleData as $row) {
                        $dateFormatted = Carbon::parse($row->date)->format('d M Y');
                        $salesFormatted = "$" . number_format($row->daily_sales, 2);
                        $tableMarkdown .= "| " . $dateFormatted . " | " . $salesFormatted . " |\n";
                    }
                    $responseText .= $tableMarkdown;
                } else if ($totalCount > 0 && $sampleData->isEmpty()) {
                    $responseText .= "Tidak ada sampel data rinci yang dapat ditampilkan untuk kriteria ini (meskipun $totalCount total data ditemukan).";
                }
                return response()->json(['response' => $this->formatGeminiResponse($responseText)]);
            }


            if (str_contains($lowerUserMessage, 'sales data') || str_contains($lowerUserMessage, 'data penjualan')) {
                $isSimpleDataRequest = true;
                $specificPatterns = [
                    '/data\s*(\d{4}-\d{2}-\d{2}).*dept\s*(\d+).*store\s*(\d+)/i',
                    '/bulan\s*(\w+)\s*(\d{4}).*dept\s*(\d+).*store\s*(\d+)/i',
                    '/bulan\s*(\w+)\s*(\d{4})(?!\s*dept)(?!\s*store)/i',
                    '/tahun\s*(\d{4}).*dept\s*(\d+).*store\s*(\d+)/i',
                ];
                foreach ($specificPatterns as $pattern) {
                    if (preg_match($pattern, $lowerUserMessage)) {
                        $isSimpleDataRequest = false;
                        break;
                    }
                }

                if ($isSimpleDataRequest) {
                    $latestSalesData = SalesData::select('date', 'store', 'dept', 'daily_sales')
                        ->orderBy('date', 'desc')
                        ->limit(5)
                        ->get();
                    $salesData = $latestSalesData->sortBy('date');
                    if ($salesData->isEmpty()) {
                        return response()->json(['response' => $this->formatGeminiResponse('Tidak ada data penjualan terbaru untuk ditampilkan.')]);
                    }

                    $responseText = "Berikut hingga 5 data penjualan terbaru dari sistem, ditampilkan dengan data terlama dari sampel ini di bagian atas:\n";
                    $tableMarkdown = "| Tanggal   | Store | Dept  | Daily Sales   |\n";
                    $tableMarkdown .= "|:----------|:------|:------|:--------------|\n";
                    foreach ($salesData as $row) {
                        $dateFmt = Carbon::parse($row->date)->format('d M Y');
                        $salesFmt = "$" . number_format($row->daily_sales, 2);
                        $tableMarkdown .= "| {$dateFmt} | {$row->store} | {$row->dept} | {$salesFmt} |\n";
                    }
                    $responseText .= $tableMarkdown;
                    return response()->json(['response' => $this->formatGeminiResponse($responseText)]);
                }
            }

            // Retrival
            if (str_contains($lowerUserMessage, 'halo') || str_contains($lowerUserMessage, 'hai') || str_contains($lowerUserMessage, 'hi')) {
                return response()->json(['response' => $this->formatGeminiResponse('Halo! Ada yang bisa saya bantu terkait analisis penjualan?')]);
            }

            if (str_contains($lowerUserMessage, 'status toko')) {
                $storeNumber = $this->extractNumber($lowerUserMessage, 'toko');
                if ($storeNumber !== null) {
                    $latestSale = SalesData::where('store', $storeNumber)->orderBy('date', 'desc')->first();
                    if ($latestSale) {
                        $responseText = "Info: Data penjualan terakhir untuk toko **{$storeNumber}** pada tanggal **" .
                            Carbon::parse($latestSale->date)->format('d M Y') .
                            "** adalah **$" . number_format($latestSale->daily_sales, 2) . "**";
                        return response()->json(['response' => $this->formatGeminiResponse($responseText)]);
                    } else {
                        return response()->json(['response' => $this->formatGeminiResponse("Info: Tidak ada data penjualan ditemukan untuk toko {$storeNumber}.")]);
                    }
                }
            }

            $periodKeywordFound = false;
            if (
                str_contains($lowerUserMessage, 'cek periode minggu ke') ||
                str_contains($lowerUserMessage, 'cek periode bulan ke') ||
                str_contains($lowerUserMessage, 'cek periode hari ke')
            ) {
                $periodKeywordFound = true;
            }
            if ($periodKeywordFound) {
                $periodType = $this->extractPeriodType($lowerUserMessage);
                $periodNumber = $this->extractPeriodNumber($lowerUserMessage);
                if ($periodType && $periodNumber) {
                    $responseText = "Info: Pengecekan diminta untuk periode Tipe='{$periodType}', Nomor='{$periodNumber}'. ";
                    if ($periodType === 'monthly') {
                        $responseText .= "Ini bisa merujuk pada bulan ke-{$periodNumber} dalam suatu tahun. ";
                    }
                    $responseText .= "Fitur detail untuk ini sedang dalam pengembangan.";
                    return response()->json(['response' => $this->formatGeminiResponse($responseText)]);
                }
            }


            if (str_contains($lowerUserMessage, 'total dataset')) {
                $totalDataCount = SalesData::count();
                $salesDataInstance = new SalesData();
                $attributes = Schema::getColumnListing($salesDataInstance->getTable());
                $attributesCount = count($attributes);
                $attributesList = "";
                foreach ($attributes as $index => $attribute) {
                    $attributesList .= "- `" . htmlspecialchars($attribute) . "`\n";
                }

                $datasetInfo = "## Informasi Dataset Proyek (dari database `sales_data`)\n\n" .
                    "- **Sumber Dataset**: Proyek menggunakan dataset dari Kaggle Perusahaan Walmart namun data aktual diambil dari database `sales_data` lokal.\n" .
                    "- **Total Jumlah Data di Database**: " . number_format($totalDataCount) . " baris data.\n" .
                    "- **Atribut/Kolom dalam Tabel `sales_data`** (Jumlah: " . $attributesCount . "):\n" . $attributesList .
                    "- **Prediksi Penjualan**: Prediksi penjualan harian kami lakukan untuk periode 90 hari kedepan.\n";

                return response()->json(['response' => $this->formatGeminiResponse($datasetInfo)]);
            }


            $forecastKeywords = ['prediksi', 'forecast', 'ramalan', 'peramalan', 'proyeksi'];
            $foundForecastKeyword = false;
            foreach ($forecastKeywords as $kw) {
                if (str_contains($lowerUserMessage, $kw)) {
                    $foundForecastKeyword = true;
                    break;
                }
            }
            if ($foundForecastKeyword) {
                $retrievedDataContext = $this->getSalesDataContextForRAG($lowerUserMessage);
                $promptForLLM = "Anda adalah AI asisten analisis penjualan yang canggih.\n\n";
                if (!empty(trim($retrievedDataContext))) {
                    $promptForLLM .= "Berikut adalah data penjualan historis yang mungkin relevan dari sistem kami untuk membantu analisis Anda:\n";
                    $promptForLLM .= $retrievedDataContext . "\n";
                } else {
                    $generalRecentData = SalesData::orderBy('date', 'desc')
                        ->limit(3)
                        ->get(['date', 'store', 'dept', 'daily_sales']);
                    if (!$generalRecentData->isEmpty()) {
                        $promptForLLM .= "Tidak ada data spesifik yang terdeteksi dari permintaan Anda. Namun, sebagai informasi umum, berikut adalah beberapa data penjualan terkini dari sistem:\n";
                        foreach ($generalRecentData as $data) {
                            $promptForLLM .= "- Tgl: " . Carbon::parse($data->date)->format('d M Y') .
                                ", Toko: {$data->store}, Dept: {$data->dept}" .
                                ", Penjualan: $" . number_format($data->daily_sales, 2) . "\n";
                        }
                        $promptForLLM .= "\n";
                    } else {
                        $promptForLLM .= "Catatan: Tidak ada data penjualan spesifik yang terdeteksi dari permintaan Anda, dan tidak ada data penjualan umum terbaru yang bisa diambil dari sistem kami untuk dijadikan konteks.\n\n";
                    }
                }
                $promptForLLM .= "Dengan mempertimbangkan data di atas (jika ada dan relevan) serta pengetahuan umum Anda, jawablah pertanyaan pengguna berikut:\n";
                $promptForLLM .= "Pertanyaan Pengguna: " . htmlspecialchars($userMessage) . "\n\n";
                $promptForLLM .= "Jawaban Analitis Anda:";
                Log::info("RAG Prompt for Forecast: " . $promptForLLM);
                $geminiResponse = $this->askGemini($promptForLLM);
                return response()->json(['response' => $this->formatGeminiResponse($geminiResponse)]);
            }


            $allowedGeneralKeywords = [

                // Kata Kunci Umum Forecasting
                'forecasting penjualan',
                'prediksi penjualan',
                'proyeksi penjualan',
                'ramalan penjualan',
                'sales projection',
                'sales prediction',
                'demand forecasting',
                'estimasi penjualan',

                // Tujuan dan Konsep
                'kenapa perlu forecasting',
                'kegunaan forecasting',
                'fungsi forecasting',
                'tujuan sales forecasting',
                'strategi bisnis',
                'pengambilan keputusan',

                // Model/Metode
                'model peramalan',
                'model statistik',
                'algoritma prediksi penjualan',
                'time series forecasting',
                'exponential smoothing',
                'moving average',

                // Evaluasi dan Analisis
                'akurasi prediksi',
                'error forecasting',
                'metrik evaluasi',
                'MAPE',
                'RMSE',
                'evaluasi model',

                // Data Umum & Eksternal
                'pengaruh musim',
                'tren pasar',
                'kondisi pasar',
                'faktor eksternal',
                'faktor musiman',
                'libur nasional',
                'event khusus',

                // Studi Kasus / Industri Umum
                'studi kasus forecasting',
                'penerapan forecasting',
                'contoh forecasting',
                'industri retail',
                'industri makanan',
                'e-commerce forecasting',

                // Konteks Walmart (Umum)
                'data penjualan walmart',
                'analisis walmart',
                'walmart forecasting',
                'strategi walmart',
                'tren walmart',

                // Pertanyaan Definisi
                'apa itu sales forecasting',
                'definisi forecasting',
                'arti forecasting',
                'penjelasan forecasting',
                'manfaat sales forecasting',

                // Tambahan
                'peforma',
            ];

            $isGeneralSalesQuery = false;
            foreach ($allowedGeneralKeywords as $keyword) {
                if (str_contains($lowerUserMessage, $keyword)) {
                    $isGeneralSalesQuery = true;
                    break;
                }
            }

            if ($isGeneralSalesQuery) {
                $retrievedDataContext = $this->getSalesDataContextForRAG($lowerUserMessage);
                $promptForLLM = "Anda adalah AI asisten analisis penjualan yang sangat membantu dan informatif.\n\n";
                if (!empty(trim($retrievedDataContext))) {
                    $promptForLLM .= "Berikut adalah data penjualan historis yang mungkin relevan dari sistem kami untuk membantu analisis Anda:\n";
                    $promptForLLM .= $retrievedDataContext . "\n";
                } else {
                    if (str_contains($lowerUserMessage, 'analisis') || str_contains($lowerUserMessage, 'tren') || str_contains($lowerUserMessage, 'pertumbuhan') || str_contains($lowerUserMessage, 'penurunan')) {
                        $promptForLLM .= "Catatan: Tidak ada data penjualan spesifik yang terdeteksi dari permintaan Anda untuk dijadikan konteks dari sistem kami.\n\n";
                    }
                }
                $promptForLLM .= "Dengan mempertimbangkan data di atas (jika ada dan relevan) serta pengetahuan umum Anda, jawablah pertanyaan pengguna berikut:\n";
                $promptForLLM .= "Pertanyaan Pengguna: " . htmlspecialchars($userMessage) . "\n\n";
                $promptForLLM .= "Jawaban Informatif Anda:";
                Log::info("RAG Prompt for General Query: " . $promptForLLM);
                $geminiResponse = $this->askGemini($promptForLLM);
                return response()->json(['response' => $this->formatGeminiResponse($geminiResponse)]);
            }


            return response()->json([
                'response' => $this->formatGeminiResponse('Maaf, saya tidak mengerti pertanyaan Anda. Anda bisa bertanya tentang data penjualan spesifik (berdasarkan tanggal, bulan, tahun, departemen, atau toko), meminta ringkasan data penjualan terbaru, atau menanyakan prediksi penjualan dan analisis umum terkait penjualan.')
            ]);
        } catch (\Exception $e) {
            Log::error('ChatbotController Error: ' . $e->getMessage() . ' StackTrace: ' . $e->getTraceAsString());
            return response()->json([
                'response' => $this->formatGeminiResponse('Maaf, terjadi kesalahan internal di server saat memproses permintaan Anda. Tim kami telah diberitahu.')
            ], 500);
        }
    }

    private function getSalesDataContextForRAG($lowerUserMessage)
    {
        $contextParts = [];
        $queryParams = ['store' => null, 'dept' => null, 'year' => null, 'month' => null, 'date' => null];
        $limit = 3;

        if (preg_match('/(\d{4}-\d{2}-\d{2})/', $lowerUserMessage, $matches)) {
            $queryParams['date'] = $matches[1];
            $limit = 1;
        }
        if (preg_match('/toko\s*(\d+)/i', $lowerUserMessage, $matches) || preg_match('/store\s*(\d+)/i', $lowerUserMessage, $matches)) {
            $queryParams['store'] = $matches[1];
        }
        if (preg_match('/dept\s*(\d+)/i', $lowerUserMessage, $matches) || preg_match('/departemen\s*(\d+)/i', $lowerUserMessage, $matches)) {
            $queryParams['dept'] = $matches[1];
        }
        foreach ($this->bulanMap as $namaBulan => $angkaBulan) {
            if (preg_match('/' . preg_quote($namaBulan, '/') . '\s*(\d{4})/i', $lowerUserMessage, $matchesMonthYear)) {
                $queryParams['month'] = $angkaBulan;
                $queryParams['year'] = $matchesMonthYear[1];
                break;
            }
        }
        if (!$queryParams['year'] && preg_match('/tahun\s*(\d{4})/i', $lowerUserMessage, $matchesYear)) {
            $queryParams['year'] = $matchesYear[1];
        }

        $query = SalesData::query();
        $hasSpecificFilters = false;

        if ($queryParams['date']) {
            $query->where('date', $queryParams['date']);
            $hasSpecificFilters = true;
        } else {
            if ($queryParams['year']) {
                $query->whereYear('date', $queryParams['year']);
                $hasSpecificFilters = true;
            }
            if ($queryParams['month']) {
                $query->whereMonth('date', $queryParams['month']);
                $hasSpecificFilters = true;
            }
        }
        if ($queryParams['store']) {
            $query->where('store', $queryParams['store']);
            $hasSpecificFilters = true;
        }
        if ($queryParams['dept']) {
            $query->where('dept', $queryParams['dept']);
            $hasSpecificFilters = true;
        }

        $dataToPresent = collect();

        if ($hasSpecificFilters) {
            $dataToPresent = $query->orderBy('date', 'desc')->limit($limit)->get(['date', 'store', 'dept', 'daily_sales']);
        }

        if (!$dataToPresent->isEmpty()) {
            $filterDescriptions = [];
            if ($queryParams['store']) $filterDescriptions[] = "Toko {$queryParams['store']}";
            if ($queryParams['dept']) $filterDescriptions[] = "Dept {$queryParams['dept']}";
            if ($queryParams['date']) {
                $filterDescriptions[] = "tanggal " . Carbon::parse($queryParams['date'])->format('d M Y');
            } else {
                if ($queryParams['month'] && $queryParams['year']) {
                    $monthName = array_search($queryParams['month'], $this->bulanMap);
                    $monthNameDisplay = $monthName ? ucfirst(strtolower($monthName)) : "Bulan-{$queryParams['month']}";
                    $filterDescriptions[] = "bulan " . $monthNameDisplay . " {$queryParams['year']}";
                } elseif ($queryParams['year']) {
                    $filterDescriptions[] = "tahun {$queryParams['year']}";
                }
            }
            $contextParts[] = "Konteks data" . (!empty($filterDescriptions) ? " untuk " . implode(", ", $filterDescriptions) : "") . ":";
            foreach ($dataToPresent as $data) {
                $part = "- Tgl: " . Carbon::parse($data->date)->format('d M Y');
                if (!$queryParams['store'] && isset($data->store)) $part .= ", Toko: {$data->store}";
                if (!$queryParams['dept'] && isset($data->dept)) $part .= ", Dept: {$data->dept}";
                $part .= ", Penjualan: $" . number_format($data->daily_sales, 2);
                $contextParts[] = $part;
            }
        } else if ($hasSpecificFilters) {
            $contextParts[] = "Catatan: Tidak ditemukan data penjualan spesifik di sistem kami untuk filter yang terdeteksi dalam permintaan Anda.";
        }

        if (!empty($contextParts)) {
            return implode("\n", $contextParts) . "\n";
        }
        return "";
    }

    private function extractNumber($text, $keyword)
    {
        if (preg_match("/" . preg_quote($keyword, '/') . "\s*(\d+)/i", $text, $matches)) {
            return intval($matches[1]);
        }
        return null;
    }

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

    private function extractPeriodNumber($text)
    {
        if (preg_match('/(?:minggu|bulan|hari)(?:\s+ke)?\s*(\d+)/i', $text, $matches)) {
            return intval($matches[1]);
        }
        return null;
    }

    /**
     * Menerapkan format Markdown inline ke teks yang sudah di-htmlspecialchars.
     */
    private function applyInlineMarkdown($text)
    {
        // Bold: **text** or __text__
        $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);
        $text = preg_replace('/__(.*?)__/s', '<strong>$1</strong>', $text);

        // Italic: *text* or _text_
        $text = preg_replace('/(?<!\*)\*(?!\s|\*)([^*]+?)(?<!\s|\*)\*(?!\*)/s', '<em>$1</em>', $text);
        $text = preg_replace('/(?<![a-zA-Z0-9_])_([^_]+?)_(?![a-zA-Z0-9_])/s', '<em>$1</em>', $text);

        // Inline Code: `code`
        $text = preg_replace('/`([^`]+?)`/s', '<code>$1</code>', $text);

        // Links: [text](url)
        $text = preg_replace_callback(
            '/\[(.*?)\]\((.*?)\)/s',
            function ($matches) {
                $linkText = $matches[1];
                $url = htmlspecialchars($matches[2], ENT_QUOTES, 'UTF-8');
                return '<a href="' . $url . '" target="_blank">' . $linkText . '</a>';
            },
            $text
        );
        return $text;
    }

    /**
     * Mem-parsing array baris teks Markdown tabel menjadi HTML.
     * @param array $tableLines Baris-baris yang membentuk tabel Markdown.
     * @return string HTML tabel atau string kosong jika parsing gagal.
     */
    private function parseMarkdownTable(array $tableLines)
    {
        if (count($tableLines) < 2) {
            return '';
        }

        $headerLine = trim(array_shift($tableLines), " \t\n\r\0\x0B|");
        $separatorLine = trim(array_shift($tableLines), " \t\n\r\0\x0B|");


        if (!preg_match('/^\|?.*(?:---|===|:::).*\|?$/', "|" . $separatorLine . "|")) {

            return '';
        }

        $headers = array_map('trim', explode('|', $headerLine));
        $separators = array_map('trim', explode('|', $separatorLine));

        if (empty($headers[0])) array_shift($headers);
        if (empty($headers[count($headers) - 1])) array_pop($headers);
        if (empty($separators[0])) array_shift($separators);
        if (empty($separators[count($separators) - 1])) array_pop($separators);

        if (count($headers) === 0 || count($headers) !== count($separators)) {

            return '';
        }

        $alignments = [];
        foreach ($separators as $sep) {
            if (preg_match('/^:.*:$/', $sep)) {
                $alignments[] = 'center';
            } elseif (preg_match('/^:/', $sep)) {
                $alignments[] = 'left';
            } elseif (preg_match('/:$/', $sep)) {
                $alignments[] = 'right';
            } else {
                $alignments[] = '';
            }
        }

        $html = "<table border=\"1\" style=\"border-collapse: collapse; width: auto; margin-top: 10px; margin-bottom: 10px;\">\n";
        $html .= "<thead>\n<tr>\n";
        foreach ($headers as $index => $header) {
            $style = !empty($alignments[$index]) ? ' style="text-align: ' . $alignments[$index] . ';"' : '';
            $html .= "<th{$style}>" . $this->applyInlineMarkdown(htmlspecialchars(trim($header))) . "</th>\n";
        }
        $html .= "</tr>\n</thead>\n";

        $html .= "<tbody>\n";
        foreach ($tableLines as $line) {
            $trimmedDataLine = trim($line, " \t\n\r\0\x0B|");
            if (empty($trimmedDataLine) && strlen(trim($line)) > 0 && !str_contains(trim($line), '|')) {
                continue;
            }
            if (!str_contains($line, '|')) continue;

            $cells = array_map('trim', explode('|', $trimmedDataLine));

            if (empty($cells[0]) && count($cells) > 1) array_shift($cells);
            if (count($cells) > 0 && empty($cells[count($cells) - 1]) && count($cells) > 1) array_pop($cells);


            $html .= "<tr>\n";
            for ($i = 0; $i < count($headers); $i++) {
                $cellContent = isset($cells[$i]) ? trim($cells[$i]) : '';
                $style = !empty($alignments[$i]) ? ' style="text-align: ' . $alignments[$i] . ';"' : '';
                $html .= "<td{$style}>" . $this->applyInlineMarkdown(htmlspecialchars($cellContent)) . "</td>\n";
            }
            $html .= "</tr>\n";
        }
        $html .= "</tbody>\n";
        $html .= "</table>\n";

        return $html;
    }


    /**
     * Memformat teks (termasuk Markdown) ke HTML.
     */
    private function formatGeminiResponse($text)
    {
        $text = trim($text);
        $lines = explode("\n", $text);
        $htmlOutput = "";
        $inList = false;
        $listType = null;
        $inCodeBlock = false;
        $codeBlockLang = '';
        $codeBlockContent = '';

        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];
            $trimmedLine = trim($line);

            if ($inCodeBlock) {
                if (preg_match('/^```\s*$/', $trimmedLine)) {
                    $htmlOutput .= '<pre><code' . (!empty($codeBlockLang) ? ' class="language-' . htmlspecialchars($codeBlockLang) . '"' : '') . '>' . htmlspecialchars(rtrim($codeBlockContent, "\n")) . '</code></pre>' . "\n";
                    $inCodeBlock = false;
                    $codeBlockLang = '';
                    $codeBlockContent = '';
                } else {
                    $codeBlockContent .= $line . "\n";
                }
                continue;
            }

            // Close list if current line is not a list item or is empty
            if ($inList) {
                if (empty($trimmedLine) || !preg_match('/^(\*|-|\d+\.)\s+/', $trimmedLine)) {
                    $htmlOutput .= '</' . $listType . '>' . "\n";
                    $inList = false;
                    $listType = null;
                }
            }

            // Code Block (```lang or ```)
            if (preg_match('/^```(\w*)\s*$/', $trimmedLine, $matches)) {
                if ($inList) {
                    $htmlOutput .= '</' . $listType . '>' . "\n";
                    $inList = false;
                    $listType = null;
                }
                $inCodeBlock = true;
                $codeBlockLang = isset($matches[1]) ? trim($matches[1]) : '';
                $codeBlockContent = '';
                continue;
            }

            // Headings (# Text, ## Text, etc.)
            if (preg_match('/^(#{1,6})\s+(.*)/', $trimmedLine, $matches)) {
                if ($inList) {
                    $htmlOutput .= '</' . $listType . '>' . "\n";
                    $inList = false;
                    $listType = null;
                }
                $level = strlen($matches[1]);
                $content = $this->applyInlineMarkdown(htmlspecialchars(trim($matches[2])));
                $htmlOutput .= "<h{$level}>{$content}</h{$level}>\n";
                continue;
            }

            // Horizontal Rule (---, ***, ___)
            if (preg_match('/^(\*\*\*|---|___)\s*$/', $trimmedLine)) {
                if ($inList) {
                    $htmlOutput .= '</' . $listType . '>' . "\n";
                    $inList = false;
                    $listType = null;
                }
                $htmlOutput .= "<hr />\n";
                continue;
            }

            // Markdown Table Check
            // A table starts with a line containing '|', followed by a separator line with '|' and '---'
            if (
                str_contains($trimmedLine, '|') &&
                isset($lines[$i + 1]) && str_contains($lines[$i + 1], '|') && preg_match('/\|.*(?:---|===|:::).*\|/', $lines[$i + 1]) &&
                (!isset($lines[$i - 1]) || trim($lines[$i - 1]) === '' || !str_contains($lines[$i - 1], '|'))
            ) {
                $tableBlockLines = [];
                $currentTableLineIndex = $i;

                while (isset($lines[$currentTableLineIndex]) && str_contains($lines[$currentTableLineIndex], '|')) {

                    if (trim($lines[$currentTableLineIndex]) === '' && $currentTableLineIndex > $i + 1) break;
                    $tableBlockLines[] = $lines[$currentTableLineIndex];
                    $currentTableLineIndex++;
                }

                $tableHtml = $this->parseMarkdownTable($tableBlockLines);
                if (!empty($tableHtml)) {
                    if ($inList) {
                        $htmlOutput .= '</' . $listType . '>' . "\n";
                        $inList = false;
                        $listType = null;
                    }
                    $htmlOutput .= $tableHtml;
                    $i = $currentTableLineIndex - 1;
                    continue;
                }
            }

            // Blockquotes (> Text)
            if (preg_match('/^>\s+(.*)/', $trimmedLine, $matches)) {
                if ($inList) {
                    $htmlOutput .= '</' . $listType . '>' . "\n";
                    $inList = false;
                    $listType = null;
                }
                $content = $this->applyInlineMarkdown(htmlspecialchars(trim($matches[1])));
                $htmlOutput .= "<blockquote><p>{$content}</p></blockquote>\n";
                continue;
            }

            // Unordered list (* item, - item)
            if (preg_match('/^(\*|-)\s+(.*)/', $trimmedLine, $matches)) {
                if (!$inList || $listType !== 'ul') {
                    if ($inList) {
                        $htmlOutput .= '</' . $listType . '>' . "\n";
                    }
                    $htmlOutput .= "<ul>\n";
                    $inList = true;
                    $listType = 'ul';
                }
                $content = $this->applyInlineMarkdown(htmlspecialchars(trim($matches[2])));
                $htmlOutput .= '<li>' . $content . "</li>\n";
                continue;
            }
            // Ordered list (1. item)
            elseif (preg_match('/^(\d+\.)\s+(.*)/', $trimmedLine, $matches)) {
                if (!$inList || $listType !== 'ol') {
                    if ($inList) {
                        $htmlOutput .= '</' . $listType . '>' . "\n";
                    }
                    $htmlOutput .= "<ol>\n";
                    $inList = true;
                    $listType = 'ol';
                }
                $content = $this->applyInlineMarkdown(htmlspecialchars(trim($matches[2])));
                $htmlOutput .= '<li>' . $content . "</li>\n";
                continue;
            }

            // Paragraph (normal text)
            if (!empty($trimmedLine)) {
                $content = $this->applyInlineMarkdown(htmlspecialchars($trimmedLine));
                $htmlOutput .= "<p>" . $content . "</p>\n";
            } elseif (empty($trimmedLine) && $i > 0 && !empty(trim($lines[$i - 1]))) {
            }
        }

        if ($inList) {
            $htmlOutput .= '</' . $listType . '>' . "\n";
        }
        if ($inCodeBlock) {
            $htmlOutput .= '<pre><code' . (!empty($codeBlockLang) ? ' class="language-' . htmlspecialchars($codeBlockLang) . '"' : '') . '>' . htmlspecialchars(rtrim($codeBlockContent, "\n")) . '</code></pre>' . "\n";
        }

        return rtrim($htmlOutput, "\n");
    }


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
            ])->timeout(60)
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
            Log::error('Exception umum saat memproses permintaan Gemini: ' . $e->getMessage() . ' StackTrace: ' . $e->getTraceAsString());
            return 'Maaf, terjadi kesalahan umum saat memproses permintaan Anda ke AI.';
        }
    }
}
