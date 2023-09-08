<?php
namespace itdq;

use WorkerApi\Auth;

include_once "WorkerAPI/class/include.php";

/*
 *  Handles OKTA Groups.
 */
class OKTAGroups {

	private $token = null;
	private $hostname = null;

	public function __construct()
	{
		$auth = new Auth();
		$auth->ensureAuthorized();

		$this->hostname = trim($_ENV['worker_api_host']);

		// echo $_SESSION['worker_token'];
		$this->token = $_SESSION['worker_token'];
	}

	private function createCurl($type = "POST")
	{
		// create a new cURL resource
		$ch = curl_init();
		$authorization = "Authorization: SSWS ".$this->token; // Prepare the authorisation token
		$headers = [
			'Content-Type: application/json',
			'Accept: application/json, text/json, application/xml, text/xml',
			$authorization,
		];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		return $ch;
	}

	private function processURL($url, $type = 'GET')
	{
		$url = $this->hostname . $url;
		var_dump($url);
		$ch = $this->createCurl($type);
		// echo "<BR>Processing";
		// echo " URL:" . $url;
		$ret = curl_setopt($ch, CURLOPT_URL, $url);
		$ret = curl_exec($ch);

		$info = curl_getinfo($ch);
		
		var_dump($ret);
		var_dump($info);
		exit;

		$result = json_decode($ret, true);

		if (empty($ret)) {
			// some kind of an error happened
			// die(curl_error($ch));
			curl_close($ch); // close cURL handler
		} else {
			$info = curl_getinfo($ch);
			if (empty($info['http_code'])) {
				// die("No HTTP code was returned");
			} else if ($info['http_code'] == 500) {
				echo $result['message'];
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
					// echo " Successful RC: $ret";
					sleep(1); // Give BG a chance to process the request.
				}
			}
		}
		return $ret;
	}

	private function processURL__($url)
	{
		$ch = $this->createCurl();
		foreach($url as $function => $BGurl){
			echo "<BR>Processing $function.";
			echo "URL:" . $BGurl;
			$ret = curl_setopt($ch, CURLOPT_URL, $BGurl);
			$ret = curl_exec($ch);
		
			$result = json_decode($ret, true);

			if (empty($ret)) {
				//     some kind of an error happened
   		 		die(curl_error($ch));
   		 		curl_close($ch); // close cURL handler
			} else {
				$info = curl_getinfo($ch);
				if (empty($info['http_code'])) {
					// die("No HTTP code was returned");
				} else if ($info['http_code'] == 500) {
					echo $result['message'];
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

	private function getBgResponseXML($url)
	{
	    $ch = $this->createCurl();

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

	public function defineGroup($groupName,$description, $life=1)
	{
		$nextyear = time() + ((365*24*60*60) * $life);
		$yyyy = date("Y",$nextyear);
		$mm   = date("m",$nextyear);
		$dd   = date("d",$nextyear);
		$url = array();
		$url['Define_Group'] = "https://bluepages.ibm.com/tools/groups/protect/groups.wss?task=GoNew&selectOn=" . urlencode($groupName) . "&gDesc=" . urlencode($description) . "&mode=members&vAcc=Owner/Admins&Y=$yyyy&M=$mm&D=$dd&API=1";
		$this->processURL($url);
	}

	public function deleteMember($groupName,$memberEmail)
	{
		$memberUID = $this->getUID($memberEmail);
		$url = array();
		$url['Delete_Member'] = "https://bluepages.ibm.com/tools/groups/protect/groups.wss?Delete=Delete+Checked&gName=" . urlencode($groupName) . "&task=DelMem&mebox=" . urlencode($memberUID) . "&API=1";
		$this->processURL($url);
	}

	public function addMember($groupName,$memberEmail)
	{
		$memberUID = $this->getUID($memberEmail);
		$url = array();
		$url['Add_Member'] = "https://bluepages.ibm.com/tools/groups/protect/groups.wss?gName=" . urlencode($groupName) . "&task=Members&mebox=" . urlencode($memberUID) . "&Select=Add+Members&API=1";
		$this->processURL($url);
	}

	public function addAdministrator($groupName,$memberEmail)
	{
		$memberUID = $this->getUID($memberEmail);
		$url = array();
		$url['Add_Administrator'] = "https://bluepages.ibm.com/tools/groups/protect/groups.wss?gName=" . urlencode($groupName) . "&task=Administrators&mebox=" . urlencode($memberUID) . "&Submit=Add+Administrators&API=1 ";
		$this->processURL($url);
	}

	public function listMembers($groupName)
	{
		$url = "/api/v1/groups";
		return $this->processURL($url);

	    // $url = "https://bluepages.ibm.com/tools/groups/groupsxml.wss?task=listMembers&group=" . urlencode($groupName) . "&depth=1";
	    // $myXMLData =  $this->getBgResponseXML($url);

	    // $xml=simplexml_load_string($myXMLData);

        // return get_object_vars($xml)['member'];
	}

	public static function inAGroup($groupName, $ssoEmail, $depth=1)
	{

		return true;

	    // https://bluepages.ibm.com/tools/groups/groupsxml.wss?task=inAGroup&email=MEMBER_EMAIL_ADDRESS&group=GROUP_NAME[&depth=DEPTH]
	    $url = "https://bluepages.ibm.com/tools/groups/groupsxml.wss?task=inAGroup&email=" . urlencode($ssoEmail) . "&group=" . urlencode($groupName) . "&depth=" . urlencode($depth);
	    $myXMLData =  $this->getBgResponseXML($url);
	    $xml=simplexml_load_string($myXMLData);
	    return get_object_vars($xml)['msg']=='Success';

	}

	public function getUID($email)
	{
	    $details = BluePages::getDetailsFromIntranetId($email);
	    return $details['CNUM'];
	}
}
?>