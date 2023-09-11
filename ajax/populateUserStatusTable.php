<?php
use itdq\Trace;
use upes\AllTables;
use upes\ContractTable;
use upes\PesLevelTable;
use itdq\DbTable;
use upes\AccountPersonTable;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function ob_html_compress($buf){
    return str_replace(array("\n","\r"),'',$buf);
}

Trace::pageOpening($_SERVER['PHP_SELF']);
// ob_start();

$sqlToAllowPeopleToSeeRequestsForTheirAccounts = " AND A.ACCOUNT_ID in ( ";
$sqlToAllowPeopleToSeeRequestsForTheirAccounts.= "     SELECT DISTINCT ACCOUNT_ID from " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON . " as AP ";
$sqlToAllowPeopleToSeeRequestsForTheirAccounts.= "     WHERE AP.PES_REQUESTOR='" . htmlspecialchars($_SESSION['ssoEmail']) . "' ) " ;
$sqlToAllowPeopleToSeeRequestsForTheirAccounts.= " OR P.EMAIL_ADDRESS = '" . htmlspecialchars($_SESSION['ssoEmail']) . "' ";

$sql = " SELECT '' AS ACTION, P.EMAIL_ADDRESS, P.CNUM,  P.FULL_NAME, A.ACCOUNT, PL.PES_LEVEL, PL.PES_LEVEL_DESCRIPTION, PL.PES_LEVEL_REF ";
$sql.= " , AP.PES_STATUS,AP.PES_CLEARED_DATE, AP.ACCOUNT_ID, A.ACCOUNT_TYPE, AP.UPES_REF, AP.PES_REQUESTOR, AP.PES_DATE_REQUESTED,AP.COUNTRY_OF_RESIDENCE, AP.PROCESSING_STATUS, ADD_HOURS(AP.PROCESSING_STATUS_CHANGED, 1) AS PROCESSING_STATUS_CHANGED, AP.PES_RECHECK_DATE ";
$sql.= " , AP.DATE_LAST_CHASED ";
$sql.= " , AP.OFFBOARDED_DATE, AP.OFFBOARDED_BY, C.CONTRACT, AP.CONTRACT_ID ";
$sql.= " FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$PERSON . " as P ";
$sql.= " LEFT JOIN  " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON . " as AP ";
$sql.= " ON P.UPES_REF = AP.UPES_REF ";
$sql.= " LEFT JOIN  " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT . " as A ";
$sql.= " ON AP.ACCOUNT_ID = A.ACCOUNT_ID ";
$sql.= " LEFT JOIN  " . $GLOBALS['Db2Schema'] . "." . AllTables::$CONTRACT . " as C ";
$sql.= " ON AP.CONTRACT_ID = C.CONTRACT_ID ";
$sql.= " LEFT JOIN  " . $GLOBALS['Db2Schema'] . "." . AllTables::$PES_LEVELS . " as PL ";
$sql.= " ON AP.PES_LEVEL = PL.PES_LEVEL_REF ";
$sql.= " WHERE A.ACCOUNT is not null and AP.UPES_REF is not null ";
$sql.= $_SESSION['isPesTeam'] ? null : $sqlToAllowPeopleToSeeRequestsForTheirAccounts;

error_log(__FILE__ . __LINE__ . $_SESSION['ssoEmail']);
error_log($sql);

$rs = sqlsrv_query($GLOBALS['conn'], $sql);

if(!$rs){
    DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
}

$data = array();
while(($row = sqlsrv_fetch_array($rs))==true){
    $rowWithActionButtons = AccountPersonTable::addButtonsForPeopleReport(array_map('trim', $row));
    $data[] = $rowWithActionButtons;
}

$messages = ob_get_clean();
$Success = empty($messages);

if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
    if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
        ob_start("ob_gzhandler");
    } else {
        ob_start("ob_html_compress");
    }
} else {
    ob_start("ob_html_compress");
}

$response = array('data'=>$data,'success'=>$Success,'messages'=>$messages, 'sql'=>$sql);
echo json_encode($response);
Trace::pageLoadComplete($_SERVER['PHP_SELF']);