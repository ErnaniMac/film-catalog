<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        // Se não houver parâmetros de paginação, retornar todos (compatibilidade)
        if (!$request->has('per_page') && !$request->has('page')) {
            $permissions = Permission::all();
            return response()->json(['data' => $permissions], 200);
        }
        
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        
        $permissions = Permission::paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'data' => $permissions->items(),
            'meta' => [
                'current_page' => $permissions->currentPage(),
                'last_page' => $permissions->lastPage(),
                'per_page' => $permissions->perPage(),
                'total' => $permissions->total(),
            ]
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);

        $permission = Permission::create(['name' => $validated['name']]);

        return response()->json(['data' => $permission], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $permission = Permission::findOrFail($id);
        return response()->json(['data' => $permission], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $permission = Permission::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $id,
        ]);

        $permission->update($validated);

        return response()->json(['data' => $permission], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return response()->json(['message' => 'Permissão removida com sucesso'], 200);
    }
}
