<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;

class ChatbotController extends Controller{
    public function index(){
        return view('chatbot');
    }
}
