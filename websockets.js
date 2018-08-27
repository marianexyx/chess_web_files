var bSiteIsProcessingCoreMsg = false;
var bForceStopWS = false;
var coreMsgsArr = []; 

var wsUri = "ws://89.72.9.69:1234"; 
var websocket = null;

function initWebSocket() 
{
	if ("WebSocket" in window) 
	{
		if (websocket == null) 
			websocket = new WebSocket(wsUri); 
		
		websocket.onerror = function (evt) 
		{
			coreMsgsArr = [];
			bSiteIsProcessingCoreMsg = false;
			serverStatus("offline");
		};
		
		websocket.onclose = function (evt) 
		{
			serverStatus("offline");
			websocket = null;
			coreMsgsArr = []; 
			bSiteIsProcessingCoreMsg = false;
			if (!bForceStopWS)
				setTimeout(function() { initWebSocket(); }, 1000)
		};
		
		websocket.onmessage = function (evt) 
		{ 
			if (bForceStopWS)
			{
				stopWebSocket();
				return;
			}
			
			coreMsgsArr.push(evt.data);
			if (bSiteIsProcessingCoreMsg == false)
			{
				console.log("websocket.onmessage = " + evt.data + ", bSiteIsProcessingCoreMsg = false");
				doAjaxCall();
			}
			else console.log("websocket.onmessage = " + evt.data + ", bSiteIsProcessingCoreMsg = true");
		};
		
		websocket.onopen = function (evt) 
		{ 
			console.log("websocket.onopen");
			coreMsgsArr = []; 
			bSiteIsProcessingCoreMsg = false;
		
			var stateStr;
			switch (websocket.readyState) 
			{
				case 0: { stateStr = "CONNECTING"; serverStatus("connecting"); break; }
				case 1: { stateStr = "OPEN"; serverStatus("online"); break; }
				case 2: { stateStr = "CLOSING";	serverStatus("offline"); break; }
				case 3: { stateStr = "CLOSED"; serverStatus("offline"); break; }
				default: { stateStr = "UNKNOW"; serverStatus("offline"); break; }
			}
			
			if (bForceStopWS)
			{
				stopWebSocket();
				return;
			}
		}
	} else alert("WebSockets not supported on your browser.");
}

function stopWebSocket() 
{ 
	bForceStopWS = true;
	if (websocket) 
		websocket.close(); 
}

setInterval(function() { websocket.send("keepConnected"); }, 250000);