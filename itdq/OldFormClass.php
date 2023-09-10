<?php
namespace itdq;
/**
 * Provides Methods that create Input fields in an HTML Form and loads them into the descending classes variables.
 *
 * Most classes that use forms will be descended from this class.
 *
 * @author GB001399
 * @package itdqLib
 *
 */
class OldFormClass {

    protected $state;
    protected $chkState;
    protected $notEditable;
    protected $currentMode;

	private $fcFormName;

	/**
	 * Only just learned the benefit of Static variables, so have defined some here for MODE - but they are not used throughout the code
	 * You'll find examples where I've hardcoded the string 'Display' etc.
	 *
	 * @static
	 */
	public static $modeDISPLAY = 'Display';
	public static $modeDEFINE = 'Define';
	public static $modeEDIT = 'edit';

	public static $bpFieldIntranet = 'Intranet';
	public static $bpFieldNotesid  = 'Notesid';
	public static $bpFieldBoth     = 'Both';
	public static $bpFieldBothUid  = 'Uid';

	public static $javaValidateStart = "<script>
				  	function checkSubmit(){
			      	var form = document.Form.fcFormName.value;
				  	switch(form){";

	public static $javaValidateEnd = "	default:
				   	break;
				  	}
				  }
				  </script>";
	public static $yesNo = array('Yes','No');
	public static $trueFalse = array('True'=>'True','False'=>'False');
	public static $ragSelect = array('Red','Amber','Green');

	public static $formNewRow    = true;
	public static $formNotNewRow = false;
	public static $formDisabled  = ' DISABLED ';
	public static $formReadonly  = ' READONLY ';
	public static $formNull		 =  null;
	public static $formNoHelp	 =  null;
	public static $formNoOnChange=  null;
	public static $formNoHighlight= FALSE;
	public static $formFirstSelect='Select.....';
	public static $formClass	= 'blue-med-light';
	public static $db2TimeFormat = 'Y-m-d H:i:s';

	public static $ragRed = 'red';
	public static $ragAmber = 'yellow';
	public static $ragGreen = '#00ff00';

	public static $formDummySelectArray = array('Select....');


	public static $labelColCssReadOnly = "col-sm-3";
	public static $fieldColCssReadOnly = "col-sm-9";


	public $toolTipPosition = 'left';
	public $toolTip = '';
	public $onChange='';



	/**
	 * Will step through all the Variables in the Class and if there is an element in the $_REQUEST module
	 * of the same name, then it will populate the Variable with the value from $_REQUEST.
	 * So basically, any classes that descend from this class, can pick up value from the screen directly into
	 * the class' variables.
	 */
	function getForm() {
		foreach ( $this as $key => $value ) {
			if (isset ( $_REQUEST [$key] )) {
				$this->{$key} = $this->validateFormField($key, trim ( $_REQUEST [$key] ));
			}
		}
		$_SESSION ['fcFormName'] = $this->fcFormName;
	}

	/**
	 *
	 * If $_REQUEST[$fieldName] is populated(even with null), you'll get that value back
	 * Else If $_SESSION[$fieldName] is populated(even with null), you'll get that value back
	 * Else you'll get null back.
	 *
	 * @param unknown_type $fieldName
	 * @return string|unknown|NULL
	 */
	static function getFormFieldValue($fieldName){
		if(isset($_REQUEST[$fieldName])){
			return trim($_REQUEST[$fieldName]);
		} elseif(isset($_SESSION[$fieldName])){
			return $_SESSION[$fieldName];
		} else {
			return NULL;
		}
	}


	/*
	 * Allows you to perform validation on the field from the onscreen Form.
	 * Expects you to return a validated value that will then be stored in $this->$field.
	 */
	function validateFormField($field, $value){
		return $value;
	}

	/**
	 *
	 * @param string $name
	 */
	function setfcformName($name) {
		$this->fcFormName = $name;
	}

	/**
	 * Used for Debugging.
	 * It will display the values of any Variables in the class.
	 * It will iteritvely call it's self, should any of those variables contain other Objects.
	 * Each field is displayed with it's name in Bold then it's current value.
	 *
	 * By default, the variables follow on after the other, specify $length as anything other than 'short' and
	 * each variable will be displayed on it's own line.
	 *
	 * @param string $length
	 */
	function iterateVisible($length = 'short',$dbFieldsOnly = true,$color='#888888') {
	    echo "<span style='color:$color'>";
		echo "<B>" . get_class($this) . ":</B><BR/>\n";
		if ($length != 'short') {
			echo "<BR>";
		}
		foreach ( $this as $key => $value ) {
			if (!$dbFieldsOnly and is_array ( $value )) {
				echo "<B>$key =></B>";
				print_r ( $value );
			} elseif (!$dbFieldsOnly and is_object($value)){
				echo "<BR/><B> OBJECT (" . get_class($value) . "): $key</B>";
				$value->iterateVisible($length);
				echo "<B>:OBJECT END </B><BR/>";
			} else {
				if($dbFieldsOnly and $key==strtoupper($key))
				print "<B>$key =></B> $value\n";
			}
			if ($length != 'short') {
				echo "<BR>";
			}
		}
		echo "</span>";
	}

	/**
	 * Used for Debugging.
	 * It will display the values of any Variables in the class.
	 * It will iteritvely call it's self, should any of those variables contain other Objects.
	 * Each field is displayed with it's name in Bold then it's current value.
	 *
	 * By default, the variables follow on after the other, specify $length as anything other than 'short' and
	 * each variable will be displayed on it's own line.
	 *
	 * @param string $length
	 */
	function iterateSpecificFields($fieldsToDisplay=null,$length='short') {
		echo "<B>" . get_class($this) . "::iterateFieldsToDisplay()</B><BR/>\n";
		if ($length != 'short') {
			echo "<BR>";
		}
		foreach ($fieldsToDisplay as $field){
			foreach ( $this as $key => $value ) {
				if($key == $field){
					if (is_array ( $value )) {
						echo "<B>$key =></B>";
						print_r ( $value );
					} elseif (is_object($value)){
						echo "<BR/><B> OBJECT (" . get_class($value) . "): $key</B>";
						$value->iterateVisible($length);
						echo "<B>:OBJECT END </B><BR/>";
					} else {
						print "<B>$key =></B> $value\n";
					}
					if ($length != 'short') {
						echo "<BR>";
					}
				}
			}
		}
	}


