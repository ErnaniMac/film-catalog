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

        // Normalizar a query: remover acentos e caracteres especiais para melhor busca
        $normalizedQuery = $this->normalizeQuery($query);
        
        // Cache key usando query normalizada
        $cacheKey = "tmdb_search_" . md5($normalizedQuery) . "_page_{$page}";

        $response = Cache::remember($cacheKey, 3600, function () use ($apiUrl, $apiKey, $normalizedQuery, $page) {
            return Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Accept' => 'application/json',
            ])->get("{$apiUrl}/search/movie", [
                'query' => $normalizedQuery,
                'page' => $page,
                'language' => 'pt-BR',
                'include_adult' => false,
                // A API do TMDB já faz busca aproximada por padrão
                // Não precisa de parâmetros adicionais para busca fuzzy
            ]);
        });

        if ($response->failed()) {
            return response()->json([
                'error' => 'Erro ao buscar filmes na API TMDB'
            ], $response->status());
        }

        $data = $response->json();
        
        // Filtrar resultados para garantir que contenham a palavra buscada (busca mais flexível)
        if (isset($data['results']) && !empty($query)) {
            $searchTerms = explode(' ', strtolower($normalizedQuery));
            $data['results'] = array_filter($data['results'], function ($movie) use ($searchTerms, $normalizedQuery) {
                $title = strtolower($movie['title'] ?? '');
                $overview = strtolower($movie['overview'] ?? '');
                $searchText = $title . ' ' . $overview;
                
                // Verifica se pelo menos um termo da busca está presente
                foreach ($searchTerms as $term) {
                    if (strlen($term) >= 3 && str_contains($searchText, $term)) {
                        return true;
                    }
                }
                
                // Se o termo é muito curto, aceita se estiver no início do título
                if (strlen($normalizedQuery) < 3) {
                    return str_starts_with($title, strtolower($normalizedQuery));
                }
                
                return true; // Mantém todos os resultados da API (já são relevantes)
            });
            
            // Reindexar array após filtro
            $data['results'] = array_values($data['results']);
            $data['total_results'] = count($data['results']);
        }

        return response()->json($data, 200);
    }

    /**
     * Normaliza a query removendo acentos e caracteres especiais
     */
    private function normalizeQuery(string $query): string
    {
        // Remove acentos
        $query = iconv('UTF-8', 'ASCII//TRANSLIT', $query);
        
        // Remove caracteres especiais, mantém apenas letras, números e espaços
        $query = preg_replace('/[^a-zA-Z0-9\s]/', '', $query);
        
        // Remove espaços múltiplos
        $query = preg_replace('/\s+/', ' ', $query);
        
        return trim($query);
    }
}
