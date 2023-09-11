<?php
namespace upes;

use itdq\DbTable;
use itdq\Loader;
use \DateTime;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use itdq\xls;
use itdq\AuditTable;
use itdq\slack;

/*
 *
 *ALTER TABLE "UPES_UT"."ACCOUNT_PERSON" ALTER COLUMN "PES_RECHECK_DATE" SET DATA TYPE DATE;
 *
 *ALTER TABLE "UPES"."ACCOUNT_PERSON" ADD COLUMN "NI_EVIDENCE" CHAR(10);
 *
 */

class AccountPersonTable extends DbTable {

    use xls;

    private $xlsDateFormat = 'd/m/Y';
    private $db2DateFormat = 'Y-m-d';

    protected $preparedStageUpdateStmts;
    protected $preparedTrackerInsert;
    protected $preparedGetPesCommentStmt;
    protected $preparedProcessStatusUpdate;
    protected $preparedGetProcessingStatusStmt;
    protected $preparedResetForRecheck;

    const PES_TRACKER_RECORDS_ACTIVE       = 'Active';
    const PES_TRACKER_RECORDS_ACTIVE_PLUS  = 'Active Plus';
    const PES_TRACKER_RECORDS_NOT_ACTIVE   = 'Not Active';
    const PES_TRACKER_RECORDS_ALL          = 'All';
    const PES_TRACKER_RECORDS_ACTIVE_REQUESTED = 'Active Requested';
    const PES_TRACKER_RECORDS_ACTIVE_PROVISIONAL = 'Active Provisional';

    const PES_TRACKER_RETURN_RESULTS_AS_ARRAY      = 'array';
    const PES_TRACKER_RETURN_RESULTS_AS_RESULT_SET = 'resultSet';

    const PES_TRACKER_STAGES =  array(
        'CONSENT',
        'RIGHT_TO_WORK',
        'PROOF_OF_ID',
        'PROOF_OF_RESIDENCY',
        'CREDIT_CHECK',
        'FINANCIAL_SANCTIONS',
        'CRIMINAL_RECORDS_CHECK',
        'PROOF_OF_ACTIVITY',
        'QUALIFICATIONS',
        'CIFAS',
        'DIRECTORS',
        'MEDIA',
        'MEMBERSHIP',
        'NI_EVIDENCE'
    );

    const CHASER_LEVEL_ONE = 'One';
    const CHASER_LEVEL_TWO = 'Two';
    const CHASER_LEVEL_THREE = 'Three';

    const LEVEL_MAP = array('One'=>'(L1)','Two'=>'(L2)','Three'=>'(L3)');
    const LEVEL_MAP_NUMBER = array('One'=>'1','Two'=>'2','Three'=>'3');

    const PROCESS_STATUS_PES = 'PES';
    const PROCESS_STATUS_USER = 'User';
    const PROCESS_STATUS_REQUESTOR = 'Requestor';
    const PROCESS_STATUS_CRC = 'CRC';
    const PROCESS_STATUS_UNKOWN = 'Unknown';

    public $lastSelectSql;

    static function preparePesEventsStmt($records='Active', $upesRef=null, $accountId=null, $start=0, $length=10, $predicate=null){
        switch (trim($records)){
            case self::PES_TRACKER_RECORDS_ACTIVE :
                $pesStatusPredicate = "  AP.PES_STATUS in('" . AccountPersonRecord::PES_STATUS_STARTER_REQUESTED . "','" . AccountPersonRecord::PES_STATUS_CANCEL_REQ .  "','" . AccountPersonRecord::PES_STATUS_RECHECK_PROGRESSING .  "','" . AccountPersonRecord::PES_STATUS_PES_PROGRESSING. "','" . AccountPersonRecord::PES_STATUS_PROVISIONAL. "','" . AccountPersonRecord::PES_STATUS_MOVER. "') ";
                break;
            case self::PES_TRACKER_RECORDS_ACTIVE_PLUS :
                $pesStatusPredicate = "  AP.PES_STATUS in('" . AccountPersonRecord::PES_STATUS_STARTER_REQUESTED . "','" . AccountPersonRecord::PES_STATUS_CANCEL_REQ . "','" . AccountPersonRecord::PES_STATUS_CANCEL_CONFIRMED . "','" . AccountPersonRecord::PES_STATUS_PES_PROGRESSING. "','" . AccountPersonRecord::PES_STATUS_PROVISIONAL. "','" . AccountPersonRecord::PES_STATUS_RECHECK_REQ .  "','" . AccountPersonRecord::PES_STATUS_RECHECK_PROGRESSING . "','" . AccountPersonRecord::PES_STATUS_REMOVED. "','" . AccountPersonRecord::PES_STATUS_CLEARED. "','" . AccountPersonRecord::PES_STATUS_MOVER. "') ";
                break;
            case self::PES_TRACKER_RECORDS_ACTIVE_REQUESTED :
                $pesStatusPredicate = "  AP.PES_STATUS in('" . AccountPersonRecord::PES_STATUS_STARTER_REQUESTED . "','" . AccountPersonRecord::PES_STATUS_CANCEL_REQ . "','" . AccountPersonRecord::PES_STATUS_PES_PROGRESSING. "','" . AccountPersonRecord::PES_STATUS_STAGE_1. "','" . AccountPersonRecord::PES_STATUS_STAGE_2. "','" . AccountPersonRecord::PES_STATUS_RECHECK_REQ . "','" . AccountPersonRecord::PES_STATUS_RECHECK_PROGRESSING .  "','" . AccountPersonRecord::PES_STATUS_MOVER. "') ";
                break;
            case self::PES_TRACKER_RECORDS_ACTIVE_PROVISIONAL :
                $pesStatusPredicate = "  AP.PES_STATUS in('" . AccountPersonRecord::PES_STATUS_PROVISIONAL. "') ";
                break;
            case self::PES_TRACKER_RECORDS_NOT_ACTIVE :
                $pesStatusPredicate = " AP.PES_STATUS not in ('" . AccountPersonRecord::PES_STATUS_STARTER_REQUESTED . "','" . AccountPersonRecord::PES_STATUS_CANCEL_REQ .  "','" . AccountPersonRecord::PES_STATUS_RECHECK_PROGRESSING . "','" . AccountPersonRecord::PES_STATUS_PES_PROGRESSING. "','" . AccountPersonRecord::PES_STATUS_PROVISIONAL. "')  ";
                $pesStatusPredicate.= " AND AP.PROCESSING_STATUS_CHANGED > current timestamp - 31 days  ";
                break;
            case self::PES_TRACKER_RECORDS_ALL :
                $pesStatusPredicate = " 1=1 ";
                break;
            default:
                $pesStatusPredicate = 'pass a parm muppet ';
                break;
        }
      
        $sql = " SELECT P.CNUM ";
        $sql.= ", P.EMAIL_ADDRESS ";
        $sql.= ", P.PASSPORT_FIRST_NAME ";
        $sql.= ", P.PASSPORT_LAST_NAME ";
        $sql.= ", case when P.PASSPORT_FIRST_NAME is null then P.FULL_NAME else P.PASSPORT_FIRST_NAME CONCAT ' ' CONCAT P.PASSPORT_LAST_NAME end as FULL_NAME  ";
        $sql.= ", P.COUNTRY ";
        $sql.= ", P.IBM_STATUS ";
        $sql.= ", AP.UPES_REF ";
        $sql.= ", AP.ACCOUNT_ID ";
        $sql.= ", AP.PES_DATE_REQUESTED ";
        $sql.= ", AP.PES_REQUESTOR ";
        $sql.= ", AP.CONSENT ";
        $sql.= ", AP.RIGHT_TO_WORK ";
        $sql.= ", AP.PROOF_OF_ID ";
        $sql.= ", AP.PROOF_OF_RESIDENCY ";
        $sql.= ", AP.PROOF_OF_RESIDENCY AS RAG_STATUS ";
        $sql.= ", AP.CREDIT_CHECK ";
        $sql.= ", AP.FINANCIAL_SANCTIONS ";
        $sql.= ", AP.CRIMINAL_RECORDS_CHECK ";
        $sql.= ", AP.PROOF_OF_ACTIVITY ";
        $sql.= ", AP.QUALIFICATIONS ";
        $sql.= ", AP.CIFAS ";
        $sql.= ", AP.DIRECTORS ";
        $sql.= ", AP.MEDIA ";
        $sql.= ", AP.MEMBERSHIP ";
        $sql.= ", AP.NI_EVIDENCE ";
        $sql.= ", AP.PROCESSING_STATUS ";
        $sql.= ", ADD_HOURS(AP.PROCESSING_STATUS_CHANGED, 1) AS PROCESSING_STATUS_CHANGED ";
        $sql.= ", AP.DATE_LAST_CHASED ";
        $sql.= ", AP.PES_STATUS ";
        $sql.= ", AP.PES_STATUS_DETAILS ";
        $sql.= ", AP.COMMENT ";
        $sql.= ", AP.PRIORITY ";
        $sql.= ", AP.COUNTRY_OF_RESIDENCE ";
        $sql.= ", A.ACCOUNT ";
        $sql.= ", A.ACCOUNT_TYPE ";
        $sql.= ", C.CONTRACT ";
        $sql.= ", PL.PES_LEVEL ";
        $sql.= ", PL.PES_LEVEL_DESCRIPTION ";

        $sql.= " FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON . " as P ";
        $sql.= " left join " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON . " as AP ";
        $sql.= " ON P.UPES_REF = AP.UPES_REF ";
        $sql.= " left join " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT . " as A ";
        $sql.= " ON AP.ACCOUNT_ID = A.ACCOUNT_ID ";
        $sql.= " left join " . $GLOBALS['Db2Schema'] . "." . AllTables::$CONTRACT . " as C ";
        $sql.= " ON AP.CONTRACT_ID = C.CONTRACT_ID ";
        $sql.= " left join " . $GLOBALS['Db2Schema'] . "." . AllTables::$PES_LEVELS . " as PL ";
        $sql.= " ON AP.PES_LEVEL = PL.PES_LEVEL_REF ";

        $sql.= " WHERE 1=1 ";
        $sql.= " and (AP.UPES_REF is not null or ( AP.UPES_REF is null  AND AP.PES_STATUS_DETAILS is null )) "; // it has a tracker record
        $sql.= " AND " . $pesStatusPredicate;
        $sql.= !empty($predicate) ? " $predicate " : null;
        $sql.= !empty($upesRef) ? " AND AP.UPES_REF='" . htmlspecialchars($upesRef)  . "' " : null;
        $sql.= !empty($accountId) ? " AND AP.ACCOUNT_ID='" . htmlspecialchars($accountId)  . "' " : null;

        if ($length != '-1') {
            $sql.= " LIMIT " . $length . ' OFFSET ' . $start;
        }
        return $sql;
    }

