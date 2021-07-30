<?php

include("LdapConfig.php");

	class LdapUser
	{
		var $username;
		var $uid;
		var $givenName;
		var $sn;
		var $mail;
		var $valid=false;
		var $hosts=array();

		function __construct($conn, $persons_name)
		{
			global $LdapPeopleOu;
			$this->username = $persons_name;
			$result = ldap_search($conn,$LdapPeopleOu,"uid=".$persons_name);
			$info = ldap_get_entries($conn, $result);
			if ($info)
			{
				//echo print_r($info);
				if (isset($info[0]["givenname"][0]))
					$this->givenName = $info[0]["givenname"][0];
				if (isset($info[0]["sn"][0]))
					$this->sn = $info[0]["sn"][0];
				if (isset($info[0]["uidnumber"][0]))
					$this->uid = ($info[0]["uidnumber"][0])|0;
				if (isset($info[0]["mail"][0]))
					$this->mail = $info[0]["mail"][0];
				if ( isset($info[0]["jpegphoto"][0]))
					$this->photo = base64_encode($info[0]["jpegphoto"][0]);
				
				if (isset($info[0]["host"]))
				{
					for ($i=0; $i<$info[0]["host"]["count"]; $i++)
						array_push($this->hosts,$info[0]["host"][$i]);
				}
				$this->valid = true;
			}
		}

		public static function FetchOne($persons_name)
		{
			global $LdapUri;
			$conn = ldap_connect($LdapUri);
			ldap_bind($conn) or die("Could not connect to LDAP server");
			return new LdapUser($conn, $persons_name);
		}

		public static function getAllUsers()
		{
			global $LdapUri, $LdapPeopleOu;
			$conn = ldap_connect($LdapUri);
			ldap_bind($conn) or die("Could not connect to LDAP server");
			$result = ldap_search($conn,$LdapPeopleOu,"(objectClass=posixAccount)");
			$info = ldap_get_entries($conn, $result);
			$userlist = array();
			for ($i=0; $i<$info["count"]; $i++)
			{
				if ( $info[$i]["uid"][0] == $info[$i]["displayname"][0] )
				{
					continue;
				}

				array_push($userlist, $info[$i]["cn"][0]);
			}
			return $userlist;
		}

		public static function getUserMap()
		{
			global $LdapUri, $LdapPeopleOu;
			$conn = ldap_connect($LdapUri);
			ldap_bind($conn) or die("Could not connect to LDAP server");
			$result = ldap_search($conn,$LdapPeopleOu,"(objectClass=posixAccount)");
			$info = ldap_get_entries($conn, $result);
			$userList = array();
			for ($i=0; $i<$info["count"]; $i++)
			{
				if ( $info[$i]["uid"][0] == $info[$i]["displayname"][0] )
				{
					continue;
				}
				
				$userList[$info[$i]["uidnumber"][0]] = $info[$i]["uid"][0];
			}
			return $userList;
		}		
		
		public static function authenticateUser( $userName, $password )
		{
			global $LdapUri, $LdapPeopleOu;			
			$conn = ldap_connect($LdapUri);
			ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			$dn = "uid=".$userName.",".$LdapPeopleOu;
			return (@ldap_bind($conn, $dn, $password));
		}

		function getCn()
		{
		 	 return $this->username;
		}

		function getFullName()
		{
		 	 return $this->givenName." ".$this->sn;
		}

		function getUid()
		{
		 	 return $this->uid;
		}

		function getMail()
		{
		 	 return $this->mail;
		}

		function getHosts()
		{
		 	 return $this->hosts;
		}

	}

	class LdapGroup
	{
		function __construct()
		{
		}

		public static function getAllGroups()
		{
			global $LdapUri, $LdapGroupOu;
			$conn = ldap_connect($LdapUri);
			ldap_bind($conn) or die("Could not connect to LDAP server");
			$result = ldap_search($conn,$LdapGroupOu,"(objectClass=posixGroup)");
			$info = ldap_get_entries($conn, $result);
			$grouplist = array();
			for ($i=0; $i<$info["count"]; $i++)
			{
				array_push($grouplist, $info[$i]["cn"][0]);
			}
			return $grouplist;
		}

		public static function getGroupMembers($groupname)
		{
			global $LdapUri, $LdapGroupOu;
			$conn = ldap_connect($LdapUri);
			ldap_bind($conn) or die("Could not connect to LDAP server");
			$result = ldap_search($conn,$LdapGroupOu,"cn=$groupname");
			$info = ldap_get_entries($conn, $result);
			$grouplist = array();
			if ( isset($info[0]["memberuid"]["count"]) )
			{
				for ($i=0; $i<$info[0]["memberuid"]["count"]; $i++)
					array_push($grouplist,$info[0]["memberuid"][$i]);
			}
			return $grouplist;
		}

		public static function getUserGroups($persons_name)
		{
			global $LdapUri, $LdapGroupOu;
			$conn = ldap_connect($LdapUri);
			ldap_bind($conn) or die("Could not connect to LDAP server");
			$result = ldap_search($conn,$LdapGroupOu,"(objectClass=posixGroup)");
			$info = ldap_get_entries($conn, $result);
			$grouplist = array();
			for ($i=0; $i<$info["count"]; $i++)
			{
				if (isset($info[$i]["memberuid"]))
				{
					foreach($info[$i]["memberuid"] as $groupmember)
					{
						if ($groupmember == $persons_name)
						{
							array_push($grouplist, $info[$i]["cn"][0]);
							break;
						}
					}
				}
			}
			return $grouplist;
		}

		public static function isUserInGroup($user, $group)
		{
			$groupMembers = LdapGroup::getGroupMembers($group);
			foreach ($groupMembers as $member)
			{
				if ($member == $user)
					return true;
			}
			return false;
		}

	}

?>
