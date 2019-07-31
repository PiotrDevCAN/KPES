<?php
use itdq\Trace;
use upes\AllTables;
use upes\ContractTable;
use upes\PesLevelTable;
use itdq\DbTable;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

Trace::pageOpening($_SERVER['PHP_SELF']);
ob_start();

$sql = " SELECT '' AS ACTION, P.EMAIL_ADDRESS, P.FULL_NAME, A.ACCOUNT, PL.PES_LEVEL, PL.PES_LEVEL_DESCRIPTION, AP.PES_STATUS ";
$sql.= " FROM " . $_SESSION['Db2Schema'] . "." . AllTables::$PERSON . " as P ";
$sql.= " LEFT JOIN  " . $_SESSION['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON . " as AP ";
$sql.= " ON P.UPES_REF = AP.UPES_REF ";
$sql.= " LEFT JOIN  " . $_SESSION['Db2Schema'] . "." . AllTables::$ACCOUNT . " as A ";
$sql.= " ON AP.ACCOUNT_ID = AP.ACCOUNT_ID ";
$sql.= " LEFT JOIN  " . $_SESSION['Db2Schema'] . "." . AllTables::$PES_LEVELS . " as PL ";
$sql.= " ON AP.PES_LEVEL = PL.PES_LEVEL_REF ";
$sql.= " WHERE A.ACCOUNT is not null ";

$rs = db2_exec($_SESSION['conn'], $sql);

if(!$rs){
    DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
}


while(($row=db2_fetch_assoc($rs))==true){
    $data[] = array_map('trim', $row);
}

$messages = ob_get_clean();
$Success = empty($messages);

$response = array('data'=>$data,'success'=>$Success,'messages'=>$messages);
echo json_encode($response);
Trace::pageLoadComplete($_SERVER['PHP_SELF']);