    static function preparePesEventsCountStmt($records='Active', $upesRef=null, $accountId=null, $predicate=null){
        switch (trim($records)){
            case self::PES_TRACKER_RECORDS_ACTIVE :
                $pesStatusPredicate = "  AP.PES_STATUS in('" . AccountPersonRecord::PES_STATUS_STARTER_REQUESTED . "','" . AccountPersonRecord::PES_STATUS_CANCEL_REQ .  "','" . AccountPersonRecord::PES_STATUS_RECHECK_PROGRESSING .  "','" . AccountPersonRecord::PES_STATUS_PES_PROGRESSING. "','" . AccountPersonRecord::PES_STATUS_PROVISIONAL. "','" . AccountPersonRecord::PES_STATUS_MOVER. "') ";
                break;
            case self::PES_TRACKER_RECORDS_ACTIVE_PLUS :
                $pesStatusPredicate = "  AP.PES_STATUS in('" . AccountPersonRecord::PES_STATUS_STARTER_REQUESTED . "','" . AccountPersonRecord::PES_STATUS_CANCEL_REQ . "','" . AccountPersonRecord::PES_STATUS_CANCEL_CONFIRMED . "','" . AccountPersonRecord::PES_STATUS_PES_PROGRESSING. "','" . AccountPersonRecord::PES_STATUS_PROVISIONAL. "','" . AccountPersonRecord::PES_STATUS_RECHECK_REQ .  "','" . AccountPersonRecord::PES_STATUS_RECHECK_PROGRESSING . "','" . AccountPersonRecord::PES_STATUS_REMOVED. "','" . AccountPersonRecord::PES_STATUS_CLEARED. "','" . AccountPersonRecord::PES_STATUS_CLEARED_AMBER. "','" . AccountPersonRecord::PES_STATUS_MOVER. "') ";
                break;
            case self::PES_TRACKER_RECORDS_ACTIVE_REQUESTED :
                $pesStatusPredicate = "  AP.PES_STATUS in('" . AccountPersonRecord::PES_STATUS_STARTER_REQUESTED . "','" . AccountPersonRecord::PES_STATUS_CANCEL_REQ . "','" . AccountPersonRecord::PES_STATUS_PES_PROGRESSING. "','" . AccountPersonRecord::PES_STATUS_STAGE_1. "','" . AccountPersonRecord::PES_STATUS_STAGE_2. "','" . AccountPersonRecord::PES_STATUS_RECHECK_REQ . "','" . AccountPersonRecord::PES_STATUS_RECHECK_PROGRESSING .  "','" . AccountPersonRecord::PES_STATUS_MOVER. "') ";
                break;
            case self::PES_TRACKER_RECORDS_ACTIVE_PROVISIONAL :
                $pesStatusPredicate = "  AP.PES_STATUS in('" . AccountPersonRecord::PES_STATUS_PROVISIONAL. "') ";
                break;
            case self::PES_TRACKER_RECORDS_NOT_ACTIVE :
                $pesStatusPredicate = " AP.PES_STATUS not in ('" . AccountPersonRecord::PES_STATUS_STARTER_REQUESTED . "','" . AccountPersonRecord::PES_STATUS_CANCEL_REQ .  "','" . AccountPersonRecord::PES_STATUS_RECHECK_PROGRESSING . "','" . AccountPersonRecord::PES_STATUS_PES_PROGRESSING. "','" . AccountPersonRecord::PES_STATUS_PROVISIONAL. "')  ";
                $pesStatusPredicate.= " AND AP.PROCESSING_STATUS_CHANGED > current timestamp - 31 days  ";
                break;
            case self::PES_TRACKER_RECORDS_ALL :
                $pesStatusPredicate = " 1=1 ";
                break;
            default:
                $pesStatusPredicate = 'pass a parm muppet ';
                break;
        }
      
        $sql = " SELECT COUNT(*) AS COUNTER ";
        $sql.= " FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON . " as P ";
        $sql.= " left join " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON . " as AP ";
        $sql.= " ON P.UPES_REF = AP.UPES_REF ";
        $sql.= " left join " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT . " as A ";
        $sql.= " ON AP.ACCOUNT_ID = A.ACCOUNT_ID ";
        $sql.= " left join " . $GLOBALS['Db2Schema'] . "." . AllTables::$PES_LEVELS . " as PL ";
        $sql.= " ON AP.PES_LEVEL = PL.PES_LEVEL_REF ";

        $sql.= " WHERE 1=1 ";
        $sql.= " and (AP.UPES_REF is not null or ( AP.UPES_REF is null  AND AP.PES_STATUS_DETAILS is null )) "; // it has a tracker record
        $sql.= " AND " . $pesStatusPredicate;
        $sql.= !empty($predicate) ? " $predicate " : null;
        $sql.= !empty($upesRef) ? " AND AP.UPES_REF='" . htmlspecialchars($upesRef)  . "' " : null;
        $sql.= !empty($accountId) ? " AND AP.ACCOUNT_ID='" . htmlspecialchars($accountId)  . "' " : null;
        return $sql;
    }

    static function returnPesEventsTable($records='Active',$returnResultsAs='array',$upesRef=null, $accountId=null, $start=0, $length=10){

        $sql = self::preparePesEventsStmt($records, $upesRef, $accountId, $start, $length);

        $rs = sqlsrv_query($GLOBALS['conn'], $sql);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
            throw new \Exception('Error in ' . __METHOD__ . " running $sql");
        }
        
