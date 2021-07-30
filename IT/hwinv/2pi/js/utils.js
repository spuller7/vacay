var ADMINISTRATOR = "ADMIN";
var SOLICITOR = "SOLICITOR";
var REVIEWER = "REVIEWER";
ROLES = [];
var usersGroup = [];
var data = {};
var reviewersList = [];
var adminsList = [];
var solicitorList = [];
var peopleopsList = [];

function getEmployeeInfo( id )
{
	var d = data.employee_lookup[id];
	if (d == null )
		d = { "name": "Unknown", "givenname": "Unknown" };
	
	return d;
}

function getEmployeeInfoByName( nm )
{
	var d = data.employee_lookupByName[nm];
	if (d == null )
		d = { "uid": 0, "givenname": "Unknown", "collateName": "Unknown" };
	
	return d;
}

function getEmployeeInfoByUserName( unm )
{
	var d = data.employee_lookupByUserName[unm];
	if (d == null )
		d = { "uid": 0, "givenname": "Unknown", "collateName": "Unknown" };
	
	return d;
}

function formatForPrintNoLineEndings(srcText)
{
	var esc = $('<div>');	
	return esc.text(srcText).html();	
}

function formatForPrint(srcText)
{
	return formatForPrintNoLineEndings(srcText).replace(/\n/g, "<br />");
}
var lastId = null;

function toggleSubmit( id )
{
    var content = $("#fbtog_"+id);
	var icon = $("#fbico_"+id);
	if ( lastId != null && lastId != id )
	{
		var lastIcon = $("#fbico_"+lastId);
		var lastContent = $("#fbtog_"+lastId);
		lastIcon.css("transform", "rotate(-0deg)");
		lastIcon.css("transition-duration","0.2s");
		lastIcon.css("transition-property","transform");
		lastContent.hide(200);
	}
	if( $("#fbtog_"+id).css("display") == "none" )
	{
		icon.css("transform", "rotate(90deg)");
		icon.css("transition-duration","0.2s");
		icon.css("transition-property","transform");
		content.show(200);
	}
	else
	{
		icon.css("transform", "rotate(00deg)");
		icon.css("transition-duration","0.2s");
		icon.css("transition-property","transform");
		content.hide(200);
	}
	lastId = id;	
}

function calculateFinalDate( dt, durationDays )
{
	var entryDate = new Date(dt); 
	entryDate.setMinutes(0); entryDate.setHours(0); entryDate.setSeconds(0); entryDate.setMilliseconds(0);
	if( durationDays < 0 )
	{
		while(durationDays < 0)
		{ 
			entryDate.setDate(entryDate.getDate() - 1);
			if(entryDate.getDay() !== 0 && entryDate.getDay() !== 6 ) 
				durationDays++;

		}	
	}
	else
	{
		while(durationDays > 0)
		{ 
			entryDate.setDate(entryDate.getDate() + 1);
			if(entryDate.getDay() !== 0 && entryDate.getDay() !== 6 ) 
				durationDays--;

		}	
	}
	return entryDate;
}

function calculateEntryDate(dt)
{
	var entryDate = new Date(dt); 
	entryDate.setMinutes(0); entryDate.setHours(0); entryDate.setSeconds(0); entryDate.setMilliseconds(0);
	var currentDate = new Date();
	currentDate.setMinutes(0); currentDate.setHours(0); currentDate.setSeconds(0); currentDate.setMilliseconds(0);
	
	var diff = (currentDate.getTime() - entryDate.getTime())/(24*60*60*1000);
	
	if ( diff == 0 )
		return "today";
	
	if ( diff == 1 )
		return "yesterday";
	
	return Math.floor(diff) + " days ago";
}

function dispositionIcons(disposition)
{
 var html; 

 switch(disposition)
 {
   case 'POSITIVE': html = `<p class="disp-good" id="POSITIVE"><span><i class="fas fa-smile" title="POSITIVE"></i></span></p>`; break;
   case 'NEUTRAL': html =  `<p class="disp-neutral id="NEUTRAL"><span><i class="fas fa-meh" title="NETURAL"></i></span></p>`; break;
   case 'NEGATIVE': html = `<p class="disp-poor" id="NEGATIVE"><span><i class="fas fa-frown" title="NEGATIVE"></i></span></p>`; break;
 }

 return html;
} 

