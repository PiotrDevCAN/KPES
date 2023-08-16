<?php
namespace itdq;

class Loader
{

    private $pwd;

    private $notNull;

    public static $defaultEncode = 'TRUE';

    public static $defaultOrder = 'asc';

    function __construct($pwd = null, $notNull = true)
    {
        $this->pwd = $pwd;
        $this->notNull = $notNull;

        // echo "<BR/>" . __METHOD__ . __LINE__ . $this->pwd;
    }

    /**
     *
     * @param string $column
     * @param string $table
     * @param string $predicate
     * @param string $encode
     *            - Set to FALSE if you want to avoid AT&T becoming AT&amp;T
     * @param string $order
     * @return multitype:string
     */
    function load($column = null, $table = null, $predicate = null, $encode = TRUE, $order = 'asc')
    {
        Trace::traceVariable($predicate, __METHOD__, __LINE__);
        $array = array();

        $sql = self::buildSQL($column, $table);
        if ($predicate != null) {
            if ($this->notNull) {
                $sql .= strtoupper(substr(trim($predicate), 0, 3)) == 'AND' ? $predicate : " AND $predicate";
                // $sql .= " and $predicate ";
            } else {
                $sql .= " WHERE $predicate ";
            }
        }

        $sql .= " ORDER BY 1 $order ";

        Trace::traceVariable($sql, __METHOD__, __LINE__);
        $preDb2Time = microtime(TRUE);
        $rs5 = sqlsrv_query($GLOBALS['conn'], $sql);
        if (! $rs5) {
            DbTable::displayErrorMessage($rs5, __CLASS__, __METHOD__, $sql);
            return false;
        }
        $queryCompleted = microtime(TRUE);

        while (($row = sqlsrv_fetch_array($rs5)) !== false) {
           // Trace::traceVariable($row, __METHOD__, __LINE__);
//             $column = trim($column, '"');
            if (trim($row[0]) == null) {
                $value = 'null';
            } else {
                $value = trim($row[0]);
            }
            if ($encode) {
                $array[htmlspecialchars($value, ENT_QUOTES)] = htmlspecialchars($value, ENT_QUOTES);
            } else {
                $array[$value] = trim($value);
            }
        }
        $arrayBuilt = microtime(TRUE);
       // Trace::traceVariable($array, __METHOD__, __LINE__);
       // asort($array);
       //$arraySort = microtime(TRUE);
       // Trace::traceVariable($array, __METHOD__, __LINE__);

        $queryTime = $queryCompleted - $preDb2Time;
        $buildTime = $arrayBuilt - $queryCompleted;
       // $sortTime = $arraySort - $arrayBuilt;
        Trace::traceTimings("Loader Query $sql Timings: Query:($queryTime) Build:($buildTime) ", __METHOD__, __LINE__);

        return $array;
    }

    function loadIndexed($value = null, $key = null, $table = null, $predicate = null, $order = 'asc')
    {
        Trace::traceComment(null, __METHOD__);
        $array = array();

        $sql = $this->buildIxSQL($value, $key, $table);
        if ($predicate != null) {
            if ($this->notNull) {
                $sql .= " and $predicate ";
            } else {
                $sql .= " WHERE $predicate ";
            }
        }

        $sql .= " order by 1 $order ";
        Trace::traceVariable($sql, __METHOD__, __LINE__);

        $rs5 = sqlsrv_query($GLOBALS['conn'], $sql);
        if (! $rs5) {
            DbTable::displayErrorMessage($rs5, __CLASS__, __METHOD__, $sql);
        }

        while (($row = sqlsrv_fetch_both($rs5)) == true) {
            $array[utf8_encode(trim($row[$key]))] = utf8_encode(trim($row[$value]));
        }
        Trace::traceVariable($array, __METHOD__, __LINE__);
        return $array;
    }

    function buildIxSQL($value, $key, $table)
    {
        $sql = "select distinct $value,$key ";
        $sql .= " from " . $GLOBALS['Db2Schema'] . ".$table ";
        if ($this->notNull) {
            $sql .= " where $value is not null and $key is not null";
        }
        // echo "<BR/>" . __METHOD__ . __LINE__ . $table;
        return $sql;
    }

    function buildSQL($column, $table)
    {
        $sql = "select distinct $column ";
        $sql .= " from " . $GLOBALS['Db2Schema'] . ".$table ";
        if ($this->notNull) {
            $sql .= " where $column is not null";
        }
        // echo "<BR/>" . __METHOD__ . __LINE__ .$table;
        return $sql;
    }

