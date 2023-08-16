<?php
namespace itdq;

use itdq\IBM\BluePagesSLAPHAPISingle;

/*
 *  Handles Blue Pages.
 */
class BluePagesSLAPHAPI {
	
	static public $notFound = 'NOT FOUND';
	static public $notFoundInBP = 'No Bluepages record';

	// Syntax
	// http://SLAPHAPI_URI[Namespace:]Object/Filter[.Verb[,SearchParameters]][/Adverb][?AttributeList]

	static protected $slaphapiUrl = "https://bluepages.ibm.com/BpHttpApisv3/slaphapi";
	static protected $wsapiUrl = "https://bluepages.ibm.com/BpHttpApisv3/wsapi";

	// https://w3.ibm.com/w3publisher/enterprise-directory-community/schema
	// Schema
	// Object Class                       Description

    // GMMetaData                         Metadata for BlueGroups
    // groupOfUniqueNames                 Member data (unuqfor BlueGroups.
    // ibmCC 
    // ibmCCDesc 
    // ibmCompany                         Represents an HR Company
    // ibmEmployeeType                    Represents an employee type
    // ibmLocationCity 
    // ibmOrganization                    Represents an HR organization
    // ibmPerson                          Represents a Person in Bluepages
    // ibmPersonnelSystem                 Represents a personnel system
    // ibmVenture                         Represents a venture in IBM
    // ibmWorkLocation                    Custom - Blue Pages ObjectClass

	static private $objectGMMetaData = "GMMetaData";
    static private $objectGroupOfUniqueNames = "groupOfUniqueNames";
    static private $objectIbmCC = "ibmCC";
    static private $objectIbmCCDesc = "ibmCCDesc";
    static private $objectIbmCompany = "ibmCompany";
	static private $objectIbmEmployeeType = "ibmEmployeeType";
	static private $objectIbmLocationCity = "ibmLocationCity"; 
    static private $objectIbmOrganization = "ibmOrganization";
	static private $objectIbmPerson = "ibmperson";
	static private $objectIbmPersonnelSystem = "ibmPersonnelSystem";
	static private $objectIbmVenture = "ibmVenture";
	static private $objectIbmWorkLocation = "ibmWorkLocation";
	

	// Filter
	// (mail=balibora@us.ibm.com)

	// Verb (i.e., function) is an optional parameter indicating what action to be performed for the search. 
	// It can be either search, list, or count. If omitted, the default is search. list and search are synonymous. 
	
	static private $verbSearch 	 = "search";
	static private $verbList	 = "list"; 
	static private $verbCount 	 = "count";

	// SearchParameters
	// some additional parameters, can be omitted

	// Result Formats
	// SLAPHAPI can provide directory data in five formats: LDIF, TEXT, DSML, JSON, and HTML.
	
	static private $adverbByxml  = "byxml"; // byxml or bydsml for DSML (Directory Services Markup Language), 
	static private $adverbByLdif = "byldif"; // byldif for LDIF (RFC 2849 compliant),
	static private $adverbByText = "bytext"; // bytext for LDIF-like format, 
	static private $adverbByjson = "byjson"; // byjson for JSON (JavaScript Object Notation), and 
	static private $adverbByhtml = "byhtml"; // byhtml for HTML. 
	
    static public  $countryCodeMapping = array(
        '709' => '744',
        '728' => '740',
        '755' => '756'
    );
	
	static public  $countryCodeMappingOpposite = array(
        '744' => '709',
        '740' => '728',
        '756' => '755'
    );

	static private $oceanDataReturn = array(
		'ibm_cnum',
		'ibm_mail_id',
		'ibm_notes',
		'trans_mail_id',
		'trans_notes',
		'trans_serial',
		'kyndryl_mail_id',
		'country_code',
		'emp_type',
		'first_name',
		'last_name'
	);

	static function checkIfIsOceanId($id){
		$sp = strpos(strtolower($id),'ocean');
		return $sp;
	}

	static function cleanupNotesid($notesId){
		$sp = self::checkIfIsOceanId($notesId);
		if($sp === FALSE){
			return self::cleanupIBMNotesid($notesId);
		} else {
			return self::cleanupOceanNotesid($notesId);
		}
	}

	static function cleanupIBMNotesid($notesid){
		$stepOne =  str_ireplace('CN=','',str_replace('OU=','',str_replace('O=','',$notesid)));
		$location = strpos($stepOne,'@IBM');
		$cleanId = substr($stepOne,0,$location);
		return $cleanId;
	}

