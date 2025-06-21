<?php

namespace ClarionApp\RssTorrents;

class RssParser
{
    private $skip = array(".", "..");

    private function getClasses()
    {
        $classes = array();
        $dir = scandir(__DIR__."/Feeds");
        foreach($dir as $file)
        {
            if(in_array($file, $this->skip)) continue;
            if(stripos($file, ".php") === false) continue;
            if(stripos($file, ".swp") !== false) continue;

            $classname = "ClarionApp\\RssTorrents\\Feeds\\".str_replace(".php", "", $file);
            array_push($classes, $classname);
        }
        return $classes;
    }

    public function getTorrents($url)
    {
        // Match feed with a class
        $class = null;

        foreach($this->getClasses() as $classname)
        {
            if($classname::checkFeedURL($url))
            {
                $class = new $classname($url);
                break;
            }
        }
       if($class == null) throw new \Exception('Could not find a parser for feed: '.$url);

        return $class->getTorrents();
    }

    public function getURLs()
    {
        $urls = array();

        foreach($this->getClasses() as $classname)
        {
            $urls = array_merge($urls, $classname::getURLs());
        }

        return $urls;
    }
}
