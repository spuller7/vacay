
var userData = null;

function authenticate( user, pass )
{
	doPost(
		{
				"Command":"authenticate",
				"username" : user,
				"password" : pass
		}, function( data )
		{
			if ( data.result != false )
			{
				defaultLoad();
			}
			else
			{
				$("#LoginMessage").html(data.error_message);
			}
		}
	);	
}

function getAuthData( cb )
{
	doPost(
		{
				"Command":"getAuthData"
		}, function( data )
		{
			userData = data.result;
			cb();
		}
	);		
}

function logout()
{
	userData = null;
	doPost(
		{
				"Command":"logout"
		}, function( data )
		{
			location.reload();
		}
	);	
}

/*
function forceFinalize( feedbackId, cb )
{
	doPost(
		{
				"Command":"forceFinalize",
				"feedbackId": feedbackId
		}, function( data )
		{
			userData = data.result;
			cb();
		}
	);		
}*/

function RedirectToHandleError(data)
{
	var elem = document.getElementById('top');
	elem.classList.add('home');
	if ( data.error == -5 )
	{
		$("#header-tabs").html(`<div id="header"><h1>QSAI 2Pi Login</h1></div>`);
		$("#content").html(`<div class='loginDiv'>
			<form onsubmit='authenticate($("#username").val(), $("#password").val()); return false;'>
			<label class='loginLabel' for='username'>Username:</label><input class='loginInput' type='text' id='username'/><br/>
			<label class='loginLabel' for='password'>Password:</label><input class='loginInput' type='password' id='password'/><br/>
			<input class='loginSubmit' type='submit'/><span id='LoginMessage'></span>
			</form>
			</div>
		`);
		//$("#top").css( "background-image","url(./images/bg-home.jpg)");
		//$("#top").css("background-size", "cover");
		$("#username").focus();
		//$("#content").css("height", "100%");
		//$("#content").css("overflow", "auto");
		//$("#footerUn").html("");
	}
	else
	{
		$("#header-tabs").html(`<div id="header"><h1>Unexpected Error</h1></div>`);		
		$("#content").html(`<h3>Internal Error</h3><br/>Error Code (${data.error}): ${data.error_message}`);
	}
}

function doPost( payload, cb )
{
        var response = {"error":-1,"error_message": "Command failed"};
        $.ajax({
                type: "POST",
                url: './2PiAPI.php',
                dataType: 'json',
				contentType: 'application/json',
                data: JSON.stringify(payload),
                cache: false,
                async: true,
                success: function(data){
						if ( data.error == 0 )
						{
							cb(data);
						}
						else
						{
							RedirectToHandleError(data);
						}
                },
                error: function(xhr,txt, error) {
                        response.error = -2;
                        response.error_message = "Request failure: " + xhr.responseText + " " + error;
                        response.error_extra = xhr.status;
                        cb(response);
                }
        });
}


/*
function createSolicitedFeedback(solicitationReason, commenterId, subjectId, cb)
{
        doPost(
                {
                        "Command":"createSolicitedFeedback",
                        "solicitationReason" : solicitationReason,
                        "commenterId" : commenterId,
                        "subjectId" : subjectId
                }, cb
        );
}*/

function createSolicitedFeedbackBatch(solicitationReason, commenterIds, subjectIds, cb)
{
        doPost(
                {
                        "Command":"createSolicitedFeedbackBatch",
                        "solicitationReason" : solicitationReason,
                        "commenterIds" : commenterIds,
                        "subjectIds" : subjectIds
                }, cb
        );
}		
		
function createUnsolicitedFeedback(comment, disposition, subjectId, cb)
{
	doPost(
			{
					"Command":"createUnsolicitedFeedback",
					"comment" : comment,
					"disposition" : disposition,
					"subjectId" : subjectId
			}, cb
	);
}		

function getOpenFeedback(cb)
{
        doPost(
                {
                        "Command":"getOpenFeedback"
                }, cb
        );
}	

function getTodoFeedback(cb)
{
        doPost(
                {
                        "Command":"getTodoFeedback"
                }, cb
        );
}	

function getOpenSolicitedFeedback(cb)
{
        doPost(
                {
                        "Command":"getOpenSolicitedFeedback"
                }, cb
        );
}

function getOpenFeedbackForReview(cb)
{
        doPost(
                {
                        "Command":"getOpenFeedbackForReview"
                }, cb
        );
}
	
function getUsers(cb)
{
        doPost(
                {
                        "Command":"getUsers"
                }, cb
        );
}

function setReviewerAssignment(reviewerId, employeeList, cb)
{
        doPost(
                {
                        "Command":"setReviewerAssignment",
                        "reviewerId" : reviewerId,
                        "employeeList" : employeeList
                }, cb
        );
}

function getReviewerAssignment(reviewerId, cb)
{
        doPost(
                {
                        "Command":"getReviewerAssignment",
                        "reviewerId" : reviewerId
                }, cb
        );
}
		
function removeUnsolicitedOpenFeedback(feedbackId, cb)
{
        doPost(
                {
                        "Command":"removeUnsolicitedOpenFeedback",
                        "feedbackId" : feedbackId
                }, cb
        );
}		

function removeSolicitedOpenFeedback(feedbackId, cb)
{
        doPost(
                {
                        "Command":"removeSolicitedOpenFeedback",
                        "feedbackId" : feedbackId
                }, cb
        );
}	

function updateFeedback(comment, disposition, feedbackId, cb)
{
        doPost(
                {
                        "Command":"updateFeedback",
                        "comment" : comment,
                        "disposition" : disposition,
                        "feedbackId" : feedbackId
                }, cb
        );
}		

function updateTodoFeedback(comment, disposition, feedbackId, cb)
{
        doPost(
                {
                        "Command":"updateTodoFeedback",
                        "comment" : comment,
                        "disposition" : disposition,
                        "feedbackId" : feedbackId
                }, cb
        );
}		

function updateFeedbackStatus(feedbackId, status, cb)
{
        doPost(
                {
                        "Command":"updateFeedbackStatus",
                        "feedbackId" : feedbackId,
                        "status" : status
                }, cb
        );
}	

function notificationCounts(cb)
{
        doPost(
                {
                        "Command":"notificationCounts"
                }, cb
        );
}		

function reportReviewerFeedback(dateFrom, dateTo, cb)
{
        doPost(
                {
                        "Command":"reportReviewerFeedback",
                        "dateFrom" : dateFrom,
                        "dateTo" : dateTo
                }, cb
        );
}

function reportUserFeedback(dateFrom, dateTo, cb)
{
        doPost(
                {
                        "Command":"reportUserFeedback",
                        "dateFrom" : dateFrom,
                        "dateTo" : dateTo
                }, cb
        );
}
