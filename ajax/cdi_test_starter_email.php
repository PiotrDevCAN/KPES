<?php

use upes\PesEmail;
use upes\AccountPersonRecord;
use upes\AccountPersonTable;
use upes\AllTables;
use upes\PersonRecord;
use upes\PersonTable;

$upesref = '818950';
$account = 'Lloyds';
$accountid = '1330';
$country = 'India';
$recheck = 'no';

ob_start();
$pesEmailObj = new PesEmail();
$accountPersonTable = new AccountPersonTable(AllTables::$ACCOUNT_PERSON);
$personTable = new PersonTable(AllTables::$PERSON);
$personRecord = new PersonRecord();
$personRecord->setFromArray(array('UPES_REF'=>$upesref));
$personRecordData = $personTable->getRecord($personRecord);
$names = explode(" ", $personRecordData['FULL_NAME']);

sqlsrv_commit($GLOBALS['conn'],DB2_AUTOCOMMIT_OFF);

try {

    $sendResponse = PesEmail::sendPesApplicationForms($account, $country, $personRecordData['CNUM'],  $personRecordData['FULL_NAME'], $names[0],array($personRecordData['EMAIL_ADDRESS']),$recheck);

    // $indicateRecheck = strtolower($_POST['recheck']) == 'yes' ? "(recheck)" : null;
    // $nextStatus = strtolower($_POST['recheck']) == 'yes' ? AccountPersonRecord::PES_STATUS_RECHECK_PROGRESSING : AccountPersonRecord::PES_STATUS_PES_PROGRESSING ;

    // $accountPersonTable->setPesStatus($upesref,$accountid,$nextStatus,$_SESSION['ssoEmail'],'PES Application form sent:' . $sendResponse['Status']);
    // $accountPersonTable->savePesComment($upesref,$accountid, "PES application forms $indicateRecheck sent:" . $sendResponse['Status'] );

    // $accountPersonTable->setPesProcessStatus($upesref,$accountid,AccountPersonTable::PROCESS_STATUS_USER);
    // $accountPersonTable->savePesComment($upesref,$accountid,  "Process Status set to " . AccountPersonTable::PROCESS_STATUS_USER );

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

echo '<pre>';
var_dump($sendResponse);
echo '</pre>';