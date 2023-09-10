<?php

use itdq\BlueMail;
use upes\PesEmail;

ob_clean();

$emailBody = "Testing 1 2 3";

$sendResponse = BlueMail::send_mail(array('piotr.tajanowicz@kyndryl.com'), "uPES Notification of Leavers"
    , $emailBody,'piotr.tajanowicz@kyndryl.com');

var_dump($sendResponse);

$data_json = '   {
	"contact": "piotr.tajanowicz@kyndryl.com",
	"recipients": [
		{"recipient": "piotr.tajanowicz@kyndryl.com"}
	],
	"subject": "Bluemix BlueMail Test Wed Sept 22 11:44:51 EDT 2015",
	"message": "Testing the email service. Defaults selected."
   }';

var_dump($data_json);


// $vcapServices = json_decode($_SERVER['VCAP_SERVICES']);

// $ch = curl_init();
// curl_setopt($ch, CURLOPT_HEADER,         1);
// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// curl_setopt($ch, CURLOPT_TIMEOUT,        240);
// curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 240);
// curl_setopt($ch, CURLOPT_HTTPAUTH,  CURLAUTH_BASIC);
// curl_setopt($ch, CURLOPT_HEADER,    FALSE);

// $userpwd = $vcapServices->bluemailservice[0]->credentials->username . ':' . $vcapServices->bluemailservice[0]->credentials->password;
// curl_setopt($ch, CURLOPT_USERPWD,        $userpwd);

// curl_setopt($ch, CURLOPT_URL, $vcapServices->bluemailservice[0]->credentials->emailUrl);

// curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data_json)));
// curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);

// $resp = curl_exec($ch);

// var_dump($resp);

// echo curl_errno($ch);
// echo curl_error($ch);