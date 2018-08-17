//future: poupychać funkcje do osobnych plików

var whiteTotalSeconds = "-1";
var blackTotalSeconds = "-1";
var whoseTurn = "-1";
var bTableIsFull = false;
var bClientIsLogged = false;
var bClientIsPlayer = false;
var bClientIsWhitePlayer = false;
var bClientIsBlackPlayer = false;
var bClientIsInQueue = false;
var bPlayerCanSendMove = false;
var infoMsgPTE = "";
function ajaxResponse(ajaxData)
{
	disableAll();
	var startTimeVar = 0;
	var otherOptionVar = "";

	if (ajaxData.hasOwnProperty("consoleMsg")) console.log(ajaxData["consoleMsg"]);
	if (ajaxData.hasOwnProperty("PTEmsg")) infoMsgPTE = ajaxData["PTEmsg"];
	if (ajaxData.hasOwnProperty("whoseTurn")) whoseTurn = ajaxData["whoseTurn"];
	if (ajaxData.hasOwnProperty("whitePlayerName")) setName("White", ajaxData["whitePlayerName"]);
	if (ajaxData.hasOwnProperty("blackPlayerName")) setName("Black", ajaxData["blackPlayerName"]);
	if (ajaxData.hasOwnProperty("whitePlayerTimeLeft")) whiteTotalSeconds = ajaxData["whitePlayerTimeLeft"];
	if (ajaxData.hasOwnProperty("blackPlayerTimeLeft")) blackTotalSeconds = ajaxData["blackPlayerTimeLeft"];
	if (ajaxData.hasOwnProperty("startTimeLeft")) startTimeVar = ajaxData["startTimeLeft"];
	if (ajaxData.hasOwnProperty("historyOfMoves")) addMsgToClientPlainTextWindow(ajaxData["historyOfMoves"], "history");
	if (ajaxData.hasOwnProperty("promotedPawnsList")) showPromotions(ajaxData["promotedPawnsList"]);
	if (ajaxData.hasOwnProperty("queuedPlayers")) addMsgToClientPlainTextWindow(ajaxData["queuedPlayers"], "queue");
	if (ajaxData.hasOwnProperty("clientIsLogged")) bClientIsLogged = ajaxData["clientIsLogged"];
	if (ajaxData.hasOwnProperty("loggedPlayerIsOnAnyChair")) bClientIsPlayer = ajaxData["loggedPlayerIsOnAnyChair"];
	if (ajaxData.hasOwnProperty("loggedPlayerIsOnWhiteChair")) bClientIsWhitePlayer = ajaxData["loggedPlayerIsOnWhiteChair"];
	if (ajaxData.hasOwnProperty("loggedPlayerIsOnBlackChair")) bClientIsBlackPlayer = ajaxData["loggedPlayerIsOnBlackChair"];
	if (ajaxData.hasOwnProperty("tableIsFull")) bTableIsFull = ajaxData["tableIsFull"];
	if (ajaxData.hasOwnProperty("clientIsInQueue")) bClientIsInQueue = ajaxData["clientIsInQueue"];
	if (ajaxData.hasOwnProperty("whitePlayerBtn")) $("#whitePlayer").attr("disabled", !ajaxData["whitePlayerBtn"]);
	if (ajaxData.hasOwnProperty("blackPlayerBtn")) $("#blackPlayer").attr("disabled", !ajaxData["blackPlayerBtn"]);
	if (ajaxData.hasOwnProperty("playerCanMakeMove")) bPlayerCanSendMove = ajaxData["playerCanMakeMove"];
	if (ajaxData.hasOwnProperty("queuePlayerBtn")) $("#queuePlayer").attr("disabled", !ajaxData["queuePlayerBtn"]);
	if (ajaxData.hasOwnProperty("leaveQueueBtn")) $("#leaveQueue").attr("disabled", !bClientIsInQueue);
	if (ajaxData.hasOwnProperty("specialOption")) otherOptionVar = ajaxData["specialOption"];
	else closeStartGameDialogIfOpened();
	
	//needed to save above params before using some of this functions below, to avoid using non-updated global vars
	if (ajaxData.hasOwnProperty("PTEmsg")) addMsgToClientPlainTextWindow(infoMsgPTE, "info");
	updatePlayersTimers();
	updateHelpInfo();
	manageStandUpBtns();
	showActivePlayerWithCSS();
	if (startTimeVar > 0) showStartDialog(ajaxData["startTimeLeft"]); 
	if (ajaxData.hasOwnProperty("specialOption")) otherOption(otherOptionVar);
	letPlayerMakeMoveIfItsHisTurn();
}

