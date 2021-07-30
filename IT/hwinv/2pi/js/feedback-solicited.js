function generateFeedbackSolicitedDataRows(feedback)
{
  let html = ``;
  var feedbackClass;

  for( let entry of feedback)
  {

    if(entry.COMMENT != null) 
    {
      feedbackClass = "fb-status-hold";
    }
    else
    {
      feedbackClass = "fb-status-none"; 
    }
	var subject = getEmployeeInfo(entry.SUBJECT_ID);
	var commenter = getEmployeeInfo(entry.COMMENTER_ID);
	var solicitor = getEmployeeInfo(entry.SOLICITOR_ID);

	var solicitationReason = formatForPrint(entry.SOLICITATION_REASON);

    html +=
            `<div class="fb-wrapper" id="fbrow_${entry.FEEDBACK_ID}">
            <div class=${feedbackClass}>
            <p><i class="far fa-envelope"></i></p>
            </div>
            <div class="fb-input-wrapper">
                <div class="fb-text collapsible">
                <p><span class="review-name">${solicitationReason}</span></p>
                <p><b>${solicitor.name}</b> requested <b>${commenter.name}</b> to provide feedback about <b>${subject.name}</b> ${calculateEntryDate(entry.SOLICITATION_DATE)}`
				
			if ( entry.FEEDBACK_DATE != null )
			{
				html += `, the response was entered ${calculateEntryDate(entry.FEEDBACK_DATE)}`;
			}
				
			html += `</p></div></div>`;
            if(entry.COMMENT == null)
            {
                html +=
                `<div class="fb-action trash-icon">
                    <button class="button-round" onclick='OnClickDeleteSolicitedFeedback(${entry.FEEDBACK_ID}); return false;'><i class="fas fa-trash-alt"></i></button>
                </div>`;
            }
        html +=
            `</div>`;
            
  }
  return html;
};

function OnClickDeleteSolicitedFeedback( id )
{
	if ( confirm("Are you sure you want to delete this feedback?" ) )
	{
		removeSolicitedOpenFeedback(id, function() 
		{ 
			$("#fbrow_"+id).hide(200, function(){
					this.remove(); 
			});
		} );
	}
}

function showFeedbackSolicited()
{
    generatePage();
	updateSubnavigation("SOLICITED");

	var elem = document.getElementById('top');
	elem.classList.remove('home');

	$("#content").html( `<div id="fb-instructions">
					<p>Use this page to track feedback solicitations. If an employee has not entered feedback, you may delete the request. Once an employee enters feedback, the request cannot be deleted. Feedback that has been through the ${userData.submit_cooldown} weekday waiting period can be found on the <a href="#" onclick='showReports(); return false'>Reports</a> page.</p>
				</div>
				<div id="content-padding"></div>`);    
	
	
	getOpenSolicitedFeedback( function(res) {
		document.getElementById('content-padding').innerHTML = generateFeedbackSolicitedDataRows(res.result);
    });
}

