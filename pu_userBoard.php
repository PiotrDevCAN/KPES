<?php

use itdq\Trace;
use upes\AccountTable;
use upes\AllTables;
use upes\ContractTable;
use upes\PesLevelTable;
use upes\PersonTable;
use upes\AccountPersonRecord;

Trace::pageOpening($_SERVER['PHP_SELF']);

$pesLevelTable = new PesLevelTable(AllTables::$PES_LEVELS);
$pesLevelByAccount  = PesLevelTable::prepareJsonArraysForPesSelection();
$allContractAccountMapping = ContractTable::prepareJsonObjectMappingContractToAccount();
$accountIdLookup = AccountTable::prepareJsonAccountIdLookup();
$upesrefToNameMapping = PersonTable::prepareJsonUpesrefToNameMapping();

?>
<div class='container'>
<h2>Board Individual</h2>

<?php
$accountPersonRecord = new AccountPersonRecord();
$accountPersonRecord->displayForm(itdq\FormClass::$modeDEFINE);
?>
</div>
<script type="text/javascript">

var pesLevelByAccount = <?=json_encode($pesLevelByAccount);?>;
var accountContractLookup = <?=json_encode($allContractAccountMapping); ?>;
var accountIdLookup = <?=json_encode($accountIdLookup); ?>;
var upesrefToNameMapping = <?= json_encode($upesrefToNameMapping); ?>;

</script>
<?php
Trace::pageLoadComplete($_SERVER['PHP_SELF']);
?>