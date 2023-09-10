<?php
namespace itdq;

use itdq\OKTAGroups;

/**
 * Interfaces to ICON_as_a_Service.
 *
 * Call the static functions in this class - and get data back from ICON.
 *
 *
 * @author GB001399
 * @package remind
 *
 */
class IconRolesTable extends DbTable {
	protected $rolesSelectionBar;
	protected $rolesPerRow;
	public static $emailModeTo = 'roleTo';
	public static $emailModeCC = 'roleCC';
	function __construct($table, $pwd = null, $log = true) {
		$this->ignoreProperties = 0;
		parent::__construct ( $table, $pwd, $log );
		$this->rolesSelectionBar = new SelectionBar();
		$this->rolesPerRow = 6;
	}

	/**
	 *
	 *
	 *
	 *
	 *
	 * The Roles Array - Can include Db2 wildcard % character. ie '%service line' - will add " ROLE LIKE '%service line' to the query.
	 *
	 * Returns an array $allCustomerRefs[$row['CUSTOMER_ID']] = $row['CUSTOMER_REF']; (or) FALSE
	 *
	 *
	 * @param array $roles
	 *        	- Array containing Roles. If supplied only these roles are checked for the logged in indivdual.
	 * @param string $country
	 * @return boolean Array multitype:Ambigous <> >
	 */
// 	static function getAllMyCustomers($roles = null, $country = null) {
// 		/*
// 		 * Complicated or what ! Strip out any roles that include the % wildcard - as they have to be coded in a LIKE statement, rather than be included in the IN statment.
// 		 */
// 		$genericRoles = null;
// 		foreach ( $roles as $key => $specificRole ) {
// 			if (strpos ( $specificRole, '%' ) !== false) {
// 				$genericRoles [] = $specificRole;
// 				unset ( $roles [$key] );
// 			}
// 		}

// 		$rolesStr = empty ( $roles ) ? null : implode ( "','", $roles );

// 		Trace::traceTimings ( null, __METHOD__, __LINE__ );
// 		$sql = " SELECT distinct CUSTOMER_REF, CUSTOMER_ID ";
// 		$sql .= " FROM " . $GLOBALS['Db2Schema'] . "." . AllItdqTables::$ICON_ACCOUNT_ROLES;
// 		$sql .= " WHERE UPPER(PERSON_INTRANET)='" . strtoupper ( $GLOBALS ['ltcuser'] ['mail'] ) . "'  ";
// 		$sql .= empty ( $country ) ? null : " AND COUNTRY = '" . htmlspecialchars ( trim ( $country ) ) . "' ";

// //		$sql .= empty ( $roles ) ? null : " AND ( ";
// 		$sql .= empty ( $rolesStr ) ? "AND ( ROLE is null " : " AND ( ROLE in ('" . trim ( $rolesStr ) . "') "; // the ROLE is null is just to make the OR syntax work regardless if there are any roles that donthave the wildcard or not.
// 		if (! empty ( $genericRoles )) {
// 			foreach ( $genericRoles as $key => $role ) {
// 				$sql .= " OR ROLE LIKE '" . trim ( $role ) . "' ";
// 			}
// 			$sql .= " ) ";
// 		}

// 		$rs = sqlsrv_query( $_SESSION ['conn'], $sql );

// 		if (! $rs) {
// 			DBTable::displayErrorMessage ( $rs, __CLASS__, __METHOD__, $sql );
// 			return false;
// 		} else {
// 			$allCustomerRefs = array ();
// 			while ( $row = sqlsrv_fetch_array ( $rs ) ) {
// 				$allCustomerRefs [$row ['CUSTOMER_ID']] = $row ['CUSTOMER_REF'];
// 			}
// 			Trace::traceTimings ( null, __METHOD__, __LINE__ );
// 			return ! empty ( $allCustomerRefs ) ? $allCustomerRefs : false;
// 		}
// 	}
// 	static function getAllRoles($customerId = null, $country = null) {
// 		Trace::traceTimings ( $customerId, __METHOD__, __LINE__ );
// 		$sql = " SELECT distinct ROLE ";
// 		$sql .= " FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$ICON_ACCOUNT_ROLES;
// 		$sql .= " WHERE ROLE is not null ";
// 		$sql .= empty ( $customerId ) ? null : " AND CUSTOMER_ID='" . htmlspecialchars ( trim ( $customerId ) ) . "' ";
// 		$sql .= empty ( $country ) ? null : " AND COUNTRY = '" . htmlspecialchars ( trim ( $country ) ) . "' ";
// 		$rs = sqlsrv_query( $_SESSION ['conn'], $sql );
// 		if (! $rs) {
// 			DBTable::displayErrorMessage ( $rs, __CLASS__, __METHOD__, $sql );
// 			return false;
// 		} else {
// 			$allRoles = array ();
// 			while ( $row = sqlsrv_fetch_array ( $rs ) ) {
// 				$allRoles [$row ['ROLE']] = $row ['ROLE'];
// 			}
// 			Trace::traceTimings ( null, __METHOD__, __LINE__ );
// 			return ! empty ( $allRoles ) ? $allRoles : false;
// 		}
// 	}
// 	static function getIntranetAddressesForRoleAccountCombination(array $rolesArray = null, Array $customerIdArray = null) {
// 		Trace::traceTimings ( null, __METHOD__, __LINE__ );
// 		$sql = " SELECT distinct PERSON_INTRANET ";
// 		$sql .= " FROM " . $GLOBALS['Db2Schema'] . "." . AllTables::$ICON_ACCOUNT_ROLES;
// 		$sql .= " WHERE PERSON_INTRANET is not null ";

// 		if ($rolesArray) {
// 			$sql .= " AND ROLE in (";
// 			foreach ( $rolesArray as $key => $value ) {
// 				$sql .= "'" . htmlspecialchars ( trim ( $value ) ) . "',";
// 			}
// 			$sql = substr ( $sql, 0, strlen ( $sql ) - 1 ) . ") "; // Remove that last ,
// 		}

// 		if ($customerIdArray) {
// 			$sql .= " AND CUSTOMER_ID in (";
// 			foreach ( $customerIdArray as $key => $value ) {
// 				$sql .= "'" . htmlspecialchars ( trim ( $value ) ) . "',";
// 			}
// 			$sql = substr ( $sql, 0, strlen ( $sql ) - 1 ) . ") "; // Remove that last ,
// 		}
// 		Trace::traceComment ( $sql, __METHOD__, __LINE__ );
// 		$rs = sqlsrv_query( $_SESSION ['conn'], $sql );
// 		if (! $rs) {
// 			DBTable::displayErrorMessage ( $rs, __CLASS__, __METHOD__, $sql );
// 			return false;
// 		} else {
// 			$allAddresses = array ();
// 			while ( $row = sqlsrv_fetch_array ( $rs ) ) {
// 				$allAddresses [$row ['PERSON_INTRANET']] = $row ['PERSON_INTRANET'];
// 			}
// 			Trace::traceTimings ( null, __METHOD__, __LINE__ );
// 			return ! empty ( $allAddresses ) ? implode ( ",", $allAddresses ) : false;
// 		}
// 	}

