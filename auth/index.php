<?php
//used to verify and process login

include realpath(dirname(__FILE__))."/../class/include.php";
$auth = new Auth();
if($auth->verifyResponse($_GET))
{
    // header("Access-Control-Allow-Origin: *");
    // header("Location: ".$_GET['state']);
    $landingPage = urldecode($_GET['state']);
    echo 'Redirect to the final page '.$landingPage;
    echo 'Logged in successfully';
	exit();
}