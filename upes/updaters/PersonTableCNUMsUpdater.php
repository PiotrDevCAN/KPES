<?php
namespace upes\updaters;

use itdq\DbTable;
use itdq\Loader;
use itdq\AuditTable;
use itdq\BluePagesSLAPHAPI;
use itdq\slack;
use upes\PersonTable;

class PersonTableCNUMsUpdater extends PersonTable
{
    protected $preparedCheckExistsStatements;
    protected $preparedUpdateSqlStatements;

	public $unknown = 'unknown';

    function prepareCheckExistsStatement($column){
        $sql = " SELECT count(*) AS EXISTS FROM "  . $GLOBALS['Db2Schema'] . "." . $this->tableName . " AS R ";
        $sql .= " WHERE R.".$column." = ? ";
        $this->preparedCheckExistsStatements[$column] = sqlsrv_prepare($GLOBALS['conn'], $sql);
        
        if(!$this->preparedCheckExistsStatements[$column]){
            DBTable::displayErrorMessage($this->preparedCheckExistsStatements[$column], __CLASS__, __METHOD__, $sql);
        }
        
        return $this->preparedCheckExistsStatements[$column];
    }
    
    function prepareUpdateSqlStatement($column){
        // $updateColumn = str_ireplace('_intranet', '', $column);
        $sql = " UPDATE " . $GLOBALS['Db2Schema'] . "." . $this->tableName . " AS R ";
        $sql .= " SET 
            R.CNUM = ?, 
            R.NOTES_ID = ?, 
            R.TRANSITION_CNUM = ?,
            R.KYNDRYL_EMAIL_ADDRESS = ?, 
            R.OCEAN_EMAIL_ADDRESS = ?, 
            R.OCEAN_NOTES_ID = ?, 
            R.IBM_CNUM = ?,
            R.IBM_EMAIL_ADDRESS = ?,
            R.IBM_NOTES_ID ?";  
        $sql .= " WHERE R.".$column." = ? ";
        $this->preparedUpdateSqlStatements[$column] = sqlsrv_prepare($GLOBALS['conn'], $sql);
        
        if(!$this->preparedUpdateSqlStatements[$column]){
            DBTable::displayErrorMessage($this->preparedUpdateSqlStatements[$column], __CLASS__, __METHOD__, $sql);
        }
        
        return $this->preparedUpdateSqlStatements[$column];
    }
    
    function updateExistingFoundRecord($intranetId, $details, $column){
        $this->validateDetails($intranetId, $details);
        
        $data = array(
            $details['notesId'], 
            $details['mail'],
            $intranetId
        );

        $result = sqlsrv_execute($this->preparedUpdateSqlStatements[$column], $data);
        if(!$result){
            DBTable::displayErrorMessage($result, __CLASS__, __METHOD__, "prepared sql statement");
        }
    }
    
    function updateExistingNotFoundRecord($intranetId, $column){
        $data = array(
            $this->unknown,
            $this->unknown,
            $intranetId
        );

        $result = sqlsrv_execute($this->preparedUpdateSqlStatements[$column], $data);
        if(!$result){
            DBTable::displayErrorMessage($result, __CLASS__, __METHOD__, "prepared sql statement");
        }
    }

    function validateDetails($intranetId, &$details){
        $requiredFields = array('mail','notesId');
        
        $mail    = isset($details['mail']) ? $details['mail'] : 'unknown' ;
        $notesId = isset($details['notesId']) ? BluePagesSLAPHAPI::cleanupNotesid($details['notesId']) : 'unknown' ;

        if($mail == 'unknown' or $notesId == 'unknown'){
            foreach ($requiredFields as $fieldName) {
                if(!isset($details[$fieldName])){
                    echo "<br/><span style='color:red'>Missing $fieldName for $intranetId</span>";
                }
            }
        }
        
        $details['mail'] = $mail;
        $details['notesId'] = $notesId;
    }

    function checkRecordExists($intranetId, $column){
        $data = array(trim($intranetId));
        
        $result = sqlsrv_execute($this->preparedCheckExistsStatements[$column], $data);
        if(!$result){
            DBTable::displayErrorMessage($result, __CLASS__, __METHOD__, "prepared sql statement");
        }
        $row = sqlsrv_fetch_array($this->preparedCheckExistsStatements[$column]);
        return $row['EXISTS']>0;
    }

    function recordFoundInBluepages($intranetId, $details, $column){
        if($this->checkRecordExists($intranetId, $column)){
            $this->updateExistingFoundRecord($intranetId, $details, $column);
        }
    }

	function recordNotFoundInBluepages($intranetId, $column){
        if($this->checkRecordExists($intranetId, $column)) {
            $this->updateExistingNotFoundRecord($intranetId, $column);
        }
    }

    /*
    function updateTableFromBluePages($intranetId, $allDetails, $column){
        if(isset($allDetails[$intranetId])){
            $this->recordFoundInBluepages($intranetId, $allDetails[$intranetId], $column);
        } else {
            
            echo "<span style='color:red'>Missing employee data from BP for $intranetId</span><br/>";

            $this->recordNotFoundInBluepages($intranetId, $column);
        }
    }
    */

    function updateTableFromBluePages($intranetId, $allDetails, $column){
        if(isset($allDetails[$intranetId])){
            $this->recordFoundInBluepages($intranetId, $allDetails[$intranetId], $column);
        } else {
            
            echo "<span style='color:red'>Missing employee data from BP for $intranetId</span><br/>";

            $this->recordNotFoundInBluepages($intranetId, $column);
        }
    }

    function updateTable($batchOfIds, $allDetails, $column){
		foreach ($batchOfIds as $intranetId){
            $this->updateTableFromBluePages($intranetId, $allDetails, $column);
        }
        sqlsrv_commit($GLOBALS['conn']);
	}

    /*
    function updateCNUMs($batchOfIds, $allDetails, $column){
		foreach ($batchOfIds as $intranetId){
            $this->updateCNUMsFromBluePages($intranetId, $allDetails, $column);
        }
        sqlsrv_commit($GLOBALS['conn']);
	}
    */

    function fetchPeopleList($predicate=null){
        $sql = " SELECT DISTINCT EMAIL_ADDRESS AS INTRANET_ID";
        $sql .= " FROM " . $GLOBALS['Db2Schema'] . "." . $this->tableName . " AS A ";
        $sql .= !empty($predicate) ? " WHERE 1=1 " . $predicate : null;

        $rs = sqlsrv_query($GLOBALS['conn'], $sql);

        if (! $rs) {
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
        }
        return $rs;
    }
}