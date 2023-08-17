<?php
//used to verify and process login

include realpath(dirname(__FILE__))."/../class/include.php";
$auth = new Auth();
if($auth->verifyResponse($_GET))
{
    $landingPage = urldecode($_GET['state']);
    echo 'Redirect to the final page '.$landingPage;
    echo 'Logged in successfully';
    sleep(20);
    header("Access-Control-Allow-Origin: *");
    header("Location: ".$landingPage);
	exit();
}