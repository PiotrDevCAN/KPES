<?php
namespace itdq;

/*
 *  Handles OKTA Groups.
 */
class OKTAGroups {

	public static function defineGroup($groupName,$description, $life=1){
		$nextyear = time() + ((365*24*60*60) * $life);
		$yyyy = date("Y",$nextyear);
		$mm   = date("m",$nextyear);
		$dd   = date("d",$nextyear);
		$url = array();
		$url['Define_Group'] = "https://bluepages.ibm.com/tools/groups/protect/groups.wss?task=GoNew&selectOn=" . urlencode($groupName) . "&gDesc=" . urlencode($description) . "&mode=members&vAcc=Owner/Admins&Y=$yyyy&M=$mm&D=$dd&API=1";
		self::processURL($url);
	}

	public static function deleteMember($groupName,$memberEmail){
		$memberUID = self::getUID($memberEmail);
		$url = array();
		$url['Delete_Member'] = "https://bluepages.ibm.com/tools/groups/protect/groups.wss?Delete=Delete+Checked&gName=" . urlencode($groupName) . "&task=DelMem&mebox=" . urlencode($memberUID) . "&API=1";
		self::processURL($url);
	}

	public static function addMember($groupName,$memberEmail){
		$memberUID = self::getUID($memberEmail);
		$url = array();
		$url['Add_Member'] = "https://bluepages.ibm.com/tools/groups/protect/groups.wss?gName=" . urlencode($groupName) . "&task=Members&mebox=" . urlencode($memberUID) . "&Select=Add+Members&API=1";
		self::processURL($url);
	}

	public static function addAdministrator($groupName,$memberEmail){
		$memberUID = self::getUID($memberEmail);
		$url = array();
		$url['Add_Administrator'] = "https://bluepages.ibm.com/tools/groups/protect/groups.wss?gName=" . urlencode($groupName) . "&task=Administrators&mebox=" . urlencode($memberUID) . "&Submit=Add+Administrators&API=1 ";
		self::processURL($url);
	}

	public static function listMembers($groupName){
	    $url = "https://bluepages.ibm.com/tools/groups/groupsxml.wss?task=listMembers&group=" . urlencode($groupName) . "&depth=1";
	    $myXMLData =  self::getBgResponseXML($url);

	    $xml=simplexml_load_string($myXMLData);

        return get_object_vars($xml)['member'];

	    // $simple = "<para><note>simple note</note></para>";
// 	    $p = xml_parser_create();
// 	    xml_parse_into_struct($p, $xml, $vals, $index);
// 	    print_r($vals);
	}

	public static function inAGroup($groupName, $ssoEmail, $depth=1){
	    // https://bluepages.ibm.com/tools/groups/groupsxml.wss?task=inAGroup&email=MEMBER_EMAIL_ADDRESS&group=GROUP_NAME[&depth=DEPTH]
	    $url = "https://bluepages.ibm.com/tools/groups/groupsxml.wss?task=inAGroup&email=" . urlencode($ssoEmail) . "&group=" . urlencode($groupName) . "&depth=" . urlencode($depth);
	    $myXMLData =  self::getBgResponseXML($url);
	    $xml=simplexml_load_string($myXMLData);
	    return get_object_vars($xml)['msg']=='Success';

	}

	public static function getUID($email){
	    $details = BluePages::getDetailsFromIntranetId($email);
	    return $details['CNUM'];
	}

	private static function createCurl($agent='ITDQ'){
			// create a new cURL resource
		$ch = curl_init();
//		curl_setopt($ch, CURLOPT_HEADER,         1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//		curl_setopt($ch, CURLOPT_TIMEOUT,        240);
//		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 240);
//		curl_setopt($ch, CURLOPT_USERAGENT,      $agent);
//		curl_setopt($ch, CURLOPT_CAINFO,        '/cecert/cacert.pem');
//		curl_setopt($ch, CURLOPT_CAINFO,        '/usr/local/zendsvr6/share/curl/cacert.pem');
//		curl_setopt($ch, CURLOPT_HTTPAUTH,        CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_HEADER,        FALSE);
// 		$userpwd = $_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW'];
// 		$ret = curl_setopt($ch, CURLOPT_USERPWD,        $userpwd);
		return $ch;
	}

	private static function processURL($url){
		$ch = self::createCurl();
		foreach($url as $function => $BGurl){
			echo "<BR>Processing $function.";
			echo "URL:" . $BGurl;
			$ret = curl_setopt($ch, CURLOPT_URL, $BGurl);

			var_dump($ret);

			$ret = curl_exec($ch);

			var_dump($ret);

			if (empty($ret)) {
				//     some kind of an error happened
   		 		die(curl_error($ch));
   		 		curl_close($ch); // close cURL handler
			} else {
   				$info = curl_getinfo($ch);
   			 	if (empty($info['http_code'])) {
   		     	    die("No HTTP code was returned");
   		 		} else {
   		 			// So Bluegroups has processed our URL - What was the result.
   		 			$bgapiRC  = substr($ret,0,1);
   		 			if($bgapiRC!=0){
   		 				// Bluegroups has NOT returned a ZERO - so there was a problem
   		 				echo "<H3>Error processing Bluegroup URL </H3>";
   		 				echo "<H2>Please take a screen print of this page and send to the ITDQ Team ASAP.</H2>";
   		 				echo "<BR>URL<BR>";
   		 				print_r($url);
   		 				echo "<BR>Info<BR>";
   		  				print_r($info);
   		  				echo "<BR>";
   		  				exit ("<B>Unsuccessful RC: $ret</B>");
   		 			} else {
   		 				echo " Successful RC: $ret";
   		 				sleep(1); // Give BG a chance to process the request.
   		 			}
   		 		}
			}
		}
	}

	private static function getBgResponseXML($url){
	    $ch = self::createCurl();

	    curl_setopt($ch, CURLOPT_URL, $url);

        $ret = curl_exec($ch);
        if (empty($ret)) {
            //     some kind of an error happened
            die(curl_error($ch));
            curl_close($ch); // close cURL handler
        } else {
            $info = curl_getinfo($ch);
            if (empty($info['http_code'])) {
                die("No HTTP code was returned");
            } else {
                // So Bluegroups has processed our URL - What was the result.
                $bgapiRC  = substr($ret,0,1);
                if($bgapiRC!=0){
                    // Bluegroups has NOT returned a ZERO - so there was a problem
                    echo "<H3>Error processing Bluegroup URL </H3>";
                    echo "<H2>Please take a screen print of this page and send to the ITDQ Team ASAP.</H2>";
                    echo "<BR>URL<BR>";
                    print_r($url);
                    echo "<BR>Info<BR>";
                    print_r($info);
                    echo "<BR>";
                    exit ("<B>Unsuccessful RC: $ret</B>");
                } else {
                    return $ret;
                }
	        }
	    }
	}
}
?>