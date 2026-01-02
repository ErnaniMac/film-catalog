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

        // A API do TMDB já faz busca parcial por padrão (busca por substring)
        // Buscar com a query original - a API retorna títulos que contenham a palavra
        $cacheKey = "tmdb_search_" . md5($query) . "_page_{$page}";

        $response = Cache::remember($cacheKey, 3600, function () use ($apiUrl, $apiKey, $query, $page) {
            return Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Accept' => 'application/json',
            ])->get("{$apiUrl}/search/movie", [
                'query' => $query,
                'page' => $page,
                'language' => 'pt-BR',
                'include_adult' => false,
            ]);
        });

        if ($response->failed()) {
            return response()->json([
                'error' => 'Erro ao buscar filmes na API TMDB'
            ], $response->status());
        }

        $data = $response->json();
        
        // Filtrar resultados para garantir que contenham a palavra buscada (case-insensitive)
        if (isset($data['results']) && !empty($query)) {
            $searchTerm = strtolower($query);
            $filteredResults = [];
            
            foreach ($data['results'] as $movie) {
                $title = strtolower($movie['title'] ?? '');
                $overview = strtolower($movie['overview'] ?? '');
                
                // Verifica se o título ou sinopse contém a palavra buscada
                // str_contains faz busca parcial (substring)
                if (str_contains($title, $searchTerm) || str_contains($overview, $searchTerm)) {
                    $filteredResults[] = $movie;
                }
            }
            
            // Se não encontrou resultados com filtro, retorna os resultados originais da API
            // (a API já faz busca parcial, então pode ter resultados relevantes)
            if (empty($filteredResults)) {
                $filteredResults = $data['results'];
            }
            
            $data['results'] = $filteredResults;
            $data['total_results'] = count($filteredResults);
            $data['total_pages'] = ceil(count($filteredResults) / 20);
        }

        return response()->json($data, 200);

        if ($response->failed()) {
            return response()->json([
                'error' => 'Erro ao buscar filmes na API TMDB'
            ], $response->status());
        }

        return response()->json($response->json(), 200);
    }

}
