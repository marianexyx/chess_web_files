<?
	define('NONE', 'None');
	define('WHITE', 'White');
	define('BLACK', 'Black');
	
	if(!isset($_SESSION)) session_start(); //todo: jeżeli sesji by tu sesji jeszcze nie było, to chyba nic by mi nie działało i tak, więc po co to? chyba...
	//... po to, żeby plik ten miał dostęp do zmiennych sesyjnych, bo z automatu ich by nie miał
		
	function enabling()
	{
		$clientIsLogged = !empty($_SESSION['id']);
		$loggedPlayerIsOnAnyChair = false; //todo: to ma robić we frontEndzie jako zmienna sprawdzająca także dla standUp
		$loggedPlayerIsOnWhiteChair = false;
		$loggedPlayerIsOnBlackChair = false;
		$tableIsFull = false;
		$clientIsInQueue = false;
		$whitePlayerBtn = false;
		$blackPlayerBtn = false;
		$playerCanMakeMove = false;
		$queuePlayerBtn = false;
		$leaveQueueBtn = false;
			
		if ($clientIsLogged)
		{
			$loggedPlayerIsOnAnyChair = isLoggedPlayerOnAnyChair();
			$tableIsFull = are2playersAreOnChairs();
			$clientIsInQueue = isClientInQueue();
			$loggedPlayerIsOnWhiteChair = isLoggedPlayerOnChair(WHITE);
			$loggedPlayerIsOnBlackChair = isLoggedPlayerOnChair(BLACK);
			$playerCanMakeMove = isPlayerAllowedToMakeMove();
			if (isChairEmpty(WHITE) && !isLoggedPlayerOnChair(BLACK)) $whitePlayerBtn = true;
			if (isChairEmpty(BLACK) && !isLoggedPlayerOnChair(WHITE)) $blackPlayerBtn = true;
			if (are2playersAreOnChairs() && !isLoggedPlayerOnAnyChair() && !isClientInQueue()) $queuePlayerBtn = true;
			else if (isClientInQueue()) $leaveQueueBtn = true;
			
			//todo: usunąć w następnym commicie
			/*$_SESSION['consoleAjax'] .= 'disabling(): are2playersAreOnChairs() ='.(($tableIsFull)?'true':'false')
			.', isLoggedPlayerOnAnyChair() ='.(($loggedPlayerIsOnAnyChair)?'true':'false').', isClientInQueue() ='
			.(($clientIsInQueue)?'true':'false').', isLoggedPlayerOnChair(WHITE) ='.(($loggedPlayerIsOnWhiteChair)?'true':'false')
			.', isLoggedPlayerOnChair(BLACK) ='.(($loggedPlayerIsOnBlackChair)?'true':'false').', $_SESSION["queue"] ='
			.$_SESSION['queue'].', $_SESSION["login"] ='.$_SESSION['login'].', isChairEmpty(WHITE) = '
			.((isChairEmpty(WHITE))?'true':'false').', isLoggedPlayerOnChair(BLACK) = '.((isLoggedPlayerOnChair(BLACK))?'true':'false')
			.', $whitePlayerBtn = '.(($whitePlayerBtn)?'true':'false');*/
		}
		else $_SESSION['consoleAjax'] .= 'ERROR: Empty session ID- client isnt logged | ';
		
		return array
		(
			"clientIsLogged" => $clientIsLogged,
			"loggedPlayerIsOnAnyChair" => $loggedPlayerIsOnAnyChair,
			"loggedPlayerIsOnWhiteChair" => $loggedPlayerIsOnWhiteChair,
			"loggedPlayerIsOnBlackChair" => $loggedPlayerIsOnBlackChair,
			"tableIsFull" => $tableIsFull,
			"clientIsInQueue" => $clientIsInQueue,
			"whitePlayerBtn" => $whitePlayerBtn,
			"blackPlayerBtn" => $blackPlayerBtn,
			"playerCanMakeMove" => $playerCanMakeMove,
			"queuePlayerBtn" => $queuePlayerBtn,
			"leaveQueueBtn" => $leaveQueueBtn
		);
	}
		
	function isChairEmpty($playerType)
	{
		$chair = false;
		if ($playerType == WHITE) 
		{
			if ($_SESSION['whitePlayer'] == "-1") $chair = true;
			else $chair = false;
		}
		else if ($playerType == BLACK) 
		{
			if ($_SESSION['blackPlayer'] == "-1") $chair = true;
			else $chair = false;
		}
		else $_SESSION['consoleAjax'] .= 'ERROR: function isChairEmpty(): unknown playerType: '.$playerType.' | ';
		
		return $chair;
	}
	
	function isLoggedPlayerOnChair($playerType)
	{
		$loggedPlayerOnChair = false;
		if ($playerType == WHITE) 
		{
			if ($_SESSION['whitePlayer'] == $_SESSION['login']) $loggedPlayerOnChair = true;
			else $loggedPlayerOnChair = false;
		}
		else if ($playerType == BLACK) 
		{
			if ($_SESSION['blackPlayer'] == $_SESSION['login']) $loggedPlayerOnChair = true;
			else $loggedPlayerOnChair = false;
		}
		else $_SESSION['consoleAjax'] .= 'ERROR: function isLoggedPlayerOnChair(): unknown playerType: '.$playerType.' | ';
		
		return $loggedPlayerOnChair;
	}
	
	function isLoggedPlayerOnAnyChair()
	{
		if (isLoggedPlayerOnChair(WHITE) || isLoggedPlayerOnChair(BLACK)) return true;
		else return false;
	}
	
	function are2playersAreOnChairs()
	{
		if (!isChairEmpty(WHITE) && !isChairEmpty(BLACK)) return true;
		else return false;
	}
	
	function isGameInProgress()
	{
		if ($_SESSION['turn'] == WHITE_TURN || $_SESSION['turn'] == BLACK_TURN) return true;
		else return false;
	}
	
	function isClientInQueue()
	{
		$queueTempArr = explode(",", $_SESSION['queue']);
		if (in_array($_SESSION['login'], $queueTempArr)) 
			return true; 
		else return false;
	}
	
	function isPlayerAllowedToMakeMove()
	{		
		if (isGameInProgress)
		{
			if ($_SESSION['turn'] == WHITE_TURN && isLoggedPlayerOnChair(WHITE)) return true;
			else if ($_SESSION['turn'] == BLACK_TURN && isLoggedPlayerOnChair(BLACK)) return true;
			else return false;
		}
		else return false;
	}
?>							