<?
	define('NONE', 'None');
	define('WHITE', 'White');
	define('BLACK', 'Black');

	if(!isset($_SESSION)) 
		session_start();
	require_once('include/func.php');
	
	function saveCoreDataInSessionVars($tableDataString)
	{
		global $TABLE_DATA;
		global $SYNCHRONIZATION_TYPE;
				
		$tableDataStart = strpos($tableDataString, "{");
		$tableDataStop = strpos($tableDataString, "}");
		$tableDataJSON = substr($tableDataString, $tableDataStart, $tableDataStop+1);
		$tableDataArr = json_decode($tableDataJSON, true);
		
		updateClientsNamesArray($tableDataArr);
		
		if (array_key_exists($TABLE_DATA["ID"], $tableDataArr))
		{
			if (intval($tableDataArr[$TABLE_DATA["ID"]]) <= intval($SYNCHRONIZATION_TYPE["UNLOGGED"]))
			{
				unset($_SESSION['ID']);
				unset($_SESSION['login']);
			}
			else if (array_key_exists($tableDataArr[$TABLE_DATA["ID"]], $_SESSION['clientsArr']))
			{
				$_SESSION['ID'] = $tableDataArr[$TABLE_DATA["ID"]];
				$_SESSION['login'] = $_SESSION['clientsArr'][$tableDataArr[$TABLE_DATA["ID"]]];
			}
			//todo: else error: all ID should have names their names in $_SESSION['clientsArr'](setted in updateClientsNamesArray())
		}
		if (array_key_exists($TABLE_DATA["ACTION"], $tableDataArr))
		{
			$_SESSION['action'] = $tableDataArr[$TABLE_DATA["ACTION"]];
			makeAction($_SESSION['action']);
		}
		if (array_key_exists($TABLE_DATA["WHITE_PLAYER"], $tableDataArr))
		{			
			$whiteId = $tableDataArr[$TABLE_DATA["WHITE_PLAYER"]];
			if (empty($whiteId) || $whiteId == '0' || $whiteId == '-' || $whiteId == '-1') 
				$_SESSION['whitePlayer'] = '0';
			else $_SESSION['whitePlayer'] = $_SESSION['clientsArr'][$whiteId];
		}
		if (array_key_exists($TABLE_DATA["BLACK_PLAYER"], $tableDataArr))
		{
			$blackId = $tableDataArr[$TABLE_DATA["BLACK_PLAYER"]];
			if (empty($blackId) || $blackId == '0' || $blackId == '-' || $blackId == '-1') 
				$_SESSION['blackPlayer'] = '0';
			else $_SESSION['blackPlayer'] = $_SESSION['clientsArr'][$blackId];
		}
		if (array_key_exists($TABLE_DATA["GAME_STATE"], $tableDataArr)) 
		{
			$_SESSION['gameState'] = $tableDataArr[$TABLE_DATA["GAME_STATE"]];
			$_SESSION['turn'] = whoseTurnFromGameStatus($_SESSION['gameState']);
		}
		if (array_key_exists($TABLE_DATA["WHITE_TIME"], $tableDataArr)) 
			$_SESSION['whiteTime'] = $tableDataArr[$TABLE_DATA["WHITE_TIME"]];
		if (array_key_exists($TABLE_DATA["BLACK_TIME"], $tableDataArr)) 
			$_SESSION['blackTime'] = $tableDataArr[$TABLE_DATA["BLACK_TIME"]];
		if (array_key_exists($TABLE_DATA["TURN_TIME"], $tableDataArr)) 
			$_SESSION['turnTime'] = $tableDataArr[$TABLE_DATA["TURN_TIME"]];
		if (array_key_exists($TABLE_DATA["QUEUE"], $tableDataArr)) 
			$_SESSION['queue'] = getQueuedClientsList($tableDataArr[$TABLE_DATA["QUEUE"]]);
		if (array_key_exists($TABLE_DATA["START_TIME"], $tableDataArr))
		{
			$time = $tableDataArr[$TABLE_DATA["START_TIME"]];
			$_SESSION['startTime'] = (($time > 0) ? $time : "-1");
			$_SESSION['startTime'] = $tableDataArr[$TABLE_DATA["START_TIME"]];
		}
		if (array_key_exists($TABLE_DATA["HISTORY"], $tableDataArr)) 
			$_SESSION['history'] = $tableDataArr[$TABLE_DATA["HISTORY"]];
		if (array_key_exists($TABLE_DATA["PROMOTIONS"], $tableDataArr)) 
			$_SESSION['promoted'] = $tableDataArr[$TABLE_DATA["PROMOTIONS"]];
	}
	
	function updateClientsNamesArray($tableDataArray)
	{
		global $TABLE_DATA;
		$sqlQueryString = "SELECT id, login FROM users WHERE id = ";
		
		if (array_key_exists($TABLE_DATA["ID"], $tableDataArray) && intval($tableDataArray[$TABLE_DATA["ID"]]) > 0 && 
			!array_key_exists($tableDataArray[$TABLE_DATA["ID"]], $_SESSION['clientsArr'])) 
		{
			$sqlQueryString .= $tableDataArray[$TABLE_DATA["ID"]].' OR id = ';
		}
		if (array_key_exists($TABLE_DATA["WHITE_PLAYER"], $tableDataArray) && intval($tableDataArray[$TABLE_DATA["WHITE_PLAYER"]]) > 0 &&
			!array_key_exists($tableDataArray[$TABLE_DATA["WHITE_PLAYER"]], $_SESSION['clientsArr'])) 
		{
			$sqlQueryString .= $tableDataArray[$TABLE_DATA["WHITE_PLAYER"]].' OR id = ';
		}
		if (array_key_exists($TABLE_DATA["BLACK_PLAYER"], $tableDataArray) && intval($tableDataArray[$TABLE_DATA["BLACK_PLAYER"]]) > 0 &&
			!array_key_exists($tableDataArray[$TABLE_DATA["BLACK_PLAYER"]], $_SESSION['clientsArr'])) 
		{
			$sqlQueryString .= $tableDataArray[$TABLE_DATA["BLACK_PLAYER"]].' OR id = ';
		}
		if (array_key_exists($TABLE_DATA["QUEUE"], $tableDataArray) && intval($tableDataArray[$TABLE_DATA["QUEUE"]]) > 0)
		{
			$IDsCoreListArray = explode(" ", $tableDataArray[$TABLE_DATA["QUEUE"]]);
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
				else $_SESSION['ID'] = $SYNCHRONIZATION_TYPE["UNLOGGED_REMOVE_AND_REFRESH_CLIENT"]; //error type
				
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
	
			default: $_SESSION['id'] = $SYNCHRONIZATION_TYPE["UNLOGGED_REMOVE_AND_REFRESH_CLIENT"]; //error type
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
			$_SESSION['ID'] = $SYNCHRONIZATION_TYPE["UNLOGGED_REMOVE_AND_REFRESH_CLIENT"]; //error type
			return NO_TURN;
		}
	}
?>