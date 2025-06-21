<?php

namespace ClarionApp\RssTorrents\Feeds;

use ClarionApp\RssTorrents\Rss;

class NyaaSubsPlease extends Rss
{
    public static $valid_feeds = array("https://nyaa.si/?page=rss&q=[SubsPlease]");

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

            $title_parts = explode(" - ", $title);
            if(count($title_parts) == 1) continue;

            $title = str_replace("[1080p] ", "", $title_parts[0]);
            $title = str_replace("[SubsPlease] ", "", $title);
            $title = str_replace("[Lazy] ", "", $title);

            switch(count($title_parts))
            {
                case 4:
                    $title.= " - ".$title_parts[1]." - ".$title_parts[2];
                    $episode_parts = explode(" ", $title_parts[3]);
                    $episode = $episode_parts[0];
                    break;
                case 3:
                    $title.= " - ".$title_parts[1];
                    $episode_parts = explode(" ", $title_parts[2]);
                    $episode = $episode_parts[0];
                    break;
                default:
                    $episode_parts = explode(" ", $title_parts[1]);
                    $episode = $episode_parts[0];
                    break;
            }

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
