<?php
use itdq\Trace;
use itdq\WorkerAPI;

Trace::pageOpening($_SERVER['PHP_SELF']);

$host = 'https://login.microsoftonline.com/f260df36-bc43-424c-8f44-c85226657b01/oauth2/v2.0';
$client_id = 'ab8fd819-a2a7-417e-a6bf-0116d8a29ecb';
$client_secret = '-3Q8Q~6mgkg0KJtOYC3duR4lncHSPF953aOAeaY0';

$_ENV['worker_api_host'] = $host;
$_ENV['worker_api_client_id'] = $client_id;
$_ENV['worker_api_client_secret'] = $client_secret;

$workerAPI = new WorkerAPI();
$workerData = json_decode($workerAPI->getworkerByEmail('piotr.tajanowicz@kyndryl.com'));

echo '<pre>';
var_dump($workerData);
echo '</pre>';

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
