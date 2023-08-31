<?php
use itdq\Trace;
use itdq\Loader;
use upes\AllTables;
use upes\AccountPersonTable;

Trace::pageOpening($_SERVER['PHP_SELF']);

$loader = new Loader();

$date = new DateTime();
$oneMonth = new DateInterval('P1M');
$sixMonth = new DateInterval('P6M');
$date->sub($oneMonth);

$allAccounts = $loader->load('ACCOUNT',AllTables::$ACCOUNT);

$db2Report = AccountPersonTable::upcomingRechecksByContract();

foreach ($db2Report as $row) {
    $monthWithLeadingZero = substr("0" . $row['MONTH'],-2);
    $report[$row['ACCOUNT']][$row['CONTRACT']][$monthWithLeadingZero." ".$row['YEAR']] = $row['RESOURCES'];
  
}
?>
<div class='container'>
<div class='row'>
<div class='col-sm-2'>
</div>
<div class='col-sm-6'>
<h1 id='portalTitle' class='text-centre' >Upcoming Rechecks By Contract</h1>
</div>
<div class='col-sm-4'>
</div>
</div>
</div>

<div class='container'>
        <table id='upcomingRechecksReport' class='table table-striped table-bordered table-condensed '  style='width:100%'>
		<thead>
		<tr class='' ><th>Account</th><th>Contract</th>
		<?php
		for ($i = 0; $i < 6; $i++) {
		    ?><td><?=$date->format('M Y');?></td><?php
		    $date->add($oneMonth);
		}

		?>
		</tr>
		</thead>
		<tbody>

		<?php

		foreach ($allAccounts as $account) {
		    if(isset($report[$account])){
				foreach ($report[$account] as $contract => $contractData) {
					$date->sub($sixMonth);
					?><tr><td><?=$account;?></td><td><?=$contract;?></td><?php
					for ($i = 0; $i < 6; $i++) {
						$dateKey = $date->format('m Y');
						echo isset($contractData[$dateKey]) ? "<td>" . $contractData[$dateKey] . "</td>"  : "<td>0</td>";
						$date->add($oneMonth);
					}
					?></tr><?php
				}
		    }
		}
		?>
		</tbody>
		<tfoot>
		</tfoot>
		</table>


</div>
<?php
Trace::pageLoadComplete($_SERVER['PHP_SELF']);