<?php
//use ByJG\Session\JwtSession;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('memory_limit', '512M');
ini_set('max_execution_time', 360);

date_default_timezone_set('UTC');

set_include_path("./" . PATH_SEPARATOR . "../" . PATH_SEPARATOR . "../../" . PATH_SEPARATOR . "../../../" . PATH_SEPARATOR);
session_start();

include ('vendor/autoload.php');
include ('splClassLoader.php');

// $sessionConfig = (new \ByJG\Session\SessionConfig($_SERVER['SERVER_NAME']))
// ->withSecret($_ENV['jwt_token']);

// $handler = new \ByJG\Session\JwtSession($sessionConfig);
// session_set_save_handler($handler, true);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('memory_limit', '512M');
ini_set('max_execution_time', 360);

require_once("php/errorHandlers.php");

set_error_handler('myErrorHandler');
register_shutdown_function('fatalErrorShutdownHandler');

date_default_timezone_set('UTC');
set_include_path("./" . PATH_SEPARATOR . "../" . PATH_SEPARATOR . "../../" . PATH_SEPARATOR . "../../../" . PATH_SEPARATOR);
// session_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "php/w3config.php";
include "connect.php";
$GLOBALS['Db2Schema'] = strtoupper($_ENV['environment']);
$_SESSION['ssoEmail'] = 'Scheduled Job';