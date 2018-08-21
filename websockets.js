var bSiteIsProcessingCoreMsg = false;
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
			setTimeout(function() { initWebSocket(); }, 1000)
		};
		
		websocket.onmessage = function (evt) 
		{ 
			console.log("websocket.onmessage = " + evt.data);
			coreMsgsArr.push(evt.data);
			if (bSiteIsProcessingCoreMsg == false)
				doAjaxCall();
		};
		
		websocket.onopen = function (evt) 
		{ 
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
			
			sendFirstWsMsg();
		}
	} else alert("WebSockets not supported on your browser.");
}

function stopWebSocket() { if (websocket) websocket.close(); }

setInterval(function() { websocket.send("keepConnected"); }, 250000);