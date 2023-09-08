<?php
use itdq\Trace;
use itdq\WorkerAPI;
use itdq\OKTAGroups;

Trace::pageOpening($_SERVER['PHP_SELF']);

$workerAPI = new WorkerAPI();
$workerData = json_decode($workerAPI->getworkerByEmail($_SESSION['ssoEmail']));

// echo '<pre>';
// echo 'ENVIRONMENT <br>';
// var_dump($_ENV);
// echo 'SESSION <br>';
// var_dump($_SESSION);
echo 'WORKER DATA <br>';
var_dump($workerData);
echo '</pre>';

echo 'Memebers of OKTA group</br>';
$OKTAGroups = new OKTAGroups();
$OKTAGroups->listMembers('00g7bmv7zmnSf5DAX697');

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
