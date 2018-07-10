<?
	if(!isset($_SESSION)) session_start();
	require_once('disabling.php');
	require_once('include/inc.php');
	
	function extractTurnType($shortTurn)
	{
		$fullTurn;
		if ($shortTurn == 'nt') $fullTurn = NO_TURN; 
		else if($shortTurn == 'wt') $fullTurn = WHITE_TURN;
		else if($shortTurn == 'bt') $fullTurn = BLACK_TURN;
		else $fullTurn = 'ERROR: unknown turn type = '.$shortTurn;
		
		return $fullTurn;
	} 
	
	function endOfGame($checkmate, $endType)
	{
		$_SESSION['textboxAjax'] = '-1';
		
		if ($endType == "wWon") $_SESSION['textboxAjax'] = "Koniec gry: Białe wygrały wykonując ruch: ".$checkmate;
		else if($endType == "bWon") $_SESSION['textboxAjax'] = "Koniec gry: Czarne wygrały wykonując ruch: ".$checkmate;
		else if($endType == "draw")	$_SESSION['textboxAjax'] = "Koniec gry: Remis";	// TODO: co dalej?  na kurniku obu graczy deklalure remis bodajże
		else $_SESSION['textboxAjax'] = "endOfGame(): ERROR: unknown parameter";
		
		$_SESSION['textboxAjax'] = $_SESSION['textboxAjax'].". Resetowanie planszy...";
		
		return $_SESSION['textboxAjax'];
	}
	
	function gameInProgress($move, $turn)
	{
		$_SESSION['textboxAjax'] = '-1';
		
		if ($turn == 'WHITE_TURN') $_SESSION['textboxAjax'] = 'Czarny wykonał ruch: '.$move.'. Ruch wykonują Białe.';
		else if ($turn == 'BLACK_TURN') $_SESSION['textboxAjax'] = 'Biały wykonał ruch: '.$move.'. Ruch wykonują Czarne.';
		else  $_SESSION['textboxAjax'] = 'ERROR. Unknown turn value = '.$turn;
		
		return $_SESSION['textboxAjax'];
	}
		
	function tableDataStringToSession($tableDataString)
	{
		$TABLE_DATA = array(
			"NONE" => "0x00",
			"ACTION" => "0x01",
			"WHITE_PLAYER" => "0x02",
			"BLACK_PLAYER" => "0x03",
			"GAME_STATE" => "0x04",
			"WHITE_TIME" => "0x05",
			"BLACK_TYPE" => "0x06",
			"QUEUE" => "0x07",
			"START_TIME" => "0x08",
			"HISTORY" => "0x09",
			"PROMOTIONS" => "0x0a",
			"ERROR" => "0x0b",
		);
		
		$tableDataStart = strpos($tableDataString, "{");
		$tableDataStop = strpos($tableDataString, "}");
		$tableDataJSON = substr($tableDataString, $tableDataStart, $tableDataStop+1);
		$tableDataArr = json_decode($tableDataJSON, true);
		
		if (array_key_exists($TABLE_DATA["WHITE_PLAYER"], $tableDataArr))
		{			
			$whiteId = $tableDataArr[$TABLE_DATA["WHITE_PLAYER"]];
			if ($whiteId == "0") $_SESSION['white'] = $whiteId;
			else
			{
				$queryWhite = row("SELECT * FROM users WHERE id = '$whiteId'");
				if ($queryWhite) $_SESSION['white'] = $queryWhite['login']; 
				else $_SESSION['white'] = "<ERROR>";
			}
		}
		if (array_key_exists($TABLE_DATA["BLACK_PLAYER"], $tableDataArr))
		{
			$blackId = $tableDataArr[$TABLE_DATA["BLACK_PLAYER"]];
			if ($blackId == "0") $_SESSION['black'] = $blackId;
			else
			{
				$queryBlack = row("SELECT * FROM users WHERE id = ".$blackId);
				if ($queryBlack) $_SESSION['black'] = $queryBlack['login']; 
				else $_SESSION['black'] = "<ERROR>";
			}
		}
		if (array_key_exists($TABLE_DATA["GAME_STATE"], $tableDataArr)) $_SESSION['turn'] = extractTurnType($tableDataArr[$TABLE_DATA["GAME_STATE"]]);
		if (array_key_exists($TABLE_DATA["WHITE_TIME"], $tableDataArr)) $_SESSION['wtime'] = floor($tableDataArr[$TABLE_DATA["WHITE_TIME"]]/1000);
		if (array_key_exists($TABLE_DATA["BLACK_TYPE"], $tableDataArr)) $_SESSION['btime'] = floor($tableDataArr[$TABLE_DATA["BLACK_TYPE"]]/1000);
		if (array_key_exists($TABLE_DATA["QUEUE"], $tableDataArr)) $_SESSION['queue'] = $tableDataArr[$TABLE_DATA["QUEUE"]];
		if (array_key_exists($TABLE_DATA["START_TIME"], $tableDataArr)) //np.: "start":"wb92"
		{
			$_SESSION['wstart'] = substr($tableDataArr[$TABLE_DATA["START_TIME"]], 0, 1); //w or x
			$_SESSION['bstart'] = substr($tableDataArr[$TABLE_DATA["START_TIME"]], 1, 1); //b or x
			$_SESSION['stime'] = floor(substr($tableDataArr[$TABLE_DATA["START_TIME"]], 2)/1000); //int
		}
		if (array_key_exists($TABLE_DATA["HISTORY"], $tableDataArr)) $_SESSION['history'] = $tableDataArr[$TABLE_DATA["HISTORY"]];
		if (array_key_exists($TABLE_DATA["PROMOTIONS"], $tableDataArr)) $_SESSION['promoted'] = $tableDataArr[$TABLE_DATA["PROMOTIONS"]];
	}
	
	function onWsMsg($wsMsgType, $wsMsgVal)
	{
		//todo: zwracać też może informację, która będzie dawała znać jakim typem użtkownika jesteś (logged/white/black itd)
		//$whiteName = '-1'; //0
		//$blackName = '-1'; //1
		//$_SESSION['consoleAjax'] = '-1'; //2
		//$_SESSION['textboxAjax'] = '-1'; //3
		$specialOption = '-1'; //4
		//$consoleEnabling = '-1'; //5, enabling[0]
		//$textboxEnabling = '-1'; //6, enabling[1]
		//$whiteBtn = '-1'; //7, enabling[2]
		//$blackBtn = '-1'; //8, enabling[3]
		//$standUpWhite = '-1'; //9, enabling[4]
		//$standUpBlack = '-1'; //10, enabling[5]
		//$start = '-1'; //11, enabling[6]
		//$giveup = '-1'; //12, enabling[7]
		//$from = '-1'; //13, enabling[8]
		//$to = '-1'; //14, enabling[9]
		//$send = '-1'; //15, enabling[10]
		//$queuePlayer = '-1'; //16, enabling[11]
		//$leaveQueue = '-1'; //17, enabling[12]
		$_SESSION['queue'] = '-1'; //18
		//$_SESSION['wtime']; //19 //todo: te ostatnie zienne są troche niepokolei w stosunku do pierwszych pięciu...
		//$_SESSION['btime']; //20 //...tak, to zrobiłem bo nie trzeba będzie aż tak doku zmieniać
		//$_SESSION['turn']; //21
		//$_SESSION['wstart']; //22
		//$_SESSION['bstart']; //23
		//$_SESSION['stime']; //24
		//$_SESSION['history']; //25
		//$_SESSION['promoted']; //26
		
		$_SESSION['wtime'] = -1;
		$_SESSION['btime'] = -1;
		$_SESSION['turn'] = -1;
		$_SESSION['wstart'] = -1;
		$_SESSION['bstart'] = -1;
		$_SESSION['stime'] = -1;
		$_SESSION['history'] = -1;
		$_SESSION['promoted']= -1;
		
		$_SESSION['consoleAjax'] = 'wsMsgType val = '.$wsMsgType;
		$enablingArr = array();
		
		switch ($wsMsgType)
		{		
			case 'newGameStarted':
			$_SESSION['turn'] = WHITE_TURN;
			if ($_SESSION['white'] != WHITE && $_SESSION['black'] != BLACK)
			{
				$_SESSION['textboxAjax'] = "Nowa gra rozpoczęta. Białe wykonują ruch."; 
				$enablingArr = enabling('newGame');
			}
			else $_SESSION['textboxAjax'] = "ERROR: game started when players aren't on chairs"; 
			$_SESSION['wtime'] = 30*60;
			$_SESSION['btime'] = 30*60;
			break;
			
			case 'moveRespond':
			$moveOk = substr($wsMsgVal,0,4); 
			$_SESSION['turn'] = extractTurnType(substr($wsMsgVal,5,2));
			
			$gameStatus = substr($wsMsgVal,8,4);
			if (strstr($gameStatus, " ")) $gameStatus = strstr($wsMsgVal, " ");
			
			$optionalRestOfMsg = substr($wsMsgVal,13);
			
			$_SESSION['consoleAjax'] = 'moveOk = '.$moveOk .', turn = '.$_SESSION['turn'].', gameStatus = '.$gameStatus;
			
			if ($gameStatus == "cont") 
			{
				$enablingArr = enabling('gameInProgress');
				$_SESSION['textboxAjax'] = gameInProgress($moveOk, $_SESSION['turn']);
			}
			else if ($gameStatus == "wWon" || $gameStatus == "bWon" || $gameStatus == "draw") 
			{				
				if (strpos($optionalRestOfMsg, "TABLE_DATA") !== false) 
					tableDataStringToSession($wsMsgVal);
				$enablingArr = enabling('endOfGame');
				$_SESSION['textboxAjax'] = endOfGame($moveOk, $gameStatus);
			}
			else $_SESSION['textboxAjax'] = 'ERROR: moveRespond(): unknown gameStatus value = '.$gameStatus;
			break;
			
			case 'resetComplited':
			$_SESSION['turn'] = NO_TURN;
			if (strpos($wsMsgVal, "TABLE_DATA") !== false) 
				tableDataStringToSession($wsMsgVal);
			$enablingArr = enabling('resetComplited');
			break;
			
			case 'promoteToWhat':		
			$specialOption = 'promote'; 
			$enablingArr = enabling('promote');
			$_SESSION['consoleAjax'] = $_SESSION['consoleAjax'].', show promotion buttons window';
			break;
		
			case 'TABLE_DATA':					
			tableDataStringToSession($wsMsgVal);
			if ($_SESSION['turn'] != NO_TURN) $enablingArr = enabling('gameInProgress');
			else $enablingArr = enabling('endOfGame');	
			break;
			
			case 'promoted': 
			$promotingMove = substr($wsMsgVal,0,4);	
			$promotePiece = substr($wsMsgVal,5,1);
			$promoteType;
			switch($promotePiece)
			{
				case q: $promoteType = "hetmana"; break;
				case r: $promoteType = "wieżę"; break;
				case b: $promoteType = "gońca"; break;
				case n: $promoteType = "skoczka"; break	;
				default: $_SESSION['consoleAjax'] = 'ERROR. promoted(): Unknown $promotePiece var = '.$promotePiece; break;
			}
			$promoteTurn = substr($wsMsgVal,7,2); 
			$gameStateAfterPromotion = substr($wsMsgVal,10);
			$gameState;
			switch($gameStateAfterPromotion)
			{
				case 'continue': $gameState = "Ruch wykonuje ". ($promoteTurn == 'bt' ?  "Biały." : "Czarny."); break;
				case 'whiteWon': $gameState = "Koniec gry. Białe wygrały. Resetowanie planszy..."; break;
				case 'blackWon': $gameState = "Koniec gry. Czarne wygrały. Resetowanie planszy..."; break;
				case 'draw': $gameState = "Koniec gry. Remis. Resetowanie planszy..."; break;
				default: $_SESSION['consoleAjax'] = 'ERROR. promoted(): Unknown $gameStateAfterPromotion var = '. $gameStateAfterPromotion; break;
			}

			$_SESSION['textboxAjax'] = ($promoteTurn == "bt" ?  "Biały." : "Czarny.").' wykonał promocję piona ruchem)'
			.$promotingMove.' na '.$promoteType.'. '.$gameState;
			break;
			
			case 'badMove':
			$_SESSION['consoleAjax'] = 'badMove: '.$wsMsgVal;
			$badMove = substr($wsMsgVal,0,4);
			$_SESSION['turn'] = extractTurnType(substr($wsMsgVal,5,2));
			$_SESSION['textboxAjax'] = "Błędne rządanie ruchu: ".$badMove."! Wpisz inny ruch.";
			$enablingArr = enabling('badMove');
			break;
			
			case 'updateQueue':
			$_SESSION['queue'] = $wsMsgVal; 
			if ($_SESSION['queue'] == "queueEmpty") $enablingArr = enabling('queueEmpty');
			else $enablingArr = enabling('queueNotEmpty');
			break;
			
			//todo: poniższe funkcje prawie niczym się nie różnią
			case 'giveUp':
			$_SESSION['turn'] = NO_TURN;
			$whoGaveUp = substr($wsMsgVal,0,5);
			$optionalRestOfMsg = substr($wsMsgVal,5);
			if ($whoGaveUp == WHITE) $_SESSION['textboxAjax'] = "Koniec gry: Białe się poddały. Czarne wygrały.";
			else if ($whoGaveUp == BLACK) $_SESSION['textboxAjax'] = "Koniec gry: Czarne się poddały. Białe wygrały.";
			else $_SESSION['consoleAjax'] = "ERROR: undefined giving up player type";
			$_SESSION['textboxAjax'] = $_SESSION['textboxAjax'].". Resetowanie planszy...";
			if (strpos($optionalRestOfMsg, "TABLE_DATA") !== false) 
				tableDataStringToSession($wsMsgVal);
			$enablingArr = enabling('endOfGame');
			break;
			
			case 'socketLost':
			$_SESSION['turn'] = NO_TURN;
			$whoGaveUp = substr($wsMsgVal,0,5);
			$optionalRestOfMsg = substr($wsMsgVal,5);
			if ($whoGaveUp == WHITE) $_SESSION['textboxAjax'] = "Koniec gry: Białe się rozłączyły. Czarne wygrały.";
			else if ($whoGaveUp == BLACK) $_SESSION['textboxAjax'] = "Koniec gry: Czarne się rozłączyły. Białe wygrały.";
			else $_SESSION['consoleAjax'] = "ERROR: undefined socket lost player type";
			$_SESSION['textboxAjax'] = $_SESSION['textboxAjax'].". Resetowanie planszy...";
			if (strpos($optionalRestOfMsg, "TABLE_DATA") !== false) 
				tableDataStringToSession($wsMsgVal);
			$enablingArr = enabling('endOfGame');
			break;
			
			case 'timeOut':
			$_SESSION['turn'] = NO_TURN;
			$whoGaveUp = substr($wsMsgVal,0,5);
			$optionalRestOfMsg = substr($wsMsgVal,5);
			if ($whoGaveUp == WHITE) $_SESSION['textboxAjax'] = "Koniec gry: Koniec czasu białego. Czarne wygrały.";
			else if ($whoGaveUp == BLACK) $_SESSION['textboxAjax'] = "Koniec gry: Koniec czasu czarnego. Białe wygrały.";
			else $_SESSION['consoleAjax'] = "ERROR: undefined time out player type";
			$_SESSION['textboxAjax'] = $_SESSION['textboxAjax'].". Resetowanie planszy...";
			if (strpos($optionalRestOfMsg, "TABLE_DATA") !== false) 
				tableDataStringToSession($wsMsgVal);
			$enablingArr = enabling('endOfGame');
			break;
			
			//todo: jeżeli poniższy przypadek załatwie przy użyciu tylko table data, to mogę go usunąć
			case 'history':
			$_SESSION['history'] = $wsMsgVal;
			break;
			
			
			default: 
			$_SESSION['consoleAjax'] = "ERROR: undefined msg type from core: ".$wsMsgType;
			break; 
		}
		
		//todo: naprawić zwracanie tablicy- niech zwraca tylko te wartości, które są (i mogą) być zwracane. trzeba przywrócić key arraye. js powinien wyłapywać tylko te zmienne które przyjdą.
		$returnArray = array($_SESSION['white'], $_SESSION['black'], $_SESSION['consoleAjax'], $_SESSION['textboxAjax'], $specialOption, 
		$enablingArr[0], $enablingArr[1], $enablingArr[2], $enablingArr[3], $enablingArr[4], $enablingArr[5], $enablingArr[6], $enablingArr[7], $enablingArr[8], $enablingArr[9], $enablingArr[10], $enablingArr[11], 
		$enablingArr[12], $_SESSION['queue'], $_SESSION['wtime'], $_SESSION['btime'], $_SESSION['turn'], $_SESSION['wstart'], $_SESSION['bstart'], $_SESSION['stime'], $_SESSION['history'], $_SESSION['promoted']);
		$_SESSION['consoleAjax'] = '-1'; 
		$_SESSION['textboxAjax'] = '-1'; 
		return $returnArray;
	}
	
	if(isset($_POST['wsMsg']))
	{
		$rawWsg = $_POST['wsMsg'];
		$coreOption = '';
		$coreAnswer = '';
		
		if	($rawWsg == 'newOk') 							{ $return = onWsMsg("newGameStarted", ''); }
		else if	(substr($rawWsg,0,6) == 'moveOk') 			{ $return = onWsMsg("moveRespond", substr($rawWsg,7)); }
		else if ($rawWsg == 'promoteToWhat')				{ $return = onWsMsg("promoteToWhat", ''); }
		else if	(substr($rawWsg,0,14) == 'resetComplited')	{ $return = onWsMsg("resetComplited", substr($rawWsg,15)); } 
		else if	(substr($rawWsg,0,10) == 'TABLE_DATA') 		{ $return = onWsMsg("TABLE_DATA", substr($rawWsg,10)); } 
		else if	(substr($rawWsg,0,8) == 'promoted') 		{ $return = onWsMsg("promoted", substr($rawWsg,9)); }
		else if	(substr($rawWsg,0,7) == 'badMove') 			{ $return = onWsMsg("badMove", substr($rawWsg,8)); }
		else if	(substr($rawWsg,0,6) == 'giveUp') 			{ $return = onWsMsg("giveUp", substr($rawWsg,6)); }
		else if	(substr($rawWsg,0,10) == 'socketLost') 		{ $return = onWsMsg("socketLost", substr($rawWsg,10)); }
		else if	(substr($rawWsg,0,7) == 'timeOut') 			{ $return = onWsMsg("timeOut", substr($rawWsg,7)); }
		else if	(substr($rawWsg,0,7) == 'history') 			{ $return = onWsMsg("history", substr($rawWsg,8)); }
		else $return['consoleAjax'] = 'ERROR. Unknown ws::onMessage value = '.$rawWsg; 
	}
	
	foreach($return as &$value) { if (is_null($value)) { $value = '-1'; }} unset($value);
	
	header('Content-type: application/json; charset=utf-8"');
	echo json_encode($return); //, JSON_UNESCAPED_UNICODE); //todo:- polskie znaki?
?>