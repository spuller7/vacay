function generateFeedbackDoDataRows(feedback)
{
    let html = ``;
    var dispositionclass;
    for( let entry of feedback)
    {
		dispositionclass = "id-" + entry.FEEDBACK_ID;
		var subject = getEmployeeInfo(entry.SUBJECT_ID);
		var solicitor = getEmployeeInfo(entry.SOLICITOR_ID);
		
		var solicitationReason = formatForPrint(entry.SOLICITATION_REASON);
		var feedbackComment = formatForPrint(entry.COMMENT);
		
		html +=
				`<div class="fb-wrapper" id="fbrow_${entry.FEEDBACK_ID}">
				<div class="fb-status-none">
				<p><i class="far fa-envelope"></i></p>
				</div>
				<div class="fb-input-wrapper">
					<div class="fb-text">
						<form>
						<p><span class="review-name">${solicitationReason}</span></p>
						<p><b>${solicitor.name}</b> requested feedback on <b>${subject.name}</b> ${calculateEntryDate(entry.SOLICITATION_DATE)}</p>
					</div>
					<div class="fb-input-wrapper">	
						<div class="fb-input">
								<form>
								<textarea rows="4" cols="75" placeholder="Enter content..." id = "fbcomment_${entry.FEEDBACK_ID}" maxlength="32000">`;
								if(entry.COMMENT != null)
								 html += `${feedbackComment}`;
								html += `</textarea>
						</div>
						<div class="fb-input" id="${entry.FEEDBACK_ID}">`;
					html += `<button id='fb_POSITIVE_${entry.FEEDBACK_ID}' onclick="OnSetDispositionTodo(${entry.FEEDBACK_ID}, 'POSITIVE'); return false;" class="${entry.DISPOSITION == "POSITIVE"?"disposition-active ":""}button-round solicited-disposition"><span title='Good'><i class="fas fa-smile"></i></button>`;
					html += `<button id='fb_NEUTRAL_${entry.FEEDBACK_ID}' onclick="OnSetDispositionTodo(${entry.FEEDBACK_ID}, 'NEUTRAL'); return false;" class="${entry.DISPOSITION == "NEUTRAL"?"disposition-active ":""}button-round solicited-disposition"><span title='Neutral'><i class="fas fa-meh"></i><span></button>`;
					html += `<button id='fb_NEGATIVE_${entry.FEEDBACK_ID}' onclick="OnSetDispositionTodo(${entry.FEEDBACK_ID}, 'NEGATIVE'); return false;" class="${entry.DISPOSITION == "NEGATIVE"?"disposition-active ":""}button-round solicited-disposition"><span title='Poor'><i class="fas fa-frown"></i><span></button>`;
					html +=`</form>
						</div>
					</div>
						</form>
				</div>
			</div>`;
    }
    return html;
};

function OnSetDispositionTodo( id, dispo )
{
	var comment = $("#fbcomment_"+id).val();
	if( comment.trim() == "" )
	{
		alert("You cannot submit feedback without a comment");
		$("#fbcomment_"+id).focus();
	}
	else
	{
		updateTodoFeedback( comment, dispo, id, function()
		{
			$("#fbrow_"+id).effect("puff", {}, 500, function(){
					this.remove(); 
					updateNotifications();
			});
		} );
	}
}

function showFeedbackTodo()
{
    generatePage();
	updateSubnavigation("TODO");
	var elem = document.getElementById('top');
	elem.classList.remove('home');
	
	$("#content").html( `<div id="fb-instructions">
					<p>Please provide feedback for each of these requests. Once you have provided feedback, you have ${userData.submit_cooldown} days to make edits on the "<a href="#" onclick='showFeedbackSubmitted(); return false;'>Submitted</a>" page.</p>
				</div>
				<div id="content-padding">
				</div>`);
				
	getTodoFeedback( function(res) {
		document.getElementById('content-padding').innerHTML = generateFeedbackDoDataRows(res.result); 
    });
}

