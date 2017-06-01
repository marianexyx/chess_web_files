<script> 
	function keepConnected()
	{
		websocket.send("keepConnected");
		console.log("Wys³ana do websocket servera pusta wiadomoœæ podtrzymuj¹c¹ po³¹czenie");
	}
	
	function newGame()
	{
		//TODO: ! warunek by nie zaczynaæ gry, je¿eli gracz nie opuœci gry ! (?)
		if (websocket != null && document.getElementById("startGame").disabled == false)  //je¿eli mamy po³¹czenie i przycisk da³o siê wcisn¹æ
		{
			document.getElementById("startGame").disabled = true; // je¿eli wcisn¹³ gracz start raz, to przycisk siê wy³¹cza 
			//TODO: @up !! w razie problemów przycisk musi ponownie zadzia³aæ !!
			var wiadomosc = "new"; //wiadomoœæ rozpoczynaj¹ca now¹ grê
			websocket.send(wiadomosc); //wyœlij wiadomoœc na serwer
			console.log("send 'new' websocket command to server");
		}
		else debugToGameTextArea("WebSocket is null");
		
	}
	
	function movePiece(msg)
	{
		piece_from = document.getElementById("pieceFrom").value;
		piece_to = document.getElementById("pieceTo").value;
		var strToSend;
		var squareLetters = ['a','b','c','d','e','f','g','h','A','B','C','D','E','F','G','H']; //tabela dozwolonych znaków dla 1go znaku obu inputów
		if (piece_from.length == 2 && piece_to.length == 2)  //je¿eli d³ugoœæ obu stringów jest odpowiednia
		{
			if (piece_from.charAt(1) <= 8 && piece_to.charAt(1) <= 8 && piece_from.charAt(1) >= 1 && piece_to.charAt(1) >= 1) 	//je¿eli druga litera obu inputów mieœci siê w zakresie (1-8):
			{
				mainloop: 
				for (var n = 0; n < squareLetters.length; n++)
				{
					if (piece_from.charAt(0) == squareLetters[n])  //je¿eli 1sza litera 1go inputu jest miêdzy (a-h,A-H)
					{
						for (var m = 0; m < squareLetters.length; m++)
						{
							if (piece_to.charAt(0) == squareLetters[m]) { //je¿eli 1sza litera 2go inputu jest miêdzy (a-h,A-H)
								{
									strToSend = "move " + piece_from + piece_to;
									if ( websocket != null ) // je¿eli po³¹czenie websocketowe jest aktywne (TODO: a co jak nie bêdzie)
									{
										document.getElementById("pieceFrom").value = ""; //czyszczenie dla kolejnych zapytañ
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
		debugToGameTextArea("B³êdnie wprowadzone zapytanie o ruch.");
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
	
	function checkCoreVar(coreVar) //sprawdzanie wartoœci zmiennej na kompie
	{
		if ( websocket != null ) // je¿eli po³¹czenie websocketowe jest aktywne (TODO: a mo¿e nie byæ?)
		{
			//TODO: sprawdzanie czy gracz jeszcze jest zalogowany (nie robi siê to z ka¿dym wywo³aniem funkcji?)
			console.log( "string to send: check " + coreVar);
			websocket.send("check " + coreVar);
			console.log( "string sent: check " + coreVar);
		}
		else console.log("ERROR! websocket == null");
	}
	
	function change(msg, player)
	{
		if ( websocket != null ) // je¿eli po³¹czenie websocketowe jest aktywne (TODO: a mo¿e nie byæ?)
		{
			websocket.send(msg + " " + player);
			console.log(msg + " " + player);
		}
		else console.log("ERROR! websocket == null");
	}
	
	function newWhiteName()
	{
		//TODO: tutaj wstawiæ funkcje sprawdzaj¹c¹ nickname aktualnych graczy z core'a 
		//TODO: sprawdzanie czy gracz jeszcze jest zalogowany (nie robi siê to z ka¿dym wywo³aniem funkcji?)
		//if ($getBlackPlayer != $loginUzytkownika){ 
		//var userLogin = "<? echo $loginUzytkownika ?>";
		change("whitePlayerName", js_loginUzytkownika);
	}
	
	function newBlackName() 
	{
		//TODO: tutaj wstawiæ funkcje sprawdzaj¹c¹ nickname aktualnych graczy z core'a 
		//TODO: sprawdzanie czy gracz jeszcze jest zalogowany (nie robi siê to z ka¿dym wywo³aniem funkcji?)
		//if ($getBlackPlayer != $loginUzytkownika){ 
		//var userLogin = "<? echo $loginUzytkownika ?>";
		change("blackPlayerName", js_loginUzytkownika);
	}
	
	function leaveWhite() 
	{
		//TODO: dodaæ alert z zapytaniem czy na pewno chce opuœciæ grê !
		//TODO: je¿eli gracz uciek³, to drugi gracz który zosta³ ma mozliwoœæ zakoñczenia gry, badŸ grania dalej z robotem(póŸniej) !
		//TODO: sprawdŸ czy bia³y to login zalogowanego
		change("whitePlayerName", "Bia³y");
	}
	
	function leaveBlack() 
	{
		//TODO: dodaæ alert z zapytaniem czy na pewno chce opuœciæ grê !
		//TODO: je¿eli gracz uciek³, to drugi gracz który zosta³ ma mozliwoœæ zakoñczenia gry, badŸ grania dalej z robotem(póŸniej) !
		//TODO: sprawdŸ czy czarny to login zalogowanego
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
				'wie¿a': function() 
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