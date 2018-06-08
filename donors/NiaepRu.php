<?php

namespace ParseIt\Donor;

use ParseIt\ParserLogger;
use ParseIt\_String;
use ParseIt\nokogiri;
use ParseIt\simpleParser;

Class NiaepRu extends simpleParser {

    public $data = [];
    public $reload = [];
    public $cache = false;
    public $proxy = false;
    public $cookieFile = '';
    public $donor = 'NiaepRu';
    public $project = 'archive.niaep.ru';
    public $project_link = 'archive.niaep.ru';

    function __construct()
    {
        $this->cookieFile = __DIR__.'/cookie/'.$this->donor.'/'.$this->donor.'.txt';
    }

    public function getSources($var = [])
    {
        $sources = [];
        !isset($var['url']) ? $url = 'http://archive.niaep.ru/journalist/news/' : $url = $var['url'];
        $content = $this->loadUrl($url, $var);
        if ( !$content )
        {
            return $sources;
        }
        if (preg_match('%url:[\s]*\'([^\']+)\'%uis', $content, $match))
        {
            $uri = explode('/', $match[1]);
            $hash = $uri[count($uri)-1];
            $content = $this->loadUrl($url.$hash, $var);
            if ( !$content )
            {
                return $sources;
            }
            $js_string = $content;
            $js_string = html_entity_decode($js_string);
            $js_string = str_replace('"', '\"', $js_string);
            $js_string = str_replace('\'', '"', $js_string);
            $js_string = str_replace('title:', '"title":', $js_string);
            $js_string = str_replace('href:', '"href":', $js_string);
            $js_string = str_replace('date:', '"date":', $js_string);
            $js_string = str_replace('author:', '"author":', $js_string);
            $js_string = str_replace('picture:', '"picture":', $js_string);
            $js_string = str_replace('annotation:', '"annotation":', $js_string);
            $js_string = str_replace('new Date("', '"', $js_string);
            $js_string = str_replace('"),', '",', $js_string);
            $js_string = str_replace("\r", '', $js_string);
            $js_string = str_replace("\n", '', $js_string);
            $js_string = str_replace("	", '', $js_string);
            $js_string = str_replace("\"\"", '" "', $js_string);
            $sourceArray = json_decode($js_string);
            foreach ($sourceArray as $item)
            {
                $uri = explode('/', $item->href);
                $hash = $uri[count($uri)-1];
                $sources[] = [
                    'source' => $url,
                    'href' => $url.$hash,
                    'title' => $item->title,
                    'date' => date('Y-m-d H:i:s', strtotime($item->date)),
                    'author' => $item->author,
                    'pic_main' => !empty(trim($item->picture)) ? $this->fixUrl($item->picture) : '',
                ];
            }
        }

        return $sources;
    }

    public function getNewsDomMethod($url, $source = [])
    {
        $news = false;
        $source['referer'] = $url;
        $content = $this->loadUrl($url, $source);
        if ( !$content )
        {
            return $news;
        }
        $content = preg_replace('%<span>[^<]*</span>\s*<br[^>]*>\s*<br[^>]*>%uis','',$content);
//        $content = preg_replace('%<p[^>]*align="right"[^>]*>.*?</p>%uis','',$content);
        $nokogiri = new nokogiri($content);
        $text = trim(@$nokogiri->get('ul.raNewsList li')->toArray()[0]['__ref']->nodeValue);
        $imgs = @$nokogiri->get('ul.raNewsList li img')->toArray();
        $pic = '';
        foreach ($imgs as $img)
        {
            if( isset($img['src']) && !empty(trim($img['src'])) )
            {
                $pic .= $this->fixUrl($img['src'])."|";
            }
        }
        $news = [
            'source' => $url,
            'title' => $source['title'],
            'date' => $source['date'],
            'author' => $source['author'],
            'text' => $text,
            'pic_main' => $source['pic_main'],
            'pic' => trim($pic,'|'),
        ];

        return $news;
    }

    public function getNewsRegexMethod($url, $source = [])
    {
        $news = false;
        $source['referer'] = $url;
        $content = $this->loadUrl($url, $source);
        if ( !$content )
        {
            return $news;
        }
        $content = preg_replace('%<span>[^<]*</span>\s*<br[^>]*>\s*<br[^>]*>%uis','',$content);
        $nokogiri = new nokogiri($content);
        $imgs = @$nokogiri->get('ul.raNewsList li img')->toArray();
        $pic = '';
        foreach ($imgs as $img)
        {
            if( isset($img['src']) && !empty(trim($img['src'])) )
            {
                $pic .= $this->fixUrl($img['src'])."|";
            }
        }
        $text = '';
        if ( preg_match('%<ul[^>]*class="raNewsList[^>]*>.*?<li>(.*?)</li>%uis', $content, $match) )
        {
            $text = trim($match[1]);
            $text = str_replace('<br>', "\r\n", $text);
            $text = preg_replace('%<\!\-\-.*?\-\->%uis', "", $text);
            $text = preg_replace('%<style>.*?</style>%uis', "", $text);
            $text = preg_replace('%<[^>]*>%uis', "", $text);
            $text = preg_replace('%[\r\n]+%uis', "\r\n", $text);
            $text = trim($text);
            $text = html_entity_decode($text);
        }

        $news = [
            'source' => $url,
            'title' => $source['title'],
            'date' => $source['date'],
            'author' => $source['author'],
            'text' => $text,
            'pic_main' => $source['pic_main'],
            'pic' => trim($pic,'|'),
        ];

        return $news;
    }
}