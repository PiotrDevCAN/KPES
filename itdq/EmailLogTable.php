<?php
namespace itdq;
/*
 *
 * CREATE TABLE UPES.EMAIL_LOG ( RECORD_ID INTEGER NOT NULL GENERATED BY DEFAULT AS IDENTITY ( NO CYCLE ), TO VARCHAR(512), SUBJECT VARCHAR(200), MESSAGE CLOB(262144), DATA_JSON CLOB(5242880), RESPONSE CLOB(10240), LAST_STATUS CLOB(1024), SENT_TIMESTAMP TIMESTAMP(6), STATUS_TIMESTAMP TIMESTAMP(6), CC CLOB(1048576), BCC CLOB(1048576) ) IN USERSPACE1;
 * ALTER TABLE UPES.EMAIL_LOG ADD CONSTRAINT EL_PK PRIMARY KEY (RECORD_ID ) NOT ENFORCED;
 *
 */



class EmailLogTable  extends DbTable {

    function returnAsArray($startDate=null,$endDate=null){
        $data = array();
        $now = new \DateTime();

        $endDate = isset($endDate) ? $endDate : $now->format('Y-m-d');
        $startDate = isset($startDate) ? $startDate : date_format($now->modify("-1 month"),'Y-m-d');

        $sql  = " SELECT * FROM " . $_SESSION['Db2Schema'] . "." . AllItdqTables::$EMAIL_LOG;
        $sql .= " WHERE DATE(SENT_TIMESTAMP) >= DATE('" . db2_escape_string($startDate) . "') and DATE(SENT_TIMESTAMP) <= DATE('" . db2_escape_string($endDate) . "') ";
        $rs = db2_exec($_SESSION['conn'], $sql);

        if(!$rs){
            DbTable::displayErrorMessage($rs, __CLASS__, __METHOD__, $sql);
            return false;
        } else {
            while(($row=db2_fetch_assoc($rs))==true){
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

                $resendButton  =  "<button type='button' class='btn btn-primary btn-xs resendEmail' aria-label='Left Align' data-reference='" . trim($row['RECORD_ID']) . "' data-url='" . $resendUrl .  "'  >";
                $resendButton .= "<span class='glyphicon glyphicon-share-alt ' aria-hidden='true'></span>";
                $resendButton .= "</button><br/>";

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
