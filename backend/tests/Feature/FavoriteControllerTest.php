<?php

namespace Tests\Feature;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_authenticated_user_can_list_favorites(): void
    {
        Favorite::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/favorites');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_authenticated_user_can_add_favorite(): void
    {
        $favoriteData = [
            'tmdb_id' => 123,
            'title' => 'Test Movie',
            'overview' => 'Test overview',
            'poster' => 'https://example.com/poster.jpg',
            'genre_ids' => [1, 2, 3],
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/favorites', $favoriteData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'tmdb_id',
                    'title',
                    'user_id',
                ],
            ]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'tmdb_id' => 123,
            'title' => 'Test Movie',
        ]);
    }

    public function test_authenticated_user_can_remove_favorite(): void
    {
        $favorite = Favorite::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson("/api/favorites/{$favorite->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('favorites', [
            'id' => $favorite->id,
        ]);
    }

    public function test_user_cannot_add_duplicate_favorite(): void
    {
        Favorite::factory()->create([
            'user_id' => $this->user->id,
            'tmdb_id' => 123,
        ]);

        $favoriteData = [
            'tmdb_id' => 123,
            'title' => 'Test Movie',
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/favorites', $favoriteData);

        $response->assertStatus(409);
    }

    public function test_unauthenticated_user_cannot_access_favorites(): void
    {
        $response = $this->getJson('/api/favorites');

        $response->assertStatus(401);
    }
}