function dispositionListener() //For unsolicited feedback (aka the footer)
{
  $('.unsolicited-disposition').click(function(event) 
	{
		var tgt = $(event.target).closest("button").attr('id');
		event.preventDefault(); //prevent default action 
		var selectedUser = $("#fname").val();
		var uid = -1;
		for ( var i in usersGroup )
		{
			var ug = usersGroup[i];
			if ( ug.collateName == selectedUser )
			{
				uid = ug.uid;
				break;
			}
		}
		if ( uid == -1 )
		{
			alert("Unknown user!");
			return;
		}
		if ( $("#myInput").val() == "" )
		{
			alert("Feedback cannot be empty");
			return;
		}

		var input = $("#myInput").val();
		$("#myInput").val("");
		$("#fname").val("");
		$("#fname").focus();
		createUnsolicitedFeedback( input, tgt, uid, function(res) { showFeedbackSubmitted();} );
 	});
}

function generateFooter()
{
  var html;

  html = `<div class='footer'>
            <div id='unsolicited'>
              <div class='un-form-wrapper'>
              <form>
                <div class='form-fields autocomplete'>
                  <i class='far fa-paper-plane un-icon'></i>
                  <input type='text' size='20' id='fname' name='fname' placeholder='Name...' autocomplete="off" onfocus='focusFunction()' onblur='blurFunction()'>
                </div>
                <div class='form-fields'>
                  <textarea name='' rows='4' cols='75' placeholder='Submit Unsolicited Feedback...' id='myInput' onfocus='focusFunction()' onblur='blurFunction()'  maxlength="32000"></textarea>
                </div>
                <div class='form-fields'>
                  <button class='button-round unsolicited-disposition' onfocus='focusFunction()'  onblur='blurFunction()' id='POSITIVE'>
                    <span title='Good'><i class='fas fa-smile'></i></span>
                  </button>
                  <button class='button-round unsolicited-disposition' onfocus='focusFunction()'  onblur='blurFunction()'  id='NEUTRAL'>
                    <span title='Neutral'><i class='fas fa-meh'></i></span>
                  </button>
                  <button class='button-round unsolicited-disposition' onfocus='focusFunction()'  onblur='blurFunction()'  id='NEGATIVE'>
                    <span title='Poor'><i class='fas fa-frown'></i></span>
                  </button>
                </div>
              </form>
              </div>
            </div>
          </div>`;

  $("#footerUn").html(html);
  
  dispositionListener(); //Set up listener for disposition
}

function focusFunction() {
  var element = document.getElementById("unsolicited");
  element.classList.remove('un-blur');
}

function blurFunction() {
  if ( $("#myInput").val() != "" )
  {
	var element = document.getElementById("unsolicited");
	element.classList.add("un-blur");
  }
}


function generateSelectBoxContent(name)
{
	var html = '';
	for(let entry of data.employee_names)
	{
		var user = getEmployeeInfoByName(entry);
        html += `<div id="${name}-${user.uid}-div"><label for="${name}-${user.uid}" id="${name}-${user.uid}-label"><input type="checkbox" name="${name}_${user.uid}" id="${name}-${user.uid}" value= ${user.uid}>${user.collateName}</label></div>`;
    }
    return html;
};


function setPage( pg )
{
	localStorage.setItem("qsai2pi.lastPage", pg );
}

function setTab( tab )
{
	localStorage.setItem("qsai2pi.lastTab", tab );
}

function getPage()
{
	return localStorage.getItem("qsai2pi.lastPage");
}

function getTab()
{
	return localStorage.getItem("qsai2pi.lastTab");
}


function showFeedback()
{
	var tab = getTab();
	if ( tab == 'FB_SUBMITTED' )
		showFeedbackSubmitted();
	else if ( tab == 'FB_REVIEW' )
		showFeedbackReviewer();
	else if ( tab == 'FB_SOLICITED' )
		showFeedbackSolicited();
	else
		showFeedbackTodo();
}

var selectedTab = "HOME";

