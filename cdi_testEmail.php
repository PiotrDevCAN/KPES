<?php
use itdq\Trace;
use itdq\BlueGroups;
use itdq\BlueMail;

$to = array();
$to[] = 'piotr.tajanowicz@kyndryl.com';

$replyto = $_ENV['noreplyemailid'];
BlueMail::send_mail($to, 'Test', '<h1>Testing 1 2 3</h1>', $replyto);
