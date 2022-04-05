<?php
namespace upes;


use itdq\DbTable;
use itdq\Loader;

/*
 * CREATE TABLE UPES_DEV.PES_LEVELS ( PES_LEVEL_REF INTEGER NOT NULL GENERATED BY DEFAULT AS IDENTITY ( NO CYCLE ), ACCOUNT_ID INTEGER, PES_LEVEL CHAR(25) NOT NULL, PES_LEVEL_DESCRIPTION VARCHAR(50) ) IN USERSPACE1;
 * ALTER TABLE UPES_DEV.PES_LEVELS ADD CONSTRAINT PES_LEV_REF_PK PRIMARY KEY (PES_LEVEL_REF ) NOT ENFORCED;
 * ALTER TABLE UPES_DEV.PES_LEVELS ADD CONSTRAINT ACC_ID_PES_LEV_FK FOREIGN KEY (ACCOUNT_ID ) REFERENCES UPES_DEV.ACCOUNT ON DELETE CASCADE ENFORCED;
 *
 * ALTER TABLE "UPES_DEV"."PES_LEVELS" ADD COLUMN "RECHECK_YEARS" INTEGER;
 * ALTER TABLE "UPES_UT"."PES_LEVELS" ADD COLUMN "RECHECK_YEARS" INTEGER;
 * ALTER TABLE "UPES"."PES_LEVELS" ADD COLUMN "RECHECK_YEARS" INTEGER;
 *
 *
 */



class PesLevelTable extends DbTable
{
    function returnAsArray($predicate=null,$withButtons=true){
        $sql  = " SELECT '' as ACTION, A.ACCOUNT, A.ACCOUNT_ID,  PL.* ";
        $sql .= " FROM  " . $GLOBALS['Db2Schema'] . "." . $this->tableName. " as PL ";

        $sql .= " LEFT JOIN  " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT. " as A ";
        $sql .= " ON PL.ACCOUNT_ID = A.ACCOUNT_ID ";
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


    function addGlyphicons(&$row){

        $pesLevelRef = trim($row['PES_LEVEL_REF']);
        $pesLevel = trim($row['PES_LEVEL']);
        $pesLevelAccount = trim($row['ACCOUNT']);
        $pesLevelAccountId = trim($row['ACCOUNT_ID']);
        $recheckYears = trim($row['RECHECK_YEARS']);
        $description = trim($row['PES_LEVEL_DESCRIPTION']);

        $row['ACTION'] = "<button type='button' class='btn btn-primary btn-xs editPesLevel ' aria-label='Left Align' data-peslevelref='" .$pesLevelRef . "' data-peslevel='" .$pesLevel . "' data-peslevelaccount='" .$pesLevelAccount . "'  data-peslevelaccountid='" .$pesLevelAccountId . "' data-recheckyears='" .$recheckYears . "'  data-pesleveldescription='" .$description . "'  data-toggle='tooltip' title='Edit Pes Level' >
              <span class='glyphicon glyphicon-edit editPesLevel'  aria-hidden='true' data-peslevelref='" .$pesLevelRef . "' data-peslevel='" .$pesLevel . "' data-peslevelaccount='" .$pesLevelAccount . "' data-peslevelaccountid='" .$pesLevelAccountId . "'   data-recheckyears='" .$recheckYears . "'  data-pesleveldescription='" .$description . "' ></span>
              </button>";
        $row['ACTION'].= "&nbsp;";
        $row['ACTION'].= "<button type='button' class='btn btn-warning btn-xs deletePesLevel ' aria-label='Left Align' data-peslevelref='" .$pesLevelRef . "' data-peslevel='" .$pesLevel . "' data-peslevelaccount='" .$pesLevelAccount . "'  data-toggle='tooltip' title='Delete Pes Level'>
              <span class='glyphicon glyphicon-trash deletePesLevel' aria-hidden='true' data-peslevelref='" .$pesLevelRef . "' data-peslevel='" .$pesLevel . "' data-peslevelaccount='" .$pesLevelAccount . "'  ></span>
              </button>";
    }


    static function prepareJsonArraysForPesSelection(){
        $loader = new Loader();
        $pesLevels = $loader->loadIndexed('PES_LEVEL','PES_LEVEL_REF',AllTables::$PES_LEVELS);
        $pesDesc   = $loader->loadIndexed('PES_LEVEL_DESCRIPTION','PES_LEVEL_REF',AllTables::$PES_LEVELS);
        $pesByAccount = $loader->loadIndexed('ACCOUNT_ID','PES_LEVEL_REF',AllTables::$PES_LEVELS);
       // $accounts     = $loader->loadIndexed('ACCOUNT_ID','ACCOUNT',AllTables::$ACCOUNT);
        $detailedPesLevels = array();
        $pesLevelByAccount = array();

        foreach ($pesLevels as $pesLevelRef => $pesLevel) {
            $detailedPesLevels[$pesLevelRef] = $pesLevel . " (" . $pesDesc[$pesLevelRef] . ")";
        }

        foreach ($pesByAccount as $pesLevelRef=> $accountId){
            if(isset($detailedPesLevels[$pesLevelRef])){
                $option = new \stdClass();
                $option->id = $pesLevelRef;
                $option->text = $detailedPesLevels[$pesLevelRef];
                $pesLevelByAccount[$accountId][] = $option;
            }
        }

        return $pesLevelByAccount;
    }

}

