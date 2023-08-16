<?php
namespace itdq;

use itdq\DbTable;

/**
 * Interfaces to the RCA_SUB_CAUSE_CATEGORY table, basically by inserting entries.
 *
 * @author GB001399
 * @package esoft
 *
 *
 */
class RcaCauseSubCategoryTable extends DbTableTable
{

    static function getAsXML($predicate = null)
    {
        $sql = " Select * ";
        $sql .= " FROM " . $GLOBALS['Db2Schema'] . "." . AllItdqTables::$RCA_CAUSE_SUB_CATEGORY;
        $sql .= " WHERE 1=1 " . $predicate;

        $rs = sqlsrv_query($GLOBALS['conn'], $sql);

        if (! $rs) {
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
        }

        $xmlString = "<rcaCategories>";
        while (($row = sqlsrv_fetch_array($rs)) == true) {
            $xmlString .= "<rcaSubCategory>";
            foreach ($row as $key => $value) {
                $xmlString .= "<" . trim($key) . ">" . urlencode(trim($value)) . "</" . trim($key) . ">";
            }
            $xmlString .= "</rcaSubCategory>";
        }
        $xmlString .= "</rcaCategories>";
        return $xmlString;
    }

    static function getAsArrays($predicate = null)
    {
        $rcaCauseSubCategoryXMLString = RcaCauseSubCategoryTable::getAsXML($predicate);

        $rcaCauseSubCategoryDom = new \DOMDocument();
        $rcaCauseSubCategoryDom->loadXML($rcaCauseSubCategoryXMLString);

        $rcaCauseSubCategories = $rcaCauseSubCategoryDom->getElementsByTagName('CAUSE_SUB_CATEGORY');

        foreach ($rcaCauseSubCategories as $rcaCauseElement) {
            $parent = $rcaCauseElement->parentNode;
            $majorCategoryList = $parent->getElementsByTagName('MAJOR_CATEGORY');
            $causeCategoryList = $parent->getElementsByTagName('CAUSE_CATEGORY');
            $descriptionList = $parent->getElementsByTagName('DESCRIPTION');
            $majorCategory = urldecode($majorCategoryList->item(0)->nodeValue);
            $causeCategory = urldecode($causeCategoryList->item(0)->nodeValue);
            $rcaCategory = urldecode($rcaCauseElement->nodeValue);
            $description = urldecode($descriptionList->item(0)->nodeValue);
            // echo "<br/> $majorCategory : $causeCategory : $rcaCategory : $description";
            $allMajorCategories[trim($majorCategory)] = trim($majorCategory);
            $allCauseCategories[trim($causeCategory)] = trim($majorCategory);
            if (trim($rcaCategory) != "N/A") {
                $allSubCauseCategories[trim($rcaCategory)] = trim($causeCategory);
            }
        }
        return array(
            'MAJOR_CATEGORIES' => $allMajorCategories,
            'CAUSE_CATEGORIES' => $allCauseCategories,
            'CAUSE_SUB_CATEGORIES' => $allSubCauseCategories
        );
    }



    static function getRcaCauseSubCauseCategories(){

        if(isset($GLOBALS['rcaCauses'])){
            return $GLOBALS['rcaCauses'];
        }


        $url = $GLOBALS['site'] ['rcaCauseSubCategoryURL'];

        $curlHandle = curl_init($url);

        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        ;
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curlHandle, CURLOPT_HEADER, FALSE);
        curl_setopt($curlHandle, CURLOPT_VERBOSE, TRUE);

        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, Array(
            "Accept: application/xml"
            ));

        $httpData = curl_exec($curlHandle);
        $httpInfo = curl_getinfo($curlHandle);
        if ($httpInfo['http_code'] != '200') {
            var_dump($httpInfo);
            throw new \Exception("Unable to retrive RCA CATEGORIES from DPULSE", 460);
        }

        $rcaCauseSubCategoryDom = new \DOMDocument();
        $res = $rcaCauseSubCategoryDom->loadXML($httpData);

        $rcaCauseSubCategories = $rcaCauseSubCategoryDom->getElementsByTagName('CAUSE_SUB_CATEGORY');


        foreach ($rcaCauseSubCategories as $rcaCauseElement) {
            $parent = $rcaCauseElement->parentNode;
            $majorCategoryList = $parent->getElementsByTagName('MAJOR_CATEGORY');
            $causeCategoryList = $parent->getElementsByTagName('CAUSE_CATEGORY');
            $descriptionList = $parent->getElementsByTagName('DESCRIPTION');
            $d3DefinitionList = $parent->getElementsByTagName('D3_DEFINITION');
            $majorCategory = urldecode($majorCategoryList->item(0)->nodeValue);
            $causeCategory = urldecode($causeCategoryList->item(0)->nodeValue);
            $rcaCategory = urldecode($rcaCauseElement->nodeValue);
            $description = urldecode($descriptionList->item(0)->nodeValue);
            $d3Definition = urldecode($d3DefinitionList->item(0)->nodeValue);
            // echo "<br/> $majorCategory : $causeCategory : $rcaCategory : $description";
            $allMajorCategories[trim($majorCategory)] = trim($majorCategory);
            $allCauseCategories[trim($causeCategory)] = trim($majorCategory);
            if (trim($rcaCategory) != "N/A") {
                $allSubCauseCategories[trim($rcaCategory)] = trim($causeCategory);
            }
            $d3DefinitionLookup[$majorCategory][$causeCategory][$rcaCategory] = $d3Definition;
        }

        $GLOBALS['rcaCauses']['MAJOR_CATEGORIES'] = $allMajorCategories;
	    $GLOBALS['rcaCauses']['CAUSE_CATEGORIES'] = $allCauseCategories;
	    $GLOBALS['rcaCauses']['CAUSE_SUB_CATEGORIES'] = $allSubCauseCategories;
	    $GLOBALS['rcaCauses']['D3'] = $d3DefinitionLookup;

	    return $GLOBALS['rcaCauses'];

    }
}