function AddTab( tabName, tabFunc, tabText, tabRole, hasIcon )
{
	clazz = 'tab-item-placeholder';
	clazz = (selectedTab == tabName) ? "tab-item-active" : "tab-item";
	content = "<h3>" + tabText;
	if ( hasIcon )
		content += "<i class='fas fa-bell fb-notification' id='NOTIFY_" + tabName + "'></i>";
	content += "</h3>";
	return "<div style='width:25%' class='" + clazz + "' id='SUBNAV_" + tabName + "'  onclick='" + tabFunc + "(); return false;'>" + content + "</div>";
}

function generateSubNavigation()
{
	var html = "";
	
	html += AddTab( "TODO", "showFeedbackTodo", "TO DO", "", true );
	html += AddTab( "SUBMITTED", "showFeedbackSubmitted", "SUBMITTED", "", false );
	if(ROLES.includes(REVIEWER))
	{
		html += AddTab( "REVIEW", "showFeedbackReviewer", "TO REVIEW", REVIEWER, true );
	}
	if(ROLES.includes(SOLICITOR))
	{
		html += AddTab( "SOLICITED", "showFeedbackSolicited", "SOLICITED", SOLICITOR, false );
	}
	
	if ( !ROLES.includes(REVIEWER) )
	{
		html += "<div style='width:25%' class=''></div>";
	}
	
	if ( !ROLES.includes(SOLICITOR) )
	{
		html += "<div style='width:25%' class=''></div>";
	}

	$("#header-tabs").html(html);
}

function updateSubnavigation( newTab )
{
	setPage("FEEDBACK");
	setTab("FB_" + newTab);

	$("#SUBNAV_" + selectedTab).removeClass("tab-item-active");
	$("#SUBNAV_" + selectedTab).addClass("tab-item");
	
	selectedTab = newTab;

	$("#SUBNAV_" + selectedTab).removeClass("tab-item");
	$("#SUBNAV_" + selectedTab).addClass("tab-item-active");
	updateNotifications();
}

var fromDate;
var toDate;

function downloadReport( asUser )
{
	window.location = "reports.php?ISUSER="+asUser+"&MIN_DATE="+fromDate+"&MAX_DATE="+toDate;
}

function generateReport(res, asUser)
{
	var content = "";
	if ( res.result.length != 0 )
	{
		content += "<input type='button' onclick='downloadReport("+asUser+");' value='Download'/><br/><br/>";	
	}
	content += "<table>";
	content += `<tr>`;
	content += `<th>Date</th>`;
	content += `<th>Commenter</th>`;
	content += `<th>Subject</th>`;
	content += `<th>Disposition</th>`;
	content += `<th>Comment</th>`;
	content += `<th>Solicitor</th>`;
	content += `<th>Solication<br/>Date</th>`;
	content += `<th>Solicitation<br/>Reason</th>`;
	content += `<th>Closed Date</th>`;
	content += `<th>ID</th>`;
	if(ROLES.includes(REVIEWER) && !asUser)
	{
		content += `<th>Reviewer</th>`;
		content += `<th>Review<br/>Status</th>`;
	}	
	content += `</tr>`;
	for ( let entry of res.result )
	{
		var subject = getEmployeeInfo(entry.SUBJECT_ID);
		var solicitor = (entry.SOLICITOR_ID != null ? getEmployeeInfo(entry.SOLICITOR_ID).name : "");
		var commenter = getEmployeeInfo(entry.COMMENTER_ID);
		
		var solicitationReason = formatForPrint(entry.SOLICITATION_REASON);
		var feedbackComment = formatForPrint(entry.COMMENT);
		
		var fbDate = (entry.FEEDBACK_DATE != null ? entry.FEEDBACK_DATE.substring(0,10) : "" );
		var solDate = (entry.SOLICITATION_DATE != null ? entry.SOLICITATION_DATE.substring(0,10) : "" );
		var closedDate = (entry.CLOSED_DATE != null ? entry.CLOSED_DATE.substring(0,10) : "" );
		var disposition = (entry.DISPOSITION != null ? entry.DISPOSITION : "???" );
		
		content += `<tr>`;
		content += `<td class='reportDate'>${fbDate}</td>`;
		content += `<td class='reportUser'>${commenter.name}</td>`;
		content += `<td class='reportUser'>${subject.name}</td>`;
		content += `<td>${disposition}</td>`;
		content += `<td>${feedbackComment}</td>`;
		content += `<td class='reportUser'>${solicitor}</td>`;
		content += `<td class='reportDate'>${solDate}</td>`;
		content += `<td>${solicitationReason}</td>`;
		content += `<td class='reportDate'>${closedDate}</td>`;
		content += `<td>${entry.FEEDBACK_ID}</td>`;
		if(ROLES.includes(REVIEWER) && !asUser)
		{
			var reviewer = (entry.REVIEWER_ID != null ? getEmployeeInfo(entry.REVIEWER_ID).name : "");
			content += `<td class='reportUser'>${reviewer}</td>`;
			content += `<td>${entry.STATUS}</td>`;
		}
		content += `</tr>`;
	}
	if ( res.result.length == 0 )
	{
		content += `<tr><td colspan='`;
		if(ROLES.includes(REVIEWER) && !asUser)
		{
			content += "12";
		}
		else
		{
			content += "10";
		}
		content += "'>No Results found within specified date range</td></tr>";
	}
	content += "</table>";
	$("#reportResult").html(content);

}

