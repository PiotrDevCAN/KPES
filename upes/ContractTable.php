<?php
namespace upes;

use itdq\DbTable;
use itdq\Loader;

/*
 *
 * CREATE TABLE UPES.CONTRACT ( CONTRACT_ID INTEGER NOT NULL GENERATED BY DEFAULT AS IDENTITY ( START WITH 5 INCREMENT BY 5 NO CYCLE ), ACCOUNT_ID INTEGER NOT NULL WITH DEFAULT 0, CONTRACT CHAR(50) NOT NULL WITH DEFAULT 'contract' ) IN USERSPACE1;
 * ALTER TABLE UPES.CONTRACT ADD CONSTRAINT CON_ID_PK PRIMARY KEY (CONTRACT_ID ) ENFORCED;
 * ALTER TABLE UPES.CONTRACT ADD CONSTRAINT ACC_CON_FK FOREIGN KEY (ACCOUNT_ID ) REFERENCES UPES.ACCOUNT ON DELETE CASCADE ENFORCED;
 *
 */



class ContractTable extends DbTable
{
    function returnAsArray($predicate=null,$withButtons=true){
        $sql  = " SELECT '' as ACTION, A.ACCOUNT, C.* ";
        $sql .= " FROM  " . $_SESSION['Db2Schema'] . "." . $this->tableName. " as C ";

        $sql .= " LEFT JOIN  " . $_SESSION['Db2Schema'] . "." . AllTables::$ACCOUNT. " as A ";
        $sql .= " ON C.ACCOUNT_ID = A.ACCOUNT_ID ";
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
        $accountId  = trim($row['ACCOUNT_ID']);
        $contractId = trim($row['CONTRACT_ID']);
        $contract   = trim($row['CONTRACT']);

        $row['ACTION'] = "<button type='button' class='btn btn-primary btn-xs editContractName ' aria-label='Left Align' data-contractid='" .$contractId . "' data-contract='" . $contract . "' data-accountid='" . $accountId . "'  data-toggle='tooltip' title='Edit Account Name' >
              <span class='glyphicon glyphicon-edit editAccountName'  aria-hidden='true' data-contractid='" .$contractId . "' data-contract='" . $contract . "' data-accountid='" . $accountId . "'   ></span>
              </button>";
        $row['ACTION'].= "&nbsp;";
        $row['ACTION'].= "<button type='button' class='btn btn-warning btn-xs deleteContract ' aria-label='Left Align' data-contractid='" .$contractId . "' data-contract='" . $contract . "' data-accountid='" . $accountId . "'    data-toggle='tooltip' title='Delete Account'>
              <span class='glyphicon glyphicon-trash deleteAccount' aria-hidden='true' data-contractid='" .$contractId . "' data-contract='" . $contract . "' data-accountid='" . $accountId . "'  ></span>
              </button>";
    }


    static function prepareArrayForContractDropDown(){
        $loader = new Loader();
        $allAccounts = $loader->loadIndexed('ACCOUNT','ACCOUNT_ID',AllTables::$ACCOUNT);
        $allContracts = $loader->loadIndexed('CONTRACT','CONTRACT_ID',AllTables::$CONTRACT);
        $allContractAccountMapping = $loader->loadIndexed('ACCOUNT_ID','CONTRACT_ID',AllTables::$CONTRACT);

        $contractsAgainstAccount = array();

        foreach ($allContractAccountMapping as $contractId => $accountId) {
            $contractsAgainstAccount[$allAccounts[$accountId]][$contractId] = $allContracts[$contractId];
        }

        ksort($contractsAgainstAccount);

        foreach ($contractsAgainstAccount as $account => $arrayOfContracts) {
            asort($contractsAgainstAccount[$account]);
        }
        return $contractsAgainstAccount;
    }

    static function prepareJsonObjectForContractsSelect(){
//         {
//             "text": "Group 1",
//             "children" : [
//             {
//                 "id": 1,
//                 "text": "Option 1.1"
//             },
//             {
//                 "id": 2,
//                 "text": "Option 1.2"
//             }
//             ]
//         },
        $contractsAgainstAccount = self::prepareArrayForContractDropDown();

        $selectObjects = array();
        foreach ($contractsAgainstAccount as $account => $contractsArray) {
            $accountContracts = array();
            foreach ($contractsArray as $contractId => $contract) {
                $option = new \stdClass();
                $option->id = $contractId;
                $option->text = $contract;
                $accountContracts[] = $option;
            }

            $accountObj = new \stdClass();
            $accountObj->text = $account;
            $accountObj->children = $accountContracts;
            $selectObjects[] = $accountObj;
        }
        return json_encode($selectObjects);
    }

    static function prepareJsonObjectMappingContractToAccount(){
        $loader = new Loader();
        return $loader->loadIndexed('ACCOUNT_ID','CONTRACT_ID',AllTables::$CONTRACT);
    }
}

