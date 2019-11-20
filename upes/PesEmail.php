<?php
namespace upes;

use itdq\DbTable;
use upes\AllTables;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use upes\PersonTable;
use itdq\BlueMail;

class PesEmail {

    const CONSENT_PATTERN = '/(.*?)consent(.*?).(.*?)/i';
    const ODC_PATTERN = '/(.*?)odc(.*?).(.*?)/i';
    const EMAIL_PATTERN = '/(.*?)email(.*?).(.*?)/i';
    const APPLICATION_PATTERN = '/(.*?)application(.*?).(.*?)/i';

    const EMAIL_ROOT_ATTACHMENTS   = 'emailAttachments';
    const EMAIL_BODIES             = 'emailBodies';
    const EMAIL_APPLICATION_FORMS  = 'applicationForms';

    const APPLICATION_FORM_GLOBAL = 'FSS Global Application Form v1.0.doc';
    const APPLICATION_FORM_ODC    = 'ODC application form v3.0.xls';
    const APPLICATION_FORM_OWENS  = 'Owens_Consent_Form.pdf';

    const EMAIL_SUBJECT          = "IBM Confidential: URGENT - &&account_name&&  Pre Employment Screening- &&serial_number&& &&candidate_name&&";
    const APPLICATION_FORM_KEY   = array(''=>'','odc'=>self::APPLICATION_FORM_ODC,'owens'=>self::APPLICATION_FORM_OWENS);

    static private function getGlobalApplicationForm(){
        // LLoyds Global Application Form v1.4.doc
        // $filename = "../emailAttachments/LLoyds Global Application Form v1.4.doc";
        $filename = "../". self::EMAIL_ROOT_ATTACHMENTS . "/". self::EMAIL_APPLICATION_FORMS . "/" . self::APPLICATION_FORM_GLOBAL;


        $handle = fopen($filename, "r");
        $applicationForm = fread($handle, filesize($filename));
        fclose($handle);
        return base64_encode($applicationForm);
    }

    static private function getOwensConsentForm(){
        //$filename = "../emailAttachments/New Overseas Consent Form GDPR.pdf";
        $filename = "../" .  self::EMAIL_ROOT_ATTACHMENTS . "/". self::EMAIL_APPLICATION_FORMS . "/" . self::APPLICATION_FORM_OWENS;
        $handle = fopen($filename, "r",true);
        $applicationForm = fread($handle, filesize($filename));
        fclose($handle);
        return base64_encode($applicationForm);

    }

    static private function getOdcApplicationForm(){
        //$inputFileName = '../emailAttachments/ODC application form V2.0.xls';
        $inputFileName = "../" . self::EMAIL_ROOT_ATTACHMENTS . "/". self::EMAIL_APPLICATION_FORMS . "/" . self::APPLICATION_FORM_ODC;
        /** Load $inputFileName to a Spreadsheet Object  **/
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);

        // $spreadsheet = new Spreadsheet();
        // Set document properties
        $spreadsheet->getProperties()->setCreator('uPES')
        ->setLastModifiedBy('uPES')
        ->setTitle('PES Application Form generated by uPES')
        ->setSubject('PES Application')
        ->setDescription('PES Application Form generated by uPES')
        ->setKeywords('office 2007 openxml php upes tracker')
        ->setCategory('category');

        $spreadsheet->getActiveSheet()
        ->getCell('C17')
        ->setValue('Emp no. here');

        $spreadsheet->setActiveSheetIndex(0);
//         ob_clean();
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        ob_start();
        $writer->save('php://output');
        $xlsAttachment = ob_get_clean();

