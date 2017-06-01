<script> 
	function keepConnected()
	{
		websocket.send("keepConnected");
		console.log("Wys�ana do websocket servera pusta wiadomo�� podtrzymuj�c� po��czenie");
	}
	
	function newGame()
	{
		//TODO: ! warunek by nie zaczyna� gry, je�eli gracz nie opu�ci gry ! (?)
		if (websocket != null && document.getElementById("startGame").disabled == false)  //je�eli mamy po��czenie i przycisk da�o si� wcisn��
		{
			document.getElementById("startGame").disabled = true; // je�eli wcisn�� gracz start raz, to przycisk si� wy��cza 
			//TODO: @up !! w razie problem�w przycisk musi ponownie zadzia�a� !!
			var wiadomosc = "new"; //wiadomo�� rozpoczynaj�ca now� gr�
			websocket.send(wiadomosc); //wy�lij wiadomo�c na serwer
			console.log("send 'new' websocket command to server");
		}
		else debugToGameTextArea("WebSocket is null");
		
	}
	
	function movePiece(msg)
	{
		piece_from = document.getElementById("pieceFrom").value;
		piece_to = document.getElementById("pieceTo").value;
		var strToSend;
		var squareLetters = ['a','b','c','d','e','f','g','h','A','B','C','D','E','F','G','H']; //tabela dozwolonych znak�w dla 1go znaku obu input�w
		if (piece_from.length == 2 && piece_to.length == 2)  //je�eli d�ugo�� obu string�w jest odpowiednia
		{
			if (piece_from.charAt(1) <= 8 && piece_to.charAt(1) <= 8 && piece_from.charAt(1) >= 1 && piece_to.charAt(1) >= 1) 	//je�eli druga litera obu input�w mie�ci si� w zakresie (1-8):
			{
				mainloop: 
				for (var n = 0; n < squareLetters.length; n++)
				{
					if (piece_from.charAt(0) == squareLetters[n])  //je�eli 1sza litera 1go inputu jest mi�dzy (a-h,A-H)
					{
						for (var m = 0; m < squareLetters.length; m++)
						{
							if (piece_to.charAt(0) == squareLetters[m]) { //je�eli 1sza litera 2go inputu jest mi�dzy (a-h,A-H)
								{
									strToSend = "move " + piece_from + piece_to;
									if ( websocket != null ) // je�eli po��czenie websocketowe jest aktywne (TODO: a co jak nie b�dzie)
									{
										document.getElementById("pieceFrom").value = ""; //czyszczenie dla kolejnych zapyta�
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
								else if (m == squareLetters.length && piece_from.charAt(0) != squareLetters[squareLetters.length]) wrongInputs();
								if (m > squareLetters.length) wrongInputs();
							}
						}
						else if (n == squareLetters.length && piece_from.charAt(0) != squareLetters[squareLetters.length]) wrongInputs();
						if (n > squareLetters.length) wrongInputs();
					}
				}
				else wrongInputs();
			}
			else wrongInputs();	
		}
	}
	
	function wrongInputs()
	{
		debugToGameTextArea("B��dnie wprowadzone zapytanie o ruch.");
		document.getElementById("pieceFrom").value="";
		document.getElementById("pieceTo").value="";
		console.log('piece_from: ','"' + piece_from+'"');
		console.log('piece_from.length: ','"' + piece_from.length+'"');
		console.log('piece_to: ','"' + piece_to+'"');
		console.log('piece_to.length: ','"' + piece_to.length+'"');
	}
	
	function resetBoard()
	{
		console.log('reset');
		websocket.send('reset');
	}
	
	enum CHECK_VAL { WHITE, BLACK, TURN };
	function check(CHECK_VAL msg)
	{
		var checkMsg;
		switch (msg)
		{
			case WHITE: { checkMsg = "check whitePlayer"; break; }
			case BLACK: { checkMsg = "check blackPlayer"; break; }
			case TURN: { checkMsg = "check whoseTurn"; break; }
			default: { console.log("ERROR: wrong value in  check(CHECK_VAL msg): " + msg); break; }
		}
	}
	
	function checkCoreVar(coreVar) //sprawdzanie warto�ci zmiennej na kompie
	{
		if ( websocket != null ) // je�eli po��czenie websocketowe jest aktywne (TODO: a mo�e nie by�?)
		{
			//TODO: sprawdzanie czy gracz jeszcze jest zalogowany (nie robi si� to z ka�dym wywo�aniem funkcji?)
			console.log( "string to send: check " + coreVar);
			websocket.send("check " + coreVar);
			console.log( "string sent: check " + coreVar);
		}
		else console.log("ERROR! websocket == null");
	}
	
	function change(msg, player)
	{
		if ( websocket != null ) // je�eli po��czenie websocketowe jest aktywne (TODO: a mo�e nie by�?)
		{
			websocket.send(msg + " " + player);
			console.log(msg + " " + player);
		}
		else console.log("ERROR! websocket == null");
	}
	
	function newWhiteName()
	{
		//TODO: tutaj wstawi� funkcje sprawdzaj�c� nickname aktualnych graczy z core'a 
		//TODO: sprawdzanie czy gracz jeszcze jest zalogowany (nie robi si� to z ka�dym wywo�aniem funkcji?)
		//if ($getBlackPlayer != $loginUzytkownika){ 
		//var userLogin = "<? echo $loginUzytkownika ?>";
		change("whitePlayerName", js_loginUzytkownika);
	}
	
	function newBlackName() 
	{
		//TODO: tutaj wstawi� funkcje sprawdzaj�c� nickname aktualnych graczy z core'a 
		//TODO: sprawdzanie czy gracz jeszcze jest zalogowany (nie robi si� to z ka�dym wywo�aniem funkcji?)
		//if ($getBlackPlayer != $loginUzytkownika){ 
		//var userLogin = "<? echo $loginUzytkownika ?>";
		change("blackPlayerName", js_loginUzytkownika);
	}
	
	function leaveWhite() 
	{
		//TODO: doda� alert z zapytaniem czy na pewno chce opu�ci� gr� !
		//TODO: je�eli gracz uciek�, to drugi gracz kt�ry zosta� ma mozliwo�� zako�czenia gry, bad� grania dalej z robotem(p�niej) !
		//TODO: sprawd� czy bia�y to login zalogowanego
		change("whitePlayerName", "Bia�y");
	}
	
	function leaveBlack() 
	{
		//TODO: doda� alert z zapytaniem czy na pewno chce opu�ci� gr� !
		//TODO: je�eli gracz uciek�, to drugi gracz kt�ry zosta� ma mozliwo�� zako�czenia gry, bad� grania dalej z robotem(p�niej) !
		//TODO: sprawd� czy czarny to login zalogowanego
		change("blackPlayerName", "Czarny");
	}
	
	function promoteTo(msg)
	{
		$("#dialog-promote").dialog(
		{
			autoOpen: false, 
			buttons: 
			{
				'hetman': function() 
				{
					websocket.send("promoteTo: q"); //queen
					console.log('sent to core: promote_to: q');
					$(this).dialog("close");
				}, 
				'goniec': function() 
				{
					websocket.send("promoteTo: b"); //bishop
					console.log('sent to core: promote_to: b');
					$(this).dialog("close");
				}, 
				'skoczek': function() 
				{
					websocket.send("promoteTo: k"); //knight
					console.log('sent to core: promote_to: k');
					$(this).dialog("close");
				}, 
				'wie�a': function() 
				{
					websocket.send("promoteTo: r"); //rook
					console.log('sent to core: promote_to: r');
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
		$( "#opener-promote" ).click(function() 
		{
			$( "#dialog-promote" ).dialog( "open" );
		});
	});
}


</script> 				