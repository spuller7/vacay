<?php
//////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2008, Quantum Signal, LLC
// 
// This data and information is proprietary to, and a valuable trade 
// secret of, Quantum Signal, LLC.  It is given in confidence by Quantum 
// Signal, LLC. Its use, duplication, or disclosure is subject to the
// restrictions set forth in the License Agreement under which it has 
// been distributed.
//
//////////////////////////////////////////////////////////////////////

require_once( dirname( __FILE__ )."/../authenticate.php" );
require_once( dirname(__FILE__)."/../common.php" );
require_once( dirname(__FILE__)."/../utils.php" );
require_once( dirname(__FILE__)."/../DB/DBManager.php" );

class AdminUser
{
	public $Username;
	public $PasswordHash;
	public $FullName;
	public $State;
	public $PartTime;
	public $CreateDate;
	public $LastUpdate;
	public $UserID;
	
	public static function create( $username, $passwordHash, $FullName = "", $State = "active", $PartTime = false )
	{
		executeSQL( "INSERT INTO Users ( Username, PasswordHash, FullName, State, PartTime, CreateDate, LastUpdate ) VALUES ( :username, :passwordHash, :fullName, :state, :parttime, NOW(), NOW() )",
			array ('username' => $username, 'passwordHash' => $passwordHash, 'fullName' => $FullName, 'state' => $State, 'parttime' => $PartTime ) );
		return AdminUser::retrieveByName( $username );
	}
	
	public static function updatePasswordById( $userid, $passwordHash )
	{
		executeSQL( "UPDATE Users SET PasswordHash = :passwordHash WHERE UserID = :userid",
			array ('userid' => $userid, 'passwordHash' => $passwordHash ) );
	}
	
	public static function updateFullNameById( $userid, $fullName )
	{
		executeSQL( "UPDATE Users SET FullName = :fullName WHERE UserID = :userid",
			array ('userid' => $userid, 'fullName' => $fullName ) );
	}
	
	public static function updateUsernameById( $userid, $userName )
	{
		executeSQL( "UPDATE Users SET Username = :userName WHERE UserID = :userid",
			array ('userid' => $userid, 'userName' => $userName ) );
	}
	
	public static function retrieve( $UserID)
	{
		$sth = executeSQL( "SELECT UserID, Username, PasswordHash, FullName, State, PartTime, CreateDate, LastUpdate FROM Users WHERE UserID = :UserID",
			array( 'UserID'=>$UserID ) );
		
		$user = new AdminUser();
		$sth->setFetchMode( PDO::FETCH_INTO, $user );
		if ( !$sth->fetch() )
		{
			return null;
		}
		return $user;
	}
	
	public static function retrieveByName( $Username)
	{
		$userid = self::getUserID($Username);
		return self::retrieve($userid);
	}
	
	public static function getUserID( $Username )
	{
		$sth = executeSQL( "SELECT UserID FROM Users WHERE UserName = :Username",
			array( 'Username'=>$Username ) );
		return $sth->fetchColumn(0);
	}
	
	public static function getUserName( $Userid )
	{
		$sth = executeSQL( "SELECT Username FROM Users WHERE UserID = :Userid",
			array( 'Userid'=>$Userid ) );
		return $sth->fetchColumn(0);
	}

	public static function deleteById( $userid )
	{
		//TODO: don't let them delete a primary group authorizer
		executeSQL( "DELETE FROM Users WHERE UserID = :Userid",
			array( 'Userid'=>$userid ) );
	}
		
	public static function listUsers( $activeOnly = false )
	{
		$whereClause = "";
		if ($activeOnly) $whereClause = "WHERE State = 'active' ";
		$sth = executeSQL( "SELECT UserID, Username, PasswordHash, FullName, State, PartTime, CreateDate, LastUpdate FROM Users ".$whereClause."ORDER BY Username",
			array() );
		return $sth->fetchALL(PDO::FETCH_CLASS,'AdminUser');
	}
	
	public static function listActiveUsers()
	{
		$sth = executeSQL( "SELECT UserID, Username, PasswordHash, FullName, State, PartTime, CreateDate, LastUpdate FROM Users WHERE State = 'active' ORDER BY Username",
			array() );
		return $sth->fetchALL(PDO::FETCH_CLASS,'AdminUser');
	}
	
	public static function listUsersUnderAuthorizer( $authid )
	{
		if (hasRole(Roles::ADMINISTRATOR))
			return self::listUsers();
		$sth = executeSQL( "SELECT UserID, Username, PasswordHash, FullName, State, CreateDate, PartTime, LastUpdate FROM Users WHERE UserID IN (SELECT UserID FROM GroupMembership WHERE GroupID IN (SELECT GroupID FROM GroupAuthorizers WHERE UserID = ".$authid." UNION SELECT GroupID FROM Groups WHERE Authorizer = ".$authid.")) ORDER BY Username",
			array( ) );
		$users = null;
		while ( true )
		{
			$user = new AdminUser();
			$sth->setFetchMode( PDO::FETCH_INTO, $user );
			if ( !$sth->fetch() )
			{
				break;
			}
			$users[] = $user;
		}
		return $users;
	}
	
