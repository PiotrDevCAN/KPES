<?php
namespace upes;

use itdq\DbRecord;
use itdq\Loader;
use itdq\FormClass;
use upes\AccountPersonTable;
use itdq\AuditTable;

/*

DROP TABLE UPES_DEV.ACCOUNT_PERSON;


CREATE TABLE UPES_DEV.ACCOUNT_PERSON ( ACCOUNT_ID INTEGER NOT NULL, UPES_REF INTEGER NOT NULL, PES_LEVEL CHAR(5), PES_DATE_REQUESTED DATE NOT NULL WITH DEFAULT CURRENT DATE, PES_REQUESTOR CHAR(75) NOT NULL, PES_STATUS CHAR(50) NOT NULL WITH DEFAULT 'Request Created', PES_STATUS_DETAILS CLOB(1048576), PES_DATE_RESPONDED DATE,PES_EVIDENCE_DATE DATE, PES_CLEARED_DATE DATE, PES_RECHECK_DATE DATE
                                       , CONSENT CHAR(10),RIGHT_TO_WORK CHAR(10),PROOF_OF_ID CHAR(10),PROOF_OF_RESIDENCY CHAR(10),CREDIT_CHECK CHAR(10),FINANCIAL_SANCTIONS CHAR(10),CRIMINAL_RECORDS_CHECK CHAR(10)
                                       , PROOF_OF_ACTIVITY CHAR(10),QUALIFICATIONS CHAR(10),DIRECTORS CHAR(10),MEDIA CHAR(10),MEMBERSHIP CHAR(10)
                                       , PROCESSING_STATUS CHAR(20), PROCESSING_STATUS_CHANGED TIMESTAMP
                                       , DATE_LAST_CHASED DATE, COMMENT VARCHAR(8192), PRIORITY CHAR(10)
                                       , SYSTEM_START_TIME TIMESTAMP(12) NOT NULL GENERATED ALWAYS AS ROW BEGIN, SYSTEM_END_TIME TIMESTAMP(12) NOT NULL GENERATED ALWAYS AS ROW END, TRANS_ID TIMESTAMP(12) GENERATED ALWAYS AS TRANSACTION START ID, PERIOD SYSTEM_TIME(SYSTEM_START_TIME,SYSTEM_END_TIME) );
CREATE TABLE UPES_DEV.ACCOUNT_PERSON_HIST ( ACCOUNT_ID INTEGER NOT NULL, UPES_REF INTEGER NOT NULL , PES_LEVEL CHAR(5), PES_DATE_REQUESTED DATE NOT NULL, PES_REQUESTOR CHAR(75) NOT NULL,  PES_STATUS CHAR(50) NOT NULL, PES_STATUS_DETAILS CLOB(1048576), PES_DATE_RESPONDED DATE, PES_EVIDENCE_DATE DATE,PES_CLEARED_DATE DATE, PES_RECHECK_DATE DATE
                                       , CONSENT CHAR(10),RIGHT_TO_WORK CHAR(10),PROOF_OF_ID CHAR(10),PROOF_OF_RESIDENCY CHAR(10),CREDIT_CHECK CHAR(10),FINANCIAL_SANCTIONS CHAR(10),CRIMINAL_RECORDS_CHECK CHAR(10)
                                       , PROOF_OF_ACTIVITY CHAR(10),QUALIFICATIONS CHAR(10),DIRECTORS CHAR(10),MEDIA CHAR(10),MEMBERSHIP CHAR(10)
                                       , PROCESSING_STATUS CHAR(20), PROCESSING_STATUS_CHANGED TIMESTAMP
                                       , DATE_LAST_CHASED DATE, COMMENT VARCHAR(8192), PRIORITY CHAR(10)
                                       , SYSTEM_START_TIME TIMESTAMP(12) NOT NULL, SYSTEM_END_TIME TIMESTAMP(12) NOT NULL, TRANS_ID TIMESTAMP(12) );
ALTER TABLE UPES_DEV.ACCOUNT_PERSON ADD VERSIONING USE HISTORY TABLE UPES_DEV.ACCOUNT_PERSON_HIST;

ALTER TABLE "UPES_DEV"."ACCOUNT_PERSON" ADD CONSTRAINT "AccPer_PK" PRIMARY KEY ("ACCOUNT_ID","UPES_REF" ) ENFORCED;
ALTER TABLE "UPES_DEV"."ACCOUNT_PERSON" ALTER COLUMN "PES_RECHECK_DATE" SET DATA TYPE DATE;
ALTER TABLE "UPES_DEV"."ACCOUNT_PERSON" ADD COLUMN "COUNTRY_OF_RESIDENCE" CHAR(50);
ALTER TABLE "UPES_DEV"."ACCOUNT_PERSON" ADD COLUMN "OFFBOARDED_DATE" DATE;
ALTER TABLE "UPES_DEV"."ACCOUNT_PERSON" ADD COLUMN "OFFBOARDED_BY" CHAR(80);
 *
 */

class AccountPersonRecord extends DbRecord
{
    protected $ACCOUNT_ID;
    protected $UPES_REF;
    protected $PES_LEVEL;
    protected $PES_DATE_REQUESTED;
    protected $PES_REQUESTOR;
    protected $PES_CREATOR; // logged in user
    protected $PES_STATUS;
    protected $PES_STATUS_DETAILS;
    protected $PES_DATE_RESPONDED;
    protected $PES_EVIDENCE_DATE;
    protected $PES_CLEARED_DATE;
    protected $PES_RECHECK_DATE;

    protected $CONSENT;
    protected $RIGHT_TO_WORK;
    protected $PROOF_OF_ID;
    protected $PROOF_OF_RESIDENCY;
    protected $CREDIT_CHECK;
    protected $FINANCIAL_SANCTIONS;
    protected $CRIMINAL_RECORDS_CHECK;
    protected $PROOF_OF_ACTIVITY;
    protected $QUALIFICATIONS;
    protected $DIRECTORS;
    protected $MEDIA;
    protected $MEMBERSHIP;

    protected $PROCESSING_STATUS;
    protected $PROCESSING_STATUS_CHANGED;
    protected $DATE_LAST_CHASED;
    protected $COMMENT;
    protected $PRIORITY;
    protected $COUNTRY_OF_RESIDENCE;
    
    protected $OFFBOARDED_DATE;
    protected $OFFBOARDED_BY;

    protected $CONTRACT_ID;

