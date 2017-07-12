<?
	function onMessage($evt)
	{
		$consoleMsg = 'clear msg from core:'.$evt;
		debugToConsole($consoleMsg);
		
		if 		(substr($evt,0,8) == 'newWhite') 	{ newWhite(substr($evt,9)); }
		else if (substr($evt,0,8) == 'newBlack') 	{ newBlack(substr($evt,9)); }
		else if	($evt == 'connectionOnline') 		{ connectionOnline(); }
		else if	($evt == 'newOk') 					{ newGameStarted(); }
		else if	(substr($evt,0,6) == 'moveOk') 		{ moveRespond(substr($evt,7)); }
		else if ($evt == 'reseting')				{ reseting(); }
		else if	($evt == 'ready') 					{ coreIsReady(); }
		else if	(substr($evt,0,7) == 'checked') 	{ checked(substr($evt,7)); }
		else if	(substr($evt,0,8) == 'promoted') 	{ promoted(substr($evt,9)); }
		else if	(substr($evt,0,7) == 'badMove') 	{ badMove(substr($evt,8)); }
		else 
		{
			$consoleMsg = 'ERROR. Unknown onMessage value = '.$evt;
			debugToConsole($consoleMsg);
		}
	}
	
	function newWhite($newWhitePlayerName)
	{		
		if ($newWhitePlayerName == WHITE) 
		{ 
			$_SESSION['white'] = WHITE;
			echo '<script>document.getElementById("whitePlayer").value = '.WHITE.');</script>';
			enabling('whiteEmpty');
			debugToConsole('white player ='.WHITE);
		}
		else 
		{ 
			$_SESSION['white'] = $newWhitePlayerName;
			echo '<script>document.getElementById("whitePlayer").value = '.$newWhitePlayerName.');</script>';
			echo '<script>debugToGameTextArea("Gracz figur białych: "'.$newWhitePlayerName.');</script>';
			enabling('newWhite');
			$consoleMsg = 'white player = '. $newWhitePlayerName;
			debugToConsole($consoleMsg);
		}
	}
	
	function newBlack($newBlackPlayerName)
	{		
		if ($newBlackPlayerName == BLACK) 
		{ 
			echo 'document.getElementById("blackPlayer").value = '.BLACK.');</script>';
			enabling('blackEmpty');
			debugToConsole('black player ='.BLACK);
		}
		else 
		{ 
			$_SESSION['black'] = $newBlackPlayerName; 
			echo '<script>document.getElementById("blackPlayer").value = '.$newBlackPlayerName.');</script>'; 
			echo '<script>debugToGameTextArea("Gracz figur czarnych: '.$newBlackPlayerName.');</script>';
			enabling('newBlack');
			$consoleMsg = 'black player = '.$newBlackPlayerName;
			debugToConsole($consoleMsg);
		}
	}
	
	function connectionOnline()
	{
		debugToConsole("connection with weboscket server maintained");
	}
	
	function newGameStarted()
	{
		if ($_SESSION['white'] != WHITE && $_SESSION['black'] != BLACK)
		{
			echo '<script>debugToGameTextArea("Nowa gra rozpoczęta. Białe wykonują ruch.");</script>';
			enabling('newGame');
		}
		else debugToConsole("ERROR: game started when players aren't on chairs");
	}
	
	function moveRespond($coreAnswer)
	{
		$moveOk = substr($coreAnswer,0,4); 
		$whoseTurn = substr($coreAnswer,5,2);
		$gameStatus = substr($coreAnswer,8);

		$consoleMsg = 'moveOk = '. $moveOk .', whoseTurn = '.$whoseTurn.'gameStatus = '.$gameStatus;
		debugToConsole($consoleMsg);
		
		if ($gameStatus == "continue") gameInProgress($moveOk, $whoseTurn);
		else if ($gameStatus == "promote") promoteToWhat();
		else if ($gameStatus == "whiteWon" || $gameStatus == "blackWon" || $gameStatus == "draw") 
			echo '<script> endOfGame($moveOk, $gameStatus); </script>';
		else 
		{
			$consoleMsg = 'ERROR: moveRespond(): unknown gameStatus value = '.$gameStatus;
			debugToConsole($consoleMsg);
		}
	}
	
	function reseting()
	{
		echo '<script>debugToGameTextArea("Koniec gry: Gracz opuścił stół. Resetownie planszy...");</script>';
	}
	
	function coreIsReady()
	{
		enabling('resetComplited');
	}
	
	function checked($whatWasChecked)
	{
		if (substr($whatWasChecked,0,5) == 'White')
		{ 
			$_SESSION['white'] = substr($whatWasChecked,6);
			echo '<script>document.getElementById("whitePlayer").value ='.$_SESSION['white'].';</script>'; 
			enabling('newWhite');
		}							
		else if (substr($whatWasChecked,0,5) == 'Black')
		{ 
			$_SESSION['black'] = substr($whatWasChecked,6);
			echo '<script>document.getElementById("blackPlayer").value ='.$_SESSION["black"].';</script>'; 
			enabling('newBlack');			
		}
		else if (substr($whatWasChecked,0,4) == 'Turn')
		{ 
			$checkedTurn = substr($whatWasChecked,5);
			
			if ($checkedTurn == 'nt') $_SESSION['turn'] = NO_TURN; 
			else if($checkedTurn == 'wt') $_SESSION['turn'] = WHITE_TURN;
			else if($checkedTurn == 'bt') $_SESSION['turn'] = BLACK_TURN;
			else 
			{
				$consoleMsg = 'ERROR: unknown turn type = '.$checkedTurn;
				debugToConsole($consoleMsg);
			}
			
			if ($_SESSION['turn'] != NO_TURN) enabling('gameInProgress');
			else enabling('endOfGame');

			$consoleMsg = 'checked whoseTurn is = '.$_SESSION['turn'];
			debugToConsole($consoleMsg);
		}
		else if (substr($whatWasChecked,0,9) == 'TableData')
		{
			$tableData = explode(" ",$whatWasChecked);
			
			$_SESSION['white'] = $tableData[1];	
			echo '<script>document.getElementById("whitePlayer").value ='.$_SESSION['white'].';</script>';  
			
			$_SESSION['black'] = $tableData[2];
			echo '<script>document.getElementById("blackPlayer").value ='.$_SESSION['black'].';</script>'; 
			
			$_SESSION['turn'] = $tableData[3];
			if ($_SESSION['turn'] != NO_TURN) enabling('gameInProgress');
			else enabling('endOfGame');

			$consoleMsg = 'checked whoseTurn is = '.$_SESSION['turn'];
			debugToConsole($consoleMsg);
		}
		else 
		{
			$consoleMsg = 'ERROR: unknown checked function parameter = '.$whatWasChecked;
			debugToConsole($consoleMsg);
		}
	}
	
	function promoted($promotedTo)
	{
		$promotingMove = substr($promotedTo,0,4);	
		$promotePiece = substr($promotedTo,5,1);
		$promoteType;
		switch($promotePiece)
		{
			case q: $promoteType = "hetmana"; break;
			case r: $promoteType = "wieżę"; break;
			case b: $promoteType = "gońca"; break;
			case k: $promoteType = "skoczka"; break	;
			default:
				$consoleMsg = 'ERROR. promoted(): Unknown $promotePiece var = '.$promotePiece;
				debugToConsole($consoleMsg);
				break;
		}
		
		$promoteTurn = substr($promotedTo,7,2); 
		$gameStateAfterPromotion = substr($promotedTo,10);
		$gameState;
		switch($gameStateAfterPromotion)
		{
			case 'continue': $gameState = "Ruch wykonuje ". ($promoteTurn == 'bt' ?  "Biały." : "Czarny."); break;
			case 'whiteWon': $gameState = "Koniec gry. Wygrał Biały."; break;
			case 'blackWon': $gameState = "Koniec gry. Wygrał Czarny."; break;
			case 'draw': $gameState = "Koniec gry. Remis."; break;
			default: 
				$consoleMsg = 'ERROR. promoted(): Unknown $gameStateAfterPromotion var = '. $gameStateAfterPromotion;
				debugToConsole($consoleMsg);
				break;
		}
		
		echo '<script>debugToGameTextArea("Koniec gry: Gracz opuścił stół. Resetownie planszy...");</script>';
		echo '<script>debugToGameTextArea('.($promoteTurn == "bt" ?  "Biały." : "Czarny.").' wykonał promocję piona ruchem ' 
		.$promotingMove.' na '.$promoteType.'. '.$gameState.');</script>';
	}
	
	function badMove($coreAnswer)
	{
		$consoleMsg = 'badMove: '.$coreAnswer;
		debugToConsole($consoleMsg);

		$textAreaMsg = "Błędne rządanie ruchu: ".substr($coreAnswer,0,4)."! Wpisz inny ruch.";
		echo '<script>debugToGameTextArea'.($textAreaMsg).';</script>';
		enabling('badMove');
	}
	
?>							