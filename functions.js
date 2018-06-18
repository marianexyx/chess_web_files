var reactBeep = new Audio('sounds/beep1.wav');
alertWindow = (function () 
{
	//todo: nie umiem zmienić koloru na migający niebieski
	console.log("alertWindow() and play beep"); 
	
    var oldTitle = document.title;
    var msg = "Oczekiwanie na gracza!";
    var timeoutId;
    var blink = function() { document.title = document.title == msg ? ' ' : msg; };
    var clear = function() 
	{
        clearInterval(timeoutId);
        document.title = oldTitle;
        window.onmousemove = null;
        timeoutId = null;
    };
    return function () 
	{
        if (!timeoutId) 
		{
            timeoutId = setInterval(blink, 1000);
            window.onmousemove = clear;
        }
    };
}());

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

var infoPTEval = "";
var historyPTEval = "";
var queuePTEval = "";
var PTEtype = "infoPTE";

function refreshActualClientPlainTextWindowValue()
{
	if (PTEtype == "infoPTE")
	{
		clientPlainTextWindow.value = infoPTEval;
		clientPlainTextWindow.scrollTop = clientPlainTextWindow.scrollHeight;
	}
	else if (PTEtype == "historyPTE")
	{
		clientPlainTextWindow.value = historyPTEval;
		clientPlainTextWindow.scrollTop = clientPlainTextWindow.scrollHeight;
	}
	else if (PTEtype == "queuePTE")
	{
		clientPlainTextWindow.value = queuePTEval;
	}
	else console.log("ERROR: refreshActualClientPlainTextWindowValue(): unknown PTEtype val = " + PTEtype);
}

var endOfGameVar = 
{ 
	autoOpen: false, 
	title: "Koniec gry",
	closeOnEscape: true,	
	buttons: 
	{
		'OK': function()
		{
			if ($(this).dialog('isOpen'))
				$(this).dialog('close');
		}
	}
};

function addMsgToClientPlainTextWindow(message, type)
{
	if (type == "info") 
	{
		infoPTEval += "> " + message + "\n";
		if (message.indexOf("Koniec gry") != -1)
		{
			$("#endOfGameDialog").html(message);
			$("#endOfGameDialog").dialog(endOfGameVar).dialog("open");
			showPromotions("-1");
			addMsgToClientPlainTextWindow("-1", "history");
			setTimeout(function() { $("#endOfGameDialog").dialog('close'); }, 10000)
		}
	}
	else if (type == "history") historyPTEval = historyInOneLineToHistoryPTE(message);
	else if (type == "queue") queuePTEval = queueInOneLineToQueuePTE(message);
	else console.log("ERROR: unknown type val in addMsgToClientPlainTextWindow");
	
	refreshActualClientPlainTextWindowValue();
}

function changePTEsource(PTEsource)
{
	PTEtype = PTEsource;
	refreshActualClientPlainTextWindowValue();
	
	switch (PTEsource)
	{
		case "infoPTE": 
		$("#infoPTE").attr("disabled", true);
		$("#historyPTE").attr("disabled", false);
		$("#queuePTE").attr("disabled", false);
		$("#queuePlayer").hide();
		$("#leaveQueue").hide();
		break;
		
		case "historyPTE": 
		$("#infoPTE").attr("disabled", false);
		$("#historyPTE").attr("disabled", true);
		$("#queuePTE").attr("disabled", false);
		$("#queuePlayer").hide();
		$("#leaveQueue").hide();
		break;
		
		case "queuePTE":
		$("#infoPTE").attr("disabled", false);
		$("#historyPTE").attr("disabled", false);
		$("#queuePTE").attr("disabled", true);
		$("#queuePlayer").show();
		$("#leaveQueue").show();
		break;
		
		default: 
		console.log("ERROR: unknown changePTEsource type"); 
		break;
	}
	console.log("clicked btn: " + PTEsource);
}

function historyInOneLineToHistoryPTE(historyInOneLine)
{
	historyInOneLine = historyInOneLine.trim(); //remove whitespaces from both sides of a string
	var historyPTETemp = "";
	if (historyInOneLine != "-1")
	{
		var historyArray = historyInOneLine.split(" ");
		if (historyArray.length > 0)
		{
			for (i = 1; i < historyArray.length + 1; i++)
			{
				if (i/2 != Math.ceil(i/2)) historyPTETemp += Math.ceil(i/2) + ". ";
				historyPTETemp += historyArray[i-1];
				if (i/2 != Math.ceil(i/2)) historyPTETemp += "\t";
				else historyPTETemp += "\n";
			}
		}
	}

	return historyPTETemp;
}