        return base64_encode($xlsAttachment);
    }

    static function findEmailBody($account,$country){
        if(empty($account) or empty($country)){
            throw new Exception('Incorrect parms passed');
        }

        $emailBodyName = CountryTable::getEmailBodyNameForCountry($country);

        $pathToAccountBody     = "../" . self::EMAIL_ROOT_ATTACHMENTS . "/" . self::EMAIL_BODIES . "/" . $account ."/request_" . $emailBodyName['EMAIL_BODY_NAME'] . ".php";
        $pathToDefaultBody     = "../" . self::EMAIL_ROOT_ATTACHMENTS . "/" . self::EMAIL_BODIES . "//request_" . $emailBodyName['EMAIL_BODY_NAME'] . ".php";

        $pathsToTry = array($pathToAccountBody,$pathToDefaultBody);

        $pathFound = false;
        $pathIndex = 0;
        while(!$pathFound && $pathIndex < count($pathsToTry)){
            $pathToTest = $pathsToTry[$pathIndex];
            $pathFound = file_exists($pathToTest);
            $pathIndex++;
            if($pathFound){
                return $pathToTest;
            }
        }
        return false;
    }


    static function sendPesApplicationForms($account, $country, $serial,  $candidateName, $candidate_first_name, $candidateEmail){
        $emailSubjectPattern = array('/&&account_name&& /','/&&serial_number&&/','/&&candidate_name&&/');
        $emailBodyPattern    = array('/&&candidate_first_name&&/','/&&name_of_application_form&&/','/&&account_name&& /');
        $emailBody = '';// overwritten by include

        $applicationFormDetails = self::determinePesApplicationForms($country);
        $nameOfApplicationForm = $applicationFormDetails['nameOfApplicationForm'];
        $pesAttachments        = $applicationFormDetails['pesAttachments'];

        $emailBodyFile = PesEmail::findEmailBody($account, $country);

        include $emailBodyFile;
        $subjectReplacements = array($account,$serial,$candidateName);
        $subject = preg_replace($emailSubjectPattern, $subjectReplacements, PesEmail::EMAIL_SUBJECT);

        $emailBodyReplacements = array($candidate_first_name,$nameOfApplicationForm,$account);
        $email = preg_replace($emailBodyPattern, $emailBodyReplacements, $emailBody);

        // AccountPersonRecord::$pesTaskId[0]

        return BlueMail::send_mail($candidateEmail, $subject, $email, 'daniero@uk.ibm.com',array(),array(),false,$pesAttachments);
    }

    static function determinePesApplicationForms($country){

        $additionalApplicationFormDetails = CountryTable::getAdditionalAttachmentsNameCountry($country);

        $nameOfApplicationForm = "<ul><li><i>" . self::APPLICATION_FORM_GLOBAL . "</i></li>";
        $nameOfApplicationForm.= !empty($additionalApplicationFormDetails['ADDITIONAL_APPLICATION_FORM']) ? "<li><i>" . self::APPLICATION_FORM_KEY[$additionalApplicationFormDetails['ADDITIONAL_APPLICATION_FORM']] . "</i></li>" : null;
        $nameOfApplicationForm.= "</ul>";

        $pesAttachments = array();
        $encodedApplicationForm = self::getGlobalApplicationForm();
        $pesAttachments[] = array('filename'=>self::APPLICATION_FORM_GLOBAL,'content_type'=>'application/msword','data'=>$encodedApplicationForm);

        switch ($additionalApplicationFormDetails['ADDITIONAL_APPLICATION_FORM']) {
            case 'odc':
                $encodedAdditional = self::getOdcApplicationForm();
                $pesAttachments[] = array('filename'=>self::APPLICATION_FORM_ODC,'content_type'=>'application/msword','data'=>$encodedAdditional);
                break;
            case 'owens':
                $encodedAdditional = self::getOwensConsentForm();
                $pesAttachments[] = array('filename'=>self::APPLICATION_FORM_OWENS,'content_type'=>'application/pdf','data'=>$encodedAdditional);
                break;
            default:
                null;
                break;
        }
        return array('pesAttachments'=> $pesAttachments,'nameOfApplicationForm'=>$nameOfApplicationForm);
    }





//     function getEmailDetails($upesRef, $account, $country, $ibmStatus){

//         $person = array('ACCOUNT'=>$account,'COUNTRY'=>$country,'STATUS'=>$ibmStatus);

//         $attachments[] = $this->findFiles($person,self::CONSENT_PATTERN, self::EMAIL_SUB_CONSENT, self::EMAIL_ROUTE_ATTACHMENTS);


//         $emailBody = $this->findFiles($person,self::CONSENT_PATTERN, self::EMAIL_SUB_CONSENT, self::EMAIL_BODIES);

