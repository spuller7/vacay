function defaultLoad()
{
	var elem = document.getElementById('top');
	elem.classList.remove('home');
	
	$("#content-wrapper").html(`<div id="header-tabs">
		</div>
		<div id="content" class="home"></div>

		<!-- footer include file: footer-un.js -->
		<div id="footerUn">
		</div>`);
		
	getAuthData( function() {
		getUsers( 
			function(res) 
			{ 
				reviewersList = res.result.reviewers;
				adminsList = res.result.admins;
				peopleopsList = res.result.peopleops;
				solicitorList = res.result.solicitors;
				
				if (reviewersList.includes(userData.username) )
					ROLES.push(REVIEWER);
				if (adminsList.includes(userData.username) )
					ROLES.push(ADMINISTRATOR);
				//if (peopleopsList.includes(userData.username) )
				//	ROLES[] = PEOPLEOPS;
				if (solicitorList.includes(userData.username) )
					ROLES.push(SOLICITOR);
				
				usersGroup = res.result.users; 
				data.employee_names = [];
				data.employee_lookup = {};
				data.employee_lookupByName = {};
				data.employee_lookupByUserName = {};
				for ( x in res.result.users )
				{
					user = res.result.users[x];
					data.employee_names[data.employee_names.length] = user.collateName;
					data.employee_lookup[user.uid] = user;
					data.employee_lookupByName[user.collateName] = user;
					data.employee_lookupByUserName[user.username] = user;
				}
				data.employee_names.sort();
				
				var pg = getPage();
				if ( pg == 'REPORTS' )
					showReports();
				else if ( pg == 'SOLICIT' )
					showSolicitFeedback();
				else if ( pg == 'SETTINGS' )
					showSettings();
				else
					showFeedback();
			} 
			);
		}
	)
}

$(document).ready(function()
{
	$(document).keydown(function(e) {  
		if (e.which == 113 && e.ctrlKey ) 
        {
			var element = document.getElementById("top");
			element.classList.toggle("un-blur2");
		}
	});
    defaultLoad();
});

