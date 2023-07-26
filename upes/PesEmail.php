<?php
namespace upes;

use itdq\BlueMail;
use itdq\Loader;
use itdq\slack;
use PhpOffice\PhpSpreadsheet\IOFactory;
use upes\AccountPersonTable;
use upes\AllTables;
use upes\CountryTable;
use upes\PersonTable;

class PesEmail
{

    const ODC = 'odc';
    const OWENS = 'owens';
    const VF = 'vf';
    const COUNTRY_POLAND = 'Poland';
    const COUNTRY_CZECH = 'Czech Republic';
    const RECHECK = 'recheck';
    const REQUEST = 'request';
    const YES = 'yes';
    const NO = 'no';
    const INTERNAL = null;
    const EXTERNAL = '_ext';
    const FILE_TYPE_WORD = 'application/msword';
    const FILE_TYPE_PDF = 'application/pdf';
    const CONSENT_PATTERN = '/(.*?)consent(.*?).(.*?)/i';
    const ODC_PATTERN = '/(.*?)odc(.*?).(.*?)/i';
    const EMAIL_PATTERN = '/(.*?)email(.*?).(.*?)/i';
    const APPLICATION_PATTERN = '/(.*?)application(.*?).(.*?)/i';
    const EMAIL_ROOT_ATTACHMENTS = 'emailAttachments';
    const EMAIL_SUBDIRECTORY_COMMON = 'common';
    const EMAIL_SUBDIRECTORY_IBM = 'IBM';
    const EMAIL_SUBDIRECTORY_KYNDRYL = 'Kyndryl';
    const EMAIL_APPLICATION_FORMS = 'applicationForms';
    const EMAIL_BODIES = 'emailBodies';

    // files for IBM
    const IBM_APPLICATION_FORM_GLOBAL_FSS = 'FSS Global Application Form v2.5.doc';
    const IBM_APPLICATION_FORM_GLOBAL_NON_FSS = 'PES Global Application Form v1.2.doc';
    const IBM_APPLICATION_FORM_ODC = 'ODC application form v3.0.xls';

    // files for Kyndryl
    const KYNDRYL_APPLICATION_FORM_GLOBAL_FSS = 'Kyndryl FSS Global Application Form v1.1.doc';
    const KYNDRYL_APPLICATION_FORM_GLOBAL_NON_FSS = 'Kyndryl PES Global Application Form v1.1.doc';
    const KYNDRYL_APPLICATION_FORM_ODC = 'Kyndryl ODC Application Form v1.0.xls';
    const KYNDRYL_APPLICATION_FORM_POLISH_FSS = 'Kyndryl FSS Polish Global Application Form v1.2.doc';

    // common file for both companies
    const APPLICATION_FORM_OWENS = 'Owens_Consent_Form.pdf';
    const APPLICATION_FORM_VF = 'VF Overseas Consent Form.pdf';
    const EMAIL_SUBJECT = "IBM Confidential: URGENT - &&account_name&&  Pre Employment Screening- &&serial_number&& &&candidate_name&&";
    private static $notifyPesEmailAddresses = array(
        'to' => array('carrabooth@uk.ibm.com'),
        'cc' => array('Rsmith1@uk.ibm.com'),
    );

    private static function checkIfIsKyndryl()
    {
        $isKyndryl = stripos($_ENV['environment'], 'newco');
        if ($isKyndryl === false) {
            return false;
        } else {
            return true;
        }
    }

    private static function getApplicationFormFileNameByKey($key = '')
    {
        switch ($key) {
            case self::ODC:
                $fileName = self::getOdcApplicationFormFileName();
                break;
            case self::OWENS:
                $fileName = self::getOwensConsentFormFileName();
                break;
            case self::VF:
                $fileName = self::getVfConsentFormFileName();
                break;
            default:
                $fileName = '';
                break;
        }
        return $fileName;
    }

