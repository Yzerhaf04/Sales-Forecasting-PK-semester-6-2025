<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $selectedPeriod = $request->input('period', 'weekly');

        $users = User::count();

        switch ($selectedPeriod) {
            case 'daily':
                $interval = '1 day';
                $labelFormat = 'YYYY-MM-DD';
                $forecastCount = 30;
                $startDateAlign = fn($date) => Carbon::parse($date)->format('Y-m-d');
                $dateRangeCondition = "s.date = dates.day";
                break;
            case 'weekly':
                $interval = '1 week';
                $labelFormat = 'YYYY-MM-DD';
                $forecastCount = 8;
                $startDateAlign = fn($date) => Carbon::parse($date)->startOfWeek()->format('Y-m-d');
                $dateRangeCondition = "s.date >= dates.day AND s.date < dates.day + INTERVAL '1 week'";
                break;
            case 'monthly':
            default:
                $interval = '1 month';
                $labelFormat = 'YYYY-MM';
                $forecastCount = 3;
                $startDateAlign = fn($date) => Carbon::parse($date)->startOfMonth()->format('Y-m-d');
                $dateRangeCondition = "s.date >= dates.day AND s.date < dates.day + INTERVAL '1 month'";
                break;
        }

        $range = DB::table('sales_data')
            ->selectRaw('MIN(date) as start_date, MAX(date) as end_date')
            ->first();

        $startDate = $startDateAlign($range->start_date);
        $endDate = Carbon::parse($range->end_date)->format('Y-m-d');

        $salesData = DB::select("SELECT 
                to_char(dates.day, '{$labelFormat}') AS label,
                COALESCE(SUM(s.sales), 0) AS total_sales
            FROM 
                generate_series('$startDate'::date, '$endDate'::date, '$interval') AS dates(day)
            LEFT JOIN 
                sales_data s ON {$dateRangeCondition} AND s.store = ? AND s.department = ?
            GROUP BY dates.day
            ORDER BY dates.day", [$selectedStore, $selectedDept]);

        $labels = [];
        $sales = [];

        foreach ($salesData as $item) {
            $labels[] = $item->label;
            $sales[] = (float) $item->total_sales;
        }

        $forecastPart = array_slice($sales, -$forecastCount);
        $actualPart = array_slice($sales, 0, count($sales) - $forecastCount);
        $labelsActual = array_slice($labels, 0, count($sales) - $forecastCount);
        $labelsForecast = array_slice($labels, -$forecastCount);
        $transitionLabel = $labelsForecast[0] ?? end($labels);

        $dataStart = $labels[0] ?? '-';
        $dataEnd = end($labels) ?? '-';

        return view('home', [
            'widget' => [
                'users' => $users,
                'total_sales' => array_sum($sales),
            ],
            'actualSales' => $sales,
            'labelsActual' => $labelsActual,
            'labelsForecast' => $labelsForecast,
            'actualPart' => $actualPart,
            'forecastPart' => $forecastPart,
            'transitionLabel' => $transitionLabel,
            'selectedDept' => $selectedDept,
            'selectedStore' => $selectedStore,
            'selectedPeriod' => $selectedPeriod,
            'dataStart' => $dataStart,
            'dataEnd' => $dataEnd
        ]);
    }
}
