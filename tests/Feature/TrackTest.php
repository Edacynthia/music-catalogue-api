<?php

namespace Tests\Feature;

use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TrackTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_track(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tracks', [
            'title' => 'African Queen',
            'artist_name' => '2Baba',
            'genre' => 'Afrobeats',
            'duration' => 240,
            'release_date' => '2025-05-10',
            'publication_status' => 'published',
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'African Queen')
            ->assertJsonPath('data.artist_name', '2Baba');

        $this->assertDatabaseHas('tracks', [
            'user_id' => $user->id,
            'title' => 'African Queen',
            'artist_name' => '2Baba',
        ]);
    }

    public function test_track_creation_fails_with_invalid_data(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tracks', [
            'title' => '',
            'artist_name' => '',
            'genre' => '',
            'duration' => 0,
            'release_date' => 'not-a-date',
            'publication_status' => 'invalid-status',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'title',
                'artist_name',
                'genre',
                'duration',
                'release_date',
                'publication_status',
            ]);
    }

    public function test_user_cannot_view_another_users_track(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $track = Track::factory()->create([
            'user_id' => $owner->id,
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->getJson("/api/tracks/{$track->id}");

        $response
            ->assertStatus(403)
            ->assertJson([
                'message' => 'This action is unauthorized.',
            ]);
    }

    public function test_user_can_view_their_own_track(): void
    {
        $user = User::factory()->create();

        $track = Track::factory()->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/tracks/{$track->id}");

        $response
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $track->id);
    }

    public function test_user_can_update_their_own_track(): void
    {
        $user = User::factory()->create();

        $track = Track::factory()->create([
            'user_id' => $user->id,
            'title' => 'Old Title',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/tracks/{$track->id}", [
            'title' => 'New Title',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'New Title');

        $this->assertDatabaseHas('tracks', [
            'id' => $track->id,
            'title' => 'New Title',
        ]);
    }

    public function test_user_cannot_update_another_users_track(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $track = Track::factory()->create([
            'user_id' => $owner->id,
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->putJson("/api/tracks/{$track->id}", [
            'title' => 'Hacked Title',
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('tracks', [
            'id' => $track->id,
            'title' => $track->title,
        ]);
    }

    public function test_user_can_delete_their_own_track(): void
    {
        $user = User::factory()->create();

        $track = Track::factory()->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/tracks/{$track->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Track deleted successfully.',
            ]);

        $this->assertDatabaseMissing('tracks', [
            'id' => $track->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_track(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $track = Track::factory()->create([
            'user_id' => $owner->id,
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/tracks/{$track->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('tracks', [
            'id' => $track->id,
        ]);
    }

   public function test_user_can_filter_tracks_by_genre(): void
{
    $user = User::factory()->create();

    Track::factory()->create([
        'user_id' => $user->id,
        'genre' => 'Afrobeats',
    ]);

    Track::factory()->create([
        'user_id' => $user->id,
        'genre' => 'Hip Hop',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/tracks?genre=Afrobeats');

    $response
        ->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.genre', 'Afrobeats');
}

   public function test_user_can_search_tracks_by_title_or_artist(): void
{
    $user = User::factory()->create();

    Track::factory()->create([
        'user_id' => $user->id,
        'title' => 'African Queen',
        'artist_name' => '2Baba',
    ]);

    Track::factory()->create([
        'user_id' => $user->id,
        'title' => 'Another Song',
        'artist_name' => 'Another Artist',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/tracks?search=Queen');

    $response
        ->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'African Queen');
}
public function test_user_can_paginate_their_tracks(): void
{
    $user = User::factory()->create();

    Track::factory()
        ->count(12)
        ->create([
            'user_id' => $user->id,
        ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/tracks');

    $response
        ->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('meta.per_page', 10)
        ->assertJsonPath('meta.total', 12)
        ->assertJsonCount(10, 'data');
}
}