    private static function addDirectorySeparator($directory = '')
    {
        return $directory . DIRECTORY_SEPARATOR;
    }

    private static function getEmailRootAttachmentsName()
    {
        $directory = self::EMAIL_ROOT_ATTACHMENTS;
        return $directory;
    }

    private static function getEmailCommonSubdirectoryName()
    {
        $directory = self::EMAIL_SUBDIRECTORY_COMMON;
        return $directory;
    }

    private static function getEmailIBMSubdirectoryName()
    {
        $directory = self::EMAIL_SUBDIRECTORY_IBM;
        return $directory;
    }
    private static function getEmailKyndrylSubdirectoryName()
    {
        $directory = self::EMAIL_SUBDIRECTORY_KYNDRYL;
        return $directory;
    }

    private static function getEmailApplicationFormsDirectoryName()
    {
        $directory = self::EMAIL_APPLICATION_FORMS;
        return $directory;
    }

    private static function getEmailBodiesDirectoryName()
    {
        $directory = self::EMAIL_BODIES;
        return $directory;
    }

    private static function getEmailCompanySubdirectory()
    {
        $directory = self::checkIfIsKyndryl() === true ? self::getEmailKyndrylSubdirectoryName() : self::getEmailIBMSubdirectoryName();
        return $directory;
    }

    private static function getRootAttachmentsDirectory()
    {
        return self::getEmailRootAttachmentsName() . DIRECTORY_SEPARATOR . self::getEmailCompanySubdirectory();
    }

    private static function getRootAttachmentsCommonDirectory()
    {
        return self::getEmailRootAttachmentsName() . DIRECTORY_SEPARATOR . self::getEmailCommonSubdirectoryName();
    }

    private static function getApplicationFormsDirectory()
    {
        return self::getRootAttachmentsDirectory() . DIRECTORY_SEPARATOR . self::getEmailApplicationFormsDirectoryName();
    }

    private static function getApplicationFormsCommonDirectory()
    {
        return self::getRootAttachmentsCommonDirectory() . DIRECTORY_SEPARATOR . self::getEmailApplicationFormsDirectoryName();
    }

    private static function getEmailBodiesDirectoryPath()
    {
        return "../" . self::getEmailBodiesDirectory() . DIRECTORY_SEPARATOR;
        // return self::getEmailBodiesDirectory() . DIRECTORY_SEPARATOR;
    }

    private static function getEmailBodiesDirectory()
    {
        return self::getRootAttachmentsDirectory() . DIRECTORY_SEPARATOR . self::getEmailBodiesDirectoryName();
    }

    private static function getApplicationFormsDirectoryPath()
    {
        return "../" . self::getApplicationFormsDirectory() . DIRECTORY_SEPARATOR;
        // return self::getApplicationFormsDirectory() . DIRECTORY_SEPARATOR;
    }

    public static function getDirectoryPathToAttachmentFile($fileName)
    {
        return self::getApplicationFormsDirectoryPath() . $fileName;
    }

    private static function getApplicationFormsCommonDirectoryPath()
    {
        return "../" . self::getApplicationFormsCommonDirectory() . DIRECTORY_SEPARATOR;
        // return self::getApplicationFormsCommonDirectory() . DIRECTORY_SEPARATOR;
    }

    public static function getDirectoryPathToCommonAttachmentFile($fileName)
    {
        return self::getApplicationFormsCommonDirectoryPath() . $fileName;
    }

    private static function getAccountPath($account)
    {
        switch (strtolower($account)) {
            case 'lloyds ce':
                $path = 'Lloyds';
                break;
            default:
                $path = $account;
                break;
        }
        return $path;
    }

    // opens required file
    private static function getApplicationFormFile($fileName)
    {
        $handle = fopen($fileName, "r", true);
        $applicationForm = fread($handle, filesize($fileName));
        fclose($handle);
        return base64_encode($applicationForm);
    }

