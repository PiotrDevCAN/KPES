<?php
namespace upes;

use itdq\DbRecord;
use itdq\FormClass;
use itdq\Loader;

class AccountRecord extends DbRecord
{
    protected $ACCOUNT_ID;
    protected $ACCOUNT;
    protected $TASKID;
    protected $ACCOUNT_TYPE;

    const ACCOUNT_TYPE_FSS = 'FSS';
    const ACCOUNT_TYPE_NONE_FSS = 'NON-FSS';

    static public $accountTypes = array(
        AccountRecord::ACCOUNT_TYPE_FSS,
        AccountRecord::ACCOUNT_TYPE_NONE_FSS
    );

    function displayForm($mode)
    {
        ?>
        <form id='accountsForm' class="form-horizontal"  method='post'>
        <hr>
        <?php

        $notEditable = $mode == FormClass::$modeEDIT ? ' disabled ' : '';

        ?>
        <div class="form-group required" id="AccountGroup">
            <label for='ACCOUNT' class='col-sm-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='Account Name'>Account Name</label>
        	<div class='col-md-3'>
				<input id='ACCOUNT' name='ACCOUNT' class='form-control' <?=$notEditable;?> />
				<input id='ACCOUNT_ID' name='ACCOUNT_ID' type='hidden' value='0' />
            </div>
        </div>

        <div class="form-group required" id="taskidGroup">
            <label for='ACCOUNT_TYPE' class='col-sm-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='Account Type'>Account Type</label>
        	<div class='col-md-3'>
                <select id='ACCOUNT_TYPE' class='select2 form-control' name='ACCOUNT_TYPE' <?=$notEditable?> >
        		<option value=''></option>
        		<?php
        		foreach (self::$accountTypes as $accountType) {
        		    ?><option value='<?=$accountType ?>'><?=$accountType?></option><?php
        		}
        		?>
        		</select>
            </div>
        </div>

        <div class="form-group required" id="taskidGroup">
            <label for='TASKID' class='col-sm-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='PES Taskid'>PES Taskid</label>
        	<div class='col-md-3'>
				<input id='TASKID' name='TASKID' class='form-control' />
            </div>
        </div>
        <hr>
   		<div class='form-group'>
   		<div class='col-sm-offset-2 -col-md-3'>
        <?php
   		$this->formHiddenInput('mode',$mode,'mode');
        $allButtons = array();
   		$submitButton = $mode==FormClass::$modeEDIT ?  $this->formButton('submit','Submit','updateAccount',null,'Update') :  $this->formButton('submit','Submit','saveAccount',null,'Submit');
   		$resetButton  = $this->formButton('reset','Reset','resetAccountForm',null,'Reset','btn-warning');
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

