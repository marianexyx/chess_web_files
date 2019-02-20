<?
	define('NONE', 'None');
	define('WHITE', 'White');
	define('BLACK', 'Black');
	
	if(!isset($_SESSION)) 
		session_start(); 
		
	function calculateDataFromSessionVars()
	{	
		global $LOGGED_TYPE;
		global $ACTION_TYPE;
		global $GAME_STATE;
	
		$loggedPlayerIsOnAnyChair = false;
		$loggedPlayerIsOnWhiteChair = false;
		$loggedPlayerIsOnBlackChair = false;
		$tableIsFull = false;
		$clientIsInQueue = false;
		$clientIsLogged = false;
		$whitePlayerBtn = false;
		$blackPlayerBtn = false;
		$playerCanMakeMove = false;
		$queuePlayerBtn = false;
		$leaveQueueBtn = false;
		$specialOption = '-1';
		
		$tableIsFull = areBothPlayersOnChairs(); //only logged players can make use of this flag, but maybe in future this might be usable, so always return this proper value to everyone
		
		if (intval($_SESSION['ID']) > 0)
			$clientIsLogged = true;
		if (isChairEmpty(WHITE) && !isLoggedPlayerOnChair(BLACK)) //works for guests also
			$whitePlayerBtn = true;
		if (isChairEmpty(BLACK) && !isLoggedPlayerOnChair(WHITE)) //works for guests also
			$blackPlayerBtn = true;
		if ($_SESSION['action'] == $ACTION_TYPE["NEW_WHITE_PLAYER"] || $_SESSION['action'] == $ACTION_TYPE["NEW_BLACK_PLAYER"])
			printNewPlayerName();
		if ($_SESSION['gameState'] == $GAME_STATE["NEW_GAME_STARTED"]) 
			$specialOption = 'newGameStarted'; 
		else if (isGameStateAnEndType()) 
			$specialOption = 'endOfGame';
		
		if ($clientIsLogged)
		{
			if (isLoggedPlayerOnAnyChair())
			{
				$loggedPlayerIsOnAnyChair = true;
				if (isLoggedPlayerOnChair(WHITE))
					$loggedPlayerIsOnWhiteChair = true;
				else if (isLoggedPlayerOnChair(BLACK))
					$loggedPlayerIsOnBlackChair = true;
				if (isPlayerAllowedToMakeMove())
					$playerCanMakeMove = true;
				if ($_SESSION['action'] == $ACTION_TYPE["BAD_MOVE"]) 
					$specialOption = 'badMove';
				else if (isPromotionConditionsMet()) 
					$specialOption = 'promote';
			}
			else if (isClientInQueue())
			{
				$clientIsInQueue = true;
				$leaveQueueBtn = true;
			}
			else if ($tableIsFull)
				$queuePlayerBtn = true;
		}
		else
		{			
			switch($_SESSION['ID'])
			{
				case $LOGGED_TYPE["UNLOGGED_LOGIN_FAILED"]: 
					$specialOption = 'loginFailed';
					break; 
				case $LOGGED_TYPE["UNLOGGED_LOGOUT"]: 
					$specialOption = 'logout';
					break;
				case $LOGGED_TYPE["UNLOGGED_DOUBLE_LOGIN"]: 
					$specialOption = 'doubleLogin';
					break;
				case $LOGGED_TYPE["UNLOGGED_REMOVE_AND_REFRESH_CLIENT"]: 
					$specialOption = 'wrongData';
					break;
				//in default client isn't just logged
			}
		}
		
		$_SESSION['consoleAjax'] .= ', $specialOption = '.$specialOption.', $_SESSION["ID"] = '.$_SESSION['ID'].' | ';
		
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
			"leaveQueueBtn" => $leaveQueueBtn,
			"specialOption" => $specialOption
		);
	}
			
	function isChairEmpty($playerType)
	{
		$chair = false;
		if ($playerType == WHITE) 
		{
			if (empty($_SESSION['whitePlayer']) || $_SESSION['whitePlayer'] == "-1" || $_SESSION['whitePlayer'] == "-" || $_SESSION['whitePlayer'] == "0")
				$chair = true;
			else $chair = false;
		}
		else if ($playerType == BLACK) 
		{
			if (empty($_SESSION['blackPlayer']) || $_SESSION['blackPlayer'] == "-1" || $_SESSION['blackPlayer'] == "-" || $_SESSION['blackPlayer'] == "0")
				$chair = true;
			else $chair = false;
		}
		
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
		
		return $loggedPlayerOnChair;
	}
	
	function isLoggedPlayerOnAnyChair()
	{
		if (isLoggedPlayerOnChair(WHITE) || isLoggedPlayerOnChair(BLACK)) return true;
		else return false;
	}
	
	function areBothPlayersOnChairs()
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
		$queueTempArr = explode(" ", $_SESSION['queue']);
		if (in_array($_SESSION['login'], $queueTempArr)) 
			return true; 
		else return false;
	}
	
	function isPlayerAllowedToMakeMove()
	{			
		if (isGameInProgress())
		{
			if ($_SESSION['turn'] == WHITE_TURN && isLoggedPlayerOnChair(WHITE)) return true;
			else if ($_SESSION['turn'] == BLACK_TURN && isLoggedPlayerOnChair(BLACK)) return true;
			else return false;
		}
		else return false;
	}
	
	function isPromotionConditionsMet()
	{
		global $GAME_STATE;
		if (($_SESSION['gameState'] == $GAME_STATE["TURN_WHITE_PROMOTE"] && isLoggedPlayerOnChair(WHITE)) ||
			($_SESSION['gameState'] == $GAME_STATE["TURN_BLACK_PROMOTE"] && isLoggedPlayerOnChair(BLACK)))
			return true;
		else return false;
	}
	
	function isGameStateAnEndType()
	{	
		global $ACTION_TYPE;
	
		switch($_SESSION['action'])
		{
			case $ACTION_TYPE["END_GAME_NORMAL_WIN_WHITE"]:
			case $ACTION_TYPE["END_GAME_NORMAL_WIN_BLACK"]:
			case $ACTION_TYPE["END_GAME_DRAW"]:
			case $ACTION_TYPE["END_GAME_GIVE_UP_WHITE"]:
			case $ACTION_TYPE["END_GAME_GIVE_UP_BLACK"]:
			case $ACTION_TYPE["END_GAME_SOCKET_LOST_WHITE"]:
			case $ACTION_TYPE["END_GAME_SOCKET_LOST_BLACK"]:
			case $ACTION_TYPE["END_GAME_TIMEOUT_GAME_WHITE"]:
			case $ACTION_TYPE["END_GAME_TIMEOUT_GAME_BLACK"]:
				return true;
			default: return false;
		}
	}
	
	function printNewPlayerName()
	{
		global $ACTION_TYPE;
		
		if ($_SESSION['action'] == $ACTION_TYPE["NEW_WHITE_PLAYER"])
		{
			if ($_SESSION['whitePlayer'] == "-" || $_SESSION['whitePlayer'] == "-1" ||$_SESSION['whitePlayer'] == "0") 
				$_SESSION['textboxAjax'] = "Gracz figur białych opuścił stół."; 
			else $_SESSION['textboxAjax'] = "Nowy gracz figur białych: ".$_SESSION['whitePlayer'];
		}
		else if ($_SESSION['action'] == $ACTION_TYPE["NEW_BLACK_PLAYER"])
		{
			if ($_SESSION['blackPlayer'] == "-" || $_SESSION['blackPlayer'] == "-1" ||$_SESSION['blackPlayer'] == "0") 
				$_SESSION['textboxAjax'] = "Gracz figur czarnych opuścił stół."; 
			else $_SESSION['textboxAjax'] = "Nowy gracz figur czarnych: ".$_SESSION['blackPlayer'];
		}
	}
?>							