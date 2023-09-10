<?php
use itdq\Trace;
use itdq\TraceRecord;



$pwd=null;
do_auth($_SESSION['cdiBgAz']);
echo "<div class='container'>";
Trace::pageOpening(__FILE__);


$traceRecord = new TraceRecord();

if(isset($_REQUEST['daysToKeep'])){
    Trace::deleteTraceRecords($_REQUEST['daysToKeep']);
    echo "<H3>Trace file cleared down to just the last " . $_REQUEST['daysToKeep'] . " days.</h3>";
}

echo "<FORM role='form' name='traceDeleteForm' method='post' enctype='application/x-www-form-urlencoded' action='" . $_SERVER['PHP_SELF'] . "' >";
$traceRecord->displayConfirmDeletionDays();
echo "</form>";

Trace::pageLoadComplete($_SERVER['PHP_SELF']);
echo "</div>";
?>