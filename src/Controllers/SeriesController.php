<?php

namespace ClarionApp\RssTorrents\Controllers;

use ClarionApp\RssTorrents\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class SeriesController extends Controller
{
    /**
     * Display a listing of the series.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Series::query();

        // Include the most recent episode for each series
        $query->with(['episodes' => function($q) {
            $q->latest('created_at')->limit(1);
        }]);

        // Filter by subscription status if provided
        if ($request->has('subscribed')) {
            $subscribed = filter_var($request->subscribed, FILTER_VALIDATE_BOOLEAN);
            $query = $subscribed ? $query->subscribed() : $query->unsubscribed();
        }

        // Filter by feed URL if provided
        if ($request->has('feed_url')) {
            $query->where('feed_url', $request->feed_url);
        }

        // Search by name or title if provided
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $series = $query->orderBy('name')->paginate(15);

        return response()->json($series);
    }

    /**
     * Store a newly created series in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'feed_url' => 'required|url|max:255',
            'subscribed' => 'boolean',
        ]);

        $validated['name'] = strtolower($validated['title']);
        // Check for duplicate name/feed_url combination
        $existing = Series::where('name', $validated['name'])
                         ->where('feed_url', $validated['feed_url'])
                         ->first();

        if ($existing) {
            return response()->json([
                'message' => 'A series with this name already exists for this feed URL.',
                'errors' => [
                    'name' => ['This combination of name and feed URL already exists.']
                ]
            ], 422);
        }

        $series = Series::create($validated);

        return response()->json($series, 201);
    }

    /**
     * Display the specified series.
     */
    public function show(Series $series): JsonResponse
    {
        // Load the most recent episode for this series
        $series->load(['episodes' => function($q) {
            $q->latest('created_at')->limit(1);
        }]);

        return response()->json($series);
    }

    /**
     * Update the specified series in storage.
     */
    public function update(Request $request, Series $series): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'title' => 'sometimes|required|string|max:255',
            'feed_url' => 'sometimes|required|url|max:255',
            'subscribed' => 'sometimes|boolean',
        ]);

        // Check for duplicate name/feed_url combination if either is being updated
        if (isset($validated['name']) || isset($validated['feed_url'])) {
            $name = $validated['name'] ?? $series->name;
            $feedUrl = $validated['feed_url'] ?? $series->feed_url;
            
            $existing = Series::where('name', $name)
                             ->where('feed_url', $feedUrl)
                             ->where('id', '!=', $series->id)
                             ->first();

            if ($existing) {
                return response()->json([
                    'message' => 'A series with this name already exists for this feed URL.',
                    'errors' => [
                        'name' => ['This combination of name and feed URL already exists.']
                    ]
                ], 422);
            }
        }

        $series->update($validated);

        return response()->json($series);
    }

    /**
     * Remove the specified series from storage.
     */
    public function destroy(Series $series): JsonResponse
    {
        $series->delete();

        return response()->json([
            'message' => 'Series deleted successfully.'
        ]);
    }

    /**
     * Toggle subscription status for the specified series.
     */
    public function toggleSubscription($id): JsonResponse
    {
        $series = Series::findOrFail($id);
        $series->update([
            'subscribed' => !$series->subscribed
        ]);

        return response()->json([
            'message' => $series->subscribed ? 'Subscribed to series.' : 'Unsubscribed from series.',
            'series' => $series
        ]);
    }

    /**
     * Get all subscribed series.
     */
    public function subscribed(): JsonResponse
    {
        $series = Series::subscribed()
                       ->with(['episodes' => function($q) {
                           $q->latest('created_at')->limit(1);
                       }])
                       ->orderBy('name')
                       ->get();

        return response()->json($series);
    }

    /**
     * Bulk update subscription status for multiple series.
     */
    public function bulkUpdateSubscription(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'series_ids' => 'required|array',
            'series_ids.*' => 'required|string|exists:rss_torrents_series,id',
            'subscribed' => 'required|boolean',
        ]);

        $updated = Series::whereIn('id', $validated['series_ids'])
                        ->update(['subscribed' => $validated['subscribed']]);

        return response()->json([
            'message' => "Updated subscription status for {$updated} series.",
            'updated_count' => $updated
        ]);
    }
}
