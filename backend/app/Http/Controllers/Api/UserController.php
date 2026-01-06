<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        // Se não houver parâmetros de paginação, retornar todos (compatibilidade)
        if (!$request->has('per_page') && !$request->has('page')) {
            $users = User::with('roles', 'permissions')->get();
            return response()->json(['data' => $users], 200);
        }
        
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        
        $users = User::with('roles', 'permissions')
            ->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ]
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'roles' => 'nullable|array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(), // Usuários criados pelo admin já têm email verificado
        ]);

        if (isset($validated['roles'])) {
            $user->assignRole($validated['roles']);
        }

        $user->load('roles', 'permissions');

        return response()->json(['data' => $user], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $user = User::with('roles', 'permissions')->findOrFail($id);
        return response()->json(['data' => $user], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:8',
            'roles' => 'nullable|array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        $user->load('roles', 'permissions');

        return response()->json(['data' => $user], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Usuário removido com sucesso'], 200);
    }
}
