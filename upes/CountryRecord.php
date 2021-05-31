<?php
namespace upes;

use itdq\DbRecord;
use itdq\Loader;
use itdq\FormClass;

class CountryRecord extends DbRecord
{
    protected $COUNTRY;
    protected $EMAIL_BODY_NAME;
    protected $ADDITIONAL_APPLICATION_FORM;

    const EMAIL_BODY_INTERNATIONAL = 'international';
    const EMAIL_BODY_CORE4         = 'core4';
    const EMAIL_BODY_UK            = 'uk';
    const EMAIL_BODY_INDIA         = 'india';

    const EMAIL_BODY_NAMES = array(self::EMAIL_BODY_CORE4,self::EMAIL_BODY_INDIA,self::EMAIL_BODY_INTERNATIONAL,self::EMAIL_BODY_UK);

    const ADDITIONAL_APPLICATION_FORM_OWENS = 'owens';
    const ADDITIONAL_APPLICATION_FORM_ODC   = 'odc';
    const ADDITIONAL_APPLICATION_FORM_UK    = 'uk';
    const ADDITIONAL_APPLICATION_FORM_VF    = 'vf';

    const ADDITIONAL_APPLICATION_FORM_NAMES = array(self::ADDITIONAL_APPLICATION_FORM_ODC, self::ADDITIONAL_APPLICATION_FORM_OWENS, self::ADDITIONAL_APPLICATION_FORM_UK, self::ADDITIONAL_APPLICATION_FORM_VF);

    function displayForm($mode)
    {
        $notEditable = $mode == FormClass::$modeEDIT ? ' disabled ' : '';

        ?>
        <form id='countryForm' class="form-horizontal"  method='post'>
        <hr>
        <div class="form-group required">
            <label for='COUNTRY' class='col-sm-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='Country Name'>Country Name</label>
        	<div class='col-md-3'>
				<input id='COUNTRY' name='COUNTRY' class='form-control' <?=$notEditable;?> />
            </div>
        </div>

         <div class="form-group required" >
            <label for='EMAIL_BODY_NAME' class='col-sm-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='Prefix of file holding email body'>Email Body Name</label>
        	<div class='col-md-3'>
				<select id='EMAIL_BODY_NAME' name='EMAIL_BODY_NAME' >
				<option value=''></option>
					<?php
					foreach (self::EMAIL_BODY_NAMES as $emailBody){
					    ?><option value='<?=$emailBody?>'><?=$emailBody?></option>
					<?php
					}
					?>
				</select>
            </div>
        </div>

        <div class="form-group required" >
            <label for='ADDITIONAL_APPLICATION_FORM' class='col-sm-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='Additional Application Form'>Additional Application Form</label>
        	<div class='col-md-3'>
				<select id='ADDITIONAL_APPLICATION_FORM'  name='ADDITIONAL_APPLICATION_FORM' >
				<option value=''></option>
					<?php
					foreach (self::ADDITIONAL_APPLICATION_FORM_NAMES as $form){
					    ?><option value='<?=$form?>'><?=$form?></option>
					<?php
					}
					?>
				</select>
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