<?php

namespace ParseIt;

use ParseIt\ParserLogger;
use ParseIt\_String;
use ParseIt\nokogiri;
use ParseIt\simpleParser;

Class Watches2uCom extends simpleParser {

    public $cookieFile = '/cookie-Watches2uCom.txt';
    public $project_name = 'watches2u.com';
    public $project_link = 'watches2u.com';

    function __construct()
    {
        $this->cookieFile = __DIR__.'/cookie'.$this->cookieFile;
    }

    public function getSources($url = '')
    {
        //$this->sources[] = ['url' => 'https://securtv.ru/catalogue/sistemy-videonabludenia/videokamery/videokamery-vysokogo-razresenia/catalogue_MVK-M720-Ball-3-6.html', 'cookieFile' => $this->cookieFile, 'proxy' => true];
        //return $this->sources;

        $USD = $this->cookieFile.".USD";
        $RUB = $this->cookieFile.".RUB";

        if( preg_match('%\?%is', $url) ) {
            $url .= "&per_page=288";
        } else {
            $url .= "?per_page=288";
        }

        $content = $this->loadUrl($url, [
            'cookieFile' => $USD,
            'referer' => $url,
            //'sleep' => 1,
            //'proxy' => true,
        ]);

        $nikogiri = new nokogiri($content);
//        die('da');
        $currency = @$nikogiri->get("a.currency")->toArray()[0]['#text'];

        if( !preg_match('%USD%is', $currency) ) {
            $this->loadUrl('https://www.watches2u.com/interface/basket_currency.do', [
                'cookieFile' => $USD,
                'referer' => $url,
                'post' => [
                    'currency_id' => 3
                ],
            ]);
            $content = $this->loadUrl($url, [
                'cookieFile' => $USD,
                'referer' => $url,
                //'sleep' => 1,
                //'proxy' => true,
            ]);
            $nikogiri = new nokogiri($content);
            $currency = @$nikogiri->get("a.currency")->toArray()[0]['#text'];
        }

        $content_rub = $this->loadUrl($url, [
            'cookieFile' => $RUB,
            'referer' => $url,
            //'sleep' => 1,
            //'proxy' => true,
        ]);
        $nikogiri_rub = new nokogiri($content_rub);
        $currency = @$nikogiri_rub->get("a.currency")->toArray()[0]['#text'];

        if( !preg_match('%RUB%is', $currency) ) {
            $this->loadUrl('https://www.watches2u.com/interface/basket_currency.do', [
                'cookieFile' => $RUB,
                'referer' => $url,
                'post' => [
                    'currency_id' => 15
                ],
            ]);
            $content_rub = $this->loadUrl($url, [
                'cookieFile' => $RUB,
                'referer' => $url,
                //'sleep' => 1,
                //'proxy' => true,
            ]);
            $nikogiri_rub = new nokogiri($content_rub);
            $currency = @$nikogiri_rub->get("a.currency")->toArray()[0]['#text'];
        }
        $prices_rub = $nikogiri_rub->get(".xcomponent_products_medium_price")->toArray();

        $prices = $nikogiri->get(".xcomponent_products_medium_price")->toArray();
        $links = $nikogiri->get(".xcomponent_products_medium_link")->toArray();
        $tax = _String::parseNumber(@$prices[0]['span'][0]['#text']) / _String::parseNumber(@$prices_rub[0]['span'][0]['#text']);
        foreach ( $links as $k => $link) {
            $price = _String::parseNumber($prices[$k]['span'][0]['#text']);
            $this->sources[] = [
                'url' => $link['href'],
                'from' => $url,
                'price_usd' => $price,
                'price_rub' => round($price / $tax, 2),
                'cookieFile' => $USD,
                'referer' => $url,
            ];
        }

        if( preg_match('%<span>Page \d+ of (\d+)</span>%is', $content, $last_page) ) {
            for( $i = 1; $i < $last_page[1]; $i++ ) {
                unset($content);
                $url_second = "{$url}&page_num={$i}";
                $content = $this->loadUrl($url_second, [
                    'cookieFile' => $USD,
                    'referer' => $url,
                    //'sleep' => 1,
                    //'proxy' => true,
                ]);
                $nikogiri = new nokogiri($content);
                $prices = $nikogiri->get(".xcomponent_products_medium_price")->toArray();
                $links = $nikogiri->get(".xcomponent_products_medium_link")->toArray();
                foreach ( $links as $k => $link) {
                    $price = _String::parseNumber(@$prices[$k]['span'][0]['#text']);
                    $this->sources[] = [
                        'url' => $link['href'],
                        'from' => $url_second,
                        'price_usd' => $price,
                        'price_rub' => round($price / $tax, 2),
                        'cookieFile' => $USD,
                        'referer' => $url_second,
                    ];
                }
            }
        }
        //print_r($this->sources);//die();
        return $this->sources;
    }

    public function onSourceLoaded($content, $url, $source)
    {
        //$content = iconv('UTF-8', 'UTF-8//IGNORE', $content);
        //$content = ParseItHelpers::fixEncoding($content);
        if ( !$content )
        {
            ParserLogger::logToFile("Url {$url} return blank page", 'error');
            return;
        }
        $nikogiri = new nokogiri($content);

        $description = @$nikogiri->get(".page_products_details5_top_description")->toArray()[0]['__ref']->nodeValue;
        $specs = $nikogiri->get(".page_products_details5_bottom_pane_specs_col table tr")->toArray();
        foreach ( $specs as $spec ) {
            $title = trim(trim($spec['td'][0]['__ref']->nodeValue), ":");
            $attributes[$title] = trim($spec['td'][1]['__ref']->nodeValue);
        }

        preg_match_all('%w2js_display_products_details_img_register\(\'([^\']+)\'%is', $content, $images);
        $images = array_unique($images[1]);

        $sku = @$nikogiri->get("span[itemprop=sku]")->toArray()[0]['#text'];
        $brand = @$nikogiri->get("span[itemprop=brand]")->toArray()[0]['#text'];
        $product_name = $nikogiri->get("span[itemprop=name]")->toArray()[0]['#text'][0];
        if( !$stockin = @$nikogiri->get("div.stockin")->toArray()[0]['#text'] )
        {
            $stockin = @$nikogiri->get("div.stockout")->toArray()[0]['#text'];
        }

        $data = [
            'source' => $url,
            'manufacturer' => $brand,
            'product_name' => $product_name,
            'sku' => $sku,
            'stockin' => $stockin,
            'price_rub' => $source['price_rub'],
            'price_usd' => $source['price_usd'],
            'description' => $description,
            'main_image' => $images[0],
            'gallery' => serialize($images),
            'product_attributes' => serialize($attributes),
            'hash' => md5($url),
        ];
//        print_r($data);//die();
        return $data;
    }
}