	public static function listGroupsById($Userid)
	{
		$sth = executeSQL( "SELECT GroupName, Authorizer, CreateDate, LastUpdate, GroupID FROM Groups WHERE GroupID IN (SELECT GroupID FROM GroupMembership WHERE UserID = :userid) ORDER BY GroupName",
			array( 'userid' => $Userid ) );
		return $sth->fetchALL(PDO::FETCH_CLASS, 'AdminGroup');
	}	
	
	public function roles()
	{	
		return $this->rolesById();
	}
	
	public function rolesById()
	{
		$sth = executeSQL( "SELECT Role FROM Roles WHERE UserID = :userid",
			array( 'userid' => $this->UserID ) );
		$roles = array();
		$result = $sth->fetchAll();
		foreach ( $result as $row )
		{
			$roles[$row[0]] = true;
		}
		return $roles;
	}
	
	public function setRole( $role, $isSet )
	{
		if ( $isSet )
		{
			executeSQL( "REPLACE INTO Roles ( UserID, Role, CreateDate, LastUpdate ) VALUES ( :UserID, :Role, NOW(), NOW() )",
				array( 'UserID' => $this->UserID, 'Role' => $role ) );			
		}
		else
		{		
			executeSQL( "DELETE FROM Roles WHERE UserID = :UserID AND Role = :Role",
				array( 'UserID' => $this->UserID, 'Role' => $role ) );
		}
	}	
	
	public static function isAuthorizer($userid)
	{
		$sth = executeSQL( "SELECT Count(*) FROM Groups WHERE Authorizer = :userid",
			array( 'userid' => $userid ) );
		$count = $sth->fetchColumn(0);
		if ($count > 0) return true;
		$sth = executeSQL( "SELECT Count(*) FROM GroupAuthorizers WHERE UserID = :userid",
			array( 'userid' => $userid ) );
		$count = $sth->fetchColumn(0);
		if ($count > 0) return true;
		return false;
	}
	
	public static function getAvailableStates()
	{
		$sth = executeSQL( "SHOW COLUMNS FROM Users LIKE 'State'",
			array() );
		$obj = $sth->fetch( PDO::FETCH_OBJ );
		$enumString = $obj->Type;
		$enumString = substr($enumString, strpos($enumString, "(") + 1, (strpos($enumString, ")") - strpos($enumString, "(")) - 1 );
		$stateArray = explode(",", $enumString);
		foreach ($stateArray as $key => $value)
		{
			$stateArray[$key] = substr($value, 1, -1);
		}
		return $stateArray;
	}
	
	public static function updateStateById( $Userid, $state)
	{
		executeSQL( "UPDATE Users SET State=:state WHERE UserID=:Userid",
			array( 'Userid' => $Userid, 'state' => $state ) );	
	}
	
	public static function updatePartTimeStatusById($Userid, $ptstatus)
	{
		if ($ptstatus)
			executeSQL( "UPDATE Users SET PartTime=1 WHERE UserID=:Userid", array( 'Userid' => $Userid ) );
		else
			executeSQL( "UPDATE Users SET PartTime=0 WHERE UserID=:Userid", array( 'Userid' => $Userid ) );
	}
}

class AdminGroup
{
	public $GroupName;
	public $Authorizer;
	public $CreateDate;
	public $LastUpdate;
	public $GroupID;
	
	public static function createById( $groupname, $authid )
	{
		executeSQL( "INSERT INTO Groups ( GroupName, Authorizer, CreateDate, LastUpdate ) VALUES ( :groupname, :authorizer, NOW(), NOW() )",
			array ('groupname' => $groupname, 'authorizer' => $authid ) );
		return AdminGroup::retrieveByName( $groupname );
	}
	
	public static function retrieve( $GroupID)
	{
		$sth = executeSQL( "SELECT GroupName, Authorizer, CreateDate, LastUpdate, GroupID FROM Groups WHERE GroupID = :GroupID",
			array( 'GroupID'=>$GroupID ) );
		
		$group = new AdminGroup();
		$sth->setFetchMode( PDO::FETCH_INTO, $group );
		if ( !$sth->fetch() )
		{
			return null;
		}
		return $group;
	}
	
