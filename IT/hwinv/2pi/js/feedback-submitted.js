function generateFeedbackSubmittedDataRows(feedback)
{
    let html = ``;
    var statusIcon;
    var feedbackClass;

    for( let entry of feedback)
    {
        if(entry.SOLICITATION_REASON != null) 
        {
            feedbackClass = "fb-status-hold";
            statusIcon = "far fa-envelope";   
        }
        else
        {
            feedbackClass = "fb-status-un";
            statusIcon = "far fa-paper-plane";  
        }

		var user = getEmployeeInfo(entry.SUBJECT_ID);
        var finalDate = calculateFinalDate( entry.FEEDBACK_DATE, userData.submit_cooldown );
		var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
		
		var solicitationReason =formatForPrint(entry.SOLICITATION_REASON);
		var feedbackComment = formatForPrintNoLineEndings(entry.COMMENT);

        html +=`<div class="fb-wrapper" id="fbrow_${entry.FEEDBACK_ID}">
<div class="${feedbackClass}"  onclick='toggleSubmit(${entry.FEEDBACK_ID}); return false;'><p><i class= "${statusIcon}"></i><br/><i id='fbico_${entry.FEEDBACK_ID}' class='fas fa-caret-right'></i></p></div>
<div class="fb-input-wrapper">
<div class="fb-text collapsible" id='fbcol_${entry.FEEDBACK_ID}' onclick='toggleSubmit(${entry.FEEDBACK_ID}); return false;'>
<form>
`; 
		if(entry.SOLICITATION_REASON != null)
		{
			html +=`<p><span class="review-name">${solicitationReason}</span></p>`;
		}
		html += `<p>`;
		if ( entry.SOLICITATION_REASON != null )
			html += `Solicited`;
		else
			html += `Unsolicited`;
html +=` feedback about <b>${user.name}</b> entered ${calculateEntryDate(entry.FEEDBACK_DATE)} | Finalizes on ${finalDate.toLocaleDateString("en-US", options)}</p> 
</div>
<div class="active-collapse" id='fbtog_${entry.FEEDBACK_ID}'>	
<div class="fb-input">
<textarea id='fbcomment_${entry.FEEDBACK_ID}' rows="4" cols="75" onblur="OnEditCommentLostFocus(${entry.FEEDBACK_ID}); return false;" maxlength="32000">${feedbackComment}</textarea>
</div>
<div class="fb-input" id="${entry.FEEDBACK_ID}">`;
		html += `<input type='hidden' id='fbprevcomment_${entry.FEEDBACK_ID}' value=''/>`;
		html += `<input type='hidden' id='fbdispo_${entry.FEEDBACK_ID}' value='${entry.DISPOSITION}'/>`;
		html += `<button id='fb_POSITIVE_${entry.FEEDBACK_ID}' onclick="OnSetDisposition(${entry.FEEDBACK_ID}, 'POSITIVE'); return false;" class="${entry.DISPOSITION == "POSITIVE"?"disposition-active ":""}button-round solicited-disposition"><span title='Good'><i class="fas fa-smile"></i></button>`;
		html += `<button id='fb_NEUTRAL_${entry.FEEDBACK_ID}' onclick="OnSetDisposition(${entry.FEEDBACK_ID}, 'NEUTRAL'); return false;" class="${entry.DISPOSITION == "NEUTRAL"?"disposition-active ":""}button-round solicited-disposition"><span title='Neutral'><i class="fas fa-meh"></i><span></button>`;
		html += `<button id='fb_NEGATIVE_${entry.FEEDBACK_ID}' onclick="OnSetDisposition(${entry.FEEDBACK_ID}, 'NEGATIVE'); return false;" class="${entry.DISPOSITION == "NEGATIVE"?"disposition-active ":""}button-round solicited-disposition"><span title='Poor'><i class="fas fa-frown"></i><span></button>`;
		html +=`</div></div></form></div>`;
		if(entry.SOLICITOR_ID == null)
		{
			html +=`<div class="fb-action trash-icon">
		<button class="button-round" onclick='OnClickDeleteUnsolicitedFeedback(${entry.FEEDBACK_ID}); return false;'><i class="fas fa-trash-alt"></i></button>
</div>`;
		}
		html +=`</div>`;
    }
    return html;
};

function OnClickDeleteUnsolicitedFeedback( id )
{
	if ( confirm("Are you sure you want to delete this feedback?" ) )
	{
		removeUnsolicitedOpenFeedback(id, function() { $("#fbrow_"+id).remove(); } );
	}
}

function OnEditCommentLostFocus( id )
{
	updateFeedback( $("#fbcomment_"+id).val(), $("#fbdispo_"+id).val(), id, function(){} );
}

function OnSetDisposition( id, disposition )
{
	$("#fb_"+$("#fbdispo_"+id).val()+"_"+id).removeClass("disposition-active");
	$("#fbdispo_"+id).val(disposition);
	$("#fb_"+$("#fbdispo_"+id).val()+"_"+id).addClass("disposition-active");
	updateFeedback( $("#fbcomment_"+id).val(), disposition, id, function(){} );
}

function showFeedbackSubmitted()
{
    generatePage();

	var html = `<div id="fb-instructions">
	<p>Any feedback you have entered will stay here for ${userData.submit_cooldown} weekdays. Click on the gray bar to make any necessary edits to your feedback. Once the ${userData.submit_cooldown} weekdays are up, your feedback will be submitted and final. You may then find your submitted feedback on the <a href="#" onclick='showReports(); return false'>Reports</a> page. Changes made on this page are saved automatically.</p>
</div>
<div id="content-padding">
</div>
`;

	updateSubnavigation("SUBMITTED");
	
	var elem = document.getElementById('top');
	elem.classList.remove('home');
	
	$("#content").html(html);

	getOpenFeedback( function (res) {
		document.getElementById('content-padding').innerHTML = generateFeedbackSubmittedDataRows(res.result); 
		for( let entry of res.result)
		{
			$("#fbprevcomment_"+entry.FEEDBACK_ID).val($("#fbcomment_" + entry.FEEDBACK_ID).val());
		}
	});
}


