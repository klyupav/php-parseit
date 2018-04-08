<?php

require 'autoload.php';

Use ParseIt\Donor\GlobaldriveRu;

$config = new \Doctrine\DBAL\Configuration();
$connectionParams = array(
    'dbname' => 'vladmir_globaldrive',
    'user' => 'kotopec',
    'password' => '30031990',
    'host' => 'localhost',
    'driver' => 'pdo_mysql',
);
$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

if ( isset($_GET['update']) )
{
    $conn->query("UPDATE `categories` SET `status`=0");
    die('Status updated. Remove "?update" from url');
}

$donor = new GlobaldriveRu();
$cats = $conn->query("SELECT * FROM categories WHERE status=0 LIMIT 1");
while ($cat = $cats->fetch())
{
    $conn->update('categories', ['status' => 1,], ['id' => $cat['id']]);
    $category = $cat['link'];
    $category_id = $cat['id'];
    $sources = $donor->getSources(['url' => $category]);

    foreach ( $sources as $source )
    {
        $product = $donor->getProductInfo( $source['url'] );
        try
        {
            $results = $conn->query("SELECT * FROM products WHERE source='{$product['source']}'");
            if ( $results->rowCount() > 0 )
            {
                $product_id = $results->fetch()['id'];
                $conn->update('products', [
                    'source' => $product['source'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'old_price' => $product['old_price'],
                    'stock' => $product['stock'],
                    'model' => $product['model'],
                    'images' => $product['images'],
                    'video' => $product['video'],
                    'description' => $product['description'],
                    'category_id' => $category_id,
                ], ['id' => $product_id]);
            }
            else
            {
                $conn->insert('products', [
                    'source' => $product['source'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'old_price' => $product['old_price'],
                    'stock' => $product['stock'],
                    'model' => $product['model'],
                    'images' => $product['images'],
                    'video' => $product['video'],
                    'description' => $product['description'],
                    'category_id' => $category_id,
                ]);
                $product_id = $conn->lastInsertId();
                foreach ( $product['characters'] as $character )
                {
                    $search = $conn->query("SELECT * FROM characters WHERE name='{$character['name']}' AND product_id='{$product_id}'");
                    if ( $search->rowCount() > 0 )
                    {
                        $conn->update('characters', [
                            'value' => $character['value'],
                        ],[
                            'name' => $character['name'],
                            'product_id' => $product_id,
                        ]);
                    }
                    else
                    {
                        $conn->insert('characters', [
                            'name' => $character['name'],
                            'value' => $character['value'],
                            'product_id' => $product_id,
                        ]);
                    }
                }
            }
        }
        catch (\Exception $e)
        {
            print_r($product);die();
        }
    }
    die('Not end... Need reload page');
}
die('End! <a href="?update">Update</a> "status" categories for start parsing');