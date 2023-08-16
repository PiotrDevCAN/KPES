<?php
namespace itdq;
use itdq\AllItdqTables;
/**
 * Interfaces to the DIARY table, basically by inserting entries.
 *
 * @author GB001399
 * @package esoft
 *
 */
class DiaryTable  extends DbTable {

	static function insertEntry( $entry) {
		$sql = "INSERT INTO " . $GLOBALS['Db2Schema'] . "." . AllItdqTables::$DIARY . " ( ENTRY, INTRANET_ID) ";
		$sql .= " Values ('" . db2_escape_string(trim($entry)) . "','" . db2_escape_string($_SESSION['ssoEmail']) . "' ) ";

		$rs = sqlsrv_query ( $_SESSION ['conn'], $sql );
		if (! $rs) {
			print_r ( $_SESSION );
			echo "<BR/>" . db2_stmt_error ();
			echo "<BR/>" . db2_stmt_errormsg () . "<BR/>";
			exit ( "Error in: " . __METHOD__ . " running: " . $sql );
		}
		return	db2_last_insert_id($_SESSION ['conn']);

	}
}

?>