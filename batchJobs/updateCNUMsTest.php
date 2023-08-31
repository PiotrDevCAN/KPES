<?php
use itdq\Loader;
use upes\PersonRecord;
use itdq\BluePages;
use upes\PersonTable;
use upes\AllTables;
use itdq\AuditTable;
use itdq\DbTable;
use itdq\slack;

// $slack = new slack();

AuditTable::audit("Revalidation invoked.",AuditTable::RECORD_TYPE_REVALIDATION);
// $slack->sendMessageToChannel("Revalidation invoked.(" . $_ENV['environment']. ") ", slack::CHANNEL_UPES_AUDIT);

set_time_limit(60);

$personTable = new personTable(AllTables::$PERSON);

$loader = new Loader();

sqlsrv_commit($GLOBALS['conn']);

$activeIbmErsPredicate = "   ( trim(BLUEPAGES_STATUS) = '' or BLUEPAGES_STATUS is null or trim(BLUEPAGES_STATUS) =  '" . PersonRecord::BLUEPAGES_STATUS_FOUND . "') ";
$activeIbmErsPredicate.= " and ( lower(trim(EMAIL_ADDRESS)) like '%ibm.com%' )";
$allNonLeaversRaw = $loader->load('CNUM',allTables::$PERSON, $activeIbmErsPredicate );
$allNonLeavers = array_change_key_case($allNonLeaversRaw, CASE_UPPER);
AuditTable::audit("Revalidation  will check " . count($allNonLeavers) . " people currently flagged as found.",AuditTable::RECORD_TYPE_REVALIDATION);
// $slack->sendMessageToChannel("Revalidation (" . $_ENV['environment']. ") will check " . count($allNonLeavers) . " people currently flagged as found.", slack::CHANNEL_UPES_AUDIT);

$detailsFromBp = "&mail";
$bpEntries = array();
$allFound = array();

$chunkedCnum = array_chunk($allNonLeavers, 100);
foreach ($chunkedCnum as $key => $cnumList){
    $bpEntries[$key] = BluePages::getDetailsFromCnumSlapMulti($cnumList, $detailsFromBp);
    foreach ($bpEntries[$key]->search->entry as $bpEntry){
        set_time_limit(40);
        $serial = strtoupper(substr($bpEntry->dn,4,9));
        unset($allNonLeavers[$serial]);
        $allFound[] = $serial;
    }
}

// At this stage, anyone still in the $allNonLeavers array - has NOT been found in BP and so is now POTENTIALLY a leaver and needs to be flagged as such.
$potentialLeaver = $allNonLeavers;

AuditTable::audit("Revalidation found " . count($potentialLeaver) . "  leavers.",AuditTable::RECORD_TYPE_REVALIDATION);
// $slack->sendMessageToChannel("Revalidation (" . $_ENV['environment']. ") found " . count($potentialLeaver) . "  leavers.", slack::CHANNEL_UPES_AUDIT);

$chunkedAllFound = array_chunk($allFound, 100);
foreach ($chunkedAllFound as $key => $allFoundCnumList){
    PersonTable::setCnumsToFound($allFoundCnumList);
}

if($potentialLeaver){
    $chunkedCnum = array_chunk($potentialLeaver, 100);
    foreach ($chunkedCnum as $key => $cnumList){
        PersonTable::FlagAsLeftIBM($cnumList);
        PersonTable::setCnumsToNotFound($cnumList);
        PersonTable::setCnumsToLeftIBM($cnumList);
    }
}

AuditTable::audit("Revalidation completed.",AuditTable::RECORD_TYPE_REVALIDATION);
// $slack->sendMessageToChannel("Revalidation (" . $_ENV['environment']. ") completed.", slack::CHANNEL_UPES_AUDIT);

sqlsrv_commit($GLOBALS['conn']);