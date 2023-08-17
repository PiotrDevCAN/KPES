<?php
namespace itdq;

/**
 * Class for interfacing with a DB2 Table.
 * Can handle tables with Encrypted Columns, simply call the __construct with the encryption password as the 2nd Parm.
 *
 * @author GB001399
 * @package itdqLib
 *
 */
class DbTable

{
    use xls;

    protected $tableName;

    protected $columns;

    protected $special_columns;

    protected $primary_keys;
    // protected $specialColumns;
    protected $noOfCols;

    protected $pwd;
    // private static $removeAble = array ("&", " /", "/", " ", "-", "#", ".", "(", ")" ,":","\n","'",0x1d, "?");
    // private static $replaceWith = array (null, null, "_", "_", null, "NUMBER", null, null, null, null,"_",null, null, null ); // Changed 5th char from _ to null 20130903
    // public static $removeAble = array ("&", " /", "/", " ", "-", "#", ".", "(", ")" ,":","+","$","%","\n","?","'","*",">",0x1d);
    // public static $replaceWith = array (null, null, "_", "_", "_", "NUMBER", null, null, null, null, null,null,null,null,null,null,null,null,null,null,'_');
    public static $removeAble = array(
        "&",
        " /",
        "/",
        " ",
        "-",
        "#",
        ".",
        "(",
        ")",
        ":",
        "+",
        "$",
        "%",
        "\n",
        "?",
        "'",
        "*",
        ">",
        0x1d
    );

