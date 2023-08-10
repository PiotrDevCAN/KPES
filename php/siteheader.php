<?php
// use sessions to store authentication and authorization state

// ** session_cache_limiter('private');
// ** for fpdf http://www.fpdf.org/ download of pdf files in https;

use ByJG\Session\JwtSession;
use itdq\BluePages;
use itdq\BluePagesSLAPHAPI;
use itdq\JwtSecureSession;
use PhpOffice\PhpSpreadsheet\Helper\Sample;

function ob_html_compress($buf){
    return str_replace(array("\n","\r"),'',$buf);
}

# Takes a hash of values and files in a text template
function build_template($template, $vals) {
    $file = dirname(__FILE__) . "/templates/" . $template;
    $tmpl = file_get_contents($file);
    if ($tmpl == FALSE) { return FALSE; }
    $keys = array_keys($vals);
    foreach ($keys as $thekey) {
        $tmpl = str_replace("<%$thekey%>", $vals[$thekey], $tmpl);
    }
    //$tmpl = eregi_replace('<%[^ ]*%>', "", $tmpl); // Deprecated in 5.3.0
    $tmpl = preg_replace('/<%[^ ]*%>/', "", $tmpl);
    return $tmpl;
}

# send the template header
function do_header() {
    global $content, $w3nav;
    if ($GLOBALS['header_done'] == TRUE) return;
    $template = build_template($GLOBALS['page_template']. ".header", $GLOBALS['content']);
    print $template;
    $GLOBALS['header_done'] = TRUE;
}

# send the template footer
function do_footer() {
    global $content, $w3nav;
    if ($GLOBALS['header_done'] == FALSE) return;
    $template = build_template($GLOBALS['page_template']. ".footer", $GLOBALS['content']);
    print $template;
    $GLOBALS['header_done'] = FALSE;
}

function do_result($page = array())
{
    // send the header if needed
    if ($GLOBALS['header_done'] === FALSE)
        do_header();
    if (! is_array($page))
        $page = array(
            $page
        );

        // default values
    $page += array(
        'result' => "Success",
        'message' => "Operation compleated succesfully",
        'url' => "javascript:history.back()"
    );

    // print the page
    print "<h3>" . $page['result'] . "</h3>\n";
    print "<p>" . $page['message'] . "</p>\n";
    print "<p>&lt; <a href=\"" . $page['url'] . "\">Return to previous</a>\n";
    do_footer();
    exit();
}

function do_pear_error($obj, $url = "javascript:history.back()")
{
    // header if needed
    if ($GLOBALS['header_done'] === FALSE)
        do_header();

        // error icon
        // $icon = "<img src=\"" . $GLOBALS['content']['assets'] . "/images/icon-error.gif\" ";
        // $icon .= "alt=\"Error\" width=\"12\" height=\"12\" border=\"0\" />";

    // show the error message from pear

    print "<table border=\"0\" width=\"95%\" cellpadding=\"1\" cellspacing=\"0\">\n";

    if ($code = $obj->getCode()) {
        print "<tr><td width=\"20%\" style=\"color:#900; font-weight:bold;\">Error Code: </td>\n";
        print "<td>$code</td></td>";
    }

    if ($message = $obj->getMessage()) {
        print "<tr><td width=\"20%\" style=\"color:#900; font-weight:bold;\">Message: </td>\n";
        print "<td>$message</td></td>";
    }

    print "<tr><td colspan=\"2\">&#160;</td></tr>\n";
    print "<tr><td colspan=\"2\">An error has occurred while processing your request. ";
    print "Go back and correct the error or use the feedback link to report this error to the site owner.</td></tr>";

    print "<tr><td colspan=\"2\">&#160;</td></tr>\n";
    print "<tr><td colspan=\"2\">&lt; <a href=\"$url\">Return to previous</a></td></tr>";
    print "</table>";

    // send the html footer
    do_footer();
    exit();
}

function do_error($page = array())
{
    // send the header if needed
    if ($GLOBALS['header_done'] === FALSE)
        do_header();
    if (! is_array($page))
        $page = array(
            $page
        );

        // default values
    $page += array(
        'error' => "Error",
        'message' => "A general processing fault (GPF!) has occured. Please try again",
        'url' => "javascript:history.back()"
    );

    // icon
    $icon = "<img src=\"" . $GLOBALS['content']['assets'] . "/images/icon-error.gif\" ";
    $icon .= "alt=\"Error\" width=\"12\" height=\"12\" border=\"0\" />";

    // print the page
    print "<p><span style=\"color: #990000; font-weight: bold;\">" . $icon . " " . $page['error'] . "</span><br />\n";
    print $page['message'] . "</p>\n";
    print "<p>&lt; <a href=\"" . $page['url'] . "\">Return to previous</a>\n";
    do_footer();
    exit();
}

