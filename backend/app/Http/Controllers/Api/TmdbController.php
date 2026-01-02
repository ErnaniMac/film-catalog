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
            $data = Cache::remember($cacheKey, 3600, function () use ($apiUrl, $apiKey, $query, $page) {
                try {
                    $httpResponse = Http::timeout(10)->withHeaders([
                        'Authorization' => "Bearer {$apiKey}",
                        'Accept' => 'application/json',
                    ])->get("{$apiUrl}/search/movie", [
                        'query' => $query,
                        'page' => $page,
                        'language' => 'pt-BR',
                        'include_adult' => false,
                    ]);

                    // Usar throw() para lançar exceção automaticamente em caso de erro HTTP
                    $httpResponse->throw();
                    
                    // Retornar os dados JSON
                    return $httpResponse->json();
                } catch (\Illuminate\Http\Client\RequestException $e) {
                    // Se for rate limit (429), relançar com mensagem específica
                    if ($e->response && $e->response->status() === 429) {
                        throw new \Exception('Rate limit excedido. O TMDB permite 40 requisições por 10 segundos.');
                    }
                    throw $e;
                }
            });

            // Verificar se há resultados válidos - não cachear resultados vazios incorretos
            if (!isset($data['results'])) {
                // Se não tem results, limpar cache e retornar erro
                Cache::forget($cacheKey);
                return response()->json([
                    'error' => 'Resposta inválida da API TMDB'
                ], 500);
            }

            // Se a API retornou resultados vazios mas diz que há resultados, pode ser rate limit
            // Nesse caso, limpar cache para forçar nova busca
            if (empty($data['results']) && isset($data['total_results']) && $data['total_results'] > 0) {
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

            // Verificar se é rate limit
            if (str_contains($e->getMessage(), '429')) {
                return response()->json([
                    'error' => 'Muitas requisições. Aguarde alguns segundos antes de tentar novamente.',
                    'message' => 'Rate limit excedido. O TMDB permite 40 requisições por 10 segundos.'
                ], 429);
            }

            return response()->json([
                'error' => 'Erro ao buscar filmes na API TMDB',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
