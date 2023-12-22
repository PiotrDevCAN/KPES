<?php
namespace itdq;
/**
 * Builds an Array in Javascript that can be used to to populate the contents of one (Secondary) drop down, based on a selection in another (primary) drop down.
 *
 * Was originally designed to change the contents of the Pool Name Drop Down based on the Competency Selected, on the Resource Request screen
 * but can be used whenever one drop down needs to be set according to the value selected in another. Just the variable names are based on it's initial usage.
 *
 * Sample Usage :
 *
 * <B>CODE FROM THE CALLING PAGE</B> :
 *
 * $allCompetencies = $loader->load('COMPETENCY',AllTables::$RM_TABLE);
 * foreach ($allCompetencies as $competency) {
 *	$pools[$competency] = $loader->load('POOL_NAME',AllTables::$POOLS, " COMPETENCY='$competency' ");
 *	$jrss[$competency] = $loader->load('JRSS_NAME', AllTables::$VALID_JRSS_BY_COMP,  " COMPETENCY='$competency' ");
 *	$rdmIntranet[$competency] = $loader->load('RM_INTRANET', AllTables::$RM_TABLE,  "COMPETENCY='$competency' ");
 *	$rdmNotesid[$competency] = $loader->load('RM_NOTESID', AllTables::$RM_TABLE, "COMPETENCY='$competency' ");
 * }
 * JavaScript::buildSelectArray($pools, 'pools');
 * JavaScript::buildSelectArray($jrss, 'jrss');
 * JavaScript::buildSelectArray($rdmIntranet, 'rdmIntranet');
 * JavaScript::buildSelectArray($rdmNotesid, 'rdmNotesid');
 *
 * So the ARRAY in Parm 1 needs to be a 2 dimentional array, the 1st Dimension being the possible values in the "PRIMARY" drop down.
 * The 2nd Dimension being Arrays of options that will be displayed in the "SECONDARY" drop down, when the Key is selected in the Primary drop down.
 *
 *
 * <B>CODE FROM THE</B> displayForm() that is building the online form with the drop downs
 *
 * 		if(isset($this->COMPETENCY)){
 *			$poolList = $loader->load('POOL_NAME',AllTables::$POOLS," COMPETENCY='$this->COMPETENCY' ");
 *		} else {
 * 			$poolList = array();
 *		}
 *
 *		$this->formSelect ( $allActiveCompetencies, 'Competency', 'COMPETENCY', $chkState, null, 'Select...', 'blue-med-light', $onChange );
 * 		$this->formSelect ( $poolList, 'Pool', 'POOL_NAME', $jrssPoolStatus, null, 'Select...', 'blue-med-light',$onChange, $size, $newRow);
 *
 *
 * <B>JAVASCRIPT ARRAY</B> that acutally populates the Secondary Drop Down.
 *
 * 	var competency 	 = document.getElementById('COMPETENCY');
 *	var poolSelect   = document.getElementById('POOL_NAME');
 *
 * 	poolSelect.options.length=0;
 *	if(competency.selectedIndex > 0 ){
 *		poolSelect.options[0] = new Option('Select pool name....','');
 *		for(i=0; i < pools[selectedCompetency].length; i++ ){
 *			poolSelect.options[poolSelect.options.length] = new Option(pools[selectedCompetency][i],pools[selectedCompetency][i]);
 *		}
 *	}
 *	poolSelect.disabled = false;
 *
 *
 * @author GB001399
 * @package bgdm
 *
 */
class JavaScript {

	/**
	 * Creates a 4 dimensional array, called $arrayName for use by Javascript in autopopulating a Fourth Drop down based on the value selected in a Primary & Secondary Drop Downs.
	 *
	 * @param array $data			4 Dimensional Array
	 * @param string $arrayName		Name of the array when addressed by Javascript.
	 */
	static function buildSelectArrayFour($data, $arrayName){

		echo '<script type="text/javascript" charset="utf-8">';
		echo "var $arrayName = new Array();";
//		echo $arrayName . '[0] = "0";';
//		echo $arrayName . '[1] = new Array();';
//		echo $arrayName . '[1][0] = "1.0";';
//		echo $arrayName . '[1][1] = "1.1";';
//		echo $arrayName . '[0][0]=""; ';
		$i=0;
		foreach($data as $primaryKey => $primaryArray){
			echo $arrayName . '[' .++$i . '] = new Array();';
			echo $arrayName . '[' .$i . '][0]=""; ';
			$j = 0;
			$dropdownString = $arrayName . "[" . $i . "]";
			foreach($primaryArray as $secondaryKey => $secondaryArray){
				echo $arrayName . '[' . $i . '][' .++$j . '] = new Array();';
				echo $arrayName . '[' . $i . '][' .$j . '][0]=""; ';
				$k = 0;
				$dropdownString = $arrayName . '[' . $i . '][' . $j . "]";
				foreach($secondaryArray as $tertiaryKey => $tertiaryArray){
					$dropdownString .= "[" . ++$k . "] = [";
					foreach($tertiaryArray as $value){
						$dropdownString .= ',"' . $value . '"'	;
					}
				}
			}
			$dropdownString .= ']; ';
			echo str_replace("[,","[",$dropdownString);
		}
		echo "</script>";
	}

