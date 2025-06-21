<?php

namespace ClarionApp\RssTorrents\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use ClarionApp\RssTorrents\RssParser;
use ClarionApp\RssTorrents\Models\Series;
use ClarionApp\RssTorrents\Models\Episode;
use ClarionApp\RssTorrents\HttpApiCall;

class CheckFeeds extends Command
{
    /**
     * The name and signature of the console command.
     * 
     * @var string
     */
    protected $signature = 'feeds:check';

    /**
     * The console command description.
     * 
     * @var string
     */
    protected $description = 'Check RSS feeds for new torrents and update the database';

    /**
     * Create new command instance.
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Checking RSS feeds for new torrents...');

        $parser = new RssParser();
        $urls = $parser->getURLs();
        foreach($urls as $url)
        {
            $feed = $parser->getTorrents($url);
            if (empty($feed)) {
                $this->warn("No torrents found for feed: $url");
                continue;
            }
            $names = [];
            foreach($feed as $name=>$series)
            {
                $names[] = $name;
            }

            $series = Series::whereIn('name', $names)->where('feed_url', $url)->get();
            
            // Get existing series names for comparison
            $existingNames = $series->pluck('name')->toArray();
            
            // Find names that don't exist in the database
            $newNames = array_diff($names, $existingNames);
            
            // Create new Series for names that don't exist
            foreach ($newNames as $newName) {
                $newSeries = new Series();
                $newSeries->name = $newName;
                $newSeries->feed_url = $url;
                $newSeries->title = $feed[$newName]['title'] ?? $newName;
                $newSeries->save();
                
                $this->info("Created new series: {$newName}");
                Log::info("Created new series: {$newName}");
            }

            // update the timestamp on the existing series
            foreach ($series as $existingSeries) {
                $existingSeries->updated_at = now();
                $existingSeries->save();
                $this->info("Updated existing series: {$existingSeries->name}");
            }

            $series = Series::whereIn('name', $names)->where('feed_url', $url)->where('subscribed', true)->with('episodes')->get();
            foreach($series as $show)
            {
                foreach($feed[$show->name]['episodes'] as $episode=>$torrents)
                {
                    $downloaded = false;
                    foreach($torrents as $torrent)
                    {
                        if($downloaded) continue;
                        if(($torrent['resolution'] != "720p") && ($torrent['resolution'] != "1080p")) continue;

                        $downloaded = true;
                        // Check if the episode already exists
                        $existingEpisode = Episode::where('series_id', $show->id)
                            ->where('episode', $episode)->first();

                        if (!$existingEpisode)
                        {
                            $newEpisode = new Episode();
                            $newEpisode->series_id = $show->id;
                            $newEpisode->episode = $episode;
                            $newEpisode->save();

                            $httpCall = new HttpApiCall("/api/clarion-app/download-manager/torrents");
                            $result = $httpCall->post(['magnetURI' => $torrent['magnetURI'], 'name' => $show->title . " - Episode " . $episode]);
                        }
                    }
                }
            }
        }

        return 0;
    }
}
