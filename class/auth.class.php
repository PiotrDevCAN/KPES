<?php
	class Auth {
		private $config = false;
		private $technology = false;

		//construct function, automatically gets which technology to use and loads its config
		function __construct() {
			$technology = $this->getTechnology();
			switch (mb_strtolower($technology)) {
				case "openidconnect":
					$this->config = $this->loadOpenIDConnectConfig();
					$this->technology = mb_strtolower($technology);
					break;
				default:
					throw new \Exception(htmlentities($technology).' not yet implemented.');
			}
		}

		//makes sure that user is authorized
		//returns boolean
		public function ensureAuthorized()
		{
			if(isset($_SESSION['uid']) && isset($_SESSION['exp']) && ($_SESSION['exp']-300) > time()) return true;

			switch ($this->technology) {
				case "openidconnect":
					$this->authenticateOpenIDConnect();
					break;
			}
			return false;
		}

		//verifies response from authentication service depending on technologies
		//returns boolean
		public function verifyResponse($response)
		{
			switch ($this->technology) {
				case "openidconnect":
					return $this->verifyCodeOpenIDConnect($response['code']);
					break;
			}
		}

		/********* OPEN ID CONNECT RELATED FUNCTIONS *********/

		//verifies openID response
		private function verifyCodeOpenIDConnect($code)
		{
		    $url = $this->config->token_url;

		    $fields = array(
				'code' => $code,
				'client_id' => $this->config->client_id,
				'client_secret' => $this->config->client_secret,
				'redirect_uri' => $this->config->redirect_url,
				'grant_type' => 'authorization_code'
			);

			$postvars = http_build_query($fields);
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, count($fields));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);

			$result = curl_exec($ch);

			curl_close($ch);

			return $this->processOpenIDConnectCallback($result);
		}

		//processes openid data and sets session
		//returns boolean
		private function processOpenIDConnectCallback($data)
		{
			$token_response = json_decode($data);
			if($token_response)
			{
				if(isset($token_response->error)) {
					var_dump($token_response->error);	
					throw new \Exception('Error happened while authenticating. Please, try again later.');
				}
				if ( isset( $token_response->id_token ) ) {
					$jwt_arr = explode('.', $token_response->id_token );
					$encoded = $jwt_arr[1];
					$decoded = "";
					for ($i=0; $i < ceil(strlen($encoded)/4); $i++)
						$decoded = $decoded . base64_decode(substr($encoded,$i*4,4));
					$userData = json_decode( $decoded, true );
				} else {
					return false;
				}

				//use this to debug returned values from w3id/IBM ID service if you got to else in the condition below
				//var_dump($userData);
				//die();

				//if using this code on w3ID
				if(isset($userData) && !empty($userData)
					&& isset($userData['emailAddress']) && !empty($userData['emailAddress'])
					&& isset($userData['firstName']) && !empty($userData['firstName'])
					&& isset($userData['lastName']) && !empty($userData['lastName'])
					&& isset($userData['exp']) && !empty($userData['exp'])
					&& isset($userData['uid']) && !empty($userData['uid'])
					)
				{
					$_SESSION['ssoEmail'] = $userData['emailAddress'];
					$_SESSION['firstName'] = $userData['firstName'];
					$_SESSION['lastName'] = $userData['lastName'];
					$_SESSION['exp'] = $userData['exp'];
					$_SESSION['uid'] = $userData['uid'];
					return true;
				}
				//if using this code on IBM ID
				else if(isset($userData) && !empty($userData)
					&& isset($userData['ssoEmail']) && !empty($userData['email'])
					&& isset($userData['given_name']) && !empty($userData['given_name'])
					&& isset($userData['family_name']) && !empty($userData['family_name'])
					&& isset($userData['exp']) && !empty($userData['exp'])
					&& isset($userData['uniqueSecurityName']) && !empty($userData['uniqueSecurityName'])
					)
				{
					$_SESSION['ssoEmail'] = $userData['email'];
					$_SESSION['firstName'] = $userData['given_name'];
					$_SESSION['lastName'] = $userData['family_name'];
					$_SESSION['exp'] = $userData['exp'];
					$_SESSION['uid'] = $userData['uniqueSecurityName'];
					return true;
				}
				//if something in the future gets changed and the strict checking on top of this is not working any more
				//please note, that you should always use strict matching in this function on your prod app so that you can handle changes correctly and not fill in the session with all the data
				//so basically, if you get to the else below, adjust it, open an issue on github so that the strict matching can be adjusted and it doesnt get to the else below
				else
				{
					//throw new \Exception('OpenIDConnect returned values were not correct.');
					$_SESSION = $userData;
					$_SESSION['somethingChanged'] = true;
					return true;
				}
			}
			return false;
		}

		//gets technology to use for authenticating
		//uses Config
		//returns string
		private function getTechnology()
		{
			$cfg = new Config();
			return $cfg->getTechnology();
		}

		//starts authentication process and redirects user to service for authorizing
		//returns exit();
		private function authenticateOpenIDConnect()
		{
		    $authorizedUrL = $this->generateOpenIDConnectAuthorizeURL();
		    error_log(__CLASS__ . __FUNCTION__ . __LINE__. " About to pass to  : " . $authorizedUrL);
		    header("Access-Control-Allow-Origin: *");
			header("Location: ".$authorizedUrL);
			exit();
		}

		//generates correct openidconnect authorize URL
		//returns string
		private function generateOpenIDConnectAuthorizeURL()
		{
			$current_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$authorizeString = $this->config->authorize_url . "?scope=openid&response_type=code&client_id=".$this->config->client_id."&state=".urlencode($current_link)."&redirect_uri=".$this->config->redirect_url;
            return $authorizeString;
		}

		//loads openidconnect
		//uses Config
		//returns stdClass
		private function loadOpenIDConnectConfig()
		{
			$cfg = new Config();
			$authData = $cfg->getConfig("openidconnect");
			if($this->verifyOpenIDConnectConfig($authData))
			{
				return $authData;
			}
			else
			{
				throw new \Exception('OpenIDConnect data not correct. Please check if everything is filled out in OpenIDConnect configuration.');
			}
		}

		//verifies if all openidconnect config data are filled out correctly
		//returns boolean
		private function verifyOpenIDConnectConfig($config)
		{
			if(isset($config) && !empty($config)
			    && isset($config->authorize_url) && !empty($config->authorize_url)
			    && isset($config->token_url) && !empty($config->token_url)
			    && isset($config->introspect_url) && !empty($config->introspect_url)
				&& isset($config->client_id) && !empty($config->client_id)
				&& isset($config->client_secret) && !empty($config->client_secret)
				&& isset($config->redirect_url) && !empty($config->redirect_url)
				)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
?>