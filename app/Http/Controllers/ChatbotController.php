<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function index()
    {
        return view('chatbot');
    }

    public function response(Request $request)
    {
        $userMessage = $request->input('message');

        // Logika jawaban dummy (bisa pakai AI/NLP nanti)
        $reply = "Kamu bertanya: \"$userMessage\". Jawaban belum tersedia ya 😅";

        return response()->json(['reply' => $reply]);
    }
}
