<?php

use upes\PesEmail;
use upes\AccountPersonRecord;
use upes\AccountPersonTable;
use upes\AllTables;
use upes\PersonRecord;
use upes\PersonTable;

ob_start();
$pesEmailObj = new PesEmail();
$accountPersonTable = new AccountPersonTable(AllTables::$ACCOUNT_PERSON);
$personTable = new PersonTable(AllTables::$PERSON);
$personRecord = new PersonRecord();
$personRecord->setFromArray(array('UPES_REF'=>$_POST['upesref']));
$personRecordData = $personTable->getRecord($personRecord);
$names = explode(" ", $personRecordData['FULL_NAME']);

db2_autocommit($GLOBALS['conn'],DB2_AUTOCOMMIT_OFF);

$emailDetails = array();

try {

    $sendResponse = PesEmail::sendPesApplicationForms($_POST['account'], $_POST['country'], $personRecordData['CNUM'],  $personRecordData['FULL_NAME'], $names[0], $personRecordData['EMAIL_ADDRESS'],$_POST['recheck']);

    $indicateRecheck = strtolower($_POST['recheck']) == 'yes' ? "(recheck)" : null;
    $nextStatus = strtolower($_POST['recheck']) == 'yes' ? AccountPersonRecord::PES_STATUS_RECHECK_PROGRESSING : AccountPersonRecord::PES_STATUS_PES_PROGRESSING ;

    $accountPersonTable->setPesStatus($_POST['upesref'],$_POST['accountid'],$nextStatus,$_SESSION['ssoEmail'],'PES Application form sent:' . $sendResponse['Status']);
    $accountPersonTable->savePesComment($_POST['upesref'],$_POST['accountid'], "PES application forms $indicateRecheck sent:" . $sendResponse['Status'] );

    $accountPersonTable->setPesProcessStatus($_POST['upesref'],$_POST['accountid'],AccountPersonTable::PROCESS_STATUS_USER);
    $accountPersonTable->savePesComment($_POST['upesref'],$_POST['accountid'],  "Process Status set to " . AccountPersonTable::PROCESS_STATUS_USER );

} catch ( \Exception $e) {
    switch ($e->getCode()) {
        case 803:
            $emailDetails['warning']['filename'] = 'No email exists for combination of Internal/External and Country';
            echo "Warning";
        break;
        default:
            var_dump($e);
        break;
    }
}

// can afford to code "ALL" here because we're supplying the UPESREF & Account ID - so will only get 1 record anyway
$data = AccountPersonTable::returnPesEventsTable(AccountPersonTable::PES_TRACKER_RECORDS_ALL, AccountPersonTable::PES_TRACKER_RETURN_RESULTS_AS_ARRAY,$_POST['upesref'],$_POST['accountid']);

$pesStatusField = AccountPersonRecord::getPesStatusWithButtons($data[0]);
$processingStatusField =  AccountPersonTable::formatProcessingStatusCell($data[0]);

db2_commit($GLOBALS['conn']);
db2_autocommit($GLOBALS['conn'],DB2_AUTOCOMMIT_ON);

$pesCommentField = $data[0]['COMMENT'];

$messages = ob_get_clean();
ob_start();
$success = strlen($messages)==0;

$emailDetails['success'] = $success;
$emailDetails['messages'] = $messages;
$emailDetails['cnum'] = $data[0]['CNUM'];
$emailDetails['comment'] = $pesCommentField;
$emailDetails['pesStatus'] = $pesStatusField;
$emailDetails['processingStatus'] = $processingStatusField;
$emailDetails['data'] = $data[0];
$emailDetails['sendResponse'] = $sendResponse['sendResponse'];

ob_clean();
echo json_encode($emailDetails);