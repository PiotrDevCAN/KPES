<?php
namespace itdq;
use DateTime;
use itdq\FormClass;
/**
 * Class to deal manipulate data that has come from a DB2 Records.
 *
 * This class does NOT know how to read/write data to/from a DB2 Table, you need DTable to do that.
 * It simply lets you manipulate the data once it's been read from DB2 (or prior to it being written to DB2)
 *
 * When defining Class Properties in the Classes descended from this class follow these rules :
 * <ul>
 * <li> Class Properties that map to DB2 Columns are defined in UPPERCASE.
 * <li> They can contain the characters in $removable, as they will be translated out whenever it needs the actual DB2 Column name.
 * </ul>
 * If the class needs other properties - define them in lower or MiXiD case and they won't be treated as DB2 Columns.
 *
 * @author GB001399
 * @package ITDQLib
 *
 */
class DbRecord extends FormClass {

	protected $ignoreProperties;
	protected $autoTruncate;
	protected $keyColumns = array ();
	protected static $uniqueKeyCounter = 0;

	private static $removeAble = array ("&", " /", " ", "_EC2" );
	private static $replaceWith = array (null, null, "_", null );
	private static $dateFormats = array ('yyyy-mm-dd hh:ii:ss', 'dd/mm/yy hh:ii:ss', 'dd/mm/yyyy hh:ii:ss' );
	private static $mandatoryFields = null;

//	public static $mandatoryFields = array();

	function __construct($pwd=null) {
		Trace::traceComment(null,__METHOD__);
		$this->fcFormName = 'DbRecord';
		$this->state = 'Undefined';
		$this->pwd = $pwd;
		$this->autoTruncate = FALSE;
	}

	/**
	 * Populate the properties of the Class from an Array.
	 *
	 * Pass an array and it will step through, taking the Key and populating a Property of that name with the Value.
	 *
	 * @param array $record
	 */
	function setFromArray($record) {
		Trace::traceComment(null,__METHOD__);
		if (! empty ( $record )) {
			foreach ( $record as $key => $value ) {
				if(!empty($key) && property_exists($this, $key)){
					$this->$key = $value;
				}
			}
		}
	}

	/**
	 * Create an Array from the Properties of the Record.
	 *
	 * @param array $record
	 */
	function setToArray() {
		Trace::traceComment(null,__METHOD__);
		$array = false;
		foreach ( $this as $key => $value ) {
			if($key==strtoupper($key)){
				$array[$key] = $value;
			}
		}
		return $array;
	}




	/**
	 * Populate the properties of the Class from an Array.
	 * 	 *
	 * Pass an array and it will step through, it's own properties, only populating those that appear in the data passed in to the function.
	 *
	 * @param array $record
	 */
	function recordDrivenSetFromArray($record) {
		Trace::traceComment(null,__METHOD__);
		$someDataFound = false;
		if (! empty ( $record )) {
			foreach ( $this as $key => $value ) {				// Step through my own Properties
				if(isset($record[$key])){						// Does this entry exist in the record passed in ?
					if(!empty($record[$key])){					// If we have a Value in the incoming array
						$this->$key = $record[$key];			// Then that's the value we need.
						$someDataFound = true;
					}
				}
			}
		}
		return $someDataFound;
	}

	function initialise(){
		foreach ($this as $key => $value) {
			if($key==strtoupper($key)){
				unset($this->$key);
			}
		}
	}


	/**
	 * Will return an array where KEY is the Property Name and VALUE it's value.
	 *
	 * @return array
	 */
	function getValues() {
		Trace::traceComment(null,__METHOD__);
		foreach ( $this as $key => $value ) {
			$values [] = $value;
		}
		return $values;
	}

	/**
	 * Will return an array holding the values from the $this->$keyColumns fields - for use in prepared SQL statements.
	 *
	 * @return array
	 */
	function getKeyValues() {
		Trace::traceComment(null,__METHOD__);
		foreach ( $this->keyColumns as $key ) {
			$values [] = $this->$key;
		}
		return $values;
	}


