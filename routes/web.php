<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\SentimenController;

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/sentimen', [SentimenController::class, 'index'])->name('sentimen');
Route::post('/sentimen/predict', [SentimenController::class, 'predict'])->name('sentimen.predict');
Route::post('/sentimen/save', [SentimenController::class, 'save'])->name('sentimen.save');

Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

Route::get('/chatbot', [ChatbotController::class, 'index'])->name('chatbot');
Route::post('/chatbot/response', [ChatbotController::class, 'response'])->name('chatbot.response');
Route::get('/about', function () {
    return view('about');
})->name('about');
