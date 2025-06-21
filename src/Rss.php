<?php

namespace ClarionApp\RssTorrents;

use \Feeds;

abstract class Rss
{
    protected $feed;
    protected $items;
    protected $torrents = array();

    public function __construct($url)
    {
        // Invalidate caching
        if(stripos($url, "?") === false) $url.= "?";
        else $url.= "&";
        $url.= rand(0, 1000000);

        $this->feed = Feeds::make($url);
        $this->items = $this->feed->get_items();
    }

    public static function checkFeedURL($url) 
    {
        if(in_array($url, static::$valid_feeds)) return true;

        return false;
    }

    public static function getURLs()
    {
        return static::$valid_feeds;
    }

    abstract public function getTorrents();
}
