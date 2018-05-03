<?php

require 'autoload.php';

//http://www.atomstroyexport.ru/journalists/press/

$atom = new \ParseIt\Donor\AtomstroyexportRu();
$atom_s = $atom->getSources();
$fp = fopen('1.atomstroyexport.csv','w');
fwrite($fp, "№,источник,заголовок,дата,текст,фотки,основная фотка\r\n");
foreach ( $atom_s as $k => $source)
{
    $N = $k+1;
//    $news = $atom->getNewsDomMethod($source['href'], $source);
//    $source['href'] = 'http://www.atomstroyexport.ru/journalists/press/e4506780482cac299477ff329052ef2a';
    $news = $atom->getNewsRegexMethod($source['href'], $source);
//    print_r($news);die();
    $src = $news['source'];
    $title = str_replace('"', '""', $news['title']);
    $date = str_replace('"', '""', $news['date']);
    $text = str_replace('"', '""', $news['text']);
    $pic = str_replace('"', '""', $news['pic']);
    $pic_main = str_replace('"', '""', $news['pic_main']);
    $csvstr = "{$N},\"{$src}\",\"{$title}\",\"{$date}\",\"{$text}\",\"{$pic}\",\"{$pic_main}\"";
    $csvstr = preg_replace("%(\r\n|\n\r|\r|\n)%uis", '<br>', $csvstr);
    fwrite($fp, $csvstr."\r\n");
//    break;
//    print_r($csvstr);die();
//    die();
}
//die();
fclose($fp);
//http://archive.niaep.ru/journalist/news/

$niaep = new \ParseIt\Donor\NiaepRu();
$niaep_s = $niaep->getSources();
$fp = fopen('2.niaep.csv','w');
fwrite($fp, "№,источник,заголовок,дата,текст,фотки,основная фотка\r\n");
foreach ( $niaep_s as $k => $source)
{
    $N = $k+1;
//    $news = $niaep->getNewsDomMethod($source['href'], $source);
//    $source['href'] = 'http://www.atomstroyexport.ru/journalists/press/3e4392004d20e8deb331bf8ad73eecf7';
    $news = $niaep->getNewsRegexMethod($source['href'], $source);
    $src = $news['source'];
    $title = str_replace('"', '""', $news['title']);
    $date = str_replace('"', '""', $news['date']);
    $text = str_replace('"', '""', $news['text']);
    $pic = str_replace('"', '""', $news['pic']);
    $pic_main = str_replace('"', '""', $news['pic_main']);
    $csvstr = "{$N},\"{$src}\",\"{$title}\",\"{$date}\",\"{$text}\",\"{$pic}\",\"{$pic_main}\"";
    $csvstr = preg_replace("%(\r\n|\n\r|\r|\n)%uis", '<br>', $csvstr);
    fwrite($fp, $csvstr."\r\n");
//    break;
//    print_r($csvstr);die();
//    die();
}
fclose($fp);

//http://archive.aep.ru/wps/wcm/connect/aep/main/presscenter/nnews/

$aep = new \ParseIt\Donor\AepRu();
$aep_s = $aep->getSources();
$fp = fopen('3.aep.csv','w');
fwrite($fp, "№,источник,заголовок,дата,текст,фотки,основная фотка\r\n");
foreach ( $aep_s as $k => $source)
{
    $N = $k+1;
//    $news = $aep->getNewsDomMethod($source['href'], $source);
//    $source['href'] = 'http://archive.aep.ru/wps/wcm/connect/aep/main/presscenter/nnews/489news';
    $news = $aep->getNewsRegexMethod($source['href'], $source);
    $src = $news['source'];
    $title = str_replace('"', '""', $news['title']);
    $date = str_replace('"', '""', $news['date']);
    $text = str_replace('"', '""', $news['text']);
    $pic = str_replace('"', '""', $news['pic']);
    $pic_main = str_replace('"', '""', $news['pic_main']);
    $csvstr = "{$N},\"{$src}\",\"{$title}\",\"{$date}\",\"{$text}\",\"{$pic}\",\"{$pic_main}\"";
    $csvstr = preg_replace("%(\r\n|\n\r|\r|\n)%uis", '<br>', $csvstr);
    fwrite($fp, $csvstr."\r\n");
//    break;
//    print_r($csvstr);die();
//    die();
}
fclose($fp);