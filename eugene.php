<?php
// eugene
// pulls in delimited files and creates a table in an mysql db

date_default_timezone_set("America/New_York");

class Eugene {

    static $options;
    static $dboptions = array(
        "info" => DBINFO,
        "user" => DBUSER, // make sure the dbuser has CREATE, INSERT, and SELECT privileges
        "pass" => DBPASS
    );

    /**
     *  __construct:
     *      merges $options array with our $defaults
     */


    function __construct($file, $options = array()) {
        
        $name = Eugene::generateTableName();

        $defaults = array(
            "delimiter" => "\t",
            "name" => $name,
            "schema" => array(
                "id" => "int(10)",
                "primary_key" => "id"
            )
        );

        Eugene::$options = array_merge($defaults, $options);

        // let's prep our file for work too
        $this->file = explode("\n", file_get_contents($file));
        $this->rows = explode(Eugene::$options['delimiter'], array_shift($this->file));
    }


    /**
     *  build:
     *      the main revealed function that inserts the file into the
     *      now newly-created database
     */

    function build() {
        $query = $this->buildTableQuery();

        $where = implode(",", Eugene::$options['schema']);

    }

    /**
     *  buildInsertQuery:
     *      generates a query to push the file contents into the db
     */

    function buildInsertQuery() {
        $tableName = Eugene::$options['name'];

        $where = $this->rows;
        
        foreach($where as &$row) {
            $row = str_replace(" ", "_", strtolower($row));

            // this might be a no-no, but the way millennium exports "call number"
            //   is so gross and not conducive to future querying, so we'll change it
            if ($row == "call_#(item)") { $row = "call_number"; }
        }

        $where = implode(",", $where);

        $fullQuery = "INSERT INTO " . $tableName . "(" . $where . ") VALUES ";

        $contents = $this->file;
        $count = count($contents);

        for($i = 0; $i < $count; $i++) {
            $line = explode(Eugene::$options['delimiter'], $contents[$i]);
            $linestring = "";

            foreach($line as $item) {
                $linestring .= "`" . str_replace(";", "|", $item) . "`,";
            }

            substr($linestring, -1);
            $fullQuery .= "(" . $linestring . "),";
        }

        substr($fullQuery, -1);
        return $fullQuery;
    }


    /**
     *  buildTableQuery:
     *      takes in the $options['schema'] and $options['name'] items and
     *      does the grunt work of building the mysql query
     */

    function buildTableQuery() {
        
        // first we'll slice out the primary key from the 'schema' option array
        $opts = Eugene::$options;
        $primaryKey = Eugene::$options['schema']['primary_key'];

        // start building our query (don't forget to wrap the key/types in parens!)
        $string = "CREATE TABLE IF NOT EXISTS `" . $opts['name'] . "` (\n";
        
        // to make our lives a bit easier, we'll stuff a NEW array with each
        // line of the table query, that way we can implode with a comma/newline
        // and it'll come out cleanly.
        $table = array();
        foreach($opts['schema'] as $key => $type) {
            $null = $key == $primaryKey ? "NOT NULL" : "NULL";

            if ($key == "primary_key") {
                $thing = "\t" . strtoupper(str_replace("_", " ", $key)) 
                       . "(`" . $type . "`)";
            } else {
                $thing = "\t`" . $key . "`" . " " . strtoupper($type) . " " . $null;
            }

            array_push($table, $thing);
        }
        
        // implode and add to our query string
        $table = implode(",\n", $table);
        $string .= $table . "\n)";

        return $string;
    }


    /**
     *  generateTableName:
     *      returns a datestring that we'll use as a table name
     *      year-month-dayThour:min:sec
     */

    static function generateTableName() {
        return date('Y-m-d\TH:i:s');
    }



    /**
     *  querydb:
     *      i feel like i write this for everything. could do it in my sleep. ^_^
     */

    static function querydb($query, $fillings = array()) {
        $pdo = new PDO(Eugene::$dboptions['dbinfo'], Eugene::$dboptions['dbname'], Eugene::$dboptions['dbpass']);
        $stmt = $pdo->prepare($query);
        $stmt->execute($fillings);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo->closeCursor();

        return $results;
    }

}
?>