    public static $replaceWith = array(
        null,
        null,
        "_",
        "_",
        "_",
        "NUMBER",
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        "_",
        null,
        null,
        null,
        null,
        null,
        null,
        '_'
    );
 // Changed "\n" to map to "_" 20140427
    public static $wordCloudIgnoreList = array("+","/","-", "a", "about", "above", "above", "account", "across", "after", "afterwards", "again", "against", "all", "almost", "alone", "along", "already", "also","although","always","am","among", "amongst", "amoungst", "amount",  "an", "and", "another", "any","anyhow","anyone","anything","anyway", "anywhere", "are", "around", "as",  "at", "back","be","became", "because","become","becomes", "becoming", "been", "before", "beforehand", "behind", "being", "below", "beside", "besides", "between", "beyond", "bill", "both", "bottom","but", "by", "call", "can", "cannot", "cant", "co", "con", "could", "couldnt", "cry", "de", "describe", "detail", "do", "done", "down", "due", "during", "each", "eg", "eight", "either", "eleven","else", "elsewhere", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything", "everywhere", "except", "few", "fifteen", "fify", "fill", "find", "fire", "first", "five", "for", "former", "formerly", "forty", "found", "four", "from", "front", "full", "further", "get", "give", "go", "had", "has", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "him", "himself", "his", "how", "however", "hundred", "i", "ie", "if", "in", "inc", "indeed", "interest", "into", "is", "it", "its", "itself", "keep", "last", "latter", "latterly", "least", "less", "ltd", "made", "many", "may", "me", "meanwhile", "might", "mill", "mine", "more", "moreover", "most", "mostly", "move", "much", "must", "my", "myself", "name", "namely", "neither", "never", "nevertheless", "next", "nine", "no", "nobody", "none", "noone", "nor", "not", "nothing", "now", "nowhere", "of", "off", "often", "on", "once", "one", "only", "onto", "or", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own","part", "per", "perhaps", "please", "put", "rather", "re", "same", "see", "seem", "seemed", "seeming", "seems", "serious", "several", "she", "should", "show", "side", "since", "sincere", "six", "sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "such", "system", "take", "ten", "than", "that", "the", "their", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "top", "toward", "towards", "twelve", "twenty", "two", "un", "under", "until", "up", "upon", "us", "very", "via", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "will", "with", "within", "without", "would", "yet", "you", "your", "yours", "yourself", "yourselves", "the","mid-cycle");
 // default word list to ignore.
    protected static $wordCloudCreateMode = 'w';

    protected static $wordCloudAppendMode = 'a';

    public static $wordCloudSize = '20';
    public static $wordCloudMagnifyFactor = '10';
    public static $wordCloudBiggestBubble = 100;
 // Max number of entries to return.
    private $inserted;

    private $failed;

    protected $preparedInsert;

    public $preparedInsertSQL;

    protected $preparedSelect;

    public $preparedSelectSQL;

    public $lastUpdateSql;

    protected $log;

    protected $nonMandatoryColumns;

    protected $ignoreRows;

    protected $addTheseFieldsWhenLoading;

    protected $uploadId;

    protected $lastId;

    public $lastDb2StmtError;

    public $lastDb2StmtErrorMsg;

    private static $dateFormats = array(
        'yyyy-mm-dd hh:ii:ss',
        'dd/mm/yy hh:ii:ss',
        'dd/mm/yyyy hh:ii:ss'
    );

    public static $months = array(
        'JAN' => '01',
        'FEB' => '02',
        'MAR' => '03',
        'APR' => '04',
        'MAY' => '05',
        'JUN' => '06',
        'JUL' => '07',
        'AUG' => '08',
        'SEP' => '09',
        'OCT' => '10',
        'NOV' => '11',
        'DEC' => '12'
    );

    const ROLLBACK_YES = true;
    const ROLLBACK_NO  = false;

    function __construct($table, $pwd = null, $log = true)
    {
        Trace::traceComment("Table:$table", __METHOD__);
        $this->tableName = $table;
        $this->pwd = $pwd;
        $this->getDBColumns();
        $this->getSpecialColumns();
        $this->getPrimaryKeys();
        $this->noOfCols = count($this->columns);
        $this->preparedInsert = null;
        $this->preparedSelect = null;
        $this->log = $log;
        $this->ignoreRows = 0;
    }

    /**
     * Supports uploading a CSV file into a DB2 Table.
     *
     * Will check that all the columns in the Table are represented in the CSV.
     * Is not bothered if the CSV has MORE columns than the table, additional columns will be ignored.
     * Call setIgnorows to tell this function how many header rows to ignore before it starts to load data.
     * Will treat the 1st Row (after the ignored rows) as a HEADER Row with Column Titles.
     * Will translate the Headings found in the CSV into valid DB2 Column Names using the function toColumnName
     *
     * Tracks rows Processed, Inserted and Ignored and uses Javascript to update fields in document.MyForm with these stats
     *
     * @param string $csv
     *            Name of the CCSV file to be loaded.
     */
    function loadFromCsv($csv, $withUploadLogId = false)
    {
        Trace::traceComment(null, __METHOD__, __LINE__);

        if ($withUploadLogId) {
            echo "<BR/>Will log this load.";
            $uploadLogTable = new UploadLogTable(AllItdqTables::$UPLOAD_LOG);
            $account = isset($_REQUEST['accountName']) ? $_REQUEST['accountName'] : null;
            $uploadLogRecord = new UploadLogRecord($_FILES['CSVFilename']['name'], $_REQUEST['tablename'], $account);
            $uploadLogTable->saveRecord($uploadLogRecord);
            $this->uploadId = $uploadLogTable->lastId();
            $uploadLogRecord->setId($this->uploadId);
            $db2CommitState = sqlsrv_commit($GLOBALS['conn'], sqlsrv_commit_OFF);
            echo "<BR/>Log id is " . $this->uploadId;
        }

        $log = new LoadLog();
        $this->processed = 0;
        $this->inserted = 0;
        $this->failed = 0;
        $time = array();
        $csvMap = array(); // Col[n] = name;
        $csvCols = array(); // Col[name] = n;
        $updateable = FALSE; // we will start from the premis that we will perform DELETE/INSERT and not UPDATE
        if (($handle = fopen($csv, "r")) !== FALSE) {
            $comma = $this->getSeperatorCharacter($csv, $handle);
            for ($index = 0; $index < $this->ignoreRows; $index ++) {
                /*
                 * Skip over the "ignore" rows.
                 */
                $data = fgetcsv($handle);
            }
            while (($data = fgetcsv($handle, 0, $comma)) !== FALSE) {
                set_time_limit(60);
                if ($this->processed ++ == 0) { // Only do these checks ONCE - on the HEADER Row.
                    Trace::traceVariable($withUploadLogId, __METHOD__, __LINE__);
                    $withUploadLogId ? $data[] = 'UPLOAD ID' : null;
                    Trace::traceVariable($data, __METHOD__, __LINE__);
                    // $data = $this->preprocessHeaderRow($data);

                    $num = count($data); // $num = Number of Columns in the CSV
                    $this->empty ++; // of course the header row we don't load, so count it as an 'empty'
                    /*
                     * Chain along the row placing each column heading in the $csvOrig, $csvMap, $csvCols arrays
                     * Then call toColumnName to convert it to the value we'll expect to see as a DB2 Column name.
                     * $csvMap maps the actual Column Name in the CSV to the expected DB2 Column Name.
                     * $csvCols maps the DB2 Column Name to the Column Name in the CSV
                     */
                    for ($c = 0; $c < $num; $c ++) {
                        $csvOrig[$c] = $data[$c];
                        $columnHeading = DbTable::toColumnName($this->translateColumnHeading($data[$c]));
                        Trace::traceComment("Col $c Mapped :" . $data[$c] . " on to " . $columnHeading, __METHOD__, __LINE__);
                        if ($this->validColumn($columnHeading)) {
                            Trace::traceComment("Valid $c:$columnHeading", __METHOD__, __LINE__);
                            $dbColumnName = DbTable::toColumnName($columnHeading);
                            $csvMap[$c] = $dbColumnName;
                            $csvCols[$dbColumnName] = $c;
                        } else {
                            Trace::traceComment("Invalid $c:$columnHeading", __METHOD__, __LINE__);
                        }
                    }
                    Trace::traceVariable($csvOrig, __METHOD__, __LINE__);
                    Trace::traceVariable($csvMap, __METHOD__, __LINE__);
                    Trace::traceVariable($csvCols, __METHOD__, __LINE__);
                    /*
                     * Lets see if we have ALL the columns we need.
                     * If we do - we will "delete" and "INSERT" the new data.
                     * If we don't - then we won't do the DELETE - rather we will exit(message)
                     */
                    $missing = $this->checkCsvColumns($csvCols);
                    if (is_array($missing)) {
                        Trace::traceVariable($missing, __METHOD__, __LINE__);
                        echo "<BR/>Some columns present in <B>$this->tableName</B> are not present in <B>$csv</B>";
                        echo "<BR/>Missing columns are :";
                        print_r($missing);
                        echo "<BR/>Input Row :";
                        var_dump($data);
                        $message = "<BR/><B>No Action Taken</B>.";
                        exit($message);
                    } else {
                        echo "<BR/><B>All columns in " . $this->tableName . "</B> are present in <B>$csv</B>";
                        /*
                         * So at this point - we know we can process the data file.
                         */
                        $this->displayCsvMap($csvOrig, $csvMap, $csvCols);
                        if (isset($_REQUEST['type'])) {
                            if ($_REQUEST['type'] == 'Replace') {
                                $this->deleteData();
                                $this->commitUpdates();
                            }
                        }
                        $preparedInsert = $this->prepareInsert();
                        $mode = "insert";
                    }
                } elseif ($this->loadThisRow($data, $csvCols, $withUploadLogId)) {
                    $withUploadLogId ? $data[] = $this->uploadId : null;
                    // echo "<BR/>" . __METHOD__ . __LINE__ . ":";
                    // print_r($data);
                    $data = $this->preprocessDataRow($data);
                    switch ($mode) {
                        case 'insert':
                            foreach ($this->columns as $key => $propertiesArray) {
                                if (! isset($this->nonMandatoryColumns[$key])) {
                                    $csvCol = $csvCols[$key];
                                    $data[$csvCol] = $this->preprocessField($csvCol, $data[$csvCol], $key);
                                    if (! isset($data[$csvCol])) {
                                        $data[$csvCol] = null;
                                    }
                                    // echo "<BR/><B>Type:</B>" . $this->columns [$key] ['DATA_TYPE'] . ":" . trim($data [$csvCol]);
                                    switch ($this->columns[$key]['DATA_TYPE']) {
                                        case 93: // It's a TIMESTAMP
                                            $valid = FALSE;
                                            foreach (self::$dateFormats as $dateFormat) {
                                                if (isset($data[$csvCol])) {
                                                    Trace::traceVariable(trim($data[$csvCol]), __METHOD__, __LINE__);
                                                    $validTimestamp = $this->interpretDateTime(trim($data[$csvCol]), $dateFormat);
                                                    if ($validTimestamp) {
                                                        $trace = "<BR/>$csvCol Timestamp was : " . $data[$csvCol];
                                                        $insertArray[$csvMap[$csvCol]] = $validTimestamp;
                                                        $this->$key = $validTimestamp;
                                                        $valid = TRUE;
                                                        $trace .= " Timestamp is :" . $validTimestamp;
                                                        Trace::traceComment($trace, __METHOD__);
                                                        break;
                                                    }
                                                } else {
                                                    $insertArray[$csvMap[$csvCol]] = null;
                                                }
                                            }
                                            if (! $valid) {
                                                $trace = "<BR/>Row: $this->processed Column: $csvMap[$csvCol] [$csvCol] Value: $data[$csvCol] did not validate as a timestamp, so field set to NULL";
                                                echo "$trace";
                                                Trace::traceComment($trace, __METHOD__);
                                                $insertArray[$csvCol] = null;
                                            }
                                            break;
                                        case 92: // IT's a TIME
                                            $valid = FALSE;
                                            foreach (self::$dateFormats as $dateFormat) {
                                                $formatSection = explode(" ", trim($dateFormat));
                                                $formatTime = $formatSection[1];
                                                Trace::traceVariable(trim($data[$csvCol]), __METHOD__, __LINE__);
                                                $validTime = $this->interpretTime(trim($data[$csvCol]), $formatTime);
                                                if ($validTime) {
                                                    $trace = "<BR/>$csvCol Time was : " . $data[$csvCol];
                                                    $insertArray[$csvMap[$csvCol]] = $validTime;
                                                    $this->$key = $validTime;
                                                    $valid = TRUE;
                                                    $trace .= " Time is :" . $validTime;
                                                    Trace::traceComment($trace, __METHOD__);
                                                    break;
                                                }
                                            }
                                            if (! $valid) {
                                                $trace = "<BR/>Row: $this->processed Column: $csvMap[$csvCol] [$csvCol]  Value: $data[$csvCol] did not validate as a TIME, so field set to NULL";
                                                // echo "$trace";
                                                Trace::traceComment($trace, __METHOD__, __LINE__);
                                                $insertArray[$csvCol] = null;
                                            }
                                            break;
                                        case 9: // It's a Date
                                        case 91: // Also a date
                                            $valid = FALSE;
                                            foreach (self::$dateFormats as $dateFormat) {
                                                $formatSection = explode(" ", trim($dateFormat));
                                                $formatDate = $formatSection[0];
                                                if (isset($data[$csvCol])) {
                                                    Trace::traceVariable(trim($data[$csvCol]), __METHOD__, __LINE__);
                                                    $validDate = $this->interpretDate(trim($data[$csvCol]), $formatDate);
                                                    if ($validDate) {
                                                        $trace = "<BR/>$csvCol Date was : " . $data[$csvCol];
                                                        $insertArray[$csvMap[$csvCol]] = $validDate;
                                                        $this->$key = $validDate;
                                                        $valid = TRUE;
                                                        $trace .= " Date is :" . $validDate;
                                                        Trace::traceComment($trace, __METHOD__);
                                                        break;
                                                    }
                                                } else {
                                                    $insertArray[$csvMap[$csvCol]] = null;
                                                }
                                            }
                                            if (! isset($data[$csvCol]))
                                                if (! $valid) {
                                                    $trace = "<BR/>Row: $this->processed Column: $csvMap[$csvCol] [$csvCol] Value: $data[$csvCol] did not validate as a date, so field set to NULL";
                                                    // echo "$trace";
                                                    Trace::traceComment($trace, __METHOD__);
                                                    $insertArray[$csvCol] = null;
                                                }

                                                // $date = $this->interpretDate ( trim ( $data [$csvCol] ) );
                                                // $insertArray [] = $date;
                                                // echo "<BR/>Data:";
                                                // var_dump(trim($data[$csvCol]));
                                                // echo "<BR/>Date:";
                                                // var_dump($date);
                                            ;
                                            break;
                                        case 3:
                                            // echo "<BR/>Row: " . $this->processed . " col: $csvCol data:" . $data [$csvCol] . " trim:" . trim();
                                            if (strlen(trim($data[$csvCol])) > 0) {
                                                $insertArray[$csvMap[$csvCol]] = trim($data[$csvCol]) * 1;
                                            } else {
                                                $insertArray[$csvMap[$csvCol]] = null;
                                            }
                                            break;
                                        default:
                                            if (isset($data[$csvCol])) {
                                                if (strlen(trim($data[$csvCol])) > 0) {
                                                    $insertArray[$csvMap[$csvCol]] = trim($data[$csvCol]);
                                                } else {
                                                    $insertArray[$csvMap[$csvCol]] = null;
                                                }
                                            } else {
                                                $insertArray[$csvMap[$csvCol]] = null;
                                            }
                                            ;
                                            break;
                                    }
                                }
                            }
                            ;

                            /*
                             * Prepare here - so we can ignore blank date fields.
                             */
                            $insertArray = $this->clearFieldsWithZero($insertArray);
                            // print_r($insertArray);
                            if (! empty($insertArray)) {
                                $preparedInsert = $this->prepareInsert($insertArray);
                                if (sqlsrv_execute($preparedInsert, $insertArray)) {
                                    $this->inserted ++;
                                } else {
                                    $this->failed ++;
                                    echo "<BR/><B>Insert Failed</B> Error msg: " . sqlsrv_errors() . " Error:" . sqlsrv_errors();
                                    echo "<BR/><B>Record #:</B>" . $this->processed . " <BR/><B>Data :</B>";
                                    echo "<BR/><B>Prepared Insert:</B>" . $this->preparedInsertSQL . "<BR/>";
                                    print_r($insertArray);
                                    echo "</HR>";
                                    $sqlstate = sqlsrv_errors();
                                    if (! isset($sqlStates[$sqlstate])) {
                                        $sqlStates[$sqlstate] = 1;
                                    } else {
                                        $sqlStates[$sqlstate] ++;
                                    }
                                }
                            }
                            break;
                        default:
                            exit(__METHOD__ . __LINE__ . " Unidentified mode");
                            ;
                            break;
                    }
                    unset($insertArray);
                    if (($this->processed) % 50 == 0) {
                        $this->commitUpdates();
                        echo "<SCRIPT LANGUAGE='JavaScript'>\n";
                        echo "\ndocument.MyForm.processed.value = " . $this->processed;
                        echo "\ndocument.MyForm.inserted.value = " . $this->inserted;
                        echo "\ndocument.MyForm.ignored.value = " . $this->failed;
                        echo "</script>\n";
                    }
                }
            }
            echo "<SCRIPT LANGUAGE='JavaScript'>\n";
            echo "\ndocument.MyForm.processed.value = " . $this->processed;
            echo "\ndocument.MyForm.inserted.value = " . $this->inserted;
            echo "\ndocument.MyForm.ignored.value = " . $this->failed;
            echo "</script>\n";
            if (isset($sqlStates)) {
                echo "<H2>Errors by SQLSTATE Codes : ";
                print_r($sqlStates);
                echo "</H2>";
            } else {
                echo "<H2>No Errors</H2>";
            }
            if (! isset($_REQUEST['type'])) {
                if (! isset($_REQUEST['load_type'])) {
                    $type = 'unknown';
                } else {
                    $type = $_REQUEST['load_type'];
                }
            } else {
                $type = $_REQUEST['type'];
            }
            $log->logEntry($this->tableName, $csv, $type, $this->inserted, $this->failed);
        }
        fclose($handle);

        if ($withUploadLogId) {
            $uploadLogTable->setStatus($this->uploadId, UploadLogRecord::$statusLOADED);
            sqlsrv_commit($GLOBALS['conn']);
            sqlsrv_commit($GLOBALS['conn'], $db2CommitState);
            return $this->uploadId;
        } else {
            return TRUE;
        }
    }

    /**
     * Calls PHP Function db2_columns and populates the Property <b>columns</b> with details of the columns in the table.
     */
    function getDBColumns()
    {
        Trace::traceComment(null, __METHOD__);
        $rs = db2_columns($GLOBALS['conn'], null, $GLOBALS['Db2Schema'], strtoupper($this->tableName), '%');
        while ($row = sqlsrv_fetch_array($rs)) {
            Trace::traceVariable($row, __METHOD__, __LINE__);
            $this->columns[trim($row['COLUMN_NAME'])] = $row;
        }
        Trace::traceVariable($this->columns, __METHOD__, __LINE__);
    }

    function getSpecialColumns()
    {
        Trace::traceComment(null, __METHOD__);
        $rs = db2_special_columns($GLOBALS['conn'], null, $GLOBALS['Db2Schema'], $this->tableName, 0);
        while ($row = sqlsrv_fetch_array($rs)) {
            $this->special_columns[trim($row['COLUMN_NAME'])] = $row;
        }
    }

    function isSpecialColumn($colName)
    {
        return array_key_exists($colName, $this->special_columns);
    }

    function getColumnType($columnName)
    {
        return $this->columns[$columnName]['TYPE_NAME'];
    }

    function getColumnLength($columnName)
    {
        return $this->columns[$columnName]['COLUMN_SIZE'];
    }

    function getNullable($columnName)
    {
        if ($this->columns[$columnName]['NULLABLE'] == 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function getColumnDef($columnName)
    {
        return $this->columns[$columnName]['COLUMN_DEF'];
    }

    /**
     * Calls PHP Function db2_primary_keys and populates the property <B>primary_keys</b> with the details.
     */
    function getPrimaryKeys()
    {
        Trace::traceComment(null, __METHOD__);
        $rs = db2_primary_keys($GLOBALS['conn'], null, $GLOBALS['Db2Schema'], $this->tableName);
        while ($row = sqlsrv_fetch_array($rs)) {
            // print_r($row);
            $this->primary_keys[trim($row['COLUMN_NAME'])] = $row;
        }
    }

    /**
     * Runs a simple select with the Predicate param and returns the Row returned by sqlsrv_fetch_array
     *
     * @param string $predicate
     *            Valid DB2 predicate, without the leading WHERE
     * @return multitype:
     */
    function getPredicate($predicate = null)
    {
        Trace::traceComment(null, __METHOD__);
        Trace::traceVariable($predicate, __METHOD__);
        $sql = $this->getSelect();
        $sql .= " WHERE " . $predicate;
        Trace::traceVariable($sql, __METHOD__);
        $rs = $this->execute($sql);
        $row = sqlsrv_fetch_array($rs);
        return $row;
    }

    /**
     * Runs a simple select with the Predicate param and returns the ResultSet for further processing.
     *
     * @param string $predicate
     *            Valid DB2 predicate, without the leading WHERE
     * @return multitype:
     */
    function getRsWithPredicate($predicate = null, $distinct = false)
    {
        Trace::traceComment(null, __METHOD__);
        Trace::traceVariable($predicate, __METHOD__);
        $sql = $this->getSelect();
        if ($distinct) {
            $sql = str_replace('SELECT', 'SELECT DISTINCT', $sql);
        }
        $sql .= " WHERE " . $predicate;
        Trace::traceVariable($sql, __METHOD__);
        $rs = sqlsrv_query($GLOBALS['conn'], $sql, array(
            'cursor' => DB2_SCROLLABLE
        ));
        return $rs;
    }

    /**
     * Issues a Select for the Record passed to it, using the values in the Key Fields in the Record to build a specific DB2 SELECT.
     *
     * It does NOT populate the record with the result of the sqlsrv_fetch_array, rather it returns the data in an array.
     * You can then populate the record using the dbRecord function setFromArray();
     *
     * @param DbRecord $record
     * @return array:
     */
    function getRecord(DbRecord $record, $predicate = null)
    {
        Trace::traceComment(null, __METHOD__);
        $select = $this->getSelect();
        $pred = $this->buildKeyPredicate($record);
        $sql = $select . " WHERE " . $pred . $predicate;
        Trace::traceVariable($sql, __METHOD__);

        $row = sqlsrv_fetch_array($this->execute($sql));
        if (! $row) {
            DbTable::displayErrorMessage($row, __CLASS__, __METHOD__, $sql);
        } else {
            foreach ($row as $key => $value) {
                $row[$key] = trim($value);
            }
        }
        return $row;
    }

    /**
     * Simply returns the number of columns defined on the table.
     *
     * @return number
     */
    function getNoOfCols()
    {
        return $this->noOfCols;
    }

    /**
     * Used in the loadCsV function to inform user of the format of each of the columns being loaded.
     */
    function reportColumns()
    {
        foreach ($this->columns as $key => $array) {
            // print_r($array);
            echo "<BR/>$key : $array[DATA_TYPE]";
            if ($this->columns[$key]['DATA_TYPE'] == 98 or $this->columns[$key]['DATA_TYPE'] == - 3) {
                echo " Is encrypted";
            }
            if ($this->columns[$key]['DATA_TYPE'] == 9) {
                echo "<B>Is Date</B><BR/>";
                echo "Format to be used is : " . $_REQUEST['date_format'];
            }
        }
    }

    /**
     * Used to inform the user of the Primary Keys defined to the table.
     */
    function reportKeys()
    {
        if ($this->primary_keys != null) {
            foreach ($this->primary_keys as $key => $array) {
                echo "<BR/>$key : $array[PK_NAME]";
            }
        }
    }

    /**
     * Passed as input an array of column names, this function will check to see if :
     * <li>Every column in our Table has a column in the array passed to us.
     *
     * @param array $csvCols
     * @return string|boolean
     */
    function checkCsvColumns($csvCols)
    {
        Trace::traceVariable($csvCols, __METHOD__, __LINE__);
        $missing = null;
        foreach ($this->columns as $key => $value) {
            Trace::traceVariable($key, __METHOD__, __LINE__);
            if (! isset($csvCols[$key])) {
                Trace::traceComment(null, __METHOD__, __LINE__);
                if ($this->mandatoryField($key)) {
                    Trace::traceComment(null, __METHOD__, __LINE__);
                    $missing[] = $key;
                }
            }
        }
        Trace::traceVariable($missing, __METHOD__, __LINE__);
        if (! isset($missing)) {
            return TRUE;
        } else {
            return $missing;
        }
    }

    /**
     * Allows extending classes to allow some fields to be non-mandatory.
     * If non-mandatory, they won't have to be loaded from a CSV.
     * To declare a Property/Column as non-mandatory, simple have an entry for it in <b>nonMandatoryColumns</B>,
     * typically you'd populate that array in the construct for the extending class.
     *
     *
     * @param string $columnName
     * @return boolean
     */
    function mandatoryField($columnName)
    {
        Trace::traceVariable($columnName, __METHOD__, __LINE__);
        Trace::traceVariable($this->nonMandatoryColumns, __METHOD__, __LINE__);
        if (isset($this->nonMandatoryColumns[$columnName])) {
            Trace::traceComment('False', __METHOD__, __LINE__);
            return false;
        } else {
            Trace::traceComment('True', __METHOD__, __LINE__);
            return true;
        }
    }

    /**
     * Called from loadFromCsv()
     *
     * To display details of the columns in the CSV and how they map to the DB2 Table
     *
     *
     * @param array $csvOrig
     *            This is the original column headings from the CSV
     * @param array $csvMap
     *            This is those headings after conversion by toColumnName()
     */
    function displayCsvMap($csvOrig, $csvMap, $csvCols)
    {
        // echo "<BR/>" . __LINE__ . "<BR/>";
        $header = "<TR class='blue-med-dark'><TH>Col #</TH>";
        $csvName = "<TR class='blue-med-dark'><TH>Header</TH>";
        $db2Col = "<TR class='blue-med-dark'><TH>DB2 Column</TH>";

        foreach ($csvOrig as $columnNumb => $columnName) {
            // echo "<BR/>" . __LINE__ . "<BR/>";
            $header .= "<TH style='text-align:center'>" . $columnNumb . "</TH>";
            $csvName .= "<TD style='text-align:center'>" . $columnName . "</TD>";
            // if (isset ( $this->columns [$csvMap [$columnNumb]] )) {
            if (isset($csvMap[$columnNumb])) {
                // echo "<BR/>" . __LINE__ . "<BR/>";
                $db2Col .= "<TD style='background-color:yellow;text-align:center'>" . $csvMap[$columnNumb];
                switch ($this->columns[$csvMap[$columnNumb]]['DATA_TYPE']) {
                    case 9:
                        $db2Col .= "<BR/>" . $_REQUEST['date_format'];
                        break;
                    case - 3:
                    case 98:
                        $db2Col .= "<BR/>Encrypted";
                        break;
                    default:
                        $db2Col .= "<BR/>" . $this->columns[$csvMap[$columnNumb]]['TYPE_NAME'] . " (" . $this->columns[$csvMap[$columnNumb]]['DATA_TYPE'] . ")<BR/> Size:" . $this->columns[$csvMap[$columnNumb]]['BUFFER_LENGTH'];
                        break;
                }
                $db2Col .= "</TD>";
            } else {
                $db2Col .= "<TD></TD>";
            }

            // echo $db2Col .= "</TD>";
        }
        // echo "<BR/>" . __LINE__ . "<BR/>";
        echo "<H3>Csv to DB2 Mapping</H3>";
        echo "<table>";
        echo $header . "</TR>";
        echo $csvName . "</TR>";
        echo $db2Col . "</TR>";
        echo "</TABLE>";
    }

    /**
     * Deletes the entire contents of the table.
     */
    function clear($announce = true)
    {
        $sql = " DELETE FROM " . $GLOBALS['Db2Schema'] . "." . $this->tableName;
        Trace::traceVariable($sql, __METHOD__);
        $rs = sqlsrv_query($GLOBALS['conn'], $sql);
        if (! $rs) {
            print_r($_SESSION);
            echo "<BR/>" . sqlsrv_errors();
            echo "<BR/>" . sqlsrv_errors() . "<BR/>";
            exit("Error in: " . __METHOD__ . " running: " . $sql);
        }
        if ($announce) {
            echo "<BR/><B>Entire contents of " . $GLOBALS['Db2Schema'] . "." . $this->tableName . " deleted.</B>";
        }
    }

    /**
     *
     * Simply builds and executes a DB2 DELETE FROM ... WHERE .....
     *
     * @param string $predicate
     *            The predicate for the Delete Statement, without the WHERE
     * @param boolean $announce
     *            If True then a comment will be displayed on the screen
     */
    function deleteData($predicate = null, $announce = true)
    {
        Trace::traceVariable($predicate, __METHOD__);
        $sql = " DELETE FROM " . $GLOBALS['Db2Schema'] . "." . $this->tableName;
        $comment = "Contents of ";
        if ($predicate != null) {
            $sql .= " WHERE $predicate ";
            $safePredicate = str_replace($this->pwd, 'password', $predicate);
            $comment = "Record where " . $safePredicate . " in ";
        }
        if ($announce) {
            echo "<BR/><B>$comment " . $GLOBALS['Db2Schema'] . "." . $this->tableName . " about to be deleted.</B>";
        }
        Trace::traceVariable($sql, __METHOD__);
        $rs = $this->execute($sql);
        if (! $rs) {
            echo "<BR/>SQLState:" . $this->lastDb2StmtError;
            echo "<BR/>SQLError:" . $this->lastDb2StmtErrorMsg . "<BR/>";
            exit("Error in: " . __METHOD__ . " running: " . str_replace($this->pwd, "******", $sql));
        } elseif ($announce) {
            echo "<BR/><B>$comment " . $GLOBALS['Db2Schema'] . "." . $this->tableName . " deleted.</B>";
        }
    }

    function deleteRecord(DbRecord $record)
    {
        Trace::traceComment(null, __METHOD__, __LINE__);
        $sql = " Delete FROM " . $GLOBALS['Db2Schema'] . ".$this->tableName WHERE " . $this->buildKeyPredicate($record);
        Trace::traceVariable($sql, __METHOD__, __LINE__);
        $rs = $this->execute($sql);
        if (! $rs) {
            print_r($_SESSION);
            echo "<BR/>" . sqlsrv_errors();
            echo "<BR/>" . sqlsrv_errors() . "<BR/>";
            exit("Error in: " . __METHOD__ . " running: " . str_replace($this->pwd, "******", $sql));
        }
    }

    /**
     * Calls DB2 REFRESH TABLE
     */
    function refresh($time = 180)
    {
        set_time_limit($time); // Can take a while
        echo "<BR/><B>About to refresh :" . $GLOBALS['Db2Schema'] . "." . $this->tableName . "</B>";
        $refreshStart = microtime(true);
        $rs = $this->execute(" REFRESH TABLE " . $GLOBALS['Db2Schema'] . "." . $this->tableName);
        $refreshEnded = microtime(true);
        $refreshElapsed = ($refreshEnded - $refreshStart);
        Trace::traceComment("Refresh took " . $refreshElapsed, __METHOD__, __LINE__);
        echo "<BR/><B>Contents of " . $GLOBALS['Db2Schema'] . "." . $this->tableName . " have been Refreshed.</B>";
    }

    /**
     * Calls sqlsrv_query on the sql passed in.
     * Optionally creating a Log::logEntry recording the SQL (with any encryption password masked out)
     *
     * @param string $sql
     *            SQL to be run.
     * @param boolean $log
     *            True = create a log entry.
     * @return resource
     */
    function execute($sql, $log = false)
    {
        Trace::traceVariable($sql, __METHOD__);
        $rs = sqlsrv_query($GLOBALS['conn'], $sql);
        if (! $rs) {
            $this->lastDb2StmtError = sqlsrv_errors();
            $this->lastDb2StmtErrorMsg = sqlsrv_errors();
            self::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
            return false;
        } else {
            if ($log) {
                Log::logEntry("<B>" . get_class() . "</B>" . str_replace($this->pwd, 'password', $sql), $this->pwd);
            }
            return $rs;
        }
    }

    /**
     * Will build an INSERT statement, call DB2_PREPARE on it and return the Resource DB2 returns.
     *
     * It expects $insertArray to be an array where the KEY is the column name and the VALUE the value to be inserted into the Table.
     * It uses $this->columns (built when __construct calls getDBColumns) to know if the Column is Encrypted or not, if it is then the INSERT
     * is created with ENCRYPT_RC2 around the encrypted column.
     *
     * It also knows to remove Date Time columns that are empty, as they won't insert. It actually removes them from insertArray, so they
     * don't mess up the sqlsrv_queryUTE when it gets invoked, this is why $insertArray is declared as &$insertArray, as this method needs to be able to modify it.
     *
     * <B>NOTE</B> In one upgrade to the server, maintenance was applied that modified the values for some of the Datatypes, can't remember now if they changed from 98 to -3 or the other
     * way around, but that's why both values are specified in the switch. The 'symptom' was that we were inserting non-encrpyted values into columns that should have been encrypted.
     *
     *
     *
     *
     * @param array $insertArray
     *            Array containing the data to be inserted
     * @return resource
     */
    function prepareInsert(&$insertArray = null)
    {
        Trace::traceVariable($insertArray, __METHOD__, __LINE__);
        $insert = " INSERT INTO  " . $GLOBALS['Db2Schema'] . "." . $this->tableName;
        $colNames = " (";
        $values = " (";
        foreach ($this->columns as $key => $properties) {
            if ((($insertArray != null && isset($insertArray[$key])) or (($insertArray == null)) && (! isset($this->nonMandatoryColumns[$key])))) {
                Trace::traceComment('Processing' . $key . "type " . $properties['DATA_TYPE'] . "Len:" . strlen($insertArray[$key]), __METHOD__, __LINE__);
                // They have passed us an Array to use - AND - This field has a value in that array
                // or
                // Thet did NOT pass a field (so we're using ALL the fields defind in uppercase in the Class
                // AND
                // THIS field is NOT defined as one that can be ignored becuase it's "non mandatory"
                switch ($properties['DATA_TYPE']) {
                    case 98:
                    case - 3:
                        $colNames .= "," . $key;
                        $values .= ", ENCRYPT_RC2(CAST(? as VARCHAR(" . $properties['CHAR_OCTET_LENGTH'] . ")),'$this->pwd')";
                        break;
                    case 91:
                    case 93:
                        Trace::traceComment('Processing a DATE' . $key, __METHOD__, __LINE__);
                        if (isset($insertArray)) {
                            if (! empty($insertArray[$key])) {
                                Trace::traceComment('NOT Removing Date', __METHOD__, __LINE__);
                                $colNames .= "," . $key;
                                $values .= ", ? ";
                            } else {
                                Trace::traceComment('Removing Date', __METHOD__, __LINE__);
                                // Remove and Date/Timestamp fields that are empty - because they won't insert.
                                echo "<BR/>Removing blank Date/Timestamp field $key";
                                unset($insertArray[$key]);
                            }
                        } else {
                            $colNames .= "," . $key;
                            $values .= ", ? ";
                        }
                        break;
                    default:
                        $colNames .= "," . $key;
                        $values .= ", ? ";
                        break;
                }
            }
        }
        $sql = $insert . str_replace("(,", "( ", $colNames) . ") VALUES " . str_replace("(,", "( ", $values) . ")";

        if ($sql != $this->preparedInsertSQL) {
            // This is a different INSERT to the one we prepared last time
            // So best prepare a new statement, which we will save in the hope of reusing
            Trace::traceVariable($sql, __METHOD__, __LINE__);
            $this->preparedInsertSQL = $sql;
            $this->preparedInsert = sqlsrv_prepare($GLOBALS['conn'], $sql);
            if (! $this->preparedInsert) {
                echo "<BR/>" . sqlsrv_errors();
                echo "<BR/>" . sqlsrv_errors() . "<BR/>";
                exit("Unable to Prepare $sql");
            }
        }
        return $this->preparedInsert;
    }

    /**
     * Will Insert into the Table a row from a Descendant of DbRecord.
     *
     * Will call the method prepareInsert() on an array built by dbRecord->getColumns() of just those columns that are populated.
     *
     * @param DbRecord $record
     * @return boolean
     */
    function insert(DbRecord $record, $populatedColumns = true, $nullColumns = true)
    {
        Trace::traceComment(null, __METHOD__, __LINE__);
        // $populatedColumns = false; // Only get columns that have values in them. 20130820 Move it to a Parm - so SAVE can set the value, so we can save zeros and nulls
        $key = true; // We need KEY fields for the Insert to work
        // $nullColumns = false; // Don't return empty columns
        $db2 = FALSE;
        $insertArray = $record->getColumns($populatedColumns, $key, $nullColumns, $db2);
        Trace::traceVariable($insertArray, __METHOD__, __LINE__);
        $preparedInsert = $this->prepareInsert($insertArray);

        $rs = @sqlsrv_execute($preparedInsert, $insertArray);

        if (! $rs) {
            $this->lastDb2StmtError = sqlsrv_errors();
            $this->lastDb2StmtErrorMsg = sqlsrv_errors();

            echo "<BR/>Insert Array@<pre>" . __METHOD__ . __LINE__ ;
            print_r($insertArray);
            echo "</pre>";
            self::displayErrorMessage($rs, __CLASS__, __METHOD__, $this->preparedInsertSQL, $this->pwd, $this->lastDb2StmtError, $this->lastDb2StmtErrorMsg, $insertArray);
        } else {
            $this->lastId = db2_last_insert_id($GLOBALS['conn']);
        }
        if (isset($_SESSION['log'])) {
            Log::logEntry("DBTABLE SQL:" . str_replace($this->pwd, 'password', $this->preparedInsertSql), $this->pwd);
            Log::logEntry("DBTABLE Data:" . serialize($insertArray), $this->pwd);
        }
        return $rs;
    }

    function InsertFromArray(array $insertArray, $withTimings = false, $rollbackIfError = true)
    {
        $preparedInsert = $this->prepareInsert($insertArray);

        $insert = -microtime(true);
        $rs = @sqlsrv_execute($preparedInsert, $insertArray);
        $insert += microtime(true);
        echo $withTimings ?  "Db2 Insert Time:" . sprintf('%f', $insert) . PHP_EOL : null;

        if (! $rs) {
            $this->lastDb2StmtError = sqlsrv_errors();
            $this->lastDb2StmtErrorMsg = sqlsrv_errors();

            echo "<br/>Method:" . __METHOD__ . " Line:" .  __LINE__ ;
            echo "<br/>Insert Array:";
            echo "<pre>";
            print_r($insertArray);
            echo "</pre>";
            self::displayErrorMessage($rs, __CLASS__, __METHOD__, $this->preparedInsertSQL, $this->pwd, $this->lastDb2StmtError, $this->lastDb2StmtErrorMsg, $insertArray, $rollbackIfError);
            return false;
        } else {
            $this->lastId = db2_last_insert_id($GLOBALS['conn']);
            return true;
        }
    }

    /**
     * Will update a Row in the Table, from a descendant of DbRecord.
     *
     * @param DbRecord $record
     * @param boolean $populatedColumns
     * @param boolean $nullColumns
     * @return resource
     */
    function update(DbRecord $record, $populatedColumns = true, $nullColumns = true)
    {
        Trace::traceComment(null, __METHOD__, __LINE__);
        $pred = $this->buildKeyPredicate($record);
        // $populatedColumns = false; // Only get columns that have values in them. 20130820 Move it to a Parm - so SAVE can set the value, so we can save zeros and nulls
        $key = true; // We need KEY fields for the Update to work
        // $nullColumns = false; // Don't return empty columns
        $db2 = FALSE;
        $updateArray = $record->getColumns($populatedColumns, $key, $nullColumns, $db2);
        Trace::traceVariable($updateArray, __METHOD__, __LINE__);
        $values = " SET";
        $sql = " UPDATE " . $GLOBALS['Db2Schema'] . ".$this->tableName ";

        foreach ($this->columns as $key => $properties) {
            /*
             * The following will build the SET element of the SQL to set the columns to their new values.
             *
             * It will ignore and columns that are in the PRIMARY KEY - as you cant really change them.
             *
             * So if you get an error where the SQL has only UPDATE SET and WHERE
             * ie UPDATE SCHEME.TABLE SET WHERE KEY='value'
             *
             * The problem is - the only column they changes was one in the primary key. *
             *
             */
            if (isset($updateArray[$key]) && (! isset($this->primary_keys[$key]))) {
                switch ($properties['DATA_TYPE']) {
                    case 13: // BLOB
                        $values .= ", $key = BLOB('$updateArray[$key]')";
                        break;

                    case 98: // Encrypted Field.
                    case - 3: // Encrypted Field.
                        $values .= ", $key = ENCRYPT_RC2('$updateArray[$key]','$this->pwd')";
                        break;
                    case 91: // Date Field
                        if (empty($updateArray[$key])) {
                            $values .= ", $key = null ";
                        } else {
                            $values .= ", $key = '$updateArray[$key]' ";
                        }
                        break;
                    case 93:
                        if (empty($updateArray[$key])) {
                            $values .= ", $key = null ";
                        } elseif ($updateArray[$key] == 'CURRENT TIMESTAMP') {
                            $values .= ", $key = $updateArray[$key] ";
                        } else {
                            $values .= ", $key = '$updateArray[$key]' ";
                        }
                        break;
                    case 4: // Integer
                    case 2: // Float
                        if (empty($updateArray[$key]) or $updateArray[$key] == 'null') {
                            $values .= ", $key = 0 ";
                        } else {
                            $values .= ", $key = '" . htmlspecialchars($updateArray[$key]) . "' ";
                        }
                        break;
                    default:
                        if (empty($updateArray[$key])) {
                            $values .= ", $key = null ";
                        } else {
                            $values .= ", $key = '" . htmlspecialchars($updateArray[$key]) . "' ";
                        }
                        break;
                }
            } else {
                if (! $populatedColumns) {}
            }
        }
        $values = str_replace('SET,', 'SET ', $values);

        if (strlen(trim($values)) > 3) {
            $sql .= $values . " WHERE " . $pred;
            Trace::traceVariable($sql, __METHOD__, __LINE__);

            $this->lastUpdateSql = $sql;
            $rs = $this->execute($sql);

            Trace::traceVariable($rs, __METHOD__, __LINE__);

            if (! $rs) {
                $this->lastDb2StmtError = sqlsrv_errors();
                $this->lastDb2StmtErrorMsg = sqlsrv_errors();
    
                echo "<BR/>Insert Array@<pre>" . __METHOD__ . __LINE__ ;
                print_r($updateArray);
                echo "</pre>";
                self::displayErrorMessage($rs, __CLASS__, __METHOD__, $this->preparedInsertSQL, $this->pwd, $this->lastDb2StmtError, $this->lastDb2StmtErrorMsg, $updateArray);
            } else {
                $this->lastId = db2_last_insert_id($GLOBALS['conn']);
            }
            if (isset($_SESSION['log'])) {
                Log::logEntry("DBTABLE SQL:" . str_replace($this->pwd, 'password', $this->preparedInsertSql), $this->pwd);
                Log::logEntry("DBTABLE Data:" . serialize($updateArray), $this->pwd);
            }
            return $rs==true;
        } else {
            return false;
        }
    }

    /**
     * Shouldn't be used anymore -an old version of the Update method.
     *
     * @param unknown_type $preparedUpdate
     * @param unknown_type $preparedSelect
     * @param unknown_type $cols
     * @param unknown_type $exSerial
     */
    function updateold($preparedUpdate, $preparedSelect, $cols, $exSerial = false)
    {
        // echo "<BR/>" . __METHOD__ . __LINE__ ;
        // print_r($cols);
        if ($exSerial) {
            $db2Columns = array_merge($cols, array(
                'Key' => $cols['SERIAL_NUMBER']
            )); // Put the KEY on the end, so we have an answer for every ? we PREPARED.
        } else {
            $db2Columns = $cols;
        }
        if ($cols['SERIAL_NUMBER'] != null) {
            $this->logRecord($preparedSelect, $cols, "<B>Before image </b>");
            $rs = sqlsrv_execute($preparedUpdate, $db2Columns);
            if (! $rs) {
                echo "<BR/>" . sqlsrv_errors();
                echo "<BR/>" . sqlsrv_errors() . "<BR/>";
                echo "<BR/> Cols: " . var_export($cols, true) . "<BR/>Db2Columns : " . var_export($cols, true) . "<BR/> ex Serial: $exSerial";
            }
            $this->inserted ++;
            $this->logRecord($preparedSelect, $cols, "<B>After image </b>");
        } else {
            echo "<BR/> Row does not contain values in the Key Fields, Update not possible ";
            print_r($cols);
            $this->empty ++;
        }
    }

    function prepareSelect()
    {
        // $select = " SELECT " . $GLOBALS['Db2Schema'] . "." . $this->tableName;
        // $colNames = " ";
        // foreach ( $this->columns as $key => $properties ) {
        // if ($properties ['DATA_TYPE'] == 98) {
        // $colNames .= ", DECRYPT_CHAR(CAST(? as VARCHAR(" . $properties ['CHAR_OCTET_LENGTH'] . ")),'$this->pwd')";
        // } else {
        // $values .= ", ? ";
        // }
        // }
        // $sql = str_replace ( "SELECT ,","SELECT ", "SELECT" . $colNames . " FROM " . $GLOBALS['Db2Schema'] . "." . $this->tableName );
        // echo "<BR/>" . __METHOD__ . __LINE__ . "<BR/>" . $sql;
        //
        // // $sql = " SELECT DECRYPT_CHAR((CHAR)?,'$this->pwd') FROM " . $GLOBALS['Db2Schema'] . "." . $this->tableName ;
        //
        // $this->preparedSelect = sqlsrv_prepare ( $_SESSION ['conn'], $sql );
        // if (! $preparedStmt) {
        // echo "<BR/>" . sqlsrv_errors ();
        // echo "<BR/>" . sqlsrv_errors () . "<BR/>";
        // exit ( "Unable to Prepare $sql" );
        // }
        // return $preparedSelect;
    }

    /**
     * Prepares a SELECT statement.
     *
     * Select will return all the columns on this table, optionally you can specify additional columns,
     * so it's possible to add "JOIN otherTable...." to the SQL returned by this method.
     *
     * @param string $additionColumns
     *            Additional columns you need in the SELECT statement.
     * @param string $tableRef
     *            If you need the SELECT to end .... FROM tablename AS $tableRef. For example if you're going to use this select in a join etc etc
     * @return mixed
     */
    function getSelect($additionColumns = null, $tableRef = null)
    {
        Trace::traceComment(null, __METHOD__);
        $select = " SELECT ";
        foreach ($this->columns as $key => $properties) {
            if ($tableRef != null) {
                $columnName = $tableRef . "." . $key;
            } else {
                $columnName = $key;
            }

            if ($properties['DATA_TYPE'] == 98 or $properties['DATA_TYPE'] == - 3) {
                $select .= ", DECRYPT_CHAR(" . $columnName . ",'$this->pwd') as " . $key;
            } else {
                $select .= ", " . $columnName . " as " . $key;
            }
        }
        $select .= $additionColumns;
        $select .= " FROM  " . $GLOBALS['Db2Schema'] . "." . $this->tableName;
        if ($tableRef != null) {
            $select .= " AS $tableRef";
        }
        Trace::traceVariable(str_replace('SELECT ,', ' SELECT ', $select), __METHOD__);
        return str_replace('SELECT ,', ' SELECT ', $select);
    }

    /**
     * Returns an array where both KEY and VALUE are the COLUMN_NAME
     *
     * Steps through the array $this->columns (built by getDbColumn(), invoked in __contruct.
     *
     * @return array
     */
    function getColumns($prefix = NULL)
    {
        Trace::traceComment(null, __METHOD__);
        $columns = array();
        foreach ($this->columns as $key => $properties) {
            if ($prefix != NULL) {
                $columns[$properties['COLUMN_NAME']] = $prefix . "." . $properties['COLUMN_NAME'];
            } else {
                $columns[$properties['COLUMN_NAME']] = $properties['COLUMN_NAME'];
            }
        }
        return $columns;
    }

    /**
     * Simply returns the array $this->columns (built by getDbColumn(), invoked in __contruct.
     *
     * @return array:
     */
    function getColProperties()
    {
        return $this->columns;
    }

    /**
     * Returns the property : tableName
     *
     * @return string
     */
    function getName()
    {
        return $this->tableName;
    }

    /**
     * Runs a SELECT for the record passed in, using the values for the Key fields from the DbRecord itself.
     *
     * Requires that DbRecord has any columns that are PRIMARY KEYs in the DB to be populated.
     * Requires that the Table has PRIMARY KEYS declared to it.
     *
     * So typical usage would be :
     * <li>Use setFromArray() to populate any columns from the Primary Key
     * <li>call this method
     * <li>call setFromArray() again to re-populate the DbRecord from the Array returned from this method. *
     *
     * <B>NOTE:</B> If you see an SQL error and the sql has the string 'start' in it - then you've not defined Primary Keys to the table.
     *
     * @param DbRecord $record
     *            Record to be SELECTed. Requires those columns in DbRecord that are Primary Keys in the Table to be populated
     * @return array Does not save the values to the DbRecord, rather it returns the array returned by sqlsrv_fetch_array
     */
    function getFromDb(DbRecord $record)
    {
        Trace::traceComment(null, __METHOD__);
        $select = $this->getSelect();
        $pred = $this->buildKeyPredicate($record);
        $sql = $select . " WHERE " . $pred;
        Trace::traceVariable($sql, __METHOD__);
        $row = sqlsrv_fetch_array($this->execute($sql));
        return $row;
    }

    /**
     * Uses the $predicate to build a SELECT * statement.
     * Returns the result of sqlsrv_fetch_array()
     *
     * @param string $predicate
     * @return array
     */
    function getWithPredicate($predicate)
    {
        Trace::traceVariable($predicate, __METHOD__);
        $sql = "SELECT * FROM " . $GLOBALS['Db2Schema'] . "." . $this->tableName . " WHERE " . $predicate;
        Trace::traceVariable($sql, __METHOD__);
        $resultSet = $this->execute($sql);
        if ($resultSet) {
            $result = sqlsrv_fetch_array($resultSet);
        } else {
            return false;
        }
        Trace::traceVariable($result, __METHOD__);
        return $result ? $result : false;
    }

    /**
     * Calls sqlsrv_query on COMMIT
     */
    function commitUpdates()
    {
        $rs = sqlsrv_query($GLOBALS['conn'], " COMMIT");
        if (! $rs) {
            print_r($_SESSION);
            echo "<BR/>" . sqlsrv_errors();
            echo "<BR/>" . sqlsrv_errors() . "<BR/>";
            exit("Error in: " . __METHOD__ . " running: COMMIT ");
        }
    }

    /**
     * Used by loadFromCsv to translate a date from the CSV to the format required in DB2.
     *
     * Picks up expected date format from 'date_format' field in HTML Form, then uses that format to convert the date from the CSV to the format required by DB2 on the iSeries
     *
     * @param string $value
     *            The date value from the CSV
     * @return string|NULL A string containing the date in month/day/year format for DB2 on iSeries.
     */
    function interpretDateTime($value, $format)
    {
        Trace::traceVariable($value, __METHOD__);
        Trace::traceVariable($format, __METHOD__);
        $dateSection = explode(" ", trim($value));
        $formatSection = explode(" ", trim($format));
        // print_r($dateSection);
        $date = $dateSection[0];
        $formatDate = $formatSection[0];
        $validDate = $this->interpretDate($date, $formatDate);

        if (isset($dateSection[1])) {
            // If they passed a TIME then validate it
            $time = $dateSection[1];
            $formatTime = $formatSection[1];
            $validTime = $this->interpretTime($time, $formatTime);
            $timeValue = $validTime;
        } else {
            // Else just say it's valid.
            $validTime = TRUE;
            $timeValue = '00:00:00';
        }

        if (! $validDate) {
            return FALSE;
        } elseif (! $validTime) {
            return FALSE;
        } else {
            echo "interpretDateTime will return $validDate $timeValue";
            if (! empty($timeValue)) {
                return $validDate . " " . $timeValue;
            } else {
                return $validDate;
            }
        }
    }

    /**
     * Used by loadFromCsv to translate a date from the CSV to the format required in DB2.
     *
     * Picks up expected date format from 'date_format' field in HTML Form, then uses that format to convert the date from the CSV to the format required by DB2 on the iSeries
     *
     * @param string $value
     *            The date value from the CSV
     * @return string|NULL A string containing the date in month/day/year format for DB2 on iSeries.
     */
    // function interpretDate($value) {
    // $dd = strpos ( $_REQUEST ['date_format'], 'dd' );
    // $mm = strpos ( $_REQUEST ['date_format'], 'mm' );
    // $y4 = strpos ( $_REQUEST ['date_format'], 'yyyy' );
    // $y2 = strpos ( $_REQUEST ['date_format'], 'yy' );
    // if ($y4 == 0) {
    // $year = substr ( $value, $y2, 2 );
    // } else {
    // $year = substr ( $value, $y4, 4 );
    // }
    // $day = substr ( $value, $dd, 2 );
    // $mon = substr ( $value, $mm, 2 );
    // if (checkdate ( $mon, $day, $year )) {
    // return $mon . "/" . $day . "/" . $year;
    // } else {
    // return null;
    // }
    // }
    function interpretDate($value, $format)
    {
        Trace::traceVariable($value, __METHOD__);
        Trace::traceVariable($format, __METHOD__);
        // echo "<BR/> Interpreting $value against $format";
        $dd = strpos($format, 'dd');
        $mm = strpos($format, 'mm');
        $y4 = strpos($format, 'yyyy');
        $y2 = strpos($format, 'yy');
        $tt = strpos($format, 'bf');

        // echo "<BR/>Y4:$y4 Y2:$y2 DD:$dd MM:$mm TT:$tt";

        if (! is_int($y4)) {
            $year = trim(substr($value, $y2, 2));
        } else {
            $year = trim(substr($value, $y4, 4));
        }

        if (strLen($year) == 2) {
            $year = "20" . $year;
        }

        $day = substr($value, $dd, 2);
        $mon = substr($value, $mm, 2);

        // echo "<BR/>Checking : Day $day Month $mon Year $year";

        if (strlen(trim($value)) == 0) {
            // echo "<BR/>Date field is empty";
            return FALSE;
        } elseif (strlen(trim($value)) != strlen(trim($format))) {
            // They've passed us a string that is a different length to the $format, so it can't be in this format.
            // echo "<BR/>Date & Format are different lengths Date:" . strlen(trim($value)) . " Format:" . strlen(trim($format));
            return FALSE;
        } elseif (! is_numeric($day) or ! is_numeric($mon) or ! is_numeric($year)) {
            // We've stripped out day,mon,year - but they are not all numeric values, so it can't be in this format.
            // echo "<BR/>$value $format Not all values are Numeric";
            return FALSE;
        } elseif (checkdate($mon, $day, $year)) {
            // It's passsed the checkdate test,so return the date in the format iSeries needs.
            return $year . "-" . $mon . "-" . $day;
        } else {
            // Failed the checkdate test, so return FALSE
            // echo "<BR/>Checking : Day $day Month $mon Year $year";
            // echo "<BR/>Checkdate returned False";
            return FALSE;
        }
    }

    function interpretTime($value, $format)
    {
        Trace::traceVariable($value, __METHOD__);
        Trace::traceVariable($format, __METHOD__);
        // echo "<BR/> Interpreting $value against $format";
        $hh = strpos($format, 'hh');
        $ii = strpos($format, 'ii');
        $ss = strpos($format, 'ss');

        $hour = substr($value, $hh, 2);
        $min = substr($value, $ii, 2);
        $sec = substr($value, $ss, 2);

        // Hour could be missing the leading zero, in which case we'll have picked up the seperator char, Hour won't be numeric.
        if (! is_numeric($hour)) {
            $hour = substr($value, $hh, 1);
            $min = substr($value, $ii - 1, 2);
            $sec = substr($value, $ss - 1, 2);
        }

        // echo "<BR/>Checking : Hour $hour Min $min Sec $sec";

        if (! is_numeric($hour)) {
            return FALSE;
        } elseif (! is_numeric($min)) {
            return FALSE;
        } elseif (! is_numeric($sec)) {
            return FALSE;
        } elseif ($hour < 00 or $hour > 24) {
            return FALSE;
        } elseif ($min < 00 or $min > 60) {
            return FALSE;
        } elseif (($sec < 00 or $sec > 60)) {
            return FALSE;
        } else {
            return $hour . ":" . $min . ":" . $sec;
        }
    }

    /**
     * Outputs all the Properties of the class, for debugging purposes.
     *
     * This is similar to the iterateVisible method in FormClass, but it's not quite as powerful.
     *
     * @param string $length
     *            If !='short' then you get <BR/> before each property is output
     */
    function iterateVisible($length = 'short')
    {
        if ($length != 'short') {
            echo "<BR/>";
        }
        foreach ($this as $key => $value) {
            if (is_array($value)) {
                echo "<B>$key =></B>";
                print_r($value);
            } else {
                print "<B>$key =></B> $value\n";
            }
            if ($length != 'short') {
                echo "<BR/>";
            }
        }
    }

    /**
     * Will call either $this->update or $this->insert depending if this record exists in the table or not.
     * Returns TRUE if it inserted a new record, false if it updated an existing record.
     *
     *
     * @param DbRecord $record
     * @param boolean $populatedColumns
     */
    function saveRecord(DbRecord $record, $populatedColumns = true, $nullColumns = true, $commit = true)
    {
        ob_start();
        $record->iterateVisible();
        $recordDetails = ob_get_contents();
        @ob_end_clean();
        Trace::traceComment("Saving: $recordDetails", __METHOD__, __LINE__);

        $inserted = null;
        if ($this->existsInDb($record)) {
            Trace::traceComment('Attempting Update', __METHOD__, __LINE__);
            $this->actionBeforeUpdate($record, $populatedColumns, $nullColumns, $commit);
            $sql = $this->update($record, $populatedColumns, $nullColumns);
            $this->actionAfterUpdate($record, $populatedColumns, $nullColumns, $commit);
            $inserted = false;
        } else {
            Trace::traceComment('Attempting Insert', __METHOD__, __LINE__);
            $inserted = $this->insert($record, $populatedColumns, $nullColumns);
            $inserted = $inserted ? $inserted : null;
            $this->lastId = db2_last_insert_id($GLOBALS['conn']);
        }
        if ($commit) {
            $this->commitUpdates();
        }
        Trace::traceVariable($inserted, __METHOD__, __LINE__);

        return $inserted;
    }

    function actionBeforeUpdate(DbRecord $record, $populatedColumns = true, $nullColumns = true, $commit = true)
    {
        return;
    }

    function actionAfterUpdate(DbRecord $record, $populatedColumns = true, $nullColumns = true, $commit = true)
    {
        return;
    }

    /**
     *
     * Will build the predicate part of an SQL statement that has the key columns and their values in it.
     *
     * Used to build a Select or Update on a particular Record.
     *
     * So if the primary key on the table is "reference_number" and the DbRecord has the value 5261 in the property REFERENCE_NUMBER
     * this method will return a string like this REFERENCE_NUMBER='5261'
     *
     * It uses ENCRYPT_RC2 on encrypted columns
     *
     * @param DbRecord $record
     * @return mixed
     */
    function buildKeyPredicate(DbRecord $record)
    {
        Trace::traceComment(null, __METHOD__);
        $predicate = "define a primary key";
        foreach ($this->primary_keys as $key => $value) {
            if ($this->columns[$key]['DATA_TYPE'] == 98 or $this->columns[$key]['DATA_TYPE'] == - 3) {
                $predicate .= " AND $key = ENCRYPT_RC2('" . $record->getValue($key) . "','$this->pwd') ";
            } elseif ($this->columns[$key]['DATA_TYPE'] == 93) {
                $predicate .= " AND $key = TIMESTAMP('" . $record->getValue($key) . "') ";
            } else {
                $predicate .= " AND $key='" . htmlspecialchars($record->getValue($key)) . "' ";
            }
        }
        Trace::traceVariable(str_replace('define a primary key AND', ' ', str_replace("=''", " is null", $predicate)), __METHOD__);

        // var_dump($predicate);

        // return str_replace('define a primary key AND', ' ', str_replace("=''", " is null", $predicate));
        return str_replace('define a primary key AND', ' ', str_replace("=''", " = ''", $predicate));
    }

    /**
     * Will build the section of a SELECT statement that lists the columns, using DECRYPT_CHAR on any encrypted fields.
     *
     * @param DbRecord $record
     *            Descendant of DbRecord to be subject of the SELECT
     * @return string String for use in SELECT. ie "Plain_column as Plain_column, Decrypt_char(encrypted_column) as encypted_column" etc
     */
    function buildFullSelect(DbRecord $record = null)
    {
        Trace::traceComment(null, __METHOD__);
        $predicate = "define a primary key";
        foreach ($this->columns as $key => $value) {
            if ($this->columns[$key]['DATA_TYPE'] == 98 or $this->columns[$key]['DATA_TYPE'] == - 3) {
                $predicate .= " , DECRYPT_CHAR($key,'$this->pwd') as $key ";
            } else {
                $predicate .= " , $key ";
            }
        }
        Trace::traceVariable(str_replace('define a primary key ,', ' ', $predicate), __METHOD__);
        return str_replace('define a primary key ,', ' ', $predicate);
    }

    /**
     * Calls a SELECT from the descendant of DbRecord passed in and returns TRUE or FALSE depending on the result of the DB2 EXECUTE
     *
     * @param DbRecord $record
     * @return boolean
     */
    function existsInDb(DbRecord $record)
    {
        Trace::traceComment(null, __METHOD__, __LINE__);
        $sql = " SELECT count(*) as RECORDS FROM " . $GLOBALS['Db2Schema'] . ".$this->tableName WHERE " . $this->buildKeyPredicate($record);
        Trace::traceVariable($sql, __METHOD__, __LINE__);
        $rs = $this->execute($sql);
        if (! $rs) {
            return false;
        }
        $row = sqlsrv_fetch_array($rs);
        Trace::traceVariable($row['RECORDS'], __METHOD__, __LINE__);
        if ($row['RECORDS'] > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Calls a SELECT from the descendant of DbRecord passed in and returns TRUE or FALSE depending on the result of the DB2 EXECUTE
     *
     * @param DbRecord $record
     * @return boolean
     */
    function checkExists($predicate)
    {
        Trace::traceComment(null, __METHOD__, __LINE__);
        $sql = " SELECT count(*) as RECORDS FROM " . $GLOBALS['Db2Schema'] . ".$this->tableName WHERE " . $predicate;
        Trace::traceVariable($sql, __METHOD__, __LINE__);
        $rs = $this->execute($sql);
        if (! $rs) {
            print_r($_SESSION);
            echo "<BR/>" . sqlsrv_errors();
            echo "<BR/>" . sqlsrv_errors() . "<BR/>";
            exit("Error in: " . __METHOD__ . " running: " . str_replace($this->pwd, "******", $sql));
        }
        $row = sqlsrv_fetch_array($rs);
        Trace::traceVariable($row['RECORDS'], __METHOD__, __LINE__);
        if ($row['RECORDS'] > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Calls a SELECT returns number of occurances that meet the predicate or FALSE depending on the result of the DB2 EXECUTE
     *
     * @param DbRecord $record
     * @return boolean
     */
    function occursInDb($predicate)
    {
        Trace::traceComment(null, __METHOD__, __LINE__);
        $sql = " SELECT count(*) as RECORDS FROM " . $GLOBALS['Db2Schema'] . ".$this->tableName WHERE " . $predicate;
        Trace::traceVariable($sql, __METHOD__, __LINE__);
        $rs = $this->execute($sql);
        if (! $rs) {
            print_r($_SESSION);
            echo "<BR/>" . sqlsrv_errors();
            echo "<BR/>" . sqlsrv_errors() . "<BR/>";
            exit("Error in: " . __METHOD__ . " running: " . str_replace($this->pwd, "******", $sql));
        }
        $row = sqlsrv_fetch_array($rs);
        Trace::traceVariable($row['RECORDS'], __METHOD__, __LINE__);
        if ($row['RECORDS'] > 0) {
            return $row['RECORDS'];
        } else {
            return FALSE;
        }
    }

    /**
     * Builds a Table showing the details of the columns defined in the table.
     */
    function columnDescriptions()
    {
        echo "<H2 class='blue-med'>Table : " . $this->tableName . "</H2>";
        $color = '#ffffff';
        echo "<TABLE class='sortable' >";
        echo "<TR><TH>Column</TH><TH>Definition</TH><TH>Type</TH></TR>";
        foreach ($this->columns as $column => $details) {
            echo "<TR bgcolor='$color' ><TH>" . $column . "</TH><TD>" . $details['REMARKS'] . "<TD><TD>" . $details['DATA_TYPE'] . "</TD></TR>";
            if ($color == '#eeeeee') {
                $color = '#ffffff';
            } else {
                $color = '#eeeeee';
            }
        }
        echo "</TABLE>";
    }

    /**
     * returns the result of db2_last_insert_id
     *
     * Allows you to pick up the insert_id on newly inserted records where the Identity column is set to GENERATE ALWAYS
     */
    function lastId()
    {
        // $id = db2_last_insert_id($_SESSION ['conn']);
        $trace = "Table " . $this->tableName . " Last ID:" . $this->lastId;
        Trace::traceComment($trace, __METHOD__);
        return $this->lastId;
        // return $id;
    }

    /**
     * Translates strings to valid DB2 Column Names.
     *
     * This allows you to load a CSV with headings like "Column Heading" into a DB2 Column called "COLUMN_HEADING"
     * Just what characters are translated is determined by DbTable::$removeAble and DbTable::$replaceWith
     *
     * @param string $col
     *            column name in it's raw form from a CSV typically
     * @return mixed translates prohibited characters to build a valid DB2 COLUMN NAME
     */
    static function toColumnName($col)
    {
        Trace::TraceVariable($col, __METHOD__, __LINE__);
        $resp = str_replace(DbTable::$removeAble, DbTable::$replaceWith, strtoupper(trim($col)));
        Trace::TraceVariable($resp, __METHOD__, __LINE__);
        return str_replace(DbTable::$removeAble, DbTable::$replaceWith, strtoupper(trim($col)));
    }

    /**
     * Sets the value of the property ignoreRows
     *
     * Allows the Page to tell the class how many rows at the top of the CSV are to be ignored before it should expect to find the headings
     *
     * @param unknown_type $setTo
     */
    function setIgnoreRows($setTo = 0)
    {
        $this->ignoreRows = $setTo;
    }

    /*
     * called before we do all the work to do the insert, so this is the chance to ignore rows if you want to
     */
    function loadThisRow($data, $csvCols, $withUploadLogId)
    {
        return true;
    }

    /*
     * called during LoadCSV - gives you a chance to tweak any data before it goes through the "load" checks and the insert.
     */
    function preprocessHeaderRow($data)
    {
        // Trace::traceVariable($this->columns,__METHOD__,__LINE__);
        // foreach($data as $key => $value){
        // Trace::traceVariable($value,__METHOD__,__LINE__);
        // $columnName = DbTable::toColumnName($value);
        // $col = $this->validColumn(strtoupper(trim($columnName)));
        // Trace::traceVariable("Column Name $columnName Valid : $col" ,__METHOD__,__LINE__);
        // if($this->validColumn(strtoupper(trim($columnName)))){
        // $newdata[] = trim($value);
        // }
        // }
        // Trace::traceVariable($newdata,__METHOD__,__LINE__);
        return $data;
    }

    /*
     * called during LoadCSV - gives you a chance to tweak any data before it goes through the "load" checks and the insert.
     */
    function preprocessDataRow($data)
    {
        return $data;
    }

    /*
     * Called during LoadCsv - gives you a chance to field before it gets checked/inserted.
     */
    function preprocessField($column, $value, $key)
    {
        return $value;
    }

    /*
     * Gives a chance to map column names from a spreadsheet to different values to match the columns in the database.
     * so you can load the contents of the column "column_name_here" to a db2 column call "but_this_is_my_db2_col_name"
     */
    function translateColumnHeading($value)
    {
        return $value;
    }

    /*
     * Will check the passed value is a the name of the column in this table.
     */
    function validColumn($columnName)
    {
        return isset($this->columns[strtoupper(trim($columnName))]);
    }

    function timeLastLoaded($tableName = null)
    {
        $tableName = empty($tableName) ? $this->tableName : $tableName;
        $sql = " Select MAX(TIMESTAMP) as TIMESTAMP from " . $GLOBALS['Db2Schema'] . "." . AllItdqTables::$LOAD_LOG;
        $sql .= " WHERE TABLENAME='" . $tableName . "' ";
        $sql .= " GROUP BY TABLENAME ";

        $rs = sqlsrv_query($GLOBALS['conn'], $sql);
        if (! $rs) {
            print_r($_SESSION);
            echo "<BR/>" . sqlsrv_errors();
            echo "<BR/>" . sqlsrv_errors() . "<BR/>";
            exit("Error in: " . __METHOD__ . " running: $sql");
        }
        $row = sqlsrv_fetch_array($rs);
        return $row['TIMESTAMP'];
    }

    function getUploadLogId()
    {
        return $this->uploadId;
    }

    function clearFieldsWithZero($insertArray)
    {
        foreach ($insertArray as $key => $value) {
            $insertArray[$key] = (is_numeric($value) && $value == 0) ? null : $value; // modified after php 5.4 as it was setting strings to null
        }
        return $insertArray;
    }

    function getSeperatorCharacter($csv, $handle)
    {
        $line = null;
        while (strlen($line) == 0) {
            $line = fgets($handle);
        }
        $byComma = preg_split("/[,]+/", $line);
        $bySemi = preg_split("/[;]+/", $line);

        $numberOfCommas = count($byComma);
        $numberOfSemi = count($bySemi);
        $char = ","; // Default
        if ($numberOfCommas >= $numberOfSemi) {
            echo "<H3>More commas ($numberOfCommas) found than semicolons($numberOfSemi) - Comma used as CSV Seperator character</H3>";
            $char = ",";
        } else {
            echo "<H3>More semicolons ($numberOfSemi) found than commas($numberOfCommas) - SemiColon used as CSV Seperator character</H3>";
            $char = ";";
        }
        fseek($handle, 0);
        return $char;
    }

    static function moveUploadedFile($directory = 'remind')
    {
        if (empty($_FILES) && empty($_POST) && isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            $poidsMax = ini_get('post_max_size');
            exit(sprintf("File is too big,(%s bytes). Maximum allowed size here is $poidsMax.", $_SERVER['CONTENT_LENGTH']));
        } elseif (! move_uploaded_file($_FILES['CSVFilename']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . "/$directory/data/" . $_FILES['CSVFilename']['name'])) {
            echo "<BR><BR>Move Uploaded File returned : False";
            echo "<BR>Error Code : " . $_FILES['CSVFilename']['error'] . "<BR/>";
            print_r($_FILES);
            switch ($_FILES['CSVFilename']['error']) {
                case 1:
                    echo "<BR/>The uploaded file exceeds the upload_max_filesize directive in php.ini.";
                    break;
                case 2:
                    echo "<BR/>The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
                    break;
                case 3:
                    echo "<BR/>The uploaded file was only partially uploaded.";
                    break;
                case 4:
                    echo "<BR/>No file was uploaded.";
                    break;
                case 5:
                    echo "<BR/>unknown error";
                    break;
                case 6:
                    echo "<BR/>Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3.";
                    break;
                case 7:
                    echo "<BR/> Failed to write file to disk. Introduced in PHP 5.1.0.";
                    break;
                case 8:
                    echo "<BR/>A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help. Introduced in PHP 5.2.0.";
                    break;
                default:
                    echo "<BR/>An unknown error";
                    break;
                    break;
            }
            exit("<BR/>Upload failed - Please contact support team with the details.");
        } else {
            echo "<H2>File : <scan style='text-color:red'>" . $_FILES['CSVFilename']['name'] . "</scan> successful uploaded to Server.</H2>";
        }
    }

    static function displayErrorMessage($rs, $class, $method, $sql, $pwd = null, $db2Error = null, $db2ErrorMsg = null, $data = null, $rollback = true)
    {
        $db2Error = empty($db2Error) ? sqlsrv_errors() : $db2Error;
        $db2ErrorMsg = empty($db2ErrorMsg) ? sqlsrv_errors() : $db2ErrorMsg;
        $rollback ? db2_rollback($GLOBALS['conn']) : null; // Roll back to last commit point.

        if (isset(AllItdqTables::$DB2_ERRORS)) {
            echo "<BR/>" . $method . "<B>DB2 Error:</B><span style='color:red'>" . $db2Error . "</span><B>Message:</B><span style='color:red'>" . $db2ErrorMsg . "</span>$sql";
            DbTable::logDb2Error($data);
            return array(
                'Db2Error' => $db2Error,
                'Db2ErrorMsg' => $db2ErrorMsg
            );
        } else {
            echo "<BR/><B>DB2 Error:</B><span style='color:red'>" . $db2Error . "</span><B>Message:</B><span style='color:red'>" . $db2ErrorMsg . "</span>";
            $printableSql = empty($pwd) ? $sql : str_replace($pwd, "******", $sql);
            echo "<BR/>";
            echo "<pre>";
            debug_print_backtrace();
            echo "</pre>";
            echo "<BR/>";
            switch (trim($db2Error)) {
                case 220001:
                    $pattern = "/Value for column or variable (.+) too long(.+)/i";
                    $field = preg_split($pattern, $db2ErrorMsg);
                    var_dump($field);
                    break;

                default:
                    ;
                    break;
            }
            throw new \Exception("Error in: '$method' running: $printableSql", $db2Error);
        }
    }

    static function logDb2Error($data = null)
    {
        $userid = isset($_SESSION['ssoEmail']) ? $_SESSION['ssoEmail'] : 'userNotDefined';
        $elapsed = isset($_SESSION['tracePageOpenTime']) ? microtime(true) - $_SESSION['tracePageOpenTime'] : null;

        $sql = " INSERT INTO " . $GLOBALS['Db2Schema'] . "." . AllItdqTables::$DB2_ERRORS . " ( USERID, PAGE, DB2_ERROR, DB2_MESSAGE, BACKTRACE, REQUEST ) ";

        ob_start();
        echo "<pre>";
        debug_print_backtrace();
        echo "</pre>";
        $backtrace = ob_get_contents();
        @ob_end_clean();

        $backtrace = strlen(htmlspecialchars($backtrace)) > 1024 ? substr(htmlspecialchars($backtrace), 0, 1000) : htmlspecialchars($backtrace);

        ob_start();
        print_r($_REQUEST);
        $request = ob_get_contents();
        @ob_end_clean();

        if (! empty($data)) {
            ob_start();
            print_r($data);
            $request .= ":data:" . ob_get_contents();
            @ob_end_clean();
        }
        $request = strlen(htmlspecialchars($request)) > 1024 ? substr(htmlspecialchars($request), 0, 1000) : htmlspecialchars($request);
        
        $sqlsrv_errors = str_replace(['"',"'"], "", sqlsrv_errors());
        $sqlsrv_errors = str_replace(['"',"'"], "", sqlsrv_errors());
        $sql .= " VALUES ('" . $userid . "','" . $_SERVER['PHP_SELF'] . "','" . $sqlsrv_errors . "','" . $sqlsrv_errors . "','" . $backtrace . "','" . $request . "')";

        if (isset($_SESSION['phoneHome']) && class_exists('Email')) {
            $to = $_SESSION['phoneHome'];
            $subject = $GLOBALS['Db2Schema'] . " Bug : User: $userid Page:" . $_SERVER['PHP_SELF'];
            $summary = "<BR/>Page:" . $_SERVER['PHP_SELF'] . "<BR/>DB2 Error:<B>" . sqlsrv_errors() . "</B><BR/>Db2 Error Message<B>" . sqlsrv_errors() . "</B>";
            $body = $summary . "<BR/>$backtrace<HR/>$request<HR/>";
            Email::send_mail($to, null, $subject, $body, null, false);
            echo "<h4>An email has been sent to: $to informing them of this problem</h4>";
        }

        $rs = @sqlsrv_query($GLOBALS['conn'], $sql);
        if (! $rs) {
            echo "<BR>Error: " . sqlsrv_errors();
            echo "<BR>Msg: " . sqlsrv_errors() . "<BR>";
            throw new \Exception("Error in: " . __METHOD__ . __LINE__ . "<BR>running: $sql");
        }
    }

    static function nonDb2Error($message, $class, $method, $line)
    {
        if (isset($_SESSION['phoneHome'])) {
            ob_start();
            debug_print_backtrace();
            $backtrace = ob_get_contents();
            @ob_end_clean();

            $backtrace = strlen($backtrace) > 1024 ? substr($backtrace, 0, 1024) : $backtrace;

            ob_start();
            print_r($_REQUEST);
            $request = ob_get_contents();
            @ob_end_clean();

            $to = $_SESSION['phoneHome'];
            $subject = $GLOBALS['Db2Schema'] . " Bug : User: $userid Page:" . $_SERVER['PHP_SELF'];
            $summary = "<BR/>Page:" . $_SERVER['PHP_SELF'] . "<BR/>Non DB2 Error:<B>" . $message . "</B><BR/> Class:<B>" . $class . "</B><B> Method:</B>$method</B><B> Line:</B>$line";
            $body = $summary . "<BR/>$backtrace<HR/>$request<HR/>";
            Email::send_mail($to, null, $subject, $body, null, false);
            echo "<h4>An email has been sent to: $to informing them of this problem</h4>";
        }
    }

    function resetIdentity($columnName, $initialValue = 1)
    {
        $sql = " ALTER TABLE " . $GLOBALS['Db2Schema'] . "." . $this->tableName;
        $sql .= " ALTER COLUMN $columnName RESTART WITH $initialValue  ";
        return $this->execute($sql);
    }

    function truncateValueToFitColumn($columnValue, $columnName)
    {
        $lengthOfValue = strlen(trim($columnValue));
        $lengthColumnWillSupport = $this->columns[strtoupper($columnName)]['COLUMN_SIZE'];
        $truncatedValue = substr(trim($columnValue), 0, $lengthColumnWillSupport);
        return $truncatedValue;
    }

    function getWordCloudCsv($fileName, $colourCode = 1, $column, $predicate = null, $factor=null)
    {
        $factor = empty($factor) ? self::$wordCloudMagnifyFactor : $factor;
        $fileMode = (trim($colourCode) == '1') ? self::$wordCloudCreateMode : self::$wordCloudAppendMode;

        $handle = fopen($fileName, $fileMode);
        $wordCloud = $this->buildWordCloud($column, $predicate);
        $reducedWordCloud = $this->filterWordCloud($wordCloud);

        if ($fileMode == self::$wordCloudCreateMode) {
            $fields = array(
                'group',
                'name',
                'radius'
            );
            $written = fputcsv($handle, $fields);
        }


        $bubbleSuitableSize = false;

        while (!$bubbleSuitableSize) {
            $bubbleFields = $this->buildFieldArray($reducedWordCloud, $factor,$colourCode);
            $allFields = $bubbleFields['allFields'];
            $maxBubbleSize = $bubbleFields['maxBubble'];
            if($maxBubbleSize > self::$wordCloudBiggestBubble){
                $factor = $factor * 0.9;
            } else {
                $bubbleSuitableSize = true;
            }
        }

        foreach ($allFields as $fields){
            $written = fputcsv($handle, $fields);
        }

        fclose($handle);
    }

    function buildFieldArray($reducedWordCloud,$factor,$colourCode){
        $allFields = array();
        $maxBubbleSize = 0;
        foreach ($reducedWordCloud as $word => $frequency) {
            $bubbleSize = (int) ($frequency * $factor );
            $maxBubbleSize = $bubbleSize > $maxBubbleSize ?$bubbleSize : $maxBubbleSize;
            $allFields[] = array(
                $colourCode,
                $word,
                (int) ($frequency * $factor )
            );
        }
        return array('maxBubble'=>$maxBubbleSize, 'allFields'=>$allFields);
    }



    function buildWordCloud($column, $predicate = null)
    {
        $column = strtoupper($column);
        $sql = " SELECT $column ";
        $sql .= " FROM " . $GLOBALS['Db2Schema'] . "." . $this->tableName;

        $rs = sqlsrv_query($GLOBALS['conn'], $sql);
        if (! $rs) {
            print_r($_SESSION);
            echo "<BR/>" . sqlsrv_errors();
            echo "<BR/>" . sqlsrv_errors() . "<BR/>";
            exit("Error in: " . __METHOD__ . " running: $sql");
        }

        return self::columnAnalysis($rs);
    }

    function columnAnalysis($rs)
    {
        $columnData = null;
        while (($rowData = sqlsrv_fetch_array($rs)) == true) {
            $cleanRowData = strip_tags($rowData[0]);
            $strippedCleanRow = str_replace(array(
                '&nbsp;',
                '&amp;',
                '.',
                ',',
                '\0',
                chr(13),
                chr(10),
                '&'
            ), array(
                ' ',
                ' ',
                ' ',
                ' ',
                ' ',
                ' ',
                ' ',
                ' ',
                ' '
            ), strtolower($cleanRowData));

            $words = explode(" ", trim($strippedCleanRow));
            foreach ($words as $key => $word) {
                $cleanWord = trim($word, " .\r\t\n");
                if (! empty($cleanWord)) {
                    if (isset($columnData[$cleanWord])) {
                        $columnData[$cleanWord] ++;
                    } else {
                        $columnData[$cleanWord] = 1;
                    }
                }
            }
        }
        $cleanedUpWordList = array_diff_key($columnData, array_flip(self::$wordCloudIgnoreList));

        ksort($cleanedUpWordList);

        return $cleanedUpWordList;
    }

    function filterWordCloud(array $wordCloud)
    {
       arsort($wordCloud);
       $reducedWordCloud = array_chunk($wordCloud, self::$wordCloudSize, true);
       return $reducedWordCloud[0];
    }

    function getRsAsJsonForDatatables($resultSet){
//         $obj = new stdClass();
//         $obj->data = array(
//             array('1999','3.0','row'),
//             array('2000','3.9','row'),

        $obj = new \stdClass();
        $obj->data = array();

        while (($row = sqlsrv_fetch_($resultSet))==true) {
            $obj->data[] = $row;
            ;
        }
        return json_encode($obj);
    }

    function getTableName(){
        return $this->tableName;
    }

    static function db2ErrorModal(){
        ?>
       <!-- Modal -->
    <div id="db2ErrorModal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-xl">
          <div class="modal-content">
          <div class="modal-header">
             <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title">Db2 Error </h4>
            </div>
             <div class="modal-body" >
             </div>
             <div class='modal-footer'>
             <button type="button" class="btn btn-default" data-dismiss="modal" >Close</button>
             </div>
             </div>
            </div>
        </div>
        <?php
    }


}