	/**
	 * Used for Debugging.
	 * Rather than displaying the contents of the Class on the screen, it returns an XML Form.
	 *
	 * @param string $class	This names the 'object' being defined in the XML.
	 * @return string		Returns the XML form.
	 */
	function toXML($class='object',$dbPropertiesOnly=true) {
        $xmlReply = new \XMLWriter();
        $xmlReply->openMemory();
        $xmlReply->startElement($class);

        foreach ($this as $key => $value) {
            if ($dbPropertiesOnly && $key == strtoupper($key)) {
                $xmlReply->writeElement($key, trim($value));
            } elseif(!$dbPropertiesOnly) {
                if (is_array($value)) {
                    $xmlReply->writeElement($key, print_r($value));
                } else {
                    $xmlReply->writeElement($key, trim($value));
                }
            }
        }
        $xmlReply->endElement();
        return $xmlReply->outputMemory();


	}
	/**
	 * Creates a TextArea input field on the screen
	 *
	 * @param string $title			Text displayed as a seperate Cell, typically would be the label for this input field.
	 * @param string $fieldName		This becomes the ID and the NAME of the TEXTAREA, so this needs to be the variable in the Class that you want GetForm to return the value of this field to.
	 * @param string $state			Should the field be DISABLED, READONLY etc.
	 * @param string $class			CSS Class to format the field.
	 * @param string $help			Any text in here is displayed when the mouse id hovered over the field.
	 * @param string $maxLength		Max length expected on the Input field. But you need to code a javascript form checker to impose it. NOTE: The last digit can be used to determin how many rows on the screen the textarea will occupy. 503 would be a 500 byte field over 3 lines.
	 */
	function formTextArea($label, $fieldName, $state=null, $textAreaclass=null,$help=null, $maxLength=500, $dataPlacement='top', $textAreaDivClass='col-sm-10', $rows=2, $placeholder=null) {
	    echo "<div class='form-group' id='$fieldName" . "FormGroup' >";
	    echo "<label for='$fieldName' class='col-sm-2 control-label' data-toggle='tooltip' data-placement='$dataPlacement' title='$help' >$label :</label>";
	    echo "<div class='col-sm-8'>";
		//echo "<input type='hidden' id='" . $fieldName . "Length' name = '" . $fieldName . "Length' value='$maxLength' />";
		// You can set the number of rows, by specifying a non 0 digit at the end of MaxLength
		echo "<div id='spaceForGlyphicon' style='display: none;'></div>";
		echo "<div class='$textAreaDivClass' id='divForTextarea'>";
	    echo "<TEXTAREA rows='$rows' class='form-control $textAreaclass' id='$fieldName' name='$fieldName' $state maxLength='$maxLength' placeholder='$placeholder'>" . $this->$fieldName . "</TEXTAREA>";

        echo "</div>";// divForTextArea
	    echo "</div>"; // col-sm-8
        echo "</div>"; // form-group

	}

	/**
	 * Creates a INPUT HTML Tag, in two cells, $title in a TH cell and INPUT in a TD cell.
	 *
	 * @param string $title			Label for the input field, displayed in it's own <TH> cell
	 * @param string $fieldName		This is the ID and the NAME of the input field, so this needs to be the variable in the Class that you want GetForm to return the value of this field to.
	 * @param string $state			Should the field be DISABLED, READONLY etc.
	 * @param string $size			Sets the Size of the input field.
	 * @param string $class			CSS Class to format the field.
	 * @param Boolean $newRow		True: If the Label and Input field should exist in their own row in the table, then this function will wrap the <TR /TR> around the cells, if FALSE, it won't.
	 * @param string $help			Any text in here is displayed when the mouse id hovered over the field.
	 * @param string $width			Width of the <TH> Label Field, not the width of the input field - use Size to make that bigger or smaller.
	 * @param string $onchange		Here you can pass the javascript function to be invoked when the field changeds.
	 * @param string $colspan		if you need the Cell containing the input field to span multiple fields in the table.
	 */
	function formInput($title, $fieldName, $state=null, $size='20', $class='blue-med-light',$help=null, $width='30%', $onchange=null, $colspan='1', $linkWidth='70%') {

		echo "<div class='form-group'>";
		echo "<label for='$fieldName' class='col-sm-2 control-label' >$title :</label>";
		echo "<div class='col-sm-8'>";
		if($help!=null){
			echo "&nbsp;<a class=boxpopup3><img src='../ItdqLib_V1/ui/images/icon-help-contextual-dark.gif' width='14' height='14'/><span>" . htmlspecialchars($help) . "</span></a>";
		}

		echo "<input class='form-control' id='$fieldName' name='$fieldName' value='" . $this->$fieldName . "' $state Size='$size' tabindex='1' $onchange required />";
		echo "<INPUT TYPE='hidden' id='original$fieldName' name='original$fieldName' value='" . $this->$fieldName . "' />";
        echo "</div>"; // col-sm-8
        echo "</div>"; // form-group

	}

	function formHiddenInput($fieldName='mode', $fieldValue='insert') {
	    echo "<INPUT TYPE='hidden' id='$fieldName' name='$fieldName' value='$fieldValue' />";

	}

	function formEmail($title, $fieldName, $state=null,  $onchange=null)
	{
	    ?>
	    <div class='form-group'>
	    <label for='<?=$fieldName;?>' class='col-sm-2 control-label' ><?=$title;?>:</label>
	    <div class='col-sm-8'>
	    <input type='email' class='form-control' id='<?=$fieldName;?>' name='<?=$fieldName;?>' value='<?=$this->$fieldName;?>' <?=$state;?> onchange='<?=$onchange;?>' required />
	    <input type='hidden' id='original<?=$fieldName;?>' name='original<?=$fieldName;?>' value='<?=$this->$fieldName;?>' />
	    </div>
	    </div>
	    <?php
	}


