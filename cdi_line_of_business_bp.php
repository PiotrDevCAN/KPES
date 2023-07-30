<?php
use itdq\BluePages;

ini_set('display_errors',1);
ini_set('display_startup_errors',1);

$allEmployees = array();
$allEmails = array();
$allCnums = array();
$allAccounts = array();
$details = array();

$bpParms = "preferredidentity&jobresponsibilities&notesemail&uid&preferredfirstname&hrfirstname&sn&hrfamilyname&ismanager&phonemailnumber&employeetype&co&ibmloc&hrorganizationcode";
// $bpParms = "hrorganizationcode";

// open uploaded file
$handle = fopen('Line of Business Request 2805.csv','r');

$countryCodeMapping = array(
    '709' => '744',
    '728' => '740',
    '755' => '756' 
);

$fileHeaders = array();

$count = 0;
while (($data = fgetcsv($handle, 0 ,'|')) !== FALSE) {
    $count++;
    if ($count == 1) { 
        $fileHeaders = $data;
        continue; 
    }

    $cnum = $data[1];
    $serial = substr ( $cnum, 0, 6 ); // 6 Digit Serial
    $countryCodeRaw = substr ( $cnum, 6, 3 ); // 3 Digit Country Code

    $countryCode = isset($countryCodeMapping[$countryCodeRaw]) ? $countryCodeMapping[$countryCodeRaw] : $countryCodeRaw;
    $cnum = $serial . $countryCode;
    $data[1] = $cnum;

    $allEmployees[] = $data;
    $allEmails[] = $data[0];
    $allCnums[] = $data[1];
    $allAccounts[] = $data[2];
}

fclose($handle);

$chunkedCnum = array_chunk($allCnums, 250);

echo "<pre>";

$iterationCounter = 0;
$bpDefinition = array();
$allOrganizationCodes = array();

foreach ($chunkedCnum as $key => $cnumList){
    $details = BluePages::getDetailsFromCnumSlapMulti($cnumList,$bpParms);
    echo "<br/>";
    echo "count cnumList ".count($cnumList);
    echo "<hr/>";
    $cnum="";
    foreach ($details->search->entry as $bpEntry){
        echo "<br/>";
        echo "count details ".count($details->search->entry);
        foreach($bpEntry as $individualAttributes){
            if(is_array($individualAttributes)){
                foreach ($individualAttributes as $object){
    
                    switch($object->name){
                        case "preferredfirstname":
                        case "hrfirstname":
                            $bpDefinition[$cnum]['first name'] = $object->value[0];
                            $bpDefinition[$cnum][$object->name] = $object->value[0];
                            break;
                        case "sn":
                        case "hrfamilyname":
                            $bpDefinition[$cnum]['surname'] = $object->value[0];
                            $bpDefinition[$cnum][$object->name] = $object->value[0];
                            break;
                        case "hrorganizationcode":
                            $bpDefinition[$cnum][$object->name] = $object->value[0];
                        default:
                            break;
                    }
    
                    if($object->name=='hrorganizationcode'){
                        $allOrganizationCodes[$object->value[0]] = $object->value[0];
                    }
                }
            } else {
                echo "<br/>";
                echo "<b>" . $individualAttributes . "</b>";
                $cnum = substr($individualAttributes,4,9);            
                echo " ".$cnum;
            }
    
        }
        echo "<hr/>";
    }
    $iterationCounter++;
}

print_r($bpDefinition);
echo "<br/>";
echo "count bpDefinition ".count($bpDefinition);

// echo "<br/>";
// print_r($allOrganizationCodes);
echo "<br/>";
echo "count allOrganizationCodes ".count($allOrganizationCodes);

// Lookup organizations now.

$json = Bluepages::lookupOrganizations($allOrganizationCodes);

$allOrganizations = $json->search->entry;
// echo "<br/>";
// print_r($allOrganizations);

$allOrganizationsDetails = array();
foreach ($allOrganizations as $organization){
    $hrorganizationcode = '';
    $hrorganizationdisplay = '';
    $hrunitid = '';
    $hrgroupid = '';
    foreach ($organization->attribute as $attribute){
        $name = $attribute->name;
        $$name = $attribute->value[0];
    }
    $allOrganizationsDetails[$hrorganizationcode] = array('unitid'=>$hrunitid,'groupid'=>$hrgroupid,'organizationdisplay'=>$hrorganizationdisplay);
}
// echo "<br/>";
// print_r($allOrganizationsDetails);

$foundEmployess = 0;
$missingEmployess = 0;

$foundOrganizations = 0;
$missingOrganizations = 0;

foreach ($allEmployees as $key => $employee){
    $cnum = $employee[1];
    $organizationCode = isset($bpDefinition[$cnum]) ? $bpDefinition[$cnum]['hrorganizationcode'] : null;
    if (!is_null($organizationCode)) {
        $organizationDetails = isset($allOrganizationsDetails[$organizationCode]) ? $allOrganizationsDetails[$organizationCode] : null;
        if (!is_null($organizationDetails)) {
            $organizationDisplay = $organizationDetails['organizationdisplay'];
            $foundOrganizations++;
        } else {
            $organizationDisplay = 'not found in BP Organizations '.$organizationCode;
            $missingOrganizations++;
        }
        $foundEmployess++;
    } else {
        $organizationDisplay = 'not found in BP Employees';
        $missingEmployess++;
    }
    $allEmployees[$key][3] = $organizationDisplay;
}
echo "<br/>";
print_r($allEmployees);
echo "<br/>";
echo "count allEmployees ".count($allEmployees);

echo "<br/>";
echo "Number of iterations ".$iterationCounter;

echo "<br/>";
echo 'Employees Found number ' . $foundEmployess;
echo "<br/>";
echo 'Missing Employess number ' . $missingEmployess;

echo "<br/>";
echo 'Organizations Found number ' . $foundOrganizations;
echo "<br/>";
echo 'Missing Organizations number ' . $missingOrganizations;

// save new file
$handle = fopen('Line of Business Request 2805 - output.csv','w+');

fputcsv($handle, $fileHeaders);
foreach ($allEmployees as $employee) {
    fputcsv($handle, $employee);
}

fclose($handle);

echo "</pre>";