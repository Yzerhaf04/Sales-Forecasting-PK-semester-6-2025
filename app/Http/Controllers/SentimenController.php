<?php

namespace App\Http\Controllers;

use App\Models\SentimenData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Validation\Rule; // Import Rule untuk validasi

class SentimenController extends Controller
{
    /**
     * Menampilkan halaman utama analisis sentimen dengan data statistik.
     */
    public function index()
    {
        // Ambil data statistik untuk ditampilkan di kartu-kartu sentimen
        $jumlahPositif = SentimenData::where('label_sentimen', 'positif')->count();
        $jumlahNegatif = SentimenData::where('label_sentimen', 'negatif')->count();
        $jumlahNetral  = SentimenData::where('label_sentimen', 'netral')->count();

        // Ambil timestamp pembaruan terakhir dan format
        $lastSentimenUpdateTimestamp = SentimenData::max('updated_at');
        $sentimentLastUpdateDisplay = 'N/A'; // Default value
        if ($lastSentimenUpdateTimestamp) {
            // Menggunakan Carbon untuk memformat tanggal ke format Indonesia
            $sentimentLastUpdateDisplay = Carbon::parse($lastSentimenUpdateTimestamp)->translatedFormat('d F Y H:i');
        }

        // Kirim semua data ke view 'sentimen'
        return view('sentimen', [
            'jumlahPositif' => $jumlahPositif,
            'jumlahNegatif' => $jumlahNegatif,
            'jumlahNetral' => $jumlahNetral,
            'sentimentLastUpdateDisplay' => $sentimentLastUpdateDisplay,
        ]);
    }

    /**
     * Memprediksi sentimen dari teks yang diberikan melalui API eksternal.
     */
    public function predict(Request $request)
    {
        // Validasi input
        $request->validate([
            'review_text' => 'required|string|min:3',
        ]);

        $commentText = $request->input('review_text');
        $apiEndpoint = env('SENTIMENT_API_ENDPOINT', 'http://127.0.0.1:5000/predict_sentiment');

        try {
            // Panggil API eksternal dengan timeout
            $response = Http::timeout(30)->post($apiEndpoint, [
                'text' => $commentText,
            ]);

            // Jika respons dari API berhasil
            if ($response->successful()) {
                $dataFromApi = $response->json();
                $sentimentResult = strtolower($dataFromApi['sentiment'] ?? 'tidak diketahui');

                return response()->json([
                    'message' => 'Analisis sentimen berhasil',
                    'label_sentimen' => $sentimentResult,
                    'original_comment' => $dataFromApi['received_text'] ?? $commentText
                ]);
            }

            // Jika respons dari API gagal
            $errorBody = $response->json() ?? ['message' => $response->body()];
            Log::error('Sentiment API request failed:', $errorBody);

            return response()->json([
                'error' => 'Gagal menganalisis sentimen dari layanan eksternal.',
                'details' => $errorBody
            ], $response->status());

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Sentiment API connection error:', ['message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Tidak dapat terhubung ke layanan analisis sentimen. Pastikan API berjalan.'
            ], 503); // Service Unavailable

        } catch (\Exception $e) {
            Log::error('Unexpected error during sentiment analysis:', ['message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Terjadi kesalahan tak terduga saat analisis sentimen.'
            ], 500); // Internal Server Error
        }
    }

    /**
     * Menyimpan hasil analisis sentimen ke database.
     */
    public function save(Request $request)
    {
        // Tentukan nilai sentimen yang diizinkan
        $allowedSentiments = ['positif', 'negatif', 'netral', 'tidak diketahui'];

        // Validasi data yang akan disimpan
        $validatedData = $request->validate([
            'review_text' => 'required|string|max:10000',
            'label_sentimen' => ['required', 'string', Rule::in($allowedSentiments)],
        ]);

        try {
            // Pastikan label dalam format lowercase sebelum disimpan
            $validatedData['label_sentimen'] = strtolower($validatedData['label_sentimen']);

            SentimenData::create($validatedData);

            return response()->json(['success' => true, 'message' => 'Komentar berhasil disimpan!']);
        } catch (\Exception $e) {
            Log::error('Failed to save sentiment data:', ['message' => $e->getMessage(), 'data' => $validatedData]);
            return response()->json(['success' => false, 'error' => 'Gagal menyimpan data sentimen.'], 500);
        }
    }
}
