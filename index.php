<?php
use itdq\Trace;
use itdq\WorkerAPI;

Trace::pageOpening($_SERVER['PHP_SELF']);

if(!isset($_SESSION['uid'])) {
	$_SESSION['uid'] = 'piotr.tajanowicz@kyndryl.com';
}

$start = microtime(true);

$workerAPI = new WorkerAPI();
$workerData = $workerAPI->getworkerByEmail($_SESSION['ssoEmail']);

$elapsed = microtime(true);
echo ("WorkerAPI call took:" . (float)($elapsed-$start));
echo '</br>';

// $OKTAGroups = new OKTAGroups();
// $groupDetails = $OKTAGroups->listMembers('00g7bmv7zmnSf5DAX697');

// echo '<pre>';
// echo 'ENVIRONMENT <br>';
// var_dump($_ENV);
// echo 'SESSION <br>';
// var_dump($_SESSION);
// echo 'WORKER DATA <br>';
// var_dump($workerData);

// echo 'Memebers of OKTA group</br>';
// var_dump($groupDetails);

// var_dump($_SESSION['cdiBgAz']);
// $cdiGroupData = $OKTAGroups->getGroupByName($_SESSION['cdiBgAz']);
// var_dump($cdiGroupData[0]);

// var_dump($_SESSION['pesTeamBgAz']);
// $pesTeamGroupData = $OKTAGroups->getGroupByName($_SESSION['pesTeamBgAz']);
// var_dump($pesTeamGroupData[0]);

// echo '</pre>';

echo 'Emails status: '.trim($_ENV['email']);

// $contractRecord = new ContractRecord();
// $contractTable = new ContractTable(AllTables::$CONTRACT);
// $data = array(
// 	'ACCOUNT_ID' => 900,
// 	'CONTRACT' => 'TEST INDEX'
// );
// $contractRecordData = array_map('trim', $data);
// $contractRecordData['CONTRACT_ID'] = null;
// $contractRecord->setFromArray($contractRecordData);

// $contractTable->insert($contractRecord, false, false);

?>
<style type="text/css" class="init">
body {
	background: url('./images/splash.png')
		no-repeat center center fixed;
	-webkit-background-size: cover;
	-moz-background-size: cover;
	-o-background-size: cover;
	background-size: cover;
}
</style>

<div class="container">
<!-- 	<div class="jumbotron"> -->
		<?php
			$ibmerLabel = '<em>uPES</em> UKI Pre-Employment Screening';
			$kyndrylerLabel = '<em>uPES</em> Pre-Employment Screening Tracked - For Kyndryl Employees ONLY';
			$label = stripos($_ENV['environment'], 'newco') ? $kyndrylerLabel : $ibmerLabel;
		?>
		<h1 id='welcomeJumotron'><p><?=$label?></p></h1>
<!-- 	</div> -->
</div>

<?php
Trace::pageLoadComplete($_SERVER['PHP_SELF']);
 ?>