	/**
	 *
	 * Used to put a checkbox on the screen.
	 * @param unknown_type $title
	 * @param unknown_type $fieldName
	 * @param unknown_type $state
	 * @param unknown_type $size
	 * @param unknown_type $class
	 * @param unknown_type $newRow
	 * @param unknown_type $help
	 * @param unknown_type $width
	 * @param unknown_type $onchange
	 * @param unknown_type $colspan
	 * @param unknown_type $linkWidth
	 */
// 	function formCheckbox($title, $fieldName, $state, $size='20', $rowClass='blue-med-light',$newRow=true,$help=null, $width='30%', $onchange=null, $colspan='1', $linkWidth='70%', $value="Y"){
// 		if($newRow){
// 			echo "\n<TR class='$rowClass'>";
// 		}

// 		echo "<TH id='label$fieldName' width='$width'>$title";

// 		if($help!=null){
// 			echo "&nbsp;<a class=boxpopup3><img src='../ItdqLib_V1/ui/images/icon-help-contextual-dark.gif' width='14' height='14'/><span>" . htmlspecialchars($help) . "</span></a>";
// 		}
// 		echo "</TH>";

// 		echo "<TD colspan='$colspan' width='$linkWidth' ><input type='checkbox' id='$fieldName' name='$fieldName' value='" . $value . "' $state Size='$size' tabindex='1' $onchange /></TD>";




// 		if($newRow){
// 			echo "</TR>";
// 		}

// 		echo isset($this->$fieldName) ? "<INPUT TYPE='hidden' id='original$fieldName' name='original$fieldName' value='" . $this->$fieldName . "' />" : null;
// 	}

	/**
	 * Offers a set of Checkbox Buttons in the bootstrap style.
	 * requires bootstrap buttons.js or a bootstrap.js containing the buttons javascript
	 *
	 * @param array $arrayOfOptions.
	 *            An array of the buttons values
	 * @param char $label
	 *            Label for the set of buttons.
	 * @param char $field
	 *            Field that the response will be stored in.
	 * @param char $onChange
	 *            js script to call on change.
	 * @param char $defaultSelection
	 *            Button that will be initially selected
	 * @param char $buttonClass
	 *            css class to apply to the button (if using bootstrap: btn-default, btn-success, btn-danger etc etc)
	 *
	 *            Note: The value is stored in a field that is created dynamically and appended to the form with id=$field
	 */
	function formCheckbox($arrayOfOptions, $label, $field, $vertical = false, $state = null)
	{
	    $this->$field = is_array($this->$field) ? $this->$field : array();
	    $disabled = (strtolower(trim($state))=='readonly') ? 'disabled' : null;
	    ?>
	            <div class='form-group' id='<?=$field?>FormGroup'>
	            <label  class='col-sm-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='<?=$this->toolTipPosition?>' title='<?=$this->toolTip?>'><?=$label?></label>
	            <div class='col-sm-10'>
	            <span class="ibm-input-group ibm-radio-group">
	            <?php
	            $fieldCounter = 0;
	            foreach ($arrayOfOptions as $value) {
	                $checked = in_array($value, $this->$field) ? 'checked' : null;
	                ?><input class='ceta_checkbox' id='<?=$field.++$fieldCounter?>' name='<?=$field?>[]' type="checkbox" value="<?=$value?>" <?=$disabled?> <?=$checked?> onchange='<?=$this->onChange?>' />&nbsp;<label for="<?=$field.$fieldCounter?>"><?=$value?></label>
	                <?=$vertical ? "<br/>" : null;?>
	                <?php
	            }
	            ?>
	            </span>
	            </div>
	            </div>
	            <?php
	    	$this->setToolTip();
	    	$this->setOnchange();
	        }




	/**
	 * Offers a set of Radio Buttons.
	 *
	 * @param array $arrayOfOptions. An array of the buttons values
	 * @param char $label		   Label for the set of buttons.
	 * @param char $field		   Field that the response will be stored in.
	 */
	function formRadioButtons($arrayOfOptions, $label, $field, $onChange=null, $arrayOfStates=null){
	    echo "<div class='form-group'>";
	    echo "<label for='$field' class='col-sm-2 control-label' >$label</label>";
	    echo "<div class='col-sm-8'>";

	    foreach ($arrayOfOptions as $key => $value) {
	        $checked = strtoupper(trim($this->$field)) == strtoupper(trim($value)) ? 'checked' : null;
	        $state = isset($arrayOfStates[$key]) ? $arrayOfStates[$key] : null;
	        echo "<label class='radio-inline'><input type='radio' name='$field' value='$value' $checked $state $this->onChange required>$key</label>";
	    }
	    echo "</div>"; // col-sm-8
	    echo "</div>"; // form-group
	}





