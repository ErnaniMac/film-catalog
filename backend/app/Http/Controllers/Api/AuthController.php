<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle login request
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        // Verificar se o e-mail foi verificado
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Por favor, verifique seu e-mail antes de fazer login.',
                'email_verified' => false,
                'user_id' => $user->id,
            ], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;
        $user->load('roles', 'permissions');

        return response()->json([
            'token' => $token,
            'user' => $user,
        ], 200);
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso'], 200);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('roles', 'permissions');

        return response()->json($user, 200);
    }

    /**
     * Update user profile (name)
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = $request->user();
        $user->update(['name' => $validated['name']]);
        $user->load('roles', 'permissions');

        return response()->json([
            'message' => 'Perfil atualizado com sucesso',
            'user' => $user
        ], 200);
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        // Verificar senha atual
        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Senha atual incorreta'
            ], 422);
        }

        // Atualizar senha
        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        return response()->json([
            'message' => 'Senha atualizada com sucesso'
        ], 200);
    }
}
