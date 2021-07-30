<?php		
	require_once( dirname( __FILE__ )."/./docroot/authenticate.php" );
	require_once( dirname( __FILE__ )."/projTrackDB.php" );
	//outside Variables include:
	//projectList which should include:
	//	ProjectID
	//	HEALTH
	//	PROJECT_NAME
	//  END_DATE
	//  IS_ACTIVE
	//count
	//entryDate 
	
	$userId = $_SESSION["USERID"];
	$projectId = $projectInfo->PROJECT_ID;
	$projectIsActive = $projectInfo->IS_ACTIVE;
	$projectHealth = $projectInfo->HEALTH;
	$projectName = $projectInfo->PROJECT_NAME;
	$projectEndDate = substr($projectInfo->END_DATE,0,strpos($projectInfo->END_DATE,' '));
	
	$queryEntryDate = $entryDate; 
	if(PTHasProjectEntriesForUserAndDate( $userId, $projectId, $entryDate, false) == 0)
	{
		echo "<script> console.log(\"HIT PREVIOUS WEEK\"); </script>";
		PTCreateDefaultProjectEntryForUserDate( $userId, $projectId, $entryDate, 'NONE', false ); 
	}

	$entryData = PTGetProjectEntryByUserAndProjectAndDate( $userId, $projectId, $entryDate, false );
	if ( $entryData->PREVIOUS_GOALS == "" )
		$entryData->PREVIOUS_GOALS = "No Previous Goals";
	
	$effortIsReadOnly = "";
	$isReadOnly = PTAreProjectEntriesLockedForUserAndDate($userId, $entryDate);
	if ($isReadOnly || $projectInfo->IS_ACTIVE == false)
	{
		$isReadOnly = "readonly";
		$effortIsReadOnly = "disabled";
	}
	
	if($projectHealth == "GREEN")
		$dropClass = "dropbtnGreen";
	elseif($projectHealth == "YELLOW")
		$dropClass = "dropbtnYellow";
	elseif($projectHealth == "RED")
		$dropClass = "dropbtnRed";
	else
		$dropClass = "dropbtnNull";

	echo "<tr id=\"rowCount". $count. "\" projID=\"". $projectId. "\" projName=\"". $projectName. "\">";
	if(!$entryData->ASSIGNED && $projectId != MISC_PROJECT_ID && $isReadOnly != "readonly")
		$trashHeader = "<th class=\"project-delete\"><span class=\"project-trash-no-delete\" id = \"trashIcon\" onclick=\"removeProj(". $count. ")\"><i class=\"fas fa-trash-alt\"></i></span></th>";
	else
		$trashHeader = "<th></th>";

	echo "<th class=\"project-arrow\"><i href=\"#\" class=\"fa fa-caret-square-right\" data-prod-cat=\"". $count. "\"></i></th>";
	if ( $projectId != MISC_PROJECT_ID )
	{
		echo "<th class=\"project-color\"><button id=\"dropbtn". $count. "\" class=\"". $dropClass. "\"></button></th>";
	}
	else
		echo "<th></th>";
	echo "<th class=\"project-title\"> ". $projectName. "</th>";
	if ( $projectId != MISC_PROJECT_ID )
	{
		echo "<th class=\"project-status\"><div id=\"projectStatus\"><span class=\"project-warning\"><i id=\"projectWarning". $count. "\" class=\"fas fa-exclamation-triangle\"></i></span><span class=\"project-star\"><i id=\"projectStar". $count. "\" class=\"fas fa-star\" style=\"display:none;\"></i></span></div></th>";
		echo "<th class=\"project-end\">" . $projectEndDate. "</th>";
		echo $trashHeader;
		// echo "<th class=\"project-reorder\">";
		// if ( $count < $totalCount - 1 )
			// echo "<a href=\"#\"><i class=\"fas fa-arrow-alt-circle-down\"></i></a>";
		// if ( $count != 1 )
			// echo "<a href=\"#\"><i class=\"fas fa-arrow-alt-circle-up\"></i></a>";
		
		// echo "</th>";
		echo "<th></th>";
	}
	else
		echo "<th/><th/><th/><th/>";
	echo "</tr>";
	echo "<tr class=\"cat". $count. "\" style=\"display:none\">";
	echo "<td colspan=\"7\">";
	echo "<table>";
	echo "<tr>";
	echo "<td class=\"project-headers\">Effort</td>";
	echo "<td class=\"project-headers\">Previous Goals</td>"; 
	echo "<td class=\"project-headers\">Accomplishments</td>";
	echo "<td class=\"project-headers\">Issues</td>";
	echo "<td class=\"project-headers\">Upcoming Goals</td>";
	echo "</tr>";
	echo "<tr>";
	// echo "<td class=\"effort-percentage-td\"><h4 class=\"effort-percent-sign\"><textarea class=\"effort\" maxlength=\"3\" ".$isReadOnly." id=\"EffortResponse". $count. "\" oninput=\"changeStatus(". $count. "); saveText(this);\" onmouseout=roundEffort(this.id); projid=\"". $projectId. "\" userID=\"". $userId. "\">". $entryData->EFFORT. "</textarea>&nbsp;&#37;</h4></td>";
	// echo "<td class=\"effort-percentage-td\"><h4 class=\"effort-percent-sign\"><input type=\"number\" class=\"effort\" min=\"0\" max=\"100\" step=\"5\" ".$isReadOnly." id=\"EffortResponse". $count. "\" oninput=\"changeStatus(". $count. "); saveText(this);\" projid=\"". $projectId. "\" userID=\"". $userId. "\" value = ". $entryData->EFFORT. ">&nbsp;&#37;</h4></td>";
	echo "<td class=\"effort-percentage-td\"><h4 class=\"effort-percent-sign\">";
	echo "<select class=\"effort\" ".$effortIsReadOnly." id=\"EffortResponse". $count. "\" oninput=\"changeStatus(". $count. "); saveText(this);\" projid=\"". $projectId. "\" userID=\"". $userId. "\">";
	for($i = 100; $i >= 0; $i-= 5)
	{
		if($i == $entryData->EFFORT)
		{
			echo "<option selected=\"selected\">". $i ."</option>";
		}
		else
			echo "<option>". $i ."</option>";
	}
	echo "</select>&nbsp;&#37</h4></td>";
	echo "<td class=\"project-textarea-td\"><textarea class=\"previous-goals\" readonly id=\"PreviousGoalsResponse". $count. "\">". $entryData->PREVIOUS_GOALS."</textarea></td>"; 
	echo "<td class=\"project-textarea-td\"><textarea maxlength=\"242\" ".$isReadOnly." id=\"AccomplishmentsResponse". $count. "\" oninput=\"changeStatus(". $count. "); saveText(this);\" projid=\"". $projectId. "\" userID=\"". $userId. "\">". $entryData->ACCOMPLISHMENTS. "</textarea></td>";
	echo "<td class=\"project-textarea-td\"><textarea maxlength=\"242\" ".$isReadOnly." id=\"IssuesResponse". $count. "\" oninput=\"changeStatus(". $count. "); saveText(this);\" projid=\"". $projectId. "\" userID=\"". $userId. "\">". $entryData->ISSUES. "</textarea></td>";
	echo "<td class=\"project-textarea-td\"><textarea maxlength=\"242\" ".$isReadOnly." id=\"UpcomingGoalsResponse". $count. "\" oninput=\"changeStatus(". $count. "); saveText(this);\" projid=\"". $projectId. "\" userID=\"". $userId. "\">". $entryData->UPCOMING_GOALS. "</textarea></td>";
	echo "</tr>";
	echo "</table>";
	echo "</td>";
	echo "</tr>";
?>