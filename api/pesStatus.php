<?php

use upes\AllTables;

function ob_html_compress($buf){
    return str_replace(array("\n","\r"),'',$buf);
}

if($_REQUEST['token']!= $token) {
    header('Content-Type: application/json');
    echo json_encode(array('success'=>false,'data'=>array(),'messages'=>'Invalid Token'));    
    return;    
}

$noTrim = !empty($_REQUEST['noTrim']) ? true : false;

$sql = " SELECT AP.ACCOUNT_ID, P.CNUM, P.EMAIL_ADDRESS, PROCESSING_STATUS, PROCESSING_STATUS_CHANGED ";
$sql.= ", PES_DATE_REQUESTED, PES_REQUESTOR, PES_DATE_RESPONDED, PES_STATUS_DETAILS, PES_STATUS";
$sql.= ", PL.PES_LEVEL, PES_RECHECK_DATE, PES_CLEARED_DATE ";
$sql.= "FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON . " AS AP "; 
$sql.= "LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON . " AS P ";
$sql.= "ON AP.UPES_REF = P.UPES_REF ";
$sql.= "LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . AllTables::$PES_LEVELS . " AS PL ";
$sql.= "ON AP.PES_LEVEL = PL.PES_LEVEL_REF AND AP.ACCOUNT_ID = PL.ACCOUNT_ID ";
$sql.= "WHERE AP.ACCOUNT_ID = '" . db2_escape_string($_GET['accountid']) . "' ";

ob_start();

$rs = DB2_EXEC($GLOBALS['conn'], $sql);
if (! $rs) {
    self::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
    return false;
}

$endExec = microtime(true);
$timeMeasurements['db_exec'] = (float)($endExec-$start);

$startDataTrim = microtime(true);

$data = array();
$count = 1;
while(($row=db2_fetch_assoc($rs))==true){
    if ($noTrim === false) {
        $row = array_map('trim',$row);
    }
    $data[] = $row;
    $count++;
}

$endDataTrim = microtime(true);
$timeMeasurements['data_trim'] = (float)($endDataTrim-$startDataTrim);

$messages = ob_get_clean();
$success = empty($messages);

if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
    if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
        ob_start("ob_gzhandler");
    } else {
        ob_start("ob_html_compress");
    }
} else {
    ob_start("ob_html_compress");
}

header('Content-Type: application/json');
echo json_encode(array(
    'success'=>$success,
    'data'=>$data,
    'messages'=>$messages,
    'timing'=>$timeMeasurements
));