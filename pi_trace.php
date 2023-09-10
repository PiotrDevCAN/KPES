<?php
use itdq\Trace;
use itdq\TraceList;
use itdq\AllItdqTables;
use itdq\TraceControlRecord;
use dpulse\AllTables;
use dpulse\accountManagementList;

$csv = null;
$excel = null;

$country = 'E4';
include_once 'connect.php';


do_auth($_SESSION['cdiBgAz']);

Trace::pageOpening($_SERVER['PHP_SELF']);

echo "<div class='container'>";

$pivot = FALSE;
$full = TRUE;
$dontSubTotal = false;

$list = new TraceList(AllItdqTables::$TRACE);

?>
<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
  <div class="panel panel-primary">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title">
        <a class='accordion-toggle' data-toggle="collapse" data-parent="#accordion" href="#traceSettings" aria-expanded="true" aria-controls="traceSettings">
          Trace List Settings
        </a>
        <i class="indicator glyphicon glyphicon-chevron-up  pull-left"></i>
      </h4>
    </div>
    <div id="traceSettings" class="panel-collapse collapse " role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">

<?php
echo "<div class='row'>";
$list->listOptions(TraceControlRecord::CONTROL_TYPE_CLASS_TIMINGS);
$list->listOptions(TraceControlRecord::CONTROL_TYPE_CLASS_INCLUDE);
$list->listOptions(TraceControlRecord::CONTROL_TYPE_CLASS_EXCLUDE);
echo "</div>";
echo "<div class='row'>";
$list->listOptions(TraceControlRecord::CONTROL_TYPE_METHOD_TIMINGS);
$list->listOptions(TraceControlRecord::CONTROL_TYPE_METHOD_INCLUDE);
$list->listOptions(TraceControlRecord::CONTROL_TYPE_METHOD_EXCLUDE);
echo "</div>";
?>

      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
 $('#accordion').on('hidden.bs.collapse', function () {
 //do something...
 })

$('#accordion .accordion-toggle').click(function (e){
var chevState = $(this).siblings("i.indicator").toggleClass('glyphicon-chevron-down glyphicon-chevron-up');
$("i.indicator").not(chevState).removeClass("glyphicon-chevron-down").addClass("glyphicon-chevron-up");
});
</script>


<?php
echo "<FORM class='form-inline' name='myForm' method='get' action='" . $_SERVER['PHP_SELF'] . "'>";
echo "<fieldset>";
$list->dropSelection();
echo "</fieldset>";
echo "</FORM>";
$list->displayTable($list->fetchList(),$dontSubTotal,$full);

echo "</div>"; // End of container

?>

      </div>
    </div>
  </div>
</div>
<?php
$list->dataTablesScript();

Trace::pageLoadComplete($_SERVER['PHP_SELF']);
