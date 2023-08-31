<?php

use upes\AccountPersonTable;
use upes\allTables;

$filename = "../ots_uploads/" . $_POST['filename'];

$accountPersonTable = new AccountPersonTable(allTables::$ACCOUNT_PERSON);
$accountPersonTable->copyXlsxToDb2($filename);