	static function cleanupOceanNotesid($notesid){
		$stepOne =  str_ireplace('CN=','',str_replace('OU=','',str_replace('O=','',$notesid)));
		$location = strpos($stepOne,'@Ocean');
		$cleanId = substr($stepOne,0,$location);
		return $cleanId;
	}

	static function buildNotesId($notesId){
		$sp = self::checkIfIsOceanId($notesId);
		if($sp === FALSE){
			return self::buildIBMNotesId($notesId);
		} else {
			return self::buildOceanNotesId($notesId);
		}
	}

	static function buildIBMNotesId($notesId){
		$amendIbm = str_replace("/IBM","xxxxx",$notesId);
		$amendCC  = str_replace("/","/OU=",$amendIbm);
		$amendIbm2 = str_replace("xxxxx","/O=IBM",$amendCC);
		$amendIbm2 = "CN%3D" . urlencode($amendIbm2);
		return $amendIbm2;
	}

	static function buildOceanNotesId($notesId){
		$amendIbm = str_replace("/OCEAN","xxxxx",$notesId);
		$amendCC  = str_replace("/","/OU=",$amendIbm);
		$amendIbm2 = str_replace("xxxxx","/O=OCEAN",$amendCC);
		$amendIbm2 = "CN%3D" . urlencode($amendIbm2);
		return $amendIbm2;
	}

	static function getNotesidFromIntranetId($intranetId){
		$intranetId = strtoupper(trim($intranetId));
		if(empty($intranetId)){
			return FALSE;
		}
		set_time_limit(120);
		$url = self::$wsapiUrl . "?byInternetAddr=INTRANET_ID_HERE";
		// echo "<BR/>" . str_replace('INTRANET_ID_HERE',urlencode($intranetId),$url);
		$ch = curl_init ( str_replace('INTRANET_ID_HERE',urlencode($intranetId),$url) );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );

		$m = curl_exec ( $ch );

		$pattern = "/# rc/";
		$results = preg_split ( $pattern, $m );
		$pattern = "/[=,]/";
		$resultValues = preg_split ( $pattern, $results [1] );

		$size = $resultValues [3];
		$found = false;
		if ($resultValues [3] > 0) {
			$found = true;
			$pattern = "/[\n:]/";
			$matches = preg_split ( $pattern, $results [0] );
			for($cellOffset = 0; $cellOffset < count ( $matches ); $cellOffset ++) {
				$next = $cellOffset+1;
				switch ($matches [$cellOffset]) {
					case 'CNUM' :
					case 'INTERNET' :
					case 'WORKLOC' :
					case 'NAME' :
					case 'HRACTIVE':
					case 'HREMPLOYEETYPE':
					case 'EMPTYPE':
					case 'DEPT':
					case 'HRFAMILYNAME':
					case 'JOBRESPONSIB' :
					case 'EMPNUM':
					case 'EMPCC' :
					case 'MGRNUM':
					case 'MGRCC':
						break;
					case 'NOTESID':
						$notesId = trim ( $matches [$cellOffset+1]);
					default :
						;
						break;
				}
			}
		} else {
			$found = FALSE;
		}

