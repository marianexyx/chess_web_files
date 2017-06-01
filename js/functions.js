/*var whose_turn; 
var whitePlayerName;
var blackPlayerName;
var js_loginUzytkownika */ //TODO: sprawdzić czy gdzieś tego nie powtarzam

function gameInProgress(move, turn, status)
{
	if (turn == "wt")
	{
		wiadomoscNaTextArea = "Czarny wykonał ruch: " + movement_made + ". Ruch wykonują Białe.";
		debugToGameTextArea(wiadomoscNaTextArea);
	}
	else if (turn == "bt")
	{
		wiadomoscNaTextArea = "Biały wykonał ruch: " + movement_made + ". Ruch wykonują Czarne.";
		debugToGameTextArea(wiadomoscNaTextArea);
	}
	else console.log('ERROR. Unknown turn value = ' + turn);
	
	switchTurn(turn); 
}

function switchTurn(whoseTurn)
{
	if (whoseTurn == "wt")
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
	else if (whose_turn == 'bt') //jeżeli teraz przypada tura białego
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
	else if (whose_turn == 'nt')
	{
		console.log('End of game. No turn available. Waiting for new game...');	
		document.getElementById('pieceFrom').disabled = true;
		document.getElementById('pieceTo').disabled = true;
		document.getElementById('movePieceButton').disabled = true;
	}
	else console.log('ERROR: WRONG whoseTurn VARIABLE');
}

function promoteToWhat()
{
	if (document.getElementById("whitePlayer").value == js_loginUzytkownika /*&& whose_turn == "whiteTurn" //TODO: sprawdzać gracza po sockecie WS */)
	{
		console.log("show promotion buttons window");
		promotion();
	}
	else if (document.getElementById("blackPlayer").value == js_loginUzytkownika /*&& whose_turn == "black_turn" //TODO: sprawdzać gracza po sockecie WS */)
	{
		console.log("show promotion buttons window");
		promotion();
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