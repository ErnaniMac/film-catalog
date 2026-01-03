<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\ResetPasswordEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    /**
     * Send password reset link
     */
    public function sendResetLink(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $user = User::where('email', $validated['email'])->first();

            // Gerar token de redefinição
            $token = Password::createToken($user);

            // Gerar URL de redefinição (usando URL do frontend)
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            $expires = now()->addMinutes(60)->timestamp;
            $signature = hash_hmac('sha256', $token . '|' . $user->email . '|' . $expires, config('app.key'));
            
            $resetUrl = $frontendUrl . '/reset-password?' . http_build_query([
                'token' => $token,
                'email' => $user->email,
                'expires' => $expires,
                'signature' => $signature
            ]);

            // Enviar e-mail de redefinição
            try {
                Mail::to($user->email)->send(new ResetPasswordEmail($user, $resetUrl));
            } catch (\Exception $mailException) {
                // Log do erro de email mas não falha a requisição
                \Log::error('Erro ao enviar email de reset de senha: ' . $mailException->getMessage());
                
                // Em desenvolvimento, retornar a URL diretamente
                if (config('app.env') === 'local') {
                    return response()->json([
                        'message' => 'Link de redefinição gerado (email não enviado em desenvolvimento)',
                        'reset_url' => $resetUrl
                    ], 200);
                }
                
                throw $mailException;
            }

            return response()->json([
                'message' => 'Link de redefinição de senha enviado para seu e-mail'
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao enviar e-mail de redefinição',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset password
     */
    public function reset(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'token' => 'required|string',
                'email' => 'required|email|exists:users,email',
                'password' => 'required|string|min:8|confirmed',
                'expires' => 'nullable|integer',
                'signature' => 'nullable|string',
            ]);

            // Se tiver assinatura, verificar
            if ($request->has('signature') && $request->has('expires')) {
                $expectedSignature = hash_hmac('sha256', $validated['token'] . '|' . $validated['email'] . '|' . $request->expires, config('app.key'));
                if (!hash_equals($expectedSignature, $request->signature)) {
                    return response()->json([
                        'message' => 'Link de redefinição inválido'
                    ], 400);
                }

                // Verificar expiração
                if (now()->timestamp > $request->expires) {
                    return response()->json([
                        'message' => 'Link de redefinição expirado'
                    ], 400);
                }
            }

            $status = Password::reset(
                $validated,
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));

                    $user->save();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'message' => 'Senha redefinida com sucesso'
                ], 200);
            }

            return response()->json([
                'message' => 'Erro ao redefinir senha',
                'error' => 'Token inválido ou expirado'
            ], 400);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao redefinir senha',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
