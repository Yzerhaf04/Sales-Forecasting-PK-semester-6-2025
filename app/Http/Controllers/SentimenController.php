<?php

namespace App\Http\Controllers;

use App\Models\SentimenData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SentimenController extends Controller
{
    public function index()
    {
        return view('sentimen');
    }

    public function predict(Request $request)
    {
        $request->validate([
            'review_text' => 'required|string|min:3',
        ]);

        $commentText = $request->input('review_text');
        $apiEndpoint = env('SENTIMENT_API_ENDPOINT', 'http://127.0.0.1:5000/predict_sentiment');

        try {
            $response = Http::timeout(15)->post($apiEndpoint, [
                'text' => $commentText,
            ]);

            if ($response->successful()) {
                $dataFromPythonApi = $response->json();
                $sentimentResult = $dataFromPythonApi['sentiment'] ?? 'Tidak diketahui';

                return response()->json([
                    'message' => 'Analisis sentimen berhasil',
                    'label_sentimen' => $sentimentResult,
                    'original_comment' => $dataFromPythonApi['received_text'] ?? $commentText
                ]);
            } else {
                $errorBody = $response->json() ?? ['message' => $response->body()];
                return response()->json([
                    'error' => 'Gagal menganalisis sentimen dari layanan eksternal.',
                    'details' => $errorBody
                ], $response->status());
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'error' => 'Tidak dapat terhubung ke layanan analisis sentimen.'
            ], 503);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan tak terduga saat analisis sentimen.'
            ], 500);
        }
    }

    public function save(Request $request)
    {
        $validatedData = $request->validate([
            'review_text' => 'required|string|max:1000',
            'label_sentimen' => 'required|string|in:Positif,Negatif,Netral,POSITIF,NEGATIF,NETRAL,positif,negatif,netral', // Sesuaikan dengan kemungkinan output model Anda
        ]);

        try {
            SentimenData::create($validatedData);
            return response()->json(['success' => true, 'message' => 'Komentar berhasil disimpan!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Gagal menyimpan data sentimen.'], 500);
        }
    }
}
