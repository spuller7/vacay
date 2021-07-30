var pinned = [];

var starIcon = 'fa-star';

function generateFeedbackReviewDataRows(feedback)
{
  let html = ``;
  var statusIcon;
  var statusClass;

  for( let entry of feedback)
  {

	var subject = getEmployeeInfo(entry.SUBJECT_ID);
	var commenter = getEmployeeInfo(entry.COMMENTER_ID);
	var solicitor = getEmployeeInfo(entry.SOLICITOR_ID);

	if(entry.SOLICITATION_REASON != null) //TODO: Make sure that the solicitation reason returns null when solicitation reason is not set
	{
		statusIcon = "far fa-envelope-open";   
		statusClass = "fb-status-review";
	}
	else
	{
		statusIcon = "far fa-paper-plane";  
		statusClass = "fb-status-un";
	}

	pinned[entry.FEEDBACK_ID|0] = (entry.STATUS == 'PINNED');

	var solicitationReason = formatForPrintNoLineEndings(entry.SOLICITATION_REASON != null ? entry.SOLICITATION_REASON.trim() : "");
	var feedbackComment = formatForPrint(entry.COMMENT != null ? entry.COMMENT.trim():"");
	if ( feedbackComment == "" ) feedbackComment = "&nbsp;";
	
    html += `<div class="fb-wrapper" id="fbrow_${entry.FEEDBACK_ID}" onmouseenter='ShowControls(${entry.FEEDBACK_ID});'  onmouseleave='HideControls(${entry.FEEDBACK_ID});' >`;
	html += `<div class="${statusClass}"> <!--   onmouseenter='$("#drop_${entry.FEEDBACK_ID}").css("display", "inline");' onmouseleave='$("#drop_${entry.FEEDBACK_ID}").css("display", "none");'> -->`;
	html += `<i class="${statusIcon}"></i><br/>
				<!-- div class="dropdown">
				  <i id='fbstatus_${entry.FEEDBACK_ID}' class="fas ${starIcon} dropbtn"></i>
				  <div id='drop_${entry.FEEDBACK_ID}' class="dropdown-content">
					<a href="#" onclick='setPin(${entry.FEEDBACK_ID}); return false;'><i class="fas fa-thumbtack"></i></a>
					<a href="#" onclick='setAccept(${entry.FEEDBACK_ID}); return false;'><i class="fas fa-eye"></i></a>
					<a href="#" onclick='setIgnore(${entry.FEEDBACK_ID}); return false;'><i class="fas fa-eye-slash"></i></a>
				  </div>
				</div -->`;
	html += `</div>`; //status
	html += `<div class="fb-input-wrapper-review">`;
		html += `<div class='fb-dispo'>
						<div class="popup-icon-circle2">
							${dispositionIcons(entry.DISPOSITION)}
						</div>
				</div>`;
		html += `<div class='review-icons' id='review_icons_${entry.FEEDBACK_ID}'>
						<div id='check_icon_${entry.FEEDBACK_ID}' style='display: none' class='review-icon ri-check'><a href="#" onclick='setAccept(${entry.FEEDBACK_ID}); return false;'><i class='fas fa-check ri-check'></i></a></div>
						<div id='times_icon_${entry.FEEDBACK_ID}' style='display: none' class='review-icon'><a href="#" onclick='setIgnore(${entry.FEEDBACK_ID}); return false;'><i class='fas fa-times ri-times'></i></a></div>`;
						
						
		if (entry.STATUS != 'PINNED')
		{
			html += `<div id='star_icon_${entry.FEEDBACK_ID}' style='display: none' class='review-icon'><a href="#" onclick='setPin(${entry.FEEDBACK_ID}); return false;'><i class='far ${starIcon} ri-star' id='starspec_${entry.FEEDBACK_ID}'></i></a></div>`;
		}
		else
		{
			html += `<div id='star_icon_${entry.FEEDBACK_ID}' class='review-icon'><a href="#" onclick='setPin(${entry.FEEDBACK_ID}); return false;'><i class='fas ${starIcon} ri-star' id='starspec_${entry.FEEDBACK_ID}'></i></a></div>`;
		}
						
		html += `</div>`; //review-icons
	
		html += `<div class="fb-text collapsible">`;

		var type = "Unsolicited";
		if(entry.SOLICITATION_REASON != null)
		{
			html += `<p><span class="review-name">`;
			html += `${solicitationReason}`;
			html += `</span></p>`;
			type = 'Solicited';
		}
		html += `<p>${type} feedback on <b>${subject.name}</b> submitted by <b>${commenter.name}</b>, ${calculateEntryDate(entry.FEEDBACK_DATE)}
						</p>
						<p>${feedbackComment}</p>`;
		html += `</div>`; //fb-text
	html += `</div>`; //fb-input-wrapper
	html += `</div>`; //fb-wrapper
  }
  return html;
};


function ShowControls(id)
{
	$("#check_icon_" + id).stop();
	$("#times_icon_" + id).stop();
	$("#star_icon_" + id).stop();


	$("#check_icon_" + id).fadeIn(500);
	$("#times_icon_" + id).fadeIn(500);
	if ( !pinned[id] )	
		$("#star_icon_" + id).fadeIn(500);
}

function HideControls(id)
{
	$("#check_icon_" + id).stop();
	$("#times_icon_" + id).stop();
	$("#star_icon_" + id).stop();

	$("#check_icon_" + id).fadeOut(100);
	$("#times_icon_" + id).fadeOut(100);
	if ( !pinned[id] )
		$("#star_icon_" + id).fadeOut(100);
}

function setPin( id )
{
	if ( pinned[id] )
	{
		updateFeedbackStatus(id,'UNREAD', function(){});
		pinned[id] = false;
		$("#star_icon_" + id).css("display","display");
		$("#starspec_"+id).removeClass('fas');
		$("#starspec_"+id).addClass('far');
	}
	else
	{
		updateFeedbackStatus(id,'PINNED', function(){});
		pinned[id] = true;
		$("#star_icon_" + id).css("display","display");
		$("#starspec_"+id).removeClass('far');
		$("#starspec_"+id).addClass('fas');
	}
	updateNotifications();
}

function setAccept( id )
{
	updateFeedbackStatus(id,'READ', 
		function() 
		{ 
			$("#fbrow_"+id).hide(200, function(){
					this.remove(); 
					updateNotifications();
			});
		} 
	);
}

function setIgnore( id )
{
	updateFeedbackStatus(id,'IGNORE', function() { 
			$("#fbrow_"+id).hide(200, function(){
					this.remove(); 
					updateNotifications();
			});
		} );
}

function showFeedbackReviewer()
{
	generatePage();
	
	updateSubnavigation("REVIEW");

	var elem = document.getElementById('top');
	elem.classList.remove('home');

	$("#content").html( `<div id="fb-instructions">
						<p>Please review submitted feedback by moving your mouse over the review and select your disposition by clicking one of:<br>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class='fas fa-check ri-check'></i> marks feedback as acknowledged and moves it to your <a href="#" onclick='showReports(); return false'>Reports</a> page<br/>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class='fas fa-times ri-times'></i> ignores feedback and moves it to your <a href="#" onclick='showReports(); return false'>Reports</a> page<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class='far ${starIcon} ri-star'></i> marks feedback for follow up, it will remain on this page marked with a <i class='fas ${starIcon} ri-star'></i></p>

					</div>
					<div id="content-padding"></div>`);    
					
	getOpenFeedbackForReview( function (res) {
		document.getElementById('content-padding').innerHTML = generateFeedbackReviewDataRows(res.result); 
	});		
}

