<?php

use itdq\Trace;

Trace::pageOpening($_SERVER['PHP_SELF']);

?>
<div class='container-fluid	'>

<div class='col-sm-8 col-sm-offset-2'>
<h2>PES Status Change Log</h2>
<hr>
<table id='pesStatusChangeTable' class='table table-responsive table-striped' >
<thead>
<tr><th>CNUM</th><th>Email</th><th>Account</th><th >Status</th><th>Date</th><th>Updater</th><th >Updated</th></tr>
</thead>
<tbody></tbody>
<tfoot>
<tr><th>CNUM</th><th>Email</th><th>Account</th><th >Status</th><th>Date</th><th>Updater</th><th >Updated</th></tr>
</tfoot>
</table>
</div>
</div>
<?php
Trace::pageLoadComplete($_SERVER['PHP_SELF']);
?>