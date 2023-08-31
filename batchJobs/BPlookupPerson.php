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

// $resultSet = $personTable->fetchPeopleList(" AND EMAIL_ADDRESS LIKE '%ocean.ibm.com%'");
$resultSet = $personTable->fetchPeopleList();
if (!$resultSet) {
    echo sqlsrv_errors();
    echo sqlsrv_errors();
    throw new \Exception('Error reading People from PERSON');
}

$callback = [$personTable, 'updateTable'];
$column = 'EMAIL_ADDRESS';

$personTable->prepareCheckExistsStatement($column);
$personTable->prepareUpdateSqlStatement($column);
$updater->populateDataFromBluepages($resultSet, $callback, $column);