	/**
	 * Will return a CSV list of all the values.
	 *
	 * @return array
	 */
	function getValuesForCsv() {
		$string = null;
		Trace::traceComment(null,__METHOD__);
		foreach ( $this as $key => $value ) {
			$string .= $value .",";
		}
		return substr($string,0,strlen($string)-1);
	}

	/**
	 * Will return a CSV list of all the KEYs.
	 *
	 * @return array
	 */
	function getKeyForCsv() {
		$string = null;
		Trace::traceComment(null,__METHOD__);
		foreach ( $this as $key => $value ) {
			$string .= $key .",";
		}
		return substr($string,0,strlen($string)-1);
	}

	/**
	 * Will return the value of a specific named Column.
	 *
	 * @param unknown_type $column
	 * @return string|NULL
	 */
	function getValue($column) {
		Trace::traceComment(null,__METHOD__);
		if (isset ( $this->$column )) {
			$trace = "Column:$column" . " Value:" . trim ( $this->$column );
			Trace::traceComment($trace,__METHOD__);
			return trim ( $this->$column );
		} else {
			return null;
		}
	}

	/**
	 * Similar to getValues()
	 * Only it only populates and returns Class Properties that are defined in UPPERCASE
	 *
	 * @return array
	 */
	function getAssoc() {
		Trace::traceComment(null,__METHOD__);
		foreach ( $this as $key => $value ) {
			if ($key == strtoupper ( $key )) { // only return Properties defined in UpperCase - to distinguish columns in the data from other properties.
				$assoc [$key] = $value;
			}
		}
		return $assoc;

	}
	/**
	 * Returns an Array of all the Properties that are in the Array passed into the function.
	 * Will call toColumns to translate $columns from a PHP Class Property to a DB2 Column Name.
	 *
	 * @param array $columns  	A list of column names you want populating with values.
	 * @return string|boolean	Array of data or False if none of the columns
	 */
	function getValidColumns($columns) {
		Trace::traceComment(null,__METHOD__);
		foreach ( $columns as $key => $value ) {
			if (property_exists ( $this, self::toColumnName ( $value ) )) {
				$valid [$value] = $value;
			}
		}
		if (! isset ( $valid )) {
			return FALSE;
		} else {
			return $valid;
		}
	}

	/**
	 * If the Class Property is defined in UPPERCASE and it has a value, then it will be return in an array.
	 *
	 * @return array
	 */
	function getNonNullColumns() {
		Trace::traceComment(null,__METHOD__);
		foreach ( $this as $key => $value ) {
			if ($key == strtoupper ( $key ) && isset ( $this->$key )) { // Check it's a property that is also a column - else we're not interested.
				if ($this->$key != null) {
					$columns [$key] = trim ( $value );
				}
			}
		}
		return $columns;
	}

	/**
	 * More flexible function for getting data out of the class via an Array.
	 *
	 * Returns an Array that contains the names (and values) of all the Properties that are Columns in the database.
	 * Expects $keyColumns to define the Class Properties that are the Keys in the table.
	 *
	 * @param boolean $populated =TRUE - then only returns Columns with a non-null value in them.
	 * @param boolean $keyWanted =TRUE - then returns the field even if it does not have a value (useful for building UPDATE statements for DB2, where you need to know the Key Fields)
	 * @param boolean $null =TRUE - then returns the string value of 'null' if the column has a null value. (useful for building DB2 predicates)
	 * @return array
	 *
	 */
	function getColumns($populated = false, $keyWanted = true, $null = true, $db2=false) {
		Trace::traceComment(null,__METHOD__,__LINE__);
		set_time_limit(120);
		$columns = null;
		foreach ( $this as $key => $val ) {
			if($db2){
				$value = htmlspecialchars($val);
			} else {
				$value = $val;
			}
			if ($key == strtoupper ( $key )) { // only return Properties defined in UpperCase - to distinguish columns in the data from other properties.
				if ($populated && strlen ( $value ) > 0 && ((! $keyWanted && ! in_array ( $key, self::$keyColumns )) or ($keyWanted))) { // they only want fields with values - and this has a value.
					$columns [$key] = $value;
				} elseif (! $populated && ((!$keyWanted && !in_array ( $key, self::$keyColumns )) or ($keyWanted))) { // They are not bothered if it has a value or not
					if (empty ( $value )) {
						if ($null == true) {
							$columns [$key] = 'null';
						}
					} else {
						$columns [$key] = $value;
					}
				} else {
					$columns [$key] = '';   // Removed 20130822 - As having problems with empty columns in REMIND
											// Re-added 20130823 - Problems with eSoft as it wasn't able to clear fields down.
				}
			}
		}
		Trace::traceVariable($columns,__METHOD__,__LINE__);
		return $columns;
	}

