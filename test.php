<?php
    require 'eugene.php';

    $file = "";
    $options = array(
        
        "delimiter" => "^",

        "schema" => array(
            // item information
            "call_number" => "varchar(25)",
            "title" => "varchar(250)",
            "location" => "varchar(10)",
            "barcode" => "varchar(25)",
            "status" => "varchar(5)",
            "imessage" => "varchar(5)",
            "due_date" => "varchar(25)",

            // meta info re: checking
            "on_shelf" => "tinyint(1)",
            "checked_by" => "varchar(150)",
            "date_checked" => "varchar(75)",

            // primary key for good keeping
            "primary_key" => "barcode"
        )
    );

    echo "<pre>";
    $eugene = new Eugene("../legume/inventory.txt", $options);
    print_r($eugene->buildInsertQuery());
?>