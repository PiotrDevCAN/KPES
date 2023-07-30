<?php
use itdq\Trace;
use itdq\FormClass;

Trace::pageOpening($_SERVER['PHP_SELF']);
?>

<style>
div.editable {
    width: 10   0%;
    height: 100px;
    border: 1px solid #ccc;
    padding: 5px;
    resize:vertical;
    overflow:auto;
}
</style>

<div class="container">
<div class='row'>
<div class='col-sm-6'>
<h1>Addresses to Convert</h1>
	<form id='contactsForm' class="form-horizontal" method='post'>
        <hr>
    	<div class="form-group required" >
        	<label for=CONTACTS class='col-sm-3 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='Contacts'>Contacts</label>
        	<div class='col-sm-9'>
	        	<div id='CONTACTS' contenteditable="true" class='editable'></div>
				<!-- <textarea id='CONTACTS' name='CONTACTS' class='form-control' ></textarea> -->
            </div>
        </div>
        <hr>
        <div class='col-sm-offset-3 col-sm-9'>
  		<input type='hidden' id='emailsToSave'  />
  		<input type='hidden' id='emailsSaved'  />
        <?php
        $allButtons = array();
        $form = new FormClass();
        $submitButton = $form->formButton('submit','Convert','convertMail',null,'Convert');
        $resetButton  = $form->formButton('reset','Reset','resetContacts  Form',null,'Reset','btn-warning');
   		$allButtons[] = $submitButton;
   		$allButtons[] = $resetButton;
   		$form->formBlueButtons($allButtons);
  		?>
  		</div>        
	</form>
	</div>
	</div>
<div class='row'>
<div class='col-sm-6'>
<<<<<<< HEAD
=======
<hr>
>>>>>>> 481c0dfe9947cef192191baa1c37e1d1ccd89b8e
<h1>Addresses</h1>
<table id='Addresses' class='table table-stripped table-responsive'>
<thead>
<tr><th>Email</th><th>Notes Id</th></tr>
</thead>
<tfoot>
<tr><th>Email</th><th>Notes Id</th></tr>
</tfoot>
</table>
</div>
</div>
</div>
<?php
Trace::pageLoadComplete($_SERVER['PHP_SELF']);