	/**
	 * Compares this Object with another object of type DBRecord passed in as a a parameter.
	 *
	 * Only compares those CLass Properties defined in uppercase - ie those that map directly to Columns in DB2
	 *
	 * Returning EITHER the values from this object or from the Object passed in - depending on the value of $newValues
	 *
	 * Useful for logging the changes a user may have made on a screen. To do that :
	 * <ul>
	 * <li> Load the original Record from the DB
	 * <li> CLONE it to a 'new' object
	 * <li> call Compare() on the Original, passing the new.
	 * <li> An array of changes will be returned from the call to compare()
	 * </ul>
	 *
	 * @param DbRecord $otherRecord	The object to be compared to - should be of the same type as this object.
	 * @param boolean $newValues	Do you want the values from the 'other' object returned instead of those from this object ?
	 * @return array 			    Those Class Properties that don't match are returned here.
	 */
	function compare(DbRecord $otherRecord, $newValues = false) {
		Trace::traceComment(null,__METHOD__);
		$differences = Array ();
		$otherAssoc = $otherRecord->getAssoc ();
		foreach ( $this as $key => $value ) {
			if ($key == strtoupper ( $key ) && (isset ( $this->$key ) or isset ( $otherAssoc [$key] ))) { // Check it's a property that is also a column - else we're not interested.
				if (trim ( $value ) != trim ( $otherAssoc [$key] )) {
					if ($newValues) {
						$differences [$key] = $value;
					} else {
						$differences [$key] = $otherAssoc [$key];
					}
				}
			}
		}
		return $differences;
	}

	/**
	 * Builds a Link to a NOTES Document from the <XML> that is cut&pasted into a form when you paste a Notes DB Link.
	 * <B>Note</B>: Only Firefox seems to support pasting Notes Links into Forms. NEither IE nor Notes itself parse the data to the HTML.
	 *
	 * I worked out how to convert what is "cut&pasted" to the URL manually, so it could change in future releases of Notes.
	 *
	 * @param string $link
	 * @return string|NULL
	 */
	function buildLink($link) {
		Trace::traceComment(null,__METHOD__);
		if (! empty ( $link )) {
			$hint = strpos ( $link, "<HINT>CN=" );
			$replica = strpos ( $link, "<REPLICA " );

			$partA = substr ( $link, $hint + 9, 8 );
			$partB = substr ( $link, $replica + 9, 8 );
			$partC = substr ( $link, $replica + 18, 8 );

			$url = "Notes://" . $partA . "/" . $partB . $partC . "/";

			$view = strpos ( $link, "<VIEW OF" );
			if ($view > 0) {
				$partD = substr ( $link, $view + 8, 8 );
				$partE = substr ( $link, $view + 17, 8 );
				$partF = substr ( $link, $view + 28, 8 );
				$partG = substr ( $link, $view + 37, 8 );
				$url .= $partD . $partE . $partF . $partG . "/";
				$note = strpos ( $link, "<NOTE OF" );
				if ($note > 0) {
					$partH = substr ( $link, $note + 8, 8 );
					$partI = substr ( $link, $note + 17, 8 );
					$partJ = substr ( $link, $note + 28, 8 );
					$partK = substr ( $link, $note + 37, 8 );
					$url .= $partH . $partI . $partJ . $partK;
				}
			}
			return $url;
		} else {
			return null;
		}
	}

