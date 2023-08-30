<?php
//used to verify and process login

include realpath(dirname(__FILE__))."/../SSO/class/include.php";
$auth = new Auth();
if($auth->verifyResponse($_GET))
{
    $landingPage = urldecode($_GET['state']);
    header("Access-Control-Allow-Origin: *");
    header("Location: ".$landingPage);
	exit();
}