	/**
	 * Will display as a list of Checkboxes, the Roles found in the ICON database, so they can be selected.
	 */
// 	function displayRolesSelection($emailType = null, $customerId = null, $country = null, $width = '95%') {
// 		$emailType = empty ( $emailType ) ? self::$emailModeTo : $emailType; // Default to TO
// 		$emailType = $emailType == self::$emailModeTo || $emailType == self::$emailModeCC ? $emailType : self::$emailModeTo; // Validate, force to TO if invalid

// 		$emailType .= "[]";

// 		$allRoles = self::getAllRoles ( $customerId, $country );
// 		asort ( $allRoles );
// 		if ($allRoles) {
// 			echo "<TABLE class=bar-blue-med-light width=$width>";
// 			echo "<TR>";
// 			$cell = 1;
// 			foreach ( $allRoles as $key => $value ) {
// 				if ($cell ++ == $this->rolesPerRow) {
// 					$cell = 2;
// 					echo "</TR><TR  bgcolor='#99bbee'>";
// 				}
// 				$this->rolesSelectionBar->checkBox ( $key, htmlspecialchars ( trim ( $key ), ENT_QUOTES ), htmlspecialchars ( trim ( $key ), ENT_QUOTES ), null, null, $emailType );
// 			}
// 			echo "</TR>";
// 		}

// 		echo "</TABLE>";
// 	}

