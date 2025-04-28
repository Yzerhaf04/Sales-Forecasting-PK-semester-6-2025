<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;

class ForecastController extends Controller{
    public function index(){
        return view('forecast');
    }
}
