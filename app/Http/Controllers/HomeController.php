<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Data User
        $users = User::count();

        // Data Sales
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $actualSales = [65, 59, 80, 81, 56, 55, 40, 30, 45, 60, 70, 85];
        $forecastSales = [65, 59, 80, 81, 56, 55, 40, 30, 45, 60, 75, 90];

        return view('home', [
            'widget' => [
                'users' => $users,
                //... tambahkan data widget lain jika ada
            ],
            'months' => $months,
            'actualSales' => $actualSales,
            'forecastSales' => $forecastSales
        ]);
    }
}
