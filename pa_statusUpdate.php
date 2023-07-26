<?php
use itdq\Trace;
use upes\AllTables;
use upes\AccountPersonRecord;
use upes\AccountPersonTable;
use itdq\FormClass;

Trace::pageOpening($_SERVER['PHP_SELF']);

$allStatus = AccountPersonRecord::$pesStatus;
asort($allStatus);

$allEmailAccountStatus = AccountPersonTable::getEmailAddressAccountArray();
$now = new DateTime();

?>
<div class="container">
<div class='row'>
<div class='col-sm-6'>
<h1>Manual Status Override</h1>
</div>
</div>

<form id='updateStatus' class='form-horizontal' >
 	<div class='form-group required'>
    <label for='personAccount' class='col-sm-2 control-label ceta-label-left'>Person/Account</label>
       <div class='col-sm-4'>
       	<select class='form-control select2' id='personAccount'
                name='personAccount'
                required='required'
                data-placeholder="Select Person/Account" data-allow-clear="true"
                >
        	<option value=''>Select Person/Account<option>
            <?php
            foreach ($allEmailAccountStatus as $emailAccount => $upesAccId) {
                ?><option value='<?=$upesAccId;?>'><?=$emailAccount;?></option><?php
            }
            ?>
       </select>
   	   </div>
	</div>

 	<div class='form-group required'>
    <label for='status' class='col-sm-2 control-label ceta-label-left'>Set Status to</label>
       <div class='col-sm-4'>
       	<select class='form-control select2' id='pesStatus'
                name='status'
                required='required'
                disabled
                data-placeholder="Select Status" data-allow-clear="true"
                >
        	<option value=''>Select Status</option>
            <?php
            foreach ($allStatus as  $status) {
                if ($status !== AccountPersonRecord::PES_STATUS_TBD) {
                    ?><option value='<?=$status?>'><?=$status;?></option><?php
                } else {
                    ?><option value='<?=$status?>' disabled><?=$status;?> - renamed to: <?=AccountPersonRecord::PES_STATUS_CLEARED_AMBER?></option><?php
                }
            }
            ?>
       </select>
   	   </div>
	</div>

 	<div class='form-group'>
	<label for='pes_date' class='col-sm-2 control-label '>Date</label>
    <div class='col-md-4'>
    <input class="form-control" id="pes_date" name="pes_date" value="<?=$now->format('d-m-Y') ?>" type="text" placeholder='Pes Status Changed' data-toggle='tooltip' title='PES Date Responded'>
    <input class="form-control" id="pes_date_db2"  value="<?=$now->format('Y-m-d') ?>" name="PES_DATE_RESPONDED" type='hidden' >
    </div>
    </div>
	<div class='form-group required'>
    <label for='checkDate' class='col-sm-2 control-label ceta-label-left'>Suppress Email Notification</label>
       <div class='col-sm-4'>
        <div class="radio">
  		<label><input type="radio" name="emailNotification" value='send' checked >Send Notification (If applicable)</label>
		</div>
		<div class="radio">
  		<label><input type="radio" name="emailNotification" value='suppress' >Do NOT Send Notification</label>
		</div>
		</div>
	</div>

	<div class='col-sm-offset-2 -col-md-3'>
        <?php
        $form = new FormClass();
        $allButtons = array();
        $submitButton = $form->formButton('submit','Submit','updatePerson','disabled','Update');
        $resetButton  = $form->formButton('reset','Reset','resetPersonForm',null,'Reset','btn-warning');
   		$allButtons[] = $submitButton;
   		$allButtons[] = $resetButton;
   		$form->formBlueButtons($allButtons);
  		?>
  		</div>

</form>
</div>
<?php
include_once 'includes/modalShowUpdateResult.html';
Trace::pageLoadComplete($_SERVER['PHP_SELF']);