	public static function retrieveByName( $GroupName)
	{
		$sth = executeSQL( "SELECT GroupName, Authorizer, CreateDate, LastUpdate, GroupID FROM Groups WHERE GroupName = :GroupName",
			array( 'GroupName'=>$GroupName ) );
		
		$group = new AdminGroup();
		$sth->setFetchMode( PDO::FETCH_INTO, $group );
		if ( !$sth->fetch() )
		{
			return null;
		}
		return $group;
	}
	
	public static function getGroupID($Groupname)
	{
		$sth = executeSQL( "SELECT GroupID FROM Groups WHERE GroupName = :GroupName",
			array( 'GroupName'=>$Groupname ) );
		return $sth->fetchColumn(0);
	}
	
	public static function deleteById( $GroupID)
	{
		executeSQL( "DELETE FROM Groups WHERE GroupID = :GroupID",
			array( 'GroupID'=>$GroupID ) );
	}

	public static function updateGroupName( $GroupID, $NewName )
	{
		executeSQL( "UPDATE Groups SET GroupName = :groupname WHERE GroupID = :GroupID",
			array( 'groupname'=> $NewName, 'GroupID'=>$GroupID ) );
	}
	
	public static function listGroups()
	{
		$sth = executeSQL( "SELECT GroupName, Authorizer, CreateDate, LastUpdate, GroupID FROM Groups ORDER BY GroupName",
			array( ) );
		$groups = null;
		while ( true )
		{
			$group = new AdminGroup();
			$sth->setFetchMode( PDO::FETCH_INTO, $group );
			if ( !$sth->fetch() )
			{
				break;
			}
			$groups[] = $group;
		}
		return $groups;
	}
	
	public static function listUsersById($groupid)
	{
		$sth = executeSQL( "SELECT Username, PasswordHash, FullName, State, CreateDate, LastUpdate, UserID FROM Users WHERE UserID IN (SELECT UserID FROM GroupMembership WHERE GroupID = :Groupid) ORDER BY Username",
			array( 'Groupid'=>$groupid ) );
		$users = null;
		while ( true )
		{
			$user = new AdminUser();
			$sth->setFetchMode( PDO::FETCH_INTO, $user );
			if ( !$sth->fetch() )
			{
				break;
			}
			$users[] = $user;
		}
		return $users;
	}
	
	public static function addMembershipById( $userid, $groupid )
	{
		executeSQL( "INSERT INTO GroupMembership ( UserID, GroupID, CreateDate, LastUpdate ) VALUES ( :userid, :groupid, NOW(), NOW() )",
			array ('userid' => $userid, 'groupid' => $groupid ) );
	}
	
	public static function removeMembershipById( $userid, $groupid )
	{
		executeSQL( "DELETE FROM GroupMembership WHERE UserID = :userid AND GroupID = :groupid",
			array ('userid' => $userid, 'groupid' => $groupid ) );
	}
	
	public static function addAuthorizerById( $userid, $groupid )
	{
		executeSQL( "INSERT INTO GroupAuthorizers ( UserID, GroupID, CreateDate, LastUpdate ) VALUES ( :userid, :groupid, NOW(), NOW() )",
			array ('userid' => $userid, 'groupid' => $groupid ) );
	}
	
	public static function removeAuthorizerById( $userid, $groupid )
	{
		executeSQL( "DELETE FROM GroupAuthorizers WHERE UserID = :userid AND GroupID = :groupid",
			array ('userid' => $userid, 'groupid' => $groupid ) );
	}
	
	public static function getAuthorizersById( $groupid )
	{
		$sth = executeSQL( "SELECT Username, PasswordHash, FullName, State, CreateDate, LastUpdate, UserID FROM Users WHERE UserID IN (SELECT UserID FROM GroupAuthorizers WHERE GroupID = :groupid) ORDER BY Username",
			array( 'groupid'=>$groupid ) );
		$users = null;
		while ( true )
		{
			$user = new AdminUser();
			$sth->setFetchMode( PDO::FETCH_INTO, $user );
			if ( !$sth->fetch() )
			{
				break;
			}
			$users[] = $user;
		}
		return $users;
	}
	
	public static function getPrimaryAuthorizerById( $groupid )
	{
		$sth = executeSQL( "SELECT Username, PasswordHash, FullName, State, CreateDate, LastUpdate, UserID FROM Users WHERE UserID IN (SELECT Authorizer FROM Groups WHERE GroupID = :groupid)",
			array( 'groupid'=>$groupid ) );
		
		$user = new AdminUser();
		$sth->setFetchMode( PDO::FETCH_INTO, $user );
		if ( !$sth->fetch() )
		{
			return null;
		}
		return $user;
	}
	
	public static function setPrimaryAuthorizerById( $userid, $groupid )
	{
		executeSQL( "UPDATE Groups SET Authorizer = :userid WHERE GroupID = :groupid",
			array ('userid' => $userid, 'groupid' => $groupid ) );
	}
}

?>