<?php

ini_set("error_reporting", E_ALL);
ini_set("display_errors", '1');

use itdq\Ocean\BluePagesSLAPHAPISingle;
use upes\AllTables;
use upes\TableDataUpdater;
use upes\updaters\PersonTableCNUMsUpdater;
use upes\updaters\PersonTableDataUpdater;

ini_set('max_execution_time',7201);
ini_set('max_input_time',7201);
set_time_limit(7201);

$personTable = new PersonTableDataUpdater(AllTables::$PERSON);
$personTable->unknown = 'unknown';
$updater = new TableDataUpdater($personTable);

?><h3>First Pass - People Lookup by EMAIL_ADDRESS</h3><hr/><?php

$GLOBALS['Db2Schema'] = 'UPES_NEWCO_DEV';

echo $GLOBALS['Db2Schema'];

// $resultSet = $personTable->fetchPeopleList(" AND EMAIL_ADDRESS LIKE '%ocean.ibm.com%'");
$resultSet = $personTable->fetchPeopleList();
if (!$resultSet) {
    echo db2_stmt_error();
    echo db2_stmt_errormsg();
    throw new \Exception('Error reading People from PERSON');
}

$callback = [$personTable, 'updateTable'];
$column = 'EMAIL_ADDRESS';

$personTable->prepareCheckExistsStatement($column);
$personTable->prepareUpdateSqlStatement($column);
$updater->populateDataFromBluepages($resultSet, $callback, $column);

$GLOBALS['Db2Schema'] = 'UPES_NEWCO_DEV';