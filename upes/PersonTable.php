<?php
namespace upes;

use itdq\DbTable;
use itdq\Loader;
use itdq\AuditTable;
use itdq\slack;

class PersonTable extends DbTable
{
    function returnAsArray($predicate=null,$withButtons=true){
        $sql  = " SELECT '' as ACTION, P.*, PL.PES_LEVEL as PES_LEVEL_TXT, PL.PES_LEVEL_DESCRIPTION ";
        $sql .= " FROM  " . $GLOBALS['Db2Schema'] . "." . $this->tableName. " as P ";
        $sql .= " LEFT JOIN " .  $GLOBALS['Db2Schema'] . "." . AllTables::$PES_LEVELS . " as PL ";
        $sql .= " ON P.PES_LEVEL = PL.PES_LEVEL_REF ";
        $sql .= " WHERE 1=1 " ;
        $sql .= !empty($predicate) ? " AND  $predicate " : null ;

        $resultSet = $this->execute($sql);
        $resultSet ? null : die("SQL Failed");
        $allData = null;

        while(($row = sqlsrv_fetch_array($resultSet))==true){
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


    function addGlyphicons(&$row){
        $row['PES_LEVEL'] = $row['PES_LEVEL_TXT'];
        unset($row['PES_LEVEL_TXT']);
        unset($row['PES_LEVEL_DESCRIPTION']);

    }

    static function prepareJsonKnownEmailLookup(){
        $allEmail =  array();

        $sql = " Select distinct lower(EMAIL_ADDRESS) as EMAIL_ADDRESS from " . $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON;
        $rs = sqlsrv_query($GLOBALS['conn'], $sql);

        while(($row = sqlsrv_fetch_array($rs))==true){
            $allEmail[] = trim($row['EMAIL_ADDRESS']);
        }
        return $allEmail;
    }

    static function prepareJsonUpesrefToNameMapping(){
        $allNames =  array();

        $sql = " Select distinct UPES_REF, FULL_NAME from " . $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON;
        $rs = sqlsrv_query($GLOBALS['conn'], $sql);

        while(($row = sqlsrv_fetch_array($rs))==true){
            $allNames[$row['UPES_REF']] = trim($row['FULL_NAME']);
        }
        return $allNames;
    }


    static function getEmailFromUpesref($upesref){
        $sql = " SELECT EMAIL_ADDRESS FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON;
        $sql.= " WHERE UPES_REF = '" . htmlspecialchars(strtoupper(trim($upesref))) . "' ";
        $sql.= " FETCH FIRST 1 ROW ONLY ";

        $resultSet = sqlsrv_query($GLOBALS['conn'], $sql);
        if(!$resultSet){
            DbTable::displayErrorMessage($resultSet, __CLASS__, __METHOD__, $sql);
            return false;
        }

        $row = sqlsrv_fetch_array($resultSet);

        return $row ? trim($row['EMAIL_ADDRESS']) : false;
    }

    static function getNamesFromUpesref($upesref){
        $sql = " SELECT case when P.PASSPORT_FIRST_NAME is null then P.FULL_NAME else CONCAT (P.PASSPORT_FIRST_NAME, ' ', P.PASSPORT_LAST_NAME) end as FULL_NAME ";
        $sql.= " FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON . " as P ";
        $sql.= " WHERE P.UPES_REF = '" . htmlspecialchars(strtoupper(trim($upesref))) . "' ";

        $resultSet = sqlsrv_query($GLOBALS['conn'], $sql);
        if(!$resultSet){
            DbTable::displayErrorMessage($resultSet, __CLASS__, __METHOD__, $sql);
            return false;
        }
        $row = array();
        $row = sqlsrv_fetch_array($resultSet);
        $names = array_map('trim', $row);

        return $names;
    }

    static function getUpesrefFromCNUM($cnum){
        $sql = " SELECT UPES_REF FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON;
        $sql.= " WHERE UPPER(CNUM) = '" . htmlspecialchars(strtoupper(trim($cnum))) . "' ";
        $sql.= " FETCH FIRST 1 ROW ONLY ";

        $resultSet = sqlsrv_query($GLOBALS['conn'], $sql);
        if(!$resultSet){
            DbTable::displayErrorMessage($resultSet, __CLASS__, __METHOD__, $sql);
            return false;
        }

        $row = sqlsrv_fetch_array($resultSet);

        return $row ? trim($row['UPES_REF']) : false;
    }

    static function getUpesrefFromEmail($email){
        $sql = " SELECT UPES_REF FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON;
        $sql.= " WHERE LOWER(EMAIL_ADDRESS) = '" . htmlspecialchars(strtolower(trim($email))) . "' ";
        $sql.= " FETCH FIRST 1 ROW ONLY ";

        $resultSet = sqlsrv_query($GLOBALS['conn'], $sql);
        if(!$resultSet){
            DbTable::displayErrorMessage($resultSet, __CLASS__, __METHOD__, $sql);
            return false;
        }

        $row = sqlsrv_fetch_array($resultSet);

        return $row ? trim($row['UPES_REF']) : false;
    }

    function setPesPassportNames($upesref,$passportFirstname=null,$passportSurname=null){

        $sql = " UPDATE " . $GLOBALS['Db2Schema'] . "." . $this->tableName;
        $sql.= " SET PASSPORT_FIRST_NAME=";
        $sql.= !empty($passportFirstname) ? "'" . htmlspecialchars($passportFirstname) . "', " : " null, ";
        $sql.= " PASSPORT_SURNAME=";
        $sql.= !empty($passportSurname) ? "'" . htmlspecialchars($passportSurname) . "'  " : " null ";
        $sql.= " WHERE UPES_REF='" . htmlspecialchars($upesref) . "' ";

        $rs = sqlsrv_query($GLOBALS['conn'],$sql);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, 'prepared sql');
            throw new \Exception("Failed to update Passport Names: $passportFirstname  / $passportSurname for $upesref");
        }

        return true;
    }