function showPromotions(promotions)
{
	if (promotions == "-1") 
	{
		$("#moveSection").css('float','none');
		$("#moveSection").css('clear','both');
		$("#moveSection").css('padding','25px');
		$("#promotionContent").css('display','none');
		$("#promotionContent").html("");
	}
	else 
	{
		$("#moveSection").css('float','left');
		$("#moveSection").css('clear','none');
		$("#moveSection").css('padding-left','130px');
		$("#promotionContent").css('display','block');
		$("#promotionContent").html("&nbsp;<u>Promowane pionki:</u><br/><font size='4'>" + promotionsInOneLineToPromotionsDIV(promotions) + "</font>");
	}
}

function promotionsInOneLineToPromotionsDIV(promotionsInOneLine)
{
	promotionsInOneLine = promotionsInOneLine.trim(); 
	promotionsInOneLine = promotionsInOneLine.replace(/\s/g, '\xa0\xa0\xa0');
		
	var oneLineMaxLength = 26;
	var lines = 1;
	var totalLength = 0;
	do
	{
		//todo: nie przycina perfekcyjnie, ale to naprawde mały problem
		totalLength = oneLineMaxLength * lines;
		if (promotionsInOneLine.length > totalLength)
			promotionsInOneLine = promotionsInOneLine.substr(0,totalLength) + "<br/>" + promotionsInOneLine.substr(totalLength);
		lines++;
	}
	while(promotionsInOneLine.length > totalLength + oneLineMaxLength)
	
	promotionsInOneLine = promotionsInOneLine.replace(/:Q/g, ":\u2655");
	promotionsInOneLine = promotionsInOneLine.replace(/:B/g, ":\u2657");
	promotionsInOneLine = promotionsInOneLine.replace(/:R/g, ":\u2656");
	promotionsInOneLine = promotionsInOneLine.replace(/:N/g, ":\u2658");
	promotionsInOneLine = promotionsInOneLine.replace(/:q/g, ":\u265B");
	promotionsInOneLine = promotionsInOneLine.replace(/:b/g, ":\u265D");
	promotionsInOneLine = promotionsInOneLine.replace(/:r/g, ":\u265C");
	promotionsInOneLine = promotionsInOneLine.replace(/:n/g, ":\u265E");
	return promotionsInOneLine;
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
	console.log("closeStartGameDialogIfOpened(): try to close startGameDialog");
	if ($("#startGameDialog").dialog(startGameVar).dialog('isOpen')) 
	{
		console.log("startGameDialog is open. close it");
		$("#startGameDialog").dialog(startGameVar).dialog('close'); 
	}
	$("#startGameDialog").html("Wciśnij start, by rozpocząć grę. Pozostały czas: 120");
}

var startInfo;
function showStartDialog(wClickedStart, bClickedStart, sTime)
{
	turnOffStartTimerIfItsOn();
	closeStartGameDialogIfOpened();
	console.log("open startGameDialog");
	$("#startGameDialog").dialog(startGameVar).dialog("open"); 
	var whitePlr = $("#whitePlayer").text();
	var blackPlr = $("#blackPlayer").text();
	console.log("js_login = " + js_login + ", whitePlr = " + whitePlr + ", blackPlr = " + blackPlr);
	if ((wClickedStart == "x" && js_login == whitePlr) || (bClickedStart == "x" && js_login == blackPlr))
	{
		alertWindow();
		reactBeep.play();
		startInfo = "Wciśnij start, by rozpocząć grę. Pozostały czas: ";
	}
	else if ((wClickedStart == "w" && js_login == whitePlr) || (bClickedStart == "b" && js_login == blackPlr))
	{
		startInfo = "Oczekiwanie, aż drugi gracz wciśnie start: ";
		$("#startGameDialog").dialog(startGameVar).dialog("option", "buttons", {});
	}
	else
	{
		startInfo = "Oczekiwanie, aż gracze wcisną start: ";
		$("#startGameDialog").dialog(startGameVar).dialog("option", "buttons", {});
	}
	
	if (!timerStart) 
	{
		console.log("timerStart = true");
		timerStart = setInterval(function() 
		{ 
			$("#startGameDialog").html(startInfo + sTime);
			sTime = sTime - 1;
			if (sTime <= 0)
			{
				turnOffStartTimerIfItsOn();
				closeStartGameDialogIfOpened();
			}
		}, 1000); 
	}
	else console.log("ERROR: timerStart = true");
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
			console.log('clicked: start');
			disableAll();
			websocket.send("newGame"); 
			
			startInfo = "Oczekiwanie, aż drugi gracz wciśnie start: ";
			$(this).dialog("option", "buttons", {});
		}, 
		'wstań': function() 
		{
			console.log('clicked: standUp');
			clickedBtn('standUp');		
			if ($(this).dialog('isOpen')) $(this).dialog('close');
		}
	}
};