	/**
	 * Translates a Class Property to DB2 Column Name using self::$removable and self:$replaceWith.
	 * Called from getSelect()
	 *
	 * @param string $col
	 * @return mixed
	 */
	static function toColumnName($col) {
		return str_replace ( self::$removeAble, self::$replaceWith, strtoupper ( trim ( $col ) ) );
	}

	/**
	 * Steps through the class, building a DB2 SELECT statement from the Variables that are defined in UPPERCASE.
	 * Note sure why it's in FORM Class - should it not be in DBRecord ???
	 *
	 * @return mixed
	 */
	function getSelect() {
		Trace::traceComment(null,__METHOD__);
		$select = " SELECT ";
		foreach ( $this as $key => $value ) {
			if ($key == strtoupper ( $key )) {
				$colName = self::toColumnName ( $key );
				if (! strpos ( $key, "_EC2" )) {
					$select .= ", $colName AS $colName";
				} else {
					$select .= ", DECRYPT_CHAR($colName,'password') AS $colName";
				}
			}
		}
		Trace::traceVariable($select,__METHOD__);
		return str_replace ( "SELECT ,", " SELECT ", $select );
	}

	/**
	 * Returns the number of variables defined to the CLASS in UPPERCASE, used to check we're not trying to SORT on column 5 when the table only has 3 columns.
	 * Why is it in FORM CLASS ??
	 *
	 * @return number
	 */
	function getNoOfCols() {
		$noOfCols = 0;
		foreach ( $this as $key => $value ) {
			if ($key == strtoupper ( $key )) {
				$noOfCols ++;
			}
		}
		return $noOfCols;
	}

	function validateForTable(DbTable $table) {
		Trace::traceComment(null,__METHOD__);
		$valid = $this->validateMandatoryFields ();
		if ($valid) {
			foreach ( $this as $key => $value ) {
				// echo "<BR/>" . __METHOD__ . " Key:$key Value:$value";
				if ($key == strtoupper ( $key )) {
					$colName = self::toColumnName ( trim($key) );
					if (strlen ( trim($value) ) > $table->getColumnLength ( $colName )) {
						if($this->autoTruncate){
							$this->handleAutoTruncate($key, $value, $table, $colName);
							//echo "<BR/><B>Field: $key truncated from " . strlen($value) . " to " . $table->getColumnLength ( $colName ) . " characters.</B><BR/>";
							//$value = substr(trim($value),0,$table->getColumnLength ( $colName ));
							//$this->$key = trim($value);
						} else {
							$valid = FALSE;
							echo "<BR/>Field: $key Value:'$value'  length " . strlen ( trim($value) ) . " exceeds max length of " . $table->getColumnLength ( $colName ) . " for column $colname and Autotruncate is off.<BR/>";
						}
			//		} else {
					}
					$type = $table->getColumnType ( $colName );
					$nullable = $table->getNullable ( $colName );
					$defaultValue = $table->getColumnDef ( $colName );
					if (empty ( $value ) && $nullable) {
						$valid = TRUE;
					} elseif (empty ( $value ) && $table->isSpecialColumn ( $colName )) {
						$valid = TRUE;
					} elseif (empty ( $value ) && ! $nullable && empty ( $defaultValue )) {
						echo "<BR/>Empty but NOT nullable, NOT Special and No Default $type : $key : $value Default: $defaultValue";
						$valid = FALSE;
					} else {
						// echo "<BR/> Checking Type:" . $type . " Key:" . $key . " Value:" . $value;
						switch ($type) {
							case 'VARCHAR' :
							case 'CHAR' :
							case 'VARCHAR () FOR BIT DATA':
								if (empty ( $value ) && $defaultValue != 'NULL') {
									$valid = TRUE;
								} elseif (! is_string ( $value )) {
									echo "<BR/>Field: $key Value:$value String Expected. Actual:" . var_dump ( $value );
									$valid = FALSE;
								}
								break;
							case 'DECIMAL':
							case 'INTEGER':
							case 'NUMERIC' :
							case 'BIGINT':
								if (empty ( $value ) && $defaultValue != 'NULL') {
									$valid = TRUE;
								} elseif (! is_numeric ( $value )) {
									echo "<BR/>Field: $key Value:$value Numeric Expected. Actual:" . var_dump ( $value );
									$valid = FALSE;
								}
								break;
							case 'TIMESTAMP' :
								if(strtoupper($value)=='CURRENT_DATE'){
									$now = new \DateTime();
									$this->$key = $now->format('Y-m-d H:i:s');
								} elseif (empty ( $value ) && $defaultValue != 'NULL') {
									$valid = TRUE;
								} else {
									$valid = FALSE;
									foreach ( self::$dateFormats as $dateFormat ) {
										//echo "<BR/>Will check $dateFormat";
										$valid = $this->interpretDateTime ( $value, $dateFormat );
										//echo "<BR/> Intrepret returned $valid";
										if ($valid) {
											//echo "<BR/> valid is true";
											$this->$key = $valid;
											$valid = TRUE;
											break;
										}
									}
								}
								break;
							case 'DATE':
								if (empty ( $value ) && $defaultValue != 'NULL') {
									$valid = TRUE;
								} else {
									$valid = FALSE;
									foreach ( self::$dateFormats as $dateFormat ) {
										$formatSection = explode ( " ", trim ( $dateFormat ) );
										$formatDate = $formatSection [0];
										// echo "<BR/>Checking $value against $formatDate";
										$validDate = $this->interpretDate ( $value, $formatDate );
										//echo "<BR/> Intrepret returned $valid";
										if ($validDate) {
											//echo "<BR/> valid is true";
											$this->$key = $validDate;
											$valid = TRUE;
											break;
										}
									}
								}
								break;
							default :
								echo "<BR/>Need to code a check for $type";
								$valid = FALSE;
						}
						if ($valid) {
							// echo "<BR/>" . __METHOD__ . __LINE__  . "Key $key Value $value ";
							$valid = $this->validateField ( $key, $value );
							//echo " : ValidateField returned ";
							//var_dump ( $valid );
						} else {
							echo "<H2>Validation Failed</H2>";
						}
//						}
					}
				}
				if (! $valid) {
					// Hit one invalid field and we can stop checking
					break;
				}
			}
			if (! $valid) {
				echo "<H3>$key : $value : Failed validation</H3>";
			}
		} else {
			echo "<BR/>Not all mandatory fields are present.";
		}
//		echo "<BR/>Will return ";
//		echo var_dump ( $valid );
		return $valid;
	}

