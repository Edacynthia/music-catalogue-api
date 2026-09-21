<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TrackController extends Controller
{
    /**
     * Display a paginated list of the authenticated user's tracks.
     */
   public function index(Request $request): JsonResponse
{
    $request->validate([
    'genre' => ['sometimes', 'string', 'max:100'],
    'publication_status' => ['sometimes', 'in:draft,published'],
    'search' => ['sometimes', 'string', 'max:255'],
    'page' => ['sometimes', 'integer', 'min:1'],
]);

    $query = $request->user()->tracks();

    if ($request->filled('genre')) {
        $query->where('genre', $request->input('genre'));
    }

    if ($request->filled('publication_status')) {
        $query->where(
            'publication_status',
            $request->input('publication_status')
        );
    }

    if ($request->filled('search')) {
        $search = $request->input('search');

        $query->where(function ($query) use ($search) {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('artist_name', 'like', "%{$search}%");
        });
    }

    $tracks = $query
        ->latest()
        ->paginate(10)
        ->withQueryString();

    return response()->json([
        'success' => true,
        'data' => $tracks,
    ]);
}

    /**
     * Store a newly created track.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'artist_name' => ['required', 'string', 'max:255'],
            'genre' => ['required', 'string', 'max:100'],
            'duration' => ['required', 'integer', 'min:1'],
            'release_date' => ['required', 'date'],
            'publication_status' => ['required', 'in:draft,published'],
        ]);

        $track = $request->user()->tracks()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Track created successfully.',
            'data' => $track,
        ], 201);
    }

    /**
     * Display a specific track belonging to the authenticated user.
     */
    public function show(Request $request, Track $track): JsonResponse
    {
        Gate::authorize('view', $track);

        return response()->json([
            'success' => true,
            'data' => $track,
        ]);
    }

    /**
     * Update a specific track belonging to the authenticated user.
     */
    public function update(Request $request, Track $track): JsonResponse
    {
       Gate::authorize('update', $track);

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'artist_name' => ['sometimes', 'required', 'string', 'max:255'],
            'genre' => ['sometimes', 'required', 'string', 'max:100'],
            'duration' => ['sometimes', 'required', 'integer', 'min:1'],
            'release_date' => ['sometimes', 'required', 'date'],
            'publication_status' => ['sometimes', 'required', 'in:draft,published'],
        ]);

        $track->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Track updated successfully.',
            'data' => $track->fresh(),
        ]);
    }

    /**
     * Remove a specific track belonging to the authenticated user.
     */
    public function destroy(Request $request, Track $track): JsonResponse
    {
         Gate::authorize('delete', $track);

        $track->delete();

        return response()->json([
            'success' => true,
            'message' => 'Track deleted successfully.',
        ]);
    }
}