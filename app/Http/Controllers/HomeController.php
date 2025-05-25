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
        $period = $request->input('period', 'monthly'); // Variabel $period digunakan untuk periode tampilan

        Carbon::setLocale('id'); // Atur locale di awal untuk semua operasi Carbon

        // Variabel untuk menampung hasil akhir
        $labelsForView = [];
        $alignedActualSales = [];
        $alignedForecastSales = [];
        $sumOfActualSales = 0;
        $sumOfPastForecastSales = 0;
        $lastUpdated = SalesData::max('updated_at') ?? null;

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

            // 5. Agregasi kedua set data harian mentah ke periode tampilan ($period)
            $groupedActualHistoricalSales = $this->groupSalesData($rawDailyActualHistoricalSales, $period);
            $actualLabels = !empty($groupedActualHistoricalSales) ? array_keys($groupedActualHistoricalSales) : [];

            $groupedForecastSalesFromPast = $this->groupSalesData($rawDailyForecastSalesFromPast, $period);
            $forecastLabelsFromPast = !empty($groupedForecastSalesFromPast) ? array_keys($groupedForecastSalesFromPast) : [];

            // 6. Gabungkan semua label dan urutkan
            $allCombinedLabels = $this->mergeAndSortLabels($actualLabels, $forecastLabelsFromPast, $period);

            // --- MODIFIKASI UNTUK MEMBUAT TITIK PENGHUBUNG ---
            $processedForecastDataForChart = $groupedForecastSalesFromPast;

            if (!empty($actualLabels) && !empty($groupedActualHistoricalSales)) {
                $lastActualLabelWithValue = null;
                $lastActualValue = null;

                if (count($actualLabels) > 0) {
                    $potentialLastActualLabel = end($actualLabels);
                    if (isset($groupedActualHistoricalSales[$potentialLastActualLabel]) && !is_null($groupedActualHistoricalSales[$potentialLastActualLabel])) {
                        $lastActualLabelWithValue = $potentialLastActualLabel;
                        $lastActualValue = $groupedActualHistoricalSales[$lastActualLabelWithValue];
                    }
                }

                if ($lastActualLabelWithValue !== null && $lastActualValue !== null) {
                    $processedForecastDataForChart[$lastActualLabelWithValue] = $lastActualValue;
                }
            }

            $alignedActualSales = [];
            $alignedForecastSales = [];
            foreach ($allCombinedLabels as $label) {
                $alignedActualSales[] = $groupedActualHistoricalSales[$label] ?? null;
                $alignedForecastSales[] = $processedForecastDataForChart[$label] ?? null;
            }

            $labelsForView = array_map(function ($label) use ($period) {
                return $this->formatLabelForView($label, $period);
            }, $allCombinedLabels);

            // Dapatkan lastUpdated spesifik untuk filter
            $lastUpdateForFilter = SalesData::where('dept', $selectedDept)
                ->where('store', $selectedStore)
                ->max('updated_at'); // Kolom 'updated_at' dari database
            if ($lastUpdateForFilter) {
                // Carbon::parse() di sini aman karena $lastUpdateForFilter adalah timestamp dari DB
                $lastUpdatedDateTime = Carbon::parse($lastUpdateForFilter);
                $lastUpdated = $lastUpdatedDateTime->translatedFormat('d F Y H:i');
                $lastUpdatedDateOnly = $lastUpdatedDateTime->translatedFormat('d F Y');
            } else {
                $lastUpdated = 'N/A';
                $lastUpdatedDateOnly = 'N/A';
            }

            // Jika $lastUpdateForFilter null, $lastUpdated akan tetap 'N/A' dari inisialisasi.

        } else {
            // Tidak ada data sales untuk filter ini, tapi mungkin ada update di tabel secara umum
            $lastUpdateOverall = SalesData::max('updated_at');
            if ($lastUpdateOverall) {
                $lastUpdatedDateTime = Carbon::parse($lastUpdateOverall);
                $lastUpdated = $lastUpdatedDateTime->translatedFormat('d F Y H:i');
                $lastUpdatedDateOnly = $lastUpdatedDateTime->translatedFormat('d F Y');
            } else {
                $lastUpdated = 'N/A';
                $lastUpdatedDateOnly = 'N/A';
            }
        }

        // Mengambil daftar ID Store dan Department yang unik
        $distinctStores = SalesData::select('store')->distinct()->orderBy('store', 'asc')->pluck('store');
        $distinctDepartments = SalesData::select('dept')->distinct()->orderBy('dept', 'asc')->pluck('dept');

        // Menghitung jumlahnya jika masih diperlukan
        $totalStores = $distinctStores->count();
        $totalDepartments = $distinctDepartments->count();

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
            'distinctStores' => $distinctStores,             // Kirim ID Store unik
            'distinctDepartments' => $distinctDepartments,   // Kirim ID Department unik
            'totalStores' => $totalStores,           // Kirim jumlah Store jika perlu
            'totalDepartments' => $totalDepartments, // Kirim jumlah Department jika perlu
            'lastUpdatedDateOnly' => $lastUpdatedDateOnly,
            'lastUpdated' => $lastUpdated,
            'period' => $period,
        ]);
    }

    // Fungsi parseTanggalIndonesia dihapus karena tidak digunakan dan berpotensi menyebabkan kebingungan.
    // Carbon::parse() pada timestamp dari database sudah cukup, dan translatedFormat() untuk output.

    private function groupSalesData(Collection $salesData, string $period): array
    {
        if ($salesData->isEmpty()) {
            return [];
        }
        $grouped = $salesData->groupBy(function ($item) use ($period) {
            $date = $item['date'];
            return match ($period) {
                'daily' => $date->format('Y-m-d'),
                'weekly' => $date->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'),
                'monthly' => $date->format('Y-m'),
                default => $date->format('Y-m-d'),
            };
        });
        return $grouped->map(fn($group) => $group->sum('sales'))
            ->sortKeys()
            ->toArray();
    }

    private function mergeAndSortLabels(array $actualLabels, array $forecastLabels, string $period): array
    {
        $mergedLabels = array_unique(array_merge($actualLabels, $forecastLabels));
        usort($mergedLabels, function ($a, $b) use ($period) {
            $dateA = $this->parseLabelToDate($a, $period);
            $dateB = $this->parseLabelToDate($b, $period);
            if (!$dateA || !$dateB) return 0;
            return $dateA->timestamp <=> $dateB->timestamp;
        });
        return $mergedLabels;
    }

    private function parseLabelToDate(string $label, string $period): ?Carbon
    {
        try {
            if ($period === 'monthly') {
                return Carbon::createFromFormat('Y-m', $label)->startOfMonth();
            }
            return Carbon::createFromFormat('Y-m-d', $label)->startOfDay();
        } catch (\Exception $e) {
            return null;
        }
    }

    private function formatLabelForView(string $label, string $period): string
    {
        $date = $this->parseLabelToDate($label, $period);
        if (!$date) return $label;
        if ($period === 'monthly') {
            return $date->translatedFormat('F Y');
        }
        return $date->translatedFormat('Y-m-d');
    }
}
