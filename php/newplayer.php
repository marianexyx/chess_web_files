<?php
	if(!isset($_SESSION)) session_start();
	require_once('../disabling.php'); 
	
	$enablingArr = array();
	$consoleAjax = '-1';
	$textboxAjax = '-1'; 
	$specialOption = '-1';
	$queueMsg = '-1'; 
	$queueList = '-1';
	
	if(isset($_POST['type']))
	{
		if ($_POST['type'] == "whitePlayer" || $_POST['type'] == "blackPlayer" || $_POST['type'] == "standUpWhite" || $_POST['type'] == "standUpBlack")
		{
			//TODO: tutaj wstawić funkcje sprawdzającą nickname aktualnych graczy z core'a -zabezpieczenia
			//TODO: sprawdzanie czy gracz jeszcze jest zalogowany (nie robi się to z każdym wywołaniem funkcji?)- w innych plikach podobnie zrobić
			//if ($getBlackPlayer != $loginUzytkownika){ 
			//var userLogin = "< ? echo $loginUzytkownika ? >"; - TODO: super stare komentarze (coś tu trzeba ogarniać? nie wiem)
			$enablingArr = enabling('clickedBtn');
			
			$newName; 
			if ($_POST['type'] == "whitePlayer" || $_POST['type'] == "blackPlayer") $newName = $_POST['type']." ".$_SESSION['login'];
			else if ($_POST['type'] == "standUpWhite") $newName = "whitePlayer WHITE";
			else if ($_POST['type'] == "standUpBlack") $newName = "blackPlayer BLACK";
			else $newName = 'ERROR: unknown $_POST["type"]';
				
			$specialOption = "wsSend change ".$newName;
			$consoleAjax = $specialOption;
		} 
		else $consoleAjax = 'unknown $_POST["type"] value ='.$_POST['type'];
	} 
	else $consoleAjax = '!isset($_POST["type"])';
	
	$return = array( '-1', '-1', $consoleAjax, $textboxAjax, $specialOption, 
	$enablingArr[0], $enablingArr[1], $enablingArr[2], $enablingArr[3], $enablingArr[4], $enablingArr[5], $enablingArr[6], $enablingArr[7], $enablingArr[8], $enablingArr[9], $enablingArr[10], $enablingArr[11], $enablingArr[12],
	$queueMsg, $queueList );
	
	foreach($return as &$value) { if (is_null($value)) { $value = '-1'; }} unset($value);
	
	header('Content-type: application/json; charset=utf-8"');
	echo json_encode($return);
?>