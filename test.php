<?php
    require 'eugene.php';

    define('DBINFO', '');
    define('DBUSER', '');
    define('DBPASS', '');

    $file = "../legume/inventory.txt";
    $options = array(
        
        "delimiter" => "^",
        "primary_key" => "barcode"
    );

    echo "<pre>";
    $eugene = new Eugene($file, $options);
    echo $eugene->setTable();
?>