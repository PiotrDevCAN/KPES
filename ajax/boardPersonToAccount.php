<?php

use itdq\FormClass;
use itdq\Trace;
use itdq\Loader;
use upes\AllTables;
use upes\AccountPersonRecord;
use upes\AccountPersonTable;

Trace::pageOpening($_SERVER['PHP_SELF']);

set_time_limit(0);
ob_start();

$loader = new Loader();
$allAccounts = $loader->loadIndexed('ACCOUNT','ACCOUNT_ID',AllTables::$ACCOUNT);

<<<<<<< HEAD
<<<<<<< HEAD
try {
    $accountPersonRecord = new AccountPersonRecord();
    $accountPersonTable = new AccountPersonTable(AllTables::$ACCOUNT_PERSON);
    $accountPersonRecordData = array_map('trim', $_POST);
    $accountPersonRecord->setFromArray($accountPersonRecordData);
    $emailResponse = $accountPersonRecord->sendNotificationToPesTaskid();
    $saveRecord = $accountPersonTable->insert($accountPersonRecord, false, false); // Only used to save NEW accountPersonRecords
=======

$UPES_REF = !empty($_POST['UPES_REF']) ? trim($_POST['UPES_REF']) : '';
$FULL_NAME = !empty($_POST['FULL_NAME']) ? trim($_POST['FULL_NAME']) : '';
$CONTRACT_ID = !empty($_POST['CONTRACT_ID']) ? trim($_POST['CONTRACT_ID']) : '';
$ACCOUNT_ID = !empty($_POST['ACCOUNT_ID']) ? trim($_POST['ACCOUNT_ID']) : '';
$PES_LEVEL = !empty($_POST['PES_LEVEL']) ? trim($_POST['PES_LEVEL']) : '';
$COUNTRY_OF_RESIDENCE = !empty($_POST['COUNTRY_OF_RESIDENCE']) ? trim($_POST['COUNTRY_OF_RESIDENCE']) : '';
$PES_REQUESTOR = !empty($_POST['PES_REQUESTOR']) ? trim($_POST['PES_REQUESTOR']) : '';
$PES_CREATOR = !empty($_POST['PES_CREATOR']) ? trim($_POST['PES_CREATOR']) : '';
$PES_STATUS = !empty($_POST['PES_STATUS']) ? trim($_POST['PES_STATUS']) : '';

if (empty($UPES_REF) || empty($FULL_NAME) || empty($CONTRACT_ID) || empty($ACCOUNT_ID) || empty($PES_LEVEL) || empty($COUNTRY_OF_RESIDENCE) || empty($PES_REQUESTOR) || empty($PES_CREATOR) || empty($PES_STATUS)) {
    $invalidOtherParameters = true;
>>>>>>> 481c0dfe9947cef192191baa1c37e1d1ccd89b8e

=======

$UPES_REF = !empty($_POST['UPES_REF']) ? trim($_POST['UPES_REF']) : '';
$FULL_NAME = !empty($_POST['FULL_NAME']) ? trim($_POST['FULL_NAME']) : '';
$CONTRACT_ID = !empty($_POST['CONTRACT_ID']) ? trim($_POST['CONTRACT_ID']) : '';
$ACCOUNT_ID = !empty($_POST['ACCOUNT_ID']) ? trim($_POST['ACCOUNT_ID']) : '';
$PES_LEVEL = !empty($_POST['PES_LEVEL']) ? trim($_POST['PES_LEVEL']) : '';
$COUNTRY_OF_RESIDENCE = !empty($_POST['COUNTRY_OF_RESIDENCE']) ? trim($_POST['COUNTRY_OF_RESIDENCE']) : '';
$PES_REQUESTOR = !empty($_POST['PES_REQUESTOR']) ? trim($_POST['PES_REQUESTOR']) : '';
$PES_CREATOR = !empty($_POST['PES_CREATOR']) ? trim($_POST['PES_CREATOR']) : '';
$PES_STATUS = !empty($_POST['PES_STATUS']) ? trim($_POST['PES_STATUS']) : '';

if (empty($UPES_REF) || empty($FULL_NAME) || empty($CONTRACT_ID) || empty($ACCOUNT_ID) || empty($PES_LEVEL) || empty($COUNTRY_OF_RESIDENCE) || empty($PES_REQUESTOR) || empty($PES_CREATOR) || empty($PES_STATUS)) {
    $invalidOtherParameters = true;

>>>>>>> 481c0dfe9947cef192191baa1c37e1d1ccd89b8e
    echo 'Significant parameters from form are missing.';
} else {
    $invalidOtherParameters = false;

    try {
        $accountPersonRecord = new AccountPersonRecord();
        $accountPersonTable = new AccountPersonTable(AllTables::$ACCOUNT_PERSON);
        $accountPersonRecordData = array_map('trim', $_POST);
        $accountPersonRecord->setFromArray($accountPersonRecordData);
        $emailResponse = $accountPersonRecord->sendNotificationToPesTaskid();
    
        $saveRecord = $accountPersonTable->insert($accountPersonRecord); // Only used to save NEW accountPersonRecords
    
    } catch (Exception $e) {
        echo $e->getCode();
        echo $e->getMessage();
        print_r($e->getTrace());
    }
}

$messages = ob_get_clean();
ob_start();
$success = empty($messages);
if($success){
    $messages = " Person: " . $accountPersonRecordData['FULL_NAME'] . "<br/> Will be PES Cleared for :" . $allAccounts[$accountPersonRecordData['ACCOUNT_ID']] . "<br/>";
    $messages.= $_POST['mode']==FormClass::$modeDEFINE ? "Created" : "Updated" ;
}

$response = array('success'=>$success,'saveResponse' => $saveRecord, 'Messages'=>$messages,'emailResponse'=>$emailResponse);

ob_clean();
echo json_encode($response);

Trace::pageLoadComplete($_SERVER['PHP_SELF']);