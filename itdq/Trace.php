<?php
namespace itdq;
use DateTime;

/**
 * @author GB001399
 *
 * Enables Tracing, optionally writing trace records to a TRACE table rather than outputing on the screen.
 *
 * Call the static functions anywhere in your code to create an trace entry.
 *
 * Enable Tracing by setting :
 *  $_SESSION['trace'] = 'Yes' - Trace output written to the screen.
 *  $_SESSION['trace'] = 'Log' - Will write the traceoutput to TRACE table.
 * Optionally suppress tracing from some Method's by :
 *  $_SESSION['methodIgnore'][method name here]
 * Optionally suppress tracing from some Class's by :
 *  $_SESSION['classIgnore'][class name here]

 *
 */
class Trace extends Log{
	protected $CLASS;
	protected $METHOD;
	protected $PAGE;

//	public static $fields = array(
//	'LOG_ENTRY' => ARRAY('HEADING' => 'LOG ENTRY', 'COLUMN' => 'LOG_ENTRY')
//	,'LASTUPDATED' => ARRAY('HEADING' => 'LAST UPDATED', 'COLUMN' => 'LASTUPDATED')
//	,'LASTUPDATER' => ARRAY('HEADING' => 'LAST UPDATER', 'COLUMN' => 'LASTUPDATER')
//	,'CLASS' => ARRAY('HEADING' => 'CLASS', 'COLUMN' => 'CLASS')
//	,'METHOD' => ARRAY('HEADING' => 'METHOD', 'COLUMN' => 'METHOD')
//	,'PAGE' => ARRAY('HEADING' => 'PAGE', 'COLUMN' => 'PAGE')
//	);

	static function traceTimings($additionalText=null, $methodParm='Method', $line=null ){
		$page = pathinfo($_SERVER['SCRIPT_FILENAME'],PATHINFO_BASENAME);
		if(!strpos($methodParm,"::")===FALSE){
			$classMethod = explode("::",$methodParm);
			$class = trim($classMethod[0]);
			$method = trim($classMethod[1]);
			//$page = '';
		} else {
			$fileLocation = explode("/",$methodParm);
			$levels = sizeof($fileLocation);
			//$page = $fileLocation[$levels-1];
			$class = 'pageAccess';
			$method = trim($page);
		}

		if((isset($_SESSION['methodTimings'][$method])) or (isset($_SESSION['classTimings'][$class]))){ // Are we tracing ?
			$memory = (memory_get_peak_usage(true)/1048576);
			$elapsed = isset($_SESSION['tracePageOpenTime']) ? microtime(true) - $_SESSION['tracePageOpenTime'] : null;
			$traceString = "<b>*T*:" . $methodParm . "-" . $line . "</b>:<br/> " . htmlspecialchars($additionalText) . "<br/><b>Time : $elapsed Memory:$memory mb</b>";
			self::logEntry($traceString,$class,$method,$page);
		}
	}



	static function traceComment($additionalText=null, $methodParm='Method', $line=null ){
		$page = pathinfo($_SERVER['SCRIPT_FILENAME'],PATHINFO_BASENAME);
		if(!strpos($methodParm,"::")===FALSE){
			$classMethod = explode("::",$methodParm);
			$class = trim($classMethod[0]);
			$method = trim($classMethod[1]);
			//$page = ' ';
		} else {
			$fileLocation = explode("/",$methodParm);
			$levels = sizeof($fileLocation);
			//$page = $fileLocation[$levels-1];
			$class = 'pageAccess';
			$method = trim($fileLocation[$levels-1]);
		}

		if(isset($_SESSION['trace']) or (isset($_SESSION['methodInclude'][$method])) or (isset($_SESSION['classInclude'][$class]))){ // Are we tracing ?
			if(!isset($_SESSION['methodExclude'][$method]) and !isset($_SESSION['classExclude'][$class])){ // Is this Class/Method one we're ignoring ?
				$now = new \DateTime();
				// $additionalText .= "\n<<USER>>" . $_SESSION['ssoEmail'] . " <<TIME>>" . $now->format('Y-m-d H:i:s');
				$traceString = "<B>" . $methodParm . "-" . $line . "</B>:<br/> " . htmlspecialchars($additionalText);
				self::logEntry($traceString,$class,$method,$page);
			}
		}
	}

