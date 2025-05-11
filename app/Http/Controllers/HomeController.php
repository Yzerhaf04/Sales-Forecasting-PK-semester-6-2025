<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SalesData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Get selected department from request or default to 1
        $selectedDept = $request->input('department', 1);

        // Data User
        $users = User::count();

        // Get sales data for selected department
        $salesData = SalesData::select(
                DB::raw("TO_CHAR(date, 'Mon') as month"),
                DB::raw("EXTRACT(YEAR FROM date) as year"),
                DB::raw("TO_CHAR(date, 'MM') as month_num"),
                DB::raw("SUM(weekly_sales) as total_sales")
            )
            ->where('dept', $selectedDept) // Filter by selected department
            ->groupBy(DB::raw("1, 2, 3"))
            ->orderBy('year')
            ->orderBy('month_num')
            ->get();

        // Prepare chart data
        $months = [];
        $actualSales = [];

        foreach ($salesData as $item) {
            $months[] = $item->month . ' ' . $item->year;
            $actualSales[] = (float)$item->total_sales;
        }

        // Generate forecast
        $forecastSales = $this->generateForecast($actualSales);

        return view('home', [
            'widget' => [
                'users' => $users,
                'total_sales' => array_sum($actualSales),
            ],
            'months' => $months,
            'actualSales' => $actualSales,
            'forecastSales' => $forecastSales,
            'selectedDept' => $selectedDept
        ]);
    }

    private function generateForecast(array $actualSales): array
    {
        $forecast = [];
        $windowSize = 3; // Use 3 months for moving average

        foreach ($actualSales as $index => $sale) {
            if ($index < $windowSize) {
                $forecast[] = $sale; // Use actual data for first few months
            } else {
                // Calculate moving average of last 3 months
                $lastThree = array_slice($actualSales, $index - $windowSize, $windowSize);
                $forecast[] = array_sum($lastThree) / $windowSize;
            }
        }

        return $forecast;
    }
}
