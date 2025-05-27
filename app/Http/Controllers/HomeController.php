<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SalesData;
use App\Models\SentimenData;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str; // Untuk Str::lower

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Variabel yang sudah ada sebelumnya
        $selectedDept = $request->input('department', 1);
        $selectedStore = $request->input('store', 1);
        $period = $request->input('period', 'monthly');

        Carbon::setLocale('id');

        // Inisialisasi variabel untuk data chart penjualan
        $labelsForView = [];
        $alignedActualSales = [];
        $alignedForecastSales = [];
        $sumOfActualSales = 0;
        $sumOfPastForecastSales = 0;
        $lastUpdatedSales = 'T/A'; // Tidak Ada/Tersedia
        $lastUpdatedDateOnlySales = 'T/A'; // Tidak Ada/Tersedia
        $totalSentimenComments = SentimenData::count();

        // --- Logika untuk SalesData ---
        $overallLastDailyDateString = SalesData::where('dept', $selectedDept)
            ->where('store', $selectedStore)
            ->max('date');

        if ($overallLastDailyDateString) {
            $overallLastDailyDate = Carbon::parse($overallLastDailyDateString)->startOfDay();
            $forecastBoundaryStartDate = $overallLastDailyDate->copy()->subDays(89)->startOfDay();

            $rawDailyActualHistoricalSales = SalesData::where('dept', $selectedDept)
                ->where('store', $selectedStore)
                ->where('date', '<', $forecastBoundaryStartDate->toDateString())
                ->orderBy('date', 'asc')
                ->get(['date', 'daily_sales'])
                ->map(function ($item) {
                    return [
                        'date' => Carbon::parse($item->date)->startOfDay(),
                        'sales' => (float) $item->daily_sales,
                    ];
                });
            $sumOfActualSales = $rawDailyActualHistoricalSales->sum('sales');

            $rawDailyForecastSalesFromPast = SalesData::where('dept', $selectedDept)
                ->where('store', $selectedStore)
                ->whereBetween('date', [
                    $forecastBoundaryStartDate->toDateString(),
                    $overallLastDailyDate->toDateString()
                ])
                ->orderBy('date', 'asc')
                ->get(['date', 'daily_sales'])
                ->map(function ($item) {
                    return [
                        'date' => Carbon::parse($item->date)->startOfDay(),
                        'sales' => (float) $item->daily_sales,
                    ];
                });
            $sumOfPastForecastSales = $rawDailyForecastSalesFromPast->sum('sales');

            $groupedActualHistoricalSales = $this->groupSalesData($rawDailyActualHistoricalSales, $period);
            $actualLabels = !empty($groupedActualHistoricalSales) ? array_keys($groupedActualHistoricalSales) : [];

            $groupedForecastSalesFromPast = $this->groupSalesData($rawDailyForecastSalesFromPast, $period);
            $forecastLabelsFromPast = !empty($groupedForecastSalesFromPast) ? array_keys($groupedForecastSalesFromPast) : [];

            $allCombinedLabels = $this->mergeAndSortLabels($actualLabels, $forecastLabelsFromPast, $period);

            $processedForecastDataForChart = $groupedForecastSalesFromPast;
            if (!empty($actualLabels) && !empty($groupedActualHistoricalSales)) {
                $lastActualLabelWithValue = end($actualLabels);
                if (isset($groupedActualHistoricalSales[$lastActualLabelWithValue])) {
                    $lastActualValue = $groupedActualHistoricalSales[$lastActualLabelWithValue];
                    if (!isset($processedForecastDataForChart[$lastActualLabelWithValue])) {
                         $processedForecastDataForChart = array_merge([$lastActualLabelWithValue => $lastActualValue], $processedForecastDataForChart);
                         // Jika perlu, urutkan kembali $processedForecastDataForChart berdasarkan kunci
                         // ksort($processedForecastDataForChart); // Jika label adalah tanggal/angka yang bisa di-sort
                    }
                }
            }

            foreach ($allCombinedLabels as $label) {
                $alignedActualSales[] = $groupedActualHistoricalSales[$label] ?? null;
                if ($label === ($actualLabels[count($actualLabels)-1] ?? null) && isset($groupedActualHistoricalSales[$label])) {
                    $alignedForecastSales[] = $groupedActualHistoricalSales[$label];
                } else {
                    $alignedForecastSales[] = $processedForecastDataForChart[$label] ?? null;
                }
            }

            $labelsForView = array_map(function ($label) use ($period) {
                return $this->formatLabelForView($label, $period);
            }, $allCombinedLabels);

            $lastUpdateForFilterSales = SalesData::where('dept', $selectedDept)
                ->where('store', $selectedStore)
                ->max('updated_at');
            if ($lastUpdateForFilterSales) {
                $lastUpdatedDateTimeSales = Carbon::parse($lastUpdateForFilterSales);
                $lastUpdatedSales = $lastUpdatedDateTimeSales->translatedFormat('d F Y H:i');
                $lastUpdatedDateOnlySales = $lastUpdatedDateTimeSales->translatedFormat('d F Y');
            }
        } else {
            $lastUpdateOverallSales = SalesData::max('updated_at');
            if ($lastUpdateOverallSales) {
                $lastUpdatedDateTimeSales = Carbon::parse($lastUpdateOverallSales);
                $lastUpdatedSales = $lastUpdatedDateTimeSales->translatedFormat('d F Y H:i');
                $lastUpdatedDateOnlySales = $lastUpdatedDateTimeSales->translatedFormat('d F Y');
            }
        }
        // --- Akhir Logika SalesData ---


        // --- Logika untuk SentimenData (Count dan Last Update) ---
        $jumlahPositif = SentimenData::where('label_sentimen', 'positif')->count();
        $jumlahNegatif = SentimenData::where('label_sentimen', 'negatif')->count();
        $jumlahNetral  = SentimenData::where('label_sentimen', 'netral')->count();

        $lastSentimenUpdateTimestamp = SentimenData::max('updated_at');
        $lastUpdatedSentimenDisplay = 'T/A'; // Default jika tidak ada data sentimen
        if ($lastSentimenUpdateTimestamp) {
            $lastUpdatedSentimenDisplay = Carbon::parse($lastSentimenUpdateTimestamp)->translatedFormat('d F Y H:i');
        }
        // --- Akhir Logika SentimenData (Count dan Last Update) ---

        // --- Logika untuk Donut Chart Kata Populer Sentimen ---
        $sentimentDonutLabels = [];
        $sentimentDonutDataValues = [];
        $minWordLength = 3; // Minimal panjang kata yang dihitung
        $topNWords = 5;     // Jumlah kata teratas yang ditampilkan

        // Daftar stop words gabungan Bahasa Indonesia dan Bahasa Inggris
        $stopWords = [
            // Bahasa Indonesia
            'yang', 'untuk', 'pada', 'ke', 'para', 'namun', 'menurut', 'antara', 'dia', 'dua',
            'ia', 'seperti', 'jika', 'maka', 'dan', 'atau', 'tetapi', 'dengan', 'dari',
            'oleh', 'lagi', 'juga', 'saat', 'hal', 'akan', 'adalah', 'ialah', 'saya', 'kamu',
            'kami', 'anda', 'mereka', 'ini', 'itu', 'tersebut', 'sangat', 'sekali', 'tidak',
            'sudah', 'belum', 'bisa', 'harus', 'agar', 'supaya', 'karena', 'sebab', 'mungkin',
            'hanya', 'saja', 'pula', 'pun', 'agak', 'ada', 'adanya', 'adapun', 'agaknya',
            'bagaimana', 'bagaimanapun', 'bagi', 'bahkan', 'bahwa', 'bahwasanya', 'beberapa',
            'begitu', 'begitupun', 'demi', 'demikian', 'demikianlah', 'di', 'dong', 'enggak',
            'enggaknya', 'entah', 'entahlah', 'gak', 'guna', 'hai', 'halo', 'hingga', 'iya',
            'jadi', 'jangan', 'jangankan', 'justru', 'kalau', 'kok', 'kecuali', 'kemudian',
            'kenapa', 'kepada', 'kepadanya', 'kita', 'lah', 'lain', 'lainnya', 'lalu', 'lebih',
            'macam', 'mana', 'manalagi', 'masih', 'melainkan', 'memang', 'meski', 'meskipun',
            'nah', 'nanti', 'oh', 'ok', 'pasti', 'per', 'pernah',
            'rupa', 'rupanya', 'sebagaimana', 'sebelum', 'sebelumnya', 'sebenarnya', 'sedang',
            'sedangkan', 'segala', 'sehingga', 'sejak', 'sekitar', 'selain', 'selalu', 'selama',
            'seluruh', 'seluruhnya', 'sementara', 'semua', 'sendiri', 'sering', 'serta', 'siapa',
            'sini', 'situ', 'suatu', 'tanpa', 'tapi', 'telah', 'tentang', 'tentu', 'terhadap',
            'toh', 'turut', 'untukmu', 'wah', 'wahai', 'walau', 'walaupun', 'ya', 'yaitu', 'yakni', 'nya','shopee',

            // Bahasa Inggris
            'a', 'an', 'the', 'is', 'are', 'was', 'were', 'be', 'being', 'been', 'have', 'has', 'had',
            'do', 'does', 'did', 'will', 'would', 'should', 'can', 'could', 'may', 'might', 'must',
            'and', 'but', 'or', 'nor', 'for', 'so', 'yet', 'if', 'then', 'else', 'when', 'where',
            'why', 'how', 'what', 'which', 'who', 'whom', 'whose', 'this', 'that', 'these', 'those',
            'am', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'me', 'him', 'her', 'us', 'them',
            'my', 'your', 'his', 'its', 'our', 'your', 'their', 'mine', 'yours', 'hers', 'ours', 'theirs',
            'myself', 'yourself', 'himself', 'herself', 'itself', 'ourselves', 'yourselves', 'themselves',
            'in', 'on', 'at', 'by', 'from', 'to', 'up', 'down', 'out', 'over', 'under', 'again',
            'further', 'then', 'once', 'here', 'there', 'all', 'any', 'both', 'each', 'few', 'more',
            'most', 'other', 'some', 'such', 'no', 'not', 'only', 'own', 'same', 'so', 'than',
            'too', 'very', 's', 't', 'just', 'don', 'shouldv', 'now', 'd', 'll', 'm', 'o', 're',
            've', 'y', 'ain', 'aren', 'couldn', 'didn', 'doesn', 'hadn', 'hasn', 'haven', 'isn',
            'ma', 'mightn', 'mustn', 'needn', 'shan', 'shouldn', 'wasn', 'weren', 'won', 'wouldn',
            'about', 'above', 'after', 'against', 'because', 'before', 'below', 'between', 'into',
            'through', 'during', 'of', 'off', 'throughout', 'until', 'while', 'with',
        ];

        $allReviewTexts = SentimenData::pluck('review_text');
        $wordCounts = [];

        if ($allReviewTexts->isNotEmpty()) {
            foreach ($allReviewTexts as $text) {
                if (empty(trim($text))) continue;

                // 1. Bersihkan dari tanda baca dan ubah ke huruf kecil
                $cleanedText = Str::lower(preg_replace('/[^\p{L}\p{N}\s]/u', '', $text));

                // 2. Pecah menjadi kata-kata
                $words = preg_split('/\s+/', $cleanedText, -1, PREG_SPLIT_NO_EMPTY);

                foreach ($words as $word) {
                    // 3. Filter stop words dan panjang kata minimal
                    if (!in_array($word, $stopWords) && Str::length($word) >= $minWordLength) {
                        if (!isset($wordCounts[$word])) {
                            $wordCounts[$word] = 0;
                        }
                        $wordCounts[$word]++;
                    }
                }
            }

            // 4. Urutkan berdasarkan frekuensi dan ambil top N
            arsort($wordCounts); // Urutkan array asosiatif berdasarkan value (frekuensi) secara descending
            $topWords = array_slice($wordCounts, 0, $topNWords, true); // Ambil N teratas, pertahankan kunci

            foreach ($topWords as $word => $count) {
                $sentimentDonutLabels[] = $word;
                $sentimentDonutDataValues[] = $count;
            }
        }
        // --- Akhir Logika Donut Chart ---


        // Mengambil daftar ID Store dan Department yang unik untuk filter dropdown
        $distinctStores = SalesData::select('store')->distinct()->orderBy('store', 'asc')->pluck('store');
        $distinctDepartments = SalesData::select('dept')->distinct()->orderBy('dept', 'asc')->pluck('dept');
        $totalStores = $distinctStores->count();
        $totalDepartments = $distinctDepartments->count();

        $sentimenTerbaru = SentimenData::latest('updated_at')->first(); // Sudah ada di atas, bisa dipindahkan

        return view('home', [
            'widget' => [
                'users' => User::count(),
                'total_sales' => $sumOfActualSales + $sumOfPastForecastSales,
            ],
            'months' => $labelsForView,
            'actualSales' => $alignedActualSales,
            'forecastSales' => $alignedForecastSales,
            'selectedDept' => $selectedDept,
            'selectedStore' => $selectedStore,
            'distinctStores' => $distinctStores,
            'distinctDepartments' => $distinctDepartments,
            'totalStores' => $totalStores,
            'totalDepartments' => $totalDepartments,
            'lastUpdatedDateOnly' => $lastUpdatedDateOnlySales,
            'lastUpdated' => $lastUpdatedSales,
            'period' => $period,
            'jumlahPositif' => $jumlahPositif,
            'jumlahNegatif' => $jumlahNegatif,
            'jumlahNetral' => $jumlahNetral,
            'sentimenTerbaru' => $sentimenTerbaru,
            'sentimentLastUpdateDisplay' => $lastUpdatedSentimenDisplay,
            'sentimentDonutLabels' => $sentimentDonutLabels,
            'sentimentDonutDataValues' => $sentimentDonutDataValues,
            'totalSentimenComments' => $totalSentimenComments,
        ]);
    }

    // Fungsi-fungsi pembantu yang sudah ada
    private function groupSalesData(Collection $salesData, string $period): array
    {
        if ($salesData->isEmpty()) {
            return [];
        }
        $grouped = $salesData->groupBy(function ($item) use ($period) {
            $date = $item['date']; // Asumsi 'date' adalah objek Carbon dari mapping sebelumnya
            return match ($period) {
                'daily' => $date->format('Y-m-d'),
                'weekly' => $date->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'), // Kunci sebagai tanggal awal minggu
                'monthly' => $date->format('Y-m'), // Kunci sebagai tahun-bulan
                default => $date->format('Y-m-d'),
            };
        });
        return $grouped->map(fn($group) => $group->sum('sales'))
            ->sortKeys() // Urutkan berdasarkan kunci (tanggal/bulan)
            ->toArray();
    }

    private function parseLabelToDate(string $label, string $period): ?Carbon
    {
        try {
            if ($period === 'monthly') {
                return Carbon::createFromFormat('Y-m', $label)->startOfMonth();
            }
            // Untuk 'weekly' dan 'daily', label sudah 'Y-m-d' dari groupSalesData
            return Carbon::createFromFormat('Y-m-d', $label)->startOfDay();
        } catch (\Exception $e) {
            // Log::error("Error saat mem-parsing label ke tanggal: {$label}, Periode: {$period}. Error: " . $e->getMessage());
            return null;
        }
    }

    private function mergeAndSortLabels(array $actualLabels, array $forecastLabels, string $period): array
    {
        $mergedLabels = array_unique(array_merge($actualLabels, $forecastLabels));
        usort($mergedLabels, function ($a, $b) use ($period) {
            $dateA = $this->parseLabelToDate($a, $period);
            $dateB = $this->parseLabelToDate($b, $period);
            if (!$dateA || !$dateB) return 0; // Tangani jika parse gagal
            return $dateA->timestamp <=> $dateB->timestamp;
        });
        return $mergedLabels;
    }

    private function formatLabelForView(string $label, string $period): string
    {
        $date = $this->parseLabelToDate($label, $period);
        if (!$date) return $label; // Jika parse gagal, kembalikan label asli

        if ($period === 'monthly') {
            return $date->translatedFormat('F Y'); // Misal: "Mei 2023"
        } elseif ($period === 'weekly') {
            // Label adalah Y-m-d (awal minggu)
            return "W" . $date->format('W') . " (" . $date->translatedFormat('d M') . ")"; // Misal: "W22 (29 Mei)"
        }
        // default 'harian'
        return $date->translatedFormat('d M Y'); // Misal: "27 Mei 2023"
    }
}