function setName(playerType, name)
{
	if (playerType == "White")
	{
		if (!name || name == "0" || name == "-1" || name == "-")
		{
			$('#whitePlayer').html("-");
			console.log("function setName: white name is empty. name =" + name);
		}
		else 
		{
			$('#whitePlayer').html(name);
			console.log("function setName: white name isn't empty. name =" + name);
		}
	}
	else if (playerType == "Black")
	{
		if (!name || name == "0" || name == "-1" || name == "-")
			$('#blackPlayer').html("-");
		else $('#blackPlayer').html(name);
	}
}

function otherOption(othOpt)
{
	if (othOpt == 'newGameStarted')
		startGameTimers(); 
	else if (othOpt == 'badMove')
		badMove();
	else if (othOpt == 'promote')
		$("#promoteDialog").dialog(promoteVar).dialog("open");
	else if (othOpt == 'endOfGame')
	{
		$("#endOfGameDialog").html(infoMsgPTE); 
		$("#endOfGameDialog").dialog(endOfGameVar).dialog("open");
		setTimeout(function() { $("#endOfGameDialog").dialog('close'); }, 7000)
	}
	else if (othOpt == 'doubleLogin')
	{
		disableAll();
		stopWebSocket();
		window.location.href = 'index.php?a=logout&b=doubleLogin';
	}
	else if (othOpt == 'wrongData')
	{
		disableAll();
		stopWebSocket();
		window.location.href = 'index.php?a=logout&b=wrongData';
	}
	else console.log("ERROR: Unknown othOpt val.");
}

function updatePlayersTimers()
{
	$("#whiteTime").html("Gracz Biały: " + secondsToMinutesAndSeconds(whiteTotalSeconds));
	$("#blackTime").html("Gracz Czarny: " + secondsToMinutesAndSeconds(blackTotalSeconds));
	
	if (timerGame)
	{
		clearInterval(timerGame);
		timerGame = null;
	}
	
	timerGame = setInterval(function(){ updatePlayersTime() }, 1000);
}

function updateHelpInfo()
{
	$("#additionalInfo").css('color', 'black'); 
	
	if (!bClientIsLogged)
		$("#additionalInfo").html("Musisz być zalogowany, aby móc grać.");
	else if (bClientIsLogged && !bTableIsFull && !bClientIsPlayer)
		$("#additionalInfo").html("By zagrać wybierz kolor bierek klikając przycisk gracza.");
	else if (bClientIsLogged && !bTableIsFull && bClientIsPlayer && !bClientIsInQueue)
		$("#additionalInfo").html("Oczekiwanie, aż do stołu gry przysiądzie się drugi gracz.");
	else if (bClientIsLogged && bTableIsFull && !bClientIsPlayer && !bClientIsInQueue)
		$("#additionalInfo").html("Stół gry jest pełen. By zagrać wejdź do kolejki graczy.");
	else
		$("#additionalInfo").html("&nbsp");
}

var reactBeep = new Audio('sounds/beep1.wav');
function badMove()
{
	reactBeep.play();
	$("#additionalInfo").html("Błędne rządanie ruchu! Wybierz inny ruch.");
	$("#additionalInfo").css('color', 'red'); 
	setTimeout(function() 
	{ 
		$("#additionalInfo").html("&nbsp");
		$("#additionalInfo").css('color', 'black'); 
	}, 7000)
}

