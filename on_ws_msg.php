<?
	if(!isset($_SESSION)) session_start();
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
		
		foreach($returnArray as &$value) { if (is_null($value)) { $value = '-1'; }} unset($value);
		header('Content-type: application/json; charset=utf-8"');
		echo json_encode($returnArray);
	}
	
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