    private static function getApplicationFormFileByCountryAndAccountType($country, $accountType)
    {
        $fileName = '';
        switch (trim($accountType)) {
            case AccountRecord::ACCOUNT_TYPE_FSS:
                switch (trim($country)) {
                    case self::COUNTRY_POLAND:
                        $fileName = self::getPolishFFSApplicationFormFileName();
                        break;
                    default:
                        $fileName = self::getGlobalFSSApplicationFormFileName();
                        break;
                }
                break;
            case AccountRecord::ACCOUNT_TYPE_NONE_FSS:
                $fileName = self::getGlobalNonFSSApplicationFormFileName();
                break;
            default:
                break;
        }
        if (!empty($fileName)) {
            $encodedAttachmentFile = self::getApplicationFormCompanyFile($fileName);
            $data = array(
                'filename' => $fileName,
                'content_type' => self::FILE_TYPE_WORD,
                'data' => $encodedAttachmentFile,
                'path' => self::getDirectoryPathToAttachmentFile($fileName),
            );
        } else {
            $data = array();
        }
        return $data;
    }

    private static function getApplicationFormCompanyFile($formName)
    {
        $fileName = self::getDirectoryPathToAttachmentFile($formName);
        return static::getApplicationFormFile($fileName);
    }

    private static function getApplicationFormCommonFile($formName)
    {
        $fileName = self::getDirectoryPathToCommonAttachmentFile($formName);
        return static::getApplicationFormFile($fileName);
    }

    private static function getOdcApplicationFormFile($fileName)
    {

        $inputFileName = self::getDirectoryPathToAttachmentFile($fileName);

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
        // ob_clean();
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        ob_start();
        $writer->save('php://output');
        $xlsAttachment = ob_get_clean();

        return base64_encode($xlsAttachment);
    }

    // --------------------- file names START ---------------------

    private static function getGlobalFSSApplicationFormFileName()
    {
        $fileName = self::checkIfIsKyndryl() === true ? self::KYNDRYL_APPLICATION_FORM_GLOBAL_FSS : self::IBM_APPLICATION_FORM_GLOBAL_FSS;
        return $fileName;
    }

    private static function getGlobalNonFSSApplicationFormFileName()
    {
        $fileName = self::checkIfIsKyndryl() === true ? self::KYNDRYL_APPLICATION_FORM_GLOBAL_NON_FSS : self::IBM_APPLICATION_FORM_GLOBAL_NON_FSS;
        return $fileName;
    }

    private static function getOdcApplicationFormFileName()
    {
        $fileName = self::checkIfIsKyndryl() === true ? self::KYNDRYL_APPLICATION_FORM_ODC : self::IBM_APPLICATION_FORM_ODC;
        return $fileName;
    }

    private static function getPolishFFSApplicationFormFileName()
    {
        $fileName = self::checkIfIsKyndryl() === true ? self::KYNDRYL_APPLICATION_FORM_POLISH_FSS : null;
        return $fileName;
    }

    private static function getOwensConsentFormFileName()
    {
        $fileName = self::APPLICATION_FORM_OWENS;
        return $fileName;
    }

