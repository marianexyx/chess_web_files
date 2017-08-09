function debugToGameTextArea(message) 
{
	debugTextArea.value += message + "\n";
	debugTextArea.scrollTop = debugTextArea.scrollHeight;
}

var player;
function ajaxResponse(ajaxData)
{
	if (ajaxData[0]!='-1') $('#whitePlayer').html(ajaxData[0]);
	if (ajaxData[1]!='-1') $("#blackPlayer").html(ajaxData[1]);
	
	if (ajaxData[2]!='-1') $("#whitePlayer").attr("disabled", ajaxData[2]);
	if (ajaxData[3]!='-1') $("#blackPlayer").attr("disabled", ajaxData[3]);
	if (ajaxData[4]!='-1') $("#standUpWhite").attr("disabled", ajaxData[4]);
	if (ajaxData[5]!='-1') $("#standUpBlack").attr("disabled", ajaxData[5]);
	if (ajaxData[6]!='-1') $("#startGame").attr("disabled", ajaxData[6]);
	if (ajaxData[7]!='-1') $("#giveUpBtn").attr("disabled", ajaxData[7]);
	if (ajaxData[8]!='-1') $("#pieceFrom").attr("disabled", ajaxData[8]);
	if (ajaxData[9]!='-1') $("#pieceTo").attr("disabled", ajaxData[9]);
	if (ajaxData[10]!='-1') $("#movePieceButton").attr("disabled", ajaxData[10]);
	
	if (ajaxData[13]!='-1') console.log(ajaxData[13]);
	if (ajaxData[14]!='-1') debugToGameTextArea(ajaxData[14]);
	if (ajaxData[15]!='-1') otherOption(ajaxData[15]);
	
	if (ajaxData[11]!='-1') console.log(ajaxData[11]);
	if (ajaxData[12]!='-1') debugToGameTextArea(ajaxData[12]);
	
	if (ajaxData[14].substr(0,3) == 'Bia' || ajaxData[14].substr(0,8) == "Nowa gra") player = "black";
	else if (ajaxData[14].substr(0,3) == 'Cza') player = "white";
	if (ajaxData[14].substr(0,10) == 'Koniec gry'  || ajaxData[14].substr(0,12) == "Koniec czasu" ||
	ajaxData[13] == 'white player = WHITE' || ajaxData[13] == 'black player = BLACK') 
	{
		resetPlayersTimers();
	}
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
		$("#promoteDialog").dialog(promoteVar).dialog("open");
		break;
		
		case 'wsSend':
		websocket.send(wsMsg);
		break;
		
		default:
		console.log("ERROR: Unknown othOpt val.");
		break;
	}
}


var promoteVar = 
{ //todo: sprawdzić co się stanie podczas wszystkich rodzajów wyjść z gry podczas obsługi promocji
	autoOpen: false, 
	dialogClass: "no-close",
	title: "Promuj piona na:",
	buttons: 
	{
		'\u265B': function() //todo: mogę się kiedyś pokusić o zrobienie podziału koloru znaków na białe/czarne. białe: U+2655, U+2657, U+2658, U+2656.
		{
			websocket.send("promoteTo: q"); //queen
			console.log('clicked: promoteTo: q');
			debugToGameTextArea("Pion promowany na: hetman.");
			$(this).dialog("close");
		}, 
		'\u265D': function() 
		{
			websocket.send("promoteTo: b"); //bishop
			console.log('clicked: promoteTo: b');
			debugToGameTextArea("Pion promowany na: goniec.");
			$(this).dialog("close");
		}, 
		'\u265E': function() 
		{
			websocket.send("promoteTo: k"); //knight
			console.log('clicked: promoteTo: k');
			debugToGameTextArea("Pion promowany na: skoczek.");
			$(this).dialog("close");
		}, 
		'\u265C': function() 
		{
			websocket.send("promoteTo: r"); //rook
			console.log('clicked: promoteTo: r');
			debugToGameTextArea("Pion promowany na: wieża.");
			$(this).dialog("close");
		}
	}
};

var giveUpVar = 
{
	autoOpen: false,
	modal: true,
	draggable: false,
	resizable: false,
	title: "Rezygnacja",
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
				},
				error: function(xhr, status, error) 
				{
					var err = eval("(" + xhr.responseText + ")");
					alert(err.Message);
				}
			});
			$(this).dialog("close");
		}, 
		'nie': function() 
		{
			$(this).dialog("close");
		}
	}
};

function giveUp() 
{
	$("#giveUpDialog").dialog(giveUpVar).dialog("open");
}

function deleteask()
{
	if (confirm("Czy na pewno chcesz się wylogować?")) 
	{
		//websocket.send("logoutMe"); //todo: obsłużyć w core
		return true;
		/*var request = $.ajax(
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
			},
			error: function(xhr, status, error) 
			{
			var err = eval("(" + xhr.responseText + ")");
			alert(err.Message);
			}
			});
			
			request.done(function() 
			{
			return true;		
			});
			
			request.fail(function() 
			{
			return false;		
		});*/
	}
	else return false;   
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

var whiteTotalSeconds;
var blackTotalSeconds;
var timer = null;
function resetPlayersTimers()
{
	console.log('resetPlayersTimers();');
	whiteTotalSeconds = 30*60;
	blackTotalSeconds = 30*60;
	if (timer) 
	{
		clearInterval(timer);
		timer = null;
	}
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
			
			resetPlayersTimers();
			player = "white";
			if (!timer) timer = setInterval(function(){ updatePlayersTime() }, 1000);
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

function info()
{
	$("#info").html('mariusz.pak.89@gmail.com | <a href="index.php?a=logout" onclick="return deleteask();">Wyloguj się</a></center>');
}

function updatePlayersTime()
{
	var secs;
	var mins;
	if (player == "white")
	{
		whiteTotalSeconds--;
		if  (whiteTotalSeconds < 0) whiteTotalSeconds = 0;
		secs = whiteTotalSeconds % 60;
		mins = parseInt(whiteTotalSeconds / 60);
		
		var secsPrefix = (secs > 9 ? "" : "0");
		var minsPrefix = (mins > 9 ? "" : "0");
		
		$("#whiteTime").html(minsPrefix + mins + ":" + secsPrefix + secs);
	}
	else if (player == "black")
	{
		blackTotalSeconds--;
		if  (blackTotalSeconds < 0) blackTotalSeconds = 0;
		secs = blackTotalSeconds % 60;
		mins = parseInt(blackTotalSeconds / 60);
		
		var secsPrefix = (secs > 9 ? "" : "0");
		var minsPrefix = (mins > 9 ? "" : "0");
		
		$("#whiteTime").html(minsPrefix + mins + ":" + secsPrefix + secs);
	}
	else console.log("ERROR: function updatePlayersTime(player): unknown player = " + player);
}			