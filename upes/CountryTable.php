<?php
namespace upes;

use itdq\DbTable;
use itdq\Loader;

/*
 *
 * CREATE TABLE "UPES".COUNTRY  ( COUNTRY CHAR(50) NOT NULL ,EMAIL_BODY_NAME CHAR(20) )
 * CREATE UNIQUE INDEX "UPES"."CountryIx" ON "UPES_DEV"."COUNTRY" ("COUNTRY" ASC);
 *
 * ALTER TABLE "UPES"."COUNTRY" RENAME COLUMN "INTERNATIONAL" TO "EMAIL_BODY_NAME";
 * ALTER TABLE "UPES"."COUNTRY" ALTER COLUMN "EMAIL_BODY_NAME" SET DATA TYPE CHAR(20);
 * ALTER TABLE "UPES"."COUNTRY" ALTER COLUMN "EMAIL_BODY_NAME" SET DEFAULT 'International'
 *
 *
 */



class CountryTable extends DbTable
{

    function returnAsArray($predicate=null,$withButtons=true){
        $sql  = " SELECT '' as ACTION, C.* ";
        $sql .= " FROM  " . $GLOBALS['Db2Schema'] . "." . $this->tableName. " as C ";
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
            $trimmedRow = array_map('trim', $row);
            if($withButtons){
                $this->addGlyphicons($trimmedRow);
            }
            $allData[]  = $trimmedRow;
        }
        return $allData ;
    }


    function addGlyphicons(&$row){
        $data = array();
        $country       = $row['COUNTRY'];
        $emailBodyName = $row['EMAIL_BODY_NAME'];
        $additionalDocs = $row['ADDITIONAL_APPLICATION_FORM'];

        $data['display'] = "<button type='button' class='btn btn-primary btn-xs editCountry ' aria-label='Left Align' data-country='" . $country . "'  data-toggle='tooltip' title='Edit Country' >
              <span class='glyphicon glyphicon-edit editCountryName'  aria-hidden='true' data-country='" . $country . "'  data-emailbodyname='" . $emailBodyName . "'  data-additionaldocs='" . $additionalDocs . "'  ></span>
              </button>&nbsp;" . $country;
        $data['sort'] = $country;
        $row['COUNTRY'] = $data;
    }


    static function getEmailBodyNameForCountry($country){
        $sql = " SELECT COUNTRY, EMAIL_BODY_NAME";
        $sql.= " FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$COUNTRY;
        $sql.= " WHERE COUNTRY='" . db2_escape_string($country). "' ";

        $rs = sqlsrv_query($GLOBALS['conn'], $sql);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
            throw new \Exception("Sql error in " . __METHOD__ );
        }

        $row = sqlsrv_fetch_array($rs);

        if(empty($row['EMAIL_BODY_NAME'])){
            throw new \Exception("No EMAIL_BODY_NAME found for Country:$country");
        }
        return array_map('trim', $row);
    }

    static function getAdditionalAttachmentsNameCountry($country){
        $sql = " SELECT COUNTRY, ADDITIONAL_APPLICATION_FORM ";
        $sql.= " FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$COUNTRY;
        $sql.= " WHERE COUNTRY='" . db2_escape_string($country). "' ";

        $rs = sqlsrv_query($GLOBALS['conn'], $sql);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
            throw new \Exception("Sql error in " . __METHOD__ );
        }

        $row = sqlsrv_fetch_array($rs);

        return array_map('trim', $row);
    }



}