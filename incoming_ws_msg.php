<script> 
	function onMessage(evt)
	{
		var what_is_checked;
		var checked_value;
		console.log('clear msg from core: ', evt.data); 
		
		if (evt.data.substr(0,8) == 'newWhite') 		{ newWhite(evt.data.substr(9)); }
		else if (evt.data.substr(0,8) == 'newBlack') 	{ newBlack(evt.data.substr(9)); }
		else if	 evt.data == 'connectionOnline') 		{ connectionOnline(); }
		else if	 evt.data == 'newGame') 				{ newGameStarted(); }
		else if	 evt.data.substr(0,6) == 'moveOk') 		{ moveRespond(evt.data.substr(7)); }
		else if	 evt == 'ready') 						{ coreIsReady(); }
		else if	 evt.data.substr(0,7) == 'checked') 	{ checked(evt.data.substr(8)); }
		else if	 evt.data.substr(0,8) == 'promoted') 	{ promoted(evt.data.substr(9)); }
		else if	 evt.data.substr(0,7) == 'badMove') 	{ badMove(evt.data.substr(8)); }
		else console.log('ERROR. Unknown onMessage value = ' + evt);
	}
	
	function newWhite(newWhitePlayerName)
	{
		console.log('newWhitePlayerName: '+ newWhitePlayerName); 
		
		if (newWhitePlayerName == "Bia³y") { //jak nikt ju¿ nie jest zalogowany na bia³ym, tj. gracz który by³ na bia³ym powsta³
			document.getElementById("whitePlayer").value = "Bia³y"; //to bia³e zostaje "Bia³y"m
			<? 	if(!empty($_SESSION['id'])){ //tylko dla zalogowanych
				echo 'document.getElementById("whitePlayer").disabled = false; //da siê usi¹œæ na bia³ym
				if (document.getElementById("blackPlayer").value == "Czarny") //dodatkowo jak czarny to "Czarne" (tj. nikt)
				document.getElementById("blackPlayer").disabled = false; //to czarny te¿ da siê wcisn¹æ i usi¹œæ';
			} ?>
			document.getElementById("standUpBlack").disabled = true; //na czarnym te¿ nie, ale to jest tylko zabezpieczenie
			document.getElementById("standUpWhite").disabled = true; //i jak nikt nie siedzi na bia³ym to nikt nie mo¿e wstaæ na bia³ym
			document.getElementById("startGame").disabled = true; //i je¿eli jakimœ cudem da³oby siê wcisn¹æ "start", no to siê nie da
			debugToGameTextArea("Bia³y gracz opóœci³ stó³"); 
			console.log('Bia³y gracz = "Bia³y"');
		}
		else { //je¿eli ktoœ siada na bia³ych, tj. przes³any by³ jego nick 
			document.getElementById("whitePlayer").value = newWhitePlayerName; //wartoœæ buttonu zmienia siê na jego nick
			whitePlayerName = newWhitePlayerName; //zapamiêtaj nazwê bia³ego
			document.getElementById("whitePlayer").disabled = true; //nie da siê usi¹œæ na bia³ym
			if (document.getElementById("whitePlayer").value == js_loginUzytkownika){ //je¿eli gracz bia³y jest zalogowanym
				document.getElementById("blackPlayer").disabled = true; //to nie mo¿e on usi¹œæ na czarnych 
				document.getElementById("standUpWhite").disabled = false; //tylko ten co siedzi mo¿e wstaæ
			}
			debugToGameTextArea("Gracz figur bia³ych: "+ newWhitePlayerName);
			console.log('Bia³y gracz = ', newWhitePlayerName);
			if (document.getElementById("whitePlayer").value != "Bia³y" && document.getElementById("blackPlayer").value != "Czarny") //je¿eli bia³y i czarny siedz¹ na stole
			document.getElementById("startGame").disabled = false; //to da siê wcisn¹æ start
		}
	}
	
	function newBlack(newBlackPlayerName)
	{
		console.log('newWhitePlayerName: '+ newBlackPlayerName);
		
		if (newBlackPlayerName == "Czarny"){ //jak nikt nie zalogowany na czarnym
			document.getElementById("blackPlayer").value = "Czarny";
			<? 	if(!empty($_SESSION['id'])){ //tylko dla zalogowanych
				echo 'document.getElementById("blackPlayer").disabled = false; //da siê usi¹œæ na czarnym
				if (document.getElementById("whitePlayer").value == "Bia³y") //dodatkowo jak bia³y to "Bia³y" (tj. nikt)
				document.getElementById("whitePlayer").disabled = false; //to bia³y te¿ da siê wcisn¹æ i usi¹œæ';
			} ?>
			document.getElementById("standUpWhite").disabled = true; //i jak nikt nie siedzi na bia³ym to nikt nie mo¿e wstaæ na bia³ym
			document.getElementById("standUpBlack").disabled = true; //nie da siê wstaæ jak nikt nie siedzi
			document.getElementById("startGame").disabled = true; //to jest tylko zabezpieczenie
			debugToGameTextArea("Czarny gracz opóœci³ stó³");
			console.log('Czarny gracz = "Czarny"');
		}
		else { //je¿eli ktoœ siada na czarnych, tj. przes³any by³ jego nick 
			document.getElementById("blackPlayer").value = newBlackPlayerName; //wartoœæ buttonu czarnego zmienia siê na jego nick
			blackPlayerName = newBlackPlayerName; //zapamiêtaj nazwê czarnego
			document.getElementById("blackPlayer").disabled = true; //nikt nie mo¿e usi¹œæ na czarnym jak tam w³aœnie siad³ gracz
			if (document.getElementById("blackPlayer").value == js_loginUzytkownika){ //je¿eli gracz czarny jest zalogowanym
				document.getElementById("whitePlayer").disabled = true; //to nie mo¿e on usi¹œæ jednoczeœnie na bia³ych
				document.getElementById("standUpBlack").disabled = false; //tylko ten co siedzi mo¿e wstaæ 
			}
			debugToGameTextArea("Gracz figur czarnych: "+ newBlackPlayerName);
			console.log('Czarny gracz = ', newBlackPlayerName);
			if (document.getElementById("whitePlayer").value != "Bia³y" && document.getElementById("blackPlayer").value != "Czarny") //je¿eli bia³y i czarny siedz¹ na stole
			document.getElementById("startGame").disabled = false; //to da siê wcisn¹æ start
		}
	}
	
	function connectionOnline()
	{
		console.log("connection with weboscket server maintained");
	}
	
	function newGameStarted()
	{
		debugToGameTextArea("Nowa gra rozpoczêta. Bia³e wykonuj¹ ruch.");
		document.getElementById("startGame").disabled = true;
		whiteTurn(); 
	}
	
	function moveRespond(coreAnswer)
	{
		var moveOk = coreAnswer.substr(0,4);
		var whoseTurn = coreAnswer.substr(5,2);
		var gameStatus = coreAnswer.substr(8);
		console.log("moveOk = " + moveOk + ", whoseTurn = " + whoseTurn + ", gameStatus = " + gameStatus);
		
		if (gameStatus == "continue") gameInProgress(moveOk, whoseTurn, gameStatus);
		else if (gameStatus == "promote") promoteToWhat();
		else if (gameStatus == "whiteWon" || gameStatus == "blackWon" || gameStatus == "draw") endOfGame(gameStatus);
		else console.log("ERROR: moveRespond(): unknown gameStatus value = " + gameStatus);
	}
	
	function coreIsReady()
	{
		debugToGameTextArea("Wciœnij START, aby rozpocz¹æ grê.");
		document.getElementById("startGame").disabled = false;
	}
	
	function checked(whatWasChecked)
	{
		if (whatWasChecked.substr(0,5) == 'White') //je¿eli sprawdzana wartoœc w core to gracz bia³y...
		{ 
			var whitePlayerName = whatWasChecked.substr(6);	
			document.getElementById('whitePlayer').value = whitePlayerName; //...to nazwa bia³ego jest tym kto siedzi na bia³ym wg core...
			if (document.getElementById("whitePlayer").value != "Bia³y") //...i je¿eli na bia³ym jest jakiœ gracz...
			{ 
				document.getElementById('whitePlayer').disabled = true; //...to nikt nie mo¿e usi¹œæ na bia³ym...
				if(whitePlayerName == js_loginUzytkownika) //...i je¿eli sprawdzany gracz w core to gracz bêd¹cy zalogowanym...
				document.getElementById("standUpWhite").disabled = false; //...to przycisk do wstawania jest aktywny.
				console.log('Bia³y gracz = ', whitePlayerName);  
			}
			else if (document.getElementById("whitePlayer").value == "Bia³y") //jednak je¿eli nikt nie siedzi na bia³ym...
			{  
				<? if(!empty($_SESSION['id'])) //...i klient jest zalogowany na stronie...
					{ 
						echo 'document.getElementById("whitePlayer").disabled = false; //...to mozna usi¹œæ na bia³ym...
						document.getElementById("standUpWhite").disabled = true; //...a guzik wstawania jest wy³¹czony.
						console.log("Bia³y gracz = Bia³y");';
					} ?>
			}
		}							
		else if (whatWasChecked.substr(0,5) == 'Black')//je¿eli sprawdzana wartoœæ w core to gracz czarny...
		{ 
			var blackPlayerName = whatWasChecked.substr(6);
			document.getElementById('blackPlayer').value = blackPlayerName; //...to nazwa czarnego jest tym kto siedzi na czarnym wg core...
			if (document.getElementById("blackPlayer").value != "Czarny") //...i je¿eli na czarnym jest jakiœ gracz...
			{ 
				document.getElementById('blackPlayer').disabled = true; //...to nikt nie mo¿e usi¹œæ na czarnym...
				if(blackPlayerName == js_loginUzytkownika) //...i je¿eli sprawdzany gracz w core to gracz bêd¹cy zalogowanym...
				document.getElementById("standUpBlack").disabled = false; //...to przycisk do wstawania jest aktywny.
				console.log('Czarny gracz = ', blackPlayerName);  
			}
			else if (document.getElementById("blackPlayer").value == "Czarny") //jednak je¿eli nikt nie siedzi na czarnym...
			{  
				<? if(!empty($_SESSION['id'])) //i klient jest zalogowany na stronie...
					{ 
						echo 'document.getElementById("blackPlayer").disabled = false; //to mozna usi¹œæ na czarnym...
						console.log("Czarny gracz = Czarny");';
					} ?>
			}
		}
		else if (whatWasChecked.substr(0,4) == 'Turn')
		{ 
			//TODO: czy to siê przyda?
			var checkedTurn = whatWasChecked.substr(5)
			console.log('checked whoseTurn is = ' + checkedTurn);
		}
	}
	
	function promoted(promotedTo)
	{
		var promotingMove = promotedTo.substr(0,4);	
		var promotePiece = promotedTo.substr(5,1);
		var promoteType;
		switch(promotePiece)
		{
			case q: promoteType = "hetmana"; break
			case r: promoteType = "wie¿ê"; break
			case b: promoteType = "goñca"; break
			case k: promoteType = "skoczka"; break	
		}
		
		var promoteTurn = promotedTo.substr(7,2);
		var gameStateAfterPromotion = promotedTo.substr(10);
		var gameState;
		switch(gameStateAfterPromotion)
		{
			case 'continue': gameState = "Ruch wykonuje " + (promoteTurn == 'wt' ?  "Bia³y." : "Czarny."); break;
			case 'whiteWon': gameState = "Koniec gry. Wygra³ Bia³y."; break;
			case 'blackWon': gameState = "Koniec gry. Wygra³ Czarny."; break;
			case 'draw': gameState = "Koniec gry. Remis."; break;
			default: console.log('ERROR. promoted(): Unknown gameStateAfterPromotion var = ' + gameState); break;
		}
		
		debugToGameTextArea((promoteTurn == 'bt' ?  "Bia³y." : "Czarny.") + " wykona³ promocjê piona ruchem " + 
		promotingMove + " na " + promoteType + ". " + gameState;);
	}
	
	function badMove(coreAnswer) //TODO: only for sender
	{
		var textAreaMsg = "B³êdne rz¹dzenie ruchu: " + coreAnswer + "! Wpisz inny ruch.";
		debugToGameTextArea(textAreaMsg);
	}
	
</script> 									