    private static function getVfConsentFormFileName()
    {
        $fileName = self::APPLICATION_FORM_VF;
        return $fileName;
    }
    private static function getAdditionalApplicationFormFile($applicationType)
    {
        $fileName = '';
        $contentType = '';
        $encodedAttachmentFile = null;
        $directoryPath = '';
        switch ($applicationType) {
            case self::ODC:
                $fileName = self::getOdcApplicationFormFileName();
                $contentType = self::FILE_TYPE_WORD;
                $encodedAttachmentFile = self::getOdcApplicationFormFile($fileName);
                $directoryPath = self::getDirectoryPathToAttachmentFile($fileName);
                break;
            case self::OWENS:
                $fileName = self::getOwensConsentFormFileName();
                $contentType = self::FILE_TYPE_PDF;
                $encodedAttachmentFile = self::getApplicationFormCommonFile($fileName);
                $directoryPath = self::getDirectoryPathToCommonAttachmentFile($fileName);
                break;
            case self::VF:
                $fileName = self::getVfConsentFormFileName();
                $contentType = self::FILE_TYPE_PDF;
                $encodedAttachmentFile = self::getApplicationFormCommonFile($fileName);
                $directoryPath = self::getDirectoryPathToCommonAttachmentFile($fileName);
                break;
            default:
                break;
        }
        if (!empty($fileName)) {
            $data = array(
                'filename' => $fileName,
                'content_type' => $contentType,
                'data' => $encodedAttachmentFile,
                'path' => $directoryPath,
            );
        } else {
            $data = array();
        }
        return $data;
    }

    // --------------------- file names END ---------------------

    public static function findEmailBody($account, $accountType, $country, $emailAddress, $recheck = 'no')
    {
        if (is_array($emailAddress)) {
            $email = $emailAddress[0];
        } else {
            $email = $emailAddress;
        }

        if (empty($account) || empty($country) || empty($email)) {
            throw new \Exception('Incorrect parms passed', 100);
        }

        $offboarded = AccountPersonTable::offboardedStatusFromEmail($email, $account);
        if ($offboarded) {
            $pathToRecheckOffboarded = self::getEmailBodiesDirectoryPath() . "recheck_offboarded.php";
            return $pathToRecheckOffboarded;
        } else {

            $countryData = CountryTable::getEmailBodyNameForCountry($country);
            list(
                'EMAIL_BODY_NAME' => $emailBodyName
            ) = $countryData;

            $accountPath = self::getAccountPath($account);

            $emailPrefix = null;
            $intExt = self::INTERNAL;

            switch (strtolower($recheck)) {
                case self::NO:
                    // send REQUEST type file
                    $emailPrefix = self::REQUEST;
                    $intExt = stripos($email, ".ibm.com") !== false ? self::INTERNAL : self::EXTERNAL;
                    switch (trim($country)) {
                        case self::COUNTRY_POLAND:
                            // overide email body name
                            $emailBodyName = 'poland';
                            // there is internal file only
                            $intExt = self::INTERNAL;
                            break;
                        case self::COUNTRY_CZECH:
                            // overide email body name
                            $emailBodyName = 'czech';
                            // there is internal file only
                            $intExt = self::INTERNAL;
                        default:
                            break;
                    }
                    break;
                case self::YES:
                    // send RECHECK type file
                    $emailPrefix = self::RECHECK;
                    $intExt = self::INTERNAL;
                    break;
                default:
                    break;
            }

            $fileName = $emailPrefix . "_" . $emailBodyName . $intExt . ".php";
            $pathToAccountTypeBody = self::getEmailBodiesDirectoryPath() . $accountType . DIRECTORY_SEPARATOR . $fileName;
            $pathToAccountBody = self::getEmailBodiesDirectoryPath() . $accountPath . DIRECTORY_SEPARATOR . $fileName;
            $pathToDefaultBody = self::getEmailBodiesDirectoryPath() . $fileName;

            $pathsToTry = array($pathToAccountTypeBody, $pathToAccountBody, $pathToDefaultBody);

            $pathFound = false;
            $pathIndex = 0;

            while (!$pathFound && $pathIndex < count($pathsToTry)) {
                $pathToTest = $pathsToTry[$pathIndex];
                $pathFound = file_exists($pathToTest);
                $pathIndex++;
                if ($pathFound) {
                    return $pathToTest;
                }
            }
            throw new \Exception('No email body valid path found', 101);
        }
    }