	/**
	 * Used by loadFromCsv to translate a date from the CSV to the format required in DB2.
	 *
	 * Picks up expected date format from 'date_format' field in HTML Form, then uses that format to convert the date from the CSV to the format required by DB2 on the iSeries
	 *
	 * @param string $value		The date value from the CSV
	 * @return string|NULL		A string containing the date in month/day/year format for DB2 on iSeries.
	 */
	function interpretDateTime($value, $format) {
		Trace::traceComment(null,__METHOD__);
		Trace::traceVariable($value,__METHOD__);
		Trace::traceVariable($format,__METHOD__);
		$dateSection = explode ( " ", trim ( $value ) );
		$formatSection = explode ( " ", trim ( $format ) );
		// print_r($dateSection);
		$date = $dateSection [0];
		$formatDate = $formatSection [0];
		$validDate = $this->interpretDate ( $date, $formatDate );

		if(isset($dateSection[1])){
			// If they passed a TIME then validate it
			$time = $dateSection [1];
			$formatTime = $formatSection [1];
			$validTime = $this->interpretTime ( $time, $formatTime );
			$timeValue = $validTime;
		} else {
			// Else just say it's valid.
			$validTime = TRUE;
			$timeValue = '00:00:00';
		}

		if (! $validDate) {
			return FALSE;
		} elseif (! $validTime) {
			return FALSE;
		} else {
			// echo "<br/>interpretDateTime will return $validDate $timeValue";
			if(!empty($timeValue)){
				return $validDate . " " . $timeValue;
			} else {
				return $validDate;
			}
		}
	}

