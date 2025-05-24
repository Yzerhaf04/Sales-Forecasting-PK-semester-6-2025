<?php

namespace App\Http\Controllers;

use App\Models\SentimenData; // Pastikan model ini ada dan sesuai
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // Tambahkan untuk logging error

class SentimenController extends Controller
{
    public function index()
    {
        // Pastikan view 'sentimen.blade.php' ada di resources/views/
        return view('sentimen');
    }

    public function predict(Request $request)
    {
        // 1. Ubah validasi dan input key ke 'review_text'
        $request->validate([
            'review_text' => 'required|string|min:3',
        ]);

        $commentText = $request->input('review_text');
        // Pastikan API Python Anda berjalan di alamat ini
        $apiEndpoint = env('SENTIMENT_API_ENDPOINT', 'http://127.0.0.1:5000/predict_sentiment');

        try {
            $response = Http::timeout(15)->post($apiEndpoint, [ // Timeout bisa disesuaikan
                'text' => $commentText,
            ]);

            if ($response->successful()) {
                $dataFromPythonApi = $response->json();
                $sentimentResult = $dataFromPythonApi['sentiment'] ?? 'Tidak diketahui';

                // 2. Ubah key response ke 'label_sentimen'
                return response()->json([
                    'message' => 'Analisis sentimen berhasil',
                    'label_sentimen' => $sentimentResult, // Ini yang akan diterima JavaScript
                    'original_comment' => $dataFromPythonApi['received_text'] ?? $commentText
                ]);
            } else {
                $errorBody = $response->json() ?? ['message' => $response->body()];
                Log::error("Sentimen API Error: Status " . $response->status(), ['details' => $errorBody]);
                return response()->json([
                    'error' => 'Gagal menganalisis sentimen dari layanan eksternal.',
                    'details' => $errorBody
                ], $response->status());
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("Sentimen API ConnectionException: " . $e->getMessage());
            return response()->json([
                'error' => 'Tidak dapat terhubung ke layanan analisis sentimen.'
            ], 503); // Service Unavailable
        } catch (\Exception $e) {
            Log::error("Sentimen API Exception: " . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan tak terduga saat analisis sentimen.'
            ], 500); // Internal Server Error
        }
    }

    public function save(Request $request)
    {
        // Validasi data yang akan disimpan
        $validatedData = $request->validate([
            'review_text' => 'required|string|max:1000', // Sesuaikan max length
            'label_sentimen' => 'required|string|in:Positif,Negatif,Netral,POSITIF,NEGATIF,NETRAL,positif,negatif,netral', // Sesuaikan dengan kemungkinan output model Anda
        ]);

        try {
            SentimenData::create($validatedData);
            return response()->json(['success' => true, 'message' => 'Komentar berhasil disimpan!']);
        } catch (\Exception $e) {
            Log::error("Gagal menyimpan sentimen: " . $e->getMessage(), ['data' => $validatedData]);
            return response()->json(['success' => false, 'error' => 'Gagal menyimpan data sentimen.'], 500);
        }
    }
}