    public static function sendPesApplicationForms($account, $country, $serial, $candidateName, $candidateFirstName, $candidateEmail, $recheck = 'no')
    {
        $loader = new Loader();
        $allPesTaskid = $loader->loadIndexed('TASKID', 'ACCOUNT', AllTables::$ACCOUNT);

        $accountType = '';
        $accountTypes = $loader->load('ACCOUNT_TYPE', AllTables::$ACCOUNT, " ACCOUNT = '" . $account . "'");
        foreach ($accountTypes as $value) {
            $accountType = $value;
        }

        $emailSubjectPattern = array('/&&account_name&& /', '/&&serial_number&&/', '/&&candidate_name&&/');
        $emailBodyPattern = array('/&&candidate_first_name&&/', '/&&name_of_application_form&&/', '/&&account_name&& /', '/&&pestaskid&&/');
        $emailBody = ''; // overwritten by include

        $appFormData = self::determinePesApplicationForms($country, $accountType);
        list(
            'nameOfApplicationForm' => $nameOfApplicationForm,
            'pesAttachments' => $pesAttachments
        ) = $appFormData;

        $emailBodyFile = PesEmail::findEmailBody($account, $accountType, $country, array($candidateEmail), $recheck);

        $pesTaskid = $allPesTaskid[$account];

        include_once $emailBodyFile;

        $subjectReplacements = array($account, $serial, $candidateName);
        $subject = preg_replace($emailSubjectPattern, $subjectReplacements, PesEmail::EMAIL_SUBJECT);

        $emailBodyReplacements = array($candidateFirstName, $nameOfApplicationForm, $account, $pesTaskid);

        $email = preg_replace($emailBodyPattern, $emailBodyReplacements, $emailBody);
        if (!$email) {
            throw new \Exception('Error preparing Pes Application Form email', 102);
        }

        return $email ? BlueMail::send_mail(array($candidateEmail), $subject, $email, $pesTaskid, array(), array(), false, $pesAttachments) : false;
    }

    public static function determinePesApplicationForms($country, $accountType)
    {
        $auxAppFormData = CountryTable::getAdditionalAttachmentsNameCountry($country);
        list(
            'ADDITIONAL_APPLICATION_FORM' => $additionalApplicationForm
        ) = $auxAppFormData;

        $pesAttachments = array();
        $nameOfApplicationForm = '';

        // set up an application form
        $fileData = self::getApplicationFormFileByCountryAndAccountType($country, $accountType);
        if (!empty($fileData)) {

            $nameOfApplicationForm = "<ul><li><i>" . $fileData['filename'] . "</i></li>";
            $nameOfApplicationForm .= !empty($additionalApplicationForm) ? "<li><i>" . self::getApplicationFormFileNameByKey($additionalApplicationForm) . "</i></li>" : null;
            $nameOfApplicationForm .= "</ul>";

            $pesAttachments[] = $fileData;
        }

        // set up an additional application form
        $fileData = self::getAdditionalApplicationFormFile($additionalApplicationForm);
        if (!empty($fileData)) {
            $pesAttachments[] = $fileData;
        }

        return array('pesAttachments' => $pesAttachments, 'nameOfApplicationForm' => $nameOfApplicationForm);
    }

