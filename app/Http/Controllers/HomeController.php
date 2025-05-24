<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SalesData;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $selectedDept = $request->input('department', 1);
        $selectedStore = $request->input('store', 1);
        $period = $request->input('period', 'monthly');

        Carbon::setLocale('id');

        // Variabel untuk menampung hasil akhir
        $labelsForView = [];
        $alignedActualSales = [];
        $alignedForecastSales = [];
        $sumOfActualSales = 0;
        $sumOfPastForecastSales = 0;

        // 1. Tentukan tanggal terakhir data KESELURUHAN (harian)
        $overallLastDailyDateString = SalesData::where('dept', $selectedDept)
            ->where('store', $selectedStore)
            ->max('date');

        if ($overallLastDailyDateString) {
            $overallLastDailyDate = Carbon::parse($overallLastDailyDateString)->startOfDay();
            $forecastBoundaryStartDate = $overallLastDailyDate->copy()->subDays(89)->startOfDay();

            // 3. Ambil Data Aktual Historis HARIAN
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

            // 4. Ambil Data "Forecast dari Masa Lalu" HARIAN
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

            // 5. Agregasi kedua set data harian mentah ke periode tampilan ($displayPeriod)
            $groupedActualHistoricalSales = $this->groupSalesData($rawDailyActualHistoricalSales, $period);
            $actualLabels = !empty($groupedActualHistoricalSales) ? array_keys($groupedActualHistoricalSales) : [];

            $groupedForecastSalesFromPast = $this->groupSalesData($rawDailyForecastSalesFromPast, $period);
            $forecastLabelsFromPast = !empty($groupedForecastSalesFromPast) ? array_keys($groupedForecastSalesFromPast) : [];

            // 6. Gabungkan semua label dan urutkan
            $allCombinedLabels = $this->mergeAndSortLabels($actualLabels, $forecastLabelsFromPast, $period);

            // --- MODIFIKASI UNTUK MEMBUAT TITIK PENGHUBUNG ---
            $processedForecastDataForChart = $groupedForecastSalesFromPast; // Salin data forecast asli yang sudah diagregasi

            if (!empty($actualLabels) && !empty($groupedActualHistoricalSales)) {
                $lastActualLabelWithValue = null;
                $lastActualValue = null;

                // Dapatkan label dan nilai terakhir dari data aktual yang sudah diagregasi
                if (count($actualLabels) > 0) {
                    $potentialLastActualLabel = end($actualLabels); // Label terakhir dari data aktual
                    // Pastikan label tersebut ada dan nilainya tidak null di data aktual yang sudah diagregasi
                    if (isset($groupedActualHistoricalSales[$potentialLastActualLabel]) && !is_null($groupedActualHistoricalSales[$potentialLastActualLabel])) {
                        $lastActualLabelWithValue = $potentialLastActualLabel;
                        $lastActualValue = $groupedActualHistoricalSales[$lastActualLabelWithValue];
                    }
                }

                if ($lastActualLabelWithValue !== null && $lastActualValue !== null) {
                    // Set titik data pada label ini di data forecast (yang akan diplot)
                    // agar sama dengan titik terakhir data aktual.
                    // Ini akan membuat garis menyambung di chart.
                    $processedForecastDataForChart[$lastActualLabelWithValue] = $lastActualValue;
                }
            }
            // --- AKHIR MODIFIKASI ---

            // 7. Siapkan array sales aktual dan "forecast dari masa lalu" yang sejajar dengan $allCombinedLabels
            // Inisialisasi ulang untuk memastikan array bersih sebelum diisi
            $alignedActualSales = [];
            $alignedForecastSales = [];
            foreach ($allCombinedLabels as $label) {
                $alignedActualSales[] = $groupedActualHistoricalSales[$label] ?? null;
                // Gunakan data forecast yang sudah diproses untuk chart
                $alignedForecastSales[] = $processedForecastDataForChart[$label] ?? null;
            }

            // 8. Format label untuk ditampilkan di view
            $labelsForView = array_map(function ($label) use ($period) {
                return $this->formatLabelForView($label, $period);
            }, $allCombinedLabels);
        } else {
            // Tidak ada data sama sekali untuk store dan dept yang dipilih
            // Semua array data akan kosong, yang akan ditangani oleh view.
        }

        // Data tambahan untuk widget
        $totalStores = SalesData::distinct('store')->count('store');
        $totalDepartments = SalesData::distinct('dept')->count('dept');
        $lastDatabaseUpdate = SalesData::where('dept', $selectedDept)
            ->where('store', $selectedStore)
            ->max('updated_at'); // Lebih spesifik ke filter yang dipilih
        if ($lastDatabaseUpdate) {
            $lastDatabaseUpdate = Carbon::parse($lastDatabaseUpdate)->translatedFormat('d F Y H:i');
        } else {
            // Jika tidak ada data sama sekali, $overallLastDateString juga null.
            // Cek max updated_at dari seluruh tabel sebagai fallback.
            $lastUpdateOverall = SalesData::max('updated_at');
            if ($lastUpdateOverall) {
                $lastDatabaseUpdate = Carbon::parse($lastUpdateOverall)->translatedFormat('d F Y H:i');
            }
        }


        return view('home', [
            'widget' => [
                'users' => User::count(),
                // Total sales bisa merupakan gabungan keduanya atau sesuai kebutuhan Anda
                'total_sales' => $sumOfActualSales + $sumOfPastForecastSales,
            ],
            'months' => $labelsForView, // Ini adalah $allCombinedLabels yang sudah diformat
            'actualSales' => $alignedActualSales,
            'forecastSales' => $alignedForecastSales, // Ini sekarang berisi "forecast dari masa lalu"
            'selectedDept' => $selectedDept,
            'selectedStore' => $selectedStore,
            'totalStores' => $totalStores,
            'totalDepartments' => $totalDepartments,
            'lastUpdated' => $lastDatabaseUpdate,
            'period' => $period,
        ]);
    }

    /**
     * Mengelompokkan data penjualan berdasarkan periode (daily, weekly, monthly).
     * Input $salesData adalah Collection dari array ['date' => Carbon, 'sales' => float].
     */
    private function groupSalesData(Collection $salesData, string $period): array
    {
        if ($salesData->isEmpty()) {
            return [];
        }

        // Grouping berdasarkan format tanggal periode
        $grouped = $salesData->groupBy(function ($item) use ($period) {
            $date = $item['date']; // $date sudah merupakan objek Carbon
            return match ($period) {
                'daily' => $date->format('Y-m-d'),
                'weekly' => $date->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'), // Label = awal minggu
                'monthly' => $date->format('Y-m'), // Label = YYYY-MM
                default => $date->format('Y-m-d'), // Fallback
            };
        });

        // Jumlahkan sales untuk setiap grup dan urutkan berdasarkan kunci (label periode)
        return $grouped->map(fn($group) => $group->sum('sales'))
            ->sortKeys()
            ->toArray();
    }

    /**
     * Menggabungkan label aktual dan forecast, lalu mengurutkannya secara kronologis.
     */
    private function mergeAndSortLabels(array $actualLabels, array $forecastLabels, string $period): array
    {
        $mergedLabels = array_unique(array_merge($actualLabels, $forecastLabels));
        usort($mergedLabels, function ($a, $b) use ($period) {
            $dateA = $this->parseLabelToDate($a, $period);
            $dateB = $this->parseLabelToDate($b, $period);
            if (!$dateA || !$dateB) return 0; // Jika salah satu label tidak valid
            return $dateA->timestamp <=> $dateB->timestamp;
        });
        return $mergedLabels;
    }

    /**
     * Helper untuk mem-parsing label string (Y-m atau Y-m-d) menjadi objek Carbon.
     */
    private function parseLabelToDate(string $label, string $period): ?Carbon
    {
        try {
            if ($period === 'monthly') {
                return Carbon::createFromFormat('Y-m', $label)->startOfMonth();
            }
            // Untuk daily dan weekly, labelnya adalah Y-m-d
            return Carbon::createFromFormat('Y-m-d', $label)->startOfDay();
        } catch (\Exception $e) {
            // Jika format label tidak sesuai, kembalikan null
            // error_log("Failed to parse label: $label with period: $period - " . $e->getMessage()); // Opsional: logging
            return null;
        }
    }

    /**
     * Helper untuk memformat label menjadi string yang lebih human-readable untuk view.
     */
    private function formatLabelForView(string $label, string $period): string
    {
        $date = $this->parseLabelToDate($label, $period);
        if (!$date) return $label; // Kembalikan label asli jika parsing gagal

        if ($period === 'monthly') {
            return $date->translatedFormat('F Y'); // e.g., "Januari 2024"
        }
        // Untuk daily dan weekly
        return $date->translatedFormat('d M Y'); // e.g., "23 Mei 2024"
    }
}
