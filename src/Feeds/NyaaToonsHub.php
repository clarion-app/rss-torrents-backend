<?php

namespace ClarionApp\RssTorrents\Feeds;

use ClarionApp\RssTorrents\Rss;

class NyaaToonsHub extends Rss
{
    public static $valid_feeds = array("https://nyaa.si/?page=rss&q=[ToonsHub]");

    public function getTorrents()
    {
        if(count($this->torrents)) return $this->torrents;

        foreach($this->items as $item)
        {
            $title = $item->get_title();
            $resolution = "SD";
            if(stripos($title, "720p") !== false) $resolution = "720p";
            if(stripos($title, "1080p") !== false) $resolution = "1080p";

            if($resolution == "720p") continue;

            // Parse titles like:
            // [ToonsHub] Series Name S01E03 1080p ...
            if(!preg_match('/^\[ToonsHub\]\s*(.*?)\s+(S\d{2}E\d{2,3})\b/i', $title, $matches)) continue;

            $title = trim($matches[1]);
            $title = str_replace("[1080p] ", "", $title);
            $title = str_replace("[SubsPlease] ", "", $title);
            $title = str_replace("[Lazy] ", "", $title);
            $episode = strtoupper(trim($matches[2]));

            $name = strtolower($title);

            if(!isset($this->torrents[$name])) $this->torrents[$name] = array();
            if(!isset($this->torrents[$name]['title'])) $this->torrents[$name]['title'] = $title;
            if(!isset($this->torrents[$name]['episodes'])) $this->torrents[$name]['episodes'] = array();

            if(!isset($this->torrents[$name]['episodes'][$episode])) $this->torrents[$name]['episodes'][$episode] = array();
            array_push($this->torrents[$name]['episodes'][$episode],
                array('magnetURI'=>$item->get_permalink(), 'resolution'=>$resolution)
            );
        }

        return $this->torrents;
    }
}