function reportFeedback(isUser)
{
	var fromCtl = document.querySelector('#from');
	var fromI = fromCtl.valueAsDate;
	var from = new Date( fromI.getUTCFullYear(), fromI.getUTCMonth(), fromI.getUTCDate() );
	fromDate = fromCtl.value;
	
	var toCtl = document.querySelector('#to');
	var toI = toCtl.valueAsDate;
	var to = new Date( toI.getUTCFullYear(), toI.getUTCMonth(), toI.getUTCDate() );
	toDate = toCtl.value;
	
	if (isUser)
	{
		reportUserFeedback( fromCtl.value, toCtl.value, 
			function(res)
			{
				generateReport(res, true);
			}
			);
	}
	else
	{
		reportReviewerFeedback( fromCtl.value, toCtl.value, 
			function(res)
			{
				generateReport(res, false);
			}
			);
	}
}


function showReports()
{
	setPage("REPORTS");
	
	generatePage();
	$("#content").html("");
	getOpenFeedback( function (res) {
		var html = `<div class="settings-wrapper">
					<div class="settings-section settings-section-title">
						<h4>Reporting</h4>
					</div>
					<div class="settings-section">
						<label for="from">From:</label>
						<input type="date" id="from" name="from" value="2000-01-01">

						<label for="to">To:</label>
						<input type="date" id="to" name="to" value="2099-01-01">	
					</div>
					<div class="settings-section">
					<input type='button' onclick='reportFeedback(true);' value='Feedback Report'/>
					`;

		if(ROLES.includes(REVIEWER))
		{
			html += `<input type='button' onclick='reportFeedback(false);' value='Reviewer Report'/>`;
		}
		html += `</div></div>`;
		html += `<div id='reportResult' class='reportResult'></div>`;
		$("#content").html(html);
		
		var dt = new Date();
		dt.setHours(12);
		dt.setMinutes(0);
		dt.setSeconds(0);
		var lst = new Date();
		lst.setFullYear( lst.getFullYear() -1 );
		$("#from").val(lst.getFullYear()+"-"+((lst.getMonth()+1)+"").padStart(2,"0")+"-"+(lst.getDate()+"").padStart(2,"0"));
		$("#to").val(dt.getFullYear()+"-"+((dt.getMonth()+1)+"").padStart(2,"0")+"-"+(dt.getDate()+"").padStart(2,"0"));	
	});
	$("#header-tabs").html(`<div id="header"><h1>Reports</h1></div>`);
	$("#footerUn").html(`<div class="footer"><div id="unsolicited"></div></div>`);
}

function setReviewers()
{
	previewCheckboxes("employees");
	var checks = getCheckedList("employees");
	setReviewerAssignment( $("#ReviewerSelect").val(), checks, function(res) {} );	
}

function updateReviewers()
{
	var reviewerId = $("#ReviewerSelect").val()|0;
	getReviewerAssignment( reviewerId,

		function (res )
		{
			var checked = res.result;
			for(let entry of data.employee_names)
			{
				var user = getEmployeeInfoByName(entry);
				var cbid = "#employees-checkboxes-"+user.uid;
				var id =   "#employees-checkboxes-"+user.uid+"-div";
				$(cbid).prop("checked", checked.includes( user.uid )  );
			}
			filterSelectBox("employees");
			$("#employees-checkboxes-"+reviewerId+"-div").addClass('name-hide');
			previewCheckboxes("employees");
		});
}


