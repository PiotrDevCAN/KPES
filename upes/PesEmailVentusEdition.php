<?php
namespace upes;

use itdq\DbTable;
use upes\AllTables;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use upes\PersonTable;
use itdq\BlueMail;

class PesEmailVentusEdition {

    const CONSENT_PATTERN = '/(.*?)consent(.*?).(.*?)/i';
    const ODC_PATTERN = '/(.*?)odc(.*?).(.*?)/i';
    const EMAIL_PATTERN = '/(.*?)email(.*?).(.*?)/i';
    const APPLICATION_PATTERN = '/(.*?)application(.*?).(.*?)/i';

    const EMAIL_ROOT_ATTACHMENTS = 'emailAttachments';

    const EMAIL_SUB_CONSENT       = 'consentForms';
    const EMAIL_SUB_ODC           = 'odcForms';
    const EMAIL_SUB_APPLICATON    = 'applicationForms';
    const EMAIL_SUB_BODIES        = 'emailBodies';

    private function getLloydsGlobalApplicationForm(){
        $filename = "../emailAttachments/LLoyds Global Application Form v1.4.doc";
        $handle = fopen($filename, "r");
        $applicationForm = fread($handle, filesize($filename));
        fclose($handle);
        $encodedApplicationForm = base64_encode($applicationForm);
        return $encodedApplicationForm;
    }

    private function getOverseasConsentForm(){
        $filename = "../emailAttachments/New Overseas Consent Form GDPR.pdf";
        $handle = fopen($filename, "r");
        $applicationForm = fread($handle, filesize($filename));
        fclose($handle);
        $encodedApplicationForm = base64_encode($applicationForm);
        return $encodedApplicationForm;
    }

    private function getOdcApplicationForm(){
        $inputFileName = '../emailAttachments/ODC application form V2.0.xls';

        /** Load $inputFileName to a Spreadsheet Object  **/
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);

        // $spreadsheet = new Spreadsheet();
        // Set document properties
        $spreadsheet->getProperties()->setCreator('vBAC')
        ->setLastModifiedBy('vBAC')
        ->setTitle('Ventus PES application generated from vBAC')
        ->setSubject('Ventus PES application')
        ->setDescription('Ventus PES application generated from vBAC')
        ->setKeywords('office 2007 openxml php vbac tracker')
        ->setCategory('testing 1 2 3');

        $spreadsheet->getActiveSheet()
        ->getCell('C17')
        ->setValue('Emp no. here');

        $spreadsheet->setActiveSheetIndex(0);
//         ob_clean();
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        ob_start();
        $writer->save('php://output');
        $xlsAttachment = ob_get_clean();

