<?php

namespace Domain\Letter\Services;

use Infra\Letter\Models\Classification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiLetterAnalysisService
{
    public function analyze($fileData, $mimeType)
    {
        $apiKey = env('GEMINI_API_KEY');
        
        if (!$apiKey) {
            throw new \Exception("GEMINI_API_KEY is not set");
        }

        // Context Injection: Fetch available classifications
        $availableClassifications = Classification::pluck('name')->implode(', ');
        $classificationContext = $availableClassifications 
            ? " Untuk 'sifat', prioritaskan memilih dari daftar berikut jika cocok: [{$availableClassifications}]."
            : "";

        $systemPrompt = "Anda adalah asisten administrasi digital yang ahli membaca surat resmi Indonesia. Tugas Anda adalah mengekstrak data dari gambar/PDF surat yang diberikan.

Aturan Ekstraksi:

sender_receiver: Cari nama instansi/organisasi di Kop Surat (bagian paling atas).

date_of_letter: Cari tanggal surat. Ubah formatnya menjadi 'YYYY-MM-DD' (ISO 8601). Contoh: jika tertulis '13 Januari 2024', ubah menjadi '2024-01-13'.

letter_number: Cari label 'Nomor:' atau 'No:'. Ambil seluruh string nomornya.

year: Ambil tahun dari bagian akhir nomor surat atau bagian akhir tanggal surat.

subject: Cari label 'Perihal:' atau 'Hal:'.

classification: Cari label 'Sifat:' atau 'Klasifikasi:' lalu ambil seluruh stringnya atau kata kunci seperti 'Biasa', 'Penting', 'Rahasia', atau 'Segera'.{$classificationContext} Jika tidak ada, isi 'Biasa'.

description: Berikan ringkasan 1-2 kalimat tentang maksud utama surat tersebut.

Kembalikan hasil HANYA dalam format JSON. Jika data tidak ditemukan, isi dengan null. 
Format JSON yang diharapkan:
{
    'sender_receiver': '...',
    'date_of_letter': '...',
    'year': '...',
    'letter_number': '...',
    'subject': '...',
    'classification': '...',
    'description': '...'
}";

        $base64Data = base64_encode($fileData);

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent";
        
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'x-goog-api-key' => $apiKey
        ])->post($url, [
                "contents" => [
                    [
                        "parts" => [
                            ["text" => $systemPrompt],
                            ["inline_data" => [
                                "mime_type" => $mimeType,
                                "data" => $base64Data
                            ]]
                        ]
                    ]
                ],
                "generationConfig" => [
                    "response_mime_type" => "application/json"
                ]
            ]);

        if ($response->failed()) {
            Log::error('Gemini API Error: ' . $response->body());
            throw new \Exception("Gemini API request failed: " . $response->body());
        }

        $data = $response->json();
        
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            $jsonString = $data['candidates'][0]['content']['parts'][0]['text'];
            // Clean up potentially wrapped markdown code blocks if Gemini sends them despite instructions
            $jsonString = str_replace(['```json', '```'], '', $jsonString);
            $result = json_decode($jsonString, true);

            if ($result && isset($result['classification'])) {
                // Find matching classification in DB
                $classification = Classification::where('name', 'LIKE', $result['classification'])
                    ->orWhere('name', 'LIKE', '%' . $result['classification'] . '%')
                    ->first();
                
                $result['classification_id'] = $classification ? $classification->id : null;
            }

            return $result;
        }

        return null;
    }
}
