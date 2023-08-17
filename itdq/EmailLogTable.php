<?php
namespace itdq;

class EmailLogTable  extends DbTable {

    function returnAsArray($startDate=null,$endDate=null){
        $data = array();
        $now = new \DateTime();

        $endDate = isset($endDate) ? $endDate : $now->format('Y-m-d');
        $startDate = isset($startDate) ? $startDate : date_format($now->modify("-1 month"),'Y-m-d');

        $sql  = " SELECT * FROM " . $GLOBALS['Db2Schema'] . "." . AllItdqTables::$EMAIL_LOG;
        $sql .= " WHERE DATE(SENT_TIMESTAMP) >= DATE('" . htmlspecialchars($startDate) . "') and DATE(SENT_TIMESTAMP) <= DATE('" . htmlspecialchars($endDate) . "') ";
        $rs = sqlsrv_query($GLOBALS['conn'], $sql);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
            return false;
        } else {
            while(($row=sqlsrv_fetch_array($rs))==true){
                $record=null;
                $record[0]= $row['RECORD_ID'];
                $record[1]= "<b>Subject</b>" . $row['SUBJECT'] . "<br/><b>To:</b>";
                $to = unserialize($row['TO']);
                foreach ($to as $emailAddress){
                    $record[1] .= $emailAddress . "<br/>";
                }
                $record[2] = preg_replace(array('/width="[0-9]{2}%"/','/[0-9]{2}px/'), array('width="100%"','8px'), $row['MESSAGE']);;
                //$record[2] = preg_replace(array('/"([A-z]+)":/','/,/'), array('<b>\1</b>:','<br/>'), $row['RESPONSE']);

                //Response
                $responseObject = json_decode($row['RESPONSE']);
                $statusUrl = $responseObject->link[0]->href;
                $resendUrl = $responseObject->link[1]->href;

                // Status
                $statusObject = json_decode($row['LAST_STATUS']);
                $prevStatus = $statusObject->prevStatus;
                unset($statusObject->prevStatus); // remove all the history
                $statusJson = json_encode($statusObject);

                $checkStatusButton  =  "<button type='button' class='btn btn-primary btn-xs statusCheck' aria-label='Left Align' data-reference='" . trim($row['RECORD_ID']) . "' data-url='" . $statusUrl . "' data-prevStatus='" . $prevStatus . "'  >";
                $checkStatusButton .= "<span class='glyphicon glyphicon-question-sign ' aria-hidden='true'></span>";
                $checkStatusButton .= "</button><br/>";

                $checkStatusButton = isset($row['LAST_STATUS']) ? $checkStatusButton : null;

//                 $resendButton  =  "<button type='button' class='btn btn-primary btn-xs resendEmail' aria-label='Left Align' data-reference='" . trim($row['RECORD_ID']) . "' data-url='" . $resendUrl .  "'  >";
//                 $resendButton .= "<span class='glyphicon glyphicon-share-alt ' aria-hidden='true'></span>";
//                 $resendButton .= "</button><br/>";

                $resendButton = null; // Resend not supported with PHPMailer as opposed to Bluemail service.

                $resendButton = $statusObject->locked ? null : $resendButton; // Can't resend once locked.

                $record[3] = $statusObject->sent ? $resendButton : $checkStatusButton;
                $record[3] .= preg_replace(array('/"([A-z]+)":/','/,/'), array('<b>\1</b>:','<br/>'), $statusJson);

                //Timestamps
                $record[4] = $row['SENT_TIMESTAMP'];
                $record[5] = $row['STATUS_TIMESTAMP'];
                $data[] = $record;
            }
        }



//          $rows[] = array('id','to','subject','message','response','status','sent timestamp','status_timestamp');
//          $rows[] = array('id2','to2','subject2','message2','response2','status2','sent timestamp2','status_timestamp2');
        return $data;

    }

}
