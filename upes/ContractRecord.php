<?php
namespace upes;

use itdq\DbRecord;
use itdq\Loader;
use itdq\FormClass;

class ContractRecord extends DbRecord
{
    protected $CONTRACT_ID;
    protected $ACCOUNT_ID;
    protected $CONTRACT;

    function displayForm($mode)
    {
        $notEditable = $mode == FormClass::$modeEDIT ? ' disabled ' : '';
        $loader = new Loader();
        $allAccounts = $loader->loadIndexed('ACCOUNT','ACCOUNT_ID',AllTables::$ACCOUNT);

        ?>
        <form id='contractsForm' class="form-horizontal"  method='post'>
        <hr>
		<div class="form-group required">
            <label for='ACCOUNT_ID' class='col-sm-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='Account Name'>Account Name</label>
        	<div class='col-md-3'>
        		<select id='ACCOUNT_ID' class='form-group select2' name='ACCOUNT_ID' <?=$notEditable?> required='required'>
        		<option value=''></option>
        		<?php
        		foreach ($allAccounts as $accountId => $account) {
        		    ?><option value='<?=$accountId ?>'><?=$account?></option><?php
        		}
        		?>
        		</select>
            </div>
        </div>

         <div class="form-group required" >
            <label for='CONTRACT' class='col-sm-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='Contract Name'>Contract Name</label>
        	<div class='col-md-3'>
				<input id='CONTRACT' name='CONTRACT' class='form-control' <?=$notEditable;?> required='required'/>
				<input id='CONTRACT_ID' name='CONTRACT_ID' type='hidden' value='0' />
            </div>
        </div>
		<hr>
   		<div class='form-group'>
   		<div class='col-sm-offset-2 -col-md-3'>
        <?php
   		$this->formHiddenInput('mode',$mode,'mode');
        $allButtons = array();
   		$submitButton = $mode==FormClass::$modeEDIT ?  $this->formButton('submit','Submit','updateContract',null,'Update') :  $this->formButton('submit','Submit','saveContract',null,'Submit');
   		$resetButton  = $this->formButton('reset','Reset','resetContractForm',null,'Reset','btn-warning');
   		$allButtons[] = $submitButton;
   		$allButtons[] = $resetButton;
   		$this->formBlueButtons($allButtons);
  		?>
  		</div>
  		</div>
		<hr>
	</form>
    <?php
    }

}