	/**
	 * Used to create input fields that expect dates.
	 *
	 * Used in conjunction with :
	 *
	 * Kevin Luck's Data Picker.
	 *
	 * So you also need to provide the following in the page that invokes this form.
	 *
	 * <code>
	 *
	 * $_w3_header .= "	<link type='text/css' rel='stylesheet' href='/eSOFT/ui/css/datePicker.css' />";

	 * $_w3_header .= "<!-- jQuery -->";
	 * $_w3_header .= "<script type='text/javascript' src='ui/scripts/jquery-1.4.2-min.js'></script>";
	 * $_w3_header .= "<!-- required plugins -->";
	 * $_w3_header .= "<script type='text/javascript' src='ui/scripts/date.js'></script>";
	 * $_w3_header .= "<!--[if IE]><script type='text/javascript' src='ui/scripts/jquery.bgiframe.js'></script><![endif]-->";
 	 * $_w3_header .= "<!-- jquery.datePicker.js -->";
	 * $_w3_header .= "<script type='text/javascript' src='ui/scripts/jquery.datePicker.js'></script>";
	 *
	 *
	 * echo '<script type="text/javascript" charset="utf-8">';
	 * echo "Date.format = 'dd-mm-yyyy';";
	 * echo '$(function()';
	 * echo '{';
	 * echo "$('.date-pick').datePicker({startDate:'01-01-2010'});";
	 * echo "});";
	 * echo "</script>";
	 * </code>
	 *
	 * @param string $title			 - See formInput
	 * @param string $fieldName		 - See formInput
	 * @param string $state			 - See formInput
	 * @param string $class			 - See formInput
	 * @param Boolean $newRow		 - See formInput
	 * @param string $help			 - See formInput
	 *
	 * @link http://2005.kelvinluck.com/assets/jquery/datePicker/v2/demo/datePickerPastDate.html
	 */
// 	function formDate($title, $fieldName, $state='READONLY', $class='blue-med-light',$newRow=true,$help=null,$onChange=null,$headerStyle=null, $dataStyle=null){
// 		if($newRow){
// 			echo "<TR class='$class'>";
// 		}
// 		echo "<TH $headerStyle >$title";
// 		if($help!=null){
// 			echo "&nbsp;<a class=boxpopup3><img src='../ItdqLib_V1/ui/images/icon-help-contextual-dark.gif' width='14' height='14'/><span>" . htmlspecialchars($help) . "</span></a>";
// 		}
// 		// We always display the input field READONLY, the question is do we enable them to pick a date ?
// 		if(substr($state,0,8)=='READONLY'){
// 			echo "</TH><TD $dataStyle><input name='$fieldName' id='$fieldName' $state value='" . $this->$fieldName . "' />";
// 		} else {
// 			echo "</TH><TD $dataStyle><input name='$fieldName' id='$fieldName' class='date-pick' $state value='" . $this->$fieldName . "' $onChange />";
// 		}
// 		echo "<INPUT TYPE='hidden' id='original$fieldName' name='original$fieldName' value='" . $this->$fieldName . "' />";
// 		echo "</td>";


// 		if($newRow){
// 			echo "</TR>";
// 		}

// 	}
	/**
	 * Used to create input fields that expect dates, when you need a higher level of JS than Kevin Luck's solution appears to support.
	 *
	 */
// 	function formDate($title, $fieldName,$help=null){
// 	    echo "<div class='form-group'>";
// 	    echo "<label for='$fieldName' class='col-sm-2 control-label' data-toggle='tooltip' data-placement='top' title='$help'>$title</label>";
// 	    echo "<div class='input-group date form_date col-sm-2'  data-date-format='dd M yyyy' data-link-field='$fieldName' data-link-format='yyyy-mm-dd'>";
// 	  //  echo "<input type='hidden' id='$fieldName' value='' /><br />";
// 	    echo "<input class='form-control' size='30' type='text'  />";
// 	    echo "<input type='hidden' id='$fieldName' name='$fieldName' />";
//  	    echo "<span class='input-group-addon'><span class='glyphicon glyphicon-calendar form_date'></span></span>";
// 	    echo "</div>"; // input-group
// 	    echo "</div>"; // form-group
// 	}
	function formDate($title, $fieldName, $help = null, $state = null, $class="input-group date form_datetime", $addOnClass="input-group-addon")
	{
	    $placeholder = empty($placeholder) ? "Select Date" : $placeholder;
	    if (is_null($state)) {
	        // all other modes
	        if (isset($this->$fieldName)){
	            $rawDateString = $this->$fieldName;
	            $date = date_create_from_format('Y-m-d-H.i.s.u', $rawDateString);
	            $shortDateToDisplay = date_format($date, 'd-m-y');
	            $dateToDisplay = date_format($date, 'l jS F Y');
	            $timeToDisplay = date_format($date, 'H:i');
	            $dateTimeToDisplay = date_format($date, 'd F Y - h:i a');
	        } else {
	            $rawDateString = "";
	            $dateTimeToDisplay = "";
	        }


	        //28 October 2015 - 02:50 pm
	        ?>
	            <div class='form-group' id='<?=$fieldName?>" . "FormGroup'>
	            <label for='<?=$fieldName?>' class='col-md-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='<?=$help?>'><?=$title?></label>
	            <div class='col-md-10'>
	            <div id='calendarFormGroup<?=$fieldName?>' class='<?=$class?>' data-date-format='dd MM yyyy - HH:ii p' data-link-field='<?=$fieldName?>' data-link-format='yyyy-mm-dd-hh.ii.00'>
	            <input id='Input<?=$fieldName?>' class='form-control' type='text' readonly value='<?=$dateTimeToDisplay?>' placeholder='<?=$placeholder?>'  />
	            <input type='hidden' id='<?=$fieldName?>' name='<?=$fieldName?>' value='<?=$rawDateString?>' />
	            <span class='<?=$addOnClass?>'><span id='calendarIcon<?=$fieldName?>' class='glyphicon glyphicon-calendar'></span></span>
	            </div>
	            </div>
	            </div>
	            <?php
	        } else {
	            //display mode
	            ?>
	            <div class='form-group' id='<?=$fieldName?>" . "FormGroup'>
	            <label for='<?=$fieldName?>' class='col-md-2 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='<?=$help?>'><?=$title?></label>
	            <div class='col-md-10'>";
	            <?php
	            if ($this->$fieldName){
	                $date = date_create_from_format('Y-m-d-H.i.s.u', $this->$fieldName);
	                $shortDateToDisplay = date_format($date, 'd-m-y');
	                $dateToDisplay = date_format($date, 'l jS F Y');
	                $timeToDisplay = date_format($date, 'H:i');
	                ?>
	                "<input type='text' class='form-control' value='<?=$dateToDisplay?> (<?=$shortDateToDisplay?>) at <?=$timeToDisplay?>' readonly />
	                <?php
	            } else {
	                ?>
	                <input type='text' class='form-control' value='' readonly />
	                <?php
	            }
	            ?>
	            </div>
	            </div>
	            <?php
	        }

	        ?><script>
	        $(document).ready(function() {
	        var picker = new Pikaday({
	                field: document.getElementById('Input<?=$fieldName?>'),
	                format: 'D MMM YYYY',
	                showTime: false,
	                onSelect: function() {
	                    console.log(this.getMoment().format('Do MMMM YYYY'));
	                    var db2Value = this.getMoment().format('YYYY-MM-DD')
	                    console.log(db2Value);
	                    jQuery('#<?=$fieldName?>').val(db2Value);
	                }
	            });
	                jQuery('#calendarIcon<?=$fieldName?>').click(function(){
	                    jQuery('#Input<?=$fieldName?>').click();
	                });
	        });
	        </script>
	        <?php
	        }


	static function enableDatePicker(){
	    ?>
	    <script type='text/javascript'>enableDatepicker();</script>
	    <?php
	}



