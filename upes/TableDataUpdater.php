<?php
namespace upes;

use itdq\AllItdqTables;
use itdq\BluePagesSLAPHAPI;
use itdq\Both\BluePagesSLAPHAPIMulti;
use itdq\DbRecord;
use itdq\DBTable;
use itdq\IBM\BluePagesSLAPHAPIMulti as IBMBluePagesSLAPHAPIMulti;
use itdq\Ocean\BluePagesSLAPHAPIMulti as OceanBluePagesSLAPHAPIMulti;

class TableDataUpdater
{
    protected $unknown;
    protected $table;

	// function should be moved to BluePages class
	static function readBatchOfBpEntriesIntranetId($resultSet, $numberOfRowsToReturn = 10)
    {
        $rowCounter = 1;
        $batchOfCnums = false;
        while ($rowCounter <= $numberOfRowsToReturn) {
            if (($row = sqlsrv_fetch_array($resultSet)) == false) {
                break;    /* You could also write 'break 1;' here. */
            } else {
                $batchOfCnums[] = trim($row['INTRANET_ID']);
            }
            $rowCounter++;
        }
        return $batchOfCnums;
    }

    function __construct(DBTable $table) {
        $this->unknown = 'unknown';
        $this->table = $table;
	}

    public function populateDataFromBluepages($resultSet, $callback, $column){
        $startTime = microtime(true);
        echo "Begining now :" . microtime();

        // $batchSize = 100;
        $batchSize = 50;
        $allDetails = array();

        $totalRecordsProcessed = 0;

        // read intranet ids from db
        while(($batchOfIds = self::readBatchOfBpEntriesIntranetId($resultSet, $batchSize)) == true) {
            $totalRecordsProcessed += count($batchOfIds);
            
            // read employee details from BP
            // IBMBluePagesSLAPHAPIMulti::getAllDetailsFromIntranetIdsSlapMulti($batchOfIds, $allDetails, true);
            // OceanBluePagesSLAPHAPIMulti::getAllDetailsFromIntranetIdsSlapMulti($batchOfIds, $allDetails, true);
            BluePagesSLAPHAPIMulti::getAllDetailsFromIntranetIdsSlapMulti($batchOfIds, $allDetails, true);

            // call callback function
            $callback($batchOfIds, $allDetails, $column);

            echo 'Amount of processed Ids '.$totalRecordsProcessed;
        }
        
        $endTime = microtime(true);
        
        echo "<br/>Process complete : " . ($endTime - $startTime);
        echo "<br/>People to lookup : " . $totalRecordsProcessed;
        echo "<br/>People Found in BP : " . count($allDetails);
        echo "<br/>People Not Found in BP : " . ($totalRecordsProcessed - count($allDetails));
        
        unset($batchOfIds);
        unset($allDetails);
        
        ?><hr/><?php
    }
}
?>