    /**
     * Loads a 3 dimensional Array where the First is from 1 column and Second from a 2nd Column and Third from a 3rd column.
     *
     * @param string $first
     *            column used to supply the FIRST in the array
     * @param string $second
     *            column used to supply the SECOND in the array
     * @param string $third
     *            column used to supply the THIRD in the array
     * @param string $table
     *            Table Name
     * @param boolean $trace
     *            - print diags
     * @param string $predicate
     *            with this predicate
     * @param string $order
     *            order asc or desc
     * @return array
     */
    function loadTri($first = null, $second = null, $third = null, $table = null, $predicate = null, $order = 'asc')
    {
        $array = array();

        $sql = $this->buildTriSQL($first, $second, $third, $table);
        if ($predicate != null) {
            $sql .= " and $predicate ";
        }

        $sql .= " order by 1 $order ";

        $rs5 = sqlsrv_query($GLOBALS['conn'], $sql);
        if (! $rs5) {
            DbTable::displayErrorMessage($rs5, __CLASS__, __METHOD__, $sql);
        }

        while ($row = sqlsrv_fetch_both($rs5)) {
            $array[trim($row[$first])][trim($row[$second])] = trim(trim($row[$third]));
        }

        Trace::traceVariable($array, __METHOD__);
        return $array;
    }

    /**
     * Loads a 3 dimensional Array where the First is from 1 column and Second from a 2nd Column and Third from a 3rd column.
     *
     * @param string $first
     *            column used to supply the FIRST in the array
     * @param string $second
     *            column used to supply the SECOND in the array
     * @param string $third
     *            column used to supply the THIRD in the array
     * @param string $table
     *            Table Name
     * @param boolean $trace
     *            - print diags
     * @param string $predicate
     *            with this predicate
     * @param string $order
     *            order asc or desc
     * @return array
     */
    function loadQuad($first = null, $second = null, $third = null, $fourth = null, $table = null, $predicate = null, $order = 'asc')
    {
        $array = array();

        $sql = $this->buildQuadSQL($first, $second, $third, $fourth, $table);
        if ($predicate != null) {
            $sql .= " and $predicate ";
        }

        $sql .= " order by 1 $order ";

        $rs5 = sqlsrv_query($GLOBALS['conn'], $sql);
        if (! $rs5) {
            DbTable::displayErrorMessage($rs5, __CLASS__, __METHOD__, $sql);
        }

        while ($row = sqlsrv_fetch_both($rs5)) {
            $array[trim($row[$first])][trim($row[$second])][trim($row[$third])] = trim(trim($row[$fourth]));
        }

        Trace::traceVariable($array, __METHOD__);
        return $array;
    }


    /**
     * Loads a 2 dimensional Array where the First is from 1 column and Second produces an array of values from a 2nd Column.
     *
     * returms :
     * ('fruit' => array('apples','pears'),'colours'=>array('red','yellow'))
     *
     * @param string $first
     *            column used to supply the FIRST in the array
     * @param string $second
     *            column used to supply the SECOND in the array
     * @param string $table
     *            Table Name
     * @param boolean $trace
     *            - print diags
     * @param string $predicate
     *            with this predicate
     * @param string $order
     *            order asc or desc
     * @return array
     */
    function load2Dim($keyColumn = null, $valuesColumn = null, $table = null, $predicate = null, $order = 'asc')
    {
        $array = array();

        $sql = $this->buildIxSQL( $valuesColumn, $keyColumn, $table);
        if ($predicate != null) {
            $sql .= " and $predicate ";
        }

        $sql .= " order by 1 $order ";

        $rs = sqlsrv_query($GLOBALS['conn'], $sql);
        if (! $rs) {
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
        }

        $currentKey = null;

        while ($row = sqlsrv_fetch_both($rs)) {
                     $array[trim($row[$keyColumn])][] = trim($row[$valuesColumn]);
        }

        Trace::traceVariable($array, __METHOD__);
        return $array;
    }



    /**
     * Used internally - to build the SQL for the query for LoadQuad
     *
     * @param unknown_type $first
     * @param unknown_type $second
     * @param unknown_type $third
     * @param unknown_type $forth
     * @param unknown_type $table
     * @return string
     */
    private function buildQuadSQL($first, $second, $third, $forth, $table)
    {
        $sql = "select distinct $first,$second,$third,$forth ";
        $sql .= " from " . $GLOBALS['Db2Schema'] . ".$table ";
        $sql .= " where $first is not null and $second is not null and $third is not null and $forth is not null ";
        return $sql;
    }

    /**
     * Used internally - to build the SQL for the query for LoadTri
     *
     * @param unknown_type $first
     * @param unknown_type $second
     * @param unknown_type $third
     * @param unknown_type $table
     * @return string
     */
    private function buildTriSQL($first, $second, $third, $table)
    {
        $sql = "select distinct $first,$second,$third ";
        $sql .= " from " . $GLOBALS['Db2Schema'] . ".$table ";
        $sql .= " where $first is not null and $second is not null and $third is not null";
        return $sql;
    }

    function iterateVisible()
    {
        echo "<BR/>Class:" . __CLASS__ . " does not iterate.";
    }
}

?>