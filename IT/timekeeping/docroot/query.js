function nullHandler()
{
}

function createRequest() 
{
	req = false;
    // branch for native XMLHttpRequest object
    if(window.XMLHttpRequest) {
    	try {
			req = new XMLHttpRequest();
        } catch(e) {
			req = false;
        }
    // branch for IE/Windows ActiveX version
    } else if(window.ActiveXObject) {
       	try {
        	req = new ActiveXObject("Msxml2.XMLHTTP");
      	} catch(e) {
        	try {
          		req = new ActiveXObject("Microsoft.XMLHTTP");
        	} catch(e) {
          		req = false;
        	}
		}
    }
    return req;
}

var req = createRequest();

function doPostData( url, postData )
{
	resetTimeout();
	req.abort();
	req.onreadystatechange=nullHandler;
	req.open('POST', url, false );
	req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	req.setRequestHeader("Content-length", postData.length);
	//req.setRequestHeader("Connection", "close");
	req.send(postData);	
	return req.responseText;
}

function doPostDataMap( url, map )
{
	var postData = "";
	for(var key in map)
	{
		if ( postData.length != 0 )
		{
			postData += "&";
		}
		postData += key + "=" + encodeURIComponent( map[key] );
	}
	return doPostData( url, postData );
}

function doGetData( url )
{
	resetTimeout();
	req.abort();
	req.onreadystatechange=nullHandler;
	req.open('GET', url, false );
 	req.send(null);
 	return req.responseText;
}

function doGetMap( url, map )
{
	var param = "";
	for(var key in map)
	{
		if ( param.length == 0 )
		{
			param += "?";
		}
		else
		{
			param += "&";
		}
		param += key + "=" + encodeURIComponent( map[key] );
	}
	return doGetMap( url + param );
}

function resetTimeout()
{
	parent.timeoutReset();
}