    static function setCnumsToFound($arrayOfCnum){
        $cnumString = implode("','", $arrayOfCnum);

        $cnumString = "('" . $cnumString . "') ";

        $sql = " UPDATE ";
        $sql.= $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON;
        $sql.= " SET BLUEPAGES_STATUS='" . PersonRecord::BLUEPAGES_STATUS_FOUND . "' ";
        $sql.= " WHERE BLUEPAGES_STATUS is null AND CNUM in " . $cnumString;

        $rs = sqlsrv_query($GLOBALS['conn'], $sql);
        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
        }
    }

    static function setCnumsToNotFound($arrayOfCnum){
        $cnumString = implode("','", $arrayOfCnum);
        $cnumString = "('" . $cnumString . "') ";
        $sql = " UPDATE ";
        $sql.= $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON;
        $sql.= " SET BLUEPAGES_STATUS='" . PersonRecord::BLUEPAGES_STATUS_NOT_FOUND . "' ";
        $sql.= " WHERE CNUM in " . $cnumString;

        $rs = sqlsrv_query($GLOBALS['conn'], $sql);
        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
        }
    }

    static function setCnumsStatusPriorToLeave($arrayOfCnum){
        $cnumString = implode("','", $arrayOfCnum);
        $cnumString = "('" . $cnumString . "') ";
        $sql = " UPDATE ";
        $sql.= $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON . " AS AP ";
        $sql.= " SET AP.PES_STATUS_PRIOR_LEAVE=AP.PES_STATUS ";
        $sql.= " WHERE UPES_REF in (";
        $sql.= "   SELECT UPES_REF ";
        $sql.= "   FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON . " AS P ";
        $sql.= "   WHERE P.CNUM in " . $cnumString;
        $sql.= " ) ";

        $rs = sqlsrv_query($GLOBALS['conn'], $sql);
        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
        }
    }

    static function setCnumsToLeftIBM($arrayOfCnum){
        $cnumString = implode("','", $arrayOfCnum);
        $cnumString = "('" . $cnumString . "') ";
        $sql = " UPDATE ";
        $sql.= $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON . " AS AP ";
        $sql.= " SET AP.PES_STATUS='" . AccountPersonRecord::PES_STATUS_LEFT_IBM . "' ";
        $sql.= " WHERE UPES_REF in (";
        $sql.= "   SELECT UPES_REF ";
        $sql.= "   FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON . " AS P ";
        $sql.= "   WHERE P.CNUM in " . $cnumString;
        $sql.= " ) ";

        $rs = sqlsrv_query($GLOBALS['conn'], $sql);
        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
        }
    }

    private static function recordPesStatusPriorToLeftIBM($arrayOfCnum){
        $accountPersonTable = new AccountPersonTable(AllTables::$ACCOUNT_PERSON);
        $slack = new slack();

        $cnumString = implode("','", $arrayOfCnum);
        $cnumString = "('" . $cnumString . "') ";
        $sql = " SELECT P.UPES_REF, AP.ACCOUNT_ID, A.ACCOUNT, AP.PES_STATUS, P.FULL_NAME, P.CNUM  ";
        $sql.= " FROM " .  $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON . " AS AP ";
        $sql.= " LEFT JOIN ";
        $sql.= $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON . " AS P ";
        $sql.= " ON AP.UPES_REF = P.UPES_REF ";
        $sql.= " LEFT JOIN ";
        $sql.= $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT . " AS A ";
        $sql.= " ON AP.ACCOUNT_ID = A.ACCOUNT_ID ";
        $sql.= " WHERE P.UPES_REF in (";
        $sql.= "   SELECT P2.UPES_REF ";
        $sql.= "   FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON . " AS P2 ";
        $sql.= "   WHERE P2.CNUM in " . $cnumString;
        $sql.= " ) ";

        $rs = sqlsrv_query($GLOBALS['conn'], $sql);
        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
        }

        while(($row = sqlsrv_fetch_array($rs))==true){
            $row = array_map('trim',$row);
            $accountPersonTable->savePesComment($row['UPES_REF'],$row['ACCOUNT_ID'], "PES Status was " . $row['PES_STATUS'] . " prior to leaving");
            $slack->sendMessageToChannel($row['FULL_NAME'] . "(" . $row['CNUM'] . ") -  PES Status on Account " . $row['ACCOUNT'] . " was " . $row['PES_STATUS'] . " prior to leaving", slack::CHANNEL_UPES_AUDIT);
        }
    }

    static function FlagAsLeftIBM($arrayOfCnum){

        self::recordPesStatusPriorToLeftIBM($arrayOfCnum);
        $cnumString = implode("','", $arrayOfCnum);
        $cnumString = "('" . $cnumString . "') ";

        $sql = " SELECT P.CNUM, P.FULL_NAME, A.ACCOUNT, AP.PES_STATUS, AP.PES_CLEARED_DATE, AP.PES_RECHECK_DATE ";
        $sql.= " FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON . " as P ";
        $sql.= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON . " as AP ";
        $sql.= " ON P.UPES_REF = AP.UPES_REF ";
        $sql.= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT . " as A ";
        $sql.= " ON A.ACCOUNT_ID =  AP.ACCOUNT_ID ";
        $sql.= " WHERE CNUM in " . $cnumString;

        $rs = sqlsrv_query($GLOBALS['conn'], $sql);
        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
        }
        $detailsOfLeavers = array();
        while(($row = sqlsrv_fetch_array($rs))==true){
            $trimmedRow = array_map('trim',$row);
            $detailsOfLeavers[] = $trimmedRow;
        }

        PesEmail::notifyPesTeamLeaversFound($detailsOfLeavers);
        
        // $chunkedCnum = array_chunk($arrayOfCnum, 100);
        // foreach ($chunkedCnum as $key => $cnumList){
        //     PersonTable::setCnumsToNotFound($cnumList);
        //     PersonTable::setCnumsToLeftIBM($cnumList);
        // }
    }
}