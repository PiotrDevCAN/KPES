<?php
namespace upes;

use itdq\DbTable;
use itdq\Loader;

/*
 *
 *
 */

class PesStatusAuditTable extends DbTable
{
    function returnAsArray($predicate=null,$withButtons=true){
        $sql  = " SELECT * ";
        $sql .= " FROM  " . $GLOBALS['Db2Schema'] . "." . $this->tableName. " as PSA ";
        $sql .= " WHERE 1=1 " ;
        $sql .= !empty($predicate) ? " AND  $predicate " : null ;

        $resultSet = $this->execute($sql);
        $resultSet ? null : die("SQL Failed");
        $allData = null;

        while(($row = db2_fetch_assoc($resultSet))==true){
            $testJson = json_encode($row);
            if(!$testJson){
                die('Failed JSON Encode');
                break; // It's got invalid chars in it that will be a problem later.
            }
            if($withButtons){
                $this->addGlyphicons($row);
            }
            $allData[]  = $row;
        }
        return $allData ;
    }

    static function insertRecord($cnum, $email_address, $account, $pesStatus, $pesClearedDate){
        $sql = " INSERT INTO " . $GLOBALS['Db2Schema'] . "." . AllTables::$PES_STATUS_AUDIT;
        $sql.= " (CNUM, EMAIL_ADDRESS, ACCOUNT, PES_STATUS, PES_CLEARED_DATE, UPDATER, UPDATED ) ";
        $sql.= " VALUES ";
        $sql.= " ('" . db2_escape_string($cnum) . "','" . db2_escape_string($email_address) . "','" . db2_escape_string($account) . "' ";
        $sql.= " ,'" . db2_escape_string($pesStatus) . "','" . db2_escape_string($pesClearedDate) .  "' ";
        $sql.= " ,'" . db2_escape_string($_SESSION['ssoEmail']) . "', CURRENT TIMESTAMP ";
        $sql.= " ) ";
        
        $rs = db2_exec($GLOBALS['conn'], $sql);
       
        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);  
            return false;
        }
        
        return true;
    }

}

