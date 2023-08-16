<?php

use itdq\DbTable;
use upes\PesEmail;
use upes\AccountPersonRecord;
use upes\AccountPersonTable;
use upes\AllTables;
use upes\PersonRecord;
use upes\PersonTable;

$sql = " SELECT AP.UPES_REF, A.ACCOUNT, AP.ACCOUNT_ID, P.COUNTRY, P.EMAIL_ADDRESS, P.CNUM, P.FULL_NAME, P.EMAIL_ADDRESS";
$sql .= " FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON . " P";
$sql .= " INNER JOIN " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON . " AP";
$sql .= " ON P.UPES_REF = AP.UPES_REF";
$sql .= " INNER JOIN " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT . " A";
$sql .= " ON AP.ACCOUNT_ID = A.ACCOUNT_ID";
$sql .= " WHERE AP.COMMENT like '%sent:Errored%'";

$rs = sqlsrv_query($GLOBALS['conn'],$sql);

if(!$rs){
    DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);    
}

while ($row=sqlsrv_fetch_array($rs)) {

    $upesref = trim($row['UPES_REF']);
    $account = trim($row['ACCOUNT']);
    $accountid = trim($row['ACCOUNT_ID']);
    $country = trim($row['COUNTRY']);
    $recheck = 'no';

    $cnum = trim($row['CNUM']);
    $fullName = trim($row['FULL_NAME']);
    $emailAddress = trim($row['EMAIL_ADDRESS']);
    $names = explode(" ", $fullName);

    echo '<pre>';
    var_dump($emailAddress);
    echo '</pre>';

    // $accountPersonTable = new AccountPersonTable(AllTables::$ACCOUNT_PERSON);

    // sqlsrv_commit($GLOBALS['conn'],sqlsrv_commit_OFF);

    // $emailDetails = array();

    // try {

    //     $sendResponse = PesEmail::sendPesApplicationForms($account, $country, $cnum,  $fullName, $names[0], array($emailAddress),$recheck);

    //     $indicateRecheck = strtolower($recheck) == 'yes' ? "(recheck)" : null;
    //     $nextStatus = strtolower($recheck) == 'yes' ? AccountPersonRecord::PES_STATUS_RECHECK_PROGRESSING : AccountPersonRecord::PES_STATUS_PES_PROGRESSING ;

    //     $accountPersonTable->setPesStatus($upesref,$accountid,$nextStatus,$_SESSION['ssoEmail'],'PES Application form sent:' . $sendResponse['Status']);
    //     $accountPersonTable->savePesComment($upesref,$accountid, "PES application forms $indicateRecheck sent:" . $sendResponse['Status'] );

    //     $accountPersonTable->setPesProcessStatus($upesref,$accountid,AccountPersonTable::PROCESS_STATUS_USER);
    //     $accountPersonTable->savePesComment($upesref,$accountid,  "Process Status set to " . AccountPersonTable::PROCESS_STATUS_USER );

    // } catch ( \Exception $e) {
    //     switch ($e->getCode()) {
    //         case 803:
    //             $emailDetails['warning']['filename'] = 'No email exists for combination of Internal/External and Country';
    //             echo "Warning";
    //         break;
    //         default:
    //             var_dump($e);
    //         break;
    //     }
    // }

    // // can afford to code "ALL" here because we're supplying the UPESREF & Account ID - so will only get 1 record anyway
    // $data = AccountPersonTable::returnPesEventsTable(AccountPersonTable::PES_TRACKER_RECORDS_ALL, AccountPersonTable::PES_TRACKER_RETURN_RESULTS_AS_ARRAY,$upesref,$accountid);

    // $pesStatusField = AccountPersonRecord::getPesStatusWithButtons($data[0]);
    // $processingStatusField =  AccountPersonTable::formatProcessingStatusCell($data[0]);

    // sqlsrv_commit($GLOBALS['conn']);
    // sqlsrv_commit($GLOBALS['conn'],sqlsrv_commit_ON);

    // $pesCommentField = $data[0]['COMMENT'];

    // $success = strlen($messages)==0;

    // $emailDetails['success'] = $success;
    // $emailDetails['cnum'] = $data[0]['CNUM'];
    // $emailDetails['comment'] = $pesCommentField;
    // $emailDetails['pesStatus'] = $pesStatusField;
    // $emailDetails['processingStatus'] = $processingStatusField;
    // $emailDetails['data'] = $data[0];
    // $emailDetails['sendResponse'] = $sendResponse['sendResponse'];

    // echo json_encode($emailDetails);

};