function do_auth($group = null)
{
    if(stripos($_ENV['environment'], 'dev')) {
        $_SESSION['ssoEmail'] = $_ENV['SERVER_ADMIN'];
    } else {
        // batchJobs
        $helper = new Sample();
        if ($helper->isCli()) {
            // $helper->log('This example should only be run from a Web Browser' . PHP_EOL);
            $_SESSION['ssoEmail'] = $_ENV['SERVER_ADMIN'];
        } else {
            include_once "class/include.php";
            $auth = new Auth();
            if(!$auth->ensureAuthorized()){
                die('Invalid logon attempt');
            } else {
                // $_SESSION['ssoEmail'] = $_SESSION['ssoEmail'];
                if(isset($_SESSION['somethingChanged']))
                {
                    echo "<br/><br/><span style='font-weight:bold;'>Warning: </span> The values that are returned from w3ID/IBMID has probably been changed.<br/><br/>No need to panic, this is very easy to fix.<br/>This is your session currently:<br/><br/><code>";
                    var_dump($_SESSION);
                    echo "</code><br/><br/>" . "Everything you see there except <span style='font-weight:bold;'>somethingChanged</span> is coming from w3ID/IBMID service.";
                    echo '<br/>You now need to look into <span style="font-weight:bold;">private function processOpenIDConnectCallback($data)</span> in <span style="font-weight:bold;">class/auth.class.php</span> and read the comments.';
                    echo "<br/>Please keep in mind, that even if sign in is technically working now, you should not use the code in production without strict checking of those values.";
                    echo "<br/><br/>";
                    echo "What you can do now is:";
                    echo "<br/>a) Paste this warning message to <a href='https://github.ibm.com/CWT/auth-openidconnect-w3/issues/' target='_blank'>GitHub Issues</a> and wait for it to be fixed.";
                    echo '<br/>b) Very easily adjust the code in private function processOpenIDConnectCallback($data) with new and correct values and <a href="https://github.ibm.com/CWT/auth-openidconnect-w3/issues/" target="_blank">open a new issue</a> or <a href="https://github.ibm.com/CWT/auth-openidconnect-w3/pulls" target="_blank">create a new pull request</a>.';
                    echo '<br/><br/>Note: When trying to fix this yourself, do remember to always clear cookies when refreshing the page.';
                }
            }
        }
    }
}

// #
// # misc user related functions
// #
function make_passwd($pw_length = "8")
{
    // set ASCII range for random character generation
    $low_ascii_bound = 50; // "2"
    $upper_ascii_bound = 122; // "z"
                              // exclude special characters and some confusing alphanumerics o,O,0,I,1,l etc
    $notuse = array(
        58,
        59,
        60,
        61,
        62,
        63,
        64,
        73,
        79,
        91,
        92,
        93,
        94,
        95,
        96,
        108,
        111
    );
    $i = "0";
    $password = "";
    while ($i < $pw_length) {
        mt_srand((double) microtime() * 1000000);
        $randnum = mt_rand($low_ascii_bound, $upper_ascii_bound);
        if (! in_array($randnum, $notuse)) {
            $password = $password . chr($randnum);
            $i ++;
        }
    }
    return $password;
}

function passverify($userid, $passwd, $newpass1, $newpass2)
{
    // Check passwords against ITSC204 Guidelines
    // http://w3-1.ibm.com/transform/cio.nsf/Files/ITCS204V2.1/$file/ITCS204V2.1_CIO.htm#PASSWDS
    global $passverify_error;
    // define some defaults
    $alphas = "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM";
    $numerics = "01234567890";
    // verify password is 6 or more chars in length
    if (strlen($newpass1) < 8) {
        $passverify_error = "Error: New password to short. Pick a new password.";
        return false;
    }
    // verify the new passwords match
    if (strcmp($newpass1, $newpass2) != 0) {
        $passverify_error = "Error: New passwords do not match. Try again.";
        return false;
    }
    // verify password does not contain the userid
    if (strpos($newpass1, $userid) !== false) {
        $passverify_error = "Error: New password cannot contain the userid as part of the password. Pick a new password.";
        return false;
    }
    // first and last chars in new password must be alpha chars
    $firstlast = substr($newpass1, 0, 1) . substr($newpass1, - 1);
    if (! strcheck($firstlast, $alphas)) {
        $passverify_error = "Error: first and last chars of the new password must be alphabetic. Pick a new password.";
        return false;
    }
    // new password cannot contain three consecutive chars from current password
    for ($i = 0; $i <= strlen($newpass1); $i ++) {
        $newchar = substr($newpass1, $i, 4); // Get four chars starting at position $i
        if (strlen($newchar) != 4) {
            break;
        } // If we don't have at least 4 chars we're done.
        if (strpos($passwd, $newchar) !== false) {
            $passverify_error = "Error: New password cannot contain more than three identical
				consecutive characters in any position from the current password. Pick a
				new password.";
            return false;
        }
    }
    return true;
}

