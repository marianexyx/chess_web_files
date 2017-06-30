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
		console.log('new white player name: '+ newWhitePlayerName); 
		
		if (newWhitePlayerName == "White") 
		{ 
			document.getElementById("whitePlayer").value = "White";
			<? 	if(!empty($_SESSION['id'])) 
				{ 
					echo '
					if (document.getElementById("blackPlayer").value != js_loginUzytkownika)
					{
					document.getElementById("whitePlayer").disabled = false; 
					}
					else document.getElementById("whitePlayer").disabled = true; 
					
					if (document.getElementById("blackPlayer").value == "Black")
					{
					document.getElementById("blackPlayer").disabled = true;
					document.getElementById("standUpBlack").disabled = true; 
					}';
				} ?>
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
					{
					console.log("whitePlayer.value == js_loginUzytkownika. Enable black player button");
					document.getElementById("blackPlayer").disabled = false; 
					}
					else document.getElementById("blackPlayer").disabled = true; 
					
					if (document.getElementById("whitePlayer").value == "White") 
					{
					document.getElementById("whitePlayer").disabled = true;
					document.getElementById("standUpWhite").disabled = true; 
					}';
				} ?>
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
		var tempWhite = document.getElementById("whitePlayer").value;
		var tempblack = document.getElementById("blackPlayer").value;
		
		<? if(!empty($_SESSION['id']))
			{ 
				echo '
				if (document.getElementById("whitePlayer").value != "White" && document.getElementById("blackPlayer").value != "Black" &&
				(document.getElementById("whitePlayer").value == js_loginUzytkownika || document.getElementById("blackPlayer").value == js_loginUzytkownika))
				{
				console.log("2 players on chairs. White player name = ", tempWhite, ", black player name =", tempblack);
				debugToGameTextArea("Wciśnij START, aby rozpocząć grę.");
				document.getElementById("startGame").disabled = false; 
				}
				else console.log("Players not on chairs. White player name = ", tempWhite, ", black player name =", tempblack);
				';
			} ?>
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
		if (whatWasChecked.substr(0,5) == 'White') 
		{ 
			var whitePlayerName = whatWasChecked.substr(6);	
			whitePlayerName = whitePlayerName.trim(); //remove whitespaces
			document.getElementById('whitePlayer').value = whitePlayerName; 
			if (document.getElementById("whitePlayer").value != "White") 
			{ 
				document.getElementById('whitePlayer').disabled = true; 
				if (whitePlayerName == js_loginUzytkownika) 
				{
					document.getElementById("standUpWhite").disabled = false; 
					document.getElementById("blackPlayer").disabled = true; 
					document.getElementById("standUpBlack").disabled = true; 
					console.log('white player (!=White) =', whitePlayerName);  
				}
			}
			else if (document.getElementById("whitePlayer").value == "White") 
			{  
				<? if(!empty($_SESSION['id'])) 
					{ 
						echo '
						if (document.getElementById("blackPlayer").value == js_loginUzytkownika) 
						{ 
						document.getElementById("whitePlayer").disabled = true; 
						document.getElementById("standUpWhite").disabled = true; 
						}
						else document.getElementById("whitePlayer").disabled = false; 
						console.log("white player = White");';
					} ?>
			}
		}							
		else if (whatWasChecked.substr(0,5) == 'Black')
		{ 
			var blackPlayerName = whatWasChecked.substr(6);
			blackPlayerName = blackPlayerName.trim(); //remove whitespaces
			document.getElementById('blackPlayer').value = blackPlayerName; 
			if (document.getElementById("blackPlayer").value != "Black") 
			{ 
				document.getElementById('blackPlayer').disabled = true;
				if (blackPlayerName == js_loginUzytkownika) 
				{	
					document.getElementById("standUpBlack").disabled = false; 
					document.getElementById("whitePlayer").disabled = true; 
					document.getElementById("standUpWhite").disabled = true; 
					console.log('black player (!=Czarny) =', blackPlayerName);
				}
				else console.log('blackPlayerName != js_loginUzytkownika. blackPlayerName =', blackPlayerName, ', js_loginUzytkownika =', js_loginUzytkownika);
			}
			else if (document.getElementById("blackPlayer").value == "Black") 
			{  
				<? if(!empty($_SESSION['id'])) 
					{ 
						echo '
						if (document.getElementById("whitePlayer").value == js_loginUzytkownika) 
						{ 
						document.getElementById("blackPlayer").disabled = true; 
						document.getElementById("standUpBlack").disabled = true; 
						}
						else document.getElementById("blackPlayer").disabled = false; 
						console.log("black player = Black");';
					} ?>
			}
		}
		else if (whatWasChecked.substr(0,4) == 'Turn')
		{ 
			var checkedTurn = whatWasChecked.substr(5)
			console.log('checked whoseTurn is = ' + checkedTurn);
			isStartReady();
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
		var textAreaMsg = "Błędne rządanie ruchu: " + coreAnswer + "! Wpisz inny ruch.";
		debugToGameTextArea(textAreaMsg);
		document.getElementById("pieceFrom").disabled = false;
		document.getElementById("pieceTo").disabled = false;
		document.getElementById("movePieceButton").disabled = false;
	}
	
</script> 									