		if($found){
			return self::cleanupNotesid($notesId);
		} else {
			return FALSE;
		}
	}

	static function validateNotesId($notesId) {
		$notesId = strtoupper(trim($notesId));
		if(empty($notesId)){
			return FALSE;
		}
		set_time_limit(120);
		$url = self::$wsapiUrl . "?allByNotesIDLite=NOTES_ID_HERE%25";

		$sp = strpos($notesId,'/O=IBM');

		if($sp != FALSE){
			$amendIbm2 = urlencode(trim($notesId));
		} else {
			$amendIbm2 = self::buildNotesId($notesId);
		}
        // echo "<BR/>URL:" . str_replace('NOTES_ID_HERE',$amendIbm2,$url);
		$ch = curl_init ( str_replace('NOTES_ID_HERE',$amendIbm2,$url) );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );

		$m = curl_exec ( $ch );

		$pattern = "/# rc/";
		$results = preg_split ( $pattern, $m );

		$pattern = "/[=,]/";
		$resultValues = preg_split ( $pattern, $results [1] );
		$size = $resultValues [3];
		$found = false;
		if ($resultValues [3] > 0) {
			$found = true;
			$pattern = "/[\n:]/";
			$matches = preg_split ( $pattern, $results [0] );
			for($cellOffset = 0; $cellOffset < count ( $matches ); $cellOffset ++) {
				switch ($matches [$cellOffset]) {
					case 'CNUM' :
					case 'INTERNET' :
					case 'WORKLOC' :
					case 'NAME' :
					case 'HRACTIVE':
					case 'HREMPLOYEETYPE':
					case 'EMPTYPE':
					case 'DEPT':
					case 'HRFAMILYNAME':
					case 'JOBRESPONSIB' :
					case 'EMPNUM':
					case 'EMPCC' :
					case 'MGRNUM':
					case 'MGRCC':
					case 'NOTESID':
					default :
						;
						break;
				}
			}
		} else {
			$found = FALSE;
		}
		return $found;
	}

	static function getIntranetIdFromNotesId($notesId) {
	    $notesId = strtoupper(trim($notesId));
		if(empty($notesId)){
			return FALSE;
		}
		set_time_limit(120);
		$url = self::$wsapiUrl . "?allByNotesIDLite=NOTES_ID_HERE%25";

	    $sp = strpos($notesId,'/O=IBM');

		if($sp != FALSE){
			$amendIbm2 = urlencode(trim($notesId));
		} else {
			$amendIbm2 = self::buildNotesId($notesId);
		}
        // echo "<BR/>URL:" . str_replace('NOTES_ID_HERE',$amendIbm2,$url);
		$ch = curl_init ( str_replace('NOTES_ID_HERE',$amendIbm2,$url) );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		$m = curl_exec ( $ch );

		$pattern = "/# rc/";
		$results = preg_split ( $pattern, $m );

		$pattern = "/[=,]/";
		$resultValues = preg_split ( $pattern, $results [1] );
		$size = $resultValues [3];
		$found = false;
		if ($resultValues [3] > 0) {
			$found = true;
			$pattern = "/[\n:]/";
			$matches = preg_split ( $pattern, $results [0] );
			for($cellOffset = 0; $cellOffset < count ( $matches ); $cellOffset ++) {
				switch ($matches [$cellOffset]) {
					case 'CNUM' :
					case 'WORKLOC' :
					case 'NAME' :
					case 'HRACTIVE':
					case 'HREMPLOYEETYPE':
					case 'EMPTYPE':
					case 'DEPT':
					case 'HRFAMILYNAME':
					case 'JOBRESPONSIB' :
					case 'EMPNUM':
					case 'EMPCC' :
					case 'MGRNUM':
					case 'MGRCC':
					case 'NOTESID':
						break;
					case 'INTERNET' :
//						echo "<BR/>" . __METHOD__ . __LINE__ . $cellOffset;
//						print_r($matches);
						$internetId = trim ( $matches [$cellOffset+1]);
					default :
						;
						break;
				}
			}
		} else {
			$found = FALSE;
		}
		if($found){
			return $internetId;
		} else {
			return FALSE;
		}
	}

	static function validateIntranetId($intranetId) {
		$intranetId = strtoupper(trim($intranetId));
		if(empty($intranetId)){
			return FALSE;
		}
		set_time_limit(120);
		$url = self::$wsapiUrl . "?byInternetAddr=INTRANET_ID_HERE";
		// echo "<BR/>" . str_replace('INTRANET_ID_HERE',urlencode($intranetId),$url);
		$ch = curl_init ( str_replace('INTRANET_ID_HERE',urlencode($intranetId),$url) );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );

		$m = curl_exec ( $ch );

		$pattern = "/# rc/";
		$results = preg_split ( $pattern, $m );
		$pattern = "/[=,]/";
		$resultValues = preg_split ( $pattern, $results [1] );

		$size = $resultValues [3];
		$found = false;
		if ($resultValues [3] > 0) {
			$found = true;
			$pattern = "/[\n:]/";
			$matches = preg_split ( $pattern, $results [0] );
			for($cellOffset = 0; $cellOffset < count ( $matches ); $cellOffset ++) {
				switch ($matches [$cellOffset]) {
					case 'CNUM' :
					case 'INTERNET' :
					case 'WORKLOC' :
					case 'NAME' :
					case 'HRACTIVE':
					case 'HREMPLOYEETYPE':
					case 'EMPTYPE':
					case 'DEPT':
					case 'HRFAMILYNAME':
					case 'JOBRESPONSIB' :
					case 'EMPNUM':
					case 'EMPCC' :
					case 'MGRNUM':
					case 'MGRCC':
					case 'NOTESID':
					default :
						;
						break;
				}
			}
		} else {
			$found = FALSE;
		}
		return $found;
	}

	function lookup($cnum) {
		$this->CNUM = $cnum;
		$dept = null;

		$ch = curl_init ( str_replace('CNUM_HERE',$this->CNUM,$this->url) );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );

		$m = curl_exec ( $ch );

		$pattern = "/# rc/";
		$results = preg_split ( $pattern, $m );

		$pattern = "/[=,]/";
		$resultValues = preg_split ( $pattern, $results [1] );
		$this->size = $resultValues [3];
		$this->found = false;
		if ($resultValues [3] > 0) {
//		echo "<H4>Has Direct Reports - so show them</H4>";
			$this->found = true;
			$pattern = "/[\n:]/";
			$matches = preg_split ( $pattern, $results [0] );
			for($cellOffset = 0; $cellOffset < count ( $matches ); $cellOffset ++) {
				switch ($matches [$cellOffset]) {
					case 'CNUM' :
						if($this->mgr){
							$newPerson = new BPRecord ( trim ( $matches [$cellOffset + 1] ),$this->online, $this->table, $this->mgr );
							$newPerson->lookup (trim ( $matches [$cellOffset + 1] ));
							set_time_limit ( 60 );
							$newPerson->saveToDb ( );
						}
					case 'INTERNET' :
					case 'WORKLOC' :
					case 'NAME' :
					case 'HRACTIVE':
					case 'HREMPLOYEETYPE':
					case 'EMPTYPE':
					case 'DEPT':
					case 'HRFAMILYNAME':
					case 'JOBRESPONSIB' :
					case 'EMPNUM':
					case 'EMPCC' :
					case 'MGRNUM':
					case 'MGRCC':
						if($this->mgr){
							$this->dept [trim ( $matches [$cellOffset] )] [] = trim ( $matches [$cellOffset + 1] );
						} else {
							$this->person [trim ( $matches [$cellOffset] )] = trim ( $matches [$cellOffset + 1] );
						}
						break;
					case 'NOTESID':
						// Comes like this : CN=Rob Daniel/OU=UK/O=IBM@IBMGB
						$stripCN = str_replace('CN=','',trim($matches [$cellOffset + 1]));  // Remove the leading CN=
						$stripOU = str_replace('OU=','',$stripCN);							// Remove the OU=
						$stripO  = str_replace('O=','',$stripOU);							// Remove the O=
						$stripAt = substr($stripO,0,strlen($stripO)-6);						// Remove the @IBMGB
						if($this->mgr){
							$this->dept [trim ( $matches [$cellOffset] )] [] = $stripAt ;
						} else {
							$this->person [trim ( $matches [$cellOffset] )] = $stripAt ;
						}
					default :
						;
						break;
				}
			}
		}
	}

	function saveToDb(){
		if($this->mgr){
			$this->saveDeptToDb();
		} else {
			if($this->found){
				$this->savePersonToDb();
			}
		}
	}

	function saveDeptToDb() {
		if (isset ( $this->dept )) {
		//	$sql = " INSERT INTO " . $_SESSION ['prefix'] . "." . $this->table . " ( NAME, SERIAL, COUNTRY_CODE, LOCATION, MGR_SERIAL, MGR_CTRY_CODE, REG_OR_SUBCO, INTERNET, EMPTYPE, HRACTIVE, HREMPLOYEETYPE, DEPT, HRFAMILYNAME, NOTESID, JOBRESPONSIB) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)  ";

		//	$preparedInsert = db2_prepare ( $_SESSION ['conn'], $sql );
			$actual = 0;
			foreach ( $this->dept ['NAME'] as $key => $value ) {
				$data [0] = substr ( $value, 0, 50 ); // Name from BP
				$data [1] = substr ( $this->dept ['CNUM'] [$key], 0, 6 ); // 6 Digit Serial
				$data [2] = substr ( $this->dept ['CNUM'] [$key], 6, 3 ); // 3 Digit Country Code
				$data [3] = $this->dept ['WORKLOC'] [$key]; // 3 Digit Work Location
				$data [4] = substr ( $this->CNUM, 0, 6 ); // Mgrs Serial
				$data [5] = substr ( $this->CNUM, 6, 3 ); // Mgrs Country Code
				if (stripos ( $data [0], '*CON' ) === false) {
					$data [6] = 'R'; // They are a Regular
				} else {
					$data [6] = 'C'; // They are a Contractor
				}
				$data[7]  = $this->dept['INTERNET'][$key];
				$data[8]  = $this->dept['EMPTYPE'][$key];
				$data[9]  = $this->dept['HRACTIVE'][$key];
				$data[10] = $this->dept['HREMPLOYEETYPE'][$key];
				$data[11] = $this->dept['DEPT'][$key];
				$data[12] = $this->dept['HRFAMILYNAME'][$key];
				$data[13] = $this->dept['NOTESID'][$key];
				$data[14] = $this->dept['JOBRESPONSIB'][$key];
				if ((stripos ( $data [0], '*FUN' ) === false)) { // Don't record the Functional Ids.
					$rs = sqlsrv_queryute ( $this->preparedInsert, $data );
					if (! $rs) {
						echo "<BR>" . sqlsrv_errors ();
						echo "<BR>" . sqlsrv_errors () . "<BR>";
						echo "<BR> Data :";
						print_r ( $data );
						exit ( "Unable to Execute $sql" );
					}
					if($this->online){
						echo "<BR>" . $data [0];
					}
					$actual++ ;
				}
				$data = null;
			}
			if($this->online){
				echo "<H2>Saved Department of $this->size ($actual) People for : " . $this->CNUM . "</H2>";
			}
			$rs = sqlsrv_query ( $_SESSION ['conn'], " COMMIT" );
			if (! $rs) {
				print_r ( $_SESSION );
				echo "<BR>" . sqlsrv_errors ();
				echo "<BR>" . sqlsrv_errors () . "<BR>";
				exit ( "Error in: " . __METHOD__ . " running: COMMIT " );
			}
		}
	}

	function savePersonToDb() {
		$actual = 0;
		$data [0] = substr ( $this->person  ['NAME'], 0, 50 );	 	// Name from BP
		$data [1] = substr ( $this->person  ['EMPNUM'], 0, 6 );		// 6 Digit Serial
		$data [2] = substr ( $this->person  ['EMPCC'], 0, 3 ); 		// 3 Digit Country Code
		$data [3] = $this->person ['WORKLOC'] ; 					// 3 Digit Work Location
		$data [4] = substr ( $this->person  ['MGRNUM'], 0, 6 );  	// Mgrs Serial
		$data [5] = substr ( $this->person  ['MGRCC'], 0, 3 ); 		// Mgrs Country Code
		if (stripos ( $data [0], '*CON' ) === false) {
			$data [6] = 'R'; // They are a Regular
		} else {
			$data [6] = 'C'; // They are a Contractor
		}
		$data[7]  = $this->person['INTERNET'];
		$data[8]  = $this->person['EMPTYPE'];
		$data[9]  = $this->person['HRACTIVE'];
		$data[10] = $this->person['HREMPLOYEETYPE'];
		$data[11] = $this->person['DEPT'];
		$data[12] = $this->person['HRFAMILYNAME'];
		$data[13] = $this->person['NOTESID'];
		$data[14] = $this->person['JOBRESPONSIB'];
		if ((stripos ( $data [0], '*FUN' ) === false)) { // Don't record the Functional Ids.
			$rs = sqlsrv_queryute ( $this->preparedInsert, $data );
			if (! $rs) {
				echo "<BR>" . sqlsrv_errors ();
				echo "<BR>" . sqlsrv_errors () . "<BR>";
				echo "<BR> Data :";
				print_r ( $data );
				exit ( "Unable to Execute $sql" );
			}
			$actual++ ;
		}
		$data = null;
		if($this->online){
			echo "<H2>Saved Details for : " . $this->CNUM . " " . $this->person  ['NAME'] . "</H2>";
		}
		// $rs = sqlsrv_query ( $_SESSION ['conn'], " COMMIT" );
		// if (! $rs) {
		// 	print_r ( $_SESSION );
		// 	echo "<BR>" . sqlsrv_errors ();
		// 	echo "<BR>" . sqlsrv_errors () . "<BR>";
		// 	exit ( "Error in: " . __METHOD__ . " running: COMMIT " );
		// }
	}

	static function processDetails($ch){
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );	
		$m = curl_exec ( $ch );
		
		$pattern = "/# rc/";
		$results = preg_split ( $pattern, $m );
		$pattern = "/[=,]/";
		$resultValues = preg_split ( $pattern, $results [1] );

		$size = $resultValues [3];
		$found = false;
		if ($resultValues [3] > 0) {
			$found = true;
			$pattern = "/[\n]/";
			$matches = preg_split ( $pattern, $results [0] );
			foreach($matches as $field){
				$subFields = explode(":", $field,2);
				if(isset($subFields[1])){
					$details[$subFields[0]] = $subFields[1];
				}
			}
		} else {
			$found = FALSE;
		}

		if($found){
			return $details;
		} else {
			return FALSE;
		}
	}

	static function readBatchOfBpEntries($resultSet, $numberOfRowsToReturn = 10){
        $rowCounter = 1;
        $batchOfCnums = false;
        while ($rowCounter <= $numberOfRowsToReturn) {
            if (($row = sqlsrv_fetch_array($resultSet)) == false) {
                break;    /* You could also write 'break 1;' here. */
            } else {
                $countryCode = isset(self::$countryCodeMapping[trim($row['COUNTRY'])]) ? self::$countryCodeMapping[trim($row['COUNTRY'])] : trim($row['COUNTRY']);
                $cnum = trim($row['SERIAL']) . $countryCode;
                $batchOfCnums[] = $cnum;
            }
            $rowCounter++;
        }
        return $batchOfCnums;
    }

	static function extractSpecificDetailsToAllDetails($directoryEntries,&$allDetails){
        foreach ($directoryEntries as $entry) {
            foreach ($entry as  $personDetails) {
                $specificDetails = null;
                foreach($personDetails as $attr){
                    $specificDetails[(string)$attr['name']] = (string)$attr->value;
                }
                $allDetails[$specificDetails['uid']] = $specificDetails;
            }
        }
    }

	static function extractSpecificDetailsToAllDetailsByIntranetId($directoryEntries,&$allDetails){
        foreach ($directoryEntries as $entry) {
            foreach ($entry as  $personDetails) {
                $specificDetails = null;
                foreach($personDetails as $attr){
                    $specificDetails[(string)$attr['name']] = (string)$attr->value;
                }
                $allDetails[$specificDetails['mail']] = $specificDetails;
            }
        }
    }

	static function getBPDetailsFromTemplate($template = ''){
		$startTime = microtime(true);
		set_time_limit(120);

		$ch = curl_init ( $template );
	    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );

	    $curlReturn = curl_exec ( $ch );
	    $lookupTime = (float)(microtime(true) - $startTime);
	    $xml = simplexml_load_string($curlReturn);

		$allDetails = array();
		$directoryEntries = $xml->{'directory-entries'};
		foreach ($directoryEntries as $entry) {
            foreach ($entry as  $personDetails) {
                $specificDetails = null;
                foreach($personDetails as $attr){
                    $specificDetails[(string)$attr['name']] = (string)$attr->value;
                }
                // $allDetails[$specificDetails['uid']] = $specificDetails;
				$allDetails = $specificDetails;
            }
        }

	    // return $xml;
		return $allDetails;
	}

	static function getPersonTemplate(){
		$urlTemplate = self::$slaphapiUrl . "?" . self::$objectIbmPerson . "/";
		return $urlTemplate;
	}

	static function getTemplateParams(){
		$urlTemplate = "." . self::$verbSearch . "/" . self::$adverbByxml;
		return $urlTemplate;
	}

	static function getNamesFromCallUpName($name){
		$pos = strpos($name, ',');
		if ($pos !== false) {
			$namesArr = explode(',', $name);
			$firstName = trim($namesArr[1]);
			$lastName = trim($namesArr[0]);
		} else {
			$firstName = $name;
			$lastName = '';
		}
		$return = array(
			'first' => $firstName, 
			'last' => $lastName
		);
		return $return;
	}

	// legacy functions
	static function getDetailsFromIntranetId($intranetId){
		return BluePagesSLAPHAPISingle::getDetailsFromIntranetId($intranetId);
	}

	static function getDetailsFromNotesId($notesId) {
		return BluePagesSLAPHAPISingle::getDetailsFromNotesId($notesId);
	}

	static function getDetailsFromCnum($cnum){
		return BluePagesSLAPHAPISingle::getDetailsFromCnum($cnum);
	}
}
?>