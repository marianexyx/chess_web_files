function debugToGameTextArea(message) 
{
	debugTextArea.value += message + "\n";
	debugTextArea.scrollTop = debugTextArea.scrollHeight;
}

$("#promoteDialog").dialog(
{
	autoOpen: false, 
	dialogClass: "no-close",
	buttons: 
	{
		'&#9819;': function() //hex: &#x265B;	js: \u265B	css: \00265B
		{
			websocket.send("promoteTo: q"); //queen
			console.log('clicked: promoteTo: q');
			$(this).dialog("close");
		}, 
		'&#9821;': function() 
		{
			websocket.send("promoteTo: b"); //bishop
			console.log('clicked: promoteTo: b');
			$(this).dialog("close");
		}, 
		'&#9822;': function() 
		{
			websocket.send("promoteTo: k"); //knight
			console.log('clicked: promoteTo: k');
			$(this).dialog("close");
		}, 
		'&#9820;': function() 
		{
			websocket.send("promoteTo: r"); //rook
			console.log('clicked: promoteTo: r');
			$(this).dialog("close");
		}
	},
	title: "Promuj piona na:",
	position: 
	{
		my: "center",
		at: "center",
		of: window
	}
});

$('#giveUpDialog').dialog({
    autoOpen: false,
	/*modal: true,
		draggable: false,
		resizable: false,
		position: ['center', 'top'],
		show: 'blind',
		hide: 'blind',
	width: 400,*/
	buttons: 
	{
		'tak': function() 
		{
			$.ajax(
			{
				url: "php/giveup.php",
				type: "POST",			
				dataType: "json",
				data: { }, 
				success: function (data) 
				{ 
					if(typeof data == 'object') data = $.map(data, function(el) { return el; });
					console.log('ajax: giveup.php- success: ' + data); 
					ajaxResponse(data);
					$(this).dialog("close");
				},
				error: function(xhr, status, error) 
				{
					var err = eval("(" + xhr.responseText + ")");
					alert(err.Message);
				}
			});
		}, 
		'nie': function() 
		{
			$(this).dialog("close");
		}
	},
	title: 'Czy chcesz opuścić grę?',
	position: 
	{
		my: "center",
		at: "center",
		of: window
	}
});

$('#openGiveUpDialogButton').click(function() 
{
    $('#giveUpDialog').dialog('open');
    return false;
});

function deleteask()
{
	if (confirm("Czy na pewno chcesz się wylogować?")) 
	{
		var request = $.ajax(
		{
			url: "php/logout.php",
			type: "POST",			
			dataType: "json",
			data: { } 
		});
		
		request.done(function() 
		{
			return true;		
		});
		
		request.fail(function() 
		{
			return false;		
		});
	}
	else return false;   
}

function otherOption(othOpt)
{
	console.log('otherOption = ' + othOpt);
	var wsMsg;
	if (othOpt.substr(0,6) == "wsSend")
	{
		wsMsg = othOpt.substr(7);
		othOpt = "wsSend";
	}
	
	switch (othOpt)
	{
		case 'promote':
		$("#dialog").dialog('open');
		break;
		
		case 'wsSend':
		websocket.send(wsMsg);
		break;
		
		default:
		console.log("ERROR: Unknown othOpt val.");
		break;
	}
}

function ajaxResponse(ajaxData)
{
	if (ajaxData[0]!='-1') $('#whitePlayer').html(ajaxData[0]);
	if (ajaxData[1]!='-1') $("#blackPlayer").html(ajaxData[1]);
	
	if (ajaxData[2]!='-1') $("#whitePlayer").attr("disabled", ajaxData[2]);
	if (ajaxData[3]!='-1') $("#blackPlayer").attr("disabled", ajaxData[3]);
	if (ajaxData[4]!='-1') $("#standUpWhite").attr("disabled", ajaxData[4]);
	if (ajaxData[5]!='-1') $("#standUpBlack").attr("disabled", ajaxData[5]);
	if (ajaxData[6]!='-1') $("#startGame").attr("disabled", ajaxData[6]);
	if (ajaxData[7]!='-1') $("#openGiveUpDialogButton").attr("disabled", ajaxData[7]);
	if (ajaxData[8]!='-1') $("#pieceFrom").attr("disabled", ajaxData[8]);
	if (ajaxData[9]!='-1') $("#pieceTo").attr("disabled", ajaxData[9]);
	if (ajaxData[10]!='-1') $("#movePieceButton").attr("disabled", ajaxData[10]);
	
	if (ajaxData[13]!='-1') console.log(ajaxData[13]);
	if (ajaxData[14]!='-1') debugToGameTextArea(ajaxData[14]);
	if (ajaxData[15]!='-1') otherOption(ajaxData[15]);
	
	if (ajaxData[11]!='-1') console.log(ajaxData[11]);
	if (ajaxData[12]!='-1') debugToGameTextArea(ajaxData[12]);
}

function newPlayer(id) 
{
	$.ajax(
	{
		url: "php/newplayer.php",
		type: "POST",
		dataType: "json",
		data: { type: id }, 
		success: function (data) 
		{ 			
			var arr = $.map(data, function(el) { return el; });
			console.log('ajax: newplayer.php- success: ' + arr); 
			ajaxResponse(arr);
		},
		error: function(xhr, status, error) 
		{
			var err = eval("(" + xhr.responseText + ")");
			alert(err.Message);
		}
	})
}

function newGame()
{
	$.ajax(
	{
		url: "php/newgame.php",
		type: "POST",
		dataType: "json",
		data: { }, 
		success: function (data) 
		{ 			
			var arr = $.map(data, function(el) { return el; });
			console.log('ajax: newgame.php- success: ' + arr); 
			ajaxResponse(arr);
		},
		error: function(xhr, status, error) 
		{
			var err = eval("(" + xhr.responseText + ")");
			alert(err.Message);
		}
	})
}

function movePiece()
{
	var from = $("#pieceFrom").val();
	var to = $("#pieceTo").val();;
	$("#pieceFrom").val("");
	$("#pieceTo").val("");
	
	$.ajax(
	{
		url: "php/move.php",
		type: "POST",
		dataType: "json",
		data: 
		{ 
			pieceFrom: from,
			pieceTo: to
		}, 
		success: function (data) 
		{ 			
			var arr = $.map(data, function(el) { return el; });
			console.log('ajax: move.php- success: ' + arr); 
			ajaxResponse(arr);
		},
		error: function(xhr, status, error) 
		{
			var err = eval("(" + xhr.responseText + ")");
			alert(err.Message);
		}
	})
}