	/**
	 * Creates a 3 dimensional array, called $arrayName for use by Javascript in autopopulating a Third Drop down based on the value selected in a Primary & Secondary Drop Downs.
	 *
	 * @param array $data			3 Dimensional Array
	 * @param string $arrayName		Name of the array when addressed by Javascript.
	 */
	static function buildSelectArrayThree($data, $arrayName){

		echo '<script type="text/javascript" charset="utf-8">';
		echo "var $arrayName = new Array();";
//		echo $arrayName . '[0] = "0";';
//		echo $arrayName . '[1] = new Array();';
//		echo $arrayName . '[1][0] = "1.0";';
//		echo $arrayName . '[1][1] = "1.1";';
//		echo $arrayName . '[0][0]=""; ';
		$i=0;
		foreach($data as $primaryKey => $primaryArray){
			echo $arrayName . '[' .++$i . '] = new Array();';
			echo $arrayName . '[' .$i . '][0]=""; ';
			$j = 1;
			$dropdownString = $arrayName . "[" . $i . "]";
			foreach($primaryArray as $secondaryKey => $secondaryArray){
				$dropdownString .= "[" . $j++ . "] = [";
				foreach($secondaryArray as $value){
					$dropdownString .= ',"' . $value . '"'	;
				}
			}
			$dropdownString .= ']; ';
			echo str_replace("[,","[",$dropdownString);
		}
		echo "</script>";
	}

	/**
	 * Creates an array, called $arrayName for use by Javascript.
	 *
	 * @param array $data			Array
	 * @param string $arrayName		Name of the array when addressed by Javascript.
	 */
	static function buildArray($data, $arrayName){
	    echo '<script type="text/javascript" charset="utf-8">';
	    echo "var $arrayName = new Array();";
	    echo $arrayName . '[0]=""; ';
	    $i=1;
	    foreach($data as $index => $value){
	        $poolString =  $arrayName . "[" . $i++ . "] = ['" . $value . "'];";
	        echo str_replace("[,","[",$poolString);
	    }
	    echo "</script>";
	    echo "";
	}

	/**
	 * Creates a 2 dimensional array, called $arrayName for use by Javascript in autopopulating a Secondary Drop down based on the value selected in a Primary Drop Down.
	 *
	 * @param array $data			2 Dimensional Array
	 * @param string $arrayName		Name of the array when addressed by Javascript.
	 */
	static function buildSelectArray($data, $arrayName){
	    ?>
		<script type="text/javascript" charset="utf-8">
		var <?=$arrayName?> = new Array();
		<?=$arrayName?>[0]= new Array();
		<?php
		$i=1;
		if (count($data) >0) {
			foreach($data as $competency => $pools){
				$poolString =  $arrayName . "[" . $i++ . "] = [";
				foreach ($pools as $poolName ){
					$poolString .=  ',"' . $poolName . '"';
				}
				$poolString .= ']; ';
				echo str_replace("[,","[",$poolString);
			}

			foreach($data as $competency => $pools){
				echo $arrayName?>[0].push('<?=$competency?>'); <?php
			}
		}
		?>
		</script>
		<?php
	}
	
	static function buildSelectArrayFromLoadIndexed($data, $arrayName){

		echo '<script type="text/javascript" charset="utf-8">';
		echo "var $arrayName = new Array();";
		echo $arrayName . '[0]=""; ';
		$i=1;
		foreach($data as $competency => $pools){
			$poolString =  $arrayName . "[" . $i++ . "] = [";
			$poolString .=  ',"' . $pools . '"';
			$poolString .= ']; ';
			echo str_replace("[,","[",$poolString);
		}
		echo "</script>";
	}

	/**
	 * Creates a 2 dimensional array, called $arrayName for use by Javascript in autopopulating a Secondary Drop down based on the value selected in a Primary Drop Down.
	 *
	 * @param array $data			2 Dimensional Array
	 * @param string $arrayName		Name of the array when addressed by Javascript.
	 */
	static function buildSelectArrayFromLoadIndexedPair($data, $arrayKey, $arrayValues){

		echo '<script type="text/javascript" charset="utf-8">';
		echo "var $arrayKey = new Array();";
		echo $arrayKey . '[0]=""; ';
		$i=1;
		foreach($data as $key => $value){
			$poolKString =  $arrayKey . "[" . $i++ . "] = [";
			$poolKString .=  ',"' . $key . '"';
			$poolKString .= ']; ';
			echo str_replace("[,","[",$poolKString);
		}

		echo "var $arrayValues = new Array();";
		echo $arrayValues . '[0]=""; ';
		$i=1;
		foreach($data as $key => $value){
			$poolVString =  $arrayValues . "[" . $i++ . "] = [";
			$poolVString .=  ',"' . $value . '"';
			$poolVString .= ']; ';

			echo str_replace("[,","[",$poolVString);
		}
		echo "</script>";
	}

	static function buildObjectFromLoadIndexedPair($data, $objectName){
	    ?><script type="text/javascript" charset="utf-8">

	    var <?=$objectName?> = {};
	    <?php
	    foreach ($data as $key => $value) {
	        $string = $objectName . "['" . trim($key) . "']='" . trim($value) . "';
";
	        echo $string;
	    }
	    ?>
	    </script><?php
	}

	static function buildArrayOfObjectsFromArrayOfRows($data, $arrayName, $objectName){
	    ?><script type="text/javascript" charset="utf-8">

	    var <?=$objectName?> = {};
	    <?php
	    foreach ($data as $key => $value) {
	        $string = $objectName . "['" . trim($key) . "']='" . trim($value) . "';
";
	        echo $string;
	    }
	    ?>
	    console.log(<?=$objectName?>);
	    </script><?php
	}
}?>