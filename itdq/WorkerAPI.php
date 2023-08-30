<?php
namespace itdq;

use WorkerApi\Auth;

include_once "WorkerAPI/class/include.php";

/*
 *  Handles Worker API.
 */

class WorkerAPI {
	
	private $token = null;
	private $hostname_int = null;
	private $hostname_ext = null;

	public function __construct()
	{
		$auth = new Auth();
		$auth->ensureAuthorized();

		$this->hostname_int = $_ENV['worker_api_host_int'];
		$this->hostname_ext = $_ENV['worker_api_host_ext'];

		// echo $_SESSION['worker_token'];
		$this->token = $_SESSION['worker_token'];
	}

	private function createCurl($type = "GET"){
		// create a new cURL resource
		$ch = curl_init();
		$authorization = "Authorization: Bearer ".$this->token; // Prepare the authorisation token
		$headers = [
			'Content-type: Not defined',
			'Accept: application/json, text/json, application/xml, text/xml',
			$authorization,
		];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		return $ch;
	}

	private function processURL($url, $type = 'GET'){
		$url = $this->hostname_ext . $url;
		$ch = $this->createCurl($type);
		// echo "<BR>Processing";
		// echo " URL:" . $url;
		$ret = curl_setopt($ch, CURLOPT_URL, $url);
		$ret = curl_exec($ch);
		if (empty($ret)) {
			// some kind of an error happened
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
					// echo " Successful RC: $ret";
					sleep(1); // Give BG a chance to process the request.
				}
			}
		}
		return $ret;
	}

	// Individual Worker Profile Data
	// Below endpoints will return extended worker data model

	// Worker Profile By Worker Id : GET /workers/wid/{wid}
	public function getworkerByWId($wid)
	{
		$url = "/workers/wid" . urlencode($wid);
		return $this->processURL($url);
	}

	// Worker Profile By CNUM : GET /workers/cnum/{cnum}
	public function getworkerByCNUM($cnum)
	{
		$url = "/workers/cnum/" . urlencode($cnum);
		return $this->processURL($url);
	}

	// Worker Profile By Email : GET /workers/email/{email}
	public function getworkerByEmail($email)
	{
		$url = "/workers/email/" . urlencode($email);
		return $this->processURL($url);
	}

	// Worker Profile By Dynamic Id : GET /workers/dynamicid/{Dynamic_id}
	public function getworkerByDynamicId($dynamicId)
	{
		$url = "/workers/dynamicid" . urlencode($dynamicId);
		return $this->processURL($url);
	}

	// Manager Profile By Dynamic Id : GET /workers/dynamicid/{Dynamic_id}/manager
	public function getManager($dynamicId)
	{
		$url = "/workers/dynamicid/" . urlencode($dynamicId) . "/manager";
		return $this->processURL($url);
	}

	public function getReports($dynamicId)
	{
		$url = "/workers/dynamicid/" . urlencode($dynamicId) . "/reports";
		return $this->processURL($url);
	}

	public function getPhoto($dynamicId)
	{
		$url = "/workers/dynamicid/" . urlencode($dynamicId) . "/photo";
		return $this->processURL($url);
	}

	// Worker Profile By Attribute Search : GET /workers/search/
	public function getworkerByAttributeSearch($search)
	{
		$url = "/workers/search";
		return $this->processURL($url);
	}

	// Worker Profile By Multi Attribute Search : POST /workers/search/
	public function getworkerByMultiAttributeSearch($search)
	{
		$url = "/workers/search";
		return $this->processURL($url, 'POST');
	}

	public function typeaheadSearch()
	{
		$url = "/workers/typeahead";
		return $this->processURL($url);
	}

	public function typeaheadSearchPost()
	{
		$url = "/workers/typeahead";
		return $this->processURL($url, 'POST');
	}
	
	// Direct Reports Profile Data
	// To return a worker's direct reports data alone, the below endpoint will return an array of basic worker profiles for all direct reports.

	// Reports Profile By Dynamic Id : GET /workers/dynamicid/{Dynamic_id}/reports
	public function getReportsFromDynamicId($dynamicId)
	{
		$url = "/workers/dynamicid/" . urlencode($dynamicId) . "/reports";
		return $this->processURL($url);
	}

	// Bulk Data/ File Cache Return
	// To return a larger object of bulk data from the larger worker collection, the below endpoint will query a periodically-updated flat file.

	// Download Worker File : GET /tools/download_worker_file
	public function getWorkerFile()
	{
		$url = "/tools/download_worker_file";
		return $this->processURL($url);
	}

	// Delta File Cache Return
	// Return any delta in data including added, updated, deleted worker records at set intervals.

	// Download Worker File Delta : GET /tools/download_worker_file_delta
	public function getWorkerFileDelta()
	{
		$url = "/tools/download_worker_file_delta";
		return $this->processURL($url);
	}
}