<?
	if(!isset($_SESSION)) session_start();
	require_once('disabling.php');
	require_once('include/inc.php');
	
	if(isset($_POST['wsMsg']))
	{	
		resetSessionData();
		
		//todo: wyciąganie danych do zmiennych sesyjnych może się odbywać w osobnym pliku, tak samo jak enabling (razem z wszystkimi podfunkcjami)
		//todo: special opcje/promocje/inne różne niesesyjne operacje- odbywają się w tableDataStringToSession. przenieść je do enabling
		tableDataStringToSession($_POST['wsMsg']); //todo: zmienić nazwę na taką która sugeruje, że tu jedynie zczytujemy zmienne do sesyjnych
		
		$enablingArr = array();
		$enablingArr = enabling(); //todo: niech nazwa funkcji sugeruje, że tutaj są obliczane wartości na podstawie wszystkich zapisanych zmiennych seryjnych (tj. nie mogę być obliczane bez spisania ich wcześniej)
		
		$returnArray = array
		(
			"whitePlayerName" => $_SESSION['whitePlayer'], 
			"blackPlayerName" => $_SESSION['blackPlayer'], 
			"consoleMsg" => $_SESSION['consoleAjax'], 
			"PTEmsg" => $_SESSION['textboxAjax'],
			"specialOption" => $_SESSION['specialOption'], 	
			"clientIsLogged" => $enablingArr["clientIsLogged"],
			"loggedPlayerIsOnAnyChair" => $enablingArr["loggedPlayerIsOnAnyChair"],
			"loggedPlayerIsOnWhiteChair" => $enablingArr["loggedPlayerIsOnWhiteChair"],
			"loggedPlayerIsOnBlackChair" => $enablingArr["loggedPlayerIsOnBlackChair"],
			"tableIsFull" => $enablingArr["tableIsFull"],
			"clientIsInQueue" => $enablingArr["clientIsInQueue"],
			"whitePlayerBtn" => $enablingArr["whitePlayerBtn"],
			"blackPlayerBtn" => $enablingArr["blackPlayerBtn"],
			"playerCanMakeMove" => $enablingArr["playerCanMakeMove"],
			"queuePlayerBtn" => $enablingArr["queuePlayerBtn"],
			"leaveQueueBtn" => $enablingArr["leaveQueueBtn"],
			"queuedPlayers" => $_SESSION['queue'], 
			"whitePlayerTimeLeft" => $_SESSION['whiteTime'], 
			"blackPlayerTimeLeft" => $_SESSION['blackTime'], 
			"whoseTurn" => $_SESSION['turn'], 
			"historyOfMoves" => $_SESSION['history'], 
			"promotedPawnsList" => $_SESSION['promoted']
		);
		
		foreach($returnArray as &$value) { if (is_null($value)) { $value = '-1'; }} unset($value);
		header('Content-type: application/json; charset=utf-8"');
		echo json_encode($returnArray); //, JSON_UNESCAPED_UNICODE); //todo:- polskie znaki?
	}
	
	function tableDataStringToSession($tableDataString)
	{
		$TABLE_DATA = array
		(
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
			"ERROR" => "0x0b"
		);
				
		$tableDataStart = strpos($tableDataString, "{");
		$tableDataStop = strpos($tableDataString, "}");
		$tableDataJSON = substr($tableDataString, $tableDataStart, $tableDataStop+1);
		$tableDataArr = json_decode($tableDataJSON, true);
		
		if (array_key_exists($TABLE_DATA["ACTION"], $tableDataArr))
			makeAction($tableDataArr[$TABLE_DATA["ACTION"]]);
		if (array_key_exists($TABLE_DATA["WHITE_PLAYER"], $tableDataArr))
		{			
			$whiteId = $tableDataArr[$TABLE_DATA["WHITE_PLAYER"]];
			if ($whiteId == '0') 
				$_SESSION['whitePlayer'] = '-1';
			else
			{
				$queryWhite = row("SELECT * FROM users WHERE id = '$whiteId'");
				if ($queryWhite) $_SESSION['whitePlayer'] = $queryWhite['login']; 
				else $_SESSION['whitePlayer'] = "<ERROR>";
			}
		}
		if (array_key_exists($TABLE_DATA["BLACK_PLAYER"], $tableDataArr))
		{
			$blackId = $tableDataArr[$TABLE_DATA["BLACK_PLAYER"]];
			if ($blackId == '0')
				$_SESSION['blackPlayer'] = '-1';
			else
			{
				$queryBlack = row("SELECT * FROM users WHERE id = ".$blackId);
				if ($queryBlack) $_SESSION['blackPlayer'] = $queryBlack['login'];
				else $_SESSION['blackPlayer'] = "<ERROR>";
			}
		}
		if (array_key_exists($TABLE_DATA["GAME_STATE"], $tableDataArr)) 
		{
			$_SESSION['turn'] = whoseTurnFromGameStatus($tableDataArr[$TABLE_DATA["GAME_STATE"]]);
			//$_SESSION['specialOption'] = specialOptionFromGameStatus($tableDataArr[$TABLE_DATA["GAME_STATE"]]); //todo: opis w funkcji
		}
		if (array_key_exists($TABLE_DATA["WHITE_TIME"], $tableDataArr)) 
			$_SESSION['whiteTime'] = $tableDataArr[$TABLE_DATA["WHITE_TIME"]];
		if (array_key_exists($TABLE_DATA["BLACK_TYPE"], $tableDataArr)) 
			$_SESSION['blackTime'] = $tableDataArr[$TABLE_DATA["BLACK_TYPE"]];
		if (array_key_exists($TABLE_DATA["QUEUE"], $tableDataArr)) 
			$_SESSION['queue'] = getClientNamesFromSqlIDs($tableDataArr[$TABLE_DATA["QUEUE"]]);
		if (array_key_exists($TABLE_DATA["START_TIME"], $tableDataArr)) //np.: "start":"192", where 1st number determine who clicked, rest is time
		{
			//todo: zapakować w funkcję
			$playersClickedStart = substr($tableDataArr[$TABLE_DATA["START_TIME"]], 0, 1);
			$_SESSION['whitePlayerClickedStart'] = (($playersClickedStart % 2) == 0 ? false : true); //todo: do wyrzucenia
			$_SESSION['blackPlayerClickedStart'] = (($playersClickedStart > 1) ? true : false);
			$whitePlayerClickedStart = (($playersClickedStart % 2) == 0 ? false : true);
			$blackPlayerClickedStart = (($playersClickedStart > 1) ? true : false);
			$_SESSION['startTime'] = substr($tableDataArr[$TABLE_DATA["START_TIME"]], 1);
			$startDialogType = 'none';
			if ($_SESSION['whitePlayer'] == '-1' || $_SESSION['blackPlayer'] == '-1') 
				$_SESSION['consoleAjax'] .= 'ERROR: tried to use params before they had been set | ';
			if ($_SESSION['whitePlayer'] == $_SESSION['login'])
				$startDialogType = (($whitePlayerClickedStart) ? 'wait' : 'click');
			else if	($_SESSION['blackPlayer'] == $_SESSION['login'])
				$startDialogType = (($blackPlayerClickedStart) ? 'wait' : 'click');
			$_SESSION['specialOption'] = 'showStartdialog:'.$startDialogType.'+'.$_SESSION['startTime']; //todo:json?
		}
		if (array_key_exists($TABLE_DATA["HISTORY"], $tableDataArr)) 
			$_SESSION['history'] = $tableDataArr[$TABLE_DATA["HISTORY"]];
		if (array_key_exists($TABLE_DATA["PROMOTIONS"], $tableDataArr)) 
			$_SESSION['promoted'] = $tableDataArr[$TABLE_DATA["PROMOTIONS"]];
	}
	
	function resetSessionData()
	{
		$_SESSION['consoleAjax'] = '-1'; 
		$_SESSION['textboxAjax'] = '-1'; 
		$_SESSION['specialOption'] = '-1'; //todo: upewnić się że special opcje nie będą się krossować (albo nawarstwiać je)
		$_SESSION['queue'] = '-1';
		$_SESSION['whiteTime'] = '-1'; //todo: te ostatnie zienne są troche niepokolei w stosunku do pierwszych kilku
		$_SESSION['blackTime'] = '-1';
		$_SESSION['turn'] = '-1';
		$_SESSION['whitePlayerClickedStart'] = '-1';
		$_SESSION['blackPlayerClickedStart'] = '-1';
		$_SESSION['startTime'] = '-1';
		$_SESSION['history'] = '-1';
		$_SESSION['promoted']= '-1';
	}
		
	function whoseTurnFromGameStatus($GS)
	{
		$GAME_STATE = array
		(
			"ERROR" => "0x00",
			"TURN_NONE_WAITING_FOR_PLAYERS" => "0x01",
			"TURN_NONE_WAITING_FOR_START_CONFIRMS" => "0x02",
			"TURN_NONE_RESETING" => "0x03",
			"TURN_WHITE" => "0x04",
			"TURN_WHITE_FIRST_TURN" => "0x05",
			"TURN_WHITE_PROMOTE" => "0x06",
			"TURN_BLACK" => "0x07",
			"TURN_BLACK_PROMOTE" => "0x08"
		);
		
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
	
	function specialOptionFromGameStatus($GS)
	{
		//todo: wszystkie te opcje ustawiać w disabling. samo disabling rozumieć jako "akcje ustawiane po zczytaniu wszystkich zmiennych...
		//...z core i zapisaniu ich w zmiennych sesyjnych". dopiero na ich podstawie można obliczać to jakie specjalne opcje (okna promocji...
		//..., okna startu itd) mogą się pojawiać
		$_SESSION['specialOption'] = 'promote'; 
		
		switch($GS) 
		{
		case $GAME_STATE["TURN_WHITE_PROMOTE"]: return WHITE_TURN;
		case $GAME_STATE["TURN_BLACK_PROMOTE"]: return BLACK_TURN;
		default: return "-1";
		}
	}
	
	function getClientNamesFromSqlIDs($IDsList)
	{	
		//todo: zapytanie może być wykonane tylo raz przy użyciu "OR", i mozna wyciągać tylko loginy z bazy, zamiast całych linii
		if ($IDsList == "0")
			return "-1";
		else
		{
			$IDsListArr = explode(" ", $IDsList);
			$sqlQueryString = "SELECT * FROM users WHERE id = ";
			$clientNamesList = "";
			foreach ($IDsListArr as $value)
			{
				$sqlQueryString = $sqlQueryString.' id = '.$value.' OR';
				$sqlQueuedClient = row($sqlQueryString.$value);
				$clientNamesList = $clientNamesList.$sqlQueuedClient['login'].' ';
			}
			$clientNamesList = trim($clientNamesList);
			return $clientNamesList;
		}
	}
		
	function makeAction($action)
	{
		$ACTION_TYPE = array
		(
			"NONE" => "0x00",
			"NEW_GAME_STARTED" => "0x01",
			"CONTINUE" => "0x02", //todo: useless
			"BAD_MOVE" => "0x03",
			"PROMOTE_TO_WHAT" => "0x04",
			"RESET_COMPLITED" => "0x05",
			"END_GAME_NONE" => "0x06", 	//error type. end_of_game can't be none
			"END_GAME_NORMAL_WIN_WHITE" => "0x07",
			"END_GAME_NORMAL_WIN_BLACK" => "0x08",
			"END_GAME_DRAW" => "0x09",
			"END_GAME_GIVE_UP_WHITE" => "0x0a",
			"END_GAME_GIVE_UP_BLACK" => "0x0b",
			"END_GAME_SOCKET_LOST_WHITE" => "0x0c",
			"END_GAME_SOCKET_LOST_BLACK" => "0x0d",
			"END_GAME_TIMEOUT_GAME_WHITE" => "0x0e",
			"END_GAME_TIMEOUT_GAME_BLACK" => "0x0f", 
			"END_GAME_ERROR" => "0x10",
			"ERROR" => "0xff"
		);
		
		switch ($action)
		{		
			case $ACTION_TYPE["NONE"]:
			case $ACTION_TYPE["CONTINUE"]: 
				break;
		
			case $ACTION_TYPE["NEW_GAME_STARTED"]:
				if ($_SESSION['whitePlayer'] != WHITE && $_SESSION['blackPlayer'] != BLACK)
				{
					$_SESSION['textboxAjax'] = "Nowa gra rozpoczęta. Białe wykonują ruch."; 
				}
				else $_SESSION['textboxAjax'] = "ERROR: game started when players aren't on chairs"; 
				$_SESSION['whiteTime'] = 30*60; //start time == 30 mins
				$_SESSION['blackTime'] = 30*60; 
				break;
			
			case $ACTION_TYPE["BAD_MOVE"]:
				$_SESSION['consoleAjax'] .= 'badMove | ';
				$_SESSION['textboxAjax'] = 'Błędne rządanie ruchu! Wybierz inny ruch.';
				break;
			
			//todo: to może wynikać z typu statusu gry, tymbardziej że po odświerzeniu strony ta akcja zniknie
			case $ACTION_TYPE["PROMOTE_TO_WHAT"]: 
				$_SESSION['specialOption'] = 'promote'; 
				$_SESSION['consoleAjax'] .= 'show promotion buttons window | ';
				break;
			
			case $ACTION_TYPE["RESET_COMPLITED"]: 
				$_SESSION['turn'] = NO_TURN;
				if (strpos($wsMsgVal, "TABLE_DATA") !== false) 
					tableDataStringToSession($wsMsgVal);
				break;

			case $ACTION_TYPE["END_GAME_NORMAL_WIN_WHITE"]: 
			case $ACTION_TYPE["END_GAME_NORMAL_WIN_BLACK"]: 
			case $ACTION_TYPE["END_GAME_DRAW"]: //todo: rozpatrywać oddzielnie jakoś?
			case $ACTION_TYPE["END_GAME_GIVE_UP_WHITE"]: 
			case $ACTION_TYPE["END_GAME_GIVE_UP_BLACK"]: 
			case $ACTION_TYPE["END_GAME_SOCKET_LOST_WHITE"]: 
			case $ACTION_TYPE["END_GAME_SOCKET_LOST_BLACK"]: 
			case $ACTION_TYPE["END_GAME_TIMEOUT_GAME_WHITE"]: 
			case $ACTION_TYPE["END_GAME_TIMEOUT_GAME_BLACK"]: 
				$_SESSION['turn'] = NO_TURN;
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
				else if ($whoLost == NONE) $_SESSION['textboxAjax'] = 'Koniec gry: Remis.';
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
?>