        switch ($returnResultsAs) {
            case self::PES_TRACKER_RETURN_RESULTS_AS_ARRAY:
                $report = array();
                while(($row = sqlsrv_fetch_array($rs))==true){
                    $report[] = array_map('trim',$row);
                }
               
                return $report;
            break;
            case self::PES_TRACKER_RETURN_RESULTS_AS_RESULT_SET:
                return $rs;
            default:
                return false;
                break;
        }
    }

    function buildTable($records='Active'){
        $allRows = self::returnPesEventsTable($records,self::PES_TRACKER_RETURN_RESULTS_AS_ARRAY);
        $table = "<table id='pesTrackerTable' class='table table-striped table-bordered table-condensed '  style='width:100%'>
		<thead>
		<tr class='' ><th>Person Details</th><th>Account</th><th>Requestor</th>
		<th>Consent Form</th>
		<th>Proof or Right to Work</th>
		<th>Proof of ID</th>
		<th>Proof of Residence</th>
        <th>RAG Status</th>
		<th>Credit Check</th>
		<th>Financial Sanctions</th>
		<th>Criminal Records Check</th>
		<th>Proof of Activity</th>
		<th>Qualifications</th>
        <th>CIFAS</th>
		<th>Directors</th>
		<th>Media</th>
		<th>Membership</th>
		<th>NI Evidence</th>
		<th>Process Status</th><th width='15px'>PES Status</th><th>Comment</th></tr>
		<tr class='searchingRow wrap'>
		<td>Email Address</td>
		<td class='shortSearch'>Account</td>
		<td>Requestor</td>
		<td class='nonSearchable'>Consent</td>
		<td class='nonSearchable'>Right to Work</td>
		<td class='nonSearchable'>ID</td>
		<td class='nonSearchable'>Residence</td>
        <td class='nonSearchable'>RAG</td>
		<td class='nonSearchable'>Credit Check</td>
		<td class='nonSearchable'>Financial Sanctions</td>
		<td class='nonSearchable'>Criminal Records Check</td>
		<td class='nonSearchable'>Proof of Activity</td>
		<td class='nonSearchable'>Qualifications</td>
		<td class='nonSearchable'>Directors</td>
		<td class='nonSearchable'>Media</td>
		<td class='nonSearchable'>Membership</td>
		<td class='nonSearchable'>NI Evidence</td>
		<td>Process Status</td><td class='shortSearch'>PES Status</td><td>Comment</td></tr>
		</thead>
		<tbody>";
        $today = new \DateTime();
        if (count($allRows)>0) {
            foreach ($allRows as $row){
                $row = array_map('trim', $row);
                $date = \DateTime::createFromFormat('Y-m-d', $row['PES_DATE_REQUESTED']);
                $age  = !empty($row['PES_DATE_REQUESTED']) ?  $date->diff($today)->format('%R%a days') : null ;
                // $age = !empty($row['PES_DATE_REQUESTED']) ? $interval->format('%R%a days') : null;
                $cnum = $row['CNUM'];
                $upesref = $row['UPES_REF'];
                $accountId = $row['ACCOUNT_ID'];
                $accountType = $row['ACCOUNT_TYPE'];
                $account = $row['ACCOUNT'];
                $fullName = $row['FULL_NAME'];
                $emailaddress = $row['EMAIL_ADDRESS'];
                $requestor = $row['PES_REQUESTOR'];
                $requested = $row['PES_DATE_REQUESTED'];
                $requestedObj = \DateTime::createFromFormat('Y-m-d', $requested);
                $requestedDisplay = $requestedObj ? $requestedObj->format('d-m-Y') : $requested;

                $formattedIdentityField = self::formatEmailFieldOnTracker($row);
                $originalRequestor = $row['PES_REQUESTOR'];
                $requestor = strlen($row['PES_REQUESTOR']) > 20 ? substr($row['PES_REQUESTOR'],0,20) . "....." : $row['PES_REQUESTOR'];
                
                $table .= "<tr class='".$upesref." personDetails' data-upesref='".$upesref."' data-accountid='".$accountId."' data-accounttype='".$accountType."' data-account='".$account."' data-fullname='".$fullName."' data-emailaddress='".$emailaddress."'  data-requestor='".$originalRequestor."'   >
                <td class='formattedEmailTd'>
                <div class='formattedEmailDiv'>".$formattedIdentityField."</div>
                </td>
                <td>".$row['ACCOUNT']."<br/>".$row['PES_LEVEL']."<br/>".$row['PES_LEVEL_DESCRIPTION']."</td>
                <td>".$requestor."<br/><small>".$requestedDisplay."<br/>".$age."</small></td>";

                foreach (self::PES_TRACKER_STAGES as $stage) {
                    $stageValue         = !empty($row[$stage]) ? trim($row[$stage]) : 'TBD';
                    $stageAlertValue    = self::getAlertClassForPesStage($stageValue);
                    $table .= "<td class='nonSearchable'>".self::getButtonsForPesStage($stageValue, $stageAlertValue, $stage, $upesref, $accountId)."</td>";
                }
                $table .= "<td class='nonSearchable'>
                <div class='alert alert-info text-center pesProcessStatusDisplay' role='alert' data-upesacc='".$upesref.$accountId."' >".self::formatProcessingStatusCell($row)."</div>
                <div class='text-center'>
                <span style='white-space:nowrap' >
                <a class='btn btn-xs btn-info btnProcessStatusChange accessPes accessCdi' data-processstatus='".self::PROCESS_STATUS_PES."' data-toggle='tooltip' data-placement='top' title='With PES Team' ><i class='fas fa-users'></i></a>
                <a class='btn btn-xs btn-info btnProcessStatusChange accessPes accessCdi' data-processstatus='".self::PROCESS_STATUS_USER."' data-toggle='tooltip' data-placement='top' title='With Applicant' ><i class='fas fa-user'></i></a>
                <a class='btn btn-xs btn-info btnProcessStatusChange accessPes accessCdi' data-processstatus='".self::PROCESS_STATUS_REQUESTOR."' data-toggle='tooltip' data-placement='top' title='With Requestor' ><i class='fas fa-male'></i><i class='fas fa-female'></i></a>
                <a class='btn btn-xs btn-info btnProcessStatusChange accessPes accessCdi' data-processstatus='".self::PROCESS_STATUS_CRC."' data-toggle='tooltip' data-placement='top' title='Awaiting CRC'><i class='fas fa-gavel'></i></a>
                <button class='btn btn-info btn-xs btnProcessStatusChange accessPes accessCdi' data-processstatus='".self::PROCESS_STATUS_UNKOWN."' data-toggle='tooltip'  title='Unknown'><span class='glyphicon glyphicon-erase' ></span></button>
                </span>";
                
                $dateLastChased = !empty($row['DATE_LAST_CHASED']) ? DateTime::createFromFormat('Y-m-d', $row['DATE_LAST_CHASED']) : null;
                
                // $dateLastChasedFormatted = !empty($row['DATE_LAST_CHASED']) ? $dateLastChased->format('d M y') : null;
                // $dateLastChasedWithLevel = !empty($row['DATE_LAST_CHASED']) ? $dateLastChasedFormatted . $this->extractLastChasedLevelFromComment($row['COMMENT']) : $dateLastChasedFormatted;
                
                $dateLastChasedFormatted = !empty($row['DATE_LAST_CHASED']) ? $dateLastChased->format('d/m/y') : null;
                $dateLastChasedWithLevel = !empty($row['DATE_LAST_CHASED']) ? $dateLastChasedFormatted . $this->extractLastChasedLevelAsNumberFromComment($row['COMMENT']) : $dateLastChasedFormatted;
                
                $dateLastChasedWithLevelText = !empty($dateLastChasedWithLevel) ? $dateLastChasedWithLevel : 'Last Chased';

                $alertClass = !empty($row['DATE_LAST_CHASED']) ? self::getAlertClassForPesChasedDate($row['DATE_LAST_CHASED']) : 'alert-info';
                
                $table .= "<div class='alert ".$alertClass."'>
                <p class='pesDateLastChased'>".$dateLastChasedWithLevelText."</p>
                </div>
                <span style='white-space:nowrap'>
                <a class='btn btn-xs btn-info  btnChaser accessPes accessCdi' data-chaser='".self::CHASER_LEVEL_ONE."' data-toggle='tooltip' data-placement='top' title='Chaser One' ><i>1</i></a>
                <a class='btn btn-xs btn-info  btnChaser accessPes accessCdi' data-chaser='".self::CHASER_LEVEL_TWO."' data-toggle='tooltip' data-placement='top' title='Chaser Two' ><i>2</i></a>
                <a class='btn btn-xs btn-info  btnChaser accessPes accessCdi' data-chaser='".self::CHASER_LEVEL_THREE."' data-toggle='tooltip' data-placement='top' title='Chaser Three'><i>3</i></a>
                </span>
                </div>
                </td>
                <td class='nonSearchable pesStatusTd' data-upesacc='".$upesref.$accountId."' data-upesref='".$upesref."'>".AccountPersonRecord::getPesStatusWithButtons($row)."</td>
                <td class='pesCommentsTd'><textarea rows='3' cols='20'  data-upesref='".$upesref."' data-accountid='".$accountId."' data-accounttype='".$accountType."'></textarea><br/>
                <button class='btn btn-default btn-xs btnPesSaveComment accessPes accessCdi' data-setpesto='Yes' data-toggle='tooltip' data-placement='top' title='Save Comment' ><span class='glyphicon glyphicon-save' ></span></button>
                <div class='pesComments' data-upesacc='".$upesref.$accountId."' data-upesref='".$upesref."'><small>".$row['COMMENT']."</small></div>
                </td>
                </tr>";
            }
        }
        $table .= "</tbody></table>";
		return $table;
    }

    static function recordsFiltered($records='Active',$returnResultsAs='array',$upesRef=null, $accountId=null, $predicate=null){
        $sql = self::preparePesEventsCountStmt($records, $upesRef, $accountId, $predicate);

        $rs = sqlsrv_query($GLOBALS['conn'],$sql);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
        }

        $row = sqlsrv_fetch_array($rs);

        // return $row['RECORDSFILTERED'];
        return $row['COUNTER'];
    }

    static function totalRows($records='Active',$returnResultsAs='array',$upesRef=null, $accountId=null){
        $sql = self::preparePesEventsCountStmt($records, $upesRef, $accountId);

        $rs = sqlsrv_query($GLOBALS['conn'],$sql);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
        }

        $row = sqlsrv_fetch_array($rs);

        // return $row['TOTALROWS'];
        return $row['COUNTER'];
    }

    function returnAsArray($records='Active',$returnResultsAs='array',$upesRef=null, $accountId=null, $start=0, $length=10, $predicate=null){
        
        $sql = self::preparePesEventsStmt($records, $upesRef, $accountId, $start, $length, $predicate);
        
        $resultSet = $this->execute($sql);
        $resultSet ? null : die("SQL Failed");
        $allData = array();
        $allData['data'] = array();

        /*
        while(($row = sqlsrv_fetch_array($resultSet))==true){
            $testJson = json_encode($row);
            if(!$testJson){
                die('Failed JSON Encode');
                break; // It's got invalid chars in it that will be a problem later.
            }
            $trimmedRow = array_map('trim', $row);
            // if($withButtons){
            //     $this->addGlyphicons($trimmedRow);
            // }
            $allData[]  = $trimmedRow;
        }
        */

        $today = new \DateTime();
        while(($row = sqlsrv_fetch_array($resultSet))==true){
            $row = array_map('trim',$row);
            
            $upesref = $row['UPES_REF'];
            $accountId = $row['ACCOUNT_ID'];
            $accountType = $row['ACCOUNT_TYPE'];
            $account = $row['ACCOUNT'];
            $fullName = $row['FULL_NAME'];
            $emailaddress = $row['EMAIL_ADDRESS'];
            $contract = $row['CONTRACT'];

            $formattedIdentityField = self::formatEmailFieldOnTracker($row);
            $cellContent = "<td class='formattedEmailTd'>
                <div class='formattedEmailDiv'>".$formattedIdentityField."</div>
            </td>";
            $row['PERSON_DETAILS'] = array('display'=>$cellContent, 'sort'=>false);

            $accountText = !empty($account) ? $account : "<span style='color: red;'></span>";
            $cellContent = "<td>".$accountText."<br/>".$row['PES_LEVEL']."<br/>".$row['PES_LEVEL_DESCRIPTION']."</td>";
            $row['ACCOUNT_DETAILS'] = array('display'=>$cellContent, 'sort'=>false);

            $contractText = !empty($contract) ? $contract : "<span style='color: red;'></span>";
            $cellContent = "<td>".$contractText."</td>";
            $row['CONTRACT_DETAILS'] = array('display'=>$cellContent, 'sort'=>false);

            $originalRequestor = $row['PES_REQUESTOR'];
            $requestor = strlen($row['PES_REQUESTOR']) > 20 ? substr($row['PES_REQUESTOR'],0,20) . "....." : $row['PES_REQUESTOR'];
            
            $requestor = $row['PES_REQUESTOR'];
            $requested = $row['PES_DATE_REQUESTED'];
            $requestedObj = \DateTime::createFromFormat('Y-m-d', $requested);
            $requestedDisplay = $requestedObj ? $requestedObj->format('d-m-Y') : $requested;

            $date = \DateTime::createFromFormat('Y-m-d', $row['PES_DATE_REQUESTED']);
            $age  = !empty($row['PES_DATE_REQUESTED']) ?  $date->diff($today)->format('%R%a days') : null ;
            
            $cellContent = "<td>".$requestor."<br/><small>".$requestedDisplay."<br/>".$age."</small></td>";
            $row['REQUESTOR'] = array('display'=>$cellContent, 'sort'=>false);

            foreach (self::PES_TRACKER_STAGES as $stage) {
                $stageValue = !empty($row[$stage]) ? trim($row[$stage]) : 'TBD';
                $stageAlertValue = self::getAlertClassForPesStage($stageValue);
                $cellContent = "<td class='nonSearchable'>".self::getButtonsForPesStage($stageValue, $stageAlertValue, $stage, $upesref, $accountId, $accountType, $account, $fullName, $emailaddress, $originalRequestor)."</td>";
                $row[$stage] = array('display'=>$cellContent, 'sort'=>false);
            }

            $dateLastChased = !empty($row['DATE_LAST_CHASED']) ? DateTime::createFromFormat('Y-m-d', $row['DATE_LAST_CHASED']) : null;
            
            $dateLastChasedFormatted = !empty($row['DATE_LAST_CHASED']) ? $dateLastChased->format('d/m/y') : null;
            $dateLastChasedWithLevel = !empty($row['DATE_LAST_CHASED']) ? $dateLastChasedFormatted . $this->extractLastChasedLevelAsNumberFromComment($row['COMMENT']) : $dateLastChasedFormatted;
            
            $dateLastChasedWithLevelText = !empty($dateLastChasedWithLevel) ? $dateLastChasedWithLevel : 'Last Chased';
            $alertClass = !empty($row['DATE_LAST_CHASED']) ? self::getAlertClassForPesChasedDate($row['DATE_LAST_CHASED']) : 'alert-info';
            
            $cellContent = "<td class='nonSearchable'>
            <div class='alert alert-info text-center pesProcessStatusDisplay' role='alert' data-upesacc='".$upesref.$accountId."' >".self::formatProcessingStatusCell($row)."</div>
            <div class='text-center'>
            <span style='white-space:nowrap' data-upesref='".$upesref."' data-accountid='".$accountId."' data-accounttype='".$accountType."' data-account='".$account."' data-fullname='".$fullName."' data-emailaddress='".$emailaddress."'  data-requestor='".$originalRequestor."'>
            <a class='btn btn-xs btn-info btnProcessStatusChange accessPes accessCdi' data-processstatus='".self::PROCESS_STATUS_PES."' data-toggle='tooltip' data-placement='top' title='With PES Team' ><i class='fas fa-users'></i></a>
            <a class='btn btn-xs btn-info btnProcessStatusChange accessPes accessCdi' data-processstatus='".self::PROCESS_STATUS_USER."' data-toggle='tooltip' data-placement='top' title='With Applicant' ><i class='fas fa-user'></i></a>
            <a class='btn btn-xs btn-info btnProcessStatusChange accessPes accessCdi' data-processstatus='".self::PROCESS_STATUS_REQUESTOR."' data-toggle='tooltip' data-placement='top' title='With Requestor' ><i class='fas fa-male'></i><i class='fas fa-female'></i></a>
            <a class='btn btn-xs btn-info btnProcessStatusChange accessPes accessCdi' data-processstatus='".self::PROCESS_STATUS_CRC."' data-toggle='tooltip' data-placement='top' title='Awaiting CRC'><i class='fas fa-gavel'></i></a>
            <button class='btn btn-info btn-xs btnProcessStatusChange accessPes accessCdi' data-processstatus='".self::PROCESS_STATUS_UNKOWN."' data-toggle='tooltip'  title='Unknown'><span class='glyphicon glyphicon-erase' ></span></button>
            </span>
            <div class='alert ".$alertClass."'>
            <p class='pesDateLastChased'>".$dateLastChasedWithLevelText."</p>
            </div>
            <span style='white-space:nowrap' data-upesref='".$upesref."' data-accountid='".$accountId."' data-accounttype='".$accountType."' data-account='".$account."' data-fullname='".$fullName."' data-emailaddress='".$emailaddress."'  data-requestor='".$originalRequestor."'>
            <a class='btn btn-xs btn-info  btnChaser accessPes accessCdi' data-chaser='".self::CHASER_LEVEL_ONE."' data-toggle='tooltip' data-placement='top' title='Chaser One' ><i>1</i></a>
            <a class='btn btn-xs btn-info  btnChaser accessPes accessCdi' data-chaser='".self::CHASER_LEVEL_TWO."' data-toggle='tooltip' data-placement='top' title='Chaser Two' ><i>2</i></a>
            <a class='btn btn-xs btn-info  btnChaser accessPes accessCdi' data-chaser='".self::CHASER_LEVEL_THREE."' data-toggle='tooltip' data-placement='top' title='Chaser Three'><i>3</i></a>
            </span>
            </div>
            </td>";
            $row['PROCESS_STATUS'] = array('display'=>$cellContent, 'sort'=>false);
		    
            $cellContent = "<td class='nonSearchable pesStatusTd' data-upesacc='".$upesref.$accountId."' data-upesref='".$upesref."'>".AccountPersonRecord::getPesStatusWithButtons($row)."</td>";
            $row['PES_STATUS'] = array('display'=>$cellContent, 'sort'=>false);
		    
            $cellContent = "<td class='pesCommentsTd'><textarea rows='3' cols='20'  data-upesref='".$upesref."' data-accountid='".$accountId."' data-accounttype='".$accountType."'></textarea><br/>
            <button class='btn btn-default btn-xs btnPesSaveComment accessPes accessCdi' data-setpesto='Yes' data-toggle='tooltip' data-placement='top' title='Save Comment' ><span class='glyphicon glyphicon-save' ></span></button>
            <div class='pesComments' data-upesacc='".$upesref.$accountId."' data-upesref='".$upesref."'><small>".$row['COMMENT']."</small></div>
            </td>";
            $row['COMMENT'] = array('display'=>$cellContent, 'sort'=>false);
            $allData['data'][]  = $row;
        }

        $allData['sql'] = $sql;

        return $allData;
    }
    
    function extractLastChasedLevelFromComment($comment){
        $findChasedComment = strpos($comment, 'Automated PES Chaser Level');
        $level = substr($comment, $findChasedComment+27,6);
        $level = " (" . substr($level,0,strpos($level," ")) . ")";   
        // return self::LEVEL_MAP[$level];
        return $level;
    }

    function extractLastChasedLevelAsNumberFromComment($comment){
        $findChasedComment = strpos($comment, 'Automated PES Chaser Level');
        $level = substr($comment, $findChasedComment+27,6);
        $level = substr($level,0,strpos($level," "));
        if(array_key_exists($level, self::LEVEL_MAP_NUMBER)) {
            return " <b>(" . self::LEVEL_MAP_NUMBER[$level] . ")</b>";
        } else {
            return " <b>Unknown</b>";
        }
    }

    function displayTable($records='Active Initiated'){
        ?>
        <div class='container-fluid' >
        <div class='col-sm-10 col-sm-offset-1'>
          <form class="form-horizontal">
  			<div class="form-group">
    			<label class="control-label col-sm-1" for="pesTrackerTableSearch">Table Search:</label>                
    			<div class="col-sm-3" >
                <input type="text" id="pesTrackerTableSearch" placeholder="Search"/>
      			<br/>
				</div>
    			<label class="control-label col-sm-1" for="pesRecordFilter">Records:</label>
    			<div class="col-sm-4" >
    			<div class="btn-group" role="group" aria-label="Record Selection">
  					<button type="button" role='button' name='pesRecordFilter' class="btn btn-sm btn-info btnRecordSelection active" data-pesrecords='<?=AccountPersonTable::PES_TRACKER_RECORDS_ACTIVE_REQUESTED?>'    data-toggle='tooltip'  title='Active Record in Initiated or Requested status'     >Requested</button>
					<button type="button" role='button' name='pesRecordFilter' class="btn btn-sm btn-info btnRecordSelection "       data-pesrecords='<?=AccountPersonTable::PES_TRACKER_RECORDS_ACTIVE_PROVISIONAL?>'  data-toggle='tooltip'  title='Active Records in Provisional Clearance status' >Provisional</button>
  					<button type="button" role='button' name='pesRecordFilter' class="btn btn-sm btn-info btnRecordSelection "       data-pesrecords='<?=AccountPersonTable::PES_TRACKER_RECORDS_ACTIVE?>'              data-toggle='tooltip'  title='Active Records'     >Active</button>
  					<button type="button" role='button' name='pesRecordFilter' class="btn btn-sm btn-info btnRecordSelection "       data-pesrecords='<?=AccountPersonTable::PES_TRACKER_RECORDS_ACTIVE_PLUS?>'         data-toggle='tooltip'  title='Active+ Records'     >Active+</button>
  					<button type="button" role='button' name='pesRecordFilter' class="btn btn-sm btn-info btnRecordSelection"        data-pesrecords='<?=AccountPersonTable::PES_TRACKER_RECORDS_NOT_ACTIVE?>'          data-toggle='tooltip'  title='Recently Closed'  >Recent</button>
				</div>
				</div>

    			<label class="control-label col-sm-1" for="pesPriorityFilter">Filters:</label>
    			<div class="col-sm-2" >
  				<span style='white-space:nowrap' id='pesPriorityFilter' >
  				<button class='btn btn-sm btn-danger  btnSelectPriority accessPes accessCdi' data-pespriority='1'  data-toggle='tooltip'  title='Filter on High'  type='button' onclick='return false;'><span class='glyphicon glyphicon-king' ></span></button>
            	<button class='btn btn-sm btn-warning btnSelectPriority accessPes accessCdi' data-pespriority='2'  data-toggle='tooltip'  title='Filter on Medium' type='button' onclick='return false;'><span class='glyphicon glyphicon-knight' ></span></button>
            	<button class='btn btn-sm btn-success btnSelectPriority accessPes accessCdi' data-pespriority='3'  data-toggle='tooltip'  title='Filter on Low' type='button' onclick='return false;'><span class='glyphicon glyphicon-pawn' ></span></button>
            	<button class='btn btn-sm btn-info    btnSelectPriority accessPes accessCdi' data-pespriority='0'  data-toggle='tooltip'  title='Filter off' type='button' onclick='return false;'><span class='glyphicon glyphicon-ban-circle' ></span></button>
            	<br/><br/>
            	<a class="btn btn-sm btn-info  btnSelectProcess accessPes accessCdi" 		data-pesprocess='PES' data-toggle="tooltip" data-placement="top" title="With PES Team" ><i class="fas fa-users"></i></a>
                <a class="btn btn-sm btn-info  btnSelectProcess accessPes accessCdi" 		data-pesprocess='User' data-toggle="tooltip" data-placement="top" title="With Applicant" ><i class="fas fa-user"></i></a>
                <a class="btn btn-sm btn-info  btnSelectProcess accessPes accessCdi" 		data-pesprocess='Requestor' data-toggle="tooltip" data-placement="top" title="With Requestor" ><i class="fas fa-male"></i><i class="fas fa-female"></i></a>
                <a class="btn btn-sm btn-info   btnSelectProcess accessPes accessCdi" 	    data-pesprocess='CRC' data-toggle="tooltip" data-placement="top" title="Awaiting CRC"><i class="fas fa-gavel"></i></a>
                <button class='btn btn-info btn-sm  btnSelectProcess accessPes accessCdi'   data-pesprocess='Unknown' data-toggle="tooltip"  title="Status Unknown" type='button' onclick='return false;'><span class="glyphicon glyphicon-erase" ></span></button>
              	</span>
              	</div>
              	<div class="col-sm-1"  >
              	<span style='white-space:nowrap' id='pesDownload' >
                    <a class='btn btn-sm btn-link accessBasedBtn accessPes accessCdi trackerExtract' data-trackertype='<?=AccountPersonTable::PES_TRACKER_RECORDS_ACTIVE_REQUESTED?>' href='/dn_pesTrackerEmail.php' target='_blank'><i class="glyphicon glyphicon-envelope"></i> PES Tracker(Requested)</a>
                    <a class='btn btn-sm btn-link accessBasedBtn accessPes accessCdi trackerExtract' data-trackertype='<?=AccountPersonTable::PES_TRACKER_RECORDS_ACTIVE_PROVISIONAL?>' href='/dn_pesTrackerProvisionalEmail.php' target='_blank'><i class="glyphicon glyphicon-envelope"></i> PES Tracker(Provisional)</a>
                    <a class='btn btn-sm btn-link accessBasedBtn accessPes accessCdi trackerExtract' data-trackertype='<?=AccountPersonTable::PES_TRACKER_RECORDS_ACTIVE?>' href='/dn_pesTrackerActiveEmail.php' target='_blank'><i class="glyphicon glyphicon-envelope"></i> PES Tracker(Active)</a>                    
                    <a class='btn btn-sm btn-link accessBasedBtn accessPes accessCdi trackerExtract' data-trackertype='<?=AccountPersonTable::PES_TRACKER_RECORDS_ACTIVE_PLUS?>' href='/dn_pesTrackerActivePlusEmail.php' target='_blank'><i class="glyphicon glyphicon-envelope"></i> PES Tracker(Active+)</a>
                    <a class='btn btn-sm btn-link accessBasedBtn accessPes accessCdi trackerExtract' data-trackertype='<?=AccountPersonTable::PES_TRACKER_RECORDS_NOT_ACTIVE?>' href='/dn_pesTrackerRecentEmail.php' target='_blank'><i class="glyphicon glyphicon-envelope"></i> PES Tracker(Recent)</a>				
                </span>
                <span style='white-space:nowrap; display:none;' id='pesDownload2' >
                    <a class='btn btn-sm btn-link accessBasedBtn accessPes accessCdi' href='/dn_pesTracker.php'><i class="glyphicon glyphicon-download-alt"></i> PES Tracker</a>
                    <a class='btn btn-sm btn-link accessBasedBtn accessPes accessCdi' href='/dn_pesTrackerRecent.php'><i class="glyphicon glyphicon-download-alt"></i> PES Tracker(Recent)</a>
                    <a class='btn btn-sm btn-link accessBasedBtn accessPes accessCdi' href='/dn_pesTrackerActivePlus.php'><i class="glyphicon glyphicon-download-alt"></i> PES Tracker(Active+)</a>
				</span>
            	</div>
  			</div>
		  </form>
		  </div>
		</div>
		<div id='pesTrackerTableDiv' class='center-block' width='100%'>
            <table id='pesTrackerTable' class='table table-striped table-bordered table-condensed '  style='width:100%'>
                <thead>
                    <tr class='' >
                    <th>Person Details</th>
                    <th>Account</th>
                    <th>Contract</th>
                    <th>Requestor</th>
                    <th >Consent Form</th>
                    <th>Proof or Right to Work</th>
                    <th>Proof of ID</th>
                    <th>Proof of Residence</th>
                    <th>Credit Check</th>
                    <th>Financial Sanctions</th>
                    <th>Criminal Records Check</th>
                    <th>Proof of Activity</th>
                    <th>Qualifications</th>
                    <th>CIFAS</th>
                    <th>Directors</th>
                    <th>Media</th>
                    <th>Membership</th>
                    <th>NI Evidence</th>
                    <th>Process Status</th><th width='15px'>PES Status</th><th>Comment</th></tr>
                    <tr class='searchingRow wrap'>
                        <td>Email Address</td>
                        <td class='shortSearch'>Account</td>
                        <td class='shortSearch'>Contract</td>
                        <td>Requestor</td>
                        <td class='nonSearchable'>Consent</td>
                        <td class='nonSearchable'>Right to Work</td>
                        <td class='nonSearchable'>ID</td>
                        <td class='nonSearchable'>Residence</td>
                        <td class='nonSearchable'>Credit Check</td>
                        <td class='nonSearchable'>Financial Sanctions</td>
                        <td class='nonSearchable'>Criminal Records Check</td>
                        <td class='nonSearchable'>Proof of Activity</td>
                        <td class='nonSearchable'>Qualifications</td>
                        <td class='nonSearchable'>CIFAS</td>
                        <td class='nonSearchable'>Directors</td>
                        <td class='nonSearchable'>Media</td>
                        <td class='nonSearchable'>Membership</td>
                        <td class='nonSearchable'>NI Evidence</td>
                        <td>Process Status</td><td class='shortSearch'>PES Status</td><td>Comment</td>
                    </tr>
                </thead>
            </table>
		</div>
		<?php
    }

    function setPesStageValue($upesref,$account_id, $stage,$stageValue){
        $data = array($stageValue,$account_id, $upesref);
        $preparedStmt = $this->prepareStageUpdate($stage, $data);
        
        $rs = sqlsrv_execute($preparedStmt);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, 'prepared sql');
            throw new \Exception("Failed to update PES Stage: $stage to $stageValue for $upesref on $account_id ");
        }
        return true;
    }

    function prepareStageUpdate($stage, $data){
//         if(!empty($_SESSION['preparedStageUpdateStmts'][strtoupper(htmlspecialchars($stage))] )) {
//             return $_SESSION['preparedStageUpdateStmts'][strtoupper(htmlspecialchars($stage))];
//         }
        $sql = " UPDATE " . $GLOBALS['Db2Schema'] . "." . $this->tableName;
        $sql.= " SET " . strtoupper(htmlspecialchars($stage)) . " = ? ";
        $sql.= " WHERE ACCOUNT_ID= ? and UPES_REF=? ";

        $this->preparedSelectSQL = $sql;

        $preparedStmt = sqlsrv_prepare($GLOBALS['conn'], $sql, $data);

        if($preparedStmt){
            $_SESSION['preparedStageUpdateStmts'][strtoupper(htmlspecialchars($stage))] = $preparedStmt;
        }
        return $preparedStmt;
    }


    function savePesComment($upesref, $account_id,$comment){
        $existingComment = $this->getPesComment($upesref, $account_id);
        $now = new \DateTime();

        $newComment = trim($comment) . "<br/><small>" . $_SESSION['ssoEmail'] . ":" . $now->format('Y-m-d H:i:s') . "</small><br/>" . $existingComment;


        $commentFieldSize = (int)$this->getColumnLength('COMMENT');

        if(strlen($newComment)>$commentFieldSize){
            AuditTable::audit("PES Tracker Comment too long. Will be truncated.<b>Old:</b>$existingComment <br>New:$comment");
            $newComment = substr($newComment,0,$commentFieldSize-20);
        }


        $sql = " UPDATE " . $GLOBALS['Db2Schema'] . "." . $this->tableName;
        $sql.= " SET COMMENT='" . htmlspecialchars($newComment) . "' ";
        $sql.= " WHERE UPES_REF='" . htmlspecialchars($upesref) . "' AND ACCOUNT_ID='" . htmlspecialchars($account_id) . "' ";

        $rs = sqlsrv_query($GLOBALS['conn'], $sql);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
            throw new \Exception("Failed to update PES Comment for $upesref Account: $account_id. Comment was " . $comment);
        }

        return $newComment;
    }

    function prepareGetPesCommentStmt($data){
//         if(!empty($_SESSION['preparedGetPesCommentStmt'])){
//             return $_SESSION['preparedGetPesCommentStmt'];
//         }

        $sql = " SELECT COMMENT FROM " . $GLOBALS['Db2Schema'] . "." . $this->tableName;
        $sql.= " WHERE UPES_REF=?  and ACCOUNT_ID = ? ";

        $preparedStmt = sqlsrv_prepare($GLOBALS['conn'], $sql, $data);
        
        $this->lastSelectSql = $sql;

        if($preparedStmt){
            $_SESSION['preparedGetPesCommentStmt'] = $preparedStmt;
            return $preparedStmt;
        }

        throw new \Exception('Unable to prepare GetPesComment');
    }


    function getPesComment($uposref, $account_id){
        $data = array($uposref, $account_id);
        $preparedStmt = $this->prepareGetPesCommentStmt($data);

        $rs = sqlsrv_execute($preparedStmt);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, 'Prepared Stmt');
            throw new \Exception('Unable to getPesComment for ' . $uposref . ":" . $account_id );
        }

        $row = sqlsrv_fetch_array($preparedStmt);
        
        $comment = isset($row['COMMENT']) ? $row['COMMENT'] : ""; // when boarding there is no comment to return yet.
        return  $comment;
    }


    function savePesPriority($upesRef, $accountId,$pesPriority=null){

        $sql = " UPDATE " . $GLOBALS['Db2Schema'] . "." . $this->tableName;
        $sql.= " SET PRIORITY=";
        $sql.= !empty($pesPriority) ? "'" . htmlspecialchars($pesPriority) . "' " : " null, ";
        $sql.= " WHERE UPES_REF='" . htmlspecialchars($upesRef) . "' and ACCOUNT_ID='" . htmlspecialchars($accountId) . "' ";

        $rs = sqlsrv_query($GLOBALS['conn'],$sql);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, 'prepared sql');
            throw new \Exception("Failed to update Pes Priority: $pesPriority for $upesRef");
        }   

        return true;
    }

    static function formatProcessingStatusCell($row){
        $processingStatus = empty($row['PROCESSING_STATUS']) ? 'Unknown' : trim($row['PROCESSING_STATUS']) ;
        $today = new \DateTime();
        $date = DateTime::createFromFormat('Y-m-d H:i:s', substr($row['PROCESSING_STATUS_CHANGED'],0,19));
        $age  = !empty($row['PROCESSING_STATUS_CHANGED']) ?  $date->diff($today)->format('%R%a days') : null ;

        ob_start();
        echo $processingStatus;?><br/><small><?=substr(trim($row['PROCESSING_STATUS_CHANGED']),0,10);?><br/><?=$age?></small><?php
        return ob_get_clean();
    }

    static function formatEmailFieldOnTracker($row){

        $priority = !empty($row['PRIORITY']) ? ucfirst(trim($row['PRIORITY'])) : 'TBD';

        switch (trim($row['PRIORITY'])){
            case 'High':
            case 1:
                $alertClass='alert-danger';
                break;
            case 'Medium':
            case 2:
                $alertClass='alert-warning';
                break;
            case 'Low':
            case 3:
                $alertClass='alert-success';
                break;
            default:
                $alertClass='alert-info';
                break;
        }
        $emailAddress = strlen($row['EMAIL_ADDRESS']) > 20 ? substr($row['EMAIL_ADDRESS'],0,20) . "....." : $row['EMAIL_ADDRESS'];

        $formattedField = $emailAddress . "<br/><small>";
        $formattedField.= "<i>" . $row['PASSPORT_FIRST_NAME'] . "&nbsp;<b>" . $row['PASSPORT_LAST_NAME'] . "</b></i><br/>";
        $formattedField.= $row['FULL_NAME'] . "</b></small><br/>Ref: " . $row['UPES_REF'];
        $formattedField.= "<br/>CNUM: " . $row['CNUM'];
        $formattedField.= "<br/>" . $row['IBM_STATUS'] . ":" . $row['COUNTRY'];
        $formattedField.= "<br/>Resides:&nbsp;" . $row['COUNTRY_OF_RESIDENCE'];
        $formattedField.= "<div class='alert $alertClass priorityDiv'>Priority:" . $priority . "</div>";

        $formattedField.="<span style='white-space:nowrap' >
            <button class='btn btn-xs btn-danger  btnPesPriority accessPes accessCdi' data-pespriority='1'  data-upesref='" . $row['UPES_REF'] ."' data-accountid='" . $row['ACCOUNT_ID'] . "' data-accounttype='" . $row['ACCOUNT_TYPE'] . "' data-toggle='tooltip'  title='High' ><span class='glyphicon glyphicon-king' ></button>
            <button class='btn btn-xs btn-warning btnPesPriority accessPes accessCdi' data-pespriority='2' data-upesref='" . $row['UPES_REF'] ."' data-accountid='" . $row['ACCOUNT_ID'] . "' data-accounttype='" . $row['ACCOUNT_TYPE'] . "' data-toggle='tooltip'  title='Medium' ><span class='glyphicon glyphicon-knight' ></button>
            <button class='btn btn-xs btn-success btnPesPriority accessPes accessCdi' data-pespriority='3' data-upesref='" . $row['UPES_REF'] ."' data-accountid='" . $row['ACCOUNT_ID'] . "' data-accounttype='" . $row['ACCOUNT_TYPE'] . "' data-toggle='tooltip'  title='Low'><span class='glyphicon glyphicon-pawn' ></button>
            <button class='btn btn-xs btn-info    btnPesPriority accessPes accessCdi' data-pespriority='99'    data-upesref='" . $row['UPES_REF'] ."' data-accountid='" . $row['ACCOUNT_ID'] . "' data-accounttype='" . $row['ACCOUNT_TYPE'] . "' data-toggle='tooltip'  title='Unknown'><span class='glyphicon glyphicon-erase' ></button>
            </span>";

        return $formattedField;
    }

    static function getAlertClassForPesStage($pesStageValue=null){
        switch ($pesStageValue) {
            case 'Yes':
                $alertClass = ' alert-success ';
                break;
            case 'Prov':
                $alertClass = ' alert-warning ';
                break;
            case 'N/A':
                $alertClass = ' alert-secondary ';
                break;
            default:
                $alertClass = ' alert-info ';
                break;
        }
        return $alertClass;
    }

    static function getAlertClassForPesChasedDate($pesChasedDate){
        $today = new \DateTime();
        $date = DateTime::createFromFormat('Y-m-d', $pesChasedDate);
        $age  = $date->diff($today)->d;

        switch (true) {
            case $age < 7 :
                $alertClass = ' alert-success ';
                break;
            case $age < 14:
                $alertClass = ' alert-warning ';
                break;
            default:
                $alertClass = ' alert-danger ';
                break;
        }
        return $alertClass;
    }

    static function getButtonsForPesStage($value, $alertClass, $stage, $upesref='', $accountId='', $accountType='', $account='', $fullName='', $emailaddress='', $originalRequestor=''){
        $content = "<div class='alert ".$alertClass." text-center pesStageDisplay' role='alert' >".$value."</div>
        <div class='text-center columnDetails' data-pescolumn='".$stage."' >
        <span style='white-space:nowrap' data-upesref='".$upesref."' data-accountid='".$accountId."' data-accounttype='".$accountType."' data-account='".$account."' data-fullname='".$fullName."' data-emailaddress='".$emailaddress."'  data-requestor='".$originalRequestor."'>
        <button class='btn btn-success btn-xs btnPesStageValueChange accessPes accessCdi' data-setpesto='Yes' data-toggle='tooltip' data-placement='top' title='Cleared' ><span class='glyphicon glyphicon-ok-sign'></span></button>
  		<button class='btn btn-warning btn-xs btnPesStageValueChange accessPes accessCdi' data-setpesto='Prov' data-toggle='tooltip' title='Stage Cleared Provisionally'><span class='glyphicon glyphicon-alert'></span></button>
	  	<br/>
	  	<button class='btn btn-default btn-xs btnPesStageValueChange accessPes accessCdi' data-setpesto='N/A' data-toggle='tooltip' title='Not applicable'><span class='glyphicon glyphicon-remove-sign'></span></button>
	  	<button class='btn btn-info btn-xs btnPesStageValueChange accessPes accessCdi' data-setpesto='TBD' data-toggle='tooltip' title='Clear Field'><span class='glyphicon glyphicon-erase'></span></button>
	  	</span>
	  	</div>";
        return $content;
    }

    function prepareProcessStatusUpdate($data){
        if(!empty($_SESSION['preparedProcessStatusUpdate'] )) {
            return $_SESSION['prepareProcessStatusUpdate'];
        }
        $sql = " UPDATE " . $GLOBALS['Db2Schema'] . "." . $this->tableName;
        $sql.= " SET PROCESSING_STATUS =?, PROCESSING_STATUS_CHANGED = current timestamp ";
        $sql.= " WHERE UPES_REF=? AND ACCOUNT_ID=?";

        $this->preparedSelectSQL = $sql;

        $preparedStmt = sqlsrv_prepare($GLOBALS['conn'], $sql, $data);

        if($preparedStmt){
            $_SESSION['prepareProcessStatusUpdate'] = $preparedStmt;
        }

        return $preparedStmt;
    }

    function setPesProcessStatus($upesref, $accountid, $processStatus){
        $data = array($processStatus,$upesref,$accountid);
        $preparedStmt = $this->prepareProcessStatusUpdate($data);
        
        $rs = sqlsrv_execute($preparedStmt);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, 'prepared sql');
            throw new \Exception("Failed to update PES Process Status $processStatus for $upesref / $accountid");
        }

        return true;
    }

    function setPesDateLastChased($upesref, $accountId, $dateLastChased){

        $sql = " UPDATE " . $GLOBALS['Db2Schema'] . "." . $this->tableName;
        $sql.= " SET DATE_LAST_CHASED=DATE('" . htmlspecialchars($dateLastChased) . "') ";
        $sql.= " WHERE UPES_REF='" . htmlspecialchars($upesref) . "' AND ACCOUNT_ID='" . htmlspecialchars($accountId)  . "' ";

        $rs = sqlsrv_query($GLOBALS['conn'],$sql);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, 'prepared sql');
            throw new \Exception("Failed to update Date Last Chased to : $dateLastChased for $upesref / $accountId");
        }

        return true;
    }

    function setPesStatus($upesref=null, $accountid= null, $status=null, $requestor=null, $pesStatusDetails=null, $dateToUse=null){
        
        $loader = new Loader();
        $cnums = $loader->loadIndexed('CNUM','UPES_REF',AllTables::$PERSON," UPES_REF='" . htmlspecialchars($upesref) . "'");
        $emails = $loader->loadIndexed('EMAIL_ADDRESS','UPES_REF',AllTables::$PERSON," UPES_REF='" . htmlspecialchars($upesref) . "'");
        $accounts = $loader->loadIndexed('ACCOUNT','ACCOUNT_ID',AllTables::$ACCOUNT," ACCOUNT_ID='" . htmlspecialchars($accountid) . "'");

        $db2AutoCommit = sqlsrv_commit($GLOBALS['conn']);
        // sqlsrv_commit($GLOBALS['conn'],DB2_AUTOCOMMIT_OFF);
        $now = new \DateTime();

        $dateToUse =  empty($dateToUse) ? $now->format('Y-m-d') : $dateToUse;
        
        $db2DateToUse =" date('" . htmlspecialchars($dateToUse) . "') ";

        if(!$upesref or !$accountid or !$status){
            throw new \Exception('One or more of UPESREF/ACCOUNTID/STATUS not provided in ' . __METHOD__);
        }

        $requestor = empty($requestor) ? $_SESSION['ssoEmail'] : $requestor;
        
        switch ($status) {
            case AccountPersonRecord::PES_STATUS_STARTER_REQUESTED:
            case AccountPersonRecord::PES_STATUS_RECHECK_REQ:
            case AccountPersonRecord::PES_STATUS_RECHECK_PROGRESSING:
                $requestor = empty($requestor) ? 'Unknown' : $requestor;
                $dateField = 'PES_DATE_REQUESTED';
                break;
            case AccountPersonRecord::PES_STATUS_PES_PROGRESSING:
                $dateField = 'PES_EVIDENCE_DATE';
                break;
            case AccountPersonRecord::PES_STATUS_CLEARED:
                $dateField = 'PES_CLEARED_DATE';
                $this->setPesRescheckDate($upesref,$accountid, $requestor, $dateToUse ); // too Soon, we've not set the new Cleared Date
                break;
            case AccountPersonRecord::PES_STATUS_PROVISIONAL:
            default:
                $dateField = 'PES_DATE_RESPONDED';
                break;
        }
        $sql  = " UPDATE " . $GLOBALS['Db2Schema'] . "." . $this->tableName;
        $sql .= " SET $dateField = $db2DateToUse , PES_STATUS='" . htmlspecialchars($status)  . "' ";
        $sql .= !empty($pesStatusDetails) ? " , PES_STATUS_DETAILS='" . htmlspecialchars($pesStatusDetails) . "' " : null;
        $sql .= trim($status)==AccountPersonRecord::PES_STATUS_STARTER_REQUESTED ? ", PES_REQUESTOR='" . htmlspecialchars($requestor) . "' " : null;
        $sql .= " WHERE UPES_REF='" . htmlspecialchars($upesref) . "' and ACCOUNT_ID='" . htmlspecialchars($accountid)  . "' ";

        $result = sqlsrv_query($GLOBALS['conn'], $sql);

        if(!$result){
            DbTable::displayErrorMessage($result, __CLASS__, __METHOD__, $sql);
            return false;
        }

        $pesTracker = new AccountPersonTable(AllTables::$ACCOUNT_PERSON);
        $pesTracker->savePesComment($upesref, $accountid,  "PES_STATUS set to :" . $status );

        AuditTable::audit("PES Status set for:" . $upesref . "/" . $accountid ." To : " . $status . " By:" . $requestor,AuditTable::RECORD_TYPE_AUDIT);

        // in_array($upesref, $cnums) ? $cnum = $cnums[$upesref] : $cnum = '';
        array_key_exists($upesref, $cnums) ? $cnum = $cnums[$upesref] : $cnum = '';

        in_array($status,AccountPersonRecord::$pesAuditableStatus) ? PesStatusAuditTable::insertRecord($cnum, $emails[$upesref], $accounts[$accountid], $status, $dateToUse) : null;
        /*
        $now = new \DateTime();
        $updateDate = $now->format('Y-m-d');
        if(in_array($status,AccountPersonRecord::$pesAuditableStatus)) {
            $pesStatusAuditRecord = new PesStatusAuditRecord();
            $pesStatusAuditRecord->setFromArray(
                array(
                    'CNUM'=>$cnum, 
                    'EMAIL_ADDRESS'=>$emails[$upesref], 
                    'ACCOUNT'=>$accounts[$accountid], 
                    'PES_STATUS'=>$status, 
                    'PES_CLEARED_DATE'=>$dateToUse,
                    'UPDATER'=>$_SESSION['ssoEmail'],
                    'UPDATED'=>$updateDate
                )
            );
            $pesStatusAuditTable = new PesStatusAuditTable(AllTables::$PES_STATUS_AUDIT);
            $pesStatusAuditTable->saveRecord($pesStatusAuditRecord);
        }
        */
        sqlsrv_commit($GLOBALS['conn']);
        sqlsrv_commit($GLOBALS['conn'],$db2AutoCommit);

        return true;
    }

    function setPesRescheckDate($upesref=null,$accountid=null, $requestor=null, $clearedDate= null){
        if(!$upesref or !$accountid){
            throw new \Exception('No UPES_REF/ACCOUNTID provided in ' . __METHOD__);
        }

        $requestor = empty($requestor) ? $_SESSION['ssoEmail'] : $requestor;

        $loader = new Loader();
        $predicate = " UPES_REF='" . htmlspecialchars(trim($upesref)) . "' AND ACCOUNT_ID = '" . htmlspecialchars($accountid) . "' ";
        $pesLevels = $loader->loadIndexed('PES_LEVEL','UPES_REF',AllTables::$ACCOUNT_PERSON,$predicate);
        $pesRecheckPeriods = $loader->loadIndexed('RECHECK_YEARS','PES_LEVEL_REF',AllTables::$PES_LEVELS);

        $pesRecheckPeriod = 99; // default in case we don't find the actual value for this PES_LEVEL_REF

        if(isset($pesLevels[trim($upesref)])){
            $pesLevel = $pesLevels[trim($upesref)];
            if(isset($pesRecheckPeriods[$pesLevel])){
                $pesRecheckPeriod = $pesRecheckPeriods[$pesLevel];
            }
        }

        if(empty($clearedDate)){
            // They've not supplied one, so go get it.
            $sql  = " SELECT PES_CLEARED_DATE FROM  " . $GLOBALS['Db2Schema'] . "." . $this->tableName;
            $sql .= " WHERE UPES_REF='" . htmlspecialchars($upesref) . "' AND ACCOUNT_ID='" . htmlspecialchars($accountid) . "' ";
            
            $cleared = sqlsrv_query($GLOBALS['conn'], $sql);
            
            if(!$cleared){
                DbTable::displayErrorMessage($cleared, __CLASS__, __METHOD__, $sql);
                return false;
            }
            
            $row = sqlsrv_fetch_array($cleared);
            
            $clearedDate = $row['PES_CLEARED_DATE'];
        }
        
        $pes_cleared_obj = !empty($clearedDate) ? \DateTime::createFromFormat('Y-m-d', $clearedDate) : new \DateTime();
        $pes_cleared_sql = "DATE('" . $pes_cleared_obj->format('Y-m-d') . "') ";

        $sql  = " UPDATE " . $GLOBALS['Db2Schema'] . "." . $this->tableName;
        $sql .= " SET PES_RECHECK_DATE = $pes_cleared_sql  +  $pesRecheckPeriod  years " ;
        $sql .= " WHERE UPES_REF='" . htmlspecialchars($upesref) . "' AND ACCOUNT_ID='" . htmlspecialchars($accountid) . "' ";

        $result = sqlsrv_query($GLOBALS['conn'], $sql);

        if(!$result){
            DbTable::displayErrorMessage($result, __CLASS__, __METHOD__, $sql);
            return false;
        }

        $sql  = " SELECT PES_RECHECK_DATE FROM  " . $GLOBALS['Db2Schema'] . "." . $this->tableName;
        $sql .= " WHERE UPES_REF='" . htmlspecialchars($upesref) . "' AND ACCOUNT_ID='" . htmlspecialchars($accountid) . "' ";

        $res = sqlsrv_query($GLOBALS['conn'], $sql);

        if(!$res){
            DbTable::displayErrorMessage($result, __CLASS__, __METHOD__, $sql);
            return false;
        }

        $row = sqlsrv_fetch_array($res);

        $pesTracker = new AccountPersonTable(AllTables::$ACCOUNT_PERSON);
        $pesTracker->savePesComment($upesref, $accountid, "PES_RECHECK_DATE set to :" .  $row['PES_RECHECK_DATE'] );

        AuditTable::audit("PES_RECHECK_DATE set to :  "  . $row['PES_RECHECK_DATE'] . " by " . $requestor,AuditTable::RECORD_TYPE_AUDIT);

        return true;
    }

    function getTracker($records=self::PES_TRACKER_RECORDS_ACTIVE, Spreadsheet $spreadsheet){
        $sheet = 1;
        $rs = self::returnPesEventsTable($records, self::PES_TRACKER_RETURN_RESULTS_AS_RESULT_SET, null, null, 0, -1);

        if($rs){
            set_time_limit(62);
            $recordsFound = static::writeResultSetToXls($rs, $spreadsheet);
            if($recordsFound){
                static::autoFilter($spreadsheet);
                static::autoSizeColumns($spreadsheet);
                static::setRowColor($spreadsheet,'105abd19',1);
            }
        }

        if(!$recordsFound){
            $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(1, 1, "Warning");
            $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(1, 2,"No records found");
        }
        // Rename worksheet & create next.

        $spreadsheet->getActiveSheet()->setTitle('Record ' . $records);
        $spreadsheet->createSheet();
        $spreadsheet->setActiveSheetIndex($sheet++);

        return true;
    }

    static function addButtonsForPeopleReport($row){
        $account = $row['ACCOUNT'];
        $accountId = $row['ACCOUNT_ID'];
        $accountType = $row['ACCOUNT_TYPE'];
        $contract = $row['CONTRACT'];
        $contractId = $row['CONTRACT_ID'];
        $upesref = $row['UPES_REF'];
        $email = $row['EMAIL_ADDRESS'];
        $fullname = $row['FULL_NAME'];
        $countryOfResidence = $row['COUNTRY_OF_RESIDENCE'];

        $row['ACCOUNT'] = array('display'=>"$account<br/><small>($countryOfResidence)<small>", 'sort'=>$account);

        $row['ACTION'] = '';
        
        $onOrOffBoardingIcon     = empty($row['OFFBOARDED_DATE']) ? "glyphicon-log-out" : "glyphicon-log-in";
        $onOrOffBoardingTitle    = empty($row['OFFBOARDED_DATE']) ? "offboard" : "re-board. Offboarded:" . $row['OFFBOARDED_DATE'] . " By:" . $row['OFFBOARDED_BY'] ;
        $onOrOffBoardingBtnClass = empty($row['OFFBOARDED_DATE']) ? "btn-warning" : "btn-info";
        $boarded = empty($row['OFFBOARDED_DATE']) ? 'yes' : 'no';
        
        switch ($row['PES_STATUS']) {
            case AccountPersonRecord::PES_STATUS_CLEARED:
            case AccountPersonRecord::PES_STATUS_CANCEL_REQ:
            case AccountPersonRecord::PES_STATUS_CANCEL_CONFIRMED:
                $row['ACTION'].= "<button type='button' class='btn btn-primary btn-xs editPerson accessRestrict accessPesTeam accessCdi' aria-label='Left Align' data-upesref='" . $upesref . "' data-toggle='tooltip' title='Edit Person' >
                    <span class='glyphicon glyphicon-edit editPerson'  aria-hidden='true' data-upesref='" . $upesref . "'  ></span>
                </button>";
                $row['ACTION'].= "<br/>";
                $row['ACTION'].= "<button type='button' class='btn $onOrOffBoardingBtnClass btn-xs toggleBoarded accessRestrict accessPesTeam accessCdi' aria-label='Left Align' data-accountid='" .$accountId  . "' data-accounttype='" .$accountType  . "' data-upesref='" . $upesref . "'  data-boarded='" . $boarded .  "' data-toggle='tooltip' title='$onOrOffBoardingTitle' >
                    <span class='glyphicon $onOrOffBoardingIcon'></span>
                </button>";
                break;

             default:
                 $row['ACTION'].= "<button type='button' class='btn btn-primary btn-xs editPerson accessRestrict accessPesTeam accessCdi' aria-label='Left Align' data-upesref='" . $upesref . "' data-toggle='tooltip' title='Edit Person' >
                    <span class='glyphicon glyphicon-edit editPerson'  aria-hidden='true' data-upesref='" . $upesref . "'  ></span>
                </button>";
                $row['ACTION'].= "&nbsp;";
                $row['ACTION'].= "<button type='button' class='btn btn-primary btn-xs cancelPesRequest' aria-label='Left Align' data-accountid='" .$accountId . "' data-accounttype='" .$accountType  . "' data-account='" . $account . "' data-upesref='" . $upesref . "' data-email='" . $email . "'  data-name='" . $fullname . "' data-toggle='tooltip' title='Cancel PES Request' >
                    <span class='glyphicon glyphicon-ban-circle'></span>
                </button>";
                $row['ACTION'].= "<br/>";
                $row['ACTION'].= "<button type='button' class='btn $onOrOffBoardingBtnClass btn-xs toggleBoarded accessRestrict accessPesTeam accessCdi' aria-label='Left Align' data-accountid='" .$accountId . "' data-accounttype='" .$accountType  . "' data-upesref='" . $upesref . "'  data-boarded='" . $boarded. "' data-toggle='tooltip' title='$onOrOffBoardingTitle' >
                    <span class='glyphicon $onOrOffBoardingIcon'></span>
                </button>";
                break;
        }

        $requestor = $row['PES_REQUESTOR'];
        $requested = $row['PES_DATE_REQUESTED'];
        $requestedObj = \DateTime::createFromFormat('Y-m-d', $requested);
        $requestedDisplay = $requestedObj ? $requestedObj->format('d-m-Y') : $requested;

        $row['REQUESTED'] = array('display'=> "<small>" .  $requestor . "<br/>" . $requestedDisplay . "</small>", 'sort'=>$row['PES_DATE_REQUESTED']);

        $clearedDateObj = \DateTime::createFromFormat('Y-m-d', $row['PES_CLEARED_DATE']);
        $clearedDateDisplay =  $clearedDateObj ? $clearedDateObj->format('d-m-Y') : $row['PES_CLEARED_DATE'];
        $row['PES_CLEARED_DATE'] = $clearedDateDisplay;

        $recheckDateObj = \DateTime::createFromFormat('Y-m-d', $row['PES_RECHECK_DATE']);
        $recheckDateDisplay =  $recheckDateObj ? $recheckDateObj->format('d-m-Y') : $row['PES_RECHECK_DATE'];        
        
        $chasedDateObj = \DateTime::createFromFormat('Y-m-d', $row['DATE_LAST_CHASED']);
        $chasedDateDisplay =  $chasedDateObj ? $chasedDateObj->format('d-m-Y') : $row['DATE_LAST_CHASED'];
        $row['DATE_LAST_CHASED'] = $chasedDateDisplay;

        $pesLevel = $row['PES_LEVEL'];
        $pesLevelRef = $row['PES_LEVEL_REF'];
        $row['PES_LEVEL']= "<button type='button' class='btn btn-primary btn-xs editPesLevel accessRestrict accessPesTeam accessCdi' aria-label='Left Align' data-plEmailAddress='" . $email . "' data-plFullName='" . $fullname . "' data-plAccount='" . $account . "' data-plContract='" . $contract . "' data-plupesref='" . $upesref . "' data-plAccountId='" . $accountId . "' data-plContractId='" . $contractId . "' data-plPesLevelRef='" . $pesLevelRef . "'  data-plCountry='" . $countryOfResidence . "'  data-plRequestor='" . $requestor ."'  data-plClearedDate='" . $clearedDateDisplay ."'  data-plRecheckDate='" . $recheckDateDisplay ."' data-toggle='tooltip' title='Edit Request Details' >
            <span class='glyphicon glyphicon-edit aria-hidden='true' ></span>
        </button>&nbsp;" . $pesLevel;

        $processingStatus = $row['PROCESSING_STATUS'];
        $processingStatusChanged = $row['PROCESSING_STATUS_CHANGED'];
        $processingStatusChangedObj = \DateTime::createFromFormat('Y-m-d+', $processingStatusChanged);
        $processingStatusDisplayed = $processingStatusChangedObj ? $processingStatusChangedObj->format('d-m-Y') : $processingStatusChanged;

        $row['PROCESSING_STATUS'] =  array('display'=>$processingStatus . "<br/><small>" . $processingStatusDisplayed . "</small>", 'sort'=>$processingStatus);

        return $row;
    }

    static function cancelPesRequest( $accountId=null, $upesref=null){

        // sqlsrv_commit($GLOBALS['conn'],DB2_AUTOCOMMIT_OFF);

        $sql = " UPDATE " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON;
        $sql.= " SET PES_STATUS='" . AccountPersonRecord::PES_STATUS_CANCEL_REQ . "' ";
        $sql.= " WHERE ACCOUNT_ID='" . htmlspecialchars($accountId) . "' ";
        $sql.= " AND UPES_REF='" . htmlspecialchars($upesref) . "' ";

        $rs = sqlsrv_query($GLOBALS['conn'], $sql);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
            return false;
        }

        $sql = " SELECT * ";
        $sql.= " FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON;
        $sql.= " WHERE ACCOUNT_ID='" . htmlspecialchars($accountId) . "' ";
        $sql.= " AND UPES_REF='" . htmlspecialchars($upesref) . "' ";


        $rs = sqlsrv_query($GLOBALS['conn'], $sql);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
            return false;
        }

        $accountPersonData = sqlsrv_fetch_array($rs);


        $accountPersonRecord = new AccountPersonRecord();
        $accountPersonData['PES_STATUS'] = AccountPersonRecord::PES_STATUS_CANCEL_REQ; // Because DB2 isn't a commited change
        $accountPersonRecord->setFromArray($accountPersonData);

        $accountPersonRecord->sendPesStatusChangedEmail();

        sqlsrv_commit($GLOBALS['conn']);
        // sqlsrv_commit($GLOBALS['conn'],DB2_AUTOCOMMIT_ON);
    }

    function notifyRecheckDateApproaching(){
        $slack = new slack();
        $localConnection = $GLOBALS['conn']; // So we can keep reading this RS whilst making updates to the TRACKER TABLE.
        include "connect.php"; // get new connection on $GLOBALS['conn'];

        $sql = " SELECT AP.ACCOUNT_ID, A.ACCOUNT, AP.UPES_REF, P.CNUM, P.EMAIL_ADDRESS, P.FULL_NAME,  AP.PES_STATUS, AP.PES_RECHECK_DATE ";
        $sql.= " FROM " . $GLOBALS['Db2Schema'] . "." . allTables::$ACCOUNT_PERSON . " as AP ";
        $sql.= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . allTables::$PERSON . " as P ";
        $sql.= " ON AP.UPES_REF = P.UPES_REF ";
        $sql.= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . allTables::$ACCOUNT . " as A ";
        $sql.= " ON AP.ACCOUNT_ID = A.ACCOUNT_ID ";
        $sql.= " WHERE 1=1 ";
//        $sql.= " AND AP.PES_STATUS != '" . AccountPersonRecord::PES_STATUS_RECHECK_REQ . "' ";
        $sql.= " AND AP.PES_STATUS = '" . AccountPersonRecord::PES_STATUS_CLEARED . "' ";
        $sql.= " and AP.PES_RECHECK_DATE is not null ";
        $sql.= " and AP.PES_RECHECK_DATE < CURRENT DATE + 56 DAYS ";
        $rs = sqlsrv_query($localConnection, $sql);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
        }

        $allRecheckers = false;
        while(($row = sqlsrv_fetch_array($rs))==true){
            $trimmedRow = array_map('trim', $row);
            $allRecheckers[] = $trimmedRow;
            $this->setPesStatus($trimmedRow['UPES_REF'],$trimmedRow['ACCOUNT_ID'],AccountPersonRecord::PES_STATUS_RECHECK_REQ);
            $this->resetForRecheck($trimmedRow['UPES_REF'],$trimmedRow['ACCOUNT_ID']);
            $slack->sendMessageToChannel("PES Recheck " . $trimmedRow['FULL_NAME'] . " on " . $trimmedRow['ACCOUNT'], slack::CHANNEL_UPES_AUDIT);
        }

        if($allRecheckers){
            PesEmail::notifyPesTeamOfUpcomingRechecks($allRecheckers);
        } else {
            PesEmail::notifyPesTeamNoUpcomingRechecks();
        }
        return $allRecheckers;

    }

    function resetForRecheck($upesRef=null, $accountId=null){
        $data = array($accountId,$upesRef);
        $preparedStmt = $this->prepareResetForRecheck($data);
        
        $rs = sqlsrv_execute($preparedStmt);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, 'prepared sql');
            throw new \Exception('Unable to reset for recheck Tracker record for ' . $accountId .":" . $upesRef);
        }
    }

    function prepareResetForRecheck($data){
        if(isset($this->preparedResetForRecheck)) {
            return $this->preparedResetForRecheck;
        }

        $sql = " UPDATE " . $GLOBALS['Db2Schema'] . "." . $this->tableName;
        $sql.= " SET PROCESSING_STATUS = 'PES' ";
        // CONSENT = null, RIGHT_TO_WORK = null, PROOF_OF_ID = null, PROOF_OF_RESIDENCY= null, CREDIT_CHECK= null,FINANCIAL_SANCTIONS= null ";
        // $sql.= " , CRIMINAL_RECORDS_CHECK= null, PROOF_OF_ACTIVITY= null
        foreach (self::PES_TRACKER_STAGES as $trackerStage) {
            $sql.= " , " . $trackerStage . " = null ";
        }
        $sql.= " ,  PROCESSING_STATUS_CHANGED= current timestamp, DATE_LAST_CHASED = null ";
        $sql.= " WHERE ACCOUNT_ID = ?  AND UPES_REF = ? ";

        $preparedStmt = sqlsrv_prepare($GLOBALS['conn'], $sql, $data);

        if($preparedStmt){
            $this->preparedResetForRecheck = $preparedStmt;
            return $preparedStmt;
        }

        return false;
    }

    static function statusByAccount(){
        $sql = " SELECT A.ACCOUNT, AP.PES_STATUS, count(*) as RESOURCES ";
        $sql.= " FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON . " AS AP ";
        $sql.= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT . " AS A ";
        $sql.= " ON AP.ACCOUNT_ID = A.ACCOUNT_ID ";

//         $sql.= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON . " AS P ";
//         $sql.= " ON AP.UPES_REF = P.UPES_REF ";
//         $sql.= " WHERE P.BLUEPAGES = 'found' or P.BLUEPAGES is null ";

        $sql.= " GROUP by ACCOUNT, PES_STATUS ";
        $sql.= " ORDER by ACCOUNT ";

        $rs = sqlsrv_query($GLOBALS['conn'], $sql);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
            throw new \Exception('Unable to produce StatusByAccount result set');
        }
        $report = false;
        while(($row = sqlsrv_fetch_array($rs))==true){
            $trimmedRow = array_map('trim', $row);
            $report[] = $trimmedRow;
        }

        return $report;
    }

    static function statusByContract(){
        $sql = " SELECT A.ACCOUNT, C.CONTRACT, AP.PES_STATUS, count(*) as RESOURCES ";
        $sql.= " FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON . " AS AP ";
        $sql.= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT . " AS A ";
        $sql.= " ON AP.ACCOUNT_ID = A.ACCOUNT_ID ";
        $sql.= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . AllTables::$CONTRACT . " AS C ";
        $sql.= " ON AP.CONTRACT_ID = C.CONTRACT_ID ";

//         $sql.= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON . " AS P ";
//         $sql.= " ON AP.UPES_REF = P.UPES_REF ";
//         $sql.= " WHERE P.BLUEPAGES = 'found' or P.BLUEPAGES is null ";

        $sql.= " GROUP by ACCOUNT, CONTRACT, PES_STATUS ";
        $sql.= " ORDER by ACCOUNT ";

        $rs = sqlsrv_query($GLOBALS['conn'], $sql);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
            throw new \Exception('Unable to produce StatusByAccount result set');
        }
        $report = false;
        while(($row = sqlsrv_fetch_array($rs))==true){
            $trimmedRow = array_map('trim', $row);
            $report[] = $trimmedRow;
        }

        return $report;
    }

    static function processStatusByAccount(){
        $sql = " SELECT A.ACCOUNT, AP.PROCESSING_STATUS, count(*) as RESOURCES ";
        $sql.= " FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON . " AS AP ";
        $sql.= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT . " AS A ";
        $sql.= " ON AP.ACCOUNT_ID = A.ACCOUNT_ID ";

        //         $sql.= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON . " AS P ";
        //         $sql.= " ON AP.UPES_REF = P.UPES_REF ";
        //         $sql.= " WHERE P.BLUEPAGES = 'found' or P.BLUEPAGES is null ";

        $sql.= " GROUP by ACCOUNT, PROCESSING_STATUS ";
        $sql.= " ORDER by ACCOUNT ";

        $rs = sqlsrv_query($GLOBALS['conn'], $sql);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
            throw new \Exception('Unable to produce ProcessStatusByAccount result set');
        }
        $report = false;
        while(($row = sqlsrv_fetch_array($rs))==true){
            $trimmedRow = array_map('trim', $row);
            $report[] = $trimmedRow;
        }

        return $report;
    }

    static function processStatusByContract(){
        $sql = " SELECT A.ACCOUNT, C.CONTRACT, AP.PROCESSING_STATUS, count(*) as RESOURCES ";
        $sql.= " FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON . " AS AP ";
        $sql.= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT . " AS A ";
        $sql.= " ON AP.ACCOUNT_ID = A.ACCOUNT_ID ";
        $sql.= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . AllTables::$CONTRACT . " AS C ";
        $sql.= " ON AP.CONTRACT_ID = C.CONTRACT_ID ";

        //         $sql.= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON . " AS P ";
        //         $sql.= " ON AP.UPES_REF = P.UPES_REF ";
        //         $sql.= " WHERE P.BLUEPAGES = 'found' or P.BLUEPAGES is null ";

        $sql.= " GROUP by ACCOUNT, CONTRACT, PROCESSING_STATUS ";
        $sql.= " ORDER by ACCOUNT ";

        $rs = sqlsrv_query($GLOBALS['conn'], $sql);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
            throw new \Exception('Unable to produce ProcessStatusByAccount result set');
        }
        $report = false;
        while(($row = sqlsrv_fetch_array($rs))==true){
            $trimmedRow = array_map('trim', $row);
            $report[] = $trimmedRow;
        }

        return $report;
    }

    static function upcomingRechecksByAccount(){
        $sql = " SELECT A.ACCOUNT, YEAR(PES_RECHECK_DATE) as YEAR, MONTH(PES_RECHECK_DATE) as MONTH, count(*) as RESOURCES ";
        $sql.= " FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON . " AS AP ";
        $sql.= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT . " AS A ";
        $sql.= " ON AP.ACCOUNT_ID = A.ACCOUNT_ID ";

        //         $sql.= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON . " AS P ";
        //         $sql.= " ON AP.UPES_REF = P.UPES_REF ";
        $sql.= " WHERE DATE(PES_RECHECK_DATE) >= CURRENT DATE - 1 month ";
        $sql.= " AND DATE(PES_RECHECK_DATE) <= CURRENT DATE + 5 MONTHS ";
        $sql.= " GROUP by ACCOUNT, YEAR(PES_RECHECK_DATE), MONTH(PES_RECHECK_DATE) ";
        $sql.= " ORDER by ACCOUNT ";

        $rs = sqlsrv_query($GLOBALS['conn'], $sql);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
            throw new \Exception('Unable to produce upcomingRechecksByAccount result set');
        }
        $report = false;
        while(($row = sqlsrv_fetch_array($rs))==true){
            $trimmedRow = array_map('trim', $row);
            $report[] = $trimmedRow;
        }

        return $report;

    }

    static function upcomingRechecksByContract(){
        $sql = " SELECT A.ACCOUNT, C.CONTRACT, YEAR(PES_RECHECK_DATE) as YEAR, MONTH(PES_RECHECK_DATE) as MONTH, count(*) as RESOURCES ";
        $sql.= " FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON . " AS AP ";
        $sql.= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT . " AS A ";
        $sql.= " ON AP.ACCOUNT_ID = A.ACCOUNT_ID ";
        $sql.= " left join " . $GLOBALS['Db2Schema'] . "." . AllTables::$CONTRACT . " as C ";
        $sql.= " ON AP.CONTRACT_ID = C.CONTRACT_ID ";

        //         $sql.= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON . " AS P ";
        //         $sql.= " ON AP.UPES_REF = P.UPES_REF ";
        $sql.= " WHERE DATE(PES_RECHECK_DATE) >= CURRENT DATE - 1 month ";
        $sql.= " AND DATE(PES_RECHECK_DATE) <= CURRENT DATE + 5 MONTHS ";
        $sql.= " GROUP by ACCOUNT, CONTRACT, YEAR(PES_RECHECK_DATE), MONTH(PES_RECHECK_DATE) ";
        $sql.= " ORDER by ACCOUNT ";

        $rs = sqlsrv_query($GLOBALS['conn'], $sql);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
            throw new \Exception('Unable to produce upcomingRechecksByAccount result set');
        }
        $report = false;
        while(($row = sqlsrv_fetch_array($rs))==true){
            $trimmedRow = array_map('trim', $row);
            $report[] = $trimmedRow;
        }

        return $report;
    }

    static function miClearedByAccount(){
        $twelveMonthsAgo = new DateTime("first day of this month");

        $sql = "";
        $sql.= " select trim(A.ACCOUNT) as ACCOUNT, YEAR(PES_CLEARED_DATE) as YEAR, MONTH(PES_CLEARED_DATE) as MONTH, count(*) as Cleared ";
        $sql.= " from " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON . " as AP ";
        $sql.= " left join " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT . " as A  ";
        $sql.= " on AP.ACCOUNT_ID = A.ACCOUNT_ID "; 
        $sql.= " where PES_CLEARED_DATE >= date('" . $twelveMonthsAgo->format('Y-m-d') . "') - 11 Months ";
        $sql.= " AND A.ACCOUNT is not null ";
        $sql.= " group by ACCOUNT, YEAR(PES_CLEARED_DATE), MONTH(PES_CLEARED_DATE)";
        $sql.= " ORDER BY 1, 2 desc, 3 desc ";

        $rs = sqlsrv_query($GLOBALS['conn'],$sql);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
            throw new \Exception('Unable to produce miClearedByAccount result set');
        }
        $report = false;
        while(($row = sqlsrv_fetch_array($rs))==true){
            $trimmedRow = array_map('trim', $row);
            $report[] = $trimmedRow;
        }

        return $report;
    }

    static function miClearedByContract(){
        $twelveMonthsAgo = new DateTime("first day of this month");

        $sql = "";
        $sql.= " select trim(A.ACCOUNT) as ACCOUNT, YEAR(PES_CLEARED_DATE) as YEAR, MONTH(PES_CLEARED_DATE) as MONTH, count(*) as Cleared ";
        $sql.= " from " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON . " as AP ";
        $sql.= " left join " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT . " as A  ";
        $sql.= " on AP.ACCOUNT_ID = A.ACCOUNT_ID "; 
        $sql.= " where PES_CLEARED_DATE >= date('" . $twelveMonthsAgo->format('Y-m-d') . "') - 11 Months ";
        $sql.= " AND A.ACCOUNT is not null ";
        $sql.= " group by ACCOUNT, YEAR(PES_CLEARED_DATE), MONTH(PES_CLEARED_DATE)";
        $sql.= " ORDER BY 1, 2 desc, 3 desc ";

        $rs = sqlsrv_query($GLOBALS['conn'],$sql);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
            throw new \Exception('Unable to produce miClearedByContract result set');
        }
        $report = false;
        while(($row = sqlsrv_fetch_array($rs))==true){
            $trimmedRow = array_map('trim', $row);
            $report[] = $trimmedRow;
        }

        return $report;
    }

    static function getEmailAddressAccountArray(){
        $data = array();
        $sql = " SELECT P.EMAIL_ADDRESS, A.ACCOUNT, P.UPES_REF, A.ACCOUNT_ID ";
        $sql.= " FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON . " as P ";
        $sql.= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON . " AS AP ";
        $sql.= " ON P.UPES_REF = AP.UPES_REF ";
        $sql.= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT . " AS A ";
        $sql.= " ON AP.ACCOUNT_ID = A.ACCOUNT_ID ";
        $sql.= " WHERE AP.ACCOUNT_ID is not null ";
        $sql.= " ORDER BY EMAIL_ADDRESS, ACCOUNT ";

        $rs = sqlsrv_query($GLOBALS['conn'], $sql);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
            throw new \Exception('Unable to produce emailAddress Account Pes Status result set');
        }
        while(($row = sqlsrv_fetch_array($rs))==true){
            $trimmedRow = array_map('trim', $row);
            $personAccount = $trimmedRow['EMAIL_ADDRESS'] . " : " . $trimmedRow['ACCOUNT'];
            $upesAccountId = $trimmedRow['UPES_REF'] . ":" . $trimmedRow['ACCOUNT_ID'];
            $data[$personAccount] = $upesAccountId;
        }

        return $data;
    }

    
    static function offboardFromAccount( $accountId=null, $upesref=null){
        
        // sqlsrv_commit($GLOBALS['conn'],DB2_AUTOCOMMIT_OFF);
        
        $sql = " UPDATE " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON;
        $sql.= " SET OFFBOARDED_DATE = current date ";
        $sql.= "     ,OFFBOARDED_BY = '" . htmlspecialchars($_SESSION['ssoEmail']) . "' ";
        $sql.= " WHERE ACCOUNT_ID = '" . htmlspecialchars($accountId) . "' ";
        $sql.= " AND UPES_REF = '" . htmlspecialchars($upesref) . "' ";
        
        $rs = sqlsrv_query($GLOBALS['conn'], $sql);
        
        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
            return false;
        }
        
       
        sqlsrv_commit($GLOBALS['conn']);
        // sqlsrv_commit($GLOBALS['conn'],DB2_AUTOCOMMIT_ON);
    }
    
    static function reboardToAccount( $accountId=null, $upesref=null){
        
        // sqlsrv_commit($GLOBALS['conn'],DB2_AUTOCOMMIT_OFF);
        
        $sql = " UPDATE " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON;
        $sql.= " SET OFFBOARDED_DATE = null ";
        $sql.= "     ,OFFBOARDED_BY = null ";
        $sql.= " WHERE ACCOUNT_ID = '" . htmlspecialchars($accountId) . "' ";
        $sql.= " AND UPES_REF = '" . htmlspecialchars($upesref) . "' ";
        
        $rs = sqlsrv_query($GLOBALS['conn'], $sql);
        
        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
            return false;
        }
        
        
        sqlsrv_commit($GLOBALS['conn']);
        // sqlsrv_commit($GLOBALS['conn'],DB2_AUTOCOMMIT_ON);
    }
    
    static function offboardedStatusFromEmail($email=null, $accountId=null){
        $sql = " SELECT OFFBOARDED_DATE FROM " . $GLOBALS['Db2Schema'] . "." . allTables::$ACCOUNT_PERSON . " AS AP ";
        $sql.= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . allTables::$PERSON . " AS P ";
        $sql.= " ON AP.UPES_REF = P.UPES_REF ";
        $sql.= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . allTables::$ACCOUNT . " AS A ";
        $sql.= " ON AP.ACCOUNT_ID = A.ACCOUNT_ID ";
        $sql.= " WHERE upper(P.EMAIL_ADDRESS) = upper('" . htmlspecialchars(strtoupper(trim($email))) . "') " ;
        $sql.= " AND upper(A.ACCOUNT)=upper('" . htmlspecialchars(strtoupper(trim($accountId))) . "') " ;
       
        $resultSet = sqlsrv_query($GLOBALS['conn'], $sql);
        if(!$resultSet){
            DbTable::displayErrorMessage($resultSet, __CLASS__, __METHOD__, $sql);
            return false;
        }
        
        $row = sqlsrv_fetch_array($resultSet);
        return !empty($row['OFFBOARDED_DATE']);
    }
    
    function copyXlsxToDb2($fileName, $withTimings = false){
        $elapsed = -microtime(true);
        ob_start();

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($fileName);
        $reader->setReadDataOnly(true);
        $objPHPExcel  = $reader->load($fileName);

        //  Get worksheet dimensions
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        //  Loop through each row of the worksheet in turn
        $firstRow = false;
        $columnHeaders = array();
        $recordData = array();
        $failedRecords = 0;
        // $autoCommit = sqlsrv_commit($GLOBALS['conn'],DB2_AUTOCOMMIT_OFF);
        for ($row = 1; $row <= $highestRow; $row++){
            set_time_limit(10);
            $time = -microtime(true);
            //  Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);
            //  Insert row data array into your database of choice here
            if(!$firstRow){
                foreach ($rowData[0] as $key => $value){
                    if(!empty($value)){
                        $columnHeaders[$key] = DbTable::toColumnName(strtoupper($value));
                    }
                }
                // echo '<pre>';
                // var_dump($columnHeaders);
                // echo '</pre>';
                $firstRow = true;
            } else {
                $prepareArrary = -microtime(true);
                foreach ($rowData[0] as $key => $value) {
                    if($key == 0 || !empty($value)){
                        $recordData[$columnHeaders[$key]] = trim($value);
                    }
                }
                $prepareArrary += microtime(true);
                echo $withTimings ? "Row: $row Cnum " . $recordData['CNUM'] . " Prepare Array:" . sprintf('%f', $prepareArrary) . PHP_EOL : null;

                // avoid trying to save empty rows.
                // save the row to DB2
                $convertDates = -microtime(true);
                $recordDataWithDb2Dates = $recordData;

                $date = date_create_from_format($this->xlsDateFormat, trim($recordData['NEW_CLEARED_DATE']));
                if($date !== false){
                    $adjustedData = $date->format($this->db2DateFormat);
                    $recordDataWithDb2Dates['NEW_CLEARED_DATE'] = $adjustedData;
                    
                    $convertDates += microtime(true);
                    echo  $withTimings ? "Row: $row Cnum " . $recordData['CNUM'] . " Convert Dates:" . sprintf('%f', $convertDates) . PHP_EOL : null;

                    // echo '<pre>';
                    // echo 'record data with db2 dates';
                    // var_dump($recordDataWithDb2Dates);
                    // echo '</pre>';

                    // get UPES_REF
                    if(!empty($recordData['CNUM'] )){
                        $upesRef = PersonTable::getUpesrefFromCNUM($recordDataWithDb2Dates['CNUM']);
                        // echo 'from CNUM';
                    } else {
                        $upesRef = PersonTable::getUpesrefFromEmail($recordDataWithDb2Dates['EMAIL']);
                        // echo 'from EMAIL';
                    }

                    // echo '<pre>';
                    // echo 'upes ref';
                    // var_dump($upesRef);
                    // echo '</pre>';

                    // get ACCOUNT_ID
                    $accountId = AccountTable::getAccountIdFromName($recordDataWithDb2Dates['ACCOUNT']);
                    if (!empty($upesRef) && !empty($accountId)) {
                            
                        // update Account Person record
                        $accountPersonRecordData = $this->getWithPredicate(" ACCOUNT_ID='" . $accountId . "' AND UPES_REF='" . $upesRef . "' ");
                        if (!empty($accountPersonRecordData)) {
                            try {
                                $update = -microtime(true);

                                $indicateRecheck = null;
                                $requestor = $accountPersonRecordData['PES_REQUESTOR'];
                                $pesStatusDetails = $accountPersonRecordData['PES_STATUS_DETAILS'];
                                $this->setPesStatus($upesRef, $accountId, $recordDataWithDb2Dates['NEW_PES_STATUS'], $requestor, $pesStatusDetails, $recordDataWithDb2Dates['NEW_CLEARED_DATE']);
                                $this->savePesComment($upesRef, $accountId, "PES application forms $indicateRecheck sent:" . '' );
        
                                $this->setPesProcessStatus($upesRef, $accountId, $recordDataWithDb2Dates['NEW_PROCESS_STATUS']);
                                $this->savePesComment($upesRef, $accountId, "Process Status set to " . $recordDataWithDb2Dates['NEW_PROCESS_STATUS']);
        
                                $update += microtime(true);
                                echo  $withTimings ?  "Row: $row Cnum " . $recordData['CNUM'] . " Update Row:" . sprintf('%f', $update) . PHP_EOL : null ;
                            } catch (\Exception $e) {
                                echo $e->getMessage();
                                echo $e->getCode();
                                var_dump($e->getTrace());
                                die('here');
                            }
                        } else {
                            echo "Account Person record not found. Failing data: CNUM=" . $recordDataWithDb2Dates['CNUM'] . ', ACCOUNT=' . $recordDataWithDb2Dates['ACCOUNT'] . PHP_EOL;
                            $failedRecords++;
                        }
                    } else {
                        echo "Account or Person record not found. Failing data: CNUM=" . $recordDataWithDb2Dates['CNUM'] . ', ACCOUNT=' . $recordDataWithDb2Dates['ACCOUNT'] . PHP_EOL;
                        $failedRecords++;
                    }
                } else {
                    echo "Invalid date format. Failing data: CNUM=" . $recordDataWithDb2Dates['CNUM'] . ', ACCOUNT=' . $recordDataWithDb2Dates['ACCOUNT'] . PHP_EOL;
                    $failedRecords++;
                }
                $time += microtime(true);
                echo  $withTimings ?  "Row: $row Cnum " . $recordData['CNUM'] . " Total Time:" . sprintf('%f', $time) . PHP_EOL : null;                
            }
        }

        sqlsrv_commit($GLOBALS['conn']);  // Save what we have done.
        // sqlsrv_commit($GLOBALS['conn'],$autoCommit);

        $response = ob_get_clean();
        ob_start();
        $errors = !empty($response);

        // $recordsForLocation = $this->numberOfRecordsForLocation($secureAreaName);

        $elapsed += microtime(true);
        $dataRecords = $row-2;

        echo $errors ? "<span style='color:red'><h2 >Errors writing to DB2 occured</h2><br/>" . $dataRecords . " Records Read from xlsx<br/>$failedRecords failed to update in DB<br/>Error Details Follow:<br/></span>" : "<span  style='color:green'><h3> Well that appears to have gone well !!</h3><br/>" . $dataRecords . " Records Read from xlsx<br/>$failedRecords failed to insert into DB</span>";
        echo "<span style='color:blue'>";
        echo "<br/>Load Run time : ". sprintf('%f Seconds', $elapsed);
        $mSecPerRow = $elapsed / $row;
        echo "<br/>Seconds/Record : " . sprintf('%f', $mSecPerRow) ;
        $rowPerMsec = $row / $elapsed;
        echo "<br/>Records/Second : " . sprintf('%f', $rowPerMsec) ;
        echo "</span>";
        echo "<hr/>";

        echo $response;
    }
}