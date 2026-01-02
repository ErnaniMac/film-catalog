<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class TmdbController extends Controller
{
    /**
     * Search movies in TMDB API
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:1|max:255',
            'page' => 'nullable|integer|min:1|max:1000',
        ]);

        $query = trim($request->get('query'));
        $page = $request->get('page', 1);
        $apiKey = config('services.tmdb.api_key');
        $apiUrl = config('services.tmdb.api_url', 'https://api.themoviedb.org/3');

        if (!$apiKey) {
            return response()->json([
                'error' => 'TMDB API key não configurada'
            ], 500);
        }

        // Cache key
        $cacheKey = "tmdb_search_" . md5($query) . "_page_{$page}";

        $response = Cache::remember($cacheKey, 3600, function () use ($apiUrl, $apiKey, $query, $page) {
            return Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Accept' => 'application/json',
            ])->get("{$apiUrl}/search/movie", [
                'query' => $query,
                'page' => $page,
                'language' => 'pt-BR',
            ]);
        });

        if ($response->failed()) {
            return response()->json([
                'error' => 'Erro ao buscar filmes na API TMDB'
            ], $response->status());
        }

        return response()->json($response->json(), 200);
    }
}
