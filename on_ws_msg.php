<?
	if(!isset($_SESSION)) session_start();
	
	if ($_SESSION['consoleAjax'] != '-1') 
		$returnArray["consoleMsg"] = $_SESSION['consoleAjax'];
	
	if(!isset($_SESSION['clientsArr']) || empty($_SESSION['clientsArr']))
		$_SESSION['clientsArr'] = array();
	
	if(!isset($_SESSION['synchronized']))
		$_SESSION['synchronized'] = false;
	
	$TABLE_DATA = array
	(
		"NONE" => "0",
		"ACTION" => "1",
		"WHITE_PLAYER" => "2",
		"BLACK_PLAYER" => "3",
		"GAME_STATE" => "4",
		"WHITE_TIME" => "5",
		"BLACK_TYPE" => "6",
		"QUEUE" => "7",
		"START_TIME" => "8",
		"HISTORY" => "9",
		"PROMOTIONS" => "10",
		"ERROR" => "11"
	);
	
	$ACTION_TYPE = array
	(
		"NONE" => "0",
		"SYNCHRONIZED" => "1",
		"NEW_WHITE_PLAYER" => "2",
		"NEW_BLACK_PLAYER" => "3",
		"NEW_GAME_STARTED" => "4",
		"BAD_MOVE" => "5",
		"RESET_COMPLITED" => "6",
		"DOUBLE_LOGIN" => "7",
		"REMOVE_AND_REFRESH_CLIENT" => "8",
		"END_GAME_NONE" => "9", //error type. end_of_game can't be none
		"END_GAME_NORMAL_WIN_WHITE" => "10",
		"END_GAME_NORMAL_WIN_BLACK" => "11",
		"END_GAME_DRAW" => "12",
		"END_GAME_GIVE_UP_WHITE" => "13",
		"END_GAME_GIVE_UP_BLACK" => "14",
		"END_GAME_SOCKET_LOST_WHITE" => "15",
		"END_GAME_SOCKET_LOST_BLACK" => "16",
		"END_GAME_TIMEOUT_GAME_WHITE" => "17",
		"END_GAME_TIMEOUT_GAME_BLACK" => "18", 
		"END_GAME_ERROR" => "19",
		"ERROR" => "99"
	);
	
	$GAME_STATE = array
	(
		"ERROR" => "0",
		"TURN_NONE_WAITING_FOR_PLAYERS" => "1",
		"TURN_NONE_WAITING_FOR_START_CONFIRMS" => "2",
		"TURN_NONE_RESETING" => "3",
		"TURN_WHITE" => "4",
		"TURN_WHITE_FIRST_TURN" => "5",
		"TURN_WHITE_PROMOTE" => "6",
		"TURN_BLACK" => "7",
		"TURN_BLACK_PROMOTE" => "8"
	);
	
	require_once('saveCoreData.php');
	require_once('calcCoreData.php');
	
	if(isset($_POST['wsMsg']))
	{	
		resetSessionData();
		saveCoreDataInSessionVars($_POST['wsMsg']);
		$calculatedDataArr = calculateDataFromSessionVars(); 
		
		$returnArray = array();
		if ($_SESSION['consoleAjax'] != '-1') $returnArray["consoleMsg"] = $_SESSION['consoleAjax'];
		if ($_SESSION['textboxAjax'] != '-1') $returnArray["PTEmsg"] = $_SESSION['textboxAjax'];
		if ($_SESSION['turn'] != '-1') $returnArray["whoseTurn"] = $_SESSION['turn'];
		if ($_SESSION['whitePlayer'] != '-1') $returnArray["whitePlayerName"] = $_SESSION['whitePlayer'];
		if ($_SESSION['blackPlayer'] != '-1') $returnArray["blackPlayerName"] = $_SESSION['blackPlayer'];
		if ($_SESSION['whiteTime'] != '-1') $returnArray["whitePlayerTimeLeft"] = $_SESSION['whiteTime'];
		if ($_SESSION['blackTime'] != '-1') $returnArray["blackPlayerTimeLeft"] = $_SESSION['blackTime'];
		if ($_SESSION['startTime'] != '-1') $returnArray["startTimeLeft"] = $_SESSION['startTime'];
		if ($_SESSION['history'] != '-1') $returnArray["historyOfMoves"] = $_SESSION['history'];
		if ($_SESSION['promoted'] != '-1') $returnArray["promotedPawnsList"] = $_SESSION['promoted'];
		if ($_SESSION['queue'] != '-1') $returnArray["queuedPlayers"] = $_SESSION['queue'];
		$returnArray["clientIsLogged"] = $calculatedDataArr["clientIsLogged"];
		$returnArray["clientIsSynchronized"] = $calculatedDataArr["clientIsSynchronized"];
		if (isset($_SESSION['login']) && !empty($_SESSION['login']) != '-1' && $_SESSION['login'] != '-1') $returnArray["clientName"] = $_SESSION['login'];
		$returnArray["loggedPlayerIsOnAnyChair"] = $calculatedDataArr["loggedPlayerIsOnAnyChair"];
		$returnArray["loggedPlayerIsOnWhiteChair"] = $calculatedDataArr["loggedPlayerIsOnWhiteChair"];
		$returnArray["loggedPlayerIsOnBlackChair"] = $calculatedDataArr["loggedPlayerIsOnBlackChair"];
		$returnArray["tableIsFull"] = $calculatedDataArr["tableIsFull"];
		$returnArray["clientIsInQueue"] = $calculatedDataArr["clientIsInQueue"];
		$returnArray["whitePlayerBtn"] = $calculatedDataArr["whitePlayerBtn"];
		$returnArray["blackPlayerBtn"] = $calculatedDataArr["blackPlayerBtn"];
		$returnArray["playerCanMakeMove"] = $calculatedDataArr["playerCanMakeMove"];
		$returnArray["queuePlayerBtn"] = $calculatedDataArr["queuePlayerBtn"];
		$returnArray["leaveQueueBtn"] = $calculatedDataArr["leaveQueueBtn"];
		if ($calculatedDataArr["specialOption"] != '-1') $returnArray["specialOption"] = $calculatedDataArr["specialOption"];
		else $returnArray["consoleMsg"] .= " wtf, calculatedDataArr = -1??: ".$calculatedDataArr["specialOption"]; ///tests.......................
		
		foreach($returnArray as &$value) { if (is_null($value)) { $value = '-1'; }} unset($value);
		header('Content-type: application/json; charset=utf-8"');
		echo json_encode($returnArray);
	}
	//else 
		
	function resetSessionData()
	{
		$_SESSION['consoleAjax'] = '-1'; 
		$_SESSION['textboxAjax'] = '-1'; 
		$_SESSION['turn'] = '-1';
		$_SESSION['whiteTime'] = '-1';
		$_SESSION['blackTime'] = '-1';
		$_SESSION['startTime'] = '-1';
		$_SESSION['history'] = '-1';
		$_SESSION['promoted'] = '-1';
		$_SESSION['queue'] = '-1';
		$_SESSION['action'] = '-1';
	}
?>