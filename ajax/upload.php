<?php
<<<<<<< HEAD
$target_dir = "../ots_uploads/";
$target_file = $target_dir . basename($_FILES["file"]["name"]);
$uploadOk = 1;
$fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

ob_clean();
=======

use itdq\BluePages;

// $rootDir = stripos($_ENV['environment'], 'dev')  ? '../' : '/';
$rootDir = '../';

$target_dir = $rootDir . "uploads/" . $_ENV['environment'];
$target_file = $target_dir . "_" . basename($_FILES["file"]["name"]);

$fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

$info = pathinfo($target_file);
$target_file_name = $target_dir . "_" . basename($_FILES["file"]["name"], '.'.$info['extension']);

$new_file = $target_file_name . ' - Line of Business';
$new_file_name = $new_file . $fileType;

$uploadOk = 1;

$scrap = ob_get_contents();

$now = new DateTime();

echo $now->format('Y-m-d H:i:s');
>>>>>>> 481c0dfe9947cef192191baa1c37e1d1ccd89b8e

// Check if file already exists
if (file_exists($target_file)) {
    $uploadOk = unlink($target_file);
<<<<<<< HEAD
    echo $uploadOk ? "Previous File deleted." : "Problem deleting previous file";

}
// Check file size
if ($_FILES["file"]["size"] > 500000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}
// Allow certain file formats
if($fileType != "xls" && $fileType != "xlsx" ) {
        echo "Sorry, only XLS & XLSX files are allowed.";
        $uploadOk = 0;
    }
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        chmod($target_file, 0755);
        echo "<br/>The file <b>". basename( $_FILES["file"]["name"]). "</b> has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
=======
    echo $uploadOk ? "<br/>Previous File deleted." : "<br/>Problem deleting previous file";
}

// Allow certain file formats
if($fileType != "csv" ) {
    echo "Sorry, only CSV files are allowed.";
    $uploadOk = 0;
}

// Check file size
if ($_FILES["file"]["size"] > 50000000) {
    echo "Sorry, your file is too large.";
    print_r($_FILES);
    $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
    die('here');
    // if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        echo "<br/>The file <b>". basename( $_FILES["file"]["name"]). "</b> has been uploaded to the server.";

        $allEmployees = array();
        $allEmails = array();
        $allCnums = array();
        $allAccounts = array();
        $details = array();

        $bpParms = "preferredidentity&jobresponsibilities&notesemail&uid&preferredfirstname&hrfirstname&sn&hrfamilyname&ismanager&phonemailnumber&employeetype&co&ibmloc&hrorganizationcode";
        // $bpParms = "hrorganizationcode";

        // open uploaded file
        $handle = fopen($target_file, 'r');
        if ($handle === false) {
            echo "Sorry, unable to read uploaded file.";
            die('here');            
        }

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

        // echo "<pre>";

        $iterationCounter = 0;
        $bpDefinition = array();
        $allOrganizationCodes = array();

        foreach ($chunkedCnum as $key => $cnumList){
            $details = BluePages::getDetailsFromCnumSlapMulti($cnumList,$bpParms);
            ob_clean();
            // echo "<br/>";
            // echo "count cnumList ".count($cnumList);
            // echo "<hr/>";
            $cnum="";
            foreach ($details->search->entry as $bpEntry){
                // echo "<br/>";
                // echo "count details ".count($details->search->entry);
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
                        // echo "<br/>";
                        // echo "<b>" . $individualAttributes . "</b>";
                        $cnum = substr($individualAttributes,4,9);            
                        // echo " ".$cnum;
                    }
            
                }
                // echo "<hr/>";
            }
            $iterationCounter++;
        }

        // print_r($bpDefinition);
        // echo "<br/>";
        // echo "count bpDefinition ".count($bpDefinition);

        // echo "<br/>";
        // print_r($allOrganizationCodes);
        // echo "<br/>";
        // echo "count allOrganizationCodes ".count($allOrganizationCodes);

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
        // echo "<br/>";
        // print_r($allEmployees);
        // echo "<br/>";
        // echo "count allEmployees ".count($allEmployees);

        // echo "<br/>";
        // echo "Number of iterations ".$iterationCounter;

        echo "<br/>";
        echo 'Employees Found number ' . $foundEmployess;
        echo "<br/>";
        echo 'Missing Employess number ' . $missingEmployess;

        echo "<br/>";
        echo 'Organizations Found number ' . $foundOrganizations;
        echo "<br/>";
        echo 'Missing Organizations number ' . $missingOrganizations;

        // save new file
        $handle = fopen($new_file_name,'w+');
        if ($handle === false) {
            echo "Sorry, unable to write output file.";
            die('here');            
        }

        fputcsv($handle, $fileHeaders);
        foreach ($allEmployees as $employee) {
            fputcsv($handle, $employee);
        }

        fclose($handle);

        echo "<br/>";
        echo "<hr>";
        echo "<br/>";
        echo "<a href='".$new_file_name."'>Download modified file " . $new_file . "</a>";
        // echo "</pre>";

    } else {
        echo "Sorry, there was an error uploading your file.";
    }

>>>>>>> 481c0dfe9947cef192191baa1c37e1d1ccd89b8e
}