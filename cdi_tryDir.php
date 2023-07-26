<?php
use itdq\Trace;


echo "<div class='container'>";

$personA1 = array('ACCOUNT'=>'RBS','COUNTRY'=>'India', 'STATUS'=>'Contractor');
$personA2 = array('ACCOUNT'=>'RBS','COUNTRY'=>'India', 'STATUS'=>'Regular');
$personA3 = array('ACCOUNT'=>'RBS','COUNTRY'=>'Poland', 'STATUS'=>'Contractor');
$personA4 = array('ACCOUNT'=>'RBS','COUNTRY'=>'Poland', 'STATUS'=>'Regular');
$personA5 = array('ACCOUNT'=>'RBS','COUNTRY'=>'UK', 'STATUS'=>'Regular');
$personA6 = array('ACCOUNT'=>'RBS','COUNTRY'=>'UK', 'STATUS'=>'Contractor');



$personB1 = array('ACCOUNT'=>'LLoyds','COUNTRY'=>'India', 'STATUS'=>'Regular');
$personB2 = array('ACCOUNT'=>'LLoyds','COUNTRY'=>'India', 'STATUS'=>'Contractor');
$personB3 = array('ACCOUNT'=>'LLoyds','COUNTRY'=>'Poland', 'STATUS'=>'Regular');
$personB4 = array('ACCOUNT'=>'LLoyds','COUNTRY'=>'Poland', 'STATUS'=>'Contractor');
$personB5 = array('ACCOUNT'=>'LLoyds','COUNTRY'=>'UK', 'STATUS'=>'Regular');
$personB6 = array('ACCOUNT'=>'LLoyds','COUNTRY'=>'UK', 'STATUS'=>'Contractor');





// $people = array($person1,$person2,$person3,$person4,$person5,$person6,$person7,$person8,$person9,$person10,$person11);
$people = array($personA1,$personA2,$personA3,$personA4,$personA5,$personA6,$personB1,$personB2,$personB3,$personB4,$personB5,$personB6);

// $one   = file_exists('emailAttachments');
// $two   = file_exists('emailAttachments/rob');
// $three = file_exists('emailAttachments/rob/India');




// var_dump($one);
// var_dump($two);
// var_dump($three);


function findConsentForm(array $person, $fileNamePattern = null, $subFolder='consentForms'){
    if(empty($person) or empty($fileNamePattern)){
        throw new \Exception('Incorrect parms passed');
    }

    $pathToAccountCountryStatus     = "emailAttachments/$subFolder/" . $person['ACCOUNT'] . "/" . $person['COUNTRY'] . "/" . $person['STATUS'];
    $pathToAccountCountry           = "emailAttachments/$subFolder/" . $person['ACCOUNT'] . "/" . $person['COUNTRY'];
    $pathToAccountStatus            = "emailAttachments/$subFolder/" . $person['ACCOUNT'] . "/" . $person['STATUS'];

    $pathToAccount                  = "emailAttachments/$subFolder/" . $person['ACCOUNT'];

    $pathToCountry                  = "emailAttachments/$subFolder/" . $person['COUNTRY'];
    $pathToCountryStatus            = "emailAttachments/$subFolder/" . $person['COUNTRY'] . "/" . $person['STATUS'];

    $pathToStatus                   = "emailAttachments/$subFolder/" . $person['STATUS'];

    $pathToDefault    = "emailAttachments/$subFolder";

    $pathsToTry = array($pathToAccountCountryStatus,$pathToAccountCountry, $pathToAccountStatus
        , $pathToAccount
        , $pathToCountry, $pathToCountryStatus
        , $pathToStatus
        , $pathToDefault);

    $pathFound = false;
    $consentFound = false;
    $pathIndex = 0;
    while(!$consentFound && $pathIndex < count($pathsToTry)){
        $pathToTest = $pathsToTry[$pathIndex];
        $pathFound = file_exists($pathToTest);
        $pathIndex++;

        $pathFoundYesNo = $pathFound ? "Yes" : "No";

        if($pathFound){
            $filesFound = scandir($pathToTest);
        //    $consentPattern = '/(.*?)consent(.*?).(.*?)/i';
            $consentFiles = preg_grep($fileNamePattern,$filesFound);
            $consentFiles = array_values($consentFiles);

            if(!empty($consentFiles[0])){
                $consentFound = true;
                return $consentFiles[0];
            }
        }
    }
    return false;
}


foreach ($people as $person){

    $consentForm = findConsentForm($person,'/(.*?)consent(.*?).(.*?)/i');
    $odcForm = findConsentForm($person,'/(.*?)odc(.*?).(.*?)/i','odcForms');

    ?>
    <div class='row'>
    <div class='col-sm-1'><b><?=$person['STATUS']?></b></div>
    <div class='col-sm-1'><b><?=$person['ACCOUNT']?></b></div>
    <div class='col-sm-1'><b><?=$person['COUNTRY']?></b></div>
    <div class='col-sm-4'><b><?=$consentForm ? $consentForm : "Not found";?></b></div>
    <div class='col-sm-5'><b><?=$odcForm ? $odcForm : "Not found";?></b></div>
    </div>
	<?php


}


