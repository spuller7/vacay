function filterID()
{ 
    var keyCode= window.event.keyCode; 
    
    if (
    	(keyCode==null) || 
    	(keyCode==0) || 
    	(keyCode==8) || 
    	(keyCode==9) || 
    	(keyCode==13) || 
    	(keyCode==27) )
    {
    	return true; 
    }
    
    var ch = String.fromCharCode(keyCode); 
    return ( ch >= 'a' && ch <='z' ) ||
    	 ( ch >= 'A' && ch <='Z' ) ||
    	 ( ch >= '0' && ch <='9' ) ||
    	 ( ch == '_' );
}	

function stripIDText(ctrl)
{
	ctlValue = ctrl.value;
	newValue = "";
	for(i = 0; i < ctlValue.length; i ++ )
	{
		var ch = ctlValue.charAt(i);
		if ( ( ch >= 'a' && ch <='z' ) ||
    	 ( ch >= 'A' && ch <='Z' ) ||
    	 ( ch >= '0' && ch <='9' ) ||
    	 ( ch == '_' )
		     )
		{
			newValue += ch;
		}
	}
	if ( newValue != ctlValue )
	{
		ctrl.value = newValue;
	}
}

function zeroPad(n, digits) {
	n = n.toString();
	while (n.length < digits) {
		n = '0' + n;
	}
	return n;
}

function escapeQuotes(Text)
{
	Text = Text.replace(/'/g, "\\'");
	Text = Text.replace(/"/g, "&quot;");
	return Text;
}

function getGlobalDateForSQL()
{
	var dateObj = new Date(getDateFromFormat(parent.globalDate, "M/d/yyyy"));
	return formatDate(dateObj,"yyyy-MM-dd");
}

function getDateForSQL(date)
{
	var dateObj = new Date(getDateFromFormat(date, "M/d/yyyy"));
	return formatDate(dateObj,"yyyy-MM-dd");
}

function getDateForSQLFromFormat(date, format)
{
	var dateObj = new Date(getDateFromFormat(date, format));
	return formatDate(dateObj,"yyyy-MM-dd");
}

function updateGlobalDate(date, format)
{
	var dateObj = new Date(getDateFromFormat(date, format));
	parent.globalDate = formatDate(dateObj,"MM/dd/yyyy");
}
