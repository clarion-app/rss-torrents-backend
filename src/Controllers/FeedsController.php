<?php

namespace ClarionApp\RssTorrents\Controllers;

use ClarionApp\RssTorrents\RssParser;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class FeedsController extends Controller
{
    /**
     * Display an array of URLs from RSS feeds
     *
     * @return JsonResponse
     */
    public function getUrls(): JsonResponse
    {
        try {
            $parser = new RssParser();
            $urls = $parser->getURLs();

            return response()->json($urls);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get torrents from a specific RSS feed URL
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTorrents(Request $request): JsonResponse
    {
        try {
            $feedUrl = $request->query('url');
            
            if (empty($feedUrl)) {
                return response()->json(['error' => 'Feed URL is required'], 400);
            }

            $parser = new RssParser();
            $torrents = $parser->getTorrents($feedUrl);

            return response()->json($torrents);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
