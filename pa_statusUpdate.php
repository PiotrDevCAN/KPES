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


<?php
// $person = new AccountPersonRecord();
// $person->amendPesStatusModal();
?>


<form id='updateStatus' class='form-horizontal' >
	<hr>
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
                ?><option value='<?=$status?>'><?=$status;?></option><?php
            }
            ?>
       </select>
   	   </div>
	</div>

 	<div class='form-group'>
	<label for='pes_date' class='col-sm-2 control-label '>Date</label>
    <div class='col-md-4'>
    <input class="form-control" id="pes_date" name="pes_date" value="<?=$now->format('d-m-Y') ?>" type="text" placeholder='Pes Status Changed' data-toggle='tooltip' title='PES Date Responded'>
    <input class="form-control" id="pes_date_db2"  value="<?=$now->format('Y-m-d') ?>" name="pes_date_db2" type='hidden' >
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
	<hr>
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
	<hr>
</form>
</div>

      <!-- Modal -->
    <div id="showUpdateResultModal" class="modal fade" role="dialog">
      <div class="modal-dialog">

            <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Amend PES Status</h4>
            </div>
          <div class="modal-body" >
          <div id='updateReport'>
          </div>
          </div>
          <div class="modal-footer">
	          <div class="button-blue submitButtonDiv" style="display: block ">
        		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        	  </div>
          </div>
        </div>
      </div>
    </div>





<script>


$(document).ready(function(){
	$('.select2').select2();

	$('#personAccount').on('change',function(){
		$('#pesStatus').attr('disabled',false);
	});

	$('#pesStatus').on('change',function(){
		$('#updatePerson').attr('disabled',false);
	});


    $('#pes_date').datepicker({ dateFormat: 'dd-mm-yy',
 	   						   altField:'#pes_date_db2',
 	   						   altFormat:'yy-mm-dd',
 	                           defaultDate: 0,
 	                           maxDate:0 } );

	$('#updatePerson').click(function(event){
		$(this).addClass('spinning').attr('disabled',true);
		event.preventDefault();
		console.log(event);
		var formData = $('#updateStatus').serialize();
		$.ajax({
			type:'post',
		  	url: 'ajax/manuallyUpdatePerson.php',
		  	data:formData,
		  	success: function(response) {
			  	var resultObj = JSON.parse(response);
			  	console.log(resultObj);
			  	
			  	$('.spinning').removeClass('spinning').attr('disabled',false	);

			  	var message = resultObj.success ? "<br/>Status Update Successful" : "<br/>Status Update Failed";
 			  	    message+= resultObj.emailNotification !='suppress' ? "<br/>Email Notification was Enabled" : "<br/>Email Notification was Suppressed";
				    message+= resultObj.success && resultObj.emailNotification !='suppress' ? "<br/>" + resultObj.Notification : '';
				    message+= "<br/>" + resultObj.Messages;
				$('#updateReport').html(message);
				$('#showUpdateResultModal').modal('show');


		  	}
	  	});
	});
});



</script>


<?php
Trace::pageLoadComplete($_SERVER['PHP_SELF']);