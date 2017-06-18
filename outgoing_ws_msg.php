<script> 
	function newGame()
	{
		//TODO: ! warunek by nie zaczyna� gry, je�eli gracz nie opu�ci gry ! (?)
		if (websocket != null && document.getElementById("startGame").disabled == false)  //je�eli mamy po��czenie i przycisk da�o si� wcisn��
		{
			document.getElementById("startGame").disabled = true; // je�eli wcisn�� gracz start raz, to przycisk si� wy��cza 
			//TODO: @up !! w razie problem�w przycisk musi ponownie zadzia�a� !!
			var wiadomosc = "newGame"; //wiadomo�� rozpoczynaj�ca now� gr�
			websocket.send(wiadomosc); //wy�lij wiadomo�� na serwer
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
		var squareLetters = ['a','b','c','d','e','f','g','h','A','B','C','D','E','F','G','H']; //tabela dozwolonych znak�w dla 1go znaku obu input�w
		if (pieceFrom.length == 2 && pieceTo.length == 2)  //je�eli d�ugo�� obu string�w jest odpowiednia
		{
			if (pieceFrom.charAt(1) <= 8 && pieceTo.charAt(1) <= 8 && pieceFrom.charAt(1) >= 1 && pieceTo.charAt(1) >= 1) 	//je�eli druga litera obu input�w mie�ci si� w zakresie (1-8):
			{
				mainloop: 
				for (var n = 0; n < squareLetters.length; n++)
				{
					if (pieceFrom.charAt(0) == squareLetters[n])  //je�eli 1sza litera 1go inputu jest mi�dzy (a-h,A-H)
					{
						for (var m = 0; m < squareLetters.length; m++)
						{
							if (pieceTo.charAt(0) == squareLetters[m]) //je�eli 1sza litera 2go inputu jest mi�dzy (a-h,A-H)
							{
								strToSend = "move " + pieceFrom + pieceTo;
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
		debugToGameTextArea("B��dnie wprowadzone zapytanie o ruch.");
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
	
	function checkCoreVar(coreVar) //sprawdzanie warto�ci zmiennej na kompie
	{
		if ( websocket != null ) // je�eli po��czenie websocketowe jest aktywne (TODO: a mo�e nie by�?)
		{
			//TODO: sprawdzanie czy gracz jeszcze jest zalogowany (nie robi si� to z ka�dym wywo�aniem funkcji?)
			websocket.send("check " + coreVar);
			console.log( "string sent: check " + coreVar);
		}
		else console.log("ERROR! websocket == null");
	}
	
	function change(msg, player)
	{
		if ( websocket != null ) // je�eli po��czenie websocketowe jest aktywne (TODO: a mo�e nie by�?)
		{
			websocket.send("change " + msg + " " + player);
			console.log(msg + " " + player);
		}
		else console.log("ERROR! websocket == null");
	}
	
	function newWhiteName()
	{
		//TODO: tutaj wstawi� funkcje sprawdzaj�ce nickname aktualnych graczy z core'a 
		//TODO: sprawdzanie czy gracz jeszcze jest zalogowany (nie robi si� to z ka�dym wywo�aniem funkcji?)
		//if ($getBlackPlayer != $loginUzytkownika){ 
		//var userLogin = "<? echo $loginUzytkownika ?>";
		change("whitePlayer", js_loginUzytkownika);
	}
	
	function newBlackName() 
	{
		//TODO: tutaj wstawi� funkcje sprawdzaj�c� nickname aktualnych graczy z core'a 
		//TODO: sprawdzanie czy gracz jeszcze jest zalogowany (nie robi si� to z ka�dym wywo�aniem funkcji?)
		//if ($getBlackPlayer != $loginUzytkownika){ 
		//var userLogin = "<? echo $loginUzytkownika ?>";
		change("blackPlayer", js_loginUzytkownika);
	}
	
	function leaveWhite() 
	{
		//TODO: doda� alert z zapytaniem czy na pewno chce opu�ci� gr� !
		//TODO: je�eli gracz uciek�, to drugi gracz kt�ry zosta� ma mozliwo�� zako�czenia gry, b�d� grania dalej z robotem(p�niej) !
		//TODO: sprawd� czy bia�y to login zalogowanego
		change("whitePlayerName", "White");
	}
	
	function leaveBlack() 
	{
		//TODO: doda� alert z zapytaniem czy na pewno chce opu�ci� gr� !
		//TODO: je�eli gracz uciek�, to drugi gracz kt�ry zosta� ma mozliwo�� zako�czenia gry, bad� grania dalej z robotem(p�niej) !
		//TODO: sprawd� czy czarny to login zalogowanego
		change("blackPlayerName", "Black");
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
	};
	
</script> 									