//         return array('filename'=> $pesEmailBodyFilename, 'attachments'=>$attachments);
//     }


    function sendPesEmail($firstName, $lastName, $emailAddress, $country, $openseat, $cnum){
            $emailDetails = $this->getEmailDetails($emailAddress, $country);
            $emailBodyFileName = $emailDetails['filename'];
            $pesAttachments = $emailDetails['attachments'];
            $replacements = array($firstName,$openseat);

            include_once self::EMAIL_ROOT_ATTACHMENTS . '/' . self::EMAIL_BODIES . '/' . $emailBodyFileName;
            $emailBody = preg_replace($pesEmailPattern, $replacements, $pesEmail);

            $sendResponse = BlueMail::send_mail(array($emailAddress), "NEW URGENT - Pre Employment Screening - $cnum : $firstName, $lastName", $emailBody,'LBGVETPR@uk.ibm.com',array(),array(),false,$pesAttachments);
            return $sendResponse;

    }

    function sendPesEmailChaser($upesref, $account, $emailAddress, $chaserLevel){

        $pesEmailPattern = array(); // Will be overridden when we include_once from emailBodies later.
        $pesEmail = null;          // Will be overridden when we include_once from emailBodies later.
        $names = PersonTable::getNamesFromUpesref($upesref);
        $fullName = $names['FULL_NAME'];
        $requestor = trim($_POST['requestor']);

        $emailBodyFileName = 'chaser' . trim($chaserLevel) . ".php";
        $replacements = array($fullName,$account);

        include_once self::EMAIL_ROOT_ATTACHMENTS . '/' . self::EMAIL_BODIES . '/' . $emailBodyFileName;
        $emailBody = preg_replace($pesEmailPattern, $replacements, $pesEmail);

        $sendResponse = BlueMail::send_mail(array($emailAddress), "PES Reminder - $fullName($upesref) on $account", $emailBody,'LBGVETPR@uk.ibm.com',array($requestor));
        return $sendResponse;


    }

    function sendPesProcessStatusChangedConfirmation($upesref, $account,  $fullname, $emailAddress, $processStatus, $requestor=null){

        $pesEmailPattern = array(); // Will be overridden when we include_once from emailBodies later.
        $pesEmail = null;          // Will be overridden when we include_once from emailBodies later.

        $emailBodyFileName = 'processStatus' . trim($processStatus) . ".php";
        $replacements = array($fullname, $account);

        include_once self::EMAIL_ROOT_ATTACHMENTS . '/' . self::EMAIL_BODIES . '/' . $emailBodyFileName;
        $emailBody = preg_replace($pesEmailPattern, $replacements, $pesEmail);

        return BlueMail::send_mail(array($emailAddress), "PES Status Change - $fullname($upesref) : $account", $emailBody,'LBGVETPR@uk.ibm.com', array($requestor));
    }


    static function notifyPesTeamOfUpcomingRechecks($detialsOfPeopleToBeRechecked=null){

        $now = new \DateTime();
        $pesEmail = null;          // Will be overridden when we include_once from emailBodies later.

        include_once self::EMAIL_ROOT_ATTACHMENTS . '/' . self::EMAIL_BODIES . '/recheckReport.php';

        $pesEmail.= "<h4>Generated by uPes: " . $now->format('jS M Y') . "</h4>";

        $pesEmail.= "<table border='1' style='border-collapse:collapse;'  >";
        $pesEmail.= "<thead style='background-color: #cce6ff; padding:25px;'>";
        $pesEmail.= "<tr><th style='padding:25px;'>CNUM</th><th style='padding:25px;'>Full Name</th><th style='padding:25px;'>Account</th><th style='padding:25px;'>PES Status</th><th style='padding:25px;'>Recheck Date</th>";
        $pesEmail.= "</tr></thead><tbody>";

        foreach ($detialsOfPeopleToBeRechecked as $personToBeRechecked) {
            $pesEmail.="<tr><td style='padding:15px;'>" . $personToBeRechecked['CNUM'] . "</td><td style='padding:15px;'>" . $personToBeRechecked['FULL_NAME']  . "</td><td style='padding:15px;'>" . $personToBeRechecked['ACCOUNT']  . "</td><td style='padding:15px;'>" . $personToBeRechecked['PES_STATUS'] . "</td><td style='padding:15px;'>" . $personToBeRechecked['PES_RECHECK_DATE'] . "</td></tr>";
        }

        $pesEmail.="</tbody>";
        $pesEmail.="</table>";

        $pesEmail.= "<style> th { background:red; padding:!5px; } </style>";

        $emailBody = $pesEmail;

        $sendResponse = BlueMail::send_mail(array('LBGVETPR@uk.ibm.com'), "UPES Upcoming Rechecks", $emailBody,'LBGVETPR@uk.ibm.com');
        return $sendResponse;


    }


    static function notifyPesTeamNoUpcomingRechecks(){

        $now = new \DateTime();
        $pesEmail = null;          // Will be overridden when we include_once from emailBodies later.

        include_once self::EMAIL_ROOT_ATTACHMENTS . '/' . self::EMAIL_BODIES . '/recheckReport.php';

        $pesEmail.= "<h4>Generated by uPes: " . $now->format('jS M Y') . "</h4>";
        $pesEmail.= "<p>No upcoming rechecks have been found</p>";
        $emailBody = $pesEmail;

        $sendResponse = BlueMail::send_mail(array('LBGVETPR@uk.ibm.com'), "Upcoming Rechecks-None", $emailBody,'LBGVETPR@uk.ibm.com');
        return $sendResponse;


    }



}