	function formDateV2($title, $fieldName, $state, $class='blue-med-light',$newRow=true,$help=null,$onChange=null,$headerStyle=null, $dataStyle=null){
	    if($newRow){
	        echo "<TR class='$class'>";
	    }
	    echo "<TH $headerStyle >$title";
	    if($help!=null){
	        echo "&nbsp;<a class=boxpopup3><img src='../ItdqLib_V1/ui/images/icon-help-contextual-dark.gif' width='14' height='14'/><span>" . htmlspecialchars($help) . "</span></a>";
	    }
	    // We always display the input field READONLY, the question is do we enable them to pick a date ?
	    if($state=='READONLY'){
	        echo "</TH><TD $dataStyle><input name='$fieldName' id='$fieldName' READONLY value='" . $this->$fieldName . "' /></TD>";
	    } else {
	        echo "</TH><TD $dataStyle><div style='position:relative;'><input name='$fieldName' id='$fieldName' class='datepicker' READONLY value='" . $this->$fieldName . "' $onChange /></div></TD>";
	    }
	    if($newRow){
	        echo "</TR>";
	    }
	    echo "<INPUT TYPE='hidden' id='original$fieldName' name='original$fieldName' value='" . $this->$fieldName . "' />";
	    // echo "<script>$('.datepicker').pickadate({ format: 'yyyy-mm-dd', format_submit: 'yyyy-mm-dd',  clear: false });</script>";


	}




	/**
	 * Creates a <SELECT> in the HTML Form.
	 *
	 * @param array $array					This array will form the entries in the Drop Down for the Select
	 * @param string $label					Label for the drop down, appears in the <TH> immediately before the <TD> this <SELECT> is in.
	 * @param string $field					Is the ID and NAME for the <SELECT>, so will be the Class Variable into which GetForm places the entry from the Form.
	 * @param string $state					Is the field, Readonly, Disabled etc - Remember <SELECT>fields won't return anything if READONLY, best to use DISABLED
	 * @param array $highlightFields		An array (or null) of fields that should be on a yellow background in the drop down. Seldom used feature.
	 * @param string $first					What is the first entry in the drop down - typically would be 'Select...' as opposed to an actual value from the array
	 * @param string $class					Opportunity to provide the CSS Class for formating the field.
	 * @param string $onChange				Opportunity to point to a Javascript Function that will be invoked onChange.
	 * @param string $size					sets the size= parm of the <SELECT>
	 * @param Boolean $newRow				If TRUE then <TR></TR> will be set around the <TH><TD> for this field. If FALSE, they won't be - enables you to have multiple input fields on 1 row of the form.
	 * @param string $help					Help text that will be displayed in a pop-up text box when they mouse over the field.
	 */
	function formSelect($array, $label, $field, $state=null, $first = 'Select...',$onChange= null, $class = null) {

		if ($state == 'readonly') {
			$state = 'disabled';
		}
		//	if(!isset($array)) { return;}
		// Drop Down Selection on a form
		$val = $this->$field;
		?>
		<div class='form-group'>
		<label for='<?=$field?>' class='col-sm-2 control-label' ><?=$label?>:</label>
		<div class='col-sm-8'>
		<?php
		$selectedValue = false;
		$selected = null;
		if (isset ( $array )) {
		    ?>
			<SELECT class='form-control <?=$class?>' id='<?=$field?>' name='<?=$field?>'  <?=$state.' '.$onChange?> >
			<option value=''><?=$first?></option>
			<?php
			foreach ( $array as $key => $value ) {
			    Trace::traceComment("Value $value Val $val",__METHOD__ , __LINE__);
			    if (htmlspecialchars_decode($value,ENT_QUOTES) == trim ( $val )  or htmlspecialchars_decode($key,ENT_QUOTES) === trim($val)) {
			        Trace::traceComment("Value $value Val $val selected",__METHOD__ , __LINE__);
			        $selected =  " selected ";
			        $selectedValue = htmlspecialchars_decode($value,ENT_QUOTES);
			    }
			    ?>
				<option data-key='<?=$key?>' data-value='<?=$value?>'   value='<?=$value?>' <?=$selected?>><?=htmlspecialchars_decode($value)?></option>
				<?php
			}
			?>
			</SELECT>
			<?php
		} else {
		    ?>
			<INPUT class='form-control' name='<?=$field?>' disabled value='<?=$val?>' maxlength='20'>
			<?php
		}
		if($selectedValue){
		    ?>
			<INPUT TYPE='hidden' id='original<?=$field?>' name='original<?=$field?>' value='<?=$selectedValue?>' />
			<?php
		}
		?>
		</div>
		</div>
		<?php
	}

	/**
	 * Similar to formSelect, only it will display the KEY for from $array whilst returning the VALUE in the field.
	 *
	 * @param array $array					This array will form the entries in the Drop Down for the Select
	 * @param string $label					Label for the drop down, appears in the <TH> immediately before the <TD> this <SELECT> is in.
	 * @param string $field					Is the ID and NAME for the <SELECT>, so will be the Class Variable into which GetForm places the entry from the Form.
	 * @param string $state					Is the field, Readonly, Disabled etc - Remember <SELECT>fields won't return anything if READONLY, best to use DISABLED
	 * @param array $highlightFields		An array (or null) of fields that should be on a yellow background in the drop down. Seldom used feature.
	 * @param string $first					What is the first entry in the drop down - typically would be 'Select...' as opposed to an actual value from the array
	 * @param string $class					Opportunity to provide the CSS Class for formating the field.
	 * @param string $onChange				Opportunity to point to a Javascript Function that will be invoked onChange.
	 * @param string $size					sets the size= parm of the <SELECT>
	 * @param Boolean $newRow				If TRUE then <TR></TR> will be set around the <TH><TD> for this field. If FALSE, they won't be - enables you to have multiple input fields on 1 row of the form.
	 * @param string $help					Help text that will be displayed in a pop-up text box when they mouse over the field.
	 */
	function formIxSelect($array, $label, $field, $state=null, $first = 'Select...',$onChange= null) {
		if ($state == 'readonly') {
			$state = 'disabled';
		}
		//	if(!isset($array)) { return;}
		// Drop Down Selection on a form
		$val = $this->$field;
		echo "<div class='form-group'>";
		echo "<label for='$field' class='col-sm-2 control-label' >$label :</label>";
		echo "<div class='col-sm-8'>";
		$selectedValue = false;
		if (isset ( $array )) {
			echo "<select class='form-control' id='$field' name='$field' required $state $onChange ";
			echo ">";
			echo "<option  data-key='' data-value='$first'value=''>$first</option>";
			foreach ( $array as $key => $value ) {
				echo "<option data-key='" . $key ."' data-value='" . $value ."' value='" . $value . "'";
				Trace::traceComment("Value $value Val $val",__METHOD__ , __LINE__);
				if (htmlspecialchars_decode($value,ENT_QUOTES) == trim ( $val )  or htmlspecialchars_decode($key,ENT_QUOTES) === trim($val)) {
					Trace::traceComment("Value $value Val $val selected ",__METHOD__ , __LINE__);
					echo " selected ";
					$selectedValue = htmlspecialchars_decode($value,ENT_QUOTES);
//					echo "<INPUT TYPE='hidden' id='original$field' value='" . htmlspecialchars_decode($value,ENT_QUOTES) . "' />";
				}
				echo ">" . htmlspecialchars_decode($key) . "</option>";
			}
			echo "</SELECT>";
		} else {
			echo "<INPUT class='form-control' ";
			echo " name='$field' disabled value='$val' maxlength='20'>";
		}
		if($selectedValue){
			echo "<INPUT TYPE='hidden' id='original$field' name='original$field' value='" . $selectedValue . "' />";
		}
		echo "</div>"; // col-sm-8
		echo "</div>"; // form-group
	}