	static function traceVariable($variable, $methodParm='Method', $line=null){
		$page = pathinfo($_SERVER['SCRIPT_FILENAME'],PATHINFO_BASENAME);
		if(!strpos($methodParm,"::")===FALSE){
			$classMethod = explode("::",$methodParm);
			$class = trim($classMethod[0]);
			$method = trim($classMethod[1]);
			//$page = ' ';
		} else {
			$fileLocation = explode("/",$methodParm);
			$levels = sizeof($fileLocation);
			//$page = $fileLocation[$levels-1];
			$class = 'pageAccess';
			$method = trim($fileLocation[$levels-1]);
		}

		$output = print_r($variable, TRUE);
		// $output = serialize($variable);
		$length = strlen($output);
		if($length > 31000){
			$output = substr($output,0,31000);
		}
		$type = gettype($variable);
		$traceString = "<B>" . $methodParm . "-" . $line . "</B>:<br/> Variable Type $type : Value: $output ";
		if(isset($_SESSION['trace']) or (isset($_SESSION['methodInclude'][$method])) or (isset($_SESSION['classInclude'][$class]))){
			if(!isset($_SESSION['methodExclude'][$method]) and !isset($_SESSION['classExclude'][$class])){ // Is this Class/Method one we're ignoring ?
				self::logEntry($traceString,$class,$method,$page);
			}
		}
	}

	/**
 	* Actually writes the Log Entry to the Log Table.
	*
 	* @param string $entry		Text to be written to the log.
 	* @param string $pwd		Password for encrypted logs or null
 	*/
	static function logEntry($entry,$class=null, $method=null, $page=null, $pwd=null){
		$userid = isset($_SESSION['ssoEmail']) ? db2_escape_string($_SESSION['ssoEmail']) : "unknown";
		$elapsed = isset($_SESSION['tracePageOpenTime']) ? microtime(true) - $_SESSION['tracePageOpenTime'] : null;
		$elapsed =  ($elapsed > 3600) ? 0 : $elapsed ; // Fix for long page opening times.

		$sql  = " INSERT INTO " . $GLOBALS['Db2Schema'] . "." . AllItdqTables::$TRACE . " ( LOG_ENTRY,LASTUPDATER,CLASS,METHOD,PAGE ";
		$sql .= empty($elapsed) ? ") " : ",ELAPSED) ";
		$db2Entry = db2_escape_string($entry);

		$db2Entry = strlen($db2Entry)>31000 ? "**TRUNCATED**" . substr($db2Entry, 0,31000) : $db2Entry;

		if($pwd != null){
			$db2Entry =  str_replace($pwd,'********',$db2Entry);
			$sql .= " VALUES (ENCRYPT_RC2('$db2Entry','$pwd'),ENCRYPT_RC2('$userid','$pwd'), ENCRYPT_RC2('$class','$pwd'), ENCRYPT_RC2('$method','$pwd'), ENCRYPT_RC2('$page','$pwd') ";
			$sql .= empty($elapsed) ? ") " : ", ENCRYPT_RC2('$elapsed','$pwd') )";
		} else {
			$sql .= " VALUES ('$db2Entry','$userid','$class','$method','$page'";
			$sql .= empty($elapsed) ? ") " : ",'$elapsed') ";
		}
		$rs = sqlsrv_query($GLOBALS['conn'],$sql);
		if(!$rs)
			{
			echo "<BR>Error: " . sqlsrv_errors();
			echo "<BR>Msg: " . sqlsrv_errors() . "<BR>";
			exit("Error in: " . __METHOD__ .  __LINE__ . "<BR>running: $sql");
		}
	}

	static function deleteTraceRecords($keepDays=2){
		$sql = "DELETE FROM " . $GLOBALS['Db2Schema'] . "." . AllItdqTables::$TRACE . " WHERE LASTUPDATED < (CURRENT TIMESTAMP - $keepDays DAYS) ";

		Trace::traceVariable($keepDays);
		$rs = sqlsrv_query($GLOBALS['conn'],$sql);
		if(!$rs)
			{
			echo "<BR>Error: " . sqlsrv_errors();
			echo "<BR>Msg: " . sqlsrv_errors() . "<BR>";
			exit("Error in: " . __METHOD__ .  __LINE__ . "<BR>running: $sql");
		}
	}

