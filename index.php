<?php
require 'autoload.php';
Use ParseIt\Watches2uCom;

$config = new \Doctrine\DBAL\Configuration();

$connectionParams = array(
    'dbname' => 'vladmir_globaldrive',
    'user' => 'kotopec',
    'password' => '30031990',
    'host' => 'localhost',
    'driver' => 'pdo_mysql',
);

$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

$characters = $conn->query("SELECT * FROM characters");

while ($row = $characters->fetch()) {
    print_r($row);
}
die();
$watches = new Watches2uCom();

$sources = $watches->getSources('https://www.watches2u.com/ladies-skagen-watches.html');


print_r($sources);