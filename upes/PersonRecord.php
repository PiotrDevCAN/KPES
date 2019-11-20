<?php
namespace upes;

use itdq\DbRecord;
use itdq\Loader;
use itdq\FormClass;
use itdq\DbTable;

/*
    CREATE TABLE UPES_DEV.PERSON      ( UPES_REF INTEGER NOT NULL GENERATED BY DEFAULT AS IDENTITY ( START WITH 10000 INCREMENT BY 100 NO CYCLE ), CNUM CHAR(9), EMAIL_ADDRESS CHAR(50), FULL_NAME VARCHAR(75) NOT NULL WITH DEFAULT, PASSPORT_FIRST_NAME CHAR(50), PASSPORT_LAST_NAME CHAR(50), COUNTRY CHAR(2) NOT NULL WITH DEFAULT, IBM_STATUS CHAR(30) NOT NULL WITH DEFAULT, PES_DATE_ADDED DATE NOT NULL WITH DEFAULT CURRENT DATE, PES_ADDER CHAR(75) NOT NULL,  SYSTEM_START_TIME TIMESTAMP(12) NOT NULL GENERATED ALWAYS AS ROW BEGIN, SYSTEM_END_TIME TIMESTAMP(12) NOT NULL GENERATED ALWAYS AS ROW END, TRANS_ID TIMESTAMP(12) GENERATED ALWAYS AS TRANSACTION START ID, PERIOD SYSTEM_TIME(SYSTEM_START_TIME,SYSTEM_END_TIME) );
    CREATE TABLE UPES_DEV.PERSON_HIST ( UPES_REF INTEGER NOT NULL, CNUM CHAR(9), EMAIL_ADDRESS CHAR(50), FULL_NAME VARCHAR(75) NOT NULL, PASSPORT_FIRST_NAME CHAR(50), PASSPORT_LAST_NAME CHAR(50), COUNTRY CHAR(2) NOT NULL, IBM_STATUS CHAR(30) NOT NULL, PES_DATE_ADDED DATE NOT NULL, PES_ADDER CHAR(75) NOT NULL , SYSTEM_START_TIME TIMESTAMP(12) NOT NULL, SYSTEM_END_TIME TIMESTAMP(12) NOT NULL, TRANS_ID TIMESTAMP(12) );
    ALTER TABLE  UPES_DEV.PERSON ADD VERSIONING USE HISTORY TABLE UPES_DEV.PERSON_HIST;

    ALTER TABLE UPES_DEV.PERSON ADD CONSTRAINT "Per_PK" PRIMARY KEY ("UPES_REF" ) ENFORCED;

    ALTER TABLE "UPES"."PERSON" ADD COLUMN "BLUEPAGES_STATUS" CHAR(20);



 *
 */



class PersonRecord extends DbRecord
{
    protected $UPES_REF;
    protected $CNUM;
    protected $EMAIL_ADDRESS;
    protected $FULL_NAME;
    protected $PASSPORT_FIRST_NAME;
    protected $PASSPORT_LAST_NAME;
    protected $COUNTRY;
    protected $IBM_STATUS;
    protected $PES_DATE_ADDED;
    protected $PES_ADDER;
    protected $BLUPAGES_STATUS;

    public static $pesTaskId = array('lbgvetpr@uk.ibm.com'); // Only first entry will be used as the "contact" in the PES status emails.

    const IBM_STATUS_CONTRACTOR = 'Contractor';
    const IBM_STATUS_REGULAR = 'Regular';

    const BLUEPAGES_STATUS_FOUND = 'found';
    const BLUEPAGES_STATUS_NOT_FOUND = 'not found';
    const BLUEPAGES_STATUS_IGNORE = 'ignore';

    protected $ibmStatus = array(self::IBM_STATUS_CONTRACTOR, self::IBM_STATUS_REGULAR);



  function displayForm($mode)
    {
        $notEditable = $mode == FormClass::$modeEDIT ? ' disabled ' : '';
        ?>
        <form id='personForm' class="form-horizontal" method='post'>
         <div class="form-group" >
            <label for='CNUM' class='col-sm-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='IBM CNUM if applicable'>Lookup IBMer</label>
        	<div class='col-md-4'>
				<input id='ibmer' name='ibmer' class='form-control typeahead' <?=$notEditable;?>  value='<?=!empty($this->FULL_NAME) ? $this->FULL_NAME :null ; ?>'/>
				<input id='CNUM' name='CNUM' class='form-control' type='hidden' <?=$notEditable;?> value='<?=!empty($this->CNUM) ? $this->CNUM :null ; ?>' />
            </div>
        </div>
        <div class="form-group" >
            <label for='EMAIL_ADDRESS' class='col-sm-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='Email Address'>Email Address</label>
        	<div class='col-md-4'>
				<input id='EMAIL_ADDRESS' name='EMAIL_ADDRESS' class='form-control' value='<?=!empty($this->EMAIL_ADDRESS) ? $this->EMAIL_ADDRESS :null ; ?>'/>
            </div>
        </div>
        <div class="form-group required " >
            <label for='FULL_NAME' class='col-sm-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='Full Name'>Full Name</label>
        	<div class='col-md-4'>
				<input id='FULL_NAME' name='FULL_NAME' class='form-control' <?=$notEditable;?> value='<?=!empty($this->FULL_NAME) ? $this->FULL_NAME :null ; ?>' />
            </div>
        </div>
        <div class="form-group required " >
            <label for='COUNTRY' class='col-sm-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='Country of Residence'>Country</label>
        	<div class='col-md-4'>
        		<select id='COUNTRY' class='form-group select2' name='COUNTRY'  >
        		<option value=''></option>
        		</select>
            </div>
        </div>
        <div class="form-group required " >
            <label for='IBM_STATUS' class='col-sm-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='IBM Status'>Status</label>
        	<div class='col-md-4'>
        		<select id='IBM_STATUS' class='form-group select2' name='IBM_STATUS' >
        		<option value=''></option>
        		<?php
        		foreach ($this->ibmStatus as $status) {
        		    ?><option value='<?=$status ?>' <?=$this->IBM_STATUS==$status ? 'checked ': null;
        		     ?>><?=$status?></option><?php
        		}
        		?>
        		</select>
            </div>
        </div>
        <input id='PES_ADDER' name='PES_ADDER' type='hidden'  value='<?=$_SESSION['ssoEmail']?>'/>
   		<div class='form-group'>
   		<div class='col-sm-offset-2 -col-md-4'>
        <?php
        $this->formHiddenInput('UPES_REF',$this->UPES_REF,'UPES_REF');
        $this->formHiddenInput('mode',$mode,'mode');
        $allButtons = array();
   		$submitButton = $mode==FormClass::$modeEDIT ?  $this->formButton('submit','Submit','updatePerson',null,'Update') :  $this->formButton('submit','Submit','savePerson',null,'Submit');
   		$resetButton  = $this->formButton('reset','Reset','resetPersonForm',null,'Reset','btn-warning');
   		$allButtons[] = $submitButton;
   		$allButtons[] = $resetButton;
   		$this->formBlueButtons($allButtons);
  		?>
  		</div>
  		</div>
	</form>
    <?php
    }
}