	function interpretDate($value, $format) {
		Trace::traceComment(null,__METHOD__);
		Trace::traceVariable($value,__METHOD__);
		Trace::traceVariable($format,__METHOD__);
		$dd = strpos ( $format, 'dd' );
		$mm = strpos ( $format, 'mm' );
		$y4 = strpos ( $format, 'yyyy' );
		$y2 = strpos ( $format, 'yy' );
		$tt = strpos ($format, 'bf');

		// echo "<BR/>Y4:$y4 Y2:$y2 DD:$dd MM:$mm TT:$tt";

		if (!is_int($y4)) {
			$year = trim ( substr ( $value, $y2, 2 ) );
		} else {
			$year = trim ( substr ( $value, $y4, 4 ) );
		}

		if (strLen ( $year ) == 2) {
			$year = "20" . $year;
		}

		$day = substr ( $value, $dd, 2 );
		$mon = substr ( $value, $mm, 2 );

		//echo "<BR/>Checking : Day $day Month $mon Year $year";


		if (strlen ( trim ( $value ) ) != strlen ( trim ( $format ) )) {
			// They've passed us a string that is a different length to the $format, so it can't be in this format.
			// echo "<BR/>$value $format Date & Format are different lengths Date:" . strlen(trim($value)) . " Format:" . strlen(trim($format));
			return FALSE;
		} elseif (! is_numeric ( $day ) or ! is_numeric ( $mon ) or ! is_numeric ( $year )) {
			// We've stripped out day,mon,year - but they are not all numeric values, so it can't be in this format.
			// echo "<BR/>$value $format Not all values are Numeric";
			return FALSE;
		} elseif (checkdate ( $mon, $day, $year )) {
			// It's passsed the checkdate test,so return the date in the format iSeries needs.
			// echo "<BR/>$value $format - Validated.";
			return $year . "-" . $mon . "-" . $day;
		} else {
			// Failed the checkdate test, so return FALSE
			echo "<BR/>Checking : Day $day Month $mon Year $year";
			echo "<BR/>Checkdate returned False";
			return FALSE;
		}
	}

	function interpretTime($value, $format) {
		Trace::traceComment(null,__METHOD__);
		Trace::traceVariable($value,__METHOD__);
		Trace::traceVariable($format,__METHOD__);
		$hh = strpos ( $format, 'hh' );
		$ii = strpos ( $format, 'ii' );
		$ss = strpos ( $format, 'ss' );

		$hour = substr ( $value, $hh, 2 );
		$min = substr ( $value, $ii, 2 );
		$sec = substr ( $value, $ss, 2 );

		// Hour could be missing the leading zero, in which case we'll have picked up the seperator char, Hour won't be numeric.
		if (! is_numeric ( $hour )) {
			$hour = substr ( $value, $hh, 1 );
			$min = substr ( $value, $ii - 1, 2 );
			$sec = substr ( $value, $ss - 1, 2 );
		}
		if (! is_numeric ( $hour )) {
			return FALSE;
		} elseif (! is_numeric ( $min )) {
			return FALSE;
		} elseif (! is_numeric ( $sec )) {
			return FALSE;
		} elseif ($hour < 00 or $hour > 24) {
			return FALSE;
		} elseif ($min < 00 or $min > 60) {
			return FALSE;
		} elseif (($sec < 00 or $sec > 60)) {
			return FALSE;
		} else {
			return $hour . ":" . $min . ":" . $sec;
		}
	}

	function validateMandatoryFields($mandatoryFields = null){
		Trace::traceComment(null,__METHOD__);
		$allMandatoryFieldsPresent = TRUE;

		if(empty($mandatoryFields)){
			return TRUE;
		}
		foreach($mandatoryFields as $fieldName){
			if(is_array($fieldName)){
				$oneFieldPresent = FALSE;
				foreach($fieldName as $groupedField){
					//echo "<BR/>Group Field" . $groupedField . ":" . $this->$groupedField;
					if(!empty($this->$groupedField)){
						$oneFieldPresent = TRUE;
					}
				}
				if(!$oneFieldPresent){
					echo "<H2>Group Field" . $groupedField . ":" . $this->$groupedField . " No fields are present.<H2/>";
					$allMandatoryFieldsPresent = FALSE;
				}
			} else {
				// echo "<BR/>Mandatory Field $fieldName" . ":" . $this->$fieldName;
				if(empty($this->$fieldName)){
					echo "<H3>Mandatory Field $fieldName not present.</H3>";
					$allMandatoryFieldsPresent = FALSE;
				}
			}
		}
//		echo "<BR/>Mandatory check will return ";
//		var_dump($allMandatoryFieldsPresent);
		return $allMandatoryFieldsPresent;
	}

