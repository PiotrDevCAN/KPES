<?php
//used to verify and process login

include realpath(dirname(__FILE__))."/../class/include.php";
$auth = new Auth();
if($auth->verifyResponse($_GET))
{
    header("Access-Control-Allow-Origin: *");
    echo $_GET['state'];
    sleep(20);
    header("Location: ".$_GET['state']);
	exit();
}