<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Favorite::where('user_id', Auth::id());

        // Filter by genre if provided
        if ($request->has('genre_id')) {
            $genreId = (int) $request->get('genre_id');
            $query->whereJsonContains('genre_ids', $genreId);
        }

        $favorites = $query->orderBy('created_at', 'desc')->get();

        return response()->json(['data' => $favorites], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tmdb_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'overview' => 'nullable|string',
            'poster' => 'nullable|string|max:500',
            'genre_ids' => 'nullable|array',
            'genre_ids.*' => 'integer',
        ]);

        // Check if favorite already exists
        $existing = Favorite::where('user_id', Auth::id())
            ->where('tmdb_id', $validated['tmdb_id'])
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Filme já está nos favoritos'], 409);
        }

        $favorite = Favorite::create([
            'user_id' => Auth::id(),
            ...$validated,
        ]);

        return response()->json(['data' => $favorite], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $favorite = Favorite::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $favorite->delete();

        return response()->json(['message' => 'Favorito removido com sucesso'], 200);
    }
}
