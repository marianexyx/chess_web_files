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
		
		if (newWhitePlayerName == "White") { //jak nikt ju� nie jest zalogowany na bia�ym, tj. gracz kt�ry by� na bia�ym powsta�
			document.getElementById("whitePlayer").value = "White"; //to bia�e zostaje "Bia�y"m
			<? 	if(!empty($_SESSION['id'])){ //tylko dla zalogowanych
				echo 'document.getElementById("whitePlayer").disabled = false; //da si� usi��� na bia�ym
				if (document.getElementById("blackPlayer").value == "Black") //dodatkowo jak czarny to "Czarne" (tj. nikt)
				document.getElementById("blackPlayer").disabled = false; //to czarny te� da si� wcisn�� i usi���';
			} ?>
			document.getElementById("standUpBlack").disabled = true; //na czarnym te� nie, ale to jest tylko zabezpieczenie
			document.getElementById("standUpWhite").disabled = true; //i jak nikt nie siedzi na bia�ym to nikt nie mo�e wsta� na bia�ym
			document.getElementById("startGame").disabled = true; //i je�eli jakim� cudem da�oby si� wcisn�� "start", no to si� nie da
			debugToGameTextArea("Bia�y gracz op�ci� st�"); 
			console.log('white player = "White"');
		}
		else { //je�eli kto� siada na bia�ych, tj. przes�any by� jego nick 
			document.getElementById("whitePlayer").value = newWhitePlayerName; //warto�� buttonu zmienia si� na jego nick
			whitePlayerName = newWhitePlayerName; //zapami�taj nazw� bia�ego
			document.getElementById("whitePlayer").disabled = true; //nie da si� usi��� na bia�ym
			if (document.getElementById("whitePlayer").value == js_loginUzytkownika){ //je�eli gracz bia�y jest zalogowanym
				document.getElementById("blackPlayer").disabled = true; //to nie mo�e on usi��� na czarnych 
				document.getElementById("standUpWhite").disabled = false; //tylko ten co siedzi mo�e wsta�
			}
			debugToGameTextArea("Gracz figur bia�ych: "+ newWhitePlayerName);
			console.log('white player = ', newWhitePlayerName);
			if (document.getElementById("whitePlayer").value != "White" && document.getElementById("blackPlayer").value != "Black") //je�eli bia�y i czarny siedz� na stole
			document.getElementById("startGame").disabled = false; //to da si� wcisn�� start
		}
	}
	
	function newBlack(newBlackPlayerName)
	{
		console.log('newWhitePlayerName: '+ newBlackPlayerName);
		
		if (newBlackPlayerName == "Black"){ //jak nikt nie zalogowany na czarnym
			document.getElementById("blackPlayer").value = "Black";
			<? 	if(!empty($_SESSION['id'])){ //tylko dla zalogowanych
				echo 'document.getElementById("blackPlayer").disabled = false; //da si� usi��� na czarnym
				if (document.getElementById("whitePlayer").value == "White") //dodatkowo jak bia�y to "Bia�y" (tj. nikt)
				document.getElementById("whitePlayer").disabled = false; //to bia�y te� da si� wcisn�� i usi���';
			} ?>
			document.getElementById("standUpWhite").disabled = true; //i jak nikt nie siedzi na bia�ym to nikt nie mo�e wsta� na bia�ym
			document.getElementById("standUpBlack").disabled = true; //nie da si� wsta� jak nikt nie siedzi
			document.getElementById("startGame").disabled = true; //to jest tylko zabezpieczenie
			debugToGameTextArea("Czarny gracz op�ci� st�");
			console.log('black player = "Black"');
		}
		else { //je�eli kto� siada na czarnych, tj. przes�any by� jego nick 
			document.getElementById("blackPlayer").value = newBlackPlayerName; //warto�� buttonu czarnego zmienia si� na jego nick
			blackPlayerName = newBlackPlayerName; //zapami�taj nazw� czarnego
			document.getElementById("blackPlayer").disabled = true; //nikt nie mo�e usi��� na czarnym jak tam w�a�nie siad� gracz
			if (document.getElementById("blackPlayer").value == js_loginUzytkownika){ //je�eli gracz czarny jest zalogowanym
				document.getElementById("whitePlayer").disabled = true; //to nie mo�e on usi��� jednocze�nie na bia�ych
				document.getElementById("standUpBlack").disabled = false; //tylko ten co siedzi mo�e wsta� 
			}
			debugToGameTextArea("Gracz figur czarnych: "+ newBlackPlayerName);
			console.log('black player = ', newBlackPlayerName);
			if (document.getElementById("whitePlayer").value != "White" && document.getElementById("blackPlayer").value != "Black") //je�eli bia�y i czarny siedz� na stole
			document.getElementById("startGame").disabled = false; //to da si� wcisn�� start
		}
	}
	
	function connectionOnline()
	{
		console.log("connection with weboscket server maintained");
	}
	
	function newGameStarted()
	{
		debugToGameTextArea("Nowa gra rozpocz�ta. Bia�e wykonuj� ruch.");
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
		debugToGameTextArea("Wci�nij START, aby rozpocz�� gr�.");
		document.getElementById("startGame").disabled = false;
	}
	
	function checked(whatWasChecked)
	{
		if (whatWasChecked.substr(0,5) == 'White') //je�eli sprawdzana warto�c w core to gracz bia�y...
		{ 
			var whitePlayerName = whatWasChecked.substr(6);	
			whitePlayerName = whitePlayerName.trim(); //remove whitespaces
			document.getElementById('whitePlayer').value = whitePlayerName; //...to nazwa bia�ego jest tym kto siedzi na bia�ym wg core...
			if (document.getElementById("whitePlayer").value != "White") //...i je�eli na bia�ym jest jaki� gracz...
			{ 
				document.getElementById('whitePlayer').disabled = true; //...to nikt nie mo�e usi��� na bia�ym...
				if(whitePlayerName == js_loginUzytkownika) //...i je�eli sprawdzany gracz w core to gracz b�d�cy zalogowanym...
				document.getElementById("standUpWhite").disabled = false; //...to przycisk do wstawania jest aktywny.
				console.log('white player (!=White) =', whitePlayerName);  
			}
			else if (document.getElementById("whitePlayer").value == "White") //jednak je�eli nikt nie siedzi na bia�ym...
			{  
				<? if(!empty($_SESSION['id'])) //...i klient jest zalogowany na stronie...
					{ 
						echo 'document.getElementById("whitePlayer").disabled = false; //...to mozna usi��� na bia�ym...
						document.getElementById("standUpWhite").disabled = true; //...a guzik wstawania jest wy��czony.
						console.log("white player = White");';
					} ?>
			}
		}							
		else if (whatWasChecked.substr(0,5) == 'Black')//je�eli sprawdzana warto�� w core to gracz czarny...
		{ 
			var blackPlayerName = whatWasChecked.substr(6);
			blackPlayerName = blackPlayerName.trim(); //remove whitespaces
			document.getElementById('blackPlayer').value = blackPlayerName; //...to nazwa czarnego jest tym kto siedzi na czarnym wg core...
			if (document.getElementById("blackPlayer").value != "Black") //...i je�eli na czarnym jest jaki� gracz...
			{ 
				document.getElementById('blackPlayer').disabled = true; //...to nikt nie mo�e usi��� na czarnym...
				if(blackPlayerName == js_loginUzytkownika) //...i je�eli sprawdzany gracz w core to gracz b�d�cy zalogowanym...
				document.getElementById("standUpBlack").disabled = false; //...to przycisk do wstawania jest aktywny.
				console.log('black player (!=Czarny) =', blackPlayerName);  
			}
			else if (document.getElementById("blackPlayer").value == "Black") //jednak je�eli nikt nie siedzi na czarnym...
			{  
				<? if(!empty($_SESSION['id'])) //i klient jest zalogowany na stronie...
					{ 
						echo 'document.getElementById("blackPlayer").disabled = false; //to mozna usi��� na czarnym...
						document.getElementById("standUpBlack").disabled = true; //...a guzik wstawania jest wy��czony.
						console.log("black player = Black");';
					} ?>
			}
		}
		else if (whatWasChecked.substr(0,4) == 'Turn')
		{ 
			//TODO: czy to si� przyda?
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
			case r: promoteType = "wie��"; break
			case b: promoteType = "go�ca"; break
			case k: promoteType = "skoczka"; break	
		}
		
		var promoteTurn = promotedTo.substr(7,2);
		var gameStateAfterPromotion = promotedTo.substr(10);
		var gameState;
		switch(gameStateAfterPromotion)
		{
			case 'continue': gameState = "Ruch wykonuje " + (promoteTurn == 'bt' ?  "Bia�y." : "Czarny."); break;
			case 'whiteWon': gameState = "Koniec gry. Wygra� Bia�y."; break;
			case 'blackWon': gameState = "Koniec gry. Wygra� Czarny."; break;
			case 'draw': gameState = "Koniec gry. Remis."; break;
			default: console.log('ERROR. promoted(): Unknown gameStateAfterPromotion var = ' + gameState); break;
		}
		
		debugToGameTextArea((promoteTurn == 'bt' ?  "Bia�y." : "Czarny.") + " wykona� promocj� piona ruchem " + 
		promotingMove + " na " + promoteType + ". " + gameState);
	}
	
	function badMove(coreAnswer) //TODO: only for sender
	{
		console.log('badMove: ' + coreAnswer);
		var textAreaMsg = "B��dne rz�danie ruchu: " + coreAnswer + "! Wpisz inny ruch.";
		debugToGameTextArea(textAreaMsg);
	}
	
</script> 									