	/**
	 *
	 * Same as formXiSelect, but it array_flips the Key and Value in the array, so displays one and returns the other.
	 *
	 *
	 * @param unknown $array
	 * @param unknown $label
	 * @param unknown $field
	 * @param string $state
	 * @param string $highlightFields
	 * @param string $first
	 * @param string $class
	 * @param string $onChange
	 * @param number $size
	 * @param string $newRow
	 * @param string $help
	 */
	function formXiSelect($array, $label, $field, $state=null, $highlightFields=null, $first = 'Select...',$class='blue-med-light', $onChange= null, $size=20,$newRow=true,$help=null){
		$newArray = array_flip($array);
		$this->formIxSelect($newArray, $label, $field, $state, $highlightFields, $first,$class, $onChange, $size,$newRow,$help);
	}


	function formUserid($fieldName,$title){

	    $notesid = $fieldName . "_NOTES_ID";
	    $name = $fieldName . "_NAME";
	    $intranet = $fieldName . "_INTRANET_ID";
	    $phone = $fieldName . "_PHONE";
	    $uid = $fieldName . "_UID";

	    echo "<div class='form-group'>";
	    echo "<label for='$notesid' class='col-sm-2 control-label' >$title :</label>";
	    echo "<div class='col-sm-8'>";
	    echo "<input name='$notesid' class='form-control typeahead' id='$notesid' autocomplete='off' size='64' maxlength='64' required   />";

	    echo property_exists($this, $intranet) ? "<input name='$intranet'  id='$intranet' type='hidden' />" : null;
	    echo property_exists($this, $name) ?  "<input name='$name'      id='$name' type='hidden' />": null;
	    echo property_exists($this, $uid) ?  "<input name='$uid'       id='$uid' type='hidden' />": null;
	    echo property_exists($this, $phone) ?  "<input name='$phone'     id='$phone' type='hidden' />": null;
	    echo "</div>"; // col-sm-8
	    echo "</div>"; // form-group

	    echo "<script>
	           var config = {
	               //API Key [REQUIRED]
	               key: 'esoft;piotr.tajanowicz@kyndryl.com',
	               faces: {
	                   //The handler for clicking a person in the drop-down.
	                   onclick: function(person) {


	                   var intranet = document.forms." . $this->fcFormName . ".elements['$intranet'];
					   if(typeof(intranet) !== 'undefined'){ intranet.value = person['email'];};

	                   var name =  document.forms." . $this->fcFormName . ".elements['$name'];
					   if(typeof(name) !== 'undefined'){ name.value = person['name'];};


	                   var uid = document.forms." . $this->fcFormName . ".elements['$uid'];
					   if(typeof(uid) !== 'undefined'){ uid.value = person['uid'];};

	                   var phone = document.forms." . $this->fcFormName . ".elements['$phone'];
					   if(typeof(phone) !== 'undefined'){ phone.value = person['phone'];};

	                   return person['notes-id'];
	               }
	           }
	       };
	       FacesTypeAhead.init(
	       document.forms." . $this->fcFormName . ".elements['$notesid'],
	       config
            );
    	</script>";

	}





