<?php
namespace itdq;

use itdq\DbTable;
use itdq\AllItdqTables;

class AuditTable extends DbTable {
    const RECORD_TYPE_AUDIT = 'Audit';
    const RECORD_TYPE_DETAILS = 'Details';
    const RECORD_TYPE_REVALIDATION = 'Revalidation';

    static function audit($statement,$type='Details'){
        if(property_exists('itdq\AllItdqTables','AUDIT')){

            $table = new AuditTable(AllItdqTables::$AUDIT);
            $statement = $table->truncateValueToFitColumn($statement, 'DATA');

            $sql = " INSERT INTO " . $GLOBALS['Db2Schema'] . "." . AllItdqTables::$AUDIT;
            $sql . " ('TIMESTAMP','EMAIL_ADDRESS','DATA','TYPE') ";
            $sql .= " VALUES ";
            $sql .= " ( CURRENT TIMESTAMP, '" . htmlspecialchars($_SESSION['ssoEmail']) . "','" . htmlspecialchars($statement) . "','" . htmlspecialchars($type) . "' )";

            $rs = sqlsrv_query($GLOBALS['conn'],$sql);

            if(!$rs){
                DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
            }
        }
    }

    static function removeExpired($auditLifeSpan=null,$detailsLifeSpan=null){
        if(property_exists('itdq\AllItdqTables','AUDIT')){
            $auditLifeSpan = empty($auditLifeSpan) ? $_SESSION['AuditLife'] : $auditLifeSpan;
            $detailsLifeSpan = empty($detailsLifeSpan) ? $_SESSION['AuditDetailsLife'] : $detailsLifeSpan;

            $sql  = " DELETE FROM " . $GLOBALS['Db2Schema'] . "." . AllItdqTables::$AUDIT ;
            $sql .= " WHERE " ;
            $sql .= " (TYPE='" . self::RECORD_TYPE_AUDIT . "' AND \"TIMESTAMP\" < ( CURRENT TIMESTAMP - " . htmlspecialchars($auditLifeSpan) . " )) ";
            $sql .= " OR " ;
            $sql .= " (TYPE='" . self::RECORD_TYPE_DETAILS . "' AND \"TIMESTAMP\" < ( CURRENT TIMESTAMP - " . htmlspecialchars($detailsLifeSpan) . " ))  ";

            $rs = sqlsrv_query($GLOBALS['conn'], $sql);

            if(!$rs){
                DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
                return false;
            }

            return true;
        }
    }



    static function returnAsArray($fromRecord=null, $length=null, $predicate=null, $orderBy = null){
        $fromRecord = !empty($fromRecord) ? $fromRecord : 1;
        $fromRecord = $fromRecord < 1 ? 1 : $fromRecord;
        $length = !empty($length) ? $length : 10;
        $length = $length < 1 ? 1 : $length;
        $end = $fromRecord + $length;

        $sql = " SELECT TIMESTAMP, EMAIL_ADDRESS, DATA, TYPE FROM ( ";
        $sql .= " SELECT ROW_NUMBER() OVER( ";
        $sql.= $orderBy;
        $sql.= " ) AS rownum,A.* FROM " . $GLOBALS['Db2Schema'] . "." . AllItdqTables::$AUDIT . " AS A ";
        $sql .= " WHERE 1=1 ";
        $sql .= " AND TIMESTAMP >= (CURRENT TIMESTAMP - 31 days) ";
        $sql .= !empty($predicate)   ? "  $predicate " : null;
        $sql .= " ) as tmp ";
        $sql .= " WHERE ROWNUM >= $fromRecord AND ROWNUM < " .  $end ;

        set_time_limit(0);

        // echo $sql;

        $rs = sqlsrv_query($GLOBALS['conn'],$sql);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
        }

        $data = array();
        $data['rows'] = array();

        while(($row = sqlsrv_fetch_array($rs))==true){
            $trimmedRow = array_map('trim', $row);
            $data['rows'][] = $trimmedRow;
        }
        set_time_limit(60);
        $data['sql'] = $sql;
        return $data;
     }

     static function recordsFiltered($predicate){
         $sql = " SELECT count(*) as recordsFiltered FROM " . $GLOBALS['Db2Schema'] . "." . AllItdqTables::$AUDIT . " AS A ";
         $sql .= " WHERE 1=1 ";
         $sql .= " AND TIMESTAMP >= (CURRENT TIMESTAMP - 31 days) ";
         $sql .= !empty($predicate)   ? "  $predicate " : null;

         $rs = sqlsrv_query($GLOBALS['conn'],$sql);

         if(!$rs){
             DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
         }

         $row = sqlsrv_fetch_array($rs);

         return $row['RECORDSFILTERED'];


     }

     static function totalRows($type=null){
         $sql = " SELECT count(*) as totalRows FROM " . $GLOBALS['Db2Schema'] . "." . AllItdqTables::$AUDIT . " AS A ";
         $sql .= " WHERE 1=1 ";
         $sql .= " AND TIMESTAMP >= (CURRENT TIMESTAMP - 31 days) ";
         $sql .= $type=='Revalidation' ? " AND TYPE='Revalidation' " : null;
         $rs = sqlsrv_query($GLOBALS['conn'],$sql);

         if(!$rs){
             DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
         }

         $row = sqlsrv_fetch_array($rs);

         return $row['TOTALROWS'];
     }

}