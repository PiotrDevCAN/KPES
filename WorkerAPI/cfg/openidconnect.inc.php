<?php
$config_openidconnect = new stdClass();

/*
 * SSO Element of Config
 *
 */

$config_openidconnect->client_id      = $_ENV['worker_api_client_id'];
$config_openidconnect->client_secret  = $_ENV['worker_api_client_secret'];

$config_openidconnect->authorize_url  = $_ENV['worker_api_host'].'/authorize';
$config_openidconnect->token_url      = $_ENV['worker_api_host'].'/token';
$config_openidconnect->userinfo_url   = $_ENV['worker_api_host'].'/userinfo';
$config_openidconnect->introspect_url = $_ENV['worker_api_host'].'/introspect';
 
error_log('Authorising to:' . $config_openidconnect->authorize_url . " as (" . $config_openidconnect->client_id . ") ");

/*
 * Application Instance of Config
 *
 */

$config_openidconnect->redirect_url = "https://" . $_SERVER['HTTP_HOST'] . "/auth/index.php";

?>