<?php
// eugene
// pulls in delimited files and creates a table in an mysql db

date_default_timezone_set("America/New_York");

class Eugene {

    private static $dboptions = array(
        "info" => DBINFO,
        "user" => DBUSER, // make sure the dbuser has CREATE, INSERT, and SELECT privileges
        "pass" => DBPASS
    );
    
    static $defaultOptions = array(
        "delimiter" => "^",
        "primary_key" => "id",
        "schema" => array(
            "id" => "int"
        ),
        "table_extra" => array(
            "on_shelf" => "tinyint(1)",
            "checked_by" => "varchar(150)",
            "date_checked" => "varchar(50)"
        )
    );

    public $tableName;
    public $options;
    public $headings;
    public $contents;

    /**
     *  __construct:
     *      takes in a file path and an array of options, splits out the row titles,
     *      and sets rows for all
     */

    function __construct($file, $options = array()) {
        
        $this->tableName = Eugene::buildTableName();

        $this->options = array_merge(Eugene::$defaultOptions, $options);

        $this->contents = explode("\n", file_get_contents($file));

        // we'll get an array of headings by shifting out the first row of our contents
        //  and exploding that, then we'll clean them up a bit
        $this->headings = explode($this->options['delimiter'], array_shift($this->contents));

        foreach($this->headings as &$option) {
            $option = str_replace("\r", "", strtolower(str_replace(" ", "_", $option)));
            if ($option == "call_#(item)") { $option = "call_number"; }
        }
    }


    /**
     *  setTable:
     *      let's commence-a-jigglin'
     */

    function setTable() {
        $opts = Eugene::$dboptions;

        // first we'll build the table:
        $tableQuery = $this->createTableQuery();


        try {
            print_r(Eugene::query($tableQuery));
        } catch(PDOEXCEPTION $e) {
            echo $e->getMessage();
        }

        $pdo = new PDO($opts['info'], $opts['user'], $opts['pass']);

        foreach($this->contents as $item) {
            if (!$item) { continue; }

            $rows = explode($this->options['delimiter'], $item);

            $query = $this->buildStatement($rows);

            $stmt = $pdo->prepare($query);
            $stmt->execute($rows);

            $errors = $stmt->errorInfo();

            if($errors[0] !== "00000") {
                print_r($item);
                echo "<br />";
                print_r($stmt->errorInfo());
            }

            $stmt->closeCursor();
        }

    }



    /**
     *  buildStatement:
     *      returns a prepared-statement to use w/ the querying tool
     */

    function buildStatement($input) {
        $rowString = implode(",", $this->headings);

        $query = "INSERT INTO `" . $this->tableName . "` (" . $rowString . ") VALUES ";

        $query .= "(" . implode(",", array_fill(0, count($input), "?")) . ")";

        return $query;
    }


    /**
     *  createTable:
     *      creates a table query syntax
     */

    function createTableQuery() {
        $query = "CREATE TABLE IF NOT EXISTS `" . $this->tableName . "`(";

        foreach($this->headings as $heading) {

            $null = $heading == $this->options['primary_key'] ? "NOT NULL" : "NULL";
            $query .= "`" . $heading . "`" . " VARCHAR(250) " . $null . ", ";
        }

        foreach(Eugene::$defaultOptions['table_extra'] as $key => $value) {
            $query .= "`" . $key . "` " . strtoupper($value) . " " . $null . ", ";
        }

        $query .= "PRIMARY KEY(`" . $this->options['primary_key'] . "`)";

        $query .= ")";
        return $query;
    }


    /**
     *  buildTableName:
     *      returns a date string that we'll use as our table name
     */

    static function buildTableName() {
        return date('Y-m-d\TH:i:s');
    }


    /**
     *  query:
     *      standard run-of-the-mill pdo-driven db queryin'
     */

    static function query($query, $items = array()) {
        $opts = Eugene::$dboptions;
        
        $pdo = new PDO($opts['info'], $opts['user'], $opts['pass']);
        $stmt = $pdo->prepare($query);
        
        $stmt->execute($items);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        print_r($stmt->errorInfo);

        // clear out pdo connection
        /*
        unset($stmt);
        unset($pdo);
        */
        return $results;
    }
}
?>