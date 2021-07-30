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

class Roles
{	
	//general roles
	const TRACKER = "TRACKER";
	const VIEW_PRIVATE = "VIEW_PRIVATE";
	const REPORTER = "REPORTER";
	const USER_MANAGER = "USER_MANAGER";
	const TRACKER_MANAGER = "TRACKER_MANAGER";
	const REPORT_MANAGER = "REPORT_MANAGER";
	const ADMINISTRATOR = "ADMINISTRATOR";
	const MANAGER = "MANAGER";

	static $implications = array(
		Roles::ADMINISTRATOR => array(
			Roles::TRACKER,
			Roles::REPORTER,
			Roles::USER_MANAGER,
			Roles::TRACKER_MANAGER,
			Roles::REPORT_MANAGER,
			Roles::VIEW_PRIVATE,
			),
		Roles::REPORTER => array( Roles::TRACKER ),
		);
	
	public static function getDescription( $role )
	{	
	  	$sth = executeSQL( "SELECT Description FROM RoleDescriptions WHERE Role = :role",
			array( 'role' => $role ) );

		$desc = $sth->FetchColumn(0);
		if ( $desc == null )
		{
			$desc = $role;
		}
		return $desc;
	}
		
	public static function setImplications( $roles )
	{
		$keys = array_keys( $roles );
		$iterate = true;
		while ( $iterate )
		{
			$iterate = false;
			foreach ( $keys as $key )
			{
				if ( isset( Roles::$implications[$key] ) )
				{
					foreach ( Roles::$implications[$key] as $aRole )
					{
						if ( !isset( $roles[$aRole]))
						{
							$roles[$aRole] = true;
							$iterate = true;
						}
					}
				}
			}
		}
		return $roles;
	}
	
	public static function getRoles( $userid )
	{
	  	$sth = executeSQL( "SELECT Role FROM Roles WHERE UserID = :userid",
			array( 'userid' => $userid ) );

		$roles = array();
		$result = $sth->fetchAll();
		foreach ( $result as $row )
		{
			$roles[$row[0]] = true;
		}
		return Roles::setImplications( $roles );
	}

}

function hasRole( $role )
{
	if ( isset( $_SESSION['ROLES'] ) )
	{
		if ( isset( $_SESSION['ROLES'][$role] ) )
		{
			return $_SESSION['ROLES'][$role];
		}
		if ( isset( $_SESSION['ROLES']['ROOT']))
		{
			return true;
		}
	}
	return false;
}

function roles()
{
	return $_SESSION['ROLES'];
}

class MissingRoleException extends Exception
{
	public function __construct( $role )
	{
		parent::__construct( "Missing required role: ".$role, 0 );
	}
}

function checkRole( $role )
{
	if ( !hasRole( $role ) )
	{
		header( "HTTP/1.1 403 Forbidden" );
		echo "This account does not have sufficient privileges to perform this operation";
		die();
	}
}