function manageStandUpBtns()
{
	if (bClientIsPlayer)
	{
		if (whoseTurn == 'noTurn')
		{
			if (bClientIsWhitePlayer) 
			{
				$("#standUpWhite").show();
				$("#standUpWhite").attr("disabled", false);
				$("#standUpWhite").html("Wstań");
			}
			else if (bClientIsBlackPlayer) 
			{
				$("#standUpBlack").show();
				$("#standUpBlack").attr("disabled", false);
				$("#standUpBlack").html("Wstań");
			}
		}
		else if (whoseTurn == 'whiteTurn' || whoseTurn == 'blackTurn')
		{
			if (bClientIsWhitePlayer)
			{
				$("#standUpWhite").show();
				$("#standUpWhite").attr("disabled", false);
				$("#standUpWhite").html("Wyjdź");
			}
			else if (bClientIsBlackPlayer)
			{
				$("#standUpBlack").show();
				$("#standUpBlack").attr("disabled", false);
				$("#standUpBlack").html("Wyjdź");
			}
			else console.log("ERROR: bClientIsPlayer == true, but != white && black");
		}
	}
	else //turn off btns
	{
		$("#standUpWhite").hide();
		$("#standUpBlack").hide();
		$("#standUpWhite").attr("disabled", false);
		$("#standUpBlack").attr("disabled", false);
	}
}

function showActivePlayerWithCSS()
{
	if (whoseTurn == "whiteTurn") 
	{
		$("#whitePlayerMiniBox").css('background-color', 'lightGreen'); 
		$("#blackPlayerMiniBox").css('background-color', 'white'); 
	}
	else if (whoseTurn == "blackTurn")
	{
		$("#whitePlayerMiniBox").css('background-color', 'white'); 
		$("#blackPlayerMiniBox").css('background-color', 'lightGreen'); 
	}
	else
	{
		$("#whitePlayerMiniBox").css('background-color', 'white'); 
		$("#blackPlayerMiniBox").css('background-color', 'white'); 
	}
}

function letPlayerMakeMoveIfItsHisTurn()
{
	if (bPlayerCanSendMove) $("#perspective").css('z-index', '10'); 
	else $("#perspective").css('z-index', '8'); 
}

function disableAll()
{
	$("#whitePlayer").attr("disabled", true);
	$("#blackPlayer").attr("disabled", true);
	$("#standUpWhite").attr("disabled", true);
	$("#standUpBlack").attr("disabled", true);
	$("#queuePlayer").attr("disabled", true);
	$("#leaveQueue").attr("disabled", true);
}

$(function()
{
	disableAll();
});

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
	if (type == "info") infoPTEval += "> " + message + "\n";
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
}

$(function()  //odpala funkcje dopiero po zaladowaniu sie strony 
{
	var clientPlainTextWindow = $("#clientPlainTextWindow");
	clientPlainTextWindow.value = "";
});

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
		clientPlainTextWindow.value = queuePTEval;
	else console.log("ERROR: refreshActualClientPlainTextWindowValue(): unknown PTEtype val = " + PTEtype);
}

function historyInOneLineToHistoryPTE(historyInOneLine)
{
	historyInOneLine = historyInOneLine.trim(); //remove whitespaces from both sides of a string
	var historyPTETemp = "";
	if (historyInOneLine != "0")
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
	else historyPTETemp = "1. -"

	return historyPTETemp;
}

function queueInOneLineToQueuePTE(queueList)
{
	var queueListPlainText = "";
	if (queueList != "0" && queueList != "-1" && queueList != "" && queueList != "-") 
	{
		var queueListArr = queueList.split(" ");
		$("#queuePTE").html("kolejka(" + queueListArr.length + ")");
		var index;
		for (index = 0; index < queueListArr.length; ++index) 
		{
			var indexPlainText = index + 1;
			queueListPlainText += indexPlainText + ". " + queueListArr[index] + "\n";
		}
	}
	else 
	{
		queueListPlainText = "1. -";
		$("#queuePTE").html("kolejka");
	}
	
	return queueListPlainText;
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
	
	promotionsInOneLine = promotionsInOneLine.replace(/_Q/g, ":\u2655");
	promotionsInOneLine = promotionsInOneLine.replace(/_B/g, ":\u2657");
	promotionsInOneLine = promotionsInOneLine.replace(/_R/g, ":\u2656");
	promotionsInOneLine = promotionsInOneLine.replace(/_N/g, ":\u2658");
	promotionsInOneLine = promotionsInOneLine.replace(/_q/g, ":\u265B");
	promotionsInOneLine = promotionsInOneLine.replace(/_b/g, ":\u265D");
	promotionsInOneLine = promotionsInOneLine.replace(/_r/g, ":\u265C");
	promotionsInOneLine = promotionsInOneLine.replace(/_n/g, ":\u265E");
	return promotionsInOneLine;
}

