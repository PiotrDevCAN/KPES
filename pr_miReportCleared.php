<?php
use itdq\DbTable;
use upes\AccountPersonTable;
use upes\AllTables;

$months = array('','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');

$twelveMonthsAgo = new DateTime("first day of this month");

$sql = "";
$sql.= " select trim(A.ACCOUNT) as ACCOUNT, YEAR(PES_CLEARED_DATE) as YEAR, MONTH(PES_CLEARED_DATE) as MONTH, count(*) as Cleared ";
$sql.= " from " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT_PERSON . " as AP ";
$sql.= " left join " . $GLOBALS['Db2Schema'] . "." . AllTables::$ACCOUNT . " as A  ";
$sql.= " on AP.ACCOUNT_ID = A.ACCOUNT_ID "; 
$sql.= " where PES_CLEARED_DATE >= date('" . $twelveMonthsAgo->format('Y-m-d') . "') - 11 Months ";
$sql.= " AND A.ACCOUNT is not null ";
$sql.= " group by ACCOUNT, YEAR(PES_CLEARED_DATE), MONTH(PES_CLEARED_DATE)";
$sql.= " ORDER BY 1, 2 desc, 3 desc ";

$rs = sqlsrv_query($GLOBALS['conn'],$sql);

if(!$rs){
    DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);    
}

$byMonth = array();
$allAccounts = array();
$maxYear = 0;
$minYear = 9999;

while ($row=sqlsrv_fetch_array($rs)) {
    
    $maxYear = $row['YEAR'] > $maxYear ? $row['YEAR'] : $maxYear;
    $minYear = $row['YEAR'] < $minYear ? $row['YEAR'] : $minYear;
    
    $byMonth[$row['YEAR']][$row['MONTH']][$row['ACCOUNT']] = $row['CLEARED'];
    $allAccounts[$row['ACCOUNT']] = $row['ACCOUNT'];
    $accountTotals[$row['ACCOUNT']] = 0;
};

if (count($byMonth) > 0) {
    krsort($byMonth[$maxYear]);
    krsort($byMonth[$minYear]);
    krsort($byMonth);
}

?>
<div class='container'>
<h3>Pes Cleared per Month, by Account, Year To Date</h3>
<table id='miReport' class='table table-responsive hover stripe'  data-page-length='25'>
<thead>
<tr><th>Month</th>
<?php 
foreach ($allAccounts as $accountName){
    ?><th><?=$accountName;?></th><?php     
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
        foreach ($allAccounts as $accountName) {
            ?><td><?=isset($accountValues[$accountName]) ? $accountValues[$accountName] : '';?></td>
            <?php  
            $MonthlyTotal                +=isset($accountValues[$accountName]) ? $accountValues[$accountName] : 0;
            $accountTotals[$accountName] +=isset($accountValues[$accountName]) ? $accountValues[$accountName] : 0;
            $total                       +=isset($accountValues[$accountName]) ? $accountValues[$accountName] : 0;
        }
        
        ?><td><?=$MonthlyTotal?></td></tr><?php 
    }
}
?>
</tbody>
<tfoot>
<tr><th>Totals</th>
<?php 
foreach ($allAccounts as $accountName){
    ?><th><?=$accountTotals[$accountName];?></th><?php     
}
?>
<th><?=$total;?></th></tr>
</tfoot>

</table>
</div>