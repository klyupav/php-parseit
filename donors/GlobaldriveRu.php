<?php

namespace ParseIt\Donor;

use ParseIt\ParserLogger;
use ParseIt\_String;
use ParseIt\nokogiri;
use ParseIt\simpleParser;

Class GlobaldriveRu extends simpleParser {

    public $data = [];
    public $reload = [];
    public $cache = false;
    public $proxy = false;
    public $cookieFile = '';
    public $donor = 'GlobaldriveRu';
    public $project = 'globaldrive.ru';
    public $project_link = 'globaldrive.ru';

    function __construct()
    {
        $this->cookieFile = __DIR__.'/cookie/'.$this->donor.'/'.$this->donor.'.txt';
    }

    public function getSources($var = [])
    {
        $sources = [];
        $content = $this->loadUrl(@$var['url'], $var);
        if ( !$content )
        {
            return $sources;
        }
        $nokogiri = new nokogiri($content);
        $links = @$nokogiri->get("a.product-title")->toArray();
        foreach ( $links as $link )
        {
            $hash = md5(@$link['href']);
            $sources[]= [
                'url' => @$link['href'],
            ];
        }
        if ( $next = @$nokogiri->get("a.ty-pagination__next")->toArray() )
        {
            foreach ( $this->getSources(['url' => @$next[0]['href']]) as $source )
            {
                $sources[] = $source;
            }
        }

        return $sources;
    }

    public function getProductInfo($url, $source = [])
    {
        $product = false;
        $source['referer'] = $url;
        $content = $this->loadUrl($url, $source);
        if ( !$content )
        {
            return $product;
        }
        $nikogiri = new nokogiri($content);
        $name = trim(@$nikogiri->get("h1.ty-product-block-title")->toArray()[0]['__ref']->nodeValue);
        $price = _String::parseNumber(preg_replace('%\s+%uis', '', @$nikogiri->get("span.ty-price-num")->toArray()[0]['__ref']->nodeValue));
        $old_price = _String::parseNumber(preg_replace('%\s+%uis', '', @$nikogiri->get("span.ty-strike span.ty-list-price")->toArray()[0]['__ref']->nodeValue));
        if ( $stock = $nikogiri->get("span.ty-qty-in-stock")->toArray() )
        {
            $stock = trim($stock[0]['__ref']->nodeValue);
        }
        elseif ( $stock = $nikogiri->get("span.ty-qty-out-of-stock")->toArray() )
        {
            $stock = trim($stock[0]['__ref']->nodeValue);
        }
        if ( $models = $nikogiri->get(".ty-features-list a")->toArray() )
        {
            foreach ( $models as $model )
            {
                $brands[] = $model['__ref']->nodeValue;
            }
            $model = implode(', ', $brands);
//            $model = trim($model);
//            $model = trim($model, ',');
        }
        $gallery = $nikogiri->get("div.ty-product-img img.cm-image")->toArray();
        $images = [];
        foreach ($gallery as $item)
        {
            $images[] = $item['src'];
        }
        $images = implode(';', $images);
        $video = @$nikogiri->get(".product-video")->toArray()[0]['src'];
        $description = trim(@$nikogiri->get("#content_description")->toArray()[0]['__ref']->nodeValue);
        if ( preg_match( '%<div id="content_description"[^>]+>[^<]+<div[^>]*>(.*?)<\/div%uis', $content, $match ) )
        {
            $description = trim($match[1]);
        }
        $labels = @$nikogiri->get(".ty-product-feature__label")->toArray();
        $values = @$nikogiri->get(".ty-product-feature__value")->toArray();
        $characters = [];
        foreach ( $labels as $k => $label )
        {
            $characters[] = [
                'name' => trim($label['__ref']->nodeValue, ':'),
                'value' => @$values[$k]['__ref']->nodeValue,
            ];
        }

        $product = [
            'source' => @$url,
            'name' => $name,
            'price' => @$price,
            'old_price' => @$old_price,
            'stock' => @$stock,
            'model' => @$model,
            'images' => @$images,
            'video' => @$video,
            'description' => @$description,
            'characters' => @$characters,
        ];

        return $product;
    }
}