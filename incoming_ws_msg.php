<script> 
	function onMessage(evt)
	{
		console.log('clear msg from core:', evt.data); 
		
		if (evt.data.substr(0,8) == 'newWhite') 		{ newWhite(evt.data.substr(9)); }
		else if (evt.data.substr(0,8) == 'newBlack') 	{ newBlack(evt.data.substr(9)); }
		else if	(evt.data == 'connectionOnline') 		{ connectionOnline(); }
		else if	(evt.data == 'newOk') 					{ newGameStarted(); }
		else if	(evt.data.substr(0,6) == 'moveOk') 		{ moveRespond(evt.data.substr(7)); }
		else if	(evt == 'ready') 						{ coreIsReady(); }
		else if	(evt.data.substr(0,7) == 'checked') 	{ checked(evt.data.substr(7)); }
		else if	(evt.data.substr(0,8) == 'promoted') 	{ promoted(evt.data.substr(9)); }
		else if	(evt.data.substr(0,7) == 'badMove') 	{ badMove(evt.data.substr(8)); }
		else console.log('ERROR. Unknown onMessage value = ' + evt.data);
	}
	
	function newWhite(newWhitePlayerName)
	{
		console.log('newWhitePlayerName: '+ newWhitePlayerName); 
		
		if (newWhitePlayerName == "White") 
		{ 
			document.getElementById("whitePlayer").value = "White";
			<? 	if(!empty($_SESSION['id'])) 
			{ 
				echo '
				if (document.getElementById("blackPlayer").value != js_loginUzytkownika) 
					document.getElementById("whitePlayer").disabled = false; 
				else document.getElementById("whitePlayer").disabled = true; 
				
				if (document.getElementById("blackPlayer").value == "Black") 
					document.getElementById("blackPlayer").disabled = false;';
			} ?>
			if (document.getElementById("blackPlayer").value == "Black") 
				document.getElementById("standUpBlack").disabled = true; 
			document.getElementById("standUpWhite").disabled = true; 
			document.getElementById("startGame").disabled = true; 
			document.getElementById("openGiveUpDialogButton").disabled = true;
			debugToGameTextArea("Biały gracz opóścił stół"); 
			console.log('white player = "White"');
		}
		else 
		{ 
			document.getElementById("whitePlayer").value = newWhitePlayerName;
			whitePlayerName = newWhitePlayerName; 
			document.getElementById("whitePlayer").disabled = true; 
			if (document.getElementById("whitePlayer").value == js_loginUzytkownika)
			{
				document.getElementById("blackPlayer").disabled = true; 
				document.getElementById("standUpWhite").disabled = false; 
			}
			debugToGameTextArea("Gracz figur białych: "+ newWhitePlayerName);
			console.log('white player = ', newWhitePlayerName);
			isStartReady();
		}
	}
	
	function newBlack(newBlackPlayerName)
	{
		console.log('newWhitePlayerName: '+ newBlackPlayerName);
		
		if (newBlackPlayerName == "Black")
		{ 
			document.getElementById("blackPlayer").value = "Black";
			<? 	if(!empty($_SESSION['id']))
			{ 
				echo '
				if (document.getElementById("whitePlayer").value != js_loginUzytkownika) 
					document.getElementById("blackPlayer").disabled = false; 
				else document.getElementById("blackPlayer").disabled = true; 
				
				if (document.getElementById("whitePlayer").value == "White") 
					document.getElementById("whitePlayer").disabled = false;';
			} ?>
			if (document.getElementById("whitePlayer").value == "White") 
				document.getElementById("standUpWhite").disabled = true; 
			document.getElementById("standUpBlack").disabled = true; 
			document.getElementById("startGame").disabled = true; 
			document.getElementById("openGiveUpDialogButton").disabled = true;
			debugToGameTextArea("Czarny gracz opóścił stół");
			console.log('black player = "Black"');
		}
		else 
		{ 
			document.getElementById("blackPlayer").value = newBlackPlayerName; 
			blackPlayerName = newBlackPlayerName; 
			document.getElementById("blackPlayer").disabled = true; 
			if (document.getElementById("blackPlayer").value == js_loginUzytkownika)
			{
				document.getElementById("whitePlayer").disabled = true;
				document.getElementById("standUpBlack").disabled = false; 
			}
			debugToGameTextArea("Gracz figur czarnych: "+ newBlackPlayerName);
			console.log('black player = ', newBlackPlayerName);
			isStartReady();
		}
	}
	
	function isStartReady()
	{
		if (document.getElementById("whitePlayer").value != "White" && document.getElementById("blackPlayer").value != "Black")
		{
			debugToGameTextArea("Wciśnij START, aby rozpocząć grę.");
			document.getElementById("startGame").disabled = false; 
		}
	}
	
	function connectionOnline()
	{
		console.log("connection with weboscket server maintained");
	}
	
	function newGameStarted()
	{
		document.getElementById("startGame").disabled = true;
		document.getElementById("openGiveUpDialogButton").disabled = false;
		if (document.getElementById("whitePlayer").value != "White" && document.getElementById("blackPlayer").value != "Black")
		{
			debugToGameTextArea("Nowa gra rozpoczęta. Białe wykonują ruch.");
			switchTurn("wt");
		}
		else 
		{
			debugToGameTextArea("Oczekiwanie na graczy.");
			switchTurn("nt");
		}
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
	
	function coreIsReady()
	{
		debugToGameTextArea("Wciśnij START, aby rozpocząć grę.");
		document.getElementById("startGame").disabled = false;
	}
	
	function checked(whatWasChecked)
	{
		if (whatWasChecked.substr(0,5) == 'White') //jeżeli sprawdzana wartośc w core to gracz biały...
		{ 
			var whitePlayerName = whatWasChecked.substr(6);	
			whitePlayerName = whitePlayerName.trim(); //remove whitespaces
			document.getElementById('whitePlayer').value = whitePlayerName; //...to nazwa białego jest tym kto siedzi na białym wg core...
			if (document.getElementById("whitePlayer").value != "White") //...i jeżeli na białym jest jakiś gracz...
			{ 
				document.getElementById('whitePlayer').disabled = true; //...to nikt nie może usiąść na białym...
				if (whitePlayerName == js_loginUzytkownika) //...i jeżeli sprawdzany gracz w core to gracz będący zalogowanym...
				{
					document.getElementById("standUpWhite").disabled = false; //...to przycisk do wstawania jest aktywny.
					console.log('white player (!=White) =', whitePlayerName);  
				}
			}
			else if (document.getElementById("whitePlayer").value == "White") //jednak jeżeli nikt nie siedzi na białym...
			{  
				<? if(!empty($_SESSION['id'])) //...i klient jest zalogowany na stronie...
					{ 
						echo 'document.getElementById("whitePlayer").disabled = false; //...to mozna usiąść na białym...
						document.getElementById("standUpWhite").disabled = true; //...a guzik wstawania jest wyłączony.
						console.log("white player = White");';
					} ?>
			}
		}							
		else if (whatWasChecked.substr(0,5) == 'Black')//jeżeli sprawdzana wartość w core to gracz czarny...
		{ 
			var blackPlayerName = whatWasChecked.substr(6);
			blackPlayerName = blackPlayerName.trim(); //remove whitespaces
			document.getElementById('blackPlayer').value = blackPlayerName; //...to nazwa czarnego jest tym kto siedzi na czarnym wg core...
			if (document.getElementById("blackPlayer").value != "Black") //...i jeżeli na czarnym jest jakiś gracz...
			{ 
				document.getElementById('blackPlayer').disabled = true; //...to nikt nie może usiąść na czarnym...
				if (blackPlayerName == js_loginUzytkownika) //...i jeżeli sprawdzany gracz w core to gracz będący zalogowanym...
				{	
					document.getElementById("standUpBlack").disabled = false; //...to przycisk do wstawania jest aktywny.
					console.log('black player (!=Czarny) =', blackPlayerName);
				}
			}
			else if (document.getElementById("blackPlayer").value == "Black") //jednak jeżeli nikt nie siedzi na czarnym...
			{  
				<? if(!empty($_SESSION['id'])) //i klient jest zalogowany na stronie...
					{ 
						echo 'document.getElementById("blackPlayer").disabled = false; //to mozna usiąść na czarnym...
						document.getElementById("standUpBlack").disabled = true; //...a guzik wstawania jest wyłączony.
						console.log("black player = Black");';
					} ?>
			}
		}
		else if (whatWasChecked.substr(0,4) == 'Turn')
		{ 
			//TODO: czy to się przyda?
			var checkedTurn = whatWasChecked.substr(5)
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
	
	function badMove(coreAnswer) //TODO: only for sender
	{
		console.log('badMove: ' + coreAnswer);
		var textAreaMsg = "Błędne rządanie ruchu: " + coreAnswer + "! Wpisz inny ruch.";
		debugToGameTextArea(textAreaMsg);
	}
	
</script> 									