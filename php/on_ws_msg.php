<?
	if(!isset($_SESSION)) session_start();
	require_once('../disabling.php');
	
	/*$return = array( 'whiteName'=>'-1', 'blackName'=>'-1', 'consoleAjax'=>'-1', 'textboxAjax'=>'-1', 'specialOption'=>'-1',
		'consoleEnabling'=>'-1', 'textboxEnabling'=>'-1', 'whiteBtn'=>'-1', 'blackBtn'=>'-1', 'standWhite'=>'-1', 'standBlack'=>'-1', 'start'=>'-1', 'giveup'=>'-1', 'from'=>'-1', 'to'=>'-1', 'send'=>'-1', 'queuePlayer'=>'-1', 'leaveQueue'=>'-1',
	'queueMsg'=>'-1', 'queueList'=>'-1' );*/
	
	function shortToFullTurnType($shortTurn)
	{
		$fullTurn;
		if ($shortTurn == 'nt') $fullTurn = NO_TURN; 
		else if($shortTurn == 'wt') $fullTurn = WHITE_TURN;
		else if($shortTurn == 'bt') $fullTurn = BLACK_TURN;
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
		//$_SESSION['wtime']; //20 //todo: te ostatnie zienne są troche niepokolei w stosunku do pierwszych pięciu...
		//$_SESSION['btime']; //21 //...tak, to zrobiłem bo nie trzeba będzie aż tak doku zmieniać
		//$_SESSION['turn']; //22
		
		$_SESSION['wtime'] = -1;
		$_SESSION['btime'] = -1;
		$_SESSION['turn'] = -1;
		
		$consoleAjax = 'wsMsgType val = '.$wsMsgType;
		$enablingArr = array();
		
		switch ($wsMsgType)
		{		
			case 'newGameStarted':
			$_SESSION['turn'] = WHITE_TURN;
			if ($_SESSION['white'] != 'WHITE' && $_SESSION['black'] != 'BLACK') //todo: defined
			{
				$textboxAjax = "Nowa gra rozpoczęta. Białe wykonują ruch."; 
				$enablingArr = enabling('newGame');
			}
			else $textboxAjax = "ERROR: game started when players aren't on chairs"; 
			$_SESSION['wtime'] = 30*60;
			$_SESSION['btime'] = 30*60;
			break;
			
			case 'moveRespond':
			$moveOk = substr($wsMsgVal,0,4); 
			$_SESSION['turn'] = shortToFullTurnType(substr($wsMsgVal,5,2));
			
			$gameStatus = substr($wsMsgVal,8);
			if (strstr($gameStatus, " ")) $gameStatus = strstr($wsMsgVal, " ");
			
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
			else if (substr($gameStatus) == "whiteWon" || substr($gameStatus) == "blackWon" || substr($gameStatus) == "draw") 
			{				
				if (strpos($restOfMsg, "TABLE_DATA") !== false) 
				{
					$tableDataPos = strpos($tableData, "TABLE_DATA");
					$tableDataStart = strpos($tableDataPos, "{");
					$tableDataStop = strpos($tableDataPos, "}");
					$tableDataJSON = substr($tableDataPos, $tableDataStart-1, $tableDataStop+1);
					$tableDataArr = json_decode($tableDataJSON, true);

					if (array_key_exists("wplr", $tableDataArr)) $_SESSION['white'] = $tableDataArr["wplr"];
					if (array_key_exists("bplr", $tableDataArr)) $_SESSION['black'] = $tableDataArr["bplr"];
					if (array_key_exists("turn", $tableDataArr)) $_SESSION['turn'] = shortToFullTurnType($tableDataArr["turn"]);
					if (array_key_exists("wtime", $tableDataArr)) $_SESSION['wtime'] = floor($tableDataArr["wtime"]/1000);
					if (array_key_exists("btime", $tableDataArr)) $_SESSION['btime'] = floor($tableDataArr["btime"]/1000);
					if (array_key_exists("queue", $tableDataArr)) $_SESSION['queue'] = $tableDataArr["queue"];
					
					$queueList = $_SESSION['queue'];
					if ($_SESSION['queue'] != "queueEmpty") $queueMsg = " ";
					$enablingArr = enabling('endOfGame');
				}
				
				$textboxAjax = endOfGame($moveOk, $gameStatus);
			}
			else $textboxAjax = 'ERROR: moveRespond(): unknown gameStatus value = '.$gameStatus;
			break;
			
			case 'reseting': //todo: nie ma tu enabling?
			$_SESSION['turn'] = NO_TURN;
			$textboxAjax = "Resetowanie planszy...";
			break;
			
			case 'coreIsReady':
			$_SESSION['turn'] = NO_TURN;
			$enablingArr = enabling('resetComplited');
			break;
			
			case 'TABLE_DATA':		
			//$tableDataPos = strpos($wsMsgVal, "TABLE_DATA");
			$tableDataStart = strpos($wsMsgVal, "{");
			$tableDataStop = strpos($wsMsgVal, "}");
			$tableDataJSON = substr($wsMsgVal, $tableDataStart, $tableDataStop+1);
			$tableDataArr = json_decode($tableDataJSON, true);
			//$consoleAjax = "consoleAjax. data = " . $tableDataJSON; //var_dump($tableDataArr); //testy- podgląd
			
			if (array_key_exists("wplr", $tableDataArr)) $_SESSION['white'] = $tableDataArr["wplr"];
			if (array_key_exists("bplr", $tableDataArr)) $_SESSION['black'] = $tableDataArr["bplr"];
			if (array_key_exists("turn", $tableDataArr)) $_SESSION['turn'] = shortToFullTurnType($tableDataArr["turn"]);
			if (array_key_exists("wtime", $tableDataArr)) $_SESSION['wtime'] = floor($tableDataArr["wtime"]/1000);
			if (array_key_exists("btime", $tableDataArr)) $_SESSION['btime'] = floor($tableDataArr["btime"]/1000);
			if (array_key_exists("queue", $tableDataArr)) $_SESSION['queue'] = $tableDataArr["queue"];
			
			$queueList = $_SESSION['queue'];
			if ($_SESSION['queue'] != "queueEmpty") $queueMsg = " ";
			if ($_SESSION['turn'] != NO_TURN) $enablingArr = enabling('gameInProgress');
			else $enablingArr = enabling('endOfGame');	
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
		
		case 'updateQueue':
		$_SESSION['queue'] = $wsMsgVal; 
		$queueList = $_SESSION['queue'];
		if ( $_SESSION['white'] != WHITE && $_SESSION['black'] != BLACK) $queueMsg = "Zajęte miejsca przy stole gry. Zakolejkuj się by grać";
		else $queueMsg = "Kolejkowanie wyłączone: puste miejsca przy stole gry";
		if ($_SESSION['queue'] == "queueEmpty") $enablingArr = enabling('queueEmpty');
		else $enablingArr = enabling('queueNotEmpty');
		break;
		
		default: break; //todo
	}	
	
	return array( $_SESSION['white'], $_SESSION['black'], $consoleAjax, $textboxAjax, $specialOption, 
	$enablingArr[0], $enablingArr[1], $enablingArr[2], $enablingArr[3], $enablingArr[4], $enablingArr[5], $enablingArr[6], $enablingArr[7], $enablingArr[8], $enablingArr[9], $enablingArr[10], $enablingArr[11], $enablingArr[12],
	$queueMsg, $queueList, $_SESSION['wtime'], $_SESSION['btime'], $_SESSION['turn'] );
}

