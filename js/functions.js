function disableAll()
{
	$("#whitePlayer").attr("disabled", true);
	$("#blackPlayer").attr("disabled", true);
	$("#standUpWhite").attr("disabled", true);
	$("#standUpBlack").attr("disabled", true);
	$("#giveUpBtn").attr("disabled", true);
	$("#pieceFrom").attr("disabled", true);
	$("#pieceTo").attr("disabled", true);
	$("#movePieceButton").attr("disabled", true);
	$("#queuePlayer").attr("disabled", true);
	$("#leaveQueue").attr("disabled", true);
}

function addMsgToClientPlainTextWindow(message) 
{
	clientPlainTextWindow.value += message + "\n";
	clientPlainTextWindow.scrollTop = clientPlainTextWindow.scrollHeight;
}

var timerStart = null;
function turnOffStartTimerIfItsOn()
{
	if (timerStart)
	{
		console.log("turnOffStartTimerIfItsOn()");
		clearInterval(timerStart);
		timerStart = null;
	}
}

function closeStartGameDialogIfOpened()
{
	clickedStart = false;
	console.log("closeStartGameDialogIfOpened(): try to close startGameDialog");
	if ($("#startGameDialog").dialog(startGameVar).dialog('isOpen')) 
	{
		console.log("startGameDialog is open. close it");
		$("#startGameDialog").dialog(startGameVar).dialog("close"); 
	}
}

var startLeftTime;
var startInfo = "Wciśnij start, by rozpocząć grę. Pozostały czas: ";
var clickedStart = false;
function showStartDialog(isStart, whiteName, blackName)
{
	if (isStart && whiteName != "WHITE" && blackName != "BLACK" && whiteName != "-1" && blackName != "-1" && !$("#startGameDialog").dialog(startGameVar).dialog('isOpen'))
	{ //todo: trochę oszukałem z warunkiem na startGameDialog. okno by się odświerzało z timerem wraz z każdą zmianą w kolecje (nowym TABLE_DATA)
		turnOffStartTimerIfItsOn();
		closeStartGameDialogIfOpened();

		startLeftTime = 120;
		console.log("open startGameDialog");
		$("#startGameDialog").dialog(startGameVar).dialog("open"); 
		clickedStart = false;
		startInfo = "Wciśnij start, by rozpocząć grę. Pozostały czas: ";
		
		if (!timerStart) timerStart = setInterval(function() 
		{ 
			$("#startGameDialog").html(startInfo + startLeftTime);
			startLeftTime = startLeftTime - 1;
			if (startLeftTime <= 0)
			{
				turnOffStartTimerIfItsOn();
				closeStartGameDialogIfOpened();
			}
		}, 1000); 
		else "ERROR: timerStart = true";
	}
	
	if (clickedStart) 
		$("#startGameDialog").dialog(startGameVar).dialog("option", "buttons", {}); //todo: to jest test
}

var timerGame2ndP = null;
var startGameVar = 
{ 
	autoOpen: false, 
	dialogClass: "no-close",
	title: "Start gry",
	closeOnEscape: true,	
	close: function (event, ui) 
	{
		if (event.originalEvent) 
			$(this).dialog("open");
	},
	buttons: 
	{
		'start': function() 
		{
			clickedStart = true;
			websocket.send("newGame"); 
			console.log('clicked: start');
			
			startInfo = "Oczekiwanie aż drugi gracz wciśnie start: ";
			$(this).dialog("option", "buttons", {});
		}, 
		'wstań': function() 
		{
			console.log('clicked: standUp');
			if (!$("#standUpWhite").is(":disabled")) newPlayer("standUpWhite");
			else if (!$("#standUpBlack").is(":disabled")) newPlayer("standUpBlack");
			
			if ($(this).dialog('isOpen')) $(this).dialog("close");
		}
	}
};

function updateQueueTextArea(queueList)
{
	if (queueList != "queueEmpty") 
	{
		var queueListArr = queueList.split(",");
		var index;
		var queueListPlainText = "";
		for (index = 0; index < queueListArr.length; ++index) 
		{
			console.log(queueListArr[index]);
			var indexPlainText = index + 1;
			queueListPlainText += indexPlainText + ". " + queueListArr[index] + "\n";
		}
		queueTextArea.value = queueListPlainText;
		queueTextArea.scrollTop = queueTextArea.scrollHeight;
	}
	else queueTextArea.value = "";
}