	function formUseridMk1($mode, $title, $fieldName, $state=null, $help=null, $fieldsRequired = 'Intranet'){

	    echo "<div class='form-group'>";
	    echo "<label for='$fieldName' class='col-sm-2 control-label' >$title :</label>";
	    echo "<div class='col-sm-8'>";


		$name = $fieldName . "_NAME";
		$notesid = $fieldName . "_NOTESID";
		$intranet = $fieldName . "_INTRANET";
        $phone = $fieldName . "_PHONE";
        $uid = $fieldName . "_UID";

		if(isset($this->$name)){
			$nameValue = $this->$name;
		} elseif(isset($this->$notesid)){
			$nameValue = $this->$notesid;
		} elseif (isset($this->$intranet)){
			$nameValue = $this->$intranet;
		} else {
			$nameValue = null;
		}

		echo "<label for='$name' class='col-sm-2 control-label' >Name :</label>";
		if ($mode != self::$modeDISPLAY) {
			echo "<div class='" . $fieldName . "Details'>";
			echo "<input id='faces-input$fieldName' name='$name' class='typeahead' size='30'  $state value='" . htmlspecialchars(trim($nameValue),ENT_QUOTES) . "' />";
			echo "      <script>";
			echo "		var config = { key: 'esoft;piotr.tajanowicz@kyndryl.com',";
			echo "			           faces: {";
			echo "			              onclick: function(person) {";
			echo "			     		  document.getElementById('callback-target$fieldName').innerHTML = person['bio'];";
			switch ($fieldsRequired) {
				case self::$bpFieldIntranet:
					echo "document.getElementById('$intranet').value = person['email'];";
					break;
				case self::$bpFieldNotesid:
					echo "document.getElementById('$notesid').value = person['notes-id'];";
					break;
				case self::$bpFieldBothUid:
					echo "var uid = document.getElementById('$uid');";
					echo "if(typeof(uid)==='object'){ uid.value = person['uid'];};";
					case self::$bpFieldBoth:
					echo "document.getElementById('$intranet').value = person['email'];";
					echo "document.getElementById('$notesid').value = person['notes-id'];";

					break;
				default:
					echo "alert('default');";
					;
				break;
			}
			echo "			 	                	return person['name'];";
			echo "		 	                   }";
			echo "	 	               },";
			echo "					   topsearch: {";
			echo "						   enabled: false";
			echo "	 	               }";
			echo "				 };";
			echo "	        FacesTypeAhead.init(";
			echo "				document.getElementById('faces-input$fieldName'),";
			echo " 				config";
			echo "	         );";
			echo "    </script> ";
			echo "</div>";
			echo "<div id='callback-target$fieldName'><B>$help</B>Allow <a href='http://ciolab.ibm.com/misc/typeahead/' target='_blank'>Faces Type-ahead</a> time to work.</div> ";



// 			switch ($fieldsRequired) {
// 				case self::$bpFieldIntranet:
// 					echo "<TR>";
// 					echo "<th style='background-color:#bd6' width=80><B>Email</B></th>";
// 					echo "<td><INPUT size='40' name='" . $intranet . "' id='" . $intranet . "' READONLY value='" . htmlspecialchars(trim($this->$intranet),ENT_QUOTES) . "' /></td>";
// 					echo "</TR>";
// 					echo "<INPUT TYPE='hidden' id='original$intranet' value='" . trim($this->$intranet) . "' />";
// 					break;
// 				case self::$bpFieldNotesid:
// 					echo "<TR>";
// 					echo "<th style='background-color:#bd6' width=80><B>Notesid</B></th>";
// 					echo "<td><INPUT size='40' name='" . $notesid . "' id='" . $notesid . "' READONLY value='" . htmlspecialchars(trim($this->$notesid),ENT_QUOTES) . "' /></td>";
// 					echo "</TR>";
// 					echo "<INPUT TYPE='hidden' id='original$notesid' value='" . trim($this->$notesid) . "' />";
// 					break;
// 				case self::$bpFieldBothUid:
// 					echo "<INPUT TYPE='hidden' name='" . $uid . "' id='$uid' value='" . trim($this->$uid) . "' />";
// 					echo "<INPUT TYPE='hidden' id='original$uid' value='" . trim($this->$uid) . "' />";
// 				case self::$bpFieldBoth:
// 					echo "<TR>";
// 					echo "<th style='background-color:#bd6' width=80><B>Email</B></th>";
// 					echo "<td><INPUT size='40' name='" . $intranet . "' id='" . $intranet . "' READONLY value='" . trim($this->$intranet) . "' /></td>";
// 			//		echo "<INPUT type='hidden' size='40' name='" . $intranet . "' id='" . $intranet . "' READONLY value='" . htmlspecialchars(trim($this->$intranet),ENT_QUOTES) . "' />";
// 					echo "</TR>";
// 					echo "<TR>";
// 					echo "<th style='background-color:#bd6' width=80><B>Notesid</B></th>";
// 					echo "<td><INPUT size='40' name='" . $notesid . "' id='" . $notesid . "' READONLY value='" . htmlspecialchars(trim($this->$notesid),ENT_QUOTES) . "' /></td>";
// 					echo "</TR>";
// 					echo "<INPUT TYPE='hidden' id='original$notesid' value='" . htmlspecialchars(trim($this->$notesid),ENT_QUOTES) . "' />";
// 					echo "<INPUT TYPE='hidden' id='original$intranet' value='" . htmlspecialchars(trim($this->$intranet),ENT_QUOTES) . "' />";
// 					break;
// 				default:
// 					;
// 				break;
// 			}
		} else {
			echo "<INPUT size='40' name='$notesid' id='$notesid' READONLY value=' ". trim($this->$notesid) . "' /></TD></TR>";
			echo "<TR>";
			echo "<th style='background-color:#bd6' width=120><B>Email</B></th>";
			echo "<td><INPUT size='40' name='$intranet' id='$intranet' READONLY value='" . trim($this->$intranet) . "' /></td>";
			echo "</TR>";
			echo "<INPUT TYPE='hidden' id='original$intranet' value='" . trim($this->$intranet) . "' />";

		}
	}

        /**
         * Used to place an "Input type='hidden'" in the form.
         * This can then be used to pass from one form to the next page
         * the "mode" the next page should perform.         *
         *
         * @param string $mode      placed in value element of input statement
         * @param string $state     placed in input statement
         *
         */
        static function nextMode($mode, $state=null){
            echo "<input type='hidden' name='mode' $state value='$mode' >";
        }
        /**
         * Used to place an "Input type='submit'" in the form.
         * This can then be used to pass from one form to the next page
         * the "mode" the next page should perform.         *
         *
         * @param string $mode      placed in value element of input statement
         * @param string $state     placed in input statement
         *
         */
        static function submitMode($mode,$state=null){
            echo "<input type='submit' name='mode' $state value='$mode' >";
        }

        static function setModeVariables($mode){
            $this->currentMode = $mode;
        		$modeVariables['mode'] = $mode;
          	if ($mode == 'Display') {
        		$modeVariables['state'] 		= self::$formReadonly;
        		$modeVariables['chkState'] 		= self::$formDisabled;
        		$modeVariables['notEditable'] 	= self::$formReadonly;
        	} else {
        		$modeVariables['state']			= self::$formNull;
        		$modeVariables['chkState'] 		= self::$formNull;
        		$modeVariables['notEditable'] 	= self::$formNull;
        	}
        	if ($mode == 'edit') {
        		$modeVariables['notEditable'] 	= self::$formReadonly;
        	}
        	return $modeVariables;
        }


        function standardInputFields(){
       		echo "<INPUT type='hidden' name='LAST_UPDATER' value='" . $_SESSION['ssoEmail'] . "' />";
			$now = new \DateTime();
			echo "<INPUT type='hidden' name='LAST_UPDATED' value='" . $now->format('Y-m-d H:i:s') . "' />";

			if(!isset($this->CREATED)){
				echo "<INPUT type='hidden' name='CREATED' value='" . $now->format('Y-m-d H:i:s') . "' />";
				echo "<INPUT type='hidden' name='CREATOR' value='" . $_SESSION['ssoEmail'] . "' />";
			} else {
				echo "<INPUT type='hidden' name='CREATED' value='" . $this->CREATED . "' />";
				echo "<INPUT type='hidden' name='CREATOR' value='" . $this->CREATOR . "' />";
			}

        }