	static function setTraceControls(){

		$sql = "SELECT * FROM " . $GLOBALS['Db2Schema'] . "." . AllItdqTables::$TRACE_CONTROL ;
		$rs = sqlsrv_query($GLOBALS['conn'],$sql);
		if(!$rs) {
			echo "<BR>Error: " . sqlsrv_errors()[0]['code'];
			echo "<BR>Msg: " . sqlsrv_errors()[0]['message'] . "<BR>";
			exit("Error in: " . __METHOD__ .  __LINE__ . "<BR>running: $sql");
		}
		$anyExcludes = FALSE;
		$_SESSION['methodInclude'] = array();  // Allows you to make changes, by reseting the array before setting specific values later.
		$_SESSION['methodExclude'] = array(); // Allows you to make changes, by reseting the array before setting specific values later.
		$_SESSION['methodTimings'] = array(); // Allows you to make changes, by reseting the array before setting specific values later.
		$_SESSION['classInclude'] = array(); // Allows you to make changes, by reseting the array before setting specific values later.
		$_SESSION['classExclude'] = array(); // Allows you to make changes, by reseting the array before setting specific values later.
		$_SESSION['classTimings'] = array(); // Allows you to make changes, by reseting the array before setting specific values later.
		unset($_SESSION['trace']);

		while($row = sqlsrv_fetch_array($rs)){
			if(trim($row['TRACE_CONTROL_TYPE'])=='methodExclude' or trim($row['TRACE_CONTROL_TYPE'])=='classExclude'){
				$anyExcludes = TRUE;
			}
			$_SESSION[trim($row['TRACE_CONTROL_TYPE'])][trim($row['TRACE_CONTROL_VALUE'])] = 'On';
		}
		if($anyExcludes){
			$_SESSION['trace']='Log';
		}
	}

	static function pageOpening($file=null,$tracePost = true,$traceRequest = false, $debugBacktrace=false){
	    if(isset(AllItdqTables::$TRACE_CONTROL)){
	        $fileName = empty($file) ? $_SERVER['PHP_SELF'] : $file;
	        $_SESSION['tracePageOpenTime'] = microtime(TRUE);
	        self::setTraceControls();
	        self::traceComment("Page opening." , $fileName);
	        $tracePost ? self::traceVariable($_POST,$fileName) : null;
	        $traceRequest ? self::traceVariable($_REQUEST,$fileName) : null;
	        $debugBacktrace ? self::traceVariable(debug_backtrace()) : null;
	    }
	}


	static function pageLoadComplete($file=null){
	    if(isset(AllItdqTables::$TRACE_CONTROL)){
	        $fileName = empty($file) ? $_SERVER['PHP_SELF'] : $file;
	        $elapsed = isset($_SESSION['tracePageOpenTime']) ? microtime(true) - $_SESSION['tracePageOpenTime'] : null;
	        $memory = (memory_get_peak_usage(true)/1048576);
	        Trace::traceComment("Page Load Time : $elapsed Memory:$memory mb", $fileName);
	    }
	}


	static function allApplicationsClasses($directory=null,$withItdq = true){
		if(empty($directory)){
			$self = $_SERVER['PHP_SELF'];
			$elementsOfSelf = explode("/", $self);
			$directory = $elementsOfSelf[1];
		}
		$allLocalClasses = Trace::allClassesInDirectory($directory);
		$allItdqClasses =  $withItdq ? Trace::allClassesInDirectory("itdq") : null;
		return array_unique(array_merge($allItdqClasses,$allLocalClasses));
	}

	static function allClassesInDirectory($directory=null){
		if(empty($directory)){
			$self = $_SERVER['PHP_SELF'];
			$elementsOfSelf = explode("/", $self);
			$directory = $elementsOfSelf[1];
		}
		$allFiles = scandir($directory);
		$allClasses = null;
		foreach ($allFiles as $fileName) {
		    if(preg_match("/php/i", $fileName)){
				$className = substr($fileName, 0,strlen($fileName)-4);
				$allClasses[] = $className;
			}
		}
		$allClasses[] = 'pageAccess';
		return empty($allClasses) ? false : $allClasses;
	}


	static function listFunctionsInClass($class){
		if(empty($directory)){
			$self = $_SERVER['PHP_SELF'];
			$elementsOfSelf = explode("/", $self);
			$directory = $elementsOfSelf[1];
		}
		$dirFile = $class . ".php";
		$file = fopen($dirFile,'r',true);

		$classFound = false;
		$allExtendedFunctions = null;
		while($line = fgets($file)){
  			if(!$classFound){
  				//echo "<BR/>$line";
  				$matches = preg_split("/class|extends|[\{]/", $line);
  				if(count($matches)>=3){
  					if(trim($matches[2])!=""){
  						//Trace::listFunctionsInClass(trim($matches[2]));
  					}
  					$classFound = true;
  				}
  			}
  			$comment = preg_match('/(\/\/.)/', $line,$comments);
  			if($comment===0){
  				$hit = preg_match('/function(.+)\(/i', $line,$matches);
  				if($hit===1){
  					$method[]= trim($matches[1]);
  				}
  			}
		}
		fclose($file);
		return array_unique($method);

	}





}
?>