    public function sendPesEmailStarter($record)
    {
        $upesRef = $record['UPES_REF'];
        $account = $record['ACCOUNT'];
        $accountId = $record['ACCOUNT_ID'];
        $country = $record['COUNTRY_OF_RESIDENCE'];
        $serial = $record['CNUM'];
        $candidateName = $record['FULL_NAME'];
        $names = explode(" ", $candidateName);
        $candidateFirstName = $names[0];
        $candidateEmail = $record['EMAIL_ADDRESS'];
        $recheck = 'no';

        $sendResponse = false;
        try {

            $sendResponse = PesEmail::sendPesApplicationForms($account, $country, $serial, $candidateName, $candidateFirstName, $candidateEmail, $recheck);

            $indicateRecheck = strtolower($recheck) == 'yes' ? "(recheck)" : null;
            $nextStatus = strtolower($recheck) == 'yes' ? AccountPersonRecord::PES_STATUS_RECHECK_PROGRESSING : AccountPersonRecord::PES_STATUS_PES_PROGRESSING;

            $accountPersonTable = new AccountPersonTable(AllTables::$ACCOUNT_PERSON);
            $accountPersonTable->setPesStatus($upesRef, $accountId, $nextStatus, $_SESSION['ssoEmail'], 'PES Application form sent:' . $sendResponse['Status']);
            $accountPersonTable->savePesComment($upesRef, $accountId, "PES application forms $indicateRecheck sent:" . $sendResponse['Status']);

            $accountPersonTable->setPesProcessStatus($upesRef, $accountId, AccountPersonTable::PROCESS_STATUS_USER);
            $accountPersonTable->savePesComment($upesRef, $accountId, "Process Status set to " . AccountPersonTable::PROCESS_STATUS_USER);

        } catch (\Exception $e) {
            $message = '';
            switch ($e->getCode()) {
                case 100:
                    $message = 'Invalid params';
                    break;
                case 101:
                    $message = 'Invalid path';
                    break;
                case 102:
                    $message = 'Email preparation error';
                    break;
                default:
                    $message = 'Generic error has occurred';
                    break;
            }

            $sendResponse = array(
                'sendResponse' => array(
                    'response' => $message,
                ),
                'Status' => 'Errored',
            );
        }

        return $sendResponse;
    }

    public function sendPesEmailChaser($upesref, $account, $emailAddress, $chaserLevel, $requestor)
    {

        $loader = new Loader();
        $allPesTaskid = $loader->loadIndexed('TASKID', 'ACCOUNT', AllTables::$ACCOUNT);
        $pesTaskid = $allPesTaskid[$account];

        $pesEmailPattern = array(); // Will be overridden when we include_once from emailBodies later.
        $pesEmail = null; // Will be overridden when we include_once from emailBodies later.
        $names = PersonTable::getNamesFromUpesref($upesref);
        $fullName = $names['FULL_NAME'];

        $emailBodyFileName = 'chaser' . trim($chaserLevel) . ".php";
        $replacements = array($fullName, $account);

        include_once self::getEmailBodiesDirectory() . '/' . $emailBodyFileName;
        $emailBody = preg_replace($pesEmailPattern, $replacements, $pesEmail);

        $sendResponse = BlueMail::send_mail(array($emailAddress), "PES Reminder - $fullName($upesref) on $account", $emailBody, $pesTaskid, array($requestor));

        return $sendResponse;
    }

    public function sendPesProcessStatusChangedConfirmation($upesref, $account, $fullname, $emailAddress, $processStatus, $requestor = null)
    {
        $loader = new Loader();
        $allPesTaskid = $loader->loadIndexed('TASKID', 'ACCOUNT', AllTables::$ACCOUNT);
        $pesTaskid = $allPesTaskid[$account];

        $pesEmailPattern = array(); // Will be overridden when we include_once from emailBodies later.
        $pesEmail = null; // Will be overridden when we include_once from emailBodies later.

        $emailBodyFileName = 'processStatus' . trim($processStatus) . ".php";
        $replacements = array($fullname, $account);

        include_once self::getEmailBodiesDirectory() . '/' . $emailBodyFileName;
        $emailBody = preg_replace($pesEmailPattern, $replacements, $pesEmail);

        return BlueMail::send_mail(array($emailAddress), "PES Status Change - $fullname($upesref) : $account", $emailBody, $pesTaskid, array($requestor));
    }

