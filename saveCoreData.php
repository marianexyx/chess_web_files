<?
	define('NONE', 'None');
	define('WHITE', 'White');
	define('BLACK', 'Black');

	if(!isset($_SESSION)) 
		session_start();
	require_once('include/func.php');
	
	function saveCoreDataInSessionVars($tableDataString)
	{
		global $CORE_DATA_TYPE;
		global $LOGGED_TYPE;
				
		$tableDataStart = strpos($tableDataString, "{");
		$tableDataStop = strpos($tableDataString, "}");
		$tableDataJSON = substr($tableDataString, $tableDataStart, $tableDataStop+1);
		$tableDataArr = json_decode($tableDataJSON, true);
		
		updateClientsNamesArray($tableDataArr);
		
		if (array_key_exists($CORE_DATA_TYPE["ID"], $tableDataArr))
		{
			$_SESSION['ID'] = $tableDataArr[$CORE_DATA_TYPE["ID"]];
			if (intval($tableDataArr[$CORE_DATA_TYPE["ID"]]) <= intval($LOGGED_TYPE["UNLOGGED"])) //client is unlogged (has ID <= 0)
				unset($_SESSION['login']);
			else if (array_key_exists($tableDataArr[$CORE_DATA_TYPE["ID"]], $_SESSION['clientsArr'])) //client is logged (has ID > 0)
				$_SESSION['login'] = $_SESSION['clientsArr'][$tableDataArr[$CORE_DATA_TYPE["ID"]]];
			//todo: else error: all ID should have names their names in $_SESSION['clientsArr'](setted in updateClientsNamesArray())
		}
		if (array_key_exists($CORE_DATA_TYPE["ACTION"], $tableDataArr))
		{
			$_SESSION['action'] = $tableDataArr[$CORE_DATA_TYPE["ACTION"]];
			makeAction($_SESSION['action']);
		}
		if (array_key_exists($CORE_DATA_TYPE["WHITE_PLAYER"], $tableDataArr))
		{			
			$whiteId = $tableDataArr[$CORE_DATA_TYPE["WHITE_PLAYER"]];
			if (empty($whiteId) || $whiteId == '0' || $whiteId == '-' || $whiteId == '-1') 
				$_SESSION['whitePlayer'] = '0';
			else $_SESSION['whitePlayer'] = $_SESSION['clientsArr'][$whiteId];
		}
		if (array_key_exists($CORE_DATA_TYPE["BLACK_PLAYER"], $tableDataArr))
		{
			$blackId = $tableDataArr[$CORE_DATA_TYPE["BLACK_PLAYER"]];
			if (empty($blackId) || $blackId == '0' || $blackId == '-' || $blackId == '-1') 
				$_SESSION['blackPlayer'] = '0';
			else $_SESSION['blackPlayer'] = $_SESSION['clientsArr'][$blackId];
		}
		if (array_key_exists($CORE_DATA_TYPE["GAME_STATE"], $tableDataArr)) 
		{
			$_SESSION['gameState'] = $tableDataArr[$CORE_DATA_TYPE["GAME_STATE"]];
			$_SESSION['turn'] = whoseTurnFromGameStatus($_SESSION['gameState']);
		}
		if (array_key_exists($CORE_DATA_TYPE["WHITE_TIME"], $tableDataArr)) 
			$_SESSION['whiteTime'] = $tableDataArr[$CORE_DATA_TYPE["WHITE_TIME"]];
		if (array_key_exists($CORE_DATA_TYPE["BLACK_TIME"], $tableDataArr)) 
			$_SESSION['blackTime'] = $tableDataArr[$CORE_DATA_TYPE["BLACK_TIME"]];
		if (array_key_exists($CORE_DATA_TYPE["TURN_TIME"], $tableDataArr)) 
			$_SESSION['turnTime'] = $tableDataArr[$CORE_DATA_TYPE["TURN_TIME"]];
		if (array_key_exists($CORE_DATA_TYPE["QUEUE"], $tableDataArr)) 
			$_SESSION['queue'] = getQueuedClientsList($tableDataArr[$CORE_DATA_TYPE["QUEUE"]]);
		if (array_key_exists($CORE_DATA_TYPE["START_TIME"], $tableDataArr))
		{
			$time = $tableDataArr[$CORE_DATA_TYPE["START_TIME"]];
			$_SESSION['startTime'] = (($time > 0) ? $time : "-1");
			$_SESSION['startTime'] = $tableDataArr[$CORE_DATA_TYPE["START_TIME"]];
		}
		if (array_key_exists($CORE_DATA_TYPE["HISTORY"], $tableDataArr)) 
			$_SESSION['history'] = $tableDataArr[$CORE_DATA_TYPE["HISTORY"]];
		if (array_key_exists($CORE_DATA_TYPE["PROMOTIONS"], $tableDataArr)) 
			$_SESSION['promoted'] = $tableDataArr[$CORE_DATA_TYPE["PROMOTIONS"]];
	}
	
	function updateClientsNamesArray($tableDataArray)
	{
		global $CORE_DATA_TYPE;
		$sqlQueryString = "SELECT id, login FROM users WHERE id = ";
		
		if (array_key_exists($CORE_DATA_TYPE["ID"], $tableDataArray) && intval($tableDataArray[$CORE_DATA_TYPE["ID"]]) > 0 && 
			!array_key_exists($tableDataArray[$CORE_DATA_TYPE["ID"]], $_SESSION['clientsArr'])) 
		{
			$sqlQueryString .= $tableDataArray[$CORE_DATA_TYPE["ID"]].' OR id = ';
		}
		if (array_key_exists($CORE_DATA_TYPE["WHITE_PLAYER"], $tableDataArray) && intval($tableDataArray[$CORE_DATA_TYPE["WHITE_PLAYER"]]) > 0 &&
			!array_key_exists($tableDataArray[$CORE_DATA_TYPE["WHITE_PLAYER"]], $_SESSION['clientsArr'])) 
		{
			$sqlQueryString .= $tableDataArray[$CORE_DATA_TYPE["WHITE_PLAYER"]].' OR id = ';
		}
		if (array_key_exists($CORE_DATA_TYPE["BLACK_PLAYER"], $tableDataArray) && intval($tableDataArray[$CORE_DATA_TYPE["BLACK_PLAYER"]]) > 0 &&
			!array_key_exists($tableDataArray[$CORE_DATA_TYPE["BLACK_PLAYER"]], $_SESSION['clientsArr'])) 
		{
			$sqlQueryString .= $tableDataArray[$CORE_DATA_TYPE["BLACK_PLAYER"]].' OR id = ';
		}
		if (array_key_exists($CORE_DATA_TYPE["QUEUE"], $tableDataArray) && intval($tableDataArray[$CORE_DATA_TYPE["QUEUE"]]) > 0)
		{
			$IDsCoreListArray = explode(" ", $tableDataArray[$CORE_DATA_TYPE["QUEUE"]]);
			foreach ($IDsCoreListArray as $sqlID)
			{
				if (!array_key_exists($sqlID, $_SESSION['clientsArr']))
					$sqlQueryString .= $sqlID.' OR id = ';
			}
		}
		$sqlQueryString = substr($sqlQueryString, 0, -9); //removes last added: ' OR id = '
		$sqlQueryString = trim($sqlQueryString);
		//$_SESSION['consoleAjax'] .= ', string for database= '.implode(' , ', $sqlQueryString).' | ';
		if (preg_match('~[1-9]~', $sqlQueryString) === 1) //if there is at least 1 new client in list (database query string will containts some number, if it has sqlID in it)
		{
			$sqlReturnArray = row($sqlQueryString);
			//$_SESSION['consoleAjax'] .= ', new clients to save in list: $sqlReturnArray = '.implode(' | ', $sqlReturnArray).' | ';
			foreach ($sqlReturnArray as $clientID)
			{
				$_SESSION['clientsArr'][$sqlReturnArray['id']] = $sqlReturnArray['login'];
				//$_SESSION['consoleAjax'] .= ' new name in clientsArray = '.$_SESSION['clientsArr'][$sqlReturnArray['login']].' with ID = '.$sqlReturnArray['id'].' | ';
			}
		}
		//else $_SESSION['consoleAjax'] .= ', no new clients to save in list | ';
		//$_SESSION['consoleAjax'] .= ' newest session clientsArray: '.http_build_query($_SESSION['clientsArr'], '', '; ').' | ';
	}
	
	function getQueuedClientsList($clientsIDsList)
	{
		$clientsIDsList = explode(" ", $clientsIDsList);
		$clientNamesList = "";
		foreach ($clientsIDsList as $sqlID)
		{
			$sqlQueuedClient = $_SESSION['clientsArr'][$sqlID];
			$clientNamesList .= $sqlQueuedClient.' ';
		}
		$clientNamesList = trim($clientNamesList);
		//$_SESSION['consoleAjax'] .= ' queuedClientNamesList = '.$clientNamesList.' | ';
		
		return $clientNamesList;
	}
	
	function makeAction($action) //actions only set msgs in PTE. //todo: func name
	{
		global $ACTION_TYPE;
		
		switch ($action)
		{		
			case $ACTION_TYPE["NONE"]:
			case $ACTION_TYPE["NEW_WHITE_PLAYER"]:
			case $ACTION_TYPE["NEW_BLACK_PLAYER"]:
				break;
			
			case $ACTION_TYPE["NEW_GAME_STARTED"]:
				$_SESSION['textboxAjax'] = "Nowa gra rozpoczęta. Białe wykonują ruch."; 
				break;
			
			case $ACTION_TYPE["BAD_MOVE"]:
				$_SESSION['textboxAjax'] = 'Błędne rządanie ruchu! Wybierz inny ruch.';
				break;
			
			case $ACTION_TYPE["RESET_COMPLITED"]: 
				$_SESSION['textboxAjax'] = 'Plansza zresetowana.';
				break;

			case $ACTION_TYPE["END_GAME_NORMAL_WIN_WHITE"]: 
			case $ACTION_TYPE["END_GAME_NORMAL_WIN_BLACK"]: 
			case $ACTION_TYPE["END_GAME_DRAW"]:
			case $ACTION_TYPE["END_GAME_GIVE_UP_WHITE"]: 
			case $ACTION_TYPE["END_GAME_GIVE_UP_BLACK"]: 
			case $ACTION_TYPE["END_GAME_SOCKET_LOST_WHITE"]: 
			case $ACTION_TYPE["END_GAME_SOCKET_LOST_BLACK"]: 
			case $ACTION_TYPE["END_GAME_TIMEOUT_GAME_WHITE"]: 
			case $ACTION_TYPE["END_GAME_TIMEOUT_GAME_BLACK"]: 
				$whoLost; $playerWhoWon; $playerWhoLost;
				switch($action)
				{
					case $ACTION_TYPE["END_GAME_NORMAL_WIN_WHITE"]: 
					case $ACTION_TYPE["END_GAME_GIVE_UP_WHITE"]:
					case $ACTION_TYPE["END_GAME_SOCKET_LOST_WHITE"]: 
					case $ACTION_TYPE["END_GAME_TIMEOUT_GAME_WHITE"]: 
						$whoLost = WHITE; break;
					case $ACTION_TYPE["END_GAME_NORMAL_WIN_BLACK"]: 
					case $ACTION_TYPE["END_GAME_GIVE_UP_BLACK"]:
					case $ACTION_TYPE["END_GAME_SOCKET_LOST_BLACK"]: 
					case $ACTION_TYPE["END_GAME_TIMEOUT_GAME_BLACK"]: 
						$whoLost = BLACK; break;
					case $ACTION_TYPE["END_GAME_DRAW"]:
					default: $whoLost = NONE;
				}
				
				if ($whoLost == WHITE) 
				{
					$playerWhoWon = "Czarn";
					$playerWhoLost = "Biał";
				}
				else if ($whoLost == BLACK) 
				{
					$playerWhoWon = "Biał";
					$playerWhoLost = "Czarn";
				}
				else if ($whoLost == NONE) 
					$_SESSION['textboxAjax'] = 'Koniec gry: Remis.';
				else $_SESSION['ID'] = $LOGGED_TYPE["UNLOGGED_REMOVE_AND_REFRESH_CLIENT"]; //error type
				
				if ($whoLost == WHITE || $whoLost == BLACK)
				{
					switch($action)
					{
						case $ACTION_TYPE["END_GAME_NORMAL_WIN_WHITE"]: 
						case $ACTION_TYPE["END_GAME_NORMAL_WIN_BLACK"]: 
							$_SESSION['textboxAjax'] = 'Koniec gry: '.$playerWhoWon.'e wygrały.';
							break;
						case $ACTION_TYPE["END_GAME_GIVE_UP_WHITE"]: 
						case $ACTION_TYPE["END_GAME_GIVE_UP_BLACK"]: 
							$_SESSION['textboxAjax'] = 'Koniec gry: '.$playerWhoLost.'e się poddały. '.$playerWhoWon.'e wygrały.';
							break;
						case $ACTION_TYPE["END_GAME_SOCKET_LOST_WHITE"]: 
						case $ACTION_TYPE["END_GAME_SOCKET_LOST_BLACK"]: 
							$_SESSION['textboxAjax'] = 'Koniec gry: '.$playerWhoLost.'e się rozłączyły. '.$playerWhoWon.'e wygrały.';
							break;
						case $ACTION_TYPE["END_GAME_TIMEOUT_GAME_WHITE"]: 
						case $ACTION_TYPE["END_GAME_TIMEOUT_GAME_BLACK"]: 
							$_SESSION['textboxAjax'] = 'Koniec gry: '.$playerWhoLost.'emu skończył się czas. '.$playerWhoWon.'e wygrały.';
							break;
					}
				}
				$_SESSION['textboxAjax'] .= ' Resetowanie planszy...';
				break;
	
			default: $_SESSION['id'] = $LOGGED_TYPE["UNLOGGED_REMOVE_AND_REFRESH_CLIENT"]; //error type
		}
	}
	
	function whoseTurnFromGameStatus($GS)
	{
		global $GAME_STATE;
				
		switch($GS)
		{
		case $GAME_STATE["ERROR"]: return NO_TURN;
		case $GAME_STATE["TURN_NONE_WAITING_FOR_PLAYERS"]: return NO_TURN;
		case $GAME_STATE["TURN_NONE_WAITING_FOR_START_CONFIRMS"]: return NO_TURN;
		case $GAME_STATE["TURN_NONE_RESETING"]: return NO_TURN;
		case $GAME_STATE["TURN_WHITE"]: return WHITE_TURN;
		case $GAME_STATE["TURN_WHITE_FIRST_TURN"]: return WHITE_TURN;
		case $GAME_STATE["TURN_WHITE_PROMOTE"]: return WHITE_TURN;
		case $GAME_STATE["TURN_BLACK"]: return BLACK_TURN;
		case $GAME_STATE["TURN_BLACK_PROMOTE"]: return BLACK_TURN;
		default:
			$_SESSION['ID'] = $LOGGED_TYPE["UNLOGGED_REMOVE_AND_REFRESH_CLIENT"]; //error type
			return NO_TURN;
		}
	}
?>