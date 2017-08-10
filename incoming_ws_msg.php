<?
	if(!isset($_SESSION)) session_start();
	
	function shortToFullTurnType($shortTurn)
	{
		$fullTurn;
		if ($shortTurn == 'nt') $fullTurn = 'NO_TURN'; 
		else if($shortTurn == 'wt') $fullTurn = 'WHITE_TURN';
		else if($shortTurn == 'bt') $fullTurn = 'BLACK_TURN';
		else $consoleAjax = 'ERROR: unknown turn type = '.$shortTurn;
		return $fullTurn;
	}
	
	function newWhite($newWhitePlayerName)
	{		
		$consoleAjax;
		$textboxAjax = '-1';
		$enablingArr = array();
		
		if ($newWhitePlayerName == 'WHITE') 
		{ 
			//todo: brakuje tu komunikatu dla wszystkich mówiącego, że gracz uciekł, a drugi wygrał
			$_SESSION['white'] = 'WHITE';
			$consoleAjax = 'white player = WHITE';
			$enablingArr = enabling('whiteEmpty');
		}
		else 
		{ 
			$_SESSION['white'] = $newWhitePlayerName;
			$textboxAjax = 'Gracz figur białych: '.$_SESSION['white'];	
			$consoleAjax = 'white player = '.$_SESSION['white'];
			$enablingArr = enabling('newWhite');
		}
		
		return array($_SESSION['white'],'-1',$enablingArr[0],$enablingArr[1],$enablingArr[2],$enablingArr[3],$enablingArr[4],
		$enablingArr[5],$enablingArr[6],$enablingArr[7],$enablingArr[8],$enablingArr[9],$enablingArr[10],$consoleAjax,$textboxAjax,'-1');
	}
	
	function newBlack($newBlackPlayerName)
	{		
		$consoleAjax;
		$textboxAjax = '-1';
		$enablingArr = array();
		
		if ($newBlackPlayerName == 'BLACK') 
		{ 
			//todo: brakuje tu komunikatu dla wszystkich mówiącego, że gracz uciekł, a drugi wygrał
			$_SESSION['black'] = 'BLACK';
			$consoleAjax = 'black player = BLACK'; 
			$enablingArr = enabling('blackEmpty');
		}
		else 
		{ 
			$_SESSION['black'] = $newBlackPlayerName; 
			$textboxAjax = 'Gracz figur czarnych: '.$_SESSION['black']; 
			$consoleAjax = 'black player = '.$_SESSION['black']; 
			$enablingArr = enabling('newBlack');
		}
		
		return array('-1',$_SESSION['black'],$enablingArr[0],$enablingArr[1],$enablingArr[2],$enablingArr[3],$enablingArr[4],
		$enablingArr[5],$enablingArr[6],$enablingArr[7],$enablingArr[8],$enablingArr[9],$enablingArr[10],$consoleAjax,$textboxAjax,'-1');
	}
	
	function newGameStarted()
	{
		$textboxAjax = '-1';
		$enablingArr = array();
		
		if ($_SESSION['white'] != 'WHITE' && $_SESSION['black'] != 'BLACK')
		{
			$textboxAjax = "Nowa gra rozpoczęta. Białe wykonują ruch."; 
			$enablingArr = enabling('newGame');
		}
		else $textboxAjax = "ERROR: game started when players aren't on chairs"; 
		
		return array('-1','-1',$enablingArr[0],$enablingArr[1],$enablingArr[2],$enablingArr[3],$enablingArr[4],
		$enablingArr[5],$enablingArr[6],$enablingArr[7],$enablingArr[8],$enablingArr[9],$enablingArr[10],'-1',$textboxAjax,'-1');
	}
	
	function moveRespond($coreAnswer)
	{
		$moveOk = substr($coreAnswer,0,4); 
		$_SESSION['turn'] = shortToFullTurnType(substr($coreAnswer,5,2));
		$gameStatus = substr($coreAnswer,8);
		
		$consoleAjax = 'moveOk = '. $moveOk .', S_turn = '.$_SESSION['turn'].', gameStatus = '.$gameStatus;
		$textboxAjax = '-1';
		$specialOption = '-1';
		$enablingArr = array();
		
		if ($gameStatus == "continue") 
		{
			$enablingArr = enabling('gameInProgress');
			$textboxAjax = gameInProgress($moveOk, $_SESSION['turn']);
		}
		else if ($gameStatus == "promote")
		{
			$specialOption = 'promote'; 
			$enablingArr = enabling('promote');
			$consoleAjax = $consoleAjax.', show promotion buttons window';
		}	
		else if ($gameStatus == "whiteWon" || $gameStatus == "blackWon" || $gameStatus == "draw") 
		{
			$enablingArr = enabling('endOfGame');
			$textboxAjax = endOfGame($moveOk, $gameStatus);
		}
		else $textboxAjax = 'ERROR: moveRespond(): unknown gameStatus value = '.$gameStatus;
		
		return array('-1','-1',$enablingArr[0],$enablingArr[1],$enablingArr[2],$enablingArr[3],$enablingArr[4],
		$enablingArr[5],$enablingArr[6],$enablingArr[7],$enablingArr[8],$enablingArr[9],$enablingArr[10],$consoleAjax,$textboxAjax,$specialOption);
	}
	
	function gameInProgress($move, $turn)
	{
		$textboxAjax = '-1';
		
		if ($turn == 'WHITE_TURN') $textboxAjax = 'Czarny wykonał ruch: '.$move.'. Ruch wykonują Białe.';
		else if ($turn == 'BLACK_TURN') $textboxAjax = 'Biały wykonał ruch: '.$move.'. Ruch wykonują Czarne.';
		else  $textboxAjax = 'ERROR. Unknown turn value = '.$turn;
		
		return $textboxAjax;
	}
	
	function endOfGame($checkmate, $endType)
	{
		$textboxAjax = '-1';
		
		if ($endType == "whiteWon") $textboxAjax = "Koniec gry: Białe wygrały wykonując ruch: ".$checkmate;
		else if($endType == "blackWon") $textboxAjax = "Koniec gry: Czarne wygrały wykonując ruch: ".$checkmate;
		else if($endType == "draw")	$textboxAjax = "Koniec gry: Remis";	// TODO: co dalej?  na kurniku obu graczy deklalure remis bodajże
		else $textboxAjax = "endOfGame(): ERROR: unknown parameter";
		
		return $textboxAjax;
	}
	
	function coreIsReady()
	{
		$enablingArr = array();
		$enablingArr = enabling('resetComplited');
		
		return array('-1','-1',$enablingArr[0],$enablingArr[1],$enablingArr[2],$enablingArr[3],$enablingArr[4],
		$enablingArr[5],$enablingArr[6],$enablingArr[7],$enablingArr[8],$enablingArr[9],$enablingArr[10],'-1','-1','-1');
	}
	
	function checked($whatWasChecked)
	{
		$enablingArr = array();
		$consoleAjax;
		$textboxAjax = '-1';
		
		if (substr($whatWasChecked,0,5) == 'WHITE')
		{ 
			$_SESSION['white'] = substr($whatWasChecked,6);
			$enablingArr = enabling('newWhite');
		}							
		else if (substr($whatWasChecked,0,5) == 'BLACK')
		{ 
			$_SESSION['black'] = substr($whatWasChecked,6);
			$enablingArr = enabling('newBlack');			
		}
		else if (substr($whatWasChecked,0,4) == 'Turn')
		{ 
			$_SESSION['turn'] = shortToFullTurnType(substr($whatWasChecked,5));
			
			if ($_SESSION['turn'] != 'NO_TURN') $enablingArr = enabling('gameInProgress');
			else $enablingArr = enabling('endOfGame');
			
			$consoleAjax = 'checked S_turn is = '.$_SESSION['turn'];
		}
		else if (substr($whatWasChecked,0,9) == 'TableData')
		{
			$tableData = explode(" ",$whatWasChecked);
			
			$_SESSION['white'] = $tableData[1];	
			
			$_SESSION['black'] = $tableData[2];
			
			$_SESSION['turn'] = shortToFullTurnType($tableData[3]);
			
			if ($_SESSION['turn'] != 'NO_TURN') $enablingArr = enabling('gameInProgress');
			else $enablingArr = enabling('endOfGame');
			
			$consoleAjax = 'checked S_turn is = '.$_SESSION['turn'];
		}
		else $consoleAjax = 'ERROR: unknown checked function parameter = '.$whatWasChecked;
		
		return array($_SESSION['white'],$_SESSION['black'],$enablingArr[0],$enablingArr[1],$enablingArr[2],$enablingArr[3],
		$enablingArr[4],$enablingArr[5],$enablingArr[6],$enablingArr[7],$enablingArr[8],$enablingArr[9],$enablingArr[10],$consoleAjax,$textboxAjax,'-1');
	}
	
	function promoted($promotedTo)
	{
		$consoleAjax;
		
		$promotingMove = substr($promotedTo,0,4);	
		$promotePiece = substr($promotedTo,5,1);
		$promoteType;
		switch($promotePiece)
		{
			case q: $promoteType = "hetmana"; break;
			case r: $promoteType = "wieżę"; break;
			case b: $promoteType = "gońca"; break;
			case k: $promoteType = "skoczka"; break	;
			default: $consoleAjax = 'ERROR. promoted(): Unknown $promotePiece var = '.$promotePiece; break;
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
			default: $consoleAjax = 'ERROR. promoted(): Unknown $gameStateAfterPromotion var = '. $gameStateAfterPromotion; break;
		}
		
		$textboxAjax = ($promoteTurn == "bt" ?  "Biały." : "Czarny.").' wykonał promocję piona ruchem)'
		.$promotingMove.' na '.$promoteType.'. '.$gameState;
		
		return array('-1','-1',$enablingArr[0],$enablingArr[1],$enablingArr[2],$enablingArr[3],
		$enablingArr[4],$enablingArr[5],$enablingArr[6],$enablingArr[7],$enablingArr[8],$enablingArr[9],$enablingArr[10],$consoleAjax,$textboxAjax,'-1');
	}
	
	function badMove($coreAnswer)
	{
		$consoleAjax = 'badMove: '.$coreAnswer;
		$badMove = substr($coreAnswer,0,4);
		$_SESSION['turn'] = shortToFullTurnType(substr($coreAnswer,5,2));
		
		$textboxAjax = "Błędne rządanie ruchu: ".$badMove."! Wpisz inny ruch.";
		$enablingArr = array();
		$enablingArr = enabling('badMove');
		
		return array('-1','-1',$enablingArr[0],$enablingArr[1],$enablingArr[2],$enablingArr[3],
		$enablingArr[4],$enablingArr[5],$enablingArr[6],$enablingArr[7],$enablingArr[8],$enablingArr[9],$enablingArr[10],$consoleAjax,$textboxAjax,'-1');
	}
	
	function timeOut($coreAnswer)
	{
		$consoleAjax = 'timeOut: '.$coreAnswer;	
		$textboxAjax = "Koniec gry. Upłynął czas gracza ".($coreAnswer == 'White' ?  "białego" : "czarnego").". Wygrywa ".($coreAnswer == 'Black' ?  "biały." : "czarny.");
		$enablingArr = array();
		$enablingArr = enabling('endOfGame');
		
		return array('-1','-1',$enablingArr[0],$enablingArr[1],$enablingArr[2],$enablingArr[3],
		$enablingArr[4],$enablingArr[5],$enablingArr[6],$enablingArr[7],$enablingArr[8],$enablingArr[9],$enablingArr[10],$consoleAjax,$textboxAjax,'-1');
	}
?>							