    public static function notifyPesTeamOfUpcomingRechecks($detialsOfPeopleToBeRechecked = null)
    {

        $now = new \DateTime();
        $pesEmail = null; // Will be overridden when we include_once from emailBodies later.

        include_once self::getEmailBodiesDirectory() . '/recheckReport.php';

        $pesEmail .= "<h4>Generated by uPes: " . $now->format('jS M Y') . "</h4>";

        $pesEmail .= "<table border='1' style='border-collapse:collapse;'  >";
        $pesEmail .= "<thead style='background-color: #cce6ff; padding:25px;'>";
        $pesEmail .= "<tr><th style='padding:25px;'>CNUM</th><th style='padding:25px;'>Full Name</th><th style='padding:25px;'>Account</th><th style='padding:25px;'>PES Status</th><th style='padding:25px;'>Recheck Date</th>";
        $pesEmail .= "</tr></thead><tbody>";

        foreach ($detialsOfPeopleToBeRechecked as $personToBeRechecked) {
            $pesEmail .= "<tr><td style='padding:15px;'>" . $personToBeRechecked['CNUM'] . "</td><td style='padding:15px;'>" . $personToBeRechecked['FULL_NAME'] . "</td><td style='padding:15px;'>" . $personToBeRechecked['ACCOUNT'] . "</td><td style='padding:15px;'>" . $personToBeRechecked['PES_STATUS'] . "</td><td style='padding:15px;'>" . $personToBeRechecked['PES_RECHECK_DATE'] . "</td></tr>";
        }

        $pesEmail .= "</tbody>";
        $pesEmail .= "</table>";

        $pesEmail .= "<style> th { background:red; padding:!5px; } </style>";

        $emailBody = $pesEmail;

        $sendResponse = BlueMail::send_mail(self::$notifyPesEmailAddresses['to'], "UPES Upcoming Rechecks", $emailBody, self::$notifyPesEmailAddresses['to'][0], self::$notifyPesEmailAddresses['cc']);
        return $sendResponse;
    }

    public static function notifyPesTeamNoUpcomingRechecks()
    {

        $now = new \DateTime();
        $pesEmail = null; // Will be overridden when we include_once from emailBodies later.

        include_once self::getEmailBodiesDirectory() . '/recheckReport.php';

        $pesEmail .= "<h4>Generated by uPes: " . $now->format('jS M Y') . "</h4>";
        $pesEmail .= "<p>No upcoming rechecks have been found</p>";
        $emailBody = $pesEmail;

        $sendResponse = BlueMail::send_mail(self::$notifyPesEmailAddresses['to'], "Upcoming Rechecks-None", $emailBody, self::$notifyPesEmailAddresses['to'][0], self::$notifyPesEmailAddresses['cc']);
        return $sendResponse;
    }

