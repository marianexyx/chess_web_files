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
			console.log('core msg received: ' + evt.data);
			if (evt.data != 'connectionOnline' && evt.data != 'logout:doubleLogin')
			{
				$.ajax(
				{
					url: "on_ws_msg.php",
					type: "POST",			
					dataType: "json",
					data: { wsMsg: evt.data },
					success: function (data) 
					{
						/*if(typeof data == 'object') data = $.map(data, function(el) { return el; });
						console.log('ajaxResponse msg received: ' + data);*/
						ajaxResponse(data); 
					},
					error: function(xhr, status, error) 
					{
						var err = eval("(" + xhr.responseText + ")");
						alert(err.Message);
					}
				});
			}
			else if (evt.data == 'logout:doubleLogin')
			{
				disableAll();
				stopWebSocket();
				window.location.href = 'index.php?a=logout&b=doubleLogin';
			}
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