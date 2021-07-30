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
require_once( dirname(__FILE__)."/../Settings/UserManager.php" );
require_once( dirname(__FILE__)."/../DB/DBManager.php" );

class Project
{
	public $ProjectID;
	public $CatID;
	public $ProjectCode;
	public $State;
	public $Type;
	public $SortOrder;
	
	public static function create( $ProjectCode, $CatID, $State = "active", $Type = "public" )
	{
		executeSQL( "INSERT INTO ProjectCodes ( ProjectCode, CatID, State, Type ) VALUES ( :projectcode, :catid, :state, :type )",
			array ('projectcode' => $ProjectCode, 'catid' => $CatID, 'state' => $State, 'type' => $Type ) );
		return Project::retrieveByCode( $ProjectCode );
	}
	
	public static function retrieveByCode( $ProjectCode )
	{
		$projectid = self::getProjectID($ProjectCode);
		return self::retrieveById($projectid);
	}
	
	public static function retrieveById( $ProjectID )
	{
		$sth = executeSQL( "SELECT ProjectID, CatID, ProjectCode, State, Type FROM ProjectCodes WHERE ProjectID = :projectid",
			array( 'projectid'=>$ProjectID ) );
		
		$code = new Project();
		$sth->setFetchMode( PDO::FETCH_INTO, $code );
		if ( !$sth->fetch() )
		{
			return null;
		}
		return $code;
	}
	
	public static function updateProjectName( $ProjectID, $NewName )
	{
		executeSQL( "UPDATE ProjectCodes SET ProjectCode = :projectname WHERE ProjectID = :ProjectID",
			array( 'projectname'=> $NewName, 'ProjectID'=>$ProjectID ) );
	}

	public static function getProjectID( $ProjectCode )
	{
		$sth = executeSQL( "SELECT ProjectID FROM ProjectCodes WHERE ProjectCode = :projectcode",
			array( 'projectcode'=>$ProjectCode ) );
		return $sth->fetchColumn(0);
	}
	
	public static function getProjectCodeFromID( $ProjectID )
	{
		$sth = executeSQL( "SELECT ProjectCode FROM ProjectCodes WHERE ProjectID = :projectid",
			array( 'projectid'=>$ProjectID ) );
		return $sth->fetchColumn(0);
	}
	
	public static function getStateById( $ProjectId )
	{
		$sth = executeSQL( "SELECT State FROM ProjectCodes WHERE ProjectID = :projectid",
			array( 'projectid'=>$ProjectId ) );
		return $sth->fetchColumn(0);
	}
	
	public static function updateStateById( $ProjectID, $State )
	{
		executeSQL( "UPDATE ProjectCodes SET State = :state WHERE ProjectID = :projectid",
			array ('projectid' => $ProjectID, 'state' => $State ) );
	}
	
	public static function getTypeById( $ProjectId )
	{
		$sth = executeSQL( "SELECT Type FROM ProjectCodes WHERE ProjectID = :projectid",
			array( 'projectid'=>$ProjectId ) );
		return $sth->fetchColumn(0);
	}
	
	public static function updateTypeById( $ProjectID, $Type )
	{
		executeSQL( "UPDATE ProjectCodes SET Type = :type WHERE ProjectID = :projectid",
			array ('projectid' => $ProjectID, 'type' => $Type ) );
	}
	
	public static function deleteById( $ProjectID )
	{
		executeSQL( "DELETE FROM ProjectCodes WHERE ProjectID = :projectid",
			array( 'projectid'=>$ProjectID ) );
	}	

	public static function setSortOrder( $ProjectID, $Position )
	{
		executeSQL( "UPDATE ProjectCodes SET SortOrder = :pos WHERE ProjectID = :projectid",
			array ('projectid' => $ProjectID, 'pos' => $Position ) );
	}
	
