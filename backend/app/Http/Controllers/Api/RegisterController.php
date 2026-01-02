<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\WelcomeEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Gerar URL de verificação (usando URL do frontend)
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            $expires = now()->addHours(24)->timestamp;
            $hash = sha1($user->email);
            $signature = hash_hmac('sha256', $user->id . '|' . $hash . '|' . $expires, config('app.key'));
            
            $verificationUrl = $frontendUrl . '/verify-email?' . http_build_query([
                'id' => $user->id,
                'hash' => $hash,
                'expires' => $expires,
                'signature' => $signature
            ]);

            // Enviar e-mail de boas-vindas com link de verificação
            Mail::to($user->email)->send(new WelcomeEmail($user, $verificationUrl));

            return response()->json([
                'message' => 'Usuário criado com sucesso. Verifique seu e-mail para ativar sua conta.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao criar usuário',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify email
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer|exists:users,id',
            'hash' => 'required|string',
            'expires' => 'required|integer',
            'signature' => 'required|string',
        ]);

        // Verificar assinatura
        $expectedSignature = hash_hmac('sha256', $request->id . '|' . $request->hash . '|' . $request->expires, config('app.key'));
        if (!hash_equals($expectedSignature, $request->signature)) {
            return response()->json([
                'message' => 'Link de verificação inválido'
            ], 400);
        }

        // Verificar expiração
        if (now()->timestamp > $request->expires) {
            return response()->json([
                'message' => 'Link de verificação expirado'
            ], 400);
        }

        $user = User::findOrFail($request->id);

        if (!hash_equals((string) $request->hash, sha1($user->email))) {
            return response()->json([
                'message' => 'Link de verificação inválido'
            ], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'E-mail já verificado'
            ], 200);
        }

        if ($user->markEmailAsVerified()) {
            return response()->json([
                'message' => 'E-mail verificado com sucesso'
            ], 200);
        }

        return response()->json([
            'message' => 'Erro ao verificar e-mail'
        ], 500);
    }

    /**
     * Resend verification email
     */
    public function resendVerification(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'E-mail já verificado'
            ], 200);
        }

        // Gerar URL de verificação (usando URL do frontend)
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
        $expires = now()->addHours(24)->timestamp;
        $hash = sha1($user->email);
        $signature = hash_hmac('sha256', $user->id . '|' . $hash . '|' . $expires, config('app.key'));
        
        $verificationUrl = $frontendUrl . '/verify-email?' . http_build_query([
            'id' => $user->id,
            'hash' => $hash,
            'expires' => $expires,
            'signature' => $signature
        ]);

        Mail::to($user->email)->send(new WelcomeEmail($user, $verificationUrl));

        return response()->json([
            'message' => 'E-mail de verificação reenviado com sucesso'
        ], 200);
    }
}
