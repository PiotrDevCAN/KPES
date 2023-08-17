<?php
//used to verify and process login

include realpath(dirname(__FILE__))."/../class/include.php";
$auth = new Auth();
if($auth->verifyResponse($_GET))
{
    // header("Access-Control-Allow-Origin: *");
    // header("Location: ".$_GET['state']);
    echo 'redirect to the finall page '.$_GET['state'];
    echo 'Logged in successfully';
	exit();
}