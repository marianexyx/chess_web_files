<script> 
	function onMessage(evt)
	{
		console.log('clear msg from core:', evt.data); 
		
		if (evt.data.substr(0,8) == 'newWhite') 		{ newWhite(evt.data.substr(9)); }
		else if (evt.data.substr(0,8) == 'newBlack') 	{ newBlack(evt.data.substr(9)); }
		else if	(evt.data == 'connectionOnline') 		{ connectionOnline(); }
		else if	(evt.data == 'newOk') 					{ newGameStarted(); }
		else if	(evt.data.substr(0,6) == 'moveOk') 		{ moveRespond(evt.data.substr(7)); }
		else if (evt.data == 'reseting')				{ reseting(); }
		else if	(evt.data == 'ready') 					{ coreIsReady(); }
		else if	(evt.data.substr(0,7) == 'checked') 	{ checked(evt.data.substr(7)); }
		else if	(evt.data.substr(0,8) == 'promoted') 	{ promoted(evt.data.substr(9)); }
		else if	(evt.data.substr(0,7) == 'badMove') 	{ badMove(evt.data.substr(8)); }
		else console.log('ERROR. Unknown onMessage value = ' + evt.data);
	}
	
	var whitePlayerName = "White";
	var blackPlayerName = "Black";
	
	function newWhite(newWhitePlayerName)
	{
		console.log('new white player name: '+ newWhitePlayerName); 
		
		if (newWhitePlayerName == "White") 
		{ 
			document.getElementById("whitePlayer").value = "White";
			enabling('whiteEmpty');
			console.log('white player = "White"');
		}
		else 
		{ 
			document.getElementById("whitePlayer").value = newWhitePlayerName;
			whitePlayerName = newWhitePlayerName; 
			debugToGameTextArea("Gracz figur białych: "+ newWhitePlayerName);
			enabling('newWhite');
			console.log('white player = ', newWhitePlayerName);
		}
	}
	
	function newBlack(newBlackPlayerName)
	{
		console.log('newWhitePlayerName: '+ newBlackPlayerName);
		
		if (newBlackPlayerName == "Black")
		{ 
			document.getElementById("blackPlayer").value = "Black";
			enabling('blackEmpty');
			console.log('black player = "Black"');
		}
		else 
		{ 
			document.getElementById("blackPlayer").value = newBlackPlayerName; 
			blackPlayerName = newBlackPlayerName; 
			debugToGameTextArea("Gracz figur czarnych: "+ newBlackPlayerName);
			enabling('newBlack');
			console.log('black player = ', newBlackPlayerName);
		}
	}
	
	function connectionOnline()
	{
		console.log("connection with weboscket server maintained");
	}
	
	function newGameStarted()
	{
		if (document.getElementById("whitePlayer").value != "White" && document.getElementById("blackPlayer").value != "Black")
		{
			debugToGameTextArea("Nowa gra rozpoczęta. Białe wykonują ruch.");
			enabling('newGame', whoseTrun = 'wt')
		}
		else console.log("ERROR: game started when players aren't on chairs");
	}
	
	function moveRespond(coreAnswer)
	{
		var moveOk = coreAnswer.substr(0,4);
		var whoseTurn = coreAnswer.substr(5,2);
		var gameStatus = coreAnswer.substr(8);
		console.log("moveOk = " + moveOk + ", whoseTurn = " + whoseTurn + ", gameStatus = " + gameStatus);
		
		if (gameStatus == "continue") gameInProgress(moveOk, whoseTurn);
		else if (gameStatus == "promote") promoteToWhat();
		else if (gameStatus == "whiteWon" || gameStatus == "blackWon" || gameStatus == "draw") endOfGame(moveOk, gameStatus);
		else console.log("ERROR: moveRespond(): unknown gameStatus value = " + gameStatus);
	}
	
	function reseting()
	{
		debugToGameTextArea("Koniec gry: Gracz opuścił stół. Resetownie planszy...");
	}
	
	function coreIsReady()
	{
		enabling('resetComplited');
	}
	
	function checked(whatWasChecked)
	{
		if (whatWasChecked.substr(0,5) == 'White') 
		{ 
			var whitePlayerName = whatWasChecked.substr(6);	
			whitePlayerName = whitePlayerName.trim(); //remove whitespaces
			document.getElementById('whitePlayer').value = whitePlayerName; 
			enabling('newWhite');
		}							
		else if (whatWasChecked.substr(0,5) == 'Black')
		{ 
			var blackPlayerName = whatWasChecked.substr(6);
			blackPlayerName = blackPlayerName.trim(); //remove whitespaces
			document.getElementById('blackPlayer').value = blackPlayerName;
			enabling('newBlack');			
		}
		else if (whatWasChecked.substr(0,4) == 'Turn')
		{ 
			var checkedTurn = whatWasChecked.substr(5);
			if (checkedTurn != 'nt') enabling('gameInProgress', checkedTurn);
			else enabling('endOfGame');
			console.log('checked whoseTurn is = ' + checkedTurn);
		}
		else if (whatWasChecked.substr(0,9) == 'TableData')
		{
			var tableData = whatWasChecked.split(" ");
			
			var whitePlayerName = tableData[1];	
			whitePlayerName = whitePlayerName.trim(); //remove whitespaces
			document.getElementById('whitePlayer').value = whitePlayerName; 
			
			var blackPlayerName = tableData[2];
			blackPlayerName = blackPlayerName.trim(); //remove whitespaces
			document.getElementById('blackPlayer').value = blackPlayerName;
			
			var checkedTurn = tableData[3];
			if (checkedTurn != 'nt') enabling('gameInProgress', checkedTurn);
			else enabling('endOfGame');
			console.log('checked whoseTurn is = ' + checkedTurn);
		}
		else console.log('unknown checked function parameter = ' + whatWasChecked);
	}
	
	function promoted(promotedTo)
	{
		var promotingMove = promotedTo.substr(0,4);	
		var promotePiece = promotedTo.substr(5,1);
		var promoteType;
		switch(promotePiece)
		{
			case q: promoteType = "hetmana"; break
			case r: promoteType = "wieżę"; break
			case b: promoteType = "gońca"; break
			case k: promoteType = "skoczka"; break	
		}
		
		var promoteTurn = promotedTo.substr(7,2);
		var gameStateAfterPromotion = promotedTo.substr(10);
		var gameState;
		switch(gameStateAfterPromotion)
		{
			case 'continue': gameState = "Ruch wykonuje " + (promoteTurn == 'bt' ?  "Biały." : "Czarny."); break;
			case 'whiteWon': gameState = "Koniec gry. Wygrał Biały."; break;
			case 'blackWon': gameState = "Koniec gry. Wygrał Czarny."; break;
			case 'draw': gameState = "Koniec gry. Remis."; break;
			default: console.log('ERROR. promoted(): Unknown gameStateAfterPromotion var = ' + gameState); break;
		}
		
		debugToGameTextArea((promoteTurn == 'bt' ?  "Biały." : "Czarny.") + " wykonał promocję piona ruchem " + 
		promotingMove + " na " + promoteType + ". " + gameState);
	}
	
	function badMove(coreAnswer)
	{
		console.log('badMove: ' + coreAnswer);
		var textAreaMsg = "Błędne rządanie ruchu: " + coreAnswer.substr(0,4); + "! Wpisz inny ruch.";
		debugToGameTextArea(textAreaMsg);
		enabling('badMove', coreAnswer.substr(5));
	}
	
</script> 									