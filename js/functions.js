function debugToGameTextArea(message) 
{
	debugTextArea.value += message + "\n";
	debugTextArea.scrollTop = debugTextArea.scrollHeight;
}

function gameInProgress(move, turn)
{
	if (turn == "wt")
	{
		wiadomoscNaTextArea = "Czarny wykonał ruch: " + move + ". Ruch wykonują Białe.";
		debugToGameTextArea(wiadomoscNaTextArea);
	}
	else if (turn == "bt")
	{
		wiadomoscNaTextArea = "Biały wykonał ruch: " + move + ". Ruch wykonują Czarne.";
		debugToGameTextArea(wiadomoscNaTextArea);
	}
	else console.log('ERROR. Unknown turn value = ' + turn);
	
	switchTurn(turn); 
}

function switchTurn(whoseTurn)
{
	document.getElementById("whitePlayer").disabled = true; 
	document.getElementById("blackPlayer").disabled = true; 
	
	if (document.getElementById("whitePlayer").value == js_loginUzytkownika) 
	{
		document.getElementById("standUpWhite").disabled = false; 
		document.getElementById("standUpBlack").disabled = true; 
	}
	else if (document.getElementById("blackPlayer").value = js_loginUzytkownika)
	{
		document.getElementById("standUpWhite").disabled = true; 
		document.getElementById("standUpBlack").disabled = false; 
	}
		
	if (whoseTurn == "wt") //czarny skończył swój ruch
	{ 
		document.getElementById("startGame").disabled == true;
		document.getElementById('openGiveUpDialogButton').disabled = false;
		
		if (document.getElementById("whitePlayer").value == js_loginUzytkownika) 
		{
			document.getElementById('pieceFrom').disabled = false;
			document.getElementById('pieceTo').disabled = false;
			document.getElementById('movePieceButton').disabled = false; //TODO: przycisk do wysyłania ma działać dodatkowo tylko gdy oba powyższe pola są dobrze wypełnione
			//TODO: zezwolenie na core by ruch mógł teraz wykonać tylko biały
			console.log("(white info) Ruch wykonuje teraz: Biały");
		}
		else if (document.getElementById("blackPlayer").value = js_loginUzytkownika) // zmiany w panelu czarnego gracza
		{			
			document.getElementById("startGame").disabled == true;
			document.getElementById('openGiveUpDialogButton').disabled = false;
			
			document.getElementById('pieceFrom').disabled = true;
			document.getElementById('pieceTo').disabled = true;
			document.getElementById('movePieceButton').disabled = true; //TODO: przycisk do wysyłania ma działać dodatkowo tylko gdy oba powyższe pola są dobrze wypełnione
			//TODO: zezwolenie na core by ruch mógł teraz wykonać tylko biały
			console.log("(black info) Ruch wykonuje teraz: Biały");
		}
		else console.log("ERROR: STATEMENT DOESNT MET- NO LOGGED PLAYER AVAILABLE (PLAYERS NICK VALUES ARE EMPTY- SHOULDNT BE POSSIBLE)");
	}
	else if (whoseTurn == 'bt') //biały skończył swój ruch
	{
		document.getElementById("startGame").disabled == true;
		document.getElementById('openGiveUpDialogButton').disabled = false;
	
		if (document.getElementById('whitePlayer').value == js_loginUzytkownika) 
		{			
			document.getElementById('pieceFrom').disabled = true;
			document.getElementById('pieceTo').disabled = true;
			document.getElementById('movePieceButton').disabled = true; //TODO: przycisk do wysyłania ma działać dodatkowo tylko gdy oba powyższe pola są dobrze wypełnione
			//TODO: zezwolenie na core by ruch mógł teraz wykonać tylko czarny
			console.log("(white info) Ruch wykonuje teraz: Czarny");
		}
		else if (document.getElementById("blackPlayer").value == js_loginUzytkownika) 
		{
			document.getElementById('pieceFrom').disabled = false;
			document.getElementById('pieceTo').disabled = false;
			document.getElementById('movePieceButton').disabled = false; //TODO: przycisk do wysyłania ma działać dodatkowo tylko gdy oba powyższe pola są dobrze wypełnione
			//TODO: zezwolenie na core by ruch mógł teraz wykonać tylko czarny
			console.log("(black info) Ruch wykonuje teraz: Czarny");
		}
		else console.log('ERROR: STATEMENT DOESNT MET- NO LOGGED PLAYER AVAILABLE (PLAYERS NICK VALUES ARE EMPTY- SHOULDNT BE POSSIBLE)');
	}
	else if (whoseTurn == 'nt')
	{
		console.log('whoseTurn == nt');	
		document.getElementById('pieceFrom').disabled = true;
		document.getElementById('pieceTo').disabled = true;
		document.getElementById('movePieceButton').disabled = true;
		document.getElementById('openGiveUpDialogButton').disabled = true;
		isStartReady();
	}
	else console.log('ERROR: WRONG whoseTurn VARIABLE');
}

