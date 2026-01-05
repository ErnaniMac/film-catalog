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
                /** @var \Illuminate\Http\Client\Response $httpResponse */
                $httpResponse = Http::timeout(10)->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Accept' => 'application/json',
                ])->get("{$apiUrl}/search/movie", [
                    'query' => $query,
                    'page' => $page,
                    'language' => 'pt-BR',
                    'include_adult' => false,
                ]);

                // Verificar se houve erro HTTP
                if ($httpResponse->clientError() || $httpResponse->serverError()) {
                    $status = method_exists($httpResponse, 'status') ? $httpResponse->status() : 500;
                    if ($status === 429) {
                        throw new \Exception('Rate limit excedido. O TMDB permite 40 requisições por 10 segundos.');
                    }
                    throw new \Exception('TMDB API request failed with status: ' . $status);
                }

                // Retornar os dados JSON
                $jsonData = $httpResponse->body();
                $decoded = json_decode($jsonData, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid JSON response from TMDB API');
                }
                
                // Limitar a 15 resultados por página
                if (isset($decoded['results']) && is_array($decoded['results'])) {
                    $decoded['results'] = array_slice($decoded['results'], 0, 15);
                    // Ajustar total_pages baseado no limite de 15 por página
                    if (isset($decoded['total_results'])) {
                        $decoded['total_pages'] = (int) ceil($decoded['total_results'] / 15);
                    }
                }
                
                return $decoded;
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

    /**
     * Get movie details including credits and additional info
     */
    public function details(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer|min:1',
        ]);

        $movieId = $request->get('id');
        $apiKey = config('services.tmdb.api_key');
        $apiUrl = config('services.tmdb.api_url', 'https://api.themoviedb.org/3');

        if (!$apiKey) {
            return response()->json([
                'error' => 'TMDB API key não configurada'
            ], 500);
        }

        // Cache key
        $cacheKey = "tmdb_movie_{$movieId}";

        try {
            $data = Cache::remember($cacheKey, 3600, function () use ($apiUrl, $apiKey, $movieId) {
                /** @var \Illuminate\Http\Client\Response $movieResponse */
                $movieResponse = Http::timeout(10)->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Accept' => 'application/json',
                ])->get("{$apiUrl}/movie/{$movieId}", [
                    'language' => 'pt-BR',
                    'append_to_response' => 'credits',
                ]);

                // Verificar se houve erro HTTP
                if ($movieResponse->clientError() || $movieResponse->serverError()) {
                    $status = method_exists($movieResponse, 'status') ? $movieResponse->status() : 500;
                    if ($status === 429) {
                        throw new \Exception('Rate limit excedido. O TMDB permite 40 requisições por 10 segundos.');
                    }
                    throw new \Exception('TMDB API request failed with status: ' . $status);
                }

                // Retornar os dados JSON
                $jsonData = $movieResponse->body();
                $decoded = json_decode($jsonData, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid JSON response from TMDB API');
                }
                
                return $decoded;
            });

            return response()->json($data, 200);

        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            
            Log::error('TMDB API Error (details): ' . $e->getMessage(), [
                'movie_id' => $movieId
            ]);

            return response()->json([
                'error' => 'Erro ao buscar detalhes do filme na API TMDB',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Discover movies with filters
     */
    public function discover(Request $request): JsonResponse
    {
        $request->validate([
            'page' => 'nullable|integer|min:1|max:1000',
            'with_genres' => 'nullable|string',
            'primary_release_year' => 'nullable|string',
            'sort_by' => 'nullable|string',
        ]);

        $page = $request->get('page', 1);
        $apiKey = config('services.tmdb.api_key');
        $apiUrl = config('services.tmdb.api_url', 'https://api.themoviedb.org/3');

        if (!$apiKey) {
            return response()->json([
                'error' => 'TMDB API key não configurada'
            ], 500);
        }

        // Construir parâmetros de filtro
        $sortBy = $request->get('sort_by', 'popularity.desc');
        
        // Validar valores de sort_by permitidos pela API do TMDB
        $allowedSortBy = [
            'popularity.asc', 'popularity.desc',
            'release_date.asc', 'release_date.desc',
            'vote_average.asc', 'vote_average.desc',
            'vote_count.asc', 'vote_count.desc'
        ];
        
        if (!in_array($sortBy, $allowedSortBy)) {
            $sortBy = 'popularity.desc'; // Valor padrão se inválido
        }
        
        $params = [
            'page' => $page,
            'language' => 'pt-BR',
            'include_adult' => false,
            'sort_by' => $sortBy,
        ];
        
        // Log para debug
        Log::info('TMDB Discover - Sort By', [
            'request_sort_by' => $request->get('sort_by'),
            'validated_sort_by' => $sortBy,
            'params' => $params
        ]);

        if ($request->has('with_genres') && $request->get('with_genres')) {
            $params['with_genres'] = $request->get('with_genres');
        }

        if ($request->has('primary_release_year') && $request->get('primary_release_year')) {
            $year = $request->get('primary_release_year');
            if ($year === 'before-1990') {
                // Para filmes anteriores a 1990, usar primary_release_date
                $params['primary_release_date.gte'] = '1900-01-01';
                $params['primary_release_date.lte'] = '1989-12-31';
            } else {
                $params['primary_release_year'] = $year;
            }
        }

        // Cache key baseado nos filtros
        $cacheKey = "tmdb_discover_" . md5(json_encode($params));
        
        try {
            $data = Cache::remember($cacheKey, 3600, function () use ($apiUrl, $apiKey, $params) {
                /** @var \Illuminate\Http\Client\Response $httpResponse */
                $httpResponse = Http::timeout(10)->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Accept' => 'application/json',
                ])->get("{$apiUrl}/discover/movie", $params);

                if ($httpResponse->clientError() || $httpResponse->serverError()) {
                    $status = method_exists($httpResponse, 'status') ? $httpResponse->status() : 500;
                    if ($status === 429) {
                        throw new \Exception('Rate limit excedido. O TMDB permite 40 requisições por 10 segundos.');
                    }
                    throw new \Exception('TMDB API request failed with status: ' . $status);
                }

                $jsonData = $httpResponse->body();
                $decoded = json_decode($jsonData, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid JSON response from TMDB API');
                }
                
                // Limitar a 15 resultados por página
                if (isset($decoded['results']) && is_array($decoded['results'])) {
                    $decoded['results'] = array_slice($decoded['results'], 0, 15);
                    // Ajustar total_pages baseado no limite de 15 por página
                    if (isset($decoded['total_results'])) {
                        $decoded['total_pages'] = (int) ceil($decoded['total_results'] / 15);
                    }
                }
                
                return $decoded;
            });

            return response()->json($data, 200);

        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            
            Log::error('TMDB API Error (discover): ' . $e->getMessage(), [
                'params' => $params
            ]);

            return response()->json([
                'error' => 'Erro ao buscar filmes na API TMDB',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of movie genres
     */
    public function genres(): JsonResponse
    {
        $apiKey = config('services.tmdb.api_key');
        $apiUrl = config('services.tmdb.api_url', 'https://api.themoviedb.org/3');

        if (!$apiKey) {
            return response()->json([
                'error' => 'TMDB API key não configurada'
            ], 500);
        }

        $cacheKey = "tmdb_genres";

        try {
            $data = Cache::remember($cacheKey, 86400, function () use ($apiUrl, $apiKey) {
                /** @var \Illuminate\Http\Client\Response $httpResponse */
                $httpResponse = Http::timeout(10)->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Accept' => 'application/json',
                ])->get("{$apiUrl}/genre/movie/list", [
                    'language' => 'pt-BR',
                ]);

                if ($httpResponse->clientError() || $httpResponse->serverError()) {
                    throw new \Exception('TMDB API request failed with status: ' . $httpResponse->status());
                }

                $jsonData = $httpResponse->body();
                $decoded = json_decode($jsonData, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid JSON response from TMDB API');
                }
                
                return $decoded;
            });

            return response()->json($data, 200);

        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            
            Log::error('TMDB API Error (genres): ' . $e->getMessage());

            return response()->json([
                'error' => 'Erro ao buscar gêneros na API TMDB',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
