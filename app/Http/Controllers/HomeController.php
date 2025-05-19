<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SalesData;
use App\Models\ForecastData;
use Carbon\Carbon;

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

        // Set locale Carbon ke bahasa Indonesia
        Carbon::setLocale('id');

        // Ambil data sales aktual
        $rawSales = SalesData::where('dept', $selectedDept)
            ->where('store', $selectedStore)
            ->orderBy('date')
            ->get(['date', 'weekly_sales'])
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date),
                    'sales' => (float) $item->weekly_sales,
                ];
            });

        // Grouping data aktual sesuai periode
        $groupedData = $this->groupSalesData($rawSales, $period);
        $actualLabels = array_keys($groupedData);
        $actualSales = array_values($groupedData);

        // Ambil data forecast
        $forecastData = ForecastData::where('dept_id', $selectedDept)
            ->where('period', $period)
            ->orderBy('forecast_date')
            ->get(['forecast_date', 'forecast_sales']);

        // Tentukan tanggal terakhir actual sales dengan aman
        if (empty($actualLabels)) {
            $lastActualDate = now();
        } else {
            if ($period === 'monthly') {
                // Parsing format Y-m jadi tanggal Carbon, karena di groupSalesData kita ubah format monthly jadi Y-m
                $lastActualDate = Carbon::createFromFormat('Y-m', end($actualLabels))->startOfMonth();
            } else {
                $lastActualDate = Carbon::createFromFormat('Y-m-d', end($actualLabels));
            }
        }

        $forecastLabels = [];
        $forecastSales = [];

        foreach ($forecastData as $item) {
            $forecastDate = Carbon::parse($item->forecast_date);
            if ($forecastDate->greaterThan($lastActualDate)) {
                $label = $period === 'monthly'
                    ? $forecastDate->format('Y-m')  // simpan label forecast dalam format Y-m (mirip actual)
                    : $forecastDate->format('Y-m-d');

                $forecastLabels[] = $label;
                $forecastSales[] = (float) $item->forecast_sales;
            }
        }

        // Gabungkan dan urutkan label actual + forecast secara kronologis
        $allLabels = $this->mergeAndSortLabels($actualLabels, $forecastLabels, $period);

        // Buat array actualSales dan forecastSales yang sejajar dengan $allLabels
        $actualSalesComplete = [];
        $forecastSalesComplete = [];

        foreach ($allLabels as $label) {
            $actualSalesComplete[] = $groupedData[$label] ?? null;

            if (in_array($label, $forecastLabels)) {
                $index = array_search($label, $forecastLabels);
                $forecastSalesComplete[] = $forecastSales[$index];
            } else {
                $forecastSalesComplete[] = null;
            }
        }

        // Ubah label format Y-m jadi F Y untuk tampil di view
        $labelsForView = array_map(function($label) use ($period) {
            if ($period === 'monthly') {
                return Carbon::createFromFormat('Y-m', $label)->translatedFormat('F Y');
            }
            return $label;
        }, $allLabels);

        $totalStores = SalesData::distinct('store')->count('store');
        $totalDepartments = SalesData::distinct('dept')->count('dept');
        $lastUpdated = SalesData::max('updated_at');


        return view('home', [
            'widget' => [
                'users' => User::count(),
                'total_sales' => array_sum($actualSales),
            ],
            'months' => $labelsForView,  // pakai label yang sudah diformat untuk view
            'actualSales' => $actualSalesComplete,
            'forecastSales' => $forecastSalesComplete,
            'selectedDept' => $selectedDept,
            'selectedStore' => $selectedStore,
            'totalStores' => $totalStores,
            'totalDepartments' => $totalDepartments,
            'lastUpdated' => $lastUpdated,
        ]);
    }

    /**
     * Grouping sales data berdasarkan periode: daily, weekly, monthly
     */
    private function groupSalesData($salesData, $period)
    {
        if ($period === 'daily') {
            return $salesData->groupBy(fn($item) => $item['date']->format('Y-m-d'))
                ->map(fn($group) => $group->first()['sales'])
                ->toArray();
        }

        $firstDate = $salesData->first()['date'] ?? now();

        return $salesData->groupBy(function ($item) use ($period, $firstDate) {
            $date = $item['date'];
            return match ($period) {
                'weekly' => $date->copy()
                    ->subDays($date->diffInDays($firstDate) % 7)
                    ->format('Y-m-d'),
                'monthly' => $date->format('Y-m'),  // format standar untuk grouping dan sorting
                default => $date->format('Y-m-d'),
            };
        })->map(fn($group) => $group->sum('sales'))->toArray();
    }

    /**
     * Menggabungkan dan mengurutkan label actual dan forecast secara kronologis
     */
    private function mergeAndSortLabels(array $actualLabels, array $forecastLabels, string $period)
    {
        $mergedLabels = array_unique(array_merge($actualLabels, $forecastLabels));

        usort($mergedLabels, function ($a, $b) use ($period) {
            if ($period === 'monthly') {
                $dateA = Carbon::createFromFormat('Y-m', $a);
                $dateB = Carbon::createFromFormat('Y-m', $b);
            } else {
                $dateA = Carbon::createFromFormat('Y-m-d', $a);
                $dateB = Carbon::createFromFormat('Y-m-d', $b);
            }

            return $dateA->timestamp <=> $dateB->timestamp;
        });

        return $mergedLabels;
    }
}