if(isset($_POST['wsMsg']))
{
	$rawWsg = $_POST['wsMsg'];
	$coreOption = '';
	$coreAnswer = '';
	
	if	($rawWsg == 'newOk') 						{ $return = onWsMsg("newGameStarted", ''); }
	else if	(substr($rawWsg,0,6) == 'moveOk') 		{ $return = onWsMsg("moveRespond", substr($rawWsg,7)); }
	else if ($rawWsg == 'reseting')					{ $return = onWsMsg("reseting", ''); }
	else if	($rawWsg == 'ready') 					{ $return = onWsMsg("coreIsReady", ''); }
	else if	(substr($rawWsg,0,10) == 'TABLE_DATA') 	{ $return = onWsMsg("TABLE_DATA", substr($rawWsg,10)); } 
	else if	(substr($rawWsg,0,8) == 'promoted') 	{ $return = onWsMsg("promoted", substr($rawWsg,9)); }
	else if	(substr($rawWsg,0,7) == 'badMove') 		{ $return = onWsMsg("badMove", substr($rawWsg,8)); }
	else $return['consoleAjax'] = 'ERROR. Unknown ws::onMessage value = '.$rawWsg; 
}

foreach($return as &$value) { if (is_null($value)) { $value = '-1'; }} unset($value);

header('Content-type: application/json; charset=utf-8"');
echo json_encode($return); //, JSON_UNESCAPED_UNICODE); //todo:- polskie znaki?
?>