        static function formHeader($formName='myForm', $action='$_SERVER["PHP_SELF"]',$formId=null){
        	$formId = empty($formId) ? $formName : $formId;
        	?><form id='<?=$formId?>' name='<?=$formName;?>' method='POST' action='<?=$action;?>' ><?php
        }

        static function formEnd(){
        	?></form><?php
        }

        static function formToggelableDivisionStart($divisionName='dropSelection', $divisionComment='Unnamed Division', $initialDisplayStyle='block', $border=true, $backgroundColor='#e5e5e5'){
        	echo "<TABLE><TBODY><TR>";
        	echo "<TH><a id='x" . $divisionName . "' href=\"javascript:Toggle('" . $divisionName . "');\" >";
        	$iconName = $initialDisplayStyle=='block' ? 'close' : 'open' ;
        	echo "<img src='../ItdqLib_V1/ui/images/icon-list-" . $iconName . ".gif' width='12' height='12' alt='" . $iconName . " link icon' /></a>";
        	echo "&nbsp;" . $divisionComment . "</TH></TR>";
        	echo "</TBODY></TABLE>";
        	$borderParms = $border ? "padding:7px;border:1px solid;border-radius:5px;background-color:$backgroundColor;overflow-x:scroll" : null;
        	echo "<div id='" . $divisionName . "' style='display:" . $initialDisplayStyle . "; margin-left:4em;$borderParms' >";
        }

        static function formToggelableDivisionEnd(){
        	echo "</DIV>";
        }

        static function formBlueButtons($buttonDetails=null){
        	echo "<scan class='button-blue' style='font-size:1.4em'>";
        	foreach ($buttonDetails as $button){
        		echo "<input type='" . $button['type'] . "' name='" . $button['name'] . "' id='" . $button['id']. "' " . $button['state'] . " value='" . $button['value'] . "'  >";
        	}
        //	echo "<input type='submit' name='btnNewProduct' id='btnNewProduct' enabled value='Define New Product'  >";
        	echo "</scan>";
        }

        static function formButton($type=null,$name=null,$id=null,$state=null,$value=null){
        	$buttonArray = array();
        	$buttonArray['type'] 	= !empty($type) 	? trim($type) 	: null;
        	$buttonArray['name'] 	= !empty($name) 	? trim($name) 	: null;
        	$buttonArray['id'] 		= !empty($id) 		? trim($id) 	: null;
        	$buttonArray['state'] 	= !empty($state) 	? trim($state) 	: null;
        	$buttonArray['value'] 	= !empty($value) 	? trim($value) 	: null;
        	return $buttonArray;
        }


        /**
         * Will produce a number of <INPUT fields in the currect form, using $arrayTo[] as the NAME of the field.
         *
         * Use this when you need to pass an ARRAY of fields FROM one form onto the next form so they get passed to the next page.
         *
         * This method will also return FALSE if $arrayFrom was empty or an "implode" CSV of the values it wrote to the form.
         *
         *
         * @param array $arrayFrom
         * @param array $arrayTo
         * @return Ambigous <boolean, string>
         */
        static function saveFormArray($arrayFrom, $arrayTo=null){
        	$listOfSavedItems = false;
        	$arrayTo = empty($arrayTo) ? $arrayFrom : $arrayTo;
        	if(isset($_REQUEST[$arrayFrom])){
    			foreach ($_REQUEST[$arrayFrom]  as $value){
        			echo "<input type='hidden' name='" . $arrayTo . "[]' value='" . urlencode($value) . "' >";
        			//$savedSomething = true; // We saved something
    			}
    			$listOfSavedItems = implode(",",$_REQUEST[$arrayFrom]);
        	}
   			return $listOfSavedItems;
        }


        static function getRagColour($rag){
        	switch (strtoupper(substr(trim($rag),0,1))) {
        		case 'R':
        			return OldFormClass::$ragRed;
        			break;
        		case 'A':
        			return OldFormClass::$ragAmber;
        			break;
        		case 'G':
        			return OldFormClass::$ragGreen;
        			break;
        		default:
        			return '#fff';
        			;
        		break;
        	}



        }





        function modeSetup($mode){
            if ($mode == OldFormClass::$modeDISPLAY) {
                $this->state = 'READONLY';
                $this->chkState = 'DISABLED';
                $this->notEditable = 'READONLY';
            } else {
                $this->state = null;
                $this->chkState = null;
                $this->notEditable = 'READONLY';
            }
        }

        function openFormDivs($title=null, $headingClass=null){
            ?>
            <div class='panel panel-primary'>
            <div class='panel-heading <?=$headingClass;?>'><?=$title?></div>
            <div class='container'>
            <div class='form-horizontal'>
            <?php
        }

        function closeFormDivs(){
            ?>
            </div>
            </div>
            </div>
            <?php
        }



        function displaySaveReset(){
            ?>
            <div class='form-group'>
            <div class='col-sm-2'>
            </div>
            <div class='btn-group col-sm-8' >
            <button type='submit' class='btn btn-default'>Save</button>
            <button type='reset' class='btn btn-default'>Reset</button>
            </div>
            </div>
            <?php
        }


        static function messageModal($id,$messageId, $title)
        {

        ?>
                <!-- page modals -->
        		<div class="modal fade" id="<?=$id;?>" tabindex="-1"
        			role="dialog" aria-labelledby="myModal" aria-hidden="true">
        			<div class="modal-dialog modal-lg">
        				<div class="modal-content">
        					<div class="modal-header">
        						<button type="button" class="close" data-dismiss="modal"
        							aria-label="Close">
        							<span aria-hidden="true">&times;</span>
        						</button>
        						<h4 class="modal-title"><?=$title?></h4>
        					</div>

        					<div class="modal-body">
        					<div id='<?=$messageId?>'></div>

        					</div>
        					</div>
        					<div class="modal-footer">
        						<span id="confirmButtonOnModal">
        						<button type="button" class="btn btn-primary" data-dismiss="modal">Confirm</button>
        						</span>
        						<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
        					</div>

        				</div>
        			</div>
                <?php
        }

        /*
         * @param char $message
         *            text to display in any tooltip
         * @param char $position
         *            positon of tooltip (top, botton, left, right)
         */

        function setToolTip($message=null, $position='left'){
            $this->toolTip = trim($message);
            $this->toolTipPosition = $position;
        }

        function setOnchange($onChange=null){
            $this->onChange = $onChange;
        }



}
?>