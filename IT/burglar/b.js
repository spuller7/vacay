const xmpp = require('node-xmpp');
const sys = require('sys');
const fs = require("fs");
const exec = require('child_process');

function sendMessage( msgBody, to )
{
	const jid = "qsitnotify@gmail.com";
	const password = "HancockLovesTuna";
	
	const conn = new xmpp.Client({
	    jid         : jid,
	    password    : password,
	    host        : 'talk.google.com',
	    port        : 5222
	});
	
	conn.on('online', function(){
	    //console.log('online');
	
	    conn.send(new xmpp.Element('presence'));
	    conn.send(new xmpp.Element('message',
	        { to: to, // to
	            type: 'chat'}).
	            c('body').
	            t(msgBody));
			conn.end();         
	});
	
	conn.on('error', function(e) {
	    sys.puts(e);
	    conn.end();
	});
}

var sendTo = [ "roblupa@gmail.com","cmshowers@gmail.com" ];
var emailTo = [ "itrobot@mail.internal.quantumsignal.com" ];

function sendEmail( msgBody, to )
{
	var mailCmd = "echo \"" + msgBody + "\" | mail -s \"Burglar Alarm state changed: " + msgBody + "\" " + to;
	exec.exec(mailCmd);
}

function sendMessages( msgBody, email )
{
	for ( var x in sendTo )
	{
		sendMessage( msgBody, sendTo[x] );
	}
	if (email !== false)
	{
		for ( var x in emailTo )
		{
			sendEmail( msgBody, emailTo[x] );
		}
	}
}

var stateFile = "amps.json"

function getState()
{
        try
        {
                var data = fs.readFileSync( stateFile );
                if ( data !== undefined )
                {
                        return JSON.parse(data);
                }
                else
                {
                        return false;
                }
        }
        catch ( err )
        {
                sendMessages( "No burglar state", false);
                return false;
        }
}

function setState(f)
{
        fs.writeFileSync( stateFile, JSON.stringify(f) );
}

function onAlarm()
{
	sendMessages("BURGLAR ALARM IS GOING OFF");
}

function offAlarm()
{
	sendMessages("burglar alarm is not going off");
}

exec.exec("../inst-nounits", function(err, out, code) {
	var amps = Math.abs(out);
	var lastAmps = getState();
	if (lastAmps === false) //no previous state existed so always send message
	{
		if (amps > 0.05)
			onAlarm();
		else
			offAlarm();
	}
	else
	{
		if (lastAmps < 0.05 && amps > 0.1)
			onAlarm();
		else if (lastAmps > 0.1 && amps < 0.05)
			offAlarm();
	}
	setState(amps);
});
