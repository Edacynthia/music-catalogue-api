<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrackRequest;
use App\Http\Requests\UpdateTrackRequest;
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
   public function store(StoreTrackRequest $request): JsonResponse
{
    $track = $request->user()->tracks()->create(
        $request->validated()
    );

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
    public function update(
    UpdateTrackRequest $request,
    Track $track
): JsonResponse {
    Gate::authorize('update', $track);

    $track->update($request->validated());

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