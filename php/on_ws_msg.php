<?
	if(!isset($_SESSION)) session_start();
	require_once('../disabling.php');
	
	/*$return = array( 'whiteName'=>'-1', 'blackName'=>'-1', 'consoleAjax'=>'-1', 'textboxAjax'=>'-1', 'specialOption'=>'-1',
	'consoleEnabling'=>'-1', 'textboxEnabling'=>'-1', 'whiteBtn'=>'-1', 'blackBtn'=>'-1', 'standWhite'=>'-1', 'standBlack'=>'-1', 'start'=>'-1', 'giveup'=>'-1', 'from'=>'-1', 'to'=>'-1', 'send'=>'-1', 'queuePlayer'=>'-1', 'leaveQueue'=>'-1',
	'queueMsg'=>'-1', 'queueList'=>'-1' );*/
	
	function shortToFullTurnType($shortTurn)
	{
		$fullTurn;
		if ($shortTurn == 'nt') $fullTurn = 'NO_TURN'; 
		else if($shortTurn == 'wt') $fullTurn = 'WHITE_TURN';
		else if($shortTurn == 'bt') $fullTurn = 'BLACK_TURN';
		else $consoleAjax = 'ERROR: unknown turn type = '.$shortTurn;
		return $fullTurn;
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
	
	function gameInProgress($move, $turn)
	{
		$textboxAjax = '-1';
		
		if ($turn == 'WHITE_TURN') $textboxAjax = 'Czarny wykonał ruch: '.$move.'. Ruch wykonują Białe.';
		else if ($turn == 'BLACK_TURN') $textboxAjax = 'Biały wykonał ruch: '.$move.'. Ruch wykonują Czarne.';
		else  $textboxAjax = 'ERROR. Unknown turn value = '.$turn;
		
		return $textboxAjax;
	}
	
	function onWsMsg($wsMsgType, $wsMsgVal)
	{
		//$whiteName = '-1'; //0
		//$blackName = '-1'; //1
		$consoleAjax = '-1'; //2
		$textboxAjax = '-1'; //3
		$specialOption = '-1'; //4
		//$consoleEnabling = '-1'; //5, enabling[0]
		//$textboxEnabling = '-1'; //6, enabling[1]
		//$whiteBtn = '-1'; //7, enabling[2]
		//$blackBtn = '-1'; //8, enabling[3]
		//$standWhite = '-1'; //9, enabling[4]
		//$standBlack = '-1'; //10, enabling[5]
		//$start = '-1'; //11, enabling[6]
		//$giveup = '-1'; //12, enabling[7]
		//$from = '-1'; //13, enabling[8]
		//$to = '-1'; //14, enabling[9]
		//$send = '-1'; //15, enabling[10]
		//$queuePlayer = '-1'; //16, enabling[11]
		//$leaveQueue = '-1'; //17, enabling[12]
		$queueMsg = '-1'; //18
		$queueList = '-1'; //19
		
		$consoleAjax = 'wsMsgType val = '.$wsMsgType;
		$enablingArr = array();
		
		switch ($wsMsgType)
		{
			case 'newWhite':
			if ($wsMsgVal == 'WHITE') 
			{ 
				//todo: brakuje tu komunikatu dla WSZYSKITCH mówiącego, że gracz uciekł, a drugi wygrał
				$_SESSION['white'] = 'WHITE';
				$consoleAjax = 'white player = WHITE';
				$enablingArr = enabling('whiteEmpty');
			}
			else 
			{ 
				$_SESSION['white'] = $wsMsgVal;
				$consoleAjax = 'white player = '.$_SESSION['white'];
				$textboxAjax = 'Gracz figur białych: '.$_SESSION['white'];	
				$enablingArr = enabling('newWhite');
			}
			break;
			
			case 'newBlack':
			if ($wsMsgVal == 'BLACK') 
			{ 
				//todo: brakuje tu komunikatu dla WSZYSKITCH mówiącego, że gracz uciekł, a drugi wygrał
				$_SESSION['black'] = 'BLACK';
				$consoleAjax = 'black player = BLACK'; 
				$enablingArr = enabling('blackEmpty');
			}
			else 
			{ 
				$_SESSION['black'] = $wsMsgVal; 
				$consoleAjax = 'black player = '.$_SESSION['black']; 
				$textboxAjax = 'Gracz figur czarnych: '.$_SESSION['black']; 
				$enablingArr = enabling('newBlack');
			}
			break;
			
			case 'newGameStarted':
			if ($_SESSION['white'] != 'WHITE' && $_SESSION['black'] != 'BLACK')
			{
				$textboxAjax = "Nowa gra rozpoczęta. Białe wykonują ruch."; 
				$enablingArr = enabling('newGame');
			}
			else $textboxAjax = "ERROR: game started when players aren't on chairs"; 
			break;
			
			case 'moveRespond':
			$moveOk = substr($wsMsgVal,0,4); 
			$_SESSION['turn'] = shortToFullTurnType(substr($wsMsgVal,5,2));
			$gameStatus = substr($wsMsgVal,8);
			$consoleAjax = 'moveOk = '. $moveOk .', S_turn = '.$_SESSION['turn'].', gameStatus = '.$gameStatus;
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
			break;
			
			case 'reseting': //todo: nie ma tu enabling?
			$textboxAjax = "Resetowanie planszy...";
			break;
			
			case 'coreIsReady':
			$enablingArr = enabling('resetComplited');
			break;
			
			case 'checked':
			if (substr($wsMsgVal,0,5) == 'WHITE')
			{ 
				$_SESSION['white'] = substr($wsMsgVal,6);
				$enablingArr = enabling('newWhite');
			}							
			else if (substr($wsMsgVal,0,5) == 'BLACK')
			{ 
				$_SESSION['black'] = substr($wsMsgVal,6);
				$enablingArr = enabling('newBlack');			
			}
			else if (substr($wsMsgVal,0,4) == 'Turn')
			{ 
				$_SESSION['turn'] = shortToFullTurnType(substr($wsMsgVal,5));
				if ($_SESSION['turn'] != 'NO_TURN') $enablingArr = enabling('gameInProgress');
				else $enablingArr = enabling('endOfGame');
				$consoleAjax = 'checked S_turn is = '.$_SESSION['turn'];
			}
			else if (substr($wsMsgVal,0,9) == 'TableData')
			{
				$tableData = explode(" ",$wsMsgVal);
				$arrElements = count($tableData);
				if ($arrElements == 5)
				{
					$_SESSION['white'] = $tableData[1];	
					$_SESSION['black'] = $tableData[2];
					$_SESSION['turn'] = shortToFullTurnType($tableData[3]);
					$_SESSION['queue'] = $tableData[4]; 
					$queueList = $_SESSION['queue'];
					if ($queueList != "queueEmpty") $queueMsg = " ";
					if ($_SESSION['turn'] != 'NO_TURN') $enablingArr = enabling('gameInProgress');
					else $enablingArr = enabling('endOfGame');
					$consoleAjax = 'checked S_turn is = '.$_SESSION['turn'];
				}
				else $consoleAjax = 'ERROR: wrong number of array elements: '.$arrElements;
			}
			else $consoleAjax = 'ERROR: unknown checked function parameter = '.$wsMsgVal;
			break;
			
			case 'promoted': //todo: nie ma tu enabling?
			$promotingMove = substr($wsMsgVal,0,4);	
			$promotePiece = substr($wsMsgVal,5,1);
			$promoteType;
			switch($promotePiece)
			{
				case q: $promoteType = "hetmana"; break;
				case r: $promoteType = "wieżę"; break;
				case b: $promoteType = "gońca"; break;
				case k: $promoteType = "skoczka"; break	;
				default: $consoleAjax = 'ERROR. promoted(): Unknown $promotePiece var = '.$promotePiece; break;
			}
			$promoteTurn = substr($wsMsgVal,7,2); 
			$gameStateAfterPromotion = substr($wsMsgVal,10);
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
			break;
			
			case 'badMove':
			$consoleAjax = 'badMove: '.$wsMsgVal;
			$badMove = substr($wsMsgVal,0,4);
			$_SESSION['turn'] = shortToFullTurnType(substr($wsMsgVal,5,2));
			$textboxAjax = "Błędne rządanie ruchu: ".$badMove."! Wpisz inny ruch.";
			$enablingArr = enabling('badMove');
			break;
			
			case 'timeOut':
			$consoleAjax = 'timeOut: '.$wsMsgVal;	
			$textboxAjax = "Koniec gry. Upłynął czas gracza ".($wsMsgVal == 'White' ?  "białego" : "czarnego").". Wygrywa ".($wsMsgVal == 'Black' ?  "biały." : "czarny.");
			$enablingArr = enabling('endOfGame');
			break;
			
			default: break; //todo
		}	
		
		return array( $_SESSION['white'], $_SESSION['black'], $consoleAjax, $textboxAjax, $specialOption, 
		$enablingArr[0], $enablingArr[1], $enablingArr[2], $enablingArr[3], $enablingArr[4], $enablingArr[5], $enablingArr[6], $enablingArr[7], $enablingArr[8], $enablingArr[9], $enablingArr[10], $enablingArr[11], $enablingArr[12],
		$queueMsg, $queueList );
	}
	
	if(isset($_POST['wsMsg']))
	{
		$rawWsg = $_POST['wsMsg'];
		$coreOption = '';
		$coreAnswer = '';
		
		if 		(substr($rawWsg,0,8) == 'newWhite')	{ $return = onWsMsg("newWhite", substr($rawWsg,9)); }
		else if (substr($rawWsg,0,8) == 'newBlack') { $return = onWsMsg("newBlack", substr($rawWsg,9)); }
		else if	($rawWsg == 'newOk') 				{ $return = onWsMsg("newGameStarted", ''); }
		else if	(substr($rawWsg,0,6) == 'moveOk') 	{ $return = onWsMsg("moveRespond", substr($rawWsg,7)); }
		else if ($rawWsg == 'reseting')				{ $return = onWsMsg("reseting", ''); }
		else if	($rawWsg == 'ready') 				{ $return = onWsMsg("coreIsReady", ''); }
		else if	(substr($rawWsg,0,7) == 'checked') 	{ $return = onWsMsg("checked", substr($rawWsg,7)); }
		else if	(substr($rawWsg,0,8) == 'promoted') { $return = onWsMsg("promoted", substr($rawWsg,9)); }
		else if	(substr($rawWsg,0,7) == 'badMove') 	{ $return = onWsMsg("badMove", substr($rawWsg,8)); }
		else if	(substr($rawWsg,0,7) == 'timeOut') 	{ $return = onWsMsg("timeOut", substr($rawWsg,7)); }
		else $return['consoleAjax'] = 'ERROR. Unknown ws::onMessage value = '.$rawWsg; 
	}
	
	foreach($return as &$value) { if (is_null($value)) { $value = '-1'; }} unset($value);
	
	header('Content-type: application/json; charset=utf-8"');
	echo json_encode($return); //, JSON_UNESCAPED_UNICODE); - polskie znaki?
?>