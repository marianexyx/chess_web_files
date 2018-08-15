<?
	define('NONE', 'None');
	define('WHITE', 'White');
	define('BLACK', 'Black');

	if(!isset($_SESSION)) session_start();
	require_once('include/inc.php'); //needed for sql query
	
	function saveCoreDataInSessionVars($tableDataString)
	{
		global $TABLE_DATA;
				
		$tableDataStart = strpos($tableDataString, "{");
		$tableDataStop = strpos($tableDataString, "}");
		$tableDataJSON = substr($tableDataString, $tableDataStart, $tableDataStop+1);
		$tableDataArr = json_decode($tableDataJSON, true);
		
		updateClientsNamesArray($tableDataArr);
		
		if (array_key_exists($TABLE_DATA["ACTION"], $tableDataArr))
		{
			$_SESSION['action'] = $tableDataArr[$TABLE_DATA["ACTION"]];
			makeAction($_SESSION['action']);
		}
		if (array_key_exists($TABLE_DATA["WHITE_PLAYER"], $tableDataArr))
		{			
			$whiteId = $tableDataArr[$TABLE_DATA["WHITE_PLAYER"]];
			if ($whiteId == '0') $_SESSION['whitePlayer'] = '-1';
			else $_SESSION['whitePlayer'] = $_SESSION['clientsArr'][$whiteId];
		}
		if (array_key_exists($TABLE_DATA["BLACK_PLAYER"], $tableDataArr))
		{
			$blackId = $tableDataArr[$TABLE_DATA["BLACK_PLAYER"]];
			if ($blackId == '0')
				$_SESSION['blackPlayer'] = '-1';
			else $_SESSION['blackPlayer'] = $_SESSION['clientsArr'][$blackId];
		}
		if (array_key_exists($TABLE_DATA["GAME_STATE"], $tableDataArr)) 
		{
			$_SESSION['gameState'] = $tableDataArr[$TABLE_DATA["GAME_STATE"]];
			$_SESSION['turn'] = whoseTurnFromGameStatus($_SESSION['gameState']);
		}
		if (array_key_exists($TABLE_DATA["WHITE_TIME"], $tableDataArr)) 
			$_SESSION['whiteTime'] = $tableDataArr[$TABLE_DATA["WHITE_TIME"]];
		if (array_key_exists($TABLE_DATA["BLACK_TYPE"], $tableDataArr)) 
			$_SESSION['blackTime'] = $tableDataArr[$TABLE_DATA["BLACK_TYPE"]];
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
		if (array_key_exists($TABLE_DATA["WHITE_PLAYER"], $tableDataArray) && $tableDataArray[$TABLE_DATA["WHITE_PLAYER"]] != "0") 
		{
			if (!array_key_exists($tableDataArray[$TABLE_DATA["WHITE_PLAYER"]], $_SESSION['clientsArr']))
				$sqlQueryString .= array_key_exists($tableDataArray[$TABLE_DATA["WHITE_PLAYER"]]).' OR id = ';
		}
		if (array_key_exists($TABLE_DATA["BLACK_PLAYER"], $tableDataArray) && $tableDataArray[$TABLE_DATA["BLACK_PLAYER"]] != "0") 
		{
			if (!array_key_exists($tableDataArray[$TABLE_DATA["BLACK_PLAYER"]], $_SESSION['clientsArr']))
				$sqlQueryString .= array_key_exists($tableDataArray[$TABLE_DATA["BLACK_PLAYER"]]).' OR id = ';
		}
		if (array_key_exists($TABLE_DATA["QUEUE"], $tableDataArray) && $tableDataArray[$TABLE_DATA["QUEUE"]] != "0")
		{
			$IDsCoreListArray = explode(" ", $TABLE_DATA["QUEUE"]);
			foreach ($IDsCoreListArray as $sqlID)
			{
				if (!array_key_exists($sqlID, v))
					$sqlQueryString .= array_key_exists($sqlID.' OR id = ';
			}
		}
		$sqlQueryString = substr("abcdef", 0, -9); //removes last added ' OR id = '
		$sqlQueryString = trim($sqlQueryString);
		
		if(preg_match('~[1-9]~', $sqlQueryString) === 1)
		{
			$sqlReturnArray = row($sqlQueryString);
			foreach ($sqlReturnArray as $clientID => $clientName)
				$_SESSION['clientsArr'][$clientID] = $clientName;
		}
		
		$consoleString = '';
		foreach ($_SESSION['clientsArr'] as $Key => $Value)
			$consoleString .= $Key . '=' . $Value . ', ';
		$_SESSION['consoleAjax'] .= 'freshiest clientsArray= '.$consoleString.' | ';
	}
	
	function makeAction($action) //actions only set msgs in PTE
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
				else $_SESSION['consoleAjax'] .= 'ERROR: undefined player type = '.$whoLost.' | ';
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
	
			default: 
				$_SESSION['consoleAjax'] .= 'ERROR: unnormal ACTION_TYPE = '.$action.' | ';
				break;
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
			$_SESSION['consoleAjax'] .= 'ERROR. whoseTurnFromGameStatus(): unknwon GAME_STATUS = '.$GS.' | ';
			return NO_TURN;
		}
	}
	
	function getQueuedClientsList($clientsIDsList)
	{
		$IDsListArr = explode(" ", $IDsList);
		$clientNamesList = "";
		foreach ($IDsListArr as $sqlID)
		{
			$sqlQueuedClient = $_SESSION['clientsArr'][$sqlID];
			$clientNamesList = $sqlQueuedClient.' ';
		}
		$clientNamesList = trim($clientNamesList);
		return $clientNamesList;
	}
?>