function queueInOneLineToQueuePTE(queueList)
{
	var queueListPlainText = "";
	if (queueList != "queueEmpty") 
	{
		var queueListArr = queueList.split(",");
		var index;
		for (index = 0; index < queueListArr.length; ++index) 
		{
			console.log(queueListArr[index]);
			var indexPlainText = index + 1;
			queueListPlainText += indexPlainText + ". " + queueListArr[index] + "\n";
		}
	}
	else queueListPlainText = "";
	
	return queueListPlainText;
}

function queueSize(queueList)
{
	if (queueList != "queueEmpty") 
	{
		var queueListArr = queueList.split(",");
		return queueListArr.length;
	}
	else return 0;
}

var timerGame = null;
function startWhiteTimerIfFirstTurn(whiteTimeLeft, blackTimeLeft)
{
	if (whiteTimeLeft == 30*60 && blackTimeLeft == 30*60)
	{
		resetPlayersTimers();
		if (!timerGame) 
		{
			ajaxResponse(ajaxData); //dlaczego tu wcześniej była deklaracja funkcji ajaxResponse?: "function "
			timerGame = setInterval(function(){ updatePlayersTime() }, 1000);
		}
		closeStartGameDialogIfOpened();
	}
}

var whiteTotalSeconds = "-1";
var blackTotalSeconds = "-1";
var whoseTurn = "-1";
function ajaxResponse(ajaxData)
{
	if (ajaxData[0] != '-1') 
	{
		if(ajaxData[0] == "White") $('#whitePlayer').html("-");
		else $('#whitePlayer').html(ajaxData[0]);
	}
	if (ajaxData[1] != '-1') 
	{
		if(ajaxData[1] == "Black") $('#blackPlayer').html("-");
		else $('#blackPlayer').html(ajaxData[1]);
	}
	
	if (ajaxData[2] != '-1') console.log(ajaxData[2]);
	if (ajaxData[3] != '-1') addMsgToClientPlainTextWindow(ajaxData[3], "info");
	if (ajaxData[4] != '-1') otherOption(ajaxData[4]);
	
	if (ajaxData[5] != '-1') console.log(ajaxData[5]);
	if (ajaxData[6] != '-1') addMsgToClientPlainTextWindow(ajaxData[6], "info");
	
	if (ajaxData[7] != '-1') $("#whitePlayer").attr("disabled", ajaxData[7]);
	if (ajaxData[8] != '-1') $("#blackPlayer").attr("disabled", ajaxData[8]);
	if (ajaxData[9] != '-1') $("#standUpWhite").attr("disabled", ajaxData[9]);
	if (ajaxData[10] != '-1') $("#standUpBlack").attr("disabled", ajaxData[10]);
	if (ajaxData[11] == '1') console.log("start dialog should appear"); 
	if (ajaxData[12] != '-1') $("#giveUpBtn").attr("disabled", ajaxData[12]);
	if (ajaxData[13] != '-1') $("#pieceFrom").attr("disabled", ajaxData[13]); 
	if (ajaxData[14] != '-1') $("#pieceTo").attr("disabled", ajaxData[14]);
	if (ajaxData[15] != '-1') $("#movePieceButton").attr("disabled", ajaxData[15]);
	if (ajaxData[16] != '-1') $("#queuePlayer").attr("disabled", ajaxData[16]);
	if (ajaxData[17] != '-1') $("#leaveQueue").attr("disabled", ajaxData[17]);
	
	if (ajaxData[18]!= '-1') 
	{
		var queued = "kolejka(" + queueSize(ajaxData[18]) + ")";
		$("#queuePTE").html(queued); 
		addMsgToClientPlainTextWindow(ajaxData[18], "queue");
	}
	
	if (ajaxData[19] != '-1') whiteTotalSeconds = ajaxData[19]; 
	if (ajaxData[20] != '-1') blackTotalSeconds = ajaxData[20];
	if (ajaxData[21] != '-1') whoseTurn = ajaxData[21];
	
	if (ajaxData[22] != '-1' && ajaxData[23] !='-1' && ajaxData[24] !='-1' && ajaxData[0] !='White' && ajaxData[1] !='Black' && !$("#startGameDialog").dialog(startGameVar).dialog('isOpen')) 
		showStartDialog(ajaxData[22], ajaxData[23], ajaxData[24]); 
	else 
	{
		console.log("ajaxData[22]:" + ajaxData[22] + ", ajaxData[23]:" + ajaxData[23] + ", ajaxData[24]:" + ajaxData[24]);
		closeStartGameDialogIfOpened();
	}
	
	if (ajaxData[25] != '-1') { addMsgToClientPlainTextWindow(ajaxData[25], "history"); }
	if (ajaxData[26] != '-1') { showPromotions(ajaxData[26]); }
	
	
	
	if (whoseTurn != "-1" && whoseTurn != "NO_TURN") startWhiteTimerIfFirstTurn(whiteTotalSeconds, blackTotalSeconds);
	
	if (whoseTurn == "WHITE_TURN") //todo: zapakować w funkcję
	{
		$("#whitePlayerMiniBox").css('background-color', 'lightGreen'); 
		$("#blackPlayerMiniBox").css('background-color', 'white'); 
	}
	else if (whoseTurn == "BLACK_TURN")
	{
		$("#whitePlayerMiniBox").css('background-color', 'white'); 
		$("#blackPlayerMiniBox").css('background-color', 'lightGreen'); 
	}
	else
	{
		$("#whitePlayerMiniBox").css('background-color', 'white'); 
		$("#blackPlayerMiniBox").css('background-color', 'white'); 
		$("#moveSection").css('background-color', 'white'); 
	}
	
	if ((whoseTurn == "WHITE_TURN" || whoseTurn == "BLACK_TURN") && ajaxData[2].includes("TABLE_DATA") == false) //todo: zapakować w funkcję
	{
		if (ajaxData[15] == false)
		{
			$("#moveSection").css('background-color', 'lightGreen'); 
			$("#pieceFrom").focus();
			$('#pieceTo').val('');
			$('#pieceFrom').val('');
		}
		else
			$("#moveSection").css('background-color', 'white'); 
	}
	
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
			websocket.send("promoteTo:q"); //auto promote queen
			console.log('auto promote: promoteTo:q');
			addMsgToClientPlainTextWindow("Pion promowany na: hetman.", "info");
			if ($(this).dialog('isOpen')) $(this).dialog('close');
		}
	},
	buttons: 
	{
		'\u265B': function() //todo: mogę się kiedyś pokusić o zrobienie podziału koloru znaków na białe/czarne. białe: U+2655, U+2657, U+2658, U+2656.
		{
			websocket.send("promoteTo:q"); //queen
			console.log('clicked: promoteTo:q');
			addMsgToClientPlainTextWindow("Pion promowany na: hetman.", "info");
			if ($(this).dialog('isOpen')) $(this).dialog('close');
		}, 
		'\u265D': function() 
		{
			websocket.send("promoteTo:b"); //bishop
			console.log('clicked: promoteTo:b');
			addMsgToClientPlainTextWindow("Pion promowany na: goniec.", "info");
			if ($(this).dialog('isOpen')) $(this).dialog('close');
		}, 
		'\u265E': function() 
		{
			websocket.send("promoteTo:n"); //knight
			console.log('clicked: promoteTo:n');
			addMsgToClientPlainTextWindow("Pion promowany na: skoczek.", "info");
			if ($(this).dialog('isOpen')) $(this).dialog('close');
		}, 
		'\u265C': function() 
		{
			websocket.send("promoteTo:r"); //rook
			console.log('clicked: promoteTo:r');
			addMsgToClientPlainTextWindow("Pion promowany na: wieża.", "info");
			if ($(this).dialog('isOpen')) $(this).dialog('close');
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
			disableAll();
			addMsgToClientPlainTextWindow("Opuszczanie stołu...", "info"); 
			websocket.send("giveUp"); 
			if ($(this).dialog('isOpen')) $(this).dialog('close');
		}, 
		'nie': function() 
		{
			if ($(this).dialog('isOpen')) $(this).dialog('close');
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
		//todo: uda mi się tą funkcję zamknąć w 1 linijce?
		case "sitOnWhite": msgForCore = "sitOn White"; break; 
		case "sitOnBlack": msgForCore = "sitOn Black"; break; 
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

function movePiece()
{
	var pieceFrom = $("#pieceFrom").val().toLowerCase();
	var pieceTo = $("#pieceTo").val().toLowerCase();
	$("#pieceFrom").val("");
	$("#pieceTo").val("");
	var pieceFromLetter = pieceFrom.charAt(0);
	var pieceFromDigit = pieceFrom.charAt(1);
	var pieceToLetter = pieceTo.charAt(0);
	var pieceToDigit = pieceTo.charAt(1);
	
	var squareLetters = ['a','b','c','d','e','f','g','h'];
	if (pieceFrom.length == 2 && pieceTo.length == 2)
	{
		if (pieceFromLetter <= 8 && pieceToLetter <= 8 && pieceFromLetter >= 1 && pieceToLetter >= 1 && 
		jQuery.inArray(pieceFromDigit, squareLetters) != '-1' && jQuery.inArray(pieceToDigit, squareLetters) != '-1')
		{ //todo: testować
			//repair vice versed move command (f.e. "2e,4e" to "e2,e4"
			var pieceFromLetterTemp = pieceFromDigit;
			var pieceFromDigitTemp = pieceFromLetter;
			var pieceToLetterTemp = pieceToDigitTemp;
			var pieceToDigitTemp = pieceToLetter;
			pieceFromLetter = pieceFromLetterTemp;
			pieceFromDigit = pieceFromDigitTemp;
			pieceToLetter = pieceToLetterTemp;
			pieceToDigit = pieceToDigitTemp;
		}
		
		if (pieceFromDigit <= 8 && pieceToDigit <= 8 && pieceFromDigit >= 1 && pieceToDigit >= 1 && 
		jQuery.inArray(pieceFromLetter, squareLetters) != '-1' && jQuery.inArray(pieceToLetter, squareLetters) != '-1')
		{
			disableAll();
			$("#whitePlayerMiniBox").css('background-color', 'white'); 
			$("#blackPlayerMiniBox").css('background-color', 'white'); 
			$("#moveSection").css('background-color', 'white'); 
			websocket.send("move " + pieceFrom + pieceTo);
		}
		else 
		{
			console.log("Błędnie wprowadzone zapytanie o ruch.");
			addMsgToClientPlainTextWindow("Błędnie wprowadzone zapytanie o ruch.", "info"); 
		}
	}
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
		$("#blackTime").html("<br/>" + secondsToMinutesAndSeconds(blackTotalSeconds));
	}
	else if (whoseTurn == "NO_TURN")
	{
		resetPlayersTimers();
	}
	else console.log("ERROR: updatePlayersTime(): unknown turn = " + whoseTurn);
}			

//todo: testować
$(function() { $("#pieceFrom").keyup(function() { pieceFromOnKeyPress(); }); });
$(function() { $("#pieceTo").keyup(function() { pieceToOnKeyPress(); }); });

function pieceFromOnKeyPress() 
{
	if ($('#pieceFrom').val().length >= 2) 
	{
		 $('#pieceTo').val('');
		 $("#pieceTo").focus();
	}
}

function pieceToOnKeyPress() 
{
	if ($('#pieceFrom').val().length >= 2 && $('#pieceTo').val().length >= 2) 
		$("#movePieceButton").focus();
}

function serverStatus(state)
{
	switch (state)
	{
		case "connecting": 
		$("#serverCSSCircleStatus").css("background-color", "grey");
		$("#serverStatusInfo").html("ŁĄCZENIE...");
		break;
		
		case "online": 
		$("#serverCSSCircleStatus").css("background-color", "green");
		$("#serverStatusInfo").html("ONLINE");
		break;
		
		case "offline":
		$("#serverCSSCircleStatus").css("background-color", "red");
		$("#serverStatusInfo").html("OFFLINE");
		break;
		
		default: 
		console.log("ERROR: unknown serverStatus state:", state); 
		$("#serverCSSCircleStatus").css("background-color", "red");
		$("#serverStatusInfo").html("OFFLINE");
		break;
	}
}