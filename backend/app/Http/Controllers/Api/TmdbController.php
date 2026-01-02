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

        // Busca aproximada: fazer múltiplas buscas com variações da query
        $searchQueries = $this->generateSearchVariations($query);
        $allResults = [];
        $seenIds = [];

        foreach ($searchQueries as $searchQuery) {
            $cacheKey = "tmdb_search_" . md5($searchQuery) . "_page_{$page}";
            
            $response = Cache::remember($cacheKey, 3600, function () use ($apiUrl, $apiKey, $searchQuery, $page) {
                return Http::withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Accept' => 'application/json',
                ])->get("{$apiUrl}/search/movie", [
                    'query' => $searchQuery,
                    'page' => $page,
                    'language' => 'pt-BR',
                    'include_adult' => false,
                ]);
            });

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['results'])) {
                    foreach ($data['results'] as $movie) {
                        // Evitar duplicatas
                        if (!in_array($movie['id'], $seenIds)) {
                            $allResults[] = $movie;
                            $seenIds[] = $movie['id'];
                        }
                    }
                }
            }
        }

        // Ordenar por relevância (popularidade e vote_average)
        usort($allResults, function ($a, $b) {
            $scoreA = ($a['popularity'] ?? 0) + (($a['vote_average'] ?? 0) * 10);
            $scoreB = ($b['popularity'] ?? 0) + (($b['vote_average'] ?? 0) * 10);
            return $scoreB <=> $scoreA;
        });

        // Limitar resultados por página (20 por página)
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $paginatedResults = array_slice($allResults, $offset, $perPage);

        return response()->json([
            'page' => $page,
            'results' => $paginatedResults,
            'total_results' => count($allResults),
            'total_pages' => ceil(count($allResults) / $perPage),
        ], 200);

        if ($response->failed()) {
            return response()->json([
                'error' => 'Erro ao buscar filmes na API TMDB'
            ], $response->status());
        }

        return response()->json($response->json(), 200);
    }

    /**
     * Gera variações da query para busca aproximada
     */
    private function generateSearchVariations(string $query): array
    {
        $variations = [];
        
        // Query original
        $variations[] = $query;
        
        // Query em minúsculas
        $variations[] = strtolower($query);
        
        // Query em maiúsculas
        $variations[] = strtoupper($query);
        
        // Query com primeira letra maiúscula
        $variations[] = ucfirst(strtolower($query));
        
        // Se a query tem múltiplas palavras, buscar também por cada palavra individualmente
        $words = explode(' ', trim($query));
        if (count($words) > 1) {
            foreach ($words as $word) {
                if (strlen(trim($word)) >= 3) {
                    $variations[] = trim($word);
                }
            }
        }
        
        // Remover duplicatas e valores vazios
        $variations = array_unique(array_filter($variations));
        
        return $variations;
    }
}
