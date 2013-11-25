# eugene

basically just takes a delimited file and imports it into mysql.

## notes/usage/whatever

```php
define("DBINFO", "mysql:host=127.0.0.1;dbname=whatever");
define("DBUSER", "theBoss");
define("DBPASS", "whatevs");

$file = "./inventory.txt";
$options = array(
  // these first two speak for themselves
  "delimiter" => "\t",
  "primary_key" => "barcode",
  
  // table_extra is an array of any extra fields you'd like
  // to add into the table that aren't included in the file
  // (columnName => columnType)
  "table_extra" => array(
    "createdBy" => varchar(150),
    "there" => tinyint(1)
  )
);
$eugene = new Eugene($file, $options);
$eugene->setTable();

if($eugene->getErrors()) {
  print_r($eugene->getErrors());
}
```