function strcheck($s, $valid)
{
    // verify that $s only contains the chars defined by $valid
    if (strspn($s, $valid) != strlen($s)) {
        return false;
    }
    return true;
}

function _microtime_float()
{
    list ($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
}

if (!isset($_SERVER['SERVER_NAME'])) {
    $_SERVER['SERVER_NAME'] = 'azure';
}

echo 'NEW VERSION OF CONTAINER CREATED - WITH SECRETS AND VARIABLES !!!';
echo '<pre>';
var_dump($_ENV);
echo '</pre>';

$start = microtime(true);

set_include_path("./" . PATH_SEPARATOR . "../" . PATH_SEPARATOR . "../../" . PATH_SEPARATOR . "../../../" . PATH_SEPARATOR);

include ('vendor/autoload.php');
include ('splClassLoader.php');

// $sessionConfig = (new \ByJG\Session\SessionConfig($_SERVER['SERVER_NAME']))
// ->withTimeoutMinutes(120)
// ->withSecret($_ENV['jwt_token']);

$sessionConfig = new \ByJG\Session\SessionConfig($_SERVER['SERVER_NAME']);
$sessionConfig->withTimeoutMinutes(120);
$sessionConfig->withSecret($_ENV['jwt_token']);

$handler = new JwtSecureSession($sessionConfig);
session_set_save_handler($handler, true);

session_start();

error_log(__FILE__ . "session:" . session_id());
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

ini_set('memory_limit', '512M');
ini_set('max_execution_time', 360);

error_reporting(E_ALL);
date_default_timezone_set('UTC');

while(ob_get_level()>0){
    ob_end_clean();
}

// ob_start();
if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
    if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
        ob_start("ob_gzhandler");
        // exit('ob_gzhandler');
    } else {
        ob_start("ob_html_compress");
        // exit('ob_html_compress 1');
    }
} else {
    ob_start("ob_html_compress");
    // exit('ob_html_compress 2');
}

$GLOBALS['Db2Schema'] = strtoupper($_ENV['environment']);
$https = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == "on");

// global var and config file
include_once ('w3config.php');
$content = array();
$page_template = "interior";
$header_done = FALSE;
$page_timestamp = filemtime($_SERVER['SCRIPT_FILENAME']);
$meta['source'] = "w3php v0.5.8, w3v8, 19 June 2008";

// initalize w3v8 navigation

// error reporting
if ($w3php['debug']) {
    ini_set("error_reporting", E_ALL);
    ini_set("display_errors", '1');
}

// CGI or CLI check
$sapi_type = php_sapi_name();
if (substr($sapi_type, 0, 3) == 'cgi') {
    error_log("You are using CGI PHP - MAIN SITEHEADER.PHP");
} else {
    error_log("You are not using CGI PHP - MAIN SITEHEADER.PHP");
}

$elapsed = microtime(true);
error_log("Pre do_Auth():" . (float)($elapsed-$start));

do_auth();

$elapsed = microtime(true);
error_log("Post do_Auth():" . (float)($elapsed-$start));

include ('php/ldap.php');

$helper = new Sample();
if ($helper->isCli()) {
    // $helper->log('This example should only be run from a Web Browser' . PHP_EOL);
} else {
    include ('php/templates/interior.header.html');
    include ('itdq/java/scripts.html');
    include ('upes/java/scripts.html');
}

$elapsed = microtime(true);
error_log("Pre connect:" . (float)($elapsed-$start));
include ('connect.php');

if ($helper->isCli()) {
    // $helper->log('This example should only be run from a Web Browser' . PHP_EOL);
} else {
    $elapsed = microtime(true);
    error_log("Post connect:" . (float)($elapsed-$start));
    include('displayNavbar.php');
    
    $elapsed = microtime(true);
    error_log("Post Navbar:" . (float)($elapsed-$start));
}
?>
