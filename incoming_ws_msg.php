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
		
		if (newWhitePlayerName == "White") { //jak nikt już nie jest zalogowany na białym, tj. gracz który był na białym powstał
			document.getElementById("whitePlayer").value = "White"; //to białe zostaje "Biały"m
			<? 	if(!empty($_SESSION['id'])){ //tylko dla zalogowanych
				echo 'document.getElementById("whitePlayer").disabled = false; //da się usiąść na białym
				if (document.getElementById("blackPlayer").value == "Black") //dodatkowo jak czarny to "Czarne" (tj. nikt)
				document.getElementById("blackPlayer").disabled = false; //to czarny też da się wcisnąć i usiąść';
			} ?>
			document.getElementById("standUpBlack").disabled = true; //na czarnym też nie, ale to jest tylko zabezpieczenie
			document.getElementById("standUpWhite").disabled = true; //i jak nikt nie siedzi na białym to nikt nie może wstać na białym
			document.getElementById("startGame").disabled = true; //i jeżeli jakimś cudem dałoby się wcisnąć "start", no to się nie da
			debugToGameTextArea("Biały gracz opóścił stół"); 
			console.log('white player = "White"');
		}
		else { //jeżeli ktoś siada na białych, tj. przesłany był jego nick 
			document.getElementById("whitePlayer").value = newWhitePlayerName; //wartość buttonu zmienia się na jego nick
			whitePlayerName = newWhitePlayerName; //zapamiętaj nazwę białego
			document.getElementById("whitePlayer").disabled = true; //nie da się usiąść na białym
			if (document.getElementById("whitePlayer").value == js_loginUzytkownika){ //jeżeli gracz biały jest zalogowanym
				document.getElementById("blackPlayer").disabled = true; //to nie może on usiąść na czarnych 
				document.getElementById("standUpWhite").disabled = false; //tylko ten co siedzi może wstać
			}
			debugToGameTextArea("Gracz figur białych: "+ newWhitePlayerName);
			console.log('white player = ', newWhitePlayerName);
			if (document.getElementById("whitePlayer").value != "White" && document.getElementById("blackPlayer").value != "Black") //jeżeli biały i czarny siedzą na stole
			document.getElementById("startGame").disabled = false; //to da się wcisnąć start
		}
	}
	
	function newBlack(newBlackPlayerName)
	{
		console.log('newWhitePlayerName: '+ newBlackPlayerName);
		
		if (newBlackPlayerName == "Black"){ //jak nikt nie zalogowany na czarnym
			document.getElementById("blackPlayer").value = "Black";
			<? 	if(!empty($_SESSION['id'])){ //tylko dla zalogowanych
				echo 'document.getElementById("blackPlayer").disabled = false; //da się usiąść na czarnym
				if (document.getElementById("whitePlayer").value == "White") //dodatkowo jak biały to "Biały" (tj. nikt)
				document.getElementById("whitePlayer").disabled = false; //to biały też da się wcisnąć i usiąść';
			} ?>
			document.getElementById("standUpWhite").disabled = true; //i jak nikt nie siedzi na białym to nikt nie może wstać na białym
			document.getElementById("standUpBlack").disabled = true; //nie da się wstać jak nikt nie siedzi
			document.getElementById("startGame").disabled = true; //to jest tylko zabezpieczenie
			debugToGameTextArea("Czarny gracz opóścił stół");
			console.log('black player = "Black"');
		}
		else { //jeżeli ktoś siada na czarnych, tj. przesłany był jego nick 
			document.getElementById("blackPlayer").value = newBlackPlayerName; //wartość buttonu czarnego zmienia się na jego nick
			blackPlayerName = newBlackPlayerName; //zapamiętaj nazwę czarnego
			document.getElementById("blackPlayer").disabled = true; //nikt nie może usiąść na czarnym jak tam właśnie siadł gracz
			if (document.getElementById("blackPlayer").value == js_loginUzytkownika){ //jeżeli gracz czarny jest zalogowanym
				document.getElementById("whitePlayer").disabled = true; //to nie może on usiąść jednocześnie na białych
				document.getElementById("standUpBlack").disabled = false; //tylko ten co siedzi może wstać 
			}
			debugToGameTextArea("Gracz figur czarnych: "+ newBlackPlayerName);
			console.log('black player = ', newBlackPlayerName);
			if (document.getElementById("whitePlayer").value != "White" && document.getElementById("blackPlayer").value != "Black") //jeżeli biały i czarny siedzą na stole
			document.getElementById("startGame").disabled = false; //to da się wcisnąć start
		}
	}
	
	function connectionOnline()
	{
		console.log("connection with weboscket server maintained");
	}
	
	function newGameStarted()
	{
		debugToGameTextArea("Nowa gra rozpoczęta. Białe wykonują ruch.");
		document.getElementById("startGame").disabled = true;
		switchTurn("bt");
	}
	
	function moveRespond(coreAnswer)
	{
		var moveOk = coreAnswer.substr(0,4);
		var whoseTurn = coreAnswer.substr(5,2);
		var gameStatus = coreAnswer.substr(8);
		console.log("moveOk = " + moveOk + ", whoseTurn = " + whoseTurn + ", gameStatus = " + gameStatus);
		
		if (gameStatus == "continue") gameInProgress(moveOk, whoseTurn);
		else if (gameStatus == "promote") promoteToWhat();
		else if (gameStatus == "whiteWon" || gameStatus == "blackWon" || gameStatus == "draw") endOfGame(gameStatus);
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
				if(whitePlayerName == js_loginUzytkownika) //...i jeżeli sprawdzany gracz w core to gracz będący zalogowanym...
				document.getElementById("standUpWhite").disabled = false; //...to przycisk do wstawania jest aktywny.
				console.log('white player (!=White) =', whitePlayerName);  
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
				if(blackPlayerName == js_loginUzytkownika) //...i jeżeli sprawdzany gracz w core to gracz będący zalogowanym...
				document.getElementById("standUpBlack").disabled = false; //...to przycisk do wstawania jest aktywny.
				console.log('black player (!=Czarny) =', blackPlayerName);  
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