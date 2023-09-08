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
		$sql .= " Values ('" . htmlspecialchars(trim($entry)) . "','" . htmlspecialchars($_SESSION['ssoEmail']) . "' ) ";

		$rs = sqlsrv_query( $_SESSION ['conn'], $sql );
		if (! $rs) {
			print_r ( $_SESSION );
			echo "<BR/>" . sqlsrv_errors ();
			echo "<BR/>" . sqlsrv_errors () . "<BR/>";
			exit ( "Error in: " . __METHOD__ . " running: " . $sql );
		}
		return	db2_last_insert_id($_SESSION ['conn']);

	}
}

?>