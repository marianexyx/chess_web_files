<?
	if(!isset($_SESSION)) session_start();
	require_once('../disabling.php'); 
	
	$enablingArr = array();
	$consoleAjax = '-1';
	$textboxAjax = '-1'; 
	$specialOption = '-1';
	$queueMsg = '-1'; 
	$queueList = '-1';
	$_SESSION['wtime'] = -1;
	$_SESSION['btime'] = -1;
	$_SESSION['turn'] = -1;
	
	if ($_SESSION['white'] == $_SESSION['login'] || $_SESSION['black'] == $_SESSION['login'])
	{
		if ($_SESSION['white'] == $_SESSION['login']) $_SESSION['white'] = 'WHITE';
		else if ($_SESSION['black'] == $_SESSION['login']) $_SESSION['black'] = 'BLACK';
		$enablingArr = enabling('clickedBtn');
		$textboxAjax = "Opuszczanie stołu..."; 
		$specialOption = "wsSend logoutMe";
	}

	$consoleAjax = $specialOption;
	
	$return = array( $_SESSION['white'], $_SESSION['black'], $consoleAjax, $textboxAjax, $specialOption, 
	$enablingArr[0], $enablingArr[1], $enablingArr[2], $enablingArr[3], $enablingArr[4], $enablingArr[5], $enablingArr[6], $enablingArr[7], $enablingArr[8], $enablingArr[9], $enablingArr[10], $enablingArr[11], $enablingArr[12],
	$queueMsg, $queueList, $_SESSION['wtime'], $_SESSION['btime'], $_SESSION['turn'] );
	
	foreach($return as &$value) { if (is_null($value)) { $value = '-1'; }} unset($value);
	
	header('Content-type: application/json; charset=utf-8"');
	echo json_encode($return);
?>