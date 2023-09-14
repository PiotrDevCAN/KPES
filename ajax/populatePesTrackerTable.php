<?php
use upes\AllTables;
use upes\AccountPersonTable;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function ob_html_compress($buf){
    return str_replace(array("\n","\r"),'',$buf);
}

set_time_limit(0);
// ob_start();

$draw = isset($_REQUEST['draw']) ? $_REQUEST['draw'] * 1 : 1 ;
$start = isset($_REQUEST['start']) ? $_REQUEST['start'] * 1 : 1 ;
$length = isset($_REQUEST['length']) ? $_REQUEST['length'] : 50;

// $predicate = '';
// if(!empty($_REQUEST['search']['value'])) {
//     $searchValue = htmlspecialchars(trim($_REQUEST['search']['value']));
//     $predicate .= " AND (REGEXP_LIKE(P.PASSPORT_FIRST_NAME, '". $searchValue . "', 'i')";
//     $predicate .= " OR REGEXP_LIKE(P.PASSPORT_LAST_NAME, '". $searchValue . "', 'i')";
//     $predicate .= " OR REGEXP_LIKE(P.CNUM, '". $searchValue . "', 'i')";
//     $predicate .= " OR REGEXP_LIKE(P.IBM_STATUS, '". $searchValue . "', 'i')";
//     $predicate .= " OR REGEXP_LIKE(P.COUNTRY, '". $searchValue . "', 'i')";
//     $predicate .= " OR REGEXP_LIKE(AP.COUNTRY_OF_RESIDENCE, '". $searchValue . "', 'i')";
//     $predicate .= " OR REGEXP_LIKE(P.UPES_REF, '". $searchValue . "', 'i')";
//     $predicate .= " OR REGEXP_LIKE(A.ACCOUNT_ID, '". $searchValue . "', 'i')";
//     $predicate .= " OR REGEXP_LIKE(A.ACCOUNT_TYPE, '". $searchValue . "', 'i')";
//     $predicate .= " OR REGEXP_LIKE(A.ACCOUNT, '". $searchValue . "', 'i')";
//     $predicate .= " OR REGEXP_LIKE(P.FULL_NAME, '". $searchValue . "', 'i')";
//     $predicate .= " OR REGEXP_LIKE(P.EMAIL_ADDRESS, '". $searchValue . "', 'i')";
//     $predicate .= " OR REGEXP_LIKE(PL.PES_LEVEL, '". $searchValue . "', 'i')";
//     $predicate .= " OR REGEXP_LIKE(PL.PES_LEVEL_DESCRIPTION, '". $searchValue . "', 'i')";
//     $predicate .= " OR REGEXP_LIKE(AP.PES_REQUESTOR, '". $searchValue . "', 'i')";
//     $predicate .= " OR REGEXP_LIKE(AP.PES_DATE_REQUESTED, '". $searchValue . "', 'i')";
//     $predicate .= " OR REGEXP_LIKE(AP.DATE_LAST_CHASED, '". $searchValue . "', 'i')";
//     $predicate .= " OR REGEXP_LIKE(AP.PES_STATUS, '". $searchValue . "', 'i')";
//     $predicate .= " OR REGEXP_LIKE(AP.COMMENT, '". $searchValue . "', 'i')";
//     $predicate .= ") ";
// }

function addFieldToSearch($searchValue = null, $column = '')
{
    $searchPredicate = ''; {
        $searchPredicate .= " " . $column . " LIKE '%$searchValue%'";
    }
    return $searchPredicate;
}

$predicate = '';
if(!empty($_REQUEST['search']['value'])) {
    $searchValue = htmlspecialchars(trim($_REQUEST['search']['value']));
    $predicate .= " AND (";
    $predicate .= addFieldToSearch($searchValue, 'P.PASSPORT_FIRST_NAME');
    $predicate .= " OR " . addFieldToSearch($searchValue, 'P.PASSPORT_LAST_NAME');
    $predicate .= " OR " . addFieldToSearch($searchValue, 'P.CNUM');
    $predicate .= " OR " . addFieldToSearch($searchValue, 'P.IBM_STATUS');
    $predicate .= " OR " . addFieldToSearch($searchValue, 'P.COUNTRY');
    $predicate .= " OR " . addFieldToSearch($searchValue, 'AP.COUNTRY_OF_RESIDENCE');
    $predicate .= " OR " . addFieldToSearch($searchValue, 'P.UPES_REF');
    $predicate .= " OR " . addFieldToSearch($searchValue, 'A.ACCOUNT_ID');
    $predicate .= " OR " . addFieldToSearch($searchValue, 'A.ACCOUNT_TYPE');
    $predicate .= " OR " . addFieldToSearch($searchValue, 'A.ACCOUNT');
    $predicate .= " OR " . addFieldToSearch($searchValue, 'P.FULL_NAME');
    $predicate .= " OR " . addFieldToSearch($searchValue, 'P.EMAIL_ADDRESS');
    $predicate .= " OR " . addFieldToSearch($searchValue, 'PL.PES_LEVEL');
    $predicate .= " OR " . addFieldToSearch($searchValue, 'PL.PES_LEVEL_DESCRIPTION');
    $predicate .= " OR " . addFieldToSearch($searchValue, 'AP.PES_REQUESTOR');
    $predicate .= " OR " . addFieldToSearch($searchValue, 'AP.PES_DATE_REQUESTED');
    $predicate .= " OR " . addFieldToSearch($searchValue, 'AP.DATE_LAST_CHASED');
    $predicate .= " OR " . addFieldToSearch($searchValue, 'AP.PES_STATUS');
    $predicate .= " OR " . addFieldToSearch($searchValue, 'AP.COMMENT');
    $predicate .= ") ";
}

$pesTrackerTable = new AccountPersonTable(AllTables::$ACCOUNT_PERSON);
$records = empty($_REQUEST['records']) ? AccountPersonTable::PES_TRACKER_RECORDS_ACTIVE : $_REQUEST['records'];

// $table = $pesTrackerTable->buildTable($records);
$dataAndSql = $pesTrackerTable->returnAsArray($records, 'array', null, null, $start, $length, $predicate);
list(
    'data' => $data, 
    'sql' => $sql
) = $dataAndSql;

// $sql = $pesTrackerTable->preparePesEventsStmt($records);
$total = $pesTrackerTable->totalRows($records, 'array', null, null);
$filtered = $pesTrackerTable->recordsFiltered($records, 'array', null, null, $predicate);

// $dataJsonAble = json_encode($table);
$dataJsonAble = json_encode($data);

if($dataJsonAble) {
    $messages = ob_get_clean();
    $success = empty($messages);
    $response = array(
        "draw"=>$draw,
        "data"=>$data,
        'recordsTotal'=>$total,
        'recordsFiltered'=>$filtered,
        "error"=>$messages,
        "sql"=>$sql
    );
    
    if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
        if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
            ob_start("ob_gzhandler");
        } else {
            ob_start("ob_html_compress");
        }
    } else {
        ob_start("ob_html_compress");
    }
    echo json_encode($response);
} else {
    var_dump($dataJsonAble);
    $messages = ob_get_clean();
    // ob_start();
    if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
        if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
            ob_start("ob_gzhandler");
        } else {
            ob_start("ob_html_compress");
        }
    } else {
        ob_start("ob_html_compress");
    }
    $success = empty($messages);
    $response = array("success"=>$success,'messages'=>$messages);
    echo json_encode($response);
}

