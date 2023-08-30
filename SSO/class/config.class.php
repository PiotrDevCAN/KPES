<?php
	//Class used to get any configuration data from cfg/ folder
	class Config {

		private $technology = false;

		//gets config for different kinds of supported authenticate technologies and returns as stdClass()
		public function getConfig($technology)
		{
			if($this->setAuthTechnology($technology))
			{
				switch (mb_strtolower($technology)) {
					case "openidconnect":
						return $this->getAuthConfigForOpenID();
						break;
				}
			}
			else
			{
				throw new Exception(htmlentities($technology).' not yet implemented.');
			}
		}

		//gets which technology to use, from auth_technology.inc
		//returns string
		public function getTechnology()
		{
			include realpath(dirname(__FILE__))."/../cfg/auth-technology.inc.php";
			return $auth_technology;
		}

		//sets $technology variable if valid technology supplied
		//returns boolean
		private function setAuthTechnology($technology)
		{
			switch (mb_strtolower($technology))
			{
				case "ldap":
					return false;
					break;
				case "openidconnect":
					$this->technology = $technology;
					return true;
					break;
				case "saml":
					return false;
					break;
			}
			return false;
		}

		//gets auth config for openidconnect technology
		private function getAuthConfigForOpenID()
		{
			include realpath(dirname(__FILE__))."/../cfg/openidconnect.inc.php";
			return $config_openidconnect;
		}
	}
?>