$("#dialog").dialog(
{
	autoOpen: false, 
	dialogClass: "no-close",
	buttons: 
	{
		'hetman': function() 
		{
			websocket.send("promoteTo: q"); //queen
			console.log('sent to core: promoteTo: q');
			$(this).dialog("close");
		}, 
		'goniec': function() 
		{
			websocket.send("promoteTo: b"); //bishop
			console.log('sent to core: promoteTo: b');
			$(this).dialog("close");
		}, 
		'skoczek': function() 
		{
			websocket.send("promoteTo: k"); //knight
			console.log('sent to core: promoteTo: k');
			$(this).dialog("close");
		}, 
		'wieża': function() 
		{
			websocket.send("promoteTo: r"); //rook
			console.log('sent to core: promoteTo: r');
			$(this).dialog("close");
		}
	},
	title: "Promocja",
	position: 
	{
		my: "center",
		at: "center",
		of: window
	}
});

function openDialogPromote()
{
	console.log("show promotion buttons window");
	$("#dialog").dialog('open');
}

function promoteToWhat()
{
	if (document.getElementById("whitePlayer").value == js_loginUzytkownika) //prośbę o promocję dostanie tylko ten gracz który wysyłał ruch- TODO: sprawdzić
	{
		openDialogPromote();
	}
}

function endOfGame(checkmate, endType)
{
	switchTurn("nt");
	
	if (endType == "whiteWon")
	{
		console.log("endOfGame(): whiteWon");
		debugToGameTextArea("Koniec gry: Białe wygrały wykonując ruch: " + checkmate);
	}
	else if( endType == "blackWon")
	{
		console.log("endOfGame(): blackWon");
		debugToGameTextArea("Koniec gry: Czarne wygrały wykonując ruch: " + checkmate);
	}
	else if( endType == "draw")
	{
		console.log("endOfGame(): draw");
		debugToGameTextArea("Koniec gry: Remis");
		// TODO: co dalej?
		// na kurniku obu graczy deklalure remis bodajże
	}
	else console.log("endOfGame(): ERROR: unknown parameter");
	
	isStartReady();
}

$('#giveUpDialog').dialog({
    autoOpen: false,
	buttons: 
	{
		'tak': function() 
		{
			if (whitePlayerName == js_loginUzytkownika)
			{
				debugToGameTextArea("Koniec gry: Biały gracz opóścił stół");
				document.getElementById("standUpWhite").disabled = true;
				change("whitePlayer", "White")
			}
			else if (blackPlayerName == js_loginUzytkownika)
			{
				debugToGameTextArea("Koniec gry: Czarny gracz opóścił stół");
				document.getElementById("standUpBlack").disabled = true;
				change("blackPlayer", "Black") 
			}
			
			document.getElementById("startGame").disabled = true;
			document.getElementById("openGiveUpDialogButton").disabled = true;
			switchTurn('nt');
			resetBoard();

			$(this).dialog("close");
		}, 
		'nie': function() 
		{
			$(this).dialog("close");
		}
	},
	title: 'Opuszczanie stołu',
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