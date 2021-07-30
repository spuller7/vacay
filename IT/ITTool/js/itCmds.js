
function doPost( payload )
{
	var response = {"error":-1,"error_message": "Command failed"};
	
	$.ajax({
		type: "POST",
		url: 'ajaxCmd.php',
		dataType: 'json',
		data: payload,
		cache: false,
		async: false,
		success: function(data){
		 	response = data;
		},
		error: function(xhr,txt, error) {
		 	response.error = -2;
		 	response.error_message = "Request failure: " + xhr.responseText;
		 	response.error_extra = xhr.status;
		}
	});
	return response;
}

var notifications = {};

function SetHostNote( host, note )
{
	var result = doPost( { "Command": "SetHostNote", "Host": host, "Note": note } );
	if ( notifications.OnStatusChanged !== undefined )
	{
		notifications.OnStatusChanged( undefined, host, "NOTE", "" );
	}
}	

function GetMsg( msgId )
{
	return doPost( { "Command": "GetMessage", "ID": msgId } );
}	

function AckAlarm( msgId, msg, e )
{
	if(!e){ e = window.event; }
	var sweep = e.ctrlKey;
	var result = doPost( { "Command": "AckMessage", "ID": msgId, "Message": msg, "Sweep": sweep } );
	if ( notifications.OnAckAlarm !== undefined )
	{
		notifications.OnAckAlarm( msgId, msg, sweep );
	}
	if ( notifications.OnMessageUpdated !== undefined )
	{
		notifications.OnMessageUpdated( msgId, result );
	}
	return result;
}

function embedMsg( msgId, loc )
{
	$.ajax({
		type: "GET",
		url: 'viewMsg.php?id='+encodeURIComponent(msgId),
		cache: false,
		async: false,
		success: function(data){
			$("#" + loc).html(data);
		},
		error: function(xhr,txt, error) {
			$("#" + loc).html("ERROR:" + xhr.responseText);
		}
	});
}

function setInfState( msgId, label, state )
{
	var result = doPost( { "Command": "SetInfState", "ID": msgId, "Label": label, "State": state } );
	if ( notifications.OnInfChanged !== undefined )
	{
		notifications.OnInfChanged( msgId, label, state );
	}
	if ( notifications.OnMessageUpdated !== undefined )
	{
		notifications.OnMessageUpdated( msgId, result );
	}
	return result;
}


function setStatusState( msgId, host, key, state )
{
	var result = doPost( { "Command": "SetStatusState", "ID": msgId, "Host": host, "Key": key, "State": state } );
	if ( notifications.OnStatusChanged !== undefined )
	{
		notifications.OnStatusChanged( msgId, host, key, state );
	}
	if ( notifications.OnMessageUpdated !== undefined )
	{
		notifications.OnMessageUpdated( msgId, result );
	}
	return result;
}

function resetStatus( msgId, host, key )
{
	var result = doPost( { "Command": "ResetStatus", "Host": host, "Key": key } );
	if ( notifications.OnStatusChanged !== undefined )
	{
		notifications.OnStatusChanged( msgId, host, key, "" );
	}	
	if ( notifications.OnMessageUpdated !== undefined )
	{
		notifications.OnMessageUpdated( msgId, result );
	}
	return result;
}

function SetMAC( macID, description, state )
{
	var result = doPost( { "Command": "SetMac", "MAC": macID, "Description": description, "State": state } );
	if ( notifications.OnMacChanged !== undefined )
	{
		notifications.OnMacChanged( macID, state, description );
	}	
	return result;
}

function setPortAlias( port, alias )
{
	var result = doPost( { "Command": "SetPortAlias", "Port": port, "Alias": alias } );
	return result;
}

function removeIP( macID, IP  )
{
	var result = doPost( { "Command": "RemoveIP", "IP": IP } );
	if ( notifications.OnMacIPRemoved !== undefined )
	{
		notifications.OnMacIPRemoved( macID, IP );
	}	
	return result;
}

function getPortMapping( port  )
{
	var result = doPost( { "Command": "GetPortMapping", "Port": port } );
	return result;
}

function switchInfo( port )
{
	$.ajax({
		type: "POST",
		url: 'ajaxCmd.php',
		dataType: 'json',
		data: { "Command": "SwitchInfo", "Port": port },
		cache: false,
		async: true,
		success: function(data){
			if ( notifications.OnSwitchInfo !== undefined )
			{
				notifications.OnSwitchInfo( port, data );
			}	
		},
		error: function(xhr,txt, error) {
		 	response.error = -2;
		 	response.error_message = "Request failure: " + xhr.responseText;
		 	response.error_extra = xhr.status;
		}
	});
}

function setAgeLimit( msgId, label, newLimit )
{
	var result = doPost( { "Command": "SetAgeLimit", "Label": label, "Limit": newLimit } );
	if ( notifications.OnStatusChanged !== undefined )
	{
		notifications.OnInfChanged( msgId, label, "" );
	}	
	if ( notifications.OnMessageUpdated !== undefined )
	{
		notifications.OnMessageUpdated( msgId, result );
	}
	return result;
}


function deleteMessage( msgId )
{
	var result = doPost( { "Command": "DeleteMsg", "ID": msgId } );
	if ( notifications.OnMessageDeleted !== undefined )
	{
		notifications.OnMessageDeleted( msgId );
	}
}
	
	
var lastId = undefined;
var lastMsgId = undefined;
	
function showMsgRow( id, rowId )
{
	if ( lastId == rowId )
	{
		embedMsg( id, "MSG_" + rowId + "_DIV" );
	}
	else
	{
		hideMsgRow( lastId );
		var t = $("#ROW_"+rowId);
		if ( t.hasClass("tr_UNREAD") )		
		{
			t.removeClass( "tr_UNREAD" );
			t.addClass( "tr_READ" );
		}
		
		var r = $("#MSG_"+rowId);
		r.removeClass("msgHidden");
		r.addClass("msgVisible");
		embedMsg( id, "MSG_" + rowId + "_DIV" );
	}
	lastId = rowId;
	lastMsgId = id;
}

function hideMsgRow( rowId )
{
	if ( rowId == undefined )
		return;
	
	var t = $("#MSG_"+rowId);
	var d = $("#MSG_"+rowId+"_DIV");
	t.removeClass("msgVisible");
	t.addClass("msgHidden");
	d.html("");
	lastId = undefined;
}

function toggleRow(id, rowId) 
{
	var t = $("#MSG_"+rowId);
	if ( t.hasClass("msgHidden") )
	{
		showMsgRow( id, rowId );
	}
	else
	{
		hideMsgRow( rowId );
	}
}

function multiToggleRow(id, rowId) 
{
	var t = $("#MSG_"+rowId);
	if ( t.hasClass("msgHidden") )
	{
		showMsgRow( id, rowId );
	}
	else
	{
		if ( lastId == rowId && lastMsgId == id )
			hideMsgRow( rowId );
		else
			showMsgRow( id, rowId );
	}
}

