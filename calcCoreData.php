<?
	//todo: destroy/clear client/session if error occurs

	define('NONE', 'None');
	define('WHITE', 'White');
	define('BLACK', 'Black');
	
	if(!isset($_SESSION)) session_start(); 
		
	function calculateDataFromSessionVars()
	{	
		global $ACTION_TYPE;
		global $GAME_STATE;
	
		$loggedPlayerIsOnAnyChair = false;
		$loggedPlayerIsOnWhiteChair = false;
		$loggedPlayerIsOnBlackChair = false;
		$tableIsFull = false;
		$clientIsInQueue = false;
		$whitePlayerBtn = false;
		$blackPlayerBtn = false;
		$playerCanMakeMove = false;
		$queuePlayerBtn = false;
		$leaveQueueBtn = false;
		$specialOption = '-1';
			
		if (empty($_SESSION['id']))
			$_SESSION['synchronized'] = false;
			
		//todo: maybe it would be better if logout actions be as synchronized tabla_data options?
		if ($_SESSION['action'] != $ACTION_TYPE["DOUBLE_LOGIN"] && $_SESSION['action'] != $ACTION_TYPE["REMOVE_AND_REFRESH_CLIENT"])	
		{	
			if ($_SESSION['synchronized'])
			{
				$loggedPlayerIsOnAnyChair = isLoggedPlayerOnAnyChair();
				$tableIsFull = are2playersAreOnChairs();
				$clientIsInQueue = isClientInQueue();
				$loggedPlayerIsOnWhiteChair = isLoggedPlayerOnChair(WHITE);
				$loggedPlayerIsOnBlackChair = isLoggedPlayerOnChair(BLACK);
				$playerCanMakeMove = isPlayerAllowedToMakeMove();
				if (isChairEmpty(WHITE) && !isLoggedPlayerOnChair(BLACK)) 
					$whitePlayerBtn = true;
				if (isChairEmpty(BLACK) && !isLoggedPlayerOnChair(WHITE)) 
					$blackPlayerBtn = true;
				
				if (are2playersAreOnChairs() && !isLoggedPlayerOnAnyChair() && !isClientInQueue()) 
					$queuePlayerBtn = true;
				else if (isClientInQueue()) 
					$leaveQueueBtn = true;
				
				if ($_SESSION['action'] == $ACTION_TYPE["NEW_WHITE_PLAYER"] || $_SESSION['action'] == $ACTION_TYPE["NEW_BLACK_PLAYER"])
					printNewPlayerName();
				
				if ($_SESSION['gameState'] == $GAME_STATE["NEW_GAME_STARTED"]) 
					$specialOption = 'newGameStarted';
				else if ($_SESSION['action'] == $ACTION_TYPE["BAD_MOVE"]) 
					$specialOption = 'badMove';
				else if (isPromotionConditionsMet()) 
					$specialOption = 'promote';
				else if (isGameStateAnEndType()) 
					$specialOption = 'endOfGame';
			}
			else if (!empty($_SESSION['id']) && !empty($_SESSION['hash'])) //don't remove this
				$specialOption = 'checkForLogin im '.$_SESSION['id'].'&'.$_SESSION['hash'] ;
		}
		else if ($_SESSION['action'] == $ACTION_TYPE["DOUBLE_LOGIN"] || $_SESSION['action'] == $ACTION_TYPE["REMOVE_AND_REFRESH_CLIENT"])
		{
			unset($_SESSION['login']);
			unset($_SESSION['id']);
			unset($_SESSION['hash']);
			if ($_SESSION['action'] == $ACTION_TYPE["DOUBLE_LOGIN"]) 
				$specialOption = 'doubleLogin';
			else if ($_SESSION['action'] == $ACTION_TYPE["REMOVE_AND_REFRESH_CLIENT"]) 
				$specialOption = 'wrongData';
			/*if ($_SESSION['action'] == $ACTION_TYPE["DOUBLE_LOGIN"]) header("Location: index.php?a=doubleLogin"); //todo: check it
			else if ($_SESSION['action'] == $ACTION_TYPE["REMOVE_AND_REFRESH_CLIENT"]) header("Location: index.php");*/
		}
		
		return array
		(
			"clientIsLogged" => $_SESSION['synchronized'],
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
		$queueTempArr = explode(" ", $_SESSION['queue']);
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
	
	function isPromotionConditionsMet()
	{
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
		else $_SESSION['consoleAjax'] .= 'ERROR: action isnt white or black new player. its ='.$_SESSION['action']
			.', $ACTION_TYPE[NEW_WHITE_PLAYER] ='.$ACTION_TYPE["NEW_WHITE_PLAYER"].' | ';
	}
?>							