    const PES_EVENT_CONSENT        = 'Consent Form';
    const PES_EVENT_WORK           = 'Right to Work';
    const PES_EVENT_ID             = 'Proof of Id';
    const PES_EVENT_RESIDENCY      = 'Residency';
    const PES_EVENT_CREDIT         = 'Credit Check';
    const PES_EVENT_SANCTIONS      = 'Financial Sanctions';
    const PES_EVENT_CRIMINAL       = 'Criminal Records Check';
    const PES_EVENT_ACTIVITY       = 'Activity';
    const PES_EVENT_QUALIFICATIONS = 'Qualifications';
    const PES_EVENT_DIRECTORS      = 'Directors';
    const PES_EVENT_MEDIA          = 'Media';
    const PES_EVENT_MEMBERSHIP     = 'Membership';

    const PES_STATUS_CLEARED        = 'Cleared';
    const PES_STATUS_CLEARED_AMBER  = 'Cleared - Amber';
    const PES_STATUS_DECLINED       = 'Declined';
    const PES_STATUS_EXCEPTION      = 'Exception';
    const PES_STATUS_FAILED         = 'Failed';
    const PES_STATUS_PES_PROGRESSING = 'PES Progressing';
    const PES_STATUS_STARTER_REQUESTED = 'Starter Requested';
    const PES_STATUS_PROVISIONAL    = 'Provisional Clearance';
    const PES_STATUS_REMOVED        = 'Removed';
    const PES_STATUS_REVOKED        = 'Revoked';
    const PES_STATUS_CANCEL_REQ     = 'Cancel Requested';
    const PES_STATUS_CANCEL_CONFIRMED = 'Cancel Confirmed';
    const PES_STATUS_TBD            = 'TBD';
    const PES_STATUS_RECHECK_REQ    = 'Recheck Req';
    const PES_STATUS_RECHECK_PROGRESSING = 'Recheck Progressing';
    const PES_STATUS_LEFT_IBM       = 'Left IBM';
    const PES_STATUS_STAGE_1        = 'Stage 1 Completed';
    const PES_STATUS_STAGE_2        = 'Stage 2 Completed';
    const PES_STATUS_MOVER          = 'Mover';
    
    static public $pesStatus = array(
      AccountPersonRecord::PES_STATUS_CLEARED,
      AccountPersonRecord::PES_STATUS_CLEARED_AMBER,
      AccountPersonRecord::PES_STATUS_DECLINED,
      AccountPersonRecord::PES_STATUS_EXCEPTION,
      AccountPersonRecord::PES_STATUS_FAILED,
      AccountPersonRecord::PES_STATUS_PES_PROGRESSING,
      AccountPersonRecord::PES_STATUS_STARTER_REQUESTED,
      AccountPersonRecord::PES_STATUS_PROVISIONAL,
      AccountPersonRecord::PES_STATUS_REMOVED,
      AccountPersonRecord::PES_STATUS_REVOKED,
      AccountPersonRecord::PES_STATUS_CANCEL_REQ,
      AccountPersonRecord::PES_STATUS_CANCEL_CONFIRMED,
      AccountPersonRecord::PES_STATUS_TBD,
      AccountPersonRecord::PES_STATUS_RECHECK_REQ,
      AccountPersonRecord::PES_STATUS_RECHECK_PROGRESSING,
      AccountPersonRecord::PES_STATUS_LEFT_IBM,
      AccountPersonRecord::PES_STATUS_STAGE_1,
      AccountPersonRecord::PES_STATUS_STAGE_2,
      AccountPersonRecord::PES_STATUS_MOVER
    );

    static public $pesAuditableStatus = array(
      AccountPersonRecord::PES_STATUS_CLEARED,
      AccountPersonRecord::PES_STATUS_PROVISIONAL,
      AccountPersonRecord::PES_STATUS_RECHECK_REQ
    );

//     PES PRocessing
//     Starter Requested
//     PRovisional Clearance
//     Recheck Req
//     Stage 1 complete
//     Stage 2 compelte

    static public $alertIfLeaving = array(
      AccountPersonRecord::PES_STATUS_PES_PROGRESSING,
      AccountPersonRecord::PES_STATUS_STARTER_REQUESTED,
      AccountPersonRecord::PES_STATUS_PROVISIONAL,
      AccountPersonRecord::PES_STATUS_RECHECK_REQ,
      AccountPersonRecord::PES_STATUS_STAGE_1,
      AccountPersonRecord::PES_STATUS_STAGE_2
    );

    static public $pesEvents = array('Consent Form','Right to Work','Proof of Id','Residency','Credit Check','Financial Sanctions','Criminal Records Check','Activity','Qualifications','Directors','Media','Membership');

    private static $pesEmailBody = 'Please initiate PES check for the following individual : Name : &&name&&, Account : &&account&&, Requested By : &&requestor&&, Requested Timestamp : &&requested&&';
    private static $pesEmailPatterns = array(
        '/&&name&&/',
        '/&&account&&/',
        '/&&requestor&&/',
        '/&&requested&&/',
    );

    private static $pesClearedEmail = 'Hello &&candidate&&,
                                              <br/>I can confirm that you have successfully passed &&accountName&& PES Screening, effective from &&effectiveDate&&
                                              <br/>If you need any more information regarding your PES clearance, please contact the taskid &&taskid&&.
                                              <br/>Please contact your manager or account project office for details of the next step in boarding to the account.
                                              <br/>Many thanks for your cooperation,';
    private static $pesClearedEmailPattern = array('/&&candidate&&/','/&&effectiveDate&&/','/&&taskid&&/','/&&accountName&&/');

    private static $pesCancelPesEmail = 'PES Team,
                                              <br/>Please stop processing the PES Clearance for : &&candidate&& UPES_REF:( &&upesref&& ) on Account : &&account&&
                                              <br/>This action has been requested by  &&requestor&&.';
    private static $pesCancelPesEmailPattern = array('/&&candidate&&/','/&&upesref&&/','/&&requestor&&/','/&&account&&/');

    private static $pesClearedProvisionalEmail = 'Hello &&candidate&&,
                                              <br/>I can confirm that you have provisionally cleared  &&accountName&& PES Screening.  Please note this is an EXCEPTION, please arrange for your documents to be certified urgently.
                                              <p><b>The Certification MUST be done by an Kyndryl/IBM�er<b>, to confirm that they have seen the original document. Either physically or Virtually (over webex).<p> For Virtual - an email can be sent directly to the PES team, by your certifier <b>to confirm each original documents/screens (in a list) which have been viewed, over video conferencing, along with the date and time they were viewed.<b> OR Physical - The following statement should be added on each document, on the same side as the image.</p>
                                              <h4><span style=\'color:red\'><center>True & Certified Copy<br/>Name of certifier in BLOCK CAPITALS<br/>IBM Serial number of certifier<br/>Certification Date<br/>Signature of certifier</center></span></h4>
                                              <p></p>
                                              <p>ALL documents that you have sent to the PES team must be certified (ie, passport, address and activity/education evidences).
                                              <br/>If you need any more information regarding your PES clearance, please let me know.
                                              <br/>When sending your document please ONLY send to the PES team.
                                              <br/>Many thanks for your cooperation,';

