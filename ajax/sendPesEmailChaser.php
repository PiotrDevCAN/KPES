<?php

use upes\PesEmail;
use upes\AllTables;
use itdq\AuditTable;
use upes\AccountPersonTable;
use upes\AccountPersonRecord;

ob_start();

AuditTable::audit("Invoked:<b>" . __FILE__ . "</b>Parms:<pre>" . print_r($_POST,true) . "</b>",AuditTable::RECORD_TYPE_AUDIT);

$upesref= $_POST['upesref'];
$account= $_POST['account'];
$accountId= $_POST['accountid'];
$emailAddress = $_POST['emailaddress'];

$pesEmailObj = new PesEmail();
$emailResponse = $pesEmailObj->sendPesEmailChaser($upesref, $account, $emailAddress, $_POST['chaser'], $_POST['requestor']);
$emailStatus = $emailResponse['Status'];
$emailMessage = $emailResponse['sendResponse']['response'];

$messages = ob_get_contents();
$success = strlen($messages)==0;
$response = array();
$response['success'] = $success;
$response['messages'] = $messages;
$response['emailResponse'] = $emailResponse;
$response['pesStatus'] = AccountPersonRecord::PES_STATUS_PES_PROGRESSING;

$pesTracker = new AccountPersonTable(AllTables::$ACCOUNT_PERSON);

if($success){    
    $dateObj = new DateTime();
    $dateLastChased = $dateObj->format('Y-m-d');
    $pesTracker->setPesDateLastChased($upesref, $accountId, $dateLastChased);

    $messages = ob_get_contents();
    $success = strlen($messages)==0;

    $response['success'] = $success;
    $response['messages'] = $messages;
    $response['lastChased'] = $dateObj->format('d M Y');;

    try {
        $pesTracker->savePesComment($upesref, $accountId, "Automated PES Chaser Level " . $_POST['chaser'] . " sent to " . $_POST['emailaddress']);
        $pesTracker->savePesComment($upesref, $accountId, "Automated PES Chaser Email Status :  " . $emailStatus);
        $pesTracker->savePesComment($upesref, $accountId, "Automated PES Chaser Email Response :  " . $emailMessage);

        $comment = $pesTracker->getPesComment($upesref, $accountId);
        $response['comment'] = $comment;

    } catch (Exception $e) {
        // Don't give up just because we didn't save the comment.
        echo $e->getMessage();
    }

} else {
    try {
        $pesTracker->savePesComment($upesref, $accountId,"Error trying to send automated PES Chaser Level " . $_POST['chaser'] . " to " .  $_POST['emailaddress']);
        $pesTracker->savePesComment($upesref, $accountId,"Automated PES Email Status :  " . $emailStatus);
    } catch (Exception $e) {
        // Don't give up just because we didn't save the comment.
        echo $e->getMessage();
    }
}

ob_clean();
echo json_encode($response);