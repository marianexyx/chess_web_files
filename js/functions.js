function debugToGameTextArea(message) 
{
	debugTextArea.value += message + "\n";
	debugTextArea.scrollTop = debugTextArea.scrollHeight;
}

function gameInProgress(move, turn)
{
	if (turn == "bt")
	{
		wiadomoscNaTextArea = "Czarny wykonał ruch: " + move + ". Ruch wykonują Białe.";
		debugToGameTextArea(wiadomoscNaTextArea);
	}
	else if (turn == "wt")
	{
		wiadomoscNaTextArea = "Biały wykonał ruch: " + move + ". Ruch wykonują Czarne.";
		debugToGameTextArea(wiadomoscNaTextArea);
	}
	else console.log('ERROR. Unknown turn value = ' + turn);
	
	switchTurn(turn); 
}

function switchTurn(whoseTurn)
{
	if (whoseTurn == "bt") //czarny skończył swój ruch
	{ 
		//console.log("White player turn. Waiting for move...");
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
			document.getElementById('pieceFrom').disabled = true;
			document.getElementById('pieceTo').disabled = true;
			document.getElementById('movePieceButton').disabled = true; //TODO: przycisk do wysyłania ma działać dodatkowo tylko gdy oba powyższe pola są dobrze wypełnione
			//TODO: zezwolenie na core by ruch mógł teraz wykonać tylko biały
			console.log("(black info) Ruch wykonuje teraz: Biały");
		}
		else console.log("ERROR: STATEMENT DOESNT MET- NO LOGGED PLAYER AVAILABLE (PLAYERS NICK VALUES ARE EMPTY- SHOULDNT BE POSSIBLE)");
	}
	else if (whoseTurn == 'wt') //biały skończył swój ruch
	{
		//console.log('Black player turn. Waiting for move...');
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
		console.log('End of game. No turn available. Waiting for new game...');	
		document.getElementById('pieceFrom').disabled = true;
		document.getElementById('pieceTo').disabled = true;
		document.getElementById('movePieceButton').disabled = true;
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

function endOfGame(endType)
{
	if (endType == "whiteWon")
	{
		switchTurn("wt");
		debugToGameTextArea("Koniec gry: Białe wygrały");
	}
	else if( endType == "black_won")
	{
		switchTurn("bt");
		debugToGameTextArea("Koniec gry: Czarne wygrały");
	}
	else if( endType == "draw")
	{
		// TODO: co dalej?
		debugToGameTextArea("Koniec gry: Remis");
	}
}