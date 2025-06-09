<?php

namespace App\Http\Controllers;

use App\Models\SentimenData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
            $response = Http::timeout(30)->post($apiEndpoint, [
                'text' => $commentText,
            ]);

            if ($response->successful()) {
                $dataFromPythonApi = $response->json();

                $sentimentResult = $dataFromPythonApi['sentiment'] ?? 'Tidak diketahui';


                $normalizedSentiment = strtolower($sentimentResult);

                return response()->json([
                    'message' => 'Analisis sentimen berhasil',
                    'label_sentimen' => $normalizedSentiment,
                    'original_comment' => $dataFromPythonApi['received_text'] ?? $commentText
                ]);
            } else {
                $errorBody = $response->json() ?? ['message' => $response->body(), 'status_code' => $response->status()];
                Log::error('Sentiment API request failed:', $errorBody);
                return response()->json([
                    'error' => 'Gagal menganalisis sentimen dari layanan eksternal.',
                    'details' => $errorBody
                ], $response->status());
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Sentiment API connection error:', ['message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Tidak dapat terhubung ke layanan analisis sentimen. Pastikan layanan API berjalan.'
            ], 503);
        } catch (\Exception $e) {
            Log::error('Unexpected error during sentiment analysis:', ['message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Terjadi kesalahan tak terduga saat analisis sentimen.'
            ], 500);
        }
    }

    public function save(Request $request)
    {
        $validatedData = $request->validate([
            'review_text' => 'required|string|max:10000',
            'label_sentimen' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $allowedSentiments = ['positif', 'negatif', 'netral', 'tidak diketahui'];
                    if (!in_array(strtolower($value), $allowedSentiments)) {
                        $fail(ucfirst($attribute) . ' tidak valid. Hanya boleh Positif, Negatif, Netral, atau Tidak Diketahui.');
                    }
                },
            ],
        ]);

        try {

            $validatedData['label_sentimen'] = strtolower($validatedData['label_sentimen']);

            SentimenData::create($validatedData);
            return response()->json(['success' => true, 'message' => 'Komentar berhasil disimpan!']);
        } catch (\Exception $e) {
            Log::error('Failed to save sentiment data:', ['message' => $e->getMessage(), 'data' => $validatedData]);
            return response()->json(['success' => false, 'error' => 'Gagal menyimpan data sentimen.'], 500);
        }
    }
}
