<?php
use itdq\JwtSecureSession;

error_log("Back from SSO");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('UTC');

set_include_path("./" . PATH_SEPARATOR . "../" . PATH_SEPARATOR . "../../" . PATH_SEPARATOR . "../../../" . PATH_SEPARATOR);

include ('vendor/autoload.php');
include ('splClassLoader.php');

$sessionConfig = (new \ByJG\Session\SessionConfig($_SERVER['SERVER_NAME']))
->withSecret($_ENV['jwt_token']);

$handler = new JwtSecureSession($sessionConfig);
session_set_save_handler($handler, true);
session_start();

error_log(__FILE__ . "server_name:" . $_SERVER['SERVER_NAME']);
error_log(__FILE__ . "jwt_token:" . $_ENV['jwt_token']);
error_log(__FILE__ . "session ID:" . session_id());
error_log(__FILE__ . "session:" . print_r($_SESSION,true));

include "connect.php";
