<script> 
	function newGame()
	{
		//TODO: ! warunek by nie zaczynać gry, jeżeli gracz nie opuści gry ! (?)
		if (websocket != null && document.getElementById("startGame").disabled == false)  //jeżeli mamy połączenie i przycisk dało się wcisnąć
		{
			document.getElementById("startGame").disabled = true; // jeżeli wcisnął gracz start raz, to przycisk się wyłącza 
			//TODO: @up !! w razie problemów przycisk musi ponownie zadziałać !!
			var wiadomosc = "newGame"; //wiadomość rozpoczynająca nową grę
			websocket.send(wiadomosc); //wyślij wiadomość na serwer
			console.log("send 'new' websocket command to server");
		}
		else debugToGameTextArea("WebSocket is null");
	}
	
	var pieceFrom;
	var pieceTo;
	function movePiece(msg)
	{
		pieceFrom = document.getElementById("pieceFrom").value;
		pieceTo = document.getElementById("pieceTo").value;
		var strToSend;
		var squareLetters = ['a','b','c','d','e','f','g','h','A','B','C','D','E','F','G','H']; //tabela dozwolonych znaków dla 1go znaku obu inputów
		if (pieceFrom.length == 2 && pieceTo.length == 2)  //jeżeli długość obu stringów jest odpowiednia
		{
			if (pieceFrom.charAt(1) <= 8 && pieceTo.charAt(1) <= 8 && pieceFrom.charAt(1) >= 1 && pieceTo.charAt(1) >= 1) 	//jeżeli druga litera obu inputów mieści się w zakresie (1-8):
			{
				mainloop: 
				for (var n = 0; n < squareLetters.length; n++)
				{
					if (pieceFrom.charAt(0) == squareLetters[n])  //jeżeli 1sza litera 1go inputu jest między (a-h,A-H)
					{
						for (var m = 0; m < squareLetters.length; m++)
						{
							if (pieceTo.charAt(0) == squareLetters[m]) //jeżeli 1sza litera 2go inputu jest między (a-h,A-H)
							{
								strToSend = "move " + pieceFrom + pieceTo;
								if ( websocket != null ) // jeżeli połączenie websocketowe jest aktywne (TODO: a co jak nie będzie)
								{
									document.getElementById("pieceFrom").value = ""; //czyszczenie dla kolejnych zapytań
									document.getElementById("pieceTo").value = "";
									websocket.send( strToSend );
									console.log( "string sent :", '"'+strToSend+'"' );
									//debugToGameTextArea(strToSend);
								}
								else 
								{
									debugToGameTextArea("Not connected to server");
									console.log("websocket not connected");
								}
								break mainloop;
							}
							else if (m == squareLetters.length && pieceFrom.charAt(0) != squareLetters[squareLetters.length]) wrongInputs();
							if (m > squareLetters.length) wrongInputs();
						}
					}
					else if (n == squareLetters.length && pieceFrom.charAt(0) != squareLetters[squareLetters.length]) wrongInputs();
					if (n > squareLetters.length) wrongInputs();
				}
			}
			else wrongInputs();
		}
		else wrongInputs();	
	}
	
	function wrongInputs()
	{
		debugToGameTextArea("Błędnie wprowadzone zapytanie o ruch.");
		document.getElementById("pieceFrom").value="";
		document.getElementById("pieceTo").value="";
		console.log('pieceFrom: ','"' + pieceFrom+'"');
		console.log('pieceFrom.length: ','"' + pieceFrom.length+'"');
		console.log('pieceTo: ','"' + pieceTo+'"');
		console.log('pieceTo.length: ','"' + pieceTo.length+'"');
	}
	
	function resetBoard()
	{
		console.log('reset');
		websocket.send('reset');
	}
	
	function checkCoreVar(coreVar) //sprawdzanie wartości zmiennej na kompie
	{
		if ( websocket != null ) // jeżeli połączenie websocketowe jest aktywne (TODO: a może nie być?)
		{
			//TODO: sprawdzanie czy gracz jeszcze jest zalogowany (nie robi się to z każdym wywołaniem funkcji?)
			websocket.send("check " + coreVar);
			console.log( "string sent: check " + coreVar);
		}
		else console.log("ERROR! websocket == null");
	}
	
	function change(msg, player)
	{
		if ( websocket != null ) // jeżeli połączenie websocketowe jest aktywne (TODO: a może nie być?)
		{
			websocket.send("change " + msg + " " + player);
			console.log(msg + " " + player);
		}
		else console.log("ERROR! websocket == null");
	}
	
	function newWhiteName()
	{
		//TODO: tutaj wstawić funkcje sprawdzające nickname aktualnych graczy z core'a 
		//TODO: sprawdzanie czy gracz jeszcze jest zalogowany (nie robi się to z każdym wywołaniem funkcji?)
		//if ($getBlackPlayer != $loginUzytkownika){ 
		//var userLogin = "<? echo $loginUzytkownika ?>";
		change("whitePlayer", js_loginUzytkownika);
	}
	
	function newBlackName() 
	{
		//TODO: tutaj wstawić funkcje sprawdzającą nickname aktualnych graczy z core'a 
		//TODO: sprawdzanie czy gracz jeszcze jest zalogowany (nie robi się to z każdym wywołaniem funkcji?)
		//if ($getBlackPlayer != $loginUzytkownika){ 
		//var userLogin = "<? echo $loginUzytkownika ?>";
		change("blackPlayer", js_loginUzytkownika);
	}
	
	function leaveWhite() 
	{
		//TODO: dodać alert z zapytaniem czy na pewno chce opuścić grę !
		//TODO: jeżeli gracz uciekł, to drugi gracz który został ma mozliwość zakończenia gry, bądź grania dalej z robotem(później) !
		//TODO: sprawdź czy biały to login zalogowanego
		change("whitePlayerName", "White");
	}
	
	function leaveBlack() 
	{
		//TODO: dodać alert z zapytaniem czy na pewno chce opuścić grę !
		//TODO: jeżeli gracz uciekł, to drugi gracz który został ma mozliwość zakończenia gry, badź grania dalej z robotem(później) !
		//TODO: sprawdź czy czarny to login zalogowanego
		change("blackPlayerName", "Black");
	}
				
	</script> 										