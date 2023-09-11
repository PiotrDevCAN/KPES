<?php
// w3php site config file
// Edit this file to control site wide defaults

// # these settings control aspects of the site
// # content (look and feel)
$_SESSION['SITE_NAME'] = 'uPES';


$site = array(

    // if not empty will override the site title from menuconf.php
    'site_title' => 'uPES : UKI Pre-Employment Screening', // CHANGE THIS WHEN STARTING NEW APP

    // enable w3 local search
    'local_search' => FALSE,

    // show bread crumb navigation
    'bread_crumbs' => TRUE,

    // url to send feedback too. See meta tags below as well
    'feedback_uri' => 'mailto:piotr.tajanowicz@kyndryl.com',

    // base location of css, js, and images
    'assets' => '/ui',

    // display the IBM w3 Intranet Password logo
    'secure_logo' => TRUE,

    // display the cool Linux logo
    'linux_logo' => FALSE,

    // display the xhtml valid icon
    // this only works for http pages, not https pages
    'xhtml_valid' => FALSE,

    // page dates displayed are either 'LASTMOD' or 'CURRENT' or FALSE
    'page_date' => 'CURRENT',

    // prefix to use if your site is not located at /
    // do not include the trailing slash
    // 'prefix' => '/vbac', // CHANGE THIS WHEN STARTING NEW APP

    'dateFormat' => 'yyyy-mm-dd', // Determines the date format for Date Picker
    'dateStart' => '2019-01-01',

    'Db2Schema' => strtoupper($_ENV['environment']),  // DB2 Schema name for the app
    'prefix' => $_ENV['environment'], // DB2 Schema name for the app
    'dirPrefix' => $_ENV['environment'],
    'csvPrefix' => $_ENV['environment'],

    'cdiBg'       => 'ventus_cdi',
    'pesTeamBg'   => 'upes_pesTeam',

    'cdiBgAz'     => 'the kPES tool - production-ventus_cdi',
    'pesTeamBgAz' => 'the kPES tool - production-upes_pesTeam',
    
    'nullBg'      => null,

    'email' => false,
    'emailId' => 'DoNotReply_upes@uk.ibm.com',
    'devEmailId' => 'piotr.tajanowicz@kyndryl.com',

    'AuditLife' => '13 months',
    'AuditDetailsLife' => '6 months',

    'SITE_NAME' => $_ENV['environment'],
    'iconDirectory' => 'ICON'



) // Sets the start date for the Date Pickr
;

// # These settings are used for the meta tags on each page. These are
// # all mandatory for Intranet sites. A full description of meta tags
// # and allowed content is at:
// # http://w3.ibm.com/standards/intranet/design/v8/checklist.html#codehtml

$meta = array(

    // description of web site
    'description' => 'uki Pre-Employment Screening',

    // keywords for w3 search
    'keywords' => 'screening tracker tool',

    // web site owner, can be different from feedback owner
    'owner' => 'carrabooth@uk.ibm.com',

    // The Feedback Meta Tag will be used to automatically route
    // feedback email received through the central Intranet Feedback
    // Form (w3.ibm.com/feedback) to the correct handler, without human
    // intervention.
    'feedback' => 'piotr.tajanowicz@kyndryl.com',

    // security class for this web site
    'security' => 'IBM internal use only',

    // robots control for indexing
    'robots' => 'index,follow',

    // ibm.country associates this site with a country
    // or list of countries, ibm.com Search uses the this tag
    'ibm.country' => 'US',

    // dc.date shows the last time a page was updated
    // and is set automatically by w3php
    'dc.date' => FALSE,

    // the ISO language code of this web site
    'dc.language' => 'en-US',

    // the effective copyright dates of this web sites content
    'dc.rights' => 'Copyright (c) 2004-2015 by IBM Corporation'
);

// # these settings control various aspects of the
// # the way w3php runs
$w3php = array(

    // enable debug only during testing
    'debug' => TRUE,

    // location of error documents
    'error_doc' => $_SERVER['DOCUMENT_ROOT'] . "/" .  $site['prefix'] . '/error_doc/',

    // enable or disable logging to syslog of auth attempts
    'log_auth' => FALSE,

    // ldap ssl connection url for authentication (iip authentication, bluepages)
    'ldaps_host' => 'bluepages.ibm.com:636',

    // attributes returned by default for authenticated users
    'ldap_attr' => array(
        'uid',
        'mail',
        'ismanager',
        'dept',
        'div',
        'employeetype',
        'ibmserialnumber',
        'manager',
        'cn',
        'workloc'
    ),

    // 'ldap_attr' => array('uid', 'mail', 'dept', 'employeetype', 'ibmserialnumber', 'workloc'),
    // base dn to use when doing iip authentication
    'ldap_basedn' => 'ou=bluepages,o=ibm.com'
);

foreach ($site as $key => $value) {
    $GLOBALS['site'][$key] = trim($value);
    $_SESSION[$key] = trim($value);
}