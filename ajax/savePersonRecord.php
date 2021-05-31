<?php

use itdq\FormClass;
use itdq\Trace;
use upes\AllTables;
use upes\PersonTable;
use upes\PersonRecord;

Trace::pageOpening($_SERVER['PHP_SELF']);

set_time_limit(0);
ob_start();

$ibmer = !empty($_POST['ibmer']) ? trim($_POST['ibmer']) : PersonRecord::IBM_STATUS_NOT_IBMER;
$CNUM = !empty($_POST['CNUM']) ? trim($_POST['CNUM']) : '';
$EMAIL_ADDRESS = !empty($_POST['EMAIL_ADDRESS']) ? trim($_POST['EMAIL_ADDRESS']) : '';
$FULL_NAME = !empty($_POST['FULL_NAME']) ? trim($_POST['FULL_NAME']) : '';
$COUNTRY = !empty($_POST['COUNTRY']) ? trim($_POST['COUNTRY']) : '';
$IBM_STATUS = !empty($_POST['IBM_STATUS']) ? trim($_POST['IBM_STATUS']) : '';
$PES_ADDER = !empty($_POST['PES_ADDER']) ? trim($_POST['PES_ADDER']) : '';

if (($ibmer !== PersonRecord::IBM_STATUS_NOT_IBMER && empty($CNUM)) || empty($EMAIL_ADDRESS) || empty($FULL_NAME) || empty($COUNTRY) || empty($IBM_STATUS) || empty($PES_ADDER)) {
    $invalidOtherParameters = true;

    echo 'Significant parameters from form are missing.';
} else {
    $invalidOtherParameters = false;

    try {
        $personRecord = new PersonRecord();
        $personTable = new PersonTable(AllTables::$PERSON);
        $personRecordRecordData = array_map('trim', $_POST);
    
        $personRecordRecordData['UPES_REF'] = $_POST['mode']==FormClass::$modeDEFINE ? null : $personRecordRecordData['UPES_REF'];
    
        $personRecord->setFromArray($personRecordRecordData);
    
        $saveRecord = $_POST['mode']==FormClass::$modeDEFINE ? $personTable->insert($personRecord) : $personTable->update($personRecord, false, false);
        $upesRef  = $_POST['mode']==FormClass::$modeDEFINE ? $personTable->lastId() : $personRecordRecordData['UPES_REF'];
    
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
    $messages = " Person: " . $personRecordData['EMAIL_ADDRESS'] . "<br/> uPES Ref:" . $upesRef . "<br/>";
    $messages.= $_POST['mode']==FormClass::$modeDEFINE ? "Created" : "Updated" ;
}

$response = array('success'=>$success,'upesref' => $upesRef, 'saveResponse' => $saveRecord, 'Messages'=>$messages);

ob_clean();
echo json_encode($response);

Trace::pageLoadComplete($_SERVER['PHP_SELF']);