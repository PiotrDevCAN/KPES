<?php

use itdq\BlueMail;
use itdq\DbTable;
use upes\AccountPersonRecord;
use upes\allTables;
use upes\PesEmail;

set_time_limit(0);
ini_set('memory_limit', '4096M');

// $_ENV['email'] = 'on';

// require_once __DIR__ . '/../../src/Bootstrap.php';
// $helper = new Sample();
// if ($helper->isCli()) {
//     $helper->log('This example should only be run from a Web Browser' . PHP_EOL);
//     return;
// }

$noreplemailid = $_ENV['noreplykyndrylemailid'];
// $emailAddress = array(
//     // 'philip.bibby@kyndryl.com',
//     $_ENV['automationemailid']
// );
$emailAddress = array(
    'piotr.tajanowicz@kyndryl.com',
);
$emailAddressCC = array();
$emailAddressBCC = array();

try {

    $sql = " SELECT
        AP.UPES_REF,
        AP.ACCOUNT_ID,
        AP.COUNTRY_OF_RESIDENCE,
        AP.PES_STATUS,
        AP.PES_CLEARED_DATE,
        AP.PES_RECHECK_DATE,
        P.CNUM,
        P.FULL_NAME,
        P.EMAIL_ADDRESS,
        A.ACCOUNT ";
    $sql .= " FROM " . $GLOBALS['Db2Schema'] . "." . allTables::$ACCOUNT_PERSON . " AS AP ";
    $sql .= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . allTables::$PERSON . " AS P ";
    $sql .= " ON AP.UPES_REF = P.UPES_REF ";
    $sql .= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . allTables::$ACCOUNT . " AS A ";
    $sql .= " ON AP.ACCOUNT_ID = A.ACCOUNT_ID ";
    $sql .= " WHERE 1=1 AND trim(AP.PES_STATUS) = '" . AccountPersonRecord::PES_STATUS_STARTER_REQUESTED . "' ";

    $rs = sqlsrv_query($GLOBALS['conn'], $sql);
    if (!$rs) {
        DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
    }
    $detailsOfStarters = array();
    while (($row = sqlsrv_fetch_array($rs)) == true) {
        $trimmedRow = array_map('trim', $row);
        $detailsOfStarters[] = $trimmedRow;
    }

    if (count($detailsOfStarters) > 0) {
        $pesEmailObj = new PesEmail();
        foreach ($detailsOfStarters as $key => $record) {
            $emailResponse = $pesEmailObj->sendPesEmailStarter($record);
            $emailStatus = $emailResponse['Status'];
            $emailMessage = $emailResponse['sendResponse']['response'];
            $detailsOfStarters[$key]['status'] = $emailMessage;
        }
        PesEmail::notifyStarterRequired($detailsOfStarters);
    }

    $subject = 'Send out Starters in the kPES';
    $message = 'Confirmation of sending out starters';
    $result = BlueMail::send_mail($emailAddress, $subject, $message, $noreplemailid, $emailAddressCC, $emailAddressBCC, true);
    // trigger_error('BlueMail::send_mail result: ' . serialize($result), E_USER_WARNING);

} catch (Exception $e) {
    $subject = 'Error in: send Starters kPES ';
    $message = $e->getMessage() . ' ' . $e->getLine() . ' ' . $e->getFile();

    $to = array('piotr.tajanowicz@kyndryl.com');
    $cc = array();
    $replyto = $_ENV['noreplyemailid'];

    $resonse = BlueMail::send_mail($to, $subject, $message, $replyto, $cc);
    // trigger_error($subject . " - " . $message, E_USER_ERROR);
}
