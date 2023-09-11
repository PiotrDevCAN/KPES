<?php
use itdq\DbTable;
use upes\AllTables;
use upes\AccountPersonRecord;

$months = array('','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');

$twelveMonthsAgo = new DateTime("first day of this month");

$sql = "";
$sql.= " select trim(A.ACCOUNT) as ACCOUNT, trim(C.CONTRACT) as CONTRACT, YEAR(PES_DATE_RESPONDED) as YEAR, MONTH(PES_DATE_RESPONDED) as MONTH, count(distinct UPES_REF) as Prov_Cleared ";
$sql.= " from ( ";
$sql.= " select distinct  PES_STATUS, PES_DATE_RESPONDED, ACCOUNT_ID, CONTRACT_ID, UPES_REF ";
$sql.= " FROM ( ";

$sql.= " select distinct  PES_STATUS, PES_DATE_RESPONDED, ACCOUNT_ID, CONTRACT_ID, UPES_REF ";
$sql.= " from " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON_HIST .  "  ";
$sql.= " where PES_STATUS = '" . AccountPersonRecord::PES_STATUS_PROVISIONAL . "' ";
$sql.= " AND PES_DATE_RESPONDED >= date('" . $twelveMonthsAgo->format('Y-m-d') . "') - 11 Months ";
$sql.= " UNION ";
$sql.= "  select distinct  PES_STATUS, PES_DATE_RESPONDED, ACCOUNT_ID, CONTRACT_ID, UPES_REF ";
$sql.= " from " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON .  " AS AP ";
$sql.= " where PES_STATUS = '" . AccountPersonRecord::PES_STATUS_PROVISIONAL . "' ";
$sql.= " AND PES_DATE_RESPONDED >= date('" . $twelveMonthsAgo->format('Y-m-d') . "') - 11 Months ";
$sql.= " ) ";
$sql.= " ) as AH ";
$sql.= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT .  " AS A ";
$sql.= " on AH.ACCOUNT_ID = A.ACCOUNT_ID "; 
$sql.= " LEFT JOIN " . $GLOBALS['Db2Schema'] . "." . AllTables::$CONTRACT .  " AS C ";
$sql.= " on AH.CONTRACT_ID = C.CONTRACT_ID "; 
$sql.= " where  A.ACCOUNT is not null ";
$sql.= " group by ACCOUNT, CONTRACT, YEAR(PES_DATE_RESPONDED), MONTH(PES_DATE_RESPONDED)";
$sql.= " ORDER BY 1, 2 desc, 3 desc ";

$rs = sqlsrv_query($GLOBALS['conn'],$sql);

if(!$rs){
    DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);    
}

$byMonth = array();
$allContracts = array();
$maxYear = 0;
$minYear = 9999;

while ($row = sqlsrv_fetch_array($rs)) {
    $maxYear = $row['YEAR'] > $maxYear ? $row['YEAR'] : $maxYear;
    $minYear = $row['YEAR'] < $minYear ? $row['YEAR'] : $minYear;
    
    $byMonth[$row['YEAR']][$row['MONTH']][$row['ACCOUNT']][$row['CONTRACT']] = $row['PROV_CLEARED'];
    $allContracts[$row['ACCOUNT']] = $row['CONTRACT'];
    $accountTotals[$row['ACCOUNT']][$row['CONTRACT']] = 0;
};

if (count($byMonth) > 0) {
    krsort($byMonth[$maxYear]);
    krsort($byMonth[$minYear]);
    krsort($byMonth);
}

?>
<div class='container'>
<h3>Pes Provisionally Cleared per Month, by Account, Year To Date</h3>
<table id='miReportProv' class='table table-responsive hover stripe'  data-page-length='25'>
<thead>
<tr><th>Month</th>
<?php 
foreach ($allContracts as $accountName => $contractName){
    ?><th>(<?=$accountName;?>)<br><?=$contractName;?></th><?php     
}
?>
<th>Total</th></tr>
</thead>
<tbody>
<?php 
$total = 0;
foreach ($byMonth as $year => $monthlyValues) {
    foreach ($monthlyValues as $month => $accountValues) {
        $MonthlyTotal=0;
        $sortableDate = $year . substr("00".$month, -2,2);
        ?><tr><th data-order="<?=$sortableDate;?>" ><?=$months[$month] . "&nbsp;" . $year;?></th><?php 
        foreach ($allContracts as $accountName => $contractName) {
            ?><td><?=isset($accountValues[$accountName][$contractName]) ? $accountValues[$accountName][$contractName] : '';?></td>
            <?php  
            $MonthlyTotal                +=isset($accountValues[$accountName][$contractName]) ? $accountValues[$accountName][$contractName] : 0;
            $accountTotals[$accountName][$contractName] +=isset($accountValues[$accountName][$contractName]) ? $accountValues[$accountName][$contractName] : 0;
            $total                       +=isset($accountValues[$accountName][$contractName]) ? $accountValues[$accountName][$contractName] : 0;
        }
        
        ?><td><?=$MonthlyTotal?></td></tr><?php 
    }
}
?>
</tbody>
<tfoot>
<tr><th>Totals</th>
<?php 
foreach ($allContracts as $accountName => $contractName) {
    ?><th><?=$accountTotals[$accountName][$contractName];?></th><?php     
}
?>
<th><?=$total;?></th></tr>
</tfoot>

</table>
</div>