	function setRolesPerRow($rolesPerRow = 6) {
		$this->rolesPerRow = $rolesPerRow;
	}

	function displayServiceLineSelection() {
		$serviceLineFunctions = self::getServiceLines ();
		$onChange = " onchange='populateIcrFunctions()' ";
		echo "<TABLE class=bar-blue-med-light>";
		echo "<TR>";
		echo "<TH>Service Line:</TH>";
		echo "<TD><SELECT id='icrServiceLine' name='icrServiceLine' $onChange  >";
		echo "<OPTION VALUE='abc'>Select...</OPTION>";
		foreach ( $serviceLineFunctions as $serviceLine => $functions ) {
			echo "<OPTION VALUE='$serviceLine' ";
			if (! empty ( $_REQUEST ['icrServiceLine'] )) {
				if (trim ( $_REQUEST ['icrServiceLine'] ) == trim ( $serviceLine )) {
					echo " SELECTED ";
				}
			}
			echo " >$serviceLine</OPTION>";
		}

		$disabled = ! empty ( $_REQUEST ['icrFunction'] ) ? null : ' disabled ';

		echo "</SELECT>";
		echo "</TD>";
		echo "</TR>";
		echo "<TR>";
		echo "<TH>Function:</TH>";
		echo "<TD><SELECT id='icrFunction' name='icrFunction' $disabled >";
		if (! empty ( $_REQUEST ['icrFunction'] )) {
			echo "<OPTION VALUE='" . trim ( $_REQUEST ['icrFunction'] ) . "'>" . trim ( $_REQUEST ['icrFunction'] ) . "</OPTION>";
		} else {
			echo "<OPTION VALUE=''>Select...</OPTION>";
		}
		echo "</SELECT>";
		echo "</TD>";
		echo "</TR>";
		echo "</TABLE>";
	}