    public static function notifyPesTeamLeaversFound($detailsOfLeavers)
    {
        $shortDetails = array();
        $fullDetails = array();
        foreach ($detailsOfLeavers as $leaver) {
            $shortDetails[$leaver['CNUM']] = $leaver['FULL_NAME'];
            $fullDetails[$leaver['CNUM']][] = $leaver;
        }

        $slack = new slack();
        $now = new \DateTime();
        $pesEmail = null; // Will be overridden when we include_once from emailBodies later.

        include_once self::getEmailBodiesDirectory() . '/leaversFound.php';

        $pesEmail .= "<h4>Generated by uPes: " . $now->format('jS M Y') . "</h4>";

        $pesEmail .= "<table border='1' style='border-collapse:collapse;'  >";
        $pesEmail .= "<thead style='background-color: #cce6ff; padding:25px;'>";
        $pesEmail .= "<tr><th style='padding:25px;'>CNUM</th><th style='padding:25px;'>Full Name</th><th style='padding:25px;'>Account</th><th style='padding:25px;'>PES Status</th><th style='padding:25px;'>Cleared Date</th><th style='padding:25px;'>Recheck Date</th>";
        $pesEmail .= "</tr></thead><tbody>";

        //         foreach ($detailsOfLeavers as $leaver) {
        //             $pesEmail.="<tr><td style='padding:15px;'>" . $leaver['CNUM'] . "</td><td style='padding:15px;'>" . $leaver['FULL_NAME']  . "</td><td style='padding:15px;'>" . $leaver['ACCOUNT']  . "</td><td style='padding:15px;'>" . $leaver['PES_STATUS'] . "</td><td style='padding:15px;'>" . $leaver['PES_CLEARED_DATE'] . "</td><td style='padding:15px;'>" . $leaver['PES_RECHECK_DATE'] . "</td></tr>";
        //         }

        foreach ($shortDetails as $cnum => $fullName) {
            $slack->sendMessageToChannel("Leaver :  " . $cnum . " : " . $fullName, slack::CHANNEL_UPES_AUDIT);
            foreach ($fullDetails[$cnum] as $leaver) {
                $pesEmail .= "<tr><td style='padding:15px;'>" . $leaver['CNUM'] . "</td><td style='padding:15px;'>" . $leaver['FULL_NAME'] . "</td><td style='padding:15px;'>" . $leaver['ACCOUNT'] . "</td><td style='padding:15px;'>" . $leaver['PES_STATUS'] . "</td><td style='padding:15px;'>" . $leaver['PES_CLEARED_DATE'] . "</td><td style='padding:15px;'>" . $leaver['PES_RECHECK_DATE'] . "</td></tr>";
            }
        }

        $pesEmail .= "</tbody>";
        $pesEmail .= "</table>";

        $pesEmail .= "<style> th { background:blue; padding:5px; } </style>";

        $emailBody = $pesEmail;

        $sendResponse = BlueMail::send_mail(self::$notifyPesEmailAddresses['to'], "uPES Notification of Leavers", $emailBody, self::$notifyPesEmailAddresses['to'][0], self::$notifyPesEmailAddresses['cc']);
        return $sendResponse;
    }

    public static function notifyStarterRequired($records)
    {
        $now = new \DateTime();
        $pesEmail = null; // Will be overridden when we include_once from emailBodies later.

        include_once self::getEmailBodiesDirectory() . '/starterRequiredFound.php';

        $pesEmail .= "<h4>Generated by uPes: " . $now->format('jS M Y') . "</h4>";

        $pesEmail .= "<table border='1' style='border-collapse:collapse;'  >";
        $pesEmail .= "<thead style='background-color: #cce6ff; padding:25px;'>";
        $pesEmail .= "<tr><th style='padding:25px;'>CNUM</th><th style='padding:25px;'>Email Address</th><th style='padding:25px;'>Full Name</th><th style='padding:25px;'>Account</th><th style='padding:25px;'>PES Status</th><th style='padding:25px;'>Email Status</th>";
        $pesEmail .= "</tr></thead><tbody>";

        foreach ($records as $record) {
            $pesEmail .= "<tr><td style='padding:15px;'>" . $record['CNUM'] . "</td><td style='padding:15px;'>" . $record['EMAIL_ADDRESS'] . "</td><td style='padding:15px;'>" . $record['FULL_NAME'] . "</td><td style='padding:15px;'>" . $record['ACCOUNT'] . "</td><td style='padding:15px;'>" . $record['PES_STATUS'] . "</td><td style='padding:15px;'>" . $record['status'] . "</td></tr>";
        }

        $pesEmail .= "</tbody>";
        $pesEmail .= "</table>";

        $pesEmail .= "<style> th { background:blue; padding:5px; } </style>";

        $emailBody = $pesEmail;

        // $sendResponse = BlueMail::send_mail(self::$notifyPesEmailAddresses['to'], "uPES Notification of Sent out Starters", $emailBody, self::$notifyPesEmailAddresses['to'][0], self::$notifyPesEmailAddresses['cc']);
        $sendResponse = BlueMail::send_mail(
            array('piotr.tajanowicz@kyndryl.com'),
            "kPES Notification of Sent out Starters",
            $emailBody,
            'kPES@noreply.ibm.com'
        );

        return $sendResponse;
    }
}
