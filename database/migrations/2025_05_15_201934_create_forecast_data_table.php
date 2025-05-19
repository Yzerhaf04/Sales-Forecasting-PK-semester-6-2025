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
        $period = $request->input('period', 'monthly');  // default pakai English

        $users = User::count();

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

        // Ambil data forecast dari tabel ForecastData
        $forecastData = ForecastData::where('dept_id', $selectedDept)
            ->where('period', $period)
            ->orderBy('forecast_date')
            ->get(['forecast_date', 'forecast_sales']);

        // Ambil tanggal terakhir actual sales
        $lastActualDate = $period === 'monthly'
            ? Carbon::createFromFormat('F Y', end($actualLabels))->startOfMonth()
            : Carbon::createFromFormat('Y-m-d', end($actualLabels));

        // Siapkan label dan nilai forecast
        $forecastLabels = [];
        $forecastSales = [];

        foreach ($forecastData as $item) {
            $forecastDate = Carbon::parse($item->forecast_date);
            if ($forecastDate->greaterThan($lastActualDate)) {
                $label = $period === 'monthly'
                    ? $forecastDate->format('F Y')
                    : $forecastDate->format('Y-m-d');

                $forecastLabels[] = $label;
                $forecastSales[] = (float) $item->forecast_sales;
            }
        }

        // Gabungkan label keseluruhan
        $allLabels = array_merge($actualLabels, $forecastLabels);

        // Untuk Chart.js: pad forecast dengan null di awal agar sejajar
        $actualSalesComplete = array_merge($actualSales, array_fill(0, count($forecastLabels), null));
        $forecastSalesComplete = array_merge(array_fill(0, count($actualLabels), null), $forecastSales);

        return view('home', [
            'widget' => [
                'users' => $users,
                'total_sales' => array_sum($actualSales),
            ],
            'months' => $allLabels,
            'actualSales' => $actualSalesComplete,
            'forecastSales' => $forecastSalesComplete,
            'selectedDept' => $selectedDept,
            'selectedStore' => $selectedStore,
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
                'monthly' => $date->format('F Y'),
                default => $date->format('Y-m-d'),
            };
        })->map(fn($group) => $group->sum('sales'))->toArray();
    }
}
