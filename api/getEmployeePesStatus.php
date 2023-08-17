<?php

use upes\AllTables;

ob_start();

$callToken = !empty($_REQUEST['token']) ? $_REQUEST['token'] : null;

$emailId = !empty($_REQUEST['emailId']) ? $_REQUEST['emailId'] : null;
$CNUM = !empty($_REQUEST['cnum']) ? $_REQUEST['cnum'] : null;

if($callToken!= $token) {
    header('Content-Type: application/json');
    echo json_encode(array('success'=>false,'data'=>array(),'messages'=>'Invalid Token'));
    return;    
}

if(empty($emailId) && empty($CNUM)) {
    header('Content-Type: application/json');
    echo json_encode(array('success'=>false,'data'=>array(),'messages'=>'Invalid Employee Data'));
    return;    
}

$predicate = " 1=1 ";
$predicate .= ! empty($emailId) ? " AND lower(P.EMAIL_ADDRESS)='" . htmlspecialchars(strtolower($emailId)) . "' " : null;
$predicate .= ! empty($CNUM) ? " AND P.CNUM='" . htmlspecialchars($CNUM) . "' " : null;

$sql = " select AP.PES_STATUS ";
$sql.= "from " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON . " as AP "; 
$sql.= "left join " . $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON . " as P ";
$sql.= "on AP.UPES_REF = P.UPES_REF ";
$sql.= " WHERE 1=1 " ;
$sql.= !empty($predicate) ? " AND  $predicate " : null ;

$rs = sqlsrv_query($GLOBALS['conn'], $sql);
if (! $rs) {
    self::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
    return false;
}

$data = array();
$count = 1;
while(($row=sqlsrv_fetch_array($rs))==true){
    $row = array_map('trim',$row);
    $data[] = $row;
}

$messages = ob_get_clean();

$success = empty($messages);

header('Content-Type: application/json');
echo json_encode(array('success'=>$success,'data'=>$data,'messages'=>$messages));

