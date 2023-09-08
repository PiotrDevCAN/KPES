<?php
use itdq\JwtSecureSession;

function do_auth($group = null)
{
    if(stripos($_ENV['environment'], 'local')) {
        $_SESSION['ssoEmail'] = $_ENV['SERVER_ADMIN'];
    } else {
        // $_SESSION['ssoEmail'] = $_SESSION['ssoEmail'];
    }
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('UTC');
set_include_path("./" . PATH_SEPARATOR . "../" . PATH_SEPARATOR . "../../" . PATH_SEPARATOR . "../../../" . PATH_SEPARATOR);

include ('vendor/autoload.php');
include ('splClassLoader.php');

$GLOBALS['Db2Schema'] = strtoupper($_ENV['environment']);
$GLOBALS['Db2Schema'] = str_replace('_LOCAL', '_DEV', $GLOBALS['Db2Schema']);

if (!isset($_SERVER['SERVER_NAME'])) {
    $_SERVER['SERVER_NAME'] = 'cli';
}

$sessionConfig = (new \ByJG\Session\SessionConfig($_SERVER['SERVER_NAME']))
->withTimeoutMinutes(120)
->withSecret($_ENV['jwt_token']);

$handler = new JwtSecureSession($sessionConfig);
session_set_save_handler($handler, true);

// session_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
error_log(__FILE__ . "session:" . session_id());
do_auth();
include "connect.php";
