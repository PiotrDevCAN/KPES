<?php
use itdq\Trace;
use upes\AllTables;
use itdq\DbTable;

Trace::pageOpening($_SERVER['PHP_SELF']);
set_time_limit(0);
ob_start();

if(empty($_POST['peslevelref'])){
    $response = array('success'=>false,'peslevelref' => null, 'Messages'=>"Delete not performed<br/>No Parms passed in");
    ob_clean();
    echo json_encode($response);
    return false;
}


$sql = " DELETE FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$PES_LEVELS ;
$sql.= " WHERE PES_LEVEL_REF='" . htmlspecialchars($_POST['peslevelref']) . "' ";

$rs = sqlsrv_query($GLOBALS['conn'], $sql);

if(!$rs){
    DbTable::displayErrorMessage($rs, __CLASS__, __FILE__, $sql);
}

$messages = ob_get_clean();
ob_start();
$success = $rs && empty($messages);

$response = array('success'=>$success,'peslevelref' => $_POST['peslevelref'], 'Messages'=>$messages);

ob_clean();
echo json_encode($response);

Trace::pageLoadComplete($_SERVER['PHP_SELF']);