<?php
namespace itdq;
/**
 * @author GB001399
 *
 *
 *
 */
class TraceControlTable extends DbTable {
	/**
	 * Deletes a record from the Delegate Table.
	 * @param string $delegateIntranet
	 * @param boolean $announce
	 */
	static function deleteTraceControl($traceControlType, $traceControlValue){
		$sql = "Delete from " . $GLOBALS['Db2Schema'] . "." . AllItdqTables::$TRACE_CONTROL . " WHERE TRACE_CONTROL_TYPE = '$traceControlType' and TRACE_CONTROL_VALUE = '$traceControlValue' ";
		$rs = sqlsrv_query ( $_SESSION ['conn'], $sql );
		if (! $rs) {
			print_r ( $_SESSION );
			echo "<BR/>" . db2_stmt_error ();
			echo "<BR/>" . db2_stmt_errormsg () . "<BR/>";
			exit ( "Error in: " . __METHOD__ . " running: " . $sql );
		}
	}

	/**
	 * Inserts a record into the Delegate Table
	 *
	 * @param string $delegateIntranet
	 * @param string $delegateNotesid
	 */
	static function insertTraceControl($traceControlType, $traceControlValue){
		self::deleteTraceControl($traceControlType, $traceControlValue);
		$sql = "INSERT INTO " . $GLOBALS['Db2Schema'] . "." . AllItdqTables::$TRACE_CONTROL . " ( TRACE_CONTROL_TYPE, TRACE_CONTROL_VALUE) ";
		$sql .= " Values ('$traceControlType','$traceControlValue') ";
		$rs = sqlsrv_query ( $_SESSION ['conn'], $sql );
		if (! $rs) {
			print_r ( $_SESSION );
			echo "<BR/>" . db2_stmt_error ();
			echo "<BR/>" . db2_stmt_errormsg () . "<BR/>";
			exit ( "Error in: " . __METHOD__ . " running: " . $sql );
		}
	}

}
?>