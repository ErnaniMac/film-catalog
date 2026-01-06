<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirect(): JsonResponse
    {
        try {
            // Verificar se as credenciais estão configuradas
            if (empty(config('services.google.client_id')) || empty(config('services.google.client_secret'))) {
                return response()->json([
                    'error' => 'Credenciais do Google não configuradas. Verifique o arquivo .env'
                ], 500);
            }

            $url = Socialite::driver('google')
                ->stateless()
                ->redirect()
                ->getTargetUrl();

            return response()->json(['url' => $url], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar URL de redirect do Google: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erro ao iniciar autenticação com Google'
            ], 500);
        }
    }

    /**
     * Handle Google OAuth callback
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            // Verificar se há erro na query string do Google
            if ($request->has('error')) {
                $error = $request->get('error');
                
                $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
                $errorMessage = $error === 'access_denied' 
                    ? 'Autenticação cancelada pelo usuário' 
                    : 'Erro ao autenticar com Google';
                $errorUrl = $frontendUrl . '/login?error=' . urlencode($errorMessage);
                
                return redirect($errorUrl);
            }

            // Verificar se as credenciais do Google estão configuradas
            if (empty(config('services.google.client_id')) || empty(config('services.google.client_secret'))) {
                $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
                $errorUrl = $frontendUrl . '/login?error=' . urlencode('Credenciais do Google não configuradas');
                
                return redirect($errorUrl);
            }

            $googleUser = Socialite::driver('google')->stateless()->user();

            if (!$googleUser || !$googleUser->getEmail()) {
                $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
                $errorUrl = $frontendUrl . '/login?error=' . urlencode('Não foi possível obter dados do Google');
                
                return redirect($errorUrl);
            }

            // Verificar se o usuário já existe pelo email
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Se o usuário existe mas não tem google_id, atualizar
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->getId(),
                        'email_verified_at' => now() // Google já verifica o email
                    ]);
                }
            } else {
                // Criar novo usuário
                $user = User::create([
                    'name' => $googleUser->getName() ?? $googleUser->getEmail(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt(Str::random(32)), // Senha aleatória, não será usada
                    'email_verified_at' => now() // Google já verifica o email
                ]);
                
                // Atribuir role 'user' ao novo usuário
                $user->assignRole('user');
            }

            // Gerar token
            $token = $user->createToken('auth-token')->plainTextToken;
            $user->load('roles', 'permissions');

            // Retornar URL de redirecionamento para o frontend
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];
            
            $redirectUrl = $frontendUrl . '/auth/google/callback?' . http_build_query([
                'token' => $token,
                'user' => urlencode(json_encode($userData))
            ]);

            // Redirecionar diretamente para o frontend
            return redirect($redirectUrl);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Erro de conexão no callback do Google OAuth: ' . $e->getMessage());
            
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            $errorUrl = $frontendUrl . '/login?error=' . urlencode('Erro de conexão com Google');
            
            return redirect($errorUrl);
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            $errorUrl = $frontendUrl . '/login?error=' . urlencode('Sessão expirada. Tente novamente.');
            
            return redirect($errorUrl);
        } catch (\Exception $e) {
            Log::error('Erro no callback do Google OAuth: ' . $e->getMessage());
            
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            $errorUrl = $frontendUrl . '/login?error=' . urlencode('Erro ao autenticar com Google');
            
            return redirect($errorUrl);
        }
    }
}

