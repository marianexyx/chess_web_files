<?
	function newGame()
	{
		enabling('clickedBtn');
		websocket.send("newGame"); 
		debugToConsole("send 'new' websocket command to server");
	}

	function movePiece() //todo: post?
	{
		$pieceFrom = echo '<script> document.getElementById("pieceFrom").value; </script>'; //todo: to chyba inaczej niż formularzem nie pójdzie
		$pieceTo = echo '<script> document.getElementById("pieceTo").value; </script>';
		$strToSend;
		$squareLetters = ['a','b','c','d','e','f','g','h','A','B','C','D','E','F','G','H']; //tabela dozwolonych znaków dla 1go znaku obu inputów
		if (strlen($pieceFrom) == 2 && strlen($pieceTo) == 2)  //jeżeli długość obu stringów jest odpowiednia
		{
			if ($pieceFrom[1] <= 8 && $pieceTo[1] <= 8 && $pieceFrom[1] >= 1 && $pieceTo[1] >= 1) 	//jeżeli druga litera obu inputów mieści się w zakresie (1-8):
			{
				for ($n = 0; $n < strlen($squareLetters(; $n++)
				{
					if ($pieceFrom[0] == $squareLetters[$n])  //jeżeli 1sza litera 1go inputu jest między (a-h,A-H)
					{
						for ($m = 0; $m < strlen($squareLetters); $m++)
						{
							if ($pieceTo[0] == $squareLetters[$m]) //jeżeli 1sza litera 2go inputu jest między (a-h,A-H)
							{
								$strToSend = "move ".$pieceFrom.$pieceTo;
								
								echo '<script>document.getElementById("pieceFrom").value = ""; //czyszczenie dla kolejnych zapytań
								document.getElementById("pieceTo").value = "";</script>';
									
								enabling('clickedBtn');
								echo '<script>websocket.send( $strToSend );</script>';

								$consoleMsg = 'string sent :'.$strToSend;
								debugToConsole($consoleMsg);
								
								break 2;
							}
							else if ($m == strlen($squareLetters) && $pieceFrom[0] != $squareLetters[strlen($squareLetters)]) wrongInputs();
							if ($m > strlen($squareLetters)) wrongInputs();
						}
					}
					else if ($n == strlen($squareLetters) && $pieceFrom[0] != $squareLetters[strlen($squareLetters)]) wrongInputs();
					if ($n > strlen($squareLetters)) wrongInputs();
				}
			}
			else wrongInputs();
			}
		else wrongInputs();	
	}
	
	function wrongInputs()
	{
		echo '<script> debugToGameTextArea("Błędnie wprowadzone zapytanie o ruch.");
		document.getElementById("pieceFrom").value="";
		document.getElementById("pieceTo").value=""; </script>';
	}
	
	function resetBoard()
	{
		debugToConsole('reset');
		echo '<script>websocket.send("reset");</script>';
	}
	
	function checkCoreVar($coreVar) 
	{
		echo '<script>websocket.send("check "'.$coreVar.');</script>';

		$consoleMsg = 'string sent: check '.$coreVar;
		debugToConsole($consoleMsg);
	}
	
	function change($msg, $player)
	{
		echo '<script>websocket.send("change "'.$msg.'" "'.$player.');</script>';
		
		$consoleMsg = $msg.'" "'.$player;
		debugToConsole($consoleMsg);
	}
	
	function newWhiteName()
	{
		//TODO: tutaj wstawić funkcje sprawdzające nickname aktualnych graczy z core'a 
		//TODO: sprawdzanie czy gracz jeszcze jest zalogowany (nie robi się to z każdym wywołaniem funkcji?)
		//if ($getBlackPlayer != $loginUzytkownika){ 
		//var userLogin = "< ? echo $loginUzytkownika ? >";
		enabling('clickedBtn');
		change("whitePlayer", $loginUzytkownika);
	}
	
	function newBlackName() 
	{
		//TODO: tutaj wstawić funkcje sprawdzającą nickname aktualnych graczy z core'a 
		//TODO: sprawdzanie czy gracz jeszcze jest zalogowany (nie robi się to z każdym wywołaniem funkcji?)
		//if ($getBlackPlayer != $loginUzytkownika){ 
		//var userLogin = "< ? echo $loginUzytkownika ? >";
		enabling('clickedBtn');
		change("blackPlayer", $loginUzytkownika);
	}
	
	function leaveWhite() 
	{
		//TODO: dodać alert z zapytaniem czy na pewno chce opuścić grę !
		//TODO: jeżeli gracz uciekł, to drugi gracz który został ma mozliwość zakończenia gry, bądź grania dalej z robotem(później) !
		//TODO: sprawdź czy biały to login zalogowanego
		enabling('clickedBtn');
		change("whitePlayer", WHITE);
	}
	
	function leaveBlack() 
	{
		//TODO: dodać alert z zapytaniem czy na pewno chce opuścić grę !
		//TODO: jeżeli gracz uciekł, to drugi gracz który został ma mozliwość zakończenia gry, badź grania dalej z robotem(później) !
		//TODO: sprawdź czy czarny to login zalogowanego
		enabling('clickedBtn');
		change("blackPlayer", BLACK);
	}
				
?>										