    private static $pesClearedProvisionalEmailPattern = array('/&&candidate&&/','/&&accountName&&/');

   //  public static $pesTaskId = array('lbgvetpr@uk.ibm.com'); // Only first entry will be used as the "contact" in the PES status emails.

   static function prepareJsonArraysForPesSelection(){

        $allStatus = self::$pesStatus;
        $pesStatuses = array();

        foreach ($allStatus as $key=> $status){
            $option = new \stdClass();
            $option->id = $status;
            if ($status != 'TBD') {
                $option->text = $status;
            } else {
                $option->text = $status . ' - renamed to: '.AccountPersonRecord::PES_STATUS_CLEARED_AMBER;
                $option->disabled = true;
            }
            $pesStatuses[] = $option;
        }

        return $pesStatuses;
    }

    function displayForm($mode)
    {
        $notEditable = $mode == FormClass::$modeEDIT ? ' disabled ' : '';
        $loader = new Loader();
        $allEmail = $loader->loadIndexed('EMAIL_ADDRESS','UPES_REF',AllTables::$PERSON);
        $allCountries = $loader->load('COUNTRY',AllTables::$COUNTRY);
        ?>
        <form id='accountPersonForm' class="form-horizontal" method='post'>
<<<<<<< HEAD
<<<<<<< HEAD
        <div class="form-group required">
            <label for='UPES_REF' class='col-sm-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='Email Address'>Email Address</label>
        	  <div class='col-md-3'>
			      <select id='UPES_REF' class='form-group select2' name='UPES_REF' required >
=======
        <hr>
        <div class="form-group required " >
            <label for='UPES_REF' class='col-sm-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='Email Address'>Email Address</label>
        	  <div class='col-md-3'>
			      <select id='UPES_REF' class='form-group select2' name='UPES_REF' required='required' >
>>>>>>> 481c0dfe9947cef192191baa1c37e1d1ccd89b8e
=======
        <hr>
        <div class="form-group required " >
            <label for='UPES_REF' class='col-sm-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='Email Address'>Email Address</label>
        	  <div class='col-md-3'>
			      <select id='UPES_REF' class='form-group select2' name='UPES_REF' required='required' >
>>>>>>> 481c0dfe9947cef192191baa1c37e1d1ccd89b8e
        		<option value=''></option>
        		<?php
        		foreach ($allEmail as $upesRef => $emailAddress) {
        		    ?><option value='<?=$upesRef?>' data-email='<?=$emailAddress?>'><?=$emailAddress?></option><?php
        		}
        		?>
        		</select>
            </div>
        </div>

<<<<<<< HEAD
<<<<<<< HEAD
        <div class="form-group required">
            <label for='CONTRACT_ID' class='col-sm-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='Contract'>Contract</label>
        	  <div class='col-md-3'>
        		<select id='CONTRACT_ID' class='form-group select2' name='CONTRACT_ID' disabled >
=======
        <div class="form-group required " >
            <label for='FULL_NAME' class='col-sm-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='Full Name'>Full Name</label>
        	  <div class='col-md-3'>
=======
        <div class="form-group required " >
            <label for='FULL_NAME' class='col-sm-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='Full Name'>Full Name</label>
        	  <div class='col-md-3'>
>>>>>>> 481c0dfe9947cef192191baa1c37e1d1ccd89b8e
				    <input id='FULL_NAME' name='FULL_NAME' class='form-control' placeholder='Select Individual Email Address' disabled required='required' />
            </div>
        </div>
        
        <div class="form-group required">
            <label for='CONTRACT_ID' class='col-sm-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='Contract'>Contract</label>
        	  <div class='col-md-3'>
        		<select id='CONTRACT_ID' class='form-group select2' name='CONTRACT_ID' disabled required='required' >
<<<<<<< HEAD
>>>>>>> 481c0dfe9947cef192191baa1c37e1d1ccd89b8e
=======
>>>>>>> 481c0dfe9947cef192191baa1c37e1d1ccd89b8e
        		<option value=''></option>
        		</select>
        		<input id='ACCOUNT_ID' name='ACCOUNT_ID' type='hidden'>
            </div>
        </div>

        <div class="form-group required " >
            <label for='PES_LEVEL' class='col-sm-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='Not applicable on all contracts'>PES Level</label>
        	  <div class='col-md-3'>
        		<select id='PES_LEVEL' class='form-group select2' name='PES_LEVEL' <?=$notEditable?> data-placeholder='Select Pes Level' disabled required='required' >
        		<option value=''></option>
        		</select>
            </div>
        </div>

        <div class="form-group required" >
            <label for='COUNTRY_OF_RESIDENCE' class='col-sm-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='Country'>Country of Residence</label>
        	  <div class='col-md-3'>
			      <select id='COUNTRY_OF_RESIDENCE' class='form-group select2' name='COUNTRY_OF_RESIDENCE' required required='required' >
        		<option value=''></option>
        		<option value='India' data-country='India' <?=$this->COUNTRY_OF_RESIDENCE=='India' ? ' selected ': null;?>>India</option>
        		<option value='UK' data-country='UK' <?=$this->COUNTRY_OF_RESIDENCE=='UK' ? ' selected ': null;?>>UK</option>
        		<option value='--------' data-country='--------'  disabled >---------</option>
        		<?php
        		unset($allCountries['UK']);
        		unset($allCountries['India']);
        		foreach ($allCountries as  $country) {
        		    ?><option value='<?=$country?>' data-country='<?=$country?>' <?=$this->COUNTRY_OF_RESIDENCE==$country ? ' selected ' : null;?>><?=$country?></option><?php
        		}
        		?>
        		</select>
            </div>
        </div>

        <hr>

        <div class="form-group required " >
            <label for='PES_REQUESTOR' class='col-sm-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='Requestor Name'>Requestor Name</label>
        	  <div class='col-md-3'>
<<<<<<< HEAD
<<<<<<< HEAD
				    <input id='PES_REQUESTOR' name='PES_REQUESTOR' required class='form-control' value='<?=$_SESSION['ssoEmail']?>' />
=======
				    <input id='PES_DEFAULT_REQUESTOR' name='PES_DEFAULT_REQUESTOR' type='hidden' value='<?=$_SESSION['ssoEmail']?>' />
            <input id='PES_REQUESTOR' name='PES_REQUESTOR' class='form-control' disabled value='<?=$_SESSION['ssoEmail']?>' required='required' />
>>>>>>> 481c0dfe9947cef192191baa1c37e1d1ccd89b8e
=======
				    <input id='PES_DEFAULT_REQUESTOR' name='PES_DEFAULT_REQUESTOR' type='hidden' value='<?=$_SESSION['ssoEmail']?>' />
            <input id='PES_REQUESTOR' name='PES_REQUESTOR' class='form-control' disabled value='<?=$_SESSION['ssoEmail']?>' required='required' />
>>>>>>> 481c0dfe9947cef192191baa1c37e1d1ccd89b8e
            </div>
        </div>

      <div class='form-group'>
        <div class='col-sm-offset-2 -col-md-3'>
          <?php
          $requestorButtons = array();
          $yourselfButton  = $this->formButton('button','yourself','setYourselfAsRequestor',null,'Yourself','btn-success');
          $functionalButton  = $this->formButton('button','functional_manager','setFLMAsRequestor',null,'Functional Manager','btn-info');
          $otherButton  = $this->formButton('button','other_person','setOtherRequestor',null,'Other Person','btn-secondary');
          $requestorButtons[] = $yourselfButton;
          $requestorButtons[] = $functionalButton;
          $requestorButtons[] = $otherButton;
          $this->formBlueButtons($requestorButtons);
          ?>
        </div>
  		</div>

      <hr>

   		<div class='form-group'>
        <div class='col-sm-offset-2 -col-md-3'>
            
          <input id='PES_CREATOR' name='PES_CREATOR' type='hidden' value='<?=$_SESSION['ssoEmail']?>'/>
          <input id='PES_STATUS' name='PES_STATUS' type='hidden' value='<?=AccountPersonRecord::PES_STATUS_STARTER_REQUESTED;?>'/>
          
          <?php
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

    static function getPesStatusWithButtons($row){
        $cnum = trim($row['CNUM']);
        $emailAddress= trim($row['EMAIL_ADDRESS']);
        $upesRef     = trim($row['UPES_REF']);
        $ibmStatus   = trim($row['IBM_STATUS']);
        $account     = trim($row['ACCOUNT']);
        $accountid   = trim($row['ACCOUNT_ID']);
        $accounttype   = trim($row['ACCOUNT_TYPE']);
        $passportFirst    = array_key_exists('PASSPORT_FIRST_NAME', $row) ? $row['PASSPORT_FIRST_NAME'] : null;
        $passportLastname = array_key_exists('PASSPORT_LAST_NAME', $row)    ? $row['PASSPORT_LAST_NAME'] : null;
        $fullName    = trim($row['FULL_NAME']);
        $country     = trim($row['COUNTRY_OF_RESIDENCE']);
        $status      = trim($row['PES_STATUS']);

        $pesStatusWithButton = '';
        $pesStatusWithButton.= "<span class='pesStatusField' data-upesref='" . $upesRef . "' data-account='" . $account . "' data-accountid='" . $accountid . "'  >" .  $status . "</span><br/>";
        switch (true) {
            case $status == AccountPersonRecord::PES_STATUS_CLEARED_AMBER && !$_SESSION['isPesTeam']:
                $pesStatusWithButton.= "<button type='button' class='btn btn-default btn-xs btnPesInitiate accessRestrict accessPmo accessFm' ";
                $pesStatusWithButton.= " aria-label='Left Align' ";
                $pesStatusWithButton.= " data-upesref='" .$upesRef . "' ";
                $pesStatusWithButton.= " data-account='" .$account . "' ";
                $pesStatusWithButton.= " data-accountid='" .$accountid . "' ";
                $pesStatusWithButton.= " data-accounttype='" .$accounttype . "' ";
                $pesStatusWithButton.= " data-pesstatus='$status' ";
                $pesStatusWithButton.= " data-toggle='tooltip' data-placement='top' title='Initiate PES Request'";
                $pesStatusWithButton.= " > ";
                $pesStatusWithButton.= "<span class='glyPesInitiate glyphicon glyphicon-plane ' aria-hidden='true'></span>";
                $pesStatusWithButton.= "</button>&nbsp;";
                break;
            case $status == AccountPersonRecord::PES_STATUS_RECHECK_REQ && $_SESSION['isPesTeam'] :
            case $status == AccountPersonRecord::PES_STATUS_STARTER_REQUESTED && $_SESSION['isPesTeam'] ;
              
                $recheck      = ($status==AccountPersonRecord::PES_STATUS_RECHECK_REQ) ? 'yes' : 'no' ;
                $aeroplaneColor= ($status==AccountPersonRecord::PES_STATUS_RECHECK_REQ)? 'yellow' : 'green' ;

                $missing = !empty($emailAddress) ? '' : ' Email Address';
                $missing.= !empty($fullName) ? '' : ' Full Name';
                $missing.= !empty($country) ? '' : ' Country';
                $valid = empty(trim($missing));
                $disabled = $valid ? '' : 'disabled';
                $tooltip = $valid ? 'Confirm PES Email details' : "Missing $missing";

                $pesStatusWithButton.= "<button type='button' class='btn btn-default btn-xs btnSendPesEmail accessRestrict accessPmo accessFm' ";
                $pesStatusWithButton.= "aria-label='Left Align' ";
                $pesStatusWithButton.= " data-cnum='$cnum' ";
                $pesStatusWithButton.= " data-emailaddress='$emailAddress' ";
                $pesStatusWithButton.= " data-account='" . $account . "' ";
                $pesStatusWithButton.= " data-accountid='" . $accountid . "' ";
                $pesStatusWithButton.= " data-accounttype='" . $accounttype . "' ";
                $pesStatusWithButton.= " data-fullname='$fullName' ";
                $pesStatusWithButton.= " data-country='$country' ";
                $pesStatusWithButton.= " data-upesref='$upesRef' ";
                $pesStatusWithButton.= " data-status='$status' ";
                $pesStatusWithButton.= " data-ibmstatus='$ibmStatus' ";
                $pesStatusWithButton.= " data-recheck='$recheck' ";
                $pesStatusWithButton.= " data-toggle='tooltip' data-placement='top' title='$tooltip'";
                $pesStatusWithButton.= " $disabled  ";
                $pesStatusWithButton.= " > ";
                $pesStatusWithButton.= "<span class='glyphicon glyphicon-send ' aria-hidden='true' style='color:$aeroplaneColor' ></span>";

                $pesStatusWithButton.= "</button>&nbsp;";
                
            case $status == AccountPersonRecord::PES_STATUS_PES_PROGRESSING && $_SESSION['isPesTeam'] :
            case $status == AccountPersonRecord::PES_STATUS_CANCEL_REQ && $_SESSION['isPesTeam'] :
            case $status == AccountPersonRecord::PES_STATUS_CLEARED && $_SESSION['isPesTeam'] :
            case $status == AccountPersonRecord::PES_STATUS_CLEARED_AMBER && $_SESSION['isPesTeam'] :                
            case $status == AccountPersonRecord::PES_STATUS_EXCEPTION && $_SESSION['isPesTeam'] :
            case $status == AccountPersonRecord::PES_STATUS_DECLINED && $_SESSION['isPesTeam'] ;
            case $status == AccountPersonRecord::PES_STATUS_FAILED && $_SESSION['isPesTeam'] ;
            case $status == AccountPersonRecord::PES_STATUS_REMOVED && $_SESSION['isPesTeam'] :
            case $status == AccountPersonRecord::PES_STATUS_REVOKED && $_SESSION['isPesTeam'] :
            case $status == AccountPersonRecord::PES_STATUS_LEFT_IBM && $_SESSION['isPesTeam'] :
            case $status == AccountPersonRecord::PES_STATUS_PROVISIONAL && $_SESSION['isPesTeam'] :
            case $status == AccountPersonRecord::PES_STATUS_TBD && $_SESSION['isPesTeam'] :
          //  case $status == AccountPersonRecord::PES_STATUS_RECHECK_REQ && $_SESSION['isPesTeam'] :
            case $status == AccountPersonRecord::PES_STATUS_STAGE_1 && $_SESSION['isPesTeam'] :
            case $status == AccountPersonRecord::PES_STATUS_STAGE_2 && $_SESSION['isPesTeam'] :
            case $status == AccountPersonRecord::PES_STATUS_MOVER && $_SESSION['isPesTeam'] :
            case $status == AccountPersonRecord::PES_STATUS_RECHECK_PROGRESSING && $_SESSION['isPesTeam'] :
                
                $pesStatusWithButton.= "<button type='button' class='btn btn-default btn-xs btnPesStatus' aria-label='Left Align' ";
                $pesStatusWithButton.= " data-upesref='" .$upesRef . "' ";
                $pesStatusWithButton.= " data-emailaddress='" . $emailAddress . "' ";
                $pesStatusWithButton.= " data-account='" . $account . "' ";
                $pesStatusWithButton.= " data-accountid='" . $accountid . "' ";
                $pesStatusWithButton.= " data-accounttype='" . $accounttype . "' ";
                $pesStatusWithButton.= " data-pesdaterequested='" .trim($row['PES_DATE_REQUESTED']) . "' ";
                $pesStatusWithButton.= " data-pesrequestor='" .trim($row['PES_REQUESTOR']) . "' ";
                $pesStatusWithButton.= " data-pesstatus='" .$status . "' ";
                $pesStatusWithButton.= array_key_exists('PASSPORT_FIRST_NAME', $row) ?  " data-passportfirst='" .$passportFirst . "' " : null;
                $pesStatusWithButton.= array_key_exists('PASSPORT_LAST_NAME', $row) ? " data-passportlastname='" .$passportLastname . "' " : null;
                $pesStatusWithButton.= " data-toggle='tooltip' data-placement='top' title='Amend PES Status'";
                $pesStatusWithButton.= " > ";
                $pesStatusWithButton.= "<span class='glyphicon glyphicon-edit ' aria-hidden='true'></span>";
                $pesStatusWithButton.= "</button>";
                break;
            case $status == AccountPersonRecord::PES_STATUS_RECHECK_REQ && !$_SESSION['isPesTeam'] :
            case $status == AccountPersonRecord::PES_STATUS_RECHECK_PROGRESSING && !$_SESSION['isPesTeam'] ;
            case $status == AccountPersonRecord::PES_STATUS_PES_PROGRESSING && !$_SESSION['isPesTeam'] :
            case $status == AccountPersonRecord::PES_STATUS_STARTER_REQUESTED && !$_SESSION['isPesTeam'] ;          
                $pesStatusWithButton.= "<button type='button' class='btn btn-default btn-xs btnPesCancel accessRestrict accessFm' aria-label='Left Align' ";
                $pesStatusWithButton.= " data-upesref='" .$upesRef . "' ";
                $pesStatusWithButton.= " data-emailaddress='" . $emailAddress . "' ";
                $pesStatusWithButton.= " data-account='" . $account . "' ";
                $pesStatusWithButton.= " data-accountid='" . $accountid . "' ";
                $pesStatusWithButton.= " data-accounttype='" . $accounttype . "' ";
                $pesStatusWithButton.= " data-pesdaterequested='" .trim($row['PES_DATE_REQUESTED']) . "' ";
                $pesStatusWithButton.= " data-pesrequestor='" .trim($row['PES_REQUESTOR']) . "' ";
                $pesStatusWithButton.= " data-pesstatus='" .$status . "' ";
                $pesStatusWithButton.= array_key_exists('PASSPORT_FIRST_NAME', $row) ?  " data-passportfirst='" .$passportFirst . "' " : null;
                $pesStatusWithButton.= array_key_exists('PASSPORT_LAST_NAME', $row) ? " data-passportlastname='" .$passportLastname . "' " : null;
                $pesStatusWithButton.= " data-toggle='tooltip' data-placement='top' title='Cancel PES Request'";
                $pesStatusWithButton.= " > ";
                $pesStatusWithButton.= "<span class='glyphicon glyphicon-erase ' aria-hidden='true' ></span>";
                $pesStatusWithButton.= "</button>";
                break;
            case $status == AccountPersonRecord::PES_STATUS_CANCEL_CONFIRMED && $_SESSION['isPesTeam'] :
            default:
                break;
        }

    //     if(isset($row['PROCESSING_STATUS']) && ( $row['PES_STATUS']== AccountPersonRecord::PES_STATUS_PES_PROGRESSING || $row['PES_STATUS']==AccountPersonRecord::PES_STATUS_STARTER_REQUESTED || $row['PES_STATUS']==AccountPersonRecord::PES_STATUS_RECHECK_REQ ) ){
    //         $pesStatusWithButton .= "&nbsp;<button type='button' class='btn btn-default btn-xs btnTogglePesTrackerStatusDetails' aria-label='Left Align' data-toggle='tooltip' data-placement='top' title='See PES Tracker Status' >";
    //         $pesStatusWithButton .= !empty($row['PROCESSING_STATUS']) ? "&nbsp;<small>" . $row['PROCESSING_STATUS'] . "</small>&nbsp;" : null;
    //         $pesStatusWithButton .= "<span class='glyphicon glyphicon-search  ' aria-hidden='true' ></span>";
    //         $pesStatusWithButton .= "</button>";

    //         $pesStatusWithButton .= "<div class='alert alert-info text-center pesProcessStatusDisplay' role='alert' style='display:none' >";
    //         $pesStatusWithButton .= AccountPersonTable::formatProcessingStatusCell($row);
    //         $pesStatusWithButton .= "</div>";
    //    }

        return $pesStatusWithButton;
    }

<<<<<<< HEAD
=======
    function amendPesStatusModal(){
        $now = new \DateTime();
        ?>
       <!-- Modal -->
    <div id="amendPesStatusModal" class="modal fade" role="dialog">
      <div class="modal-dialog">

            <!-- Modal content-->
        <div class="modal-content">
        <form id='psmForm' class="form-horizontal"  method='post'>
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Amend PES Status</h4>
            </div>
          <div class="modal-body" >

          <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Employee Details</h3>
          </div>
        <div class="panel-body">
            <div class='row'>
              <div class='form-group' >
                <div class='col-sm-6'>
                    <input class="form-control" id="psm_emailaddress" name="psm_emailaddress" value="" type="text" disabled>
                    <input class="form-control" id="psm_upesref" name="psm_upesref" value="" type="hidden" >
                </div>
                <div class='col-sm-6'>
                    <input class="form-control" id="psm_account" name="psm_account" value="" type="text" disabled>
                    <input class="form-control" id="psm_accountid" name="psm_accountid" value="" type="hidden" >
                </div>
             </div>
           </div>
           <div class='row' id='passportNameDetails' >
              <div class='form-group' >
                <div class='col-sm-6'>
                    <input class="form-control" id="psm_passportFirst" name="psm_passportFirst" value="" type="text" placeholder='Passport First Name' disabled >
                </div>
                <div class='col-sm-6'>
                    <input class="form-control" id="psm_passportSurname" name="psm_passportSurname" value="" type="text" placeholder='Passport Surname'>
               </div>
             </div>
           </div>

         </div>
          </div>

          <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">PES Status</h3>
          </div>
        <div class="panel-body">
           <div class='row'>
              <div class='form-group required' >
                <label for='psm_status' class='col-md-2 control-label '>Status</label>
                <div class='col-md-4'>
                      <select class='form-control select' id='psm_status'
                                name='psm_status'
                                required='required'
                                data-tags="true" data-placeholder="Status" data-allow-clear="true"
                               >
                    <option value=''>Status</option>
                    <option value='<?=AccountPersonRecord::PES_STATUS_CLEARED;?>'><?=AccountPersonRecord::PES_STATUS_CLEARED;?></option>
                    <option value='<?=AccountPersonRecord::PES_STATUS_DECLINED;?>'><?=AccountPersonRecord::PES_STATUS_DECLINED;?></option>
                    <option value='<?=AccountPersonRecord::PES_STATUS_EXCEPTION;?>'><?=AccountPersonRecord::PES_STATUS_EXCEPTION;?></option>
                    <option value='<?=AccountPersonRecord::PES_STATUS_FAILED;?>'><?=AccountPersonRecord::PES_STATUS_FAILED;?></option>
                    <option value='<?=AccountPersonRecord::PES_STATUS_PES_PROGRESSING;?>'><?=AccountPersonRecord::PES_STATUS_PES_PROGRESSING;?></option>
                    <option value='<?=AccountPersonRecord::PES_STATUS_STARTER_REQUESTED;?>'><?=AccountPersonRecord::PES_STATUS_STARTER_REQUESTED;?></option>
                    <option value='<?=AccountPersonRecord::PES_STATUS_PROVISIONAL;?>'><?=AccountPersonRecord::PES_STATUS_PROVISIONAL;?></option>
                    <option value='<?=AccountPersonRecord::PES_STATUS_REMOVED;?>'><?=AccountPersonRecord::PES_STATUS_REMOVED;?></option>
                    <option value='<?=AccountPersonRecord::PES_STATUS_REVOKED;?>'><?=AccountPersonRecord::PES_STATUS_REVOKED;?></option>
                    <option value='<?=AccountPersonRecord::PES_STATUS_CANCEL_REQ;?>'><?=AccountPersonRecord::PES_STATUS_CANCEL_REQ;?></option>
                    <option value='<?=AccountPersonRecord::PES_STATUS_CANCEL_CONFIRMED;?>'><?=AccountPersonRecord::PES_STATUS_CANCEL_CONFIRMED;?></option>
                    <option value='<?=AccountPersonRecord::PES_STATUS_TBD;?>'><?=AccountPersonRecord::PES_STATUS_TBD;?></option>
                    <option value='<?=AccountPersonRecord::PES_STATUS_RECHECK_REQ;?>'><?=AccountPersonRecord::PES_STATUS_RECHECK_REQ;?></option>
                    <option value='<?=AccountPersonRecord::PES_STATUS_RECHECK_PROGRESSING;?>'><?=AccountPersonRecord::PES_STATUS_RECHECK_PROGRESSING;?></option>
                    <option value='<?=AccountPersonRecord::PES_STATUS_LEFT_IBM;?>'><?=AccountPersonRecord::PES_STATUS_LEFT_IBM;?></option>
                    <option value='<?=AccountPersonRecord::PES_STATUS_STAGE_1;?>'><?=AccountPersonRecord::PES_STATUS_STAGE_1;?></option>
                    <option value='<?=AccountPersonRecord::PES_STATUS_STAGE_2;?>'><?=AccountPersonRecord::PES_STATUS_STAGE_2;?></option>
                    <option value='<?=AccountPersonRecord::PES_STATUS_MOVER;?>'><?=AccountPersonRecord::PES_STATUS_MOVER;?></option>
                    </select>
                 </div>
                 <label for='pes_date' class='col-md-1 control-label '>Date</label>
                 <div class='col-md-3'>
                  <input class="form-control" id="pes_date" name="pes_date" value="<?=$now->format('d-m-Y') ?>" type="text" placeholder='Pes Status Changed' data-toggle='tooltip' title='PES Date Responded'>
                  <input class="form-control" id="pes_date_db2"  value="<?=$now->format('Y-m-d') ?>" name="pes_date_db2" type='hidden' >
                 </div>
                </div>
              </div>

           <div class='row'>
              <div class='form-group required' >
                <label for='psm_detail' class='col-md-2 control-label '>Detail</label>
                  <div class='col-md-8' id='pesDateDiv'>
                  <input class="form-control" id="psm_detail" name="psm_detail" value="" type="text"  >
                     </div>
                </div>
             </div>
        	<div class='row'>
  				<div class='col-sm-10 col-sm-offset-2 '>
  					<label class="radio"><input type="radio" name="emailNotification" value='send' checked >Send Notification (If applicable)</label>
  					<label class="radio"><input type="radio" name="emailNotification" value='suppress' >Do NOT Send Notification</label>
				</div>
			</div>
	</div>

           </div>
          </div>
          <div class="modal-footer">
	          <div class="button-blue submitButtonDiv" style="display: block ">
        		<input class="btn btn-primary" type="submit" name="Submit" id="savePesStatus" value="Submit">
        		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        	  </div>
          </div>
          </form>
        </div>
      </div>
    </div>
    </div>
        <?php

    }

    function confirmSendPesEmailModal(){
        ?>
       <!-- Modal -->
    <div id="confirmSendPesEmailModal" class="modal fade" role="dialog">
          <div class="modal-dialog">
          <div class="modal-content">
          <div class="modal-header">
             <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title">Confirm PES Email Details</h4>
            </div>
             <div class="modal-body" >
             <div class="panel panel-default">
  <div class="panel-heading">Applicant Details</div>
  <div class="panel-body">
  <form>
    <div class="form-group">
    <label for="pesEmailCnum">Cnum</label>
    <input type="text" class="form-control" id="pesEmailCnum" name="pesEmailCnum" disabled >
    <input type="hidden" class="form-control" id="pesEmailUpesRef" name="pesEmailUpesRef" disabled >
  </div>
    <div class="form-group">
    <label for="pesEmailFullName">Full Name</label>
    <input type="text" class="form-control" id="pesEmailFullName" name="pesEmailFullName" disabled >
  </div>
  <div class="form-group">
    <label for="pesEmailAddress">Email address</label>
    <input type="text" class="form-control" id="pesEmailAddress" name="pesEmailAddress" disabled >
  </div>
  <div class="form-group">
    <label for="pesEmailCountryOfResidence">Country of Residence</label>
    <input type="text" class="form-control" id="pesEmailCountry" name="pesEmailCountry" disabled >
  </div>
    <div class="form-group">
    <label for="pesEmailAccount">Account</label>
    <input type="text" class="form-control" id="pesEmailAccount" name="pesEmailAccount" disabled >
    <input type="hidden" class="form-control" id="pesEmailAccountId" name="pesEmailAccountId" disabled >
  </div>
  <div class="form-group">
    <label for="pesEmailApplicationForm">Application Forms</label>
    <textarea class="form-control" id="pesEmailApplicationForm" name="pesEmailApplicationForm" disabled ></textarea>
  </div>
   <div class="form-group">
    <label for="pesEmailRecheck">Recheck</label>
    <input type="text" class="form-control" id="pesEmailRecheck" name="pesEmailRecheck" disabled >
  </div>
</form>
</div>
</div>
            </div>
             <div class='modal-footer'>

             <div class='button-blue submitButtonDiv' style='display: block '>
        		<input class='btn btn-primary' type='submit' name='confirmSendPesEmail' id='confirmSendPesEmail'  value='Confirm' >&nbsp;
                <button type="button" class="btn btn-default" data-dismiss="modal" >Close</button>
      		</div>
             </div>
            </div>
        </div>
      </div>
    <?php
    }

    function confirmFLMAsBoardingRequestorModal(){
      ?>
     <!-- Modal -->
      <div id="confirmFLMAsBoardingRequestorModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
           <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Confirm Functional Manager Email Details</h4>
          </div>
           <div class="modal-body" >
           <div class="panel panel-default">
            <div class="panel-heading">Functional Manager Details</div>
            <div class="panel-body">
            <form>
              <div class="form-group">
                <label for="pesEmailAddress">Email address</label>
                <input type="text" class="form-control" id="pesEmailAddress" name="pesEmailAddress" disabled >
              </div>
              <div class="form-group">
                <label for="pesEmailFullName">Full Name</label>
                <input type="text" class="form-control" id="pesEmailFullName" name="pesEmailFullName" disabled >
              </div>
              <div class="form-group">
                <label for="pesEmailCnum">Cnum</label>
                <input type="text" class="form-control" id="pesEmailCnum" name="pesEmailCnum" disabled >
                <input type="hidden" class="form-control" id="pesEmailUpesRef" name="pesEmailUpesRef" disabled >
              </div>
            </form>
            </div>
            </div>
          </div>
           <div class='modal-footer'>
            <div class='button-blue submitButtonDiv' style='display: block '>
              <input class='btn btn-primary' type='submit' name='confirmSendPesEmail____' id='confirmSendPesEmail____' value='Confirm' >&nbsp;
              <button type="button" class="btn btn-default" data-dismiss="modal" >Close</button>
            </div>
           </div>
          </div>
      </div>
    </div>
  <?php
  }

  function confirmOtherAsBoardingRequestorModal(){
    ?>
   <!-- Modal -->
    <div id="confirmOtherAsBoardingRequestorModal" class="modal fade" role="dialog">
      <div class="modal-dialog">
      <div class="modal-content">
      <div class="modal-header">
         <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Confirm Other Employee Email Details</h4>
        </div>
         <div class="modal-body" >
         <div class="panel panel-default">
            <div class="panel-heading">Specific Employee Details</div>
            <div class="panel-body">
            <form>
              <div class="form-group">
                <label for="pesEmailAddress">Email address</label>
                <input type="text" class="form-control" id="pesEmailAddress" name="pesEmailAddress" disabled >
              </div>
              <div class="form-group">
                <label for="pesEmailFullName">Full Name</label>
                <input type="text" class="form-control" id="pesEmailFullName" name="pesEmailFullName" disabled >
              </div>
              <div class="form-group">
                <label for="pesEmailCnum">Cnum</label>
                <input type="text" class="form-control" id="pesEmailCnum" name="pesEmailCnum" disabled >
                <input type="hidden" class="form-control" id="pesEmailUpesRef" name="pesEmailUpesRef" disabled >
              </div>
            </form>
            </div>
          </div>
        </div>
         <div class='modal-footer'>
          <div class='button-blue submitButtonDiv' style='display: block '>
            <input class='btn btn-primary' type='submit' name='confirmSendPesEmail__' id='confirmSendPesEmail__' value='Confirm' >&nbsp;
            <button type="button" class="btn btn-default" data-dismiss="modal" >Close</button>
          </div>
         </div>
        </div>
    </div>
  </div>
<?php
}

<<<<<<< HEAD
>>>>>>> 481c0dfe9947cef192191baa1c37e1d1ccd89b8e
=======
>>>>>>> 481c0dfe9947cef192191baa1c37e1d1ccd89b8e
    function sendNotificationToPesTaskid(){

        $loader = new Loader();
        $allAccounts = $loader->loadIndexed('ACCOUNT','ACCOUNT_ID',AllTables::$ACCOUNT);
        $allPeople   = $loader->loadIndexed('FULL_NAME','UPES_REF',AllTables::$PERSON, " UPES_REF='" . db2_escape_string($this->UPES_REF) ."' ");
        $allPesTaskid = $loader->loadIndexed('TASKID','ACCOUNT_ID',AllTables::$ACCOUNT);

        $name  = $allPeople[$this->UPES_REF];
        $account = $allAccounts[$this->ACCOUNT_ID];
        $pesTaskid = $allPesTaskid[$this->ACCOUNT_ID];
        $pesRequestor = $this->PES_REQUESTOR;

        $to = array();
        $to[] = $pesTaskid;

        $now = new \DateTime();

        $replacements = array(
            $name,
            $account,
            $pesRequestor,
            $now->format('Y-m-d H:i:s'),
        );
        $message = preg_replace(self::$pesEmailPatterns, $replacements, self::$pesEmailBody);

        $response = \itdq\BlueMail::send_mail($to, 'uPES Starter Request - ' . $name . " ("  . $account . ") ", $message, $pesTaskid);
        
        $pesTracker = new AccountPersonTable(AllTables::$ACCOUNT_PERSON);
        $pesTracker->savePesComment($this->UPES_REF,$this->ACCOUNT_ID, "saveNotificationToPesTaskid sendMail: " . $response['sendResponse']['response']);
        

        return $response;
    }


    function sendPesStatusChangedEmail(){

        $loader = new Loader();
        $allAccounts = $loader->loadIndexed('ACCOUNT','ACCOUNT_ID',AllTables::$ACCOUNT);
        $allPesTaskid = $loader->loadIndexed('TASKID','ACCOUNT_ID',AllTables::$ACCOUNT);

        $personTable = new PersonTable(AllTables::$PERSON);
        $emailAddress = $personTable->getEmailFromUpesref($this->UPES_REF);
        $requestor = $this->PES_REQUESTOR;
        $names = $personTable->getNamesFromUpesref($this->UPES_REF);
        $fullName = $names['FULL_NAME'];
        //$account = AccountTable::getAccountNameFromId($this->ACCOUNT_ID);
        $account = $allAccounts[$this->ACCOUNT_ID];
        $pesTaskid = $allPesTaskid[$this->ACCOUNT_ID];
        $to = array();
        $cc = array();

        switch ($this->PES_STATUS) {
            case self::PES_STATUS_PROVISIONAL:
                // Only during covid
                $pattern   = self::$pesClearedProvisionalEmailPattern;
                $emailBody = self::$pesClearedProvisionalEmail;
//                 $pesClearedDateObj = \DateTime::createFromFormat('Y-m-d', $this->PES_CLEARED_DATE);
//                 $pesClearedDate = $pesClearedDateObj ? $pesClearedDateObj->format('D dS M Y') : $this->PES_CLEARED_DATE;
                $replacements = array($fullName, $account);
                $title = "PES($account) Status Change";
                !empty($emailAddress) ? $to[] = $emailAddress : null;
                !empty($requestor)    ? $to[] = $requestor : null;
                break;
            case self::PES_STATUS_CLEARED:
                $pattern   = self::$pesClearedEmailPattern;
                $emailBody = self::$pesClearedEmail;
                $pesClearedDateObj = \DateTime::createFromFormat('Y-m-d', $this->PES_CLEARED_DATE);
                $pesClearedDate = $pesClearedDateObj ? $pesClearedDateObj->format('D dS M Y') : $this->PES_CLEARED_DATE;
                $replacements = array($fullName,$pesClearedDate,$pesTaskid, $account);
                $title = "PES($account) Status Change";
                !empty($emailAddress) ? $to[] = $emailAddress : null;
                !empty($requestor)    ? $to[] = $requestor : null;
                break;
            case self::PES_STATUS_CANCEL_REQ:
                $pattern   = self::$pesCancelPesEmailPattern;
                $emailBody = self::$pesCancelPesEmail;
                $title = "PES($account) Cancel Request";
                $replacements = array($fullName,$this->UPES_REF, $_SESSION['ssoEmail'], $account);
                $to[] = $pesTaskid;
                !empty($requestor) ? $cc[] = $requestor : null;
                $cc[] = $_SESSION['ssoEmail'];
                break;
            default:
                $pattern = '';
                $emailBody = '';
                $title = '';
                $replacements = '';
                break;
        }

        if (!empty($pattern) && !empty($emailBody) && !empty($title) && !empty($replacements)) {

          AuditTable::audit(print_r($pattern,true),AuditTable::RECORD_TYPE_DETAILS);
          AuditTable::audit(print_r($replacements,true),AuditTable::RECORD_TYPE_DETAILS);
          AuditTable::audit(print_r($emailBody,true),AuditTable::RECORD_TYPE_DETAILS);
  
          $message = preg_replace($pattern, $replacements, $emailBody);
  
          AuditTable::audit(print_r($message,true),AuditTable::RECORD_TYPE_DETAILS);
  
          $response = \itdq\BlueMail::send_mail($to, $title ,$message, $pesTaskid, $cc);
          
          $pesTracker = new AccountPersonTable(AllTables::$ACCOUNT_PERSON);
          $pesTracker->savePesComment($this->UPES_REF,$this->ACCOUNT_ID, "sendPesStatusChangedEmail sendMail: " . $response['sendResponse']['response']);
          
        } else {

          $response = false;
        
          $pesTracker = new AccountPersonTable(AllTables::$ACCOUNT_PERSON);
          $pesTracker->savePesComment($this->UPES_REF,$this->ACCOUNT_ID, "sendPesStatusChangedEmail sendMail: Message has been sent.");
          
        }

        return $response;
    }





}