	function validateField($columnName, $columnValue) {
		return TRUE;
	}

	/*
	 *  Allows you to populate the PROPERTIES of the class.
	 *  Allows for "generate always" PROPERTIES, by ignoring the first '$this->ignoreProperties' so many properties
	 *  Only populates the properties that are defined with upper case names ie FIELD_1 not Field_1
	 *
	 *  so a class like this :
	 *
	 *  property $recordIdSetByDB2;
	 *  property $recordDataItem1;
	 *  property $recordDataItem2;
	 *
	 *  would have
	 *
	 *  property $ignoreProperties=1; // Causing it NOT to attempt to set $this->recordIdSetByDB2
	 *
	 *  and code would call reset method as follows :
	 *
	 *  $myObject->reset('Data for Data Item1','Data for Data Item 2');
	 *
	 *
	 */
	function reset(){
		$parm = 0; // Track through the parms passed to the function.
		$property = 0; // Track through the properties in $this.      Added 2013 09 06 - to try to get this method to work right.
		foreach ($this as $key => $value) {
			if(     $property++ >= $this->ignoreProperties 		// Step through the first $ignoreProperties without populating them from the func_get_args
				and $key == strtoupper($key) 					// Check it is a property that ties up with a DB2 COLUMN
				and $parm < func_num_args()){					// Check we've not processed ALL the parameters passed into the function.

				// 2013 09 05 changed $parm < func_num_args() to $parm <= func_num_args() as didn't seem to process the LAST arguement passed in.

			//	echo "<BR/>" . __METHOD__ ;
			//	print_r(func_get_args());
			//	echo "<BR/>" . __METHOD__ . " : Key:$key, Ignore: $this->ignoreProperties Property $property Parm $parm Parameter : " . func_get_arg($parm);

				$this->$key = trim(func_get_arg($parm++));  // Use this parm, and increment the parm tracker.
			} elseif($key == strtoupper($key)) {
				$this->$key = null;
			}
		}
	}

	function forceKeyFieldsNotNull(){
		foreach ($this->keyColumns as $keyField){
			$this->$keyField = empty($this->$keyField) ? "...." : trim($this->$keyField);
		}
	}

	function handleAutoTruncate($key, $value, $table,  $colName){
		echo "<BR/><B>Field: $key truncated from " . strlen($value) . " to " . $table->getColumnLength ( $colName ) . " characters.</B><BR/>";
		$value = substr(trim($value),0,$table->getColumnLength ( $colName ));
		$this->$key = trim($value);
		echo "<BR/><B>Field: $key is now :.</B><BR/>";
		var_dump($this->$key);
	}

	function populateFromProductXml($product){
		foreach ($this->pcasFields as $property => $xmlTag){
			$valueFromXml = PcasReader::getXmlValue($product,null,$xmlTag);
			$value = in_array($property, $this->pcasDateFields) ? PcasReader::convertPcasDate($valueFromXml) : trim($valueFromXml);
			$this->$property = $value;
		}
	}

	function populateFromObject($object,$addNewProperties=false){
        $drivingObject = $addNewProperties ? $object : $this;
	    foreach ($drivingObject as $key =>  $value) {
	        $this->$key = isset($object->$key) ? $object->$key : null;
	    }
	}

	function htmlHeaderCells(){
	    ob_start();
	    $headerCells = null;
	    foreach ($this  as $property => $value) {
	        if($property == strtoupper($property)){
	            $headerCells .= "<th>";
	            $headerCells .= isset($this->headerTitles[$property]) ? $this->headerTitles[$property] : str_replace("_", " ", $property);
	            $headerCells .= "</th>";
	        }
	    }
	    return $headerCells;
	}


}



?>
