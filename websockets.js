var wsUri = "ws://89.72.9.69:1234"; 
var websocket = null;

function initWebSocket() 
{
	if ("WebSocket" in window) 
	{
		if (websocket == null) { websocket = new WebSocket(wsUri);} 
		
		websocket.onerror = function (evt) 
		{
			console.log('Weboscket error:', evt.data);
			serverStatus("offline");
		};
		
		websocket.onclose = function (evt) 
		{
			serverStatus("offline");
			websocket = null;
			setTimeout(function() { initWebSocket(); }, 1000)
		};
		
		websocket.onmessage = function (evt) 
		{ 
			$.ajax(
			{
				url: "on_ws_msg.php",
				type: "POST",			
				dataType: "json",
				data: { wsMsg: evt.data },
				success: function (data) { ajaxResponse(data); },
				error: function(xhr, status, error) 
				{
					var err = eval("(" + xhr.responseText + ")");
					alert(err.Message);
				}
			});
		};
		
		websocket.onopen = function (evt) 
		{ 
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