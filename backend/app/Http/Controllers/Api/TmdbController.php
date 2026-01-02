<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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

        try {
            $response = Cache::remember($cacheKey, 3600, function () use ($apiUrl, $apiKey, $query, $page) {
                $httpResponse = Http::timeout(10)->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Accept' => 'application/json',
                ])->get("{$apiUrl}/search/movie", [
                    'query' => $query,
                    'page' => $page,
                    'language' => 'pt-BR',
                    'include_adult' => false,
                ]);

                // Se a resposta falhou, não cachear
                if ($httpResponse->status() !== 200) {
                    throw new \Exception('TMDB API request failed');
                }

                return $httpResponse;
            });

            // Verificar se a resposta foi bem-sucedida
            if ($response->status() !== 200) {
                // Se falhou, limpar o cache para essa query e retornar erro
                Cache::forget($cacheKey);
                
                // Verificar se é rate limit (429)
                if ($response->status() === 429) {
                    return response()->json([
                        'error' => 'Muitas requisições. Aguarde alguns segundos antes de tentar novamente.',
                        'message' => 'Rate limit excedido. O TMDB permite 40 requisições por 10 segundos.'
                    ], 429);
                }

                return response()->json([
                    'error' => 'Erro ao buscar filmes na API TMDB',
                    'status' => $response->status()
                ], $response->status());
            }

            $data = $response->json();

            // Verificar se há resultados válidos - não cachear resultados vazios incorretos
            if (!isset($data['results']) || (empty($data['results']) && isset($data['total_results']) && $data['total_results'] > 0)) {
                // Se a API diz que há resultados mas não retornou, limpar cache
                Cache::forget($cacheKey);
            }

            return response()->json($data, 200);

        } catch (\Exception $e) {
            // Em caso de exceção, limpar cache e retornar erro
            Cache::forget($cacheKey);
            
            Log::error('TMDB API Error: ' . $e->getMessage(), [
                'query' => $query,
                'page' => $page
            ]);

            return response()->json([
                'error' => 'Erro ao buscar filmes na API TMDB',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
