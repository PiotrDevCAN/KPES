<?php
namespace itdq;

/**
 *
 * @author GB001399
 *
 */
class Email
{

    static function send_mail($to, $cc, $subject, $message, $replyto, $log = true)
    {
        $pwd = null;
        $headers = "From: " . $_SESSION['emailId'] . "\r\n" . "Reply-To: $replyto" . "\r\n";
        $headers .= !empty($cc) ? "Cc: $cc " : null ;
        $headers .= "\r\n" . "MIME-Version: 1.0\n" . "Content-type: text/html; charset=iso-8859-1";
        $message = wordwrap($message, 100);
        $rand = rand();
        // Log::logEntry("<B>Email: $subject </B><BR/>Sent to: $to <BR/>CC: $cc <BR>By " . $_SESSION['ssoEmail'],$pwd );
        if ($log) {
            $emailLogRecordId = self::prelog($to, $cc, $subject, $message, $replyto);
        }
        if ($_SESSION['email']) {
            ?>
            <div id='<?php echo "email" . $rand;?>' style='display:block;' /><H3><?php echo $rand ?> If this message remains visible or you see a further message : 'Fatal Error: Maximum execution time.....' please contact <a href='maito:daniero@uk.ibm.com'>ITDQ Application Support</a> urgently and inform them.</H3></div>
           <?php
            $result = mail($to, $subject, $message, $headers);
            if (! $result) {
                ?>
                <H3><scan style='color:red'>The email has NOT been sent</scan></H3>
                <BR/>Mail send result :
                <?php var_dump($result); ?>
                <BR/>Mail send Details : To:<?php echo $to;?><BR>Subject:"<?php echo $subject?><BR>Message:<?php echo $message;?><BR>Header:<?php echo $headers;?><BR>
                <?php
            }
            ?>
            <script type='text/javascript'>
               var emailDiv = document.getElementById('<?php  echo "email" . $rand; ?>');
               console.log(emailDiv);
               emailDiv.parentNode.removeChild(emailDiv);
               var emailDiv2 = document.getElementById('<?php  echo "email" . $rand; ?>');
               console.log(emailDiv2);
            </script>
            <?php
        } else {
            ?>
            <BR>Email function disabled in this environment
            <BR>Mail would be sent :
            <BR>Headers<?php echo $headers;?>
            <BR>To :<?php echo $to;?>
            <BR>Subject :<?php echo $subject;?>
            <BR>Message :<?php $message;?>
            <?php
            $result = false;
        }
        if ($log) {
            self::updatelog($emailLogRecordId, $result);
        }
        return $result;
    }

    /**
     *
     *
     * Will Log an Email to AllTables::$EMAIL_LOG.
     *
     * If AllTables::$EMAIL_LOG exists, then this functon will write a copy of the email to that table.
     * It checks the record_id for the newly created entry and if it's divisible by 100, it will call the clearLog() function
     * so effectively, it auto-housekeeps. *
     *
     * @param unknown_type $to
     * @param unknown_type $subject
     * @param unknown_type $message
     * @param unknown_type $headers
     * @param unknown_type $disabled
     */
    static function prelog($to, $cc, $subject, $message, $replyto)
    {
        if (isset(AllItdqTables::$EMAIL_LOG)) {
            $safeMessage = trim(substr(htmlspecialchars($message), 0, 15900));
            $ena = $_SESSION['email'] ? 'TRUE' : 'FALSE';
            $sql = " INSERT INTO " . $GLOBALS['Db2Schema'] . "." . AllItdqTables::$EMAIL_LOG;
            $sql .= " (TO,CC,SUBJECT,MESSAGE,REPLYTO,ENABLED, CREATOR ) ";
            $sql .= " VALUES ";
            $sql .= "('" . htmlspecialchars($to) . "','" . htmlspecialchars($cc) . "','" . htmlspecialchars($subject) . "','" . $safeMessage . "','" . htmlspecialchars($replyto) . "','" . htmlspecialchars($ena) . "','" . htmlspecialchars($_SESSION['ltcuser']['mail']) . "') ";
            $rs = sqlsrv_query($GLOBALS['conn'], $sql);
            if (! $rs) {
                print_r($_SESSION);
                echo "<BR/>" . print_r(sqlsrv_errors());
                echo "<BR/>" . print_r(sqlsrv_errors()) . "<BR/>";
                exit("Error in: " . __METHOD__ . " running: " . htmlspecialchars($sql, ENT_QUOTES));
            }
            $recordId = db2_last_insert_id($GLOBALS['conn']);
            if (($recordId % 100) == 0) {
                self::clearLog();
            }
            return $recordId;
        }
    }

    static function updatelog($recordId, $result)
    {
        if (isset(AllItdqTables::$EMAIL_LOG)) {
            $res = $result ? 'TRUE' : 'FALSE';
            $sql = " UPDATE " . $GLOBALS['Db2Schema'] . "." . AllItdqTables::$EMAIL_LOG;
            $sql .= " SET RESULT='" . htmlspecialchars($res) . "' ";
            $sql .= " WHERE RECORD_ID='" . trim($recordId) . "' ";
            $rs = sqlsrv_query($GLOBALS['conn'], $sql);
            if (! $rs) {
                print_r($_SESSION);
                echo "<BR/>" . print_r(sqlsrv_errors());
                echo "<BR/>" . print_r(sqlsrv_errors()) . "<BR/>";
                exit("Error in: " . __METHOD__ . " running: " . htmlspecialchars($sql, ENT_QUOTES));
            }
        }
    }

    /**
     * *
     * if AllTables is set - this function will delete any entries in it that are older than :
     * - If set then the value from $_SESSION['KeepEmailsFor']
     * or
     * - '7 DAYS'*
     */
    static function clearLog()
    {
        if (isset(AllItdqTables::$EMAIL_LOG)) {
            if (isset($_SESSION['KeepEmailsFor'])) {
                $keepEmailsFor = $_SESSION['KeepEmailsFor'];
            } else {
                $keepEmailsFor = " 7 DAYS ";
            }
            $sql = 'DELETE FROM ' . $GLOBALS['Db2Schema'] . "." . AllItdqTables::$EMAIL_LOG;
            $sql .= " WHERE CREATED < (CURRENT TIMESTAMP - $keepEmailsFor );";
            $rs = sqlsrv_query($GLOBALS['conn'], $sql);
            if (! $rs) {
                print_r($_SESSION);
                echo "<BR/>" . print_r(sqlsrv_errors());
                echo "<BR/>" . print_r(sqlsrv_errors()) . "<BR/>";
                exit("Error in: " . __METHOD__ . " running: " . htmlspecialchars($sql, ENT_QUOTES));
            }
        }
    }

    static function resend($recordId)
    {
        if (isset(AllItdqTables::$EMAIL_LOG)) {
            $sql = " SELECT * FROM " . $GLOBALS['Db2Schema'] . "." . AllItdqTables::$EMAIL_LOG;
            $sql .= " WHERE RECORD_ID='" . trim($recordId) . "' ";
            $rs = sqlsrv_query($GLOBALS['conn'], $sql);
            if (! $rs) {
                print_r($_SESSION);
                echo "<BR/>" . print_r(sqlsrv_errors());
                echo "<BR/>" . print_r(sqlsrv_errors()) . "<BR/>";
                exit("Error in: " . __METHOD__ . " running: " . htmlspecialchars($sql, ENT_QUOTES));
            } else {
                $row = sqlsrv_fetch_array($rs);
                self::send_mail($row['TO'], $row['CC'], $row['SUBJECT'], $row['MESSAGE'], $row['REPLYTO']);
            }
        }
    }
}
?>