	public static function getSortOrder( $ProjectId )
	{
		$sth = executeSQL( "SELECT SortOrder FROM ProjectCodes WHERE ProjectID = :projectid",
			array( 'projectid'=>$ProjectId ) );
		return $sth->fetchColumn(0);
	}
		
	public static function listCodes()
	{
		$sth = executeSQL( "SELECT ProjectID, ProjectCode, State, Type FROM ProjectCodes ORDER BY ProjectCode",
			array() );
		$codes = null;
		while ( true )
		{
			$code = new Project();
			$sth->setFetchMode( PDO::FETCH_INTO, $code );
			if ( !$sth->fetch() )
			{
				break;
			}
			$codes[] = $code;
		}
		return $codes;
	}	
	
	public static function getAvailableStates()
	{
		$sth = executeSQL( "SHOW COLUMNS FROM ProjectCodes LIKE 'State'",
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
	
	public static function getAvailableTypes()
	{
		$sth = executeSQL( "SHOW COLUMNS FROM ProjectCodes LIKE 'Type'",
			array() );
		$obj = $sth->fetch( PDO::FETCH_OBJ );
		$enumString = $obj->Type;
		$enumString = substr($enumString, strpos($enumString, "(") + 1, (strpos($enumString, ")") - strpos($enumString, "(")) - 1 );
		$typeArray = explode(",", $enumString);
		foreach ($typeArray as $key => $value)
		{
			$typeArray[$key] = substr($value, 1, -1);
		}
		return $typeArray;
	}
	
	public static function addMembershipById($projectid, $userid)
	{
		executeSQL( "INSERT INTO ProjectMembership ( ProjectID, UserID ) VALUES ( :projectid, :userid )",
			array ('userid' => $userid, 'projectid' => $projectid ) );
	}
	
	public static function removeMembershipById($projectid, $userid)
	{
		executeSQL( "DELETE FROM ProjectMembership WHERE ProjectID = :projectid AND UserID = :userid",
			array( 'projectid' => $projectid, 'userid' => $userid ) );
	}
	
	public static function checkMembershipExistsById( $projectid, $userid )
	{
		$sth = executeSQL( "SELECT count(*) FROM ProjectMembership WHERE ProjectID = :projectid AND UserID = :userid",
			array( 'projectid' => $projectid, 'userid' => $userid) );
		return $sth->fetchColumn(0) > 0;
	}
	
	public static function addEntireGroupById($ProjectID, $GroupID)
	{
		$users = AdminGroup::listUsersById($GroupID);
		foreach ($users as $user)
		{
			if (!(Project::checkMembershipExistsById($ProjectID, $user->UserID)))
				Project::addMembershipById($ProjectID, $user->UserID);
		}
	}
	
	public static function listMembershipById( $projectid )
	{
		$sth = executeSQL( "SELECT Username, PasswordHash, FullName, State, CreateDate, LastUpdate, UserID FROM Users WHERE UserID IN (SELECT UserID FROM ProjectMembership WHERE ProjectID = :projectid) ORDER BY Username",
			array( 'projectid' => $projectid ) );
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

}

class ProjectCat
{
	public $CatID;
	public $CatName;
	public $SortOrder;
	
	public static function create( $CatName, $SortOrder = NULL )
	{
		if ($SortOrder == NULL)
		{
			$sth = executeSQL( "SELECT max(SortOrder) FROM ProjectCodeCategory",
				array (  ) );
			$SortOrder = $sth->fetchColumn(0) + 1;
		}
		executeSQL( "INSERT INTO ProjectCodeCategory ( CatName, SortOrder ) VALUES ( :catname, :sortorder )",
			array ( 'catname' => $CatName, 'sortorder' => $SortOrder ) );
	}
	
	public static function retrieve( $CatID )
	{
		$sth = executeSQL( "SELECT CatID, CatName, SortOrder FROM ProjectCodeCategory WHERE CatID = :catid",
			array( 'catid'=>$CatID ) );
		
		$cat = new ProjectCat();
		$sth->setFetchMode( PDO::FETCH_INTO, $cat );
		if ( !$sth->fetch() )
		{
			return null;
		}
		return $cat;
	}
	
	public static function retrieveByName( $CatName )
	{
		$sth = executeSQL( "SELECT CatID, CatName, SortOrder FROM ProjectCodeCategory WHERE CatName = :catname",
			array( 'catname'=>$CatName ) );
		
		$cat = new ProjectCat();
		$sth->setFetchMode( PDO::FETCH_INTO, $cat );
		if ( !$sth->fetch() )
		{
			return null;
		}
		return $cat;
	}
	
	public static function updateCatName($CatID, $NewName)
	{
		$sth = executeSQL("UPDATE ProjectCodeCategory SET CatName = :catname WHERE CatID = :CatID",
			array ( 'CatID' => $CatID, 'catname' => $NewName ) );
	}
	
	public static function updateCat($ProjectID, $CatID)
	{
		$SortOrder = self::getProjCodeCountForCat($CatID);
		$sth = executeSQL("UPDATE ProjectCodes SET CatID=:catid WHERE ProjectID = :projid",
			array ( 'catid' => $CatID, 'projid' => $ProjectID ) );
		self::cleanUpSortOrder();
	}
	
	public static function getSortedProjectList()
	{
		$sth = executeSQL( "SELECT CatID, CatName, SortOrder FROM ProjectCodeCategory ORDER BY SortOrder",
			array (  ) );
		$cats = $sth->FetchALL( PDO::FETCH_CLASS, 'ProjectCat');
		$results = array();
		foreach( $cats as $cat)
		{
			$codes = array();
			$sth = executeSQL( "SELECT ProjectID, CatID, ProjectCode, State, Type FROM ProjectCodes WHERE CatID = :cat AND State = 'active' ORDER BY ProjectCode",
				array ( 'cat' => $cat->CatID ) );
			$codes = $sth->FetchALL( PDO::FETCH_CLASS, 'Project');
			if (count($codes) > 0)
			{
				$results[] = $cat;
				$results = array_merge($results, $codes);
			}
		}
		return $results;
	}
	
	public static function getCatOfProject($ProjectID)
	{
		$sth = executeSQL( "SELECT CatID FROM ProjectCodes WHERE ProjectID = :projectid",
			array( 'projectid'=>$ProjectID ) );
		return $sth->fetchColumn(0);
	}
	
	public static function getCatCount()
	{
		$sth = executeSQL( "SELECT Count(*) FROM ProjectCodeCategory",
			array(  ) );
		return $sth->fetchColumn(0);
	}
	
	public static function getProjCodeCountForCat( $CatID )
	{
		$sth = executeSQL( "SELECT Count(*) FROM ProjectCodes WHERE CatID = :catid",
			array( 'catid'=>$CatID ) );
		return $sth->fetchColumn(0);
	}
	
	public static function cleanUpSortOrder()
	{
		$sth = executeSQL( "SELECT CatID, CatName, SortOrder FROM ProjectCodeCategory ORDER BY SortOrder",
			array (  ) );
		$cats = $sth->FetchALL( PDO::FETCH_CLASS, 'ProjectCat');
		$i = 0;
		foreach( $cats as $cat)
		{
			$sth = executeSQL("UPDATE ProjectCodeCategory SET SortOrder = :sortorder WHERE CatID = :catid",
				array ( 'sortorder' => $i, 'catid' => $cat->CatID ) );
			$i++;
		}
	}
	
	public static function getProjectsUnderCat( $CatID )
	{
		$sth = executeSQL( "SELECT ProjectID, CatID, ProjectCode, State, Type, SortOrder FROM ProjectCodes WHERE CatID = :cat ORDER BY SortOrder",
			array ( 'cat' => $cat->CatID ) );
		return $sth->FetchALL( PDO::FETCH_CLASS, 'Project');
	}
	
	public static function delete( $CatID )
	{
		executeSQL( "DELETE FROM ProjectCodeCategory WHERE CatID = :catid",
			array( 'catid'=>$CatID ) );
	}
	
	public static function setSortOrder( $CatID, $Position )
	{
		executeSQL( "UPDATE ProjectCodeCategory SET SortOrder = :pos WHERE CatID = :catid",
			array ('catid' => $CatID, 'pos' => $Position ) );
	}
	
	public static function getSortOrder( $CatID )
	{
		$sth = executeSQL( "SELECT SortOrder FROM ProjectCodeCategory WHERE CatID = :catid",
			array( 'catid'=>$CatID ) );
		return $sth->fetchColumn(0);
	}
	
	public static function listCats()
	{
		$sth = executeSQL( "SELECT CatID, CatName, SortOrder FROM ProjectCodeCategory",
			array (  ) );
		$cats = null;
		while ( true )
		{
			$cat = new ProjectCat();
			$sth->setFetchMode( PDO::FETCH_INTO, $cat );
			if ( !$sth->fetch() )
			{
				break;
			}
			$cats[] = $cat;
		}
		return $cats;
	}
	
}

class ProjectCatCollapsed
{
	public $ProjectId;
	public $ProjectIds;
	public $CatId;
	
	public static function buildBaseProjectList()
	{
		$projBlob = ProjectCat::getSortedProjectList();
		$projects = array();
		foreach($projBlob as $obj)
		{
			if (get_class($obj) == "Project")
			{
				$thisObj = new ProjectCatCollapsed();
				$thisObj->ProjectId = $obj->ProjectID;
				$thisObj->CatId = $obj->CatID;
				$thisObj->ProjectIds = array();
				$thisObj->ProjectIds[] = $obj->ProjectID;
				
				$pos = strpos($obj->ProjectCode, "#");
				if($pos !== false)
				{
					$truncated = substr($obj->ProjectCode, 0, $pos);
					if(!array_key_exists($truncated, $projects))
						$projects[$truncated] = $thisObj;
					else
						$projects[$truncated]->ProjectIds[] = $obj->ProjectID;
				}
				else
					$projects[$obj->ProjectCode] = $thisObj;
			}
		}

		return $projects;	
	}

	public static function collapseProjectHours($userProjs, $baseProjs, $isarray = false)
	{
		$projects = array();
		foreach ($userProjs as $arrIndex => $proj)
		{
			if (!$isarray)
			{
				$thisProjId = $proj->ProjectID;
				$thisNumBlocks = $proj->numBlocks;
			}
			else
			{
				$thisProjId = $arrIndex;
				$thisNumBlocks = $proj;
			}
			$projectId = -1;
			foreach ($baseProjs as $projectCode => $baseProjObj)
			{
				if (in_array($thisProjId, $baseProjObj->ProjectIds))
				{
					$projectId = $baseProjObj->ProjectId;
					break;
				}
			}

			$foundit = false;
			if (!$isarray)
			{
				$index = 0;
				foreach ($projects as $userProj)
				{
					if ($userProj->ProjectID == $projectId)
					{
						if ($proj->state != "finalized" && $projects[$index]->state == "finalized")
							$projects[$index]->state = $proj->state;
						$projects[$index]->numBlocks += $thisNumBlocks;
						$foundit = true;
						break;
					}
					$index++;
				}
			}
			else
			{
				foreach ($projects as $retProjId => $retProjNumBlocks)
				{
					if ($retProjId == $projectId)
					{
						$projects[$projectId] += $thisNumBlocks;
						$foundit = true;
						break;
					}
				}
			}

			if (!$foundit)
			{
				if (!$isarray)
				{
					$proj->ProjectID = $projectId;
					$projects[] = $proj;
				}
				else
					$projects[$projectId] = $thisNumBlocks;
			}
		}
		return $projects;
	}
}

?>