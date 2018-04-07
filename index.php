<?php

require 'autoload.php';

Use ParseIt\Watches2uCom;

$watches = new Watches2uCom();

$sources = $watches->getSources('https://www.watches2u.com/ladies-skagen-watches.html');

print_r($sources);