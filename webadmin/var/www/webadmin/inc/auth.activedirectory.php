<?php
/**
* Active Directory Authentication Class
* No need of Proxy User anymore
* But be aware that your Users can see which Group they are Members of
*/
class auth_activedirectory {
	private $Domain;
	private $Groups = array();
	private $SearchDN;
	private $Connection;
	private $Username;

	/**
	* Constructor
	*
	* @param Object $Config
	* 	reading Domain, SearchDN and Allowed Groups from config
	*
	* @return boolean
	*	Return true if successfully connected, false if not
	*/
	public function __construct($Config, $NoSSL = false) {
		$this->Domain = $Config->getLdapSetting("domain");
		$this->SearchDN = $Config->getLdapSetting("searchdn");
		$this->Groups = $Config->getLdapSetting("groups");
		$this->connect($NoSSL);
	}


	/**
	* connect
	*
	* @return Boolean
	* 	Returns true if connected successfully, false if not
	*/
	private function connect($NoSSL) {
		if($NoSSL) {
			$this->Connection = ldap_connect($this->Domain);
		}
		else {
			$this->Connection = ldap_connect("ldaps://".$this->Domain);
		}
		/*
		* Setting options for Active Directory
		*/
		ldap_set_option($this->Connection, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($this->Connection, LDAP_OPT_REFERRALS, 0);
		if($this->Connection) {
			return true;
		}
		else {
			/*
			* Probably Active Directory isn't available (via ssl)?
			*/
			return false;
		}
	}

	/**
	* authenticate:
	* Authenticates against Active Directory using an ldap_bind.
	*
	* @param String $Username
	*	The Username of the User who tries to authenticate
	* @param String $Password
	*	The Password of the User who tries to authenticate
	*
	* @return Boolean
	*	Return true if User is enabled and the Password is right, false if not
	*
	*/
	public function authenticate($Username, $Password) {
		$this->Username = $Username;
		$Username = $Username."@".$this->Domain;
		$Bind = @ldap_bind($this->Connection, $Username, $Password);
		if($Bind) {
			return true;
		}
		else {
			/*
			* Username or Password mismatch.
			*/
			return false;
		}
	}

	/**
	* getDistinguishedName
	* Gets the DistinguishedName of an ActiveDirectory Object
	*
	* @param String $ADObject
	* 	The Name of the Object we are searching for
	*
	* @return Boolean or String
	* 	Returns false if no DN was found. Returns the DN if it was found.
	*/
	private function getDistinguishedName($ADObject) {
		$Query = sprintf("SamAccountName=%s", $ADObject);
		$Result = ldap_search($this->Connection, $this->SearchDN, $Query, array('dn'));
		if(!$Result) {
			/*
			* DN not found, probably login wasn't possible too then.
			*/
			return false;
		}
		else {
			$DNs = ldap_get_entries($this->Connection, $Result);
			if($DNs['count'] > 0) {
				return $DNs[0]['dn'];
			}
			else {
				/*
				* No Entry found!
				*/
				return false;
			}
		}
	}

	/**
	* isAuthorized
	* A check if the supplied user is Member of any of the allowed Groups
	*
	* @return boolean
	* Returns true if the User is a Member of the Allowed groups, if not false
	*
	*/
	public function isAuthorized() {
		$UserDN = $this->getDistinguishedName($this->Username);
		$GroupMemberships = $this->getGroups($UserDN);
		foreach($this->Groups as $Group) {
			if(in_array($this->getDistinguishedName($Group), $GroupMemberships)) {
			/*
			* If any Group the User is a Member of matches the Allowed groups -> Authorization success.
			*/
				return true;
			}
		}
		/*
		* This only happens when no Groupmembership matches the Allowed groups -> Authorization failed.
		*/
		return false;
	}

	/**
	* getGroups
	* Gets all (nested) groups from the supplied DistinguishedName
	*
	* @param String $DistinguishedName
	* The DistinguishedName of a Group or User whose Groupmemberships are searched for
	*
	* @return Array
	* Returns an Array of GroupDNs
	*
	*/
	private function getGroups($DistinguishedName) {
		$DNs = array();
		$Result = @ldap_read($this->Connection, $DistinguishedName, '(objectclass=*)', array('memberof'));
		if($Result) {
			$SubDNs = ldap_get_entries($this->Connection, $Result);
			if(!empty($SubDNs[0]["memberof"])) {
				for($i = 0; $i < $SubDNs[0]["memberof"]["count"]; $i++) {
					$DNs = 	array_merge(
								$DNs,
								array($SubDNs[0]["memberof"][$i]),
								$this->getGroups($SubDNs[0]["memberof"][$i])
							);
				}
			}
		}
		return $DNs;
	}

        /**
        * Close
        * Closes the LDAP Connection properly
        *
        * @return Boolean
        * Returns true if the was closed, if not false
        */
        public function close() {
                return ldap_close($this->Connection);
        }

}
?>
