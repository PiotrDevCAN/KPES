<?php

use itdq\Process;
use upes\AccountPersonTable;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$email = $_SESSION['ssoEmail'];
$type = AccountPersonTable::PES_TRACKER_RECORDS_ACTIVE_PLUS;
$scriptsDirectory = '/var/www/html/batchJobs/';
$processFile = 'downloadTrackerProcess.php';

try {
    $cmd = 'php -f ' . $scriptsDirectory . $processFile . ' ' . $email . ' ' . str_replace(" ", "_", $type);
    $process = new Process($cmd);
    $pid = $process->getPid();
    echo "Tracker Extract Script has succeed to be executed: ".$email;
} catch (Exception $exception) {
    echo $exception->getMessage();
    echo "Tracker Extract Script has failed to be executed: ".$email;
}