function showPromotions(promotions)
{
	if (promotions == "0") 
	{
		$("#promotionContent").css('display','none');
		$("#promotionContent").html("");
	}
	else 
	{
		$("#promotionContent").css('display','block');
		$("#promotionContent").html("&nbsp;<u>Promowane pionki:</u><br/><font size='4'>" 
			+ promotionsInOneLineToPromotionsDIV(promotions) + "</font>");
	}
}

var startInfo;
function showStartDialog(startTime)
{
	turnOffStartTimerIfItsOn();
	$("#startGameDialog").dialog(startGameVar).dialog("open"); 

	if (bClientIsPlayer)
	{
		alertWindow();
		reactBeep.play();
		startInfo = "Wciśnij start, by rozpocząć grę. Pozostały czas: ";
	}
	else
	{
		startInfo = "Oczekiwanie, aż gracze wcisną start: ";
		$("#startGameDialog").dialog(startGameVar).dialog("option", "buttons", {});
	}
	
	if (!timerStart) 
	{
		timerStart = setInterval(function() 
		{ 
			$("#startGameDialog").html(startInfo + startTime);
			startTime = startTime - 1;
			if (startTime <= 0)
			{
				turnOffStartTimerIfItsOn();
				closeStartGameDialogIfOpened();
			}
		}, 1000); 
	}
	else console.log("ERROR: timerStart = true");
}

function closeStartGameDialogIfOpened()
{
	if ($("#startGameDialog").dialog(startGameVar).dialog('isOpen')) 
		$("#startGameDialog").dialog(startGameVar).dialog('close'); 
	$("#startGameDialog").html("Wciśnij start, by rozpocząć grę. Pozostały czas: 120");
}

var timerStart = null;
function turnOffStartTimerIfItsOn()
{
	if (timerStart)
	{
		clearInterval(timerStart);
		timerStart = null;
	}
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
			websocket.send("newGame"); 
			
			startInfo = "Oczekiwanie, aż drugi gracz wciśnie start: ";
			$(this).dialog("option", "buttons", {});
		}, 
		'wstań': function() 
		{
			clickedBtn('standUp');		
			if ($(this).dialog('isOpen')) $(this).dialog('close');
		}
	}
};

var timerGame = null;
function startGameTimers()
{
	resetPlayersTimers();
	if (!timerGame) timerGame = setInterval(function(){ updatePlayersTime() }, 1000);
	closeStartGameDialogIfOpened();
}

function resetPlayersTimers()
{
	whiteTotalSeconds = 30*60;
	blackTotalSeconds = 30*60;
	$("#whiteTime").html("Gracz Biały: 30:00");
	$("#blackTime").html("Gracz Czarny: 30:00");
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
	if (whoseTurn == "whiteTurn")
	{
		whiteTotalSeconds--;
		$("#whiteTime").html("Gracz Biały: " + secondsToMinutesAndSeconds(whiteTotalSeconds));
	}
	else if (whoseTurn == "blackTurn")
	{
		blackTotalSeconds--;		
		$("#blackTime").html("Gracz Czarny: " + secondsToMinutesAndSeconds(blackTotalSeconds));
	}
	else if (whoseTurn == "noTurn")
		resetPlayersTimers();
	else console.log("ERROR: updatePlayersTime(): unknown turn = " + whoseTurn);
}		