        $encodedXlsAttachment = base64_encode($xlsAttachment);
        return $encodedXlsAttachment;
    }


    private function determineInternalExternal($emailAddress){
        $ibmEmail = stripos(strtolower($emailAddress), "ibm.com") !== false;

        return $ibmEmail ? 'Internal' : 'External';
    }

    private function getAttachments(array $attachmentsToLoad){
        switch (true) {
            case $intExt=='External' && $emailType=='UK':
                $encodedApplicationForm = $this->getLloydsGlobalApplicationForm();
                $pesAttachments        = array(array('filename'=>'Lloyds Global Application Form v1.4.doc','content_type'=>'application/msword','data'=>$encodedApplicationForm)
                );
                break;
            case $intExt=='Internal' && $emailType=='UK':
                $encodedApplicationForm = $this->getLloydsGlobalApplicationForm();
                $pesAttachments = array(array('filename'=>'Lloyds Global Application Form v1.4.doc','content_type'=>'application/msword','data'=>$encodedApplicationForm)
                );
                break;
            case $intExt=='External' && $emailType=='India':
                $encodedXlsAttachment = $this->getOdcApplicationForm();
                $encodedApplicationForm = $this->getLloydsGlobalApplicationForm();
                $pesAttachments = array(array('filename'=>'ODC application form V2.0.xlsx','content_type'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','data'=>$encodedXlsAttachment)
                    ,array('filename'=>'Lloyds Global Application Form v1.4.doc','content_type'=>'application/msword','data'=>$encodedApplicationForm)
                );
                break;
            case $intExt=='Internal' && $emailType=='India':
                $encodedApplicationForm = $this->getLloydsGlobalApplicationForm();
                $encodedXlsAttachment = $this->getOdcApplicationForm();
                $pesAttachments = array(array('filename'=>'Lloyds Global Application Form v1.4.doc','content_type'=>'application/msword','data'=>$encodedApplicationForm)
                    ,array('filename'=>'ODC application form V2.0.xlsx','content_type'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','data'=>$encodedXlsAttachment)
                );
                break;
            case $emailType=='Czech':
                $encodedApplicationForm = $this->getLloydsGlobalApplicationForm();
                $pesAttachments = array(array('filename'=>'Lloyds Global Application Form v1.4.doc','content_type'=>'application/msword','data'=>$encodedApplicationForm)
                );
                break;
            case $emailType=='USA':
                $encodedConsentForm = $this->getOverseasConsentForm();
                $encodedApplicationForm = $this->getLloydsGlobalApplicationForm();
                $pesAttachments = array(array('filename'=>'Lloyds Global Application Form v1.4.doc','content_type'=>'application/msword','data'=>$encodedApplicationForm)
                    ,array('filename'=>'New Overseas Consent Form GDPR.pdf','content_type'=>'application/pdf','data'=>$encodedConsentForm)
                );
                break;
            case $emailType=='core_4':
                $encodedApplicationForm = $this->getLloydsGlobalApplicationForm();
                $pesAttachments = array(array('filename'=>'Lloyds Global Application Form v1.4.doc','content_type'=>'application/msword','data'=>$encodedApplicationForm)
                );
                break;
            case $intExt=='Internal' && $emailType=='International_CRC':
                $encodedApplicationForm = $this->getLloydsGlobalApplicationForm();
                $encodedConsentForm = $this->getOverseasConsentForm();
                $pesAttachments = array(array('filename'=>'Lloyds Global Application Form v1.4.doc','content_type'=>'application/msword','data'=>$encodedApplicationForm)
                    ,array('filename'=>'New Overseas Consent Form GDPR.pdf','content_type'=>'application/pdf','data'=>$encodedConsentForm)
                );
                break;
            case $intExt=='Internal' && $emailType=='International_Credit_Check':
                $encodedApplicationForm = $this->getLloydsGlobalApplicationForm();
                $encodedConsentForm = $this->getOverseasConsentForm();
                $pesAttachments = array(array('filename'=>'Lloyds Global Application Form v1.4.doc','content_type'=>'application/msword','data'=>$encodedApplicationForm)
                    ,array('filename'=>'New Overseas Consent Form GDPR.pdf','content_type'=>'application/pdf','data'=>$encodedConsentForm)
                );
                break;
            default:

                throw new Exception('No matches found for ' . $intExt . ' and ' . $emailType, 803);
                ;
                break;
        }

        return $pesAttachments;
    }

    private function findFiles(array $personDetails, $fileNamePattern = null, $subFolder=self::EMAIL_SUB_CONSENT, $baseFolder=self::EMAIL_ROOT_ATTACHMENTS){
        if(empty($personDetails) or empty($fileNamePattern)){
            throw new Exception('Incorrect parms passed');
        }

        $pathToAccountCountryStatus     = "$baseFolder/$subFolder/" . $personDetails['ACCOUNT'] . "/" . $personDetails['COUNTRY'] . "/" . $personDetails['STATUS'];
        $pathToAccountCountry           = "$baseFolder/$subFolder/" . $personDetails['ACCOUNT'] . "/" . $personDetails['COUNTRY'];
        $pathToAccountStatus            = "$baseFolder/$subFolder/" . $personDetails['ACCOUNT'] . "/" . $personDetails['STATUS'];
        $pathToAccount                  = "$baseFolder/$subFolder/" . $personDetails['ACCOUNT'];
        $pathToCountry                  = "$baseFolder/$subFolder/" . $personDetails['COUNTRY'];
        $pathToCountryStatus            = "$baseFolder/$subFolder/" . $personDetails['COUNTRY'] . "/" . $personDetails['STATUS'];
        $pathToStatus                   = "$baseFolder/$subFolder/" . $personDetails['STATUS'];

        $pathToDefault    = "$baseFolder/$subFolder";

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
            if($pathFound){
                $filesFound = scandir($pathToTest);
                $consentFiles = preg_grep($fileNamePattern,$filesFound);
                $consentFiles = array_values($consentFiles);

                if(!empty($consentFiles[0])){
                    $consentFound = true;
                    return $pathFound .  $consentFiles[0];
                }
            }
        }
        return false;
    }




    function getEmailDetails($upesRef, $account, $country, $ibmStatus){

        $person = array('ACCOUNT'=>$account,'COUNTRY'=>$country,'STATUS'=>$ibmStatus);

        $attachments[] = $this->findFiles($person,self::CONSENT_PATTERN, self::EMAIL_SUB_CONSENT, self::EMAIL_ROUTE_ATTACHMENTS);


        $emailBody = $this->findFiles($person,self::CONSENT_PATTERN, self::EMAIL_SUB_CONSENT, self::EMAIL_ROOT_BODIES);

        return array('filename'=> $pesEmailBodyFilename, 'attachments'=>$attachments);
    }


    function sendPesEmail($firstName, $lastName, $emailAddress, $country, $openseat, $cnum){
            $emailDetails = $this->getEmailDetails($emailAddress, $country);
            $emailBodyFileName = $emailDetails['filename'];
            $pesAttachments = $emailDetails['attachments'];
            $replacements = array($firstName,$openseat);

            include_once 'emailBodies/' . $emailBodyFileName;
            $emailBody = preg_replace($pesEmailPattern, $replacements, $pesEmail);

            $sendResponse = BlueMail::send_mail(array($emailAddress), "NEW URGENT - Pre Employment Screening - $cnum : $firstName, $lastName", $emailBody,'LBGVETPR@uk.ibm.com',array(),array(),false,$pesAttachments);
            return $sendResponse;

    }

    function sendPesEmailChaser($upesref, $account, $emailAddress, $chaserLevel, $requestor){

        $pesEmailPattern = array(); // Will be overridden when we include_once from emailBodies later.
        $pesEmail = null;          // Will be overridden when we include_once from emailBodies later.
        $names = PersonTable::getNamesFromUpesref($upesref);
        $fullName = $names['FULL_NAME'];

        $emailBodyFileName = 'chaser' . trim($chaserLevel) . ".php";
        $replacements = array($fullName,$account);

        include_once 'emailBodies/' . $emailBodyFileName;
        $emailBody = preg_replace($pesEmailPattern, $replacements, $pesEmail);

        $sendResponse = BlueMail::send_mail(array($emailAddress), "PES Reminder - $fullName($upesref) on $account", $emailBody,'LBGVETPR@uk.ibm.com',array($requestor));
        return $sendResponse;


    }

    function sendPesProcessStatusChangedConfirmation($upesref, $account,  $fullname, $emailAddress, $processStatus, $requestor=null){

        $pesEmailPattern = array(); // Will be overridden when we include_once from emailBodies later.
        $pesEmail = null;          // Will be overridden when we include_once from emailBodies later.

        $emailBodyFileName = 'processStatus' . trim($processStatus) . ".php";
        $replacements = array($fullname, $account);

        include_once 'emailBodies/' . $emailBodyFileName;
        $emailBody = preg_replace($pesEmailPattern, $replacements, $pesEmail);

        return BlueMail::send_mail(array($emailAddress), "PES Status Change - $fullname($upesref) : $account", $emailBody,'LBGVETPR@uk.ibm.com', array($requestor));
    }


    static function notifyPesTeamOfUpcomingRechecks($detialsOfPeopleToBeRechecked=null){

        $now = new \DateTime();
        $pesEmail = null;          // Will be overridden when we include_once from emailBodies later.

        include_once 'emailBodies/recheckReport.php';

        $pesEmail.= "<h4>Generated by vBac: " . $now->format('jS M Y') . "</h4>";

        $pesEmail.= "<table border='1' style='border-collapse:collapse;'  ><thead style='background-color: #cce6ff; padding:25px;'><tr><th style='padding:25px;'>CNUM</th><th style='padding:25px;'>Notes ID</th><th style='padding:25px;'>PES Status</th><th style='padding:25px;'>Revalidation Status</th><th style='padding:25px;'>Recheck Date</th></tr></thead><tbody>";

        foreach ($detialsOfPeopleToBeRechecked as $personToBeRechecked) {
            $pesEmail.="<tr><td style='padding:15px;'>" . $personToBeRechecked['CNUM'] . "</td><td style='padding:15px;'>" . $personToBeRechecked['NOTES_ID']  . "</td><td style='padding:15px;'>" . $personToBeRechecked['PES_STATUS'] . "</td><td style='padding:15px;'>" . $personToBeRechecked['REVALIDATION_STATUS'] . "</td><td style='padding:15px;'>" . $personToBeRechecked['PES_RECHECK_DATE'] . "</td></tr>";
            ;
        }

        $pesEmail.="</tbody></table>";

        $pesEmail.= "<style> th { background:red; padding:!5px; } </style>";



        $emailBody = $pesEmail;

        $sendResponse = BlueMail::send_mail(array('LBGVETPR@uk.ibm.com'), "Upcoming Rechecks", $emailBody,'LBGVETPR@uk.ibm.com');
        return $sendResponse;


    }


    static function notifyPesTeamNoUpcomingRechecks(){

        $now = new \DateTime();
        $pesEmail = null;          // Will be overridden when we include_once from emailBodies later.

        include_once 'emailBodies/recheckReport.php';

        $pesEmail.= "<h4>Generated by vBac: " . $now->format('jS M Y') . "</h4>";
        $pesEmail.= "<p>No upcoming rechecks have been found</p>";
        $emailBody = $pesEmail;

        $sendResponse = BlueMail::send_mail(array('LBGVETPR@uk.ibm.com'), "Upcoming Rechecks-None", $emailBody,'LBGVETPR@uk.ibm.com');
        return $sendResponse;


    }



}
