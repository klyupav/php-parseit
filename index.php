<?php
/**
 * Created by PhpStorm.
 * User: klyupav
 * Date: 19.03.17
 * Time: 13:28
 */


require 'vendor/autoload.php';

//$pathToDonorsDir = './donors';
//$files = scandir( $pathToDonorsDir );
//foreach ( $files as $file )
//{
//    if( preg_match('%^(.*?)\.php$%is', $file, $match) )
//    {
//        $DonorClass = $match[1];
//        if (!class_exists( $DonorClass ))
//        {
//            require_once $pathToDonorsDir. DIRECTORY_SEPARATOR . "{$DonorClass}.php";
//        }
//    }
//}
use ParseIt\Parser;

$parser = new Parser();

$data = $parser->parsingDonor('https://www.watches2u.com/ladies-skagen-watches.html', 'Watches2uCom');
print_r($data);
die();