function queueSize(queueList)
{
	if (queueList != "0") 
	{
		var queueListArr = queueList.split(",");
		return queueListArr.length;
	}
	else return 0;
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
			addMsgToClientPlainTextWindow("Pion promowany na: hetman.", "info");
			if ($(this).dialog('isOpen')) $(this).dialog('close');
		}
	},
	buttons: 
	{ //future: mogę się kiedyś pokusić o zrobienie podziału koloru znaków na białe/czarne. białe: U+2655, U+2657, U+2658, U+2656.
		'\u265B': function() 
		{
			websocket.send("promoteTo:q"); //queen
			addMsgToClientPlainTextWindow("Pion promowany na: hetman.", "info");
			if ($(this).dialog('isOpen')) $(this).dialog('close');
		}, 
		'\u265D': function() 
		{
			websocket.send("promoteTo:b"); //bishop
			addMsgToClientPlainTextWindow("Pion promowany na: goniec.", "info");
			if ($(this).dialog('isOpen')) $(this).dialog('close');
		}, 
		'\u265E': function() 
		{
			websocket.send("promoteTo:n"); //knight
			addMsgToClientPlainTextWindow("Pion promowany na: skoczek.", "info");
			if ($(this).dialog('isOpen')) $(this).dialog('close');
		}, 
		'\u265C': function() 
		{
			websocket.send("promoteTo:r"); //rook
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
			$("#perspective").css('z-index', '8'); 
			websocket.send("standUp"); 
			if ($(this).dialog('isOpen')) $(this).dialog('close');
		}, 
		'nie': function() 
		{
			$("#standUpWhite").attr("disabled", false);
			$("#standUpBlack").attr("disabled", false);
			if ($(this).dialog('isOpen')) $(this).dialog('close');
		}
	}
};

function giveUp() 
{
	if (bClientIsPlayer) 
	{
		if (whoseTurn == "whiteTurn" || whoseTurn == "blackTurn")
			$("#giveUpDialog").dialog(giveUpVar).dialog("open"); 
		else
		{
			disableAll();
			websocket.send("standUp"); 
			if ($("#startGameDialog").dialog(startGameVar).dialog('isOpen')) 
				$("#startGameDialog").dialog(startGameVar).dialog('close'); 
		}
	}
}

function clickedBtn(buttonType)
{
	disableAll();
	var msgForCore;
	switch(buttonType)
	{
		case "sitOnWhite": msgForCore = "sitOn White"; break; 
		case "sitOnBlack": msgForCore = "sitOn Black"; break; 
		case "standUp": giveUp(); break;
		case "queueMe": msgForCore = "queueMe"; break; 
		case "leaveQueue": msgForCore = "leaveQueue"; break; 
		default: console.log("ERROR: unknown buttonType type"); break;
	}

	if (msgForCore) websocket.send(msgForCore); 
}

function confirmLogout() 
{	
	var logoutMsg = "Czy na pewno chcesz się wylogować?";
	if (confirm(logoutMsg)) return true;
	else return false;   
}

function info()
{
	$("#info").html('mariusz.pak.89@gmail.com | <a href="index.php?a=logout" onclick="return deleteask();">Wyloguj się</a></center>');
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

var fieldFromClicked = "0";
var fieldToClicked = "0";
var moveFromTo = "";
function clickBoardField(fieldPos)
{
	if (bClientIsPlayer && bPlayerCanSendMove) //additional conditions
	{			
		if (fieldFromClicked == "0") 
		{
			fieldFromClicked = fieldPos;
			//$(fieldPos).css("background-color", "rgba(179,212,252,0.15)");
			$(fieldPos).css("background", "radial-gradient(closest-side, green, transparent");
		}
		else 
		{
			fieldToClicked = fieldPos;
			//$(fieldFromClicked).css("background-color", ""); 
			$(fieldFromClicked).css("background", ""); 
			moveFromTo = fieldFromClicked.id + fieldToClicked.id;
			movePiece(moveFromTo);
			fieldFromClicked = "0";
			fieldToClicked = "0";
			bPlayerCanSendMove = false;
		}
	}
}

function movePiece(fromTo)
{
	var pieceFromLetter = fromTo.charAt(0);
	var pieceFromDigit = fromTo.charAt(1);
	var pieceToLetter = fromTo.charAt(2);
	var pieceToDigit = fromTo.charAt(3);
	
	var squareLetters = ['a','b','c','d','e','f','g','h'];
	
	if (fromTo.length == 4 && pieceFromDigit <= 8 && pieceToDigit <= 8 && pieceFromDigit >= 1 && pieceToDigit >= 1 && 
	jQuery.inArray(pieceFromLetter, squareLetters) != '-1' && jQuery.inArray(pieceToLetter, squareLetters) != '-1')
	{
		disableAll();
		$("#perspective").css('z-index', '8'); 
		websocket.send("move " + fromTo);
	}
	else otherOption('wrongData');
}

alertWindow = (function () //todo: testować działanie tej funkcji
{
	//future: nie umiem zmienić koloru zakładki na migający niebieski
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