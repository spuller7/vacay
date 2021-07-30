function generateSecondaryPreview()
{
    var secondarySubjects;
    var secondaryCommenters;
    var reason;
     
    secondaryCommenters = $('#primary-subject-content').text();
    secondarySubjects = $('#primary-commenter-content').text();
    reason = $('#primary-reason').text();

    var html = `<div id="secondary-request">
                    <h4>SUBJECT(S)</h4>
                    <p id="secondary-subject-content"><!-- Generated content will go here--> ${secondarySubjects}</p>
                    <h4>REASON</h4>
                    <p id="secondary-reason"><!-- Generated content will go here-->${reason}</p>
                    <h4>COMMENTER(S)</h4>
                    <p id="secondary-commenter-content"><!-- Generated content will go here--> ${secondaryCommenters}</p>
                </div>`;

    document.getElementById('secondary-request').innerHTML += html;
}

function switchSelectedListener()
{
    $('#switchBtn').click(function()
    {
        if($('#switchBtn').hasClass('disposition-active'))
        {
            document.getElementById('secondary-request').innerHTML = '';
            $('#switchBtn').removeClass('disposition-active');
        }
        else
        {
            generateSecondaryPreview();
            $('#switchBtn').addClass('disposition-active');
        }
    });
}

function previewCheckboxes(prefix)
{
		var prefixCB = prefix+ '-checkboxes';
		var result = "";
		for(let entry of data.employee_names)
		{
			var user = getEmployeeInfoByName(entry);
			var cbid = "#"+prefixCB+"-"+user.uid;
			if ($(cbid).prop("checked")) 
			{
				if ( result != "" ) result += "; ";
				result += "&lt;"+entry.replace(" ", "&nbsp;") + "&gt;";
			}			
		}
		$("#primary-" + prefix + "-content").html(result);	
}

function setupPreview() 
{
    $('#subject-checkboxes').change(function(event) 
    {
		previewCheckboxes("subject");
    });		
    
    $('#commenter-checkboxes').change(function(event) 
    {
		previewCheckboxes("commenter");
    });

    $('#reason-text-area').keyup(function()
    {
		var solicitationReason = formatForPrint($("#reason-text-area").val());
		
        document.getElementById('primary-reason').innerHTML = solicitationReason;
        if ($( "#secondary-reason" ).length)
        {
            document.getElementById('secondary-reason').innerHTML = solicitationReason;
        }
    });
}


function filterSelectBox( prefix )
{
	var substr = $('#'+prefix + "-text").val().toUpperCase(); 
	var prefixCB = prefix + '-checkboxes';
	for(let entry of data.employee_names)
	{
		var user = getEmployeeInfoByName(entry);
		var cbid = "#"+prefixCB+"-"+user.uid;
		var id = "#"+prefixCB+"-"+user.uid+"-div";
		var label_id = "#"+prefixCB+"-"+user.uid+"-label";

		if ( substr == '' || entry.toUpperCase().includes(substr) )
		{
			$(id).removeClass('name-hide');
			$(label_id).addClass('name-bold');
		}
		else
		{
			$(id).addClass('name-hide');
			$(label_id).removeClass('name-bold');
		}
		if ($(cbid).prop("checked")) 
		{
			$(id).removeClass('name-hide');
		}			
	}	
}

function searchSelectBox()
{
    $('#commenter-text').keyup(function()
    {
		filterSelectBox("commenter");
    });
    
    $('#subject-text').keyup(function()
    {
		filterSelectBox("subject");		
    });
}

function generateSolicitContent()
{
	var elem = document.getElementById('top');
	elem.classList.remove('home');
	
	$("#header-tabs").html(`<div id="header"><h1>Solicit Feedback</h1></div>`);
	$("#content").html(`
			<div id="solicit-flex">
				<div class="solicit-form">
					<div class="form-wrapper">
						<div class="form-fields">
							<h3>Reason</h3>
							<textarea id="reason-text-area" rows="5" cols="75" placeholder="Reason..." maxlength="32000"></textarea>
						</div>
					</div>
					<div class="form-wrapper">
						<div class="form-fields">
							<h4>Commenter</h4>
							<input type="text" id="commenter-text" placeholder="Name...|" size="25" class='settings-text-input'><br />
							<div class="settings-form-input">
								<div id="commenter-checkboxes">
								</div>
							</div>
						</div>
						<div class="form-fields">&nbsp;</div>
						<!-- div class="form-fields">
							<button class="button-round" id="switchBtn" style="margin-top:100px;" title="Switch commenters and subjects"><i class="fas fa-exchange-alt"></i></button>
						</div -->
						<div class="form-fields">
							<h4>Subject</h4>
							<input type="text" id="subject-text" placeholder="Name...|" size="25" class='settings-text-input'><br />
							<div class="settings-form-input" id="rightSB">
								<div id="subject-checkboxes">
								</div>
							</div>
						</div>
					</div>
				</div><!-- solicit-form -->
				<div class="solicit-preview">
					<div id="primary-request">
						<h3>Preview</h3>
						<h4>REASON</h4>
						<p id="primary-reason" class='primary-reason'><!-- Generated content will go here--></p>
						<h4>COMMENTER(S)</h4>
						<p id="primary-commenter-content"><!-- Generated content will go here--></p>
						<h4>SUBJECT(S)</h4>
						<p id="primary-subject-content"><!-- Generated content will go here--></p>
					</div>
					<hr>
					<div id="secondary-request">
						<!--Generated content will go here-->
					</div>
				</div><!-- solicit-preview -->
			</div><!-- solicit-flex -->
		`);

		$("#footerUn").html(`
		<!-- footer -->
		<div class="footer">
			<div id="unsolicited">
				<button id="submit" onclick='DoSolicit(); return false'>Send Request</button>
			</div>
		</div>`);
}

function getCheckedList(prefix)
{
	var prefixCB = prefix+ '-checkboxes';
	var result = [];
	for(let entry of data.employee_names)
	{
		var user = getEmployeeInfoByName(entry);
		var cbid = "#"+prefixCB+"-"+user.uid;
		if ($(cbid).prop("checked")) 
		{
			result.push(user.uid);
		}			
	}
	return result;
}

function DoSolicit()
{
	var commenters = getCheckedList("commenter");
	var subjects = getCheckedList("subject");
	var reason = $("#reason-text-area").val();
	
	if ( commenters.length == 0 )
	{
		alert("Commenters must not be empty");
		return;
	}
	
	if ( subjects.length == 0 )
	{
		alert("Subjects must not be empty");
		return;
	}
	
	if ( reason.trim() == "" )
	{
		alert("Reason cannot be empty");
		return;
	}

	createSolicitedFeedbackBatch( reason, commenters, subjects, showFeedbackSolicited );
}

function showSolicitFeedback()
{
	setPage("SOLICIT");
	
    generatePage();
	generateSolicitContent();
    switchSelectedListener();
    document.getElementById('commenter-checkboxes').innerHTML += generateSelectBoxContent('commenter-checkboxes'); 
    document.getElementById('subject-checkboxes').innerHTML += generateSelectBoxContent('subject-checkboxes'); 

    setupPreview();
    searchSelectBox();
}