	/**
	 * Will invoke the 'ICON' 'webservice' that returns the details of the Service Lines and their Functions
	 * Buildind and returning an Array.
	 * And all without using DB2 !! ( Well not from REMIND at least)
	 *
	 * @return string
	 */
	static function getServiceLines() {
		$url = "https://" . $_SERVER ['SERVER_NAME'] . "/" . $_SESSION ['iconDirectory'] . "/s_getServiceLineFunctionXML.php";
		$serviceLineDetails = new \SimpleXMLElement ( $url, 0, true ); // Get the XML document back from ICON

		foreach ( $serviceLineDetails as $serviceLineDetail ) {
			// Step through each of the Service Line Details return and store in an array
			// $serviceLines[urldecode((string)$serviceLineDetail->{"service_line"})] = urldecode((string)$serviceLineDetail->{"service_line"});
			$serviceLineFunctions [urldecode ( ( string ) $serviceLineDetail->{"service_line"} )] [] = urldecode ( ( string ) $serviceLineDetail->{"function"} );
		}
		JavaScript::buildSelectArray ( $serviceLineFunctions, 'serviceLines' );
		return $serviceLineFunctions;
	}
	static function getTeams($serviceLine = null, $function = null) {
		$url = "https://" . $_SERVER ['SERVER_NAME'] . "/" . $_SESSION ['iconDirectory'] . "/s_getServiceLineFunctionXML.php?serviceLine=" . urlencode ( $serviceLine ) . "&function=" . urlencode ( $function );
		$ch = curl_init ( $url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		$xmlDocument = curl_exec ( $ch );
		curl_close ( $ch );
		return $xmlDocument;
	}
	static function getRoles() {
		$url = "https://" . $_SERVER ['SERVER_NAME'] . "/" . $_SESSION ['iconDirectory'] . "/s_getRoleXML.php";
		$roleDetails = new \SimpleXMLElement ( $url, 0, true ); // Get the XML document back from ICON
		$role = array ();
		foreach ( $roleDetails as $roleName ) {
			$role [urldecode ( ( string ) $roleName )] = urldecode ( ( string ) $roleName );
		}
		return $role;
	}
	static function getMyRoles($myIntranetId = null) {
		$intranetParms = ! empty ( $myIntranetId ) ? "?intranet=" . trim ( $myIntranetId ) : null;
		$url = "https://" . $_SERVER ['SERVER_NAME'] . "/" . $_SESSION ['iconDirectory'] . "/s_getAccountRoleXML.php" . $intranetParms;

		$xml = new \SimpleXMLElement ( $url, 0, true ); // Get the XML document back from ICON
		$json = json_encode ( $xml );
		$array = json_decode ( $json, TRUE );

		$rolesArray = array ();

		foreach ( $array as $key => $value ) {
			foreach ( $value as $key2 => $value2 ) {
				switch (true) {
					case is_int($key2):
						$rolesArray [trim ( urldecode ( $value2 ['role'] ) )] = trim ( urldecode ( $value2 ['role'] ) );
						break;
					case is_string( $key2 ) :
						$rolesArray [trim ( urldecode ( $value ['role'] ) )] = trim ( urldecode ( $value ['role'] ) );
						break;
					default :
						exit ( " Value is an unexpected type. tell Piotr, let him sweat about it." );
						break;
				}
			}
		}

		return $rolesArray;
	}
	static function getAccountNames($column = null) {
		$columnName = ! empty ( $column ) ? "?columnName=" . trim ( $column ) : null;
		$url = "https://" . $_SERVER ['SERVER_NAME'] . "/" . $_SESSION ['iconDirectory'] . "/s_getAccountXML.php";

		$xml = new \SimpleXMLElement( $url, 0, true ); // Get the XML document back from ICON
		$json = json_encode ( $xml );
		$array = json_decode ( $json, TRUE );

		$accountArray = array ();

		foreach ( $array as $key => $value ) {
			foreach ( $value as $key2 => $value2 ) {
				$accountArray [trim ( urldecode ( $value2 ['customer_id'] ) )] = trim ( urldecode ( $value2 ['account_name'] ) );
			}
		}

		return $accountArray;
	}

	static function getAccountDetails() {
	    $url = "https://" . $_SERVER ['SERVER_NAME'] . "/" . $_SESSION ['iconDirectory'] . "/s_getAccountXML.php";

	    $xml = new \SimpleXMLElement( $url, 0, true ); // Get the XML document back from ICON
	    $json  = json_encode ( $xml );
	    $array = json_decode ( $json, TRUE );

	    $accountArray = array ();

	    foreach ( $array as $key => $value ) {
	        foreach ( $value as $key2 => $value2 ) {
	            $accountArray [trim ( urldecode ( $value2 ['customer_id'] ) )] = array('account_name'=> trim ( urldecode ( $value2 ['account_name'] ))
	                ,'sector'=>!empty($value2 ['sector']) ? trim ( urldecode ( $value2 ['sector'] )) : null
	                ,'sword_id'=> !empty($value2 ['sword_id']) ? trim ( urldecode ( $value2 ['sword_id'] )) : null
	                ,'business_line'=> !empty($value2 ['business_line']) ? trim ( urldecode ( $value2 ['business_line'] )) : null
	                ,'portfolio'=> !empty($value2 ['portfolio']) ? trim ( urldecode ( $value2 ['portfolio'] )) : null
	                ,'account_restricted'=> !empty($value2 ['account_restricted']) ? trim ( urldecode ( $value2 ['account_name'] )) : null
	                , ) ;
	        }
	    }

	    return $accountArray;
	}


	static function getAccountRoles($role) {
	    Trace::traceTimings('IconRolesTable::getAccountRoles start',$_SERVER['PHP_SELF'],__LINE__);
	    $roleParm = ! empty ( $role ) ? "?role=" . trim ( $role ) : null;

	    $url = "https://" . $_SERVER ['SERVER_NAME'] . "/" . $_SESSION ['iconDirectory'] . "/s_getAccountRoleXML.php$roleParm";

	    $xml = new \SimpleXMLElement( $url, 0, true ); // Get the XML document back from ICON
	    Trace::traceTimings('IconRolesTable::getAccountRoles xml returned',$_SERVER['PHP_SELF'],__LINE__);
	    $json = json_encode ( $xml );
	    $array = json_decode ( $json, TRUE );
	    $accountArray = array ();

	    if($array){
            foreach ( $array['account_role'] as $key => $accountDetails ) {
                $accountArray [trim ( urldecode ( $accountDetails ['customer_id'] ) )][trim(urldecode ( $accountDetails ['role']))][] = array('person_intranet'=> trim ( urldecode ( $accountDetails ['person_intranet'] )), 'person_name' => trim ( urldecode ( $accountDetails ['person_name'] )));
            }
            Trace::traceTimings('IconRolesTable::getAccountRoles complete',$_SERVER['PHP_SELF'],__LINE__);
            return $accountArray;
	    } else {
	        Trace::traceTimings('IconRolesTable::getAccountRoles complete',$_SERVER['PHP_SELF'],__LINE__);
	        return false;
	    }

	}



	/**
	 * Uses peoples ROLES in ICON, to build a predicate that can be used to control the records returned from amy!! table. (Assuming the table has a JOB_ROLE column and an INTRANET_ID column(exact names provided in the call)
	 *
	 * If $fullAccessBluegroup is specified - will check if they are a member and if so, returns null, as they have FULL access.
	 * If $verbose is TRUE - then messages are issued about the access control decision.
	 * $jobRoleColumn - is the column name in the table that will be used in an $jobRoleColumn in ('first_role','second_role') element of the predicate
	 * If $intranetColumn is NOT null, then it's used to prevent people seeing their own records by the inclusion of $intranetColumn != $_SESSION['ssoEmail'] in the returned predicate.
	 *
	 *
	 * @param string $fullAccessBluegroup
	 * @param string $verbose
	 * @param string $jobRoleColumn
	 * @param string $intranetColumn
	 * @return NULL|string
	 */
	static function calculateAccessPredicate($verbose= true, $jobRoleColumn='JOB_ROLE', $intranetColumn=null, $fullAccessBluegroup=null){

		$OKTAGroups = new OKTAGroups();
		if($fullAccessBluegroup!=null){
			if($OKTAGroups->inAGroup($fullAccessBluegroup, $_SESSION['ssoEmail'])){
				echo $verbose ? "<h4 style='color:blue'>" . $_SESSION['ssoEmail'] . " is a member of " . $fullAccessBluegroup . ", therefore you have full access to this view</h4>" : null;
				return null;
			}
		}

		$employeeRoles = IconRolesTable::getMyRoles($_SESSION['ssoEmail']);

		if(empty($employeeRoles)){
			$accessMessage = "<h4 style='color:red'>" . $_SESSION['ssoEmail'] . " has no roles defined in ICON ";
			$accessMessage .= !empty($fullAccessBluegroup) ? " and you are not a member of ". $fullAccessBluegroup : null;
			$accessMessage .= " therefore you are not permitted access to this view</h4>";
			echo $verbose ?   $accessMessage : null;
			return " AND 1 > 1 ";  // They are not allowed any access.
		}
		$accessMessage = "<h4 style='color:blue'>" . $_SESSION['ssoEmail'] . " is defined in ICON as having these roles : ";
		$accessPredicate = !empty($intranetColumn) ?  " AND lower(" . $intranetColumn . ") != '" . strtolower($_SESSION['ssoEmail']) ."' ": Null;
		$accessPredicate .= " AND " . $jobRoleColumn . " in (";
		foreach ($employeeRoles as $employee){
			$accessMessage .= $employee .",";
			$accessPredicate .= "'" . trim($employee) . "',";
		}
		$accessPredicate .=  "'')";
		echo  $verbose ? substr($accessMessage, 0 , strlen($accessMessage)-1) . "</h4>" : null;
		return $accessPredicate;

	}


	static function is_a($intranetId=null,$jobRole=null){
	    if(empty($intranetId) or empty($jobRole)){
	        return false;
	    }

	    $allMyRoles = IconRolesTable::getMyRoles($intranetId);
	    return isset($allMyRoles[$jobRole]);
	}


}
?>