var timerGame = null;
function startWhiteTimerIfFirstTurn(whiteTimeLeft, blackTimeLeft)
{
	if (whiteTimeLeft == 30*60 && blackTimeLeft == 30*60)
	{
		resetPlayersTimers();
		if (!timerGame) timerGame = setInterval(function(){ updatePlayersTime() }, 1000);
		closeStartGameDialogIfOpened();
	}
}

var whiteTotalSeconds = "-1";
var blackTotalSeconds = "-1";
var whoseTurn = "-1";
function ajaxResponse(ajaxData)
{
	if (ajaxData[0] !='-1') $('#whitePlayer').html(ajaxData[0]);
	if (ajaxData[1] !='-1') $("#blackPlayer").html(ajaxData[1]);
	
	if (ajaxData[2] !='-1') console.log(ajaxData[2]);
	if (ajaxData[3] !='-1') addMsgToClientPlainTextWindow(ajaxData[3]);
	if (ajaxData[4] !='-1') otherOption(ajaxData[4]);
	
	if (ajaxData[5] !='-1') console.log(ajaxData[5]);
	if (ajaxData[6] !='-1') addMsgToClientPlainTextWindow(ajaxData[6]);
	
	if (ajaxData[7] !='-1') $("#whitePlayer").attr("disabled", ajaxData[7]);
	if (ajaxData[8] !='-1') $("#blackPlayer").attr("disabled", ajaxData[8]);
	if (ajaxData[9] !='-1') $("#standUpWhite").attr("disabled", ajaxData[9]);
	if (ajaxData[10] !='-1') $("#standUpBlack").attr("disabled", ajaxData[10]);
	if (ajaxData[11] !='-1') showStartDialog(!ajaxData[11], ajaxData[0], ajaxData[1]); 	
	if (ajaxData[12] !='-1') $("#giveUpBtn").attr("disabled", ajaxData[12]);
	if (ajaxData[13] !='-1') $("#pieceFrom").attr("disabled", ajaxData[13]);
	if (ajaxData[14] !='-1') $("#pieceTo").attr("disabled", ajaxData[14]);
	if (ajaxData[15] !='-1') $("#movePieceButton").attr("disabled", ajaxData[15]);
	if (ajaxData[16] !='-1') $("#queuePlayer").attr("disabled", ajaxData[16]);
	if (ajaxData[17] !='-1') $("#leaveQueue").attr("disabled", ajaxData[17]);
	
	if (ajaxData[18]!='-1') $("#queueMsg").html(ajaxData[18]);
	if (ajaxData[19]!='-1') updateQueueTextArea(ajaxData[19]);
	
	if (ajaxData[20] !='-1') whiteTotalSeconds = ajaxData[20]; 
	if (ajaxData[21] !='-1') blackTotalSeconds = ajaxData[21];
	if (ajaxData[22] !='-1') whoseTurn = ajaxData[22];
	
	if (whoseTurn != "-1" && whoseTurn != "NO_TURN") startWhiteTimerIfFirstTurn(whiteTotalSeconds, blackTotalSeconds);
	
	if (whiteTotalSeconds != "-1" || blackTotalSeconds != "-1") //todo: zapakować w funkcję
	{
		if (whiteTotalSeconds != "-1") 
			$("#whiteTime").html(secondsToMinutesAndSeconds(whiteTotalSeconds));
		if (blackTotalSeconds != "-1") 
			$("#blackTime").html(secondsToMinutesAndSeconds(blackTotalSeconds));
		
		if (timerGame)
		{
			clearInterval(timerGame);
			timerGame = null;
		}
		timerGame = setInterval(function(){ updatePlayersTime() }, 1000);
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
	closeOnEscape: true,	
	close: function (event, ui) 
	{	
		if (event.originalEvent) 
		{
			websocket.send("promoteTo: q"); // auto promote queen
			console.log('auto promote: promoteTo: q');
			addMsgToClientPlainTextWindow("Pion promowany na: hetman.");
			if ($(this).dialog('isOpen')) $(this).dialog("close");
		}
	},
	buttons: 
	{
		'\u265B': function() //todo: mogę się kiedyś pokusić o zrobienie podziału koloru znaków na białe/czarne. białe: U+2655, U+2657, U+2658, U+2656.
		{
			websocket.send("promoteTo: q"); //queen
			console.log('clicked: promoteTo: q');
			addMsgToClientPlainTextWindow("Pion promowany na: hetman.");
			if ($(this).dialog('isOpen')) $(this).dialog("close");
		}, 
		'\u265D': function() 
		{
			websocket.send("promoteTo: b"); //bishop
			console.log('clicked: promoteTo: b');
			addMsgToClientPlainTextWindow("Pion promowany na: goniec.");
			if ($(this).dialog('isOpen')) $(this).dialog("close");
		}, 
		'\u265E': function() 
		{
			websocket.send("promoteTo: k"); //knight
			console.log('clicked: promoteTo: k');
			addMsgToClientPlainTextWindow("Pion promowany na: skoczek.");
			if ($(this).dialog('isOpen')) $(this).dialog("close");
		}, 
		'\u265C': function() 
		{
			websocket.send("promoteTo: r"); //rook
			console.log('clicked: promoteTo: r');
			addMsgToClientPlainTextWindow("Pion promowany na: wieża.");
			if ($(this).dialog('isOpen')) $(this).dialog("close");
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
			if ($(this).dialog('isOpen')) $(this).dialog("close");
		}, 
		'nie': function() 
		{
			if ($(this).dialog('isOpen')) $(this).dialog("close");
		}
	}
};

function giveUp() 
{
	$("#giveUpDialog").dialog(giveUpVar).dialog("open");
}

function clickedBtn(buttonType)
{
	disableAll();
	var msgForCore;
	switch(buttonType)
	{
		//todo: uda mi się to zamknąć w 1 linijce?
		case "sitOnWhite": msgForCore = "sitOnWhite"; break; 
		case "sitOnBlack": msgForCore = "sitOnBlack"; break; 
		case "standUp": msgForCore = "standUp"; break;
		//case giveUp: msgForCore = "giveUp"; break; - openDialog //todo: przekierowanie później z poszczególnych buttonów w dialogu tutaj robić?
		case "queueMe": msgForCore = "queueMe"; break; 
		case "leaveQueue": msgForCore = "leaveQueue"; break; 
		default: console.log("ERROR: unknown buttonType type"); break;
	}
	console.log("clicked btn: " + msgForCore);
	if (msgForCore) websocket.send(msgForCore); 
}

function confirmLogout() 
{	
	var logoutMsg = "Czy na pewno chcesz się wylogować?";
	if (confirm(logoutMsg)) return true;
	else return false;   
}

function resetPlayersTimers()
{
	whiteTotalSeconds = 30*60;
	blackTotalSeconds = 30*60;
	$("#whiteTime").html("30:00");
	$("#blackTime").html("30:00");
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
			
			//todo: wyłącz okno startu, tylko że zrób to w dialogu, a nie tu
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

function secondsToMinutesAndSeconds(time)
{
	if  (time < 0) time = 0;
	secs = time % 60;
	mins = parseInt(time / 60);
	
	var secsPrefix = (secs > 9 ? "" : "0");
	var minsPrefix = (mins > 9 ? "" : "0");
	
	var minsAndSecs = minsPrefix + mins + ":" + secsPrefix + secs;
	return minsAndSecs;
}

function updatePlayersTime()
{
	var secs;
	var mins;
	if (whoseTurn == "WHITE_TURN")
	{
		whiteTotalSeconds--;
		$("#whiteTime").html(secondsToMinutesAndSeconds(whiteTotalSeconds));
	}
	else if (whoseTurn == "BLACK_TURN")
	{
		blackTotalSeconds--;		
		$("#blackTime").html(secondsToMinutesAndSeconds(blackTotalSeconds));
	}
	else if (whoseTurn == "NO_TURN")
	{
		resetPlayersTimers();
	}
	else console.log("ERROR: updatePlayersTime(): unknown turn = " + whoseTurn);
}				