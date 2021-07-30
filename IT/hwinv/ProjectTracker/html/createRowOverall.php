<?php 
	$innerCount = 0;

	$userId = GOD_USER_ID;
	$projectId = $projectInfo->PROJECT_ID;
	$projectIsActive = $projectInfo->IS_ACTIVE;
	$projectHealth = $projectInfo->HEALTH;
	$projectName = $projectInfo->PROJECT_NAME;
	$projectEndDate = substr($projectInfo->END_DATE,0,strpos($projectInfo->END_DATE,' '));
	$managerId = PTGetManagerForProject($projectId);

	if(PTHasProjectEntriesForUserAndDate( $userId, $projectId, $entryDate, true) == 0)
	{
		if (PTCreateDefaultProjectEntryForUserDate( $userId, $projectId, $entryDate, 'NONE', true ));
	}
	
	if($projectHealth == "GREEN")
		$dropClass = "dropbtnGreen";
	elseif($projectHealth == "YELLOW")
		$dropClass = "dropbtnYellow";
	elseif($projectHealth == "RED")
		$dropClass = "dropbtnRed";
	else
		$dropClass = "dropbtnNull";
	
	echo "<tr projid=\"". $projectId."\" id=\"row". $rowCount. "\">";
	echo "<th class=\"project-arrow\"><i href=\"#\" class=\"fa fa-caret-square-right\" data-prod-cat=\"". $rowCount. "\"></i></th>";
	if ($projectId != MISC_PROJECT_ID )
		echo "<th class=\"project-color\"><div class=\"dropdown\"><button onchange=\"saveColor(this)\" id=\"dropbtn". $rowCount. "\" class=\"". $dropClass. "\"></button><div class=\"dropdown-content\"><a onclick=\"document.getElementById('dropbtn". $rowCount. "').className = 'dropbtnGreen'; saveColor(this);\"><span class=\"status-green\"><i class=\"fas fa-circle\"></i></span></a><a onclick=\"document.getElementById('dropbtn". $rowCount. "').className = 'dropbtnYellow'; saveColor(this);\"><span class=\"status-yellow\"><i class=\"fas fa-circle\"></i></span></a><a onclick=\"document.getElementById('dropbtn". $rowCount. "').className = 'dropbtnRed'; saveColor(this);\"><span class=\"status-red\"><i class=\"fas fa-circle\"></i></span></a></div></div></th>";
	else 
		echo "<th></th>";
	echo "<th class=\"project-title\"> ". $projectName. "</th>";
	
	if ( $projectId != MISC_PROJECT_ID )
	{
		echo "<th><div id=\"projectStatus\"><span class=\"project-warning\"><i id=\"projectWarning". $projectId . "\" class=\"fas fa-exclamation-triangle\"></i></span><span class=\"project-star\"><i id=\"projectStar". $projectId."\" class=\"fas fa-star\" style=\"display:none;\"></i></span></div></th>";
		if(hasRole(Roles::ADMINISTRATOR))
		{ 
			echo "<th><i class=\"fas fa-edit editIcon\" projid=\"". $projectId . "\"></i></th>";
			$managerName = PTGetNamebyUserID($managerId);
			echo "<th id=\"project-managerName". $projectId."\">". $managerName ."</th>";
		} 
		else 
		{
			echo "<th></th><th></th>";
		}				
		echo "<th class=\"project-end\">" . $projectEndDate . "</th>";
		echo "<th></th>";
		//Following line is to add the project reordering buttons back in in place of the empty th in the line above
		//<th class=\"project-reorder\"><a href=\"#\"><i class=\"fas fa-arrow-alt-circle-down\"></i></a><a href=\"#\"><i class=\"fas fa-arrow-alt-circle-up\"></i></a></th>";
	}
	else
		echo "<th/><th/><th/><th/><th/>";
	echo "</tr>";
	echo "<tr class=\"cat". $rowCount. "\" style=\"display:none\">";

	echo "<td colspan=\"8\">";
	echo "<table id=\"overallUpdates\">";
	echo "<tr>";
	//echo "<td rowspan=2>Overall Status</td>";

	$entryData = PTGetProjectEntryByUserAndProjectAndDate( $userId, $projectId, $entryDate, true );
	if ( $entryData->PREVIOUS_GOALS == "" )
		$entryData->PREVIOUS_GOALS = "No Previous Goals";
	
	$isReadOnly = "";
	if ($projectInfo->IS_ACTIVE == false)
		$isReadOnly = "readonly";
	
	echo "<td class=\"project-headers-overall\">OVERALL</td>";
	echo "<td class=\"project-headers\">Previous Goals</td>"; 
	echo "<td class=\"project-headers\">Accomplishments</td>";
	echo "<td class=\"project-headers\">Issues</td>";
	echo "<td class=\"project-headers\">Upcoming Goals</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td></td>";
	echo "<td><textarea class=\"previous-goals\" readonly id=\"PreviousGoalsResponse". $rowCount. "\">". $entryData->PREVIOUS_GOALS."</textarea></td>";
	echo "<td><textarea maxlength=\"242\" ".$isReadOnly." id=\"AccomplishmentsResponse". $rowCount. "\" oninput=\"saveText(this);\" projID=\"".  $projectId. "\" userID= \"". $userId. "\">". $entryData->ACCOMPLISHMENTS. "</textarea></td>"; 
	echo "<td><textarea maxlength=\"242\" ".$isReadOnly." id=\"IssuesResponse". $rowCount. "\" oninput=\"saveText(this);\" projID=\"".  $projectId. "\" userID= \"". $userId. "\">". $entryData->ISSUES. "</textarea></td>"; 
	echo "<td><textarea maxlength=\"242\" ".$isReadOnly." id=\"UpcomingGoalsResponse". $rowCount. "\" oninput=\"saveText(this);\" projID=\"".  $projectId. "\" userID= \"". $userId. "\">". $entryData->UPCOMING_GOALS. "</textarea></td>";
	echo "</tr>";
	echo "</table>";
	echo "</td>";
	echo "</tr>";

	$innerCount = 1;

	$projectManager = PTGetManagerForProject($projectId);
	
	if ( $projectId != MISC_PROJECT_ID )
	{
		$userData = PTGetAssignedUsersForProject($projectId); 
		
		$tempUserData = PTGetUsersWithEntriesbyProjectandDate($projectId, $entryDate);

		for($i = 0; $i < sizeof($tempUserData); $i++)
		{
			$userId = $tempUserData[$i]->UserID;
			$field = 'UserID';
			if ( !searchForId( $userId, $userData, $field))
			{
				array_push($userData, $tempUserData[$i]);
			}
		}
	}
	else
		$userData = PTGetActiveUsersData();
	$previousGoals = "";
	$accomplishments = "";
	$issues = "";
	$upcomingGoals = "";

	foreach($userData as $user)
	{
		$userID = $user->UserID;
		if(PTHasProjectEntriesForUserAndDate($userID, $projectId, $entryDate, false))
		{
			$entryData = PTGetProjectEntryByUserAndProjectAndDate( $userID, $projectId, $entryDate, false );
			
			$previousGoals = $entryData->PREVIOUS_GOALS;
			$accomplishments = $entryData->ACCOMPLISHMENTS;
			$issues = $entryData->ISSUES;
			$upcomingGoals = $entryData->UPCOMING_GOALS;
		}
		else
		{
			$previousGoals = "No Previous Goals";
			$accomplishments = "";
			$issues = "";
			$upcomingGoals = "";
		}
		
		if ( $previousGoals == "" ) 
			$previousGoals = "No Previous Goals";
		
		$isReadOnly = ($entryData->ENTRY_STATE == "COMPLETE" || $projectIsActive == 0);

		echo "<tr class=\"cat". $rowCount. "\" style=\"display:none\">";
		echo "<td colspan=\"8\">";
		echo "<table class=\"overallProjectUpdates\" id = \"userUpdates". $innerCount. "\">";
		echo "<tr>";
		if($user->UserID == $projectManager)
			echo "<td class=\"overall-name\" rowspan=2><strong>LEAD<br>". $user->Name. "</strong></td>";
		else
			echo "<td class=\"overall-name\" rowspan=2><strong>". $user->Name . "</strong></td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td class=\"overall-entries\" id=\"PreviousGoalsResponse". $innerCount. " ". $rowCount. "\">". $previousGoals. "</td>"; 
		echo "<td class=\"overall-entries\" id=\"AccomplishmentsResponse". $innerCount. " ". $rowCount. "\">". $accomplishments. "</td>"; 
		echo "<td class=\"overall-entries\" id=\"IssuesResponse". $innerCount. " ". $rowCount. "\">". $issues. "</td>"; 
		echo "<td class=\"overall-entries\" id=\"UpcomingGoalsResponse". $innerCount. " ". $rowCount. "\">". $upcomingGoals. "</td>"; 
		echo "</tr>";
		echo "</table>";
		echo "</td>";
		echo "</tr>";
		
		$innerCount++;
	}
	
?>