$.fn.sortSelect = function() {
    var op = this.children("option");
    op.sort(function(a, b) {
        return a.text > b.text ? 1 : -1;
    })
    return this.empty().append(op);
}

function showSettings()
{
	setPage("SETTINGS");
	generatePage();
	var html = `<div class="settings-wrapper">
				<div class="settings-flex">
				<div class="settings-section settings-section-title">
					<h4>Manage Reviewers</h4>
				</div>
				<div class="settings-section">
					<h5>Reviewers</h5>
					<select id='ReviewerSelect'>`
					
	for ( var idx in reviewersList )
	{
		var nm = reviewersList[idx];
		var user = getEmployeeInfoByUserName(nm);
		html += `<option value='${user.uid}'>${user.collateName}</option>`;
	}
	html += `</select>`;
	
	
	html += `</div>
				<div class="settings-section">
					<h5>Employees</h5>
					<input type="text" id="employees-text" placeholder="Name...|" size="25" class='settings-text-input'><br />
					<div class="settings-form-input">

					<div id="employees-checkboxes">
					</div>					
					</div>
				</div>
				<div class="settings-section" style='width:100%'>
					<h5>Users</h5>
					<p id='primary-employees-content'>
					</p>
				</div>
				</div>
			</div>`;
			
	$("#content").html(html);
	$("#header-tabs").html(`<div id="header"><h1>Settings</h1></div>`);
	$("#footerUn").html(`<div class="footer"><div id="unsolicited"></div></div>`);
	
	$("#ReviewerSelect").change(function() { updateReviewers(); } );
	document.getElementById('employees-checkboxes').innerHTML += generateSelectBoxContent('employees-checkboxes'); 
	
    $('#employees-text').keyup(function()
    {
		filterSelectBox("employees");
    });
	
    $('#employees-checkboxes').change(function(event) 
    {
		setReviewers();
    });	
    			
	updateReviewers();
	$("#ReviewerSelect").sortSelect();
}

function generateNavigation()
{
  var html;
  html = `<div class='sticky-top'>
				<div id='navigation'>
					<div id='branding'>
					</div>
				<div class='VersionStr'>${versionStr}</div>
					<ul>
					<li><a href='#' onclick='showFeedbackTodo(); return false;'><i class='far fa-envelope-open'></i><br />FEEDBACK</a></li>
          <ul>`;
          if(ROLES.includes(SOLICITOR))
          {
            html += `<li>
                  <a href='#' onclick='showSolicitFeedback(); return false;'>
                  <i class='fas fa-inbox'></i>
                  <br />SOLICIT<br />FEEDBACK
                  </a>
                  </li>`;
          }
	html += `<li>
					<a href='#' onclick='showReports(); return false;'>
					<i class='far fa-clipboard'></i>
					<br />REPORTS
					</a>
          </li>`;

          if(ROLES.includes(ADMINISTRATOR))
          {
            html += `<li>
                    <a href='#' onclick='showSettings(); return false;'>
                    <i class='fas fa-wrench'></i>
                    <br />SETTINGS
                    </a>
                    </li>`;
          }
	html +=	`</ul>
				</div>
				<div id='log-out'>
					<h4>Welcome, ${userData.givenName}</h4>
					<button class="button-round" title='Log Out' alt='Log Out' onclick='logout(); return false;'><i class='fas fa-sign-out-alt'></i></button>
				</div>
        </div>`;

  $("#nav-wrapper").html(html);
}

function generatePage()
{
  generateNavigation();
  generateSubNavigation();
  generateFooter();
	$( "#fname" ).autocomplete({
	  source: data.employee_names, delay: 0
	});  
}

function updateNotifications() //TODO: Add notification functionality
{
	notificationCounts(
		function(res) 
		{ 
			if ( res.result.todo > 0 )
			{
				$('#NOTIFY_TODO').addClass('fb-notification-active');
			}
			else
			{
				$('#NOTIFY_TODO').removeClass('fb-notification-active'); 
			}
			
			if ( res.result.reviews > 0 )
			{
				$('#NOTIFY_REVIEW').addClass('fb-notification-active'); 
			}
			else
			{
				$('#NOTIFY_REVIEW').removeClass('fb-notification-active'); 
			}
			
		}
	);
}
