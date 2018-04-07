<?php

require 'vendor/autoload.php';

$pathToDonorsDir = './donors';
$files = scandir( $pathToDonorsDir );
foreach ( $files as $file )
{
    if( preg_match('%^(.*?)\.php$%is', $file, $match) )
    {
        $DonorClass = $match[1];
        if (!class_exists( $DonorClass ))
        {
            require_once $pathToDonorsDir. DIRECTORY_SEPARATOR . "{$DonorClass}.php";
        }
    }
}