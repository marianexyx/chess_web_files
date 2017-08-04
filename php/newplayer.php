<?php
	if(!isset($_SESSION)) session_start();
	require_once('../disabling.php'); 
	
	$return = array( 'whiteName'=>'-1', 'blackName'=>'-1',
	'whiteBtn'=>'-1', 'blackBtn'=>'-1', 'standWhite'=>'-1', 'standBlack'=>'-1', 'start'=>'-1', 'giveup'=>'-1', 'from'=>'-1', 'to'=>'-1', 'send'=>'-1', 'consoleEnabling'=>'-1', 'textboxEnabling'=>'-1',
	'consoleAjax'=>'-1', 'textboxAjax'=>'-1', 'specialOption'=>'-1' );
	
	$enablingArr = array();
	
	if(isset($_POST['type']))
	{
		if ($_POST['type'] == "whitePlayer" || $_POST['type'] == "blackPlayer" || $_POST['type'] == "standUpWhite" || $_POST['type'] == "standUpBlack")
		{
			//TODO: tutaj wstawić funkcje sprawdzającą nickname aktualnych graczy z core'a -zabezpieczenia
			//TODO: sprawdzanie czy gracz jeszcze jest zalogowany (nie robi się to z każdym wywołaniem funkcji?)- w innych plikach podobnie zrobić
			//if ($getBlackPlayer != $loginUzytkownika){ 
			//var userLogin = "< ? echo $loginUzytkownika ? >"; - TODO: super stare komentarze (coś tu trzeba ogarniać? nie wiem)
			$enablingArr = enabling('clickedBtn');
			
			$return['whiteBtn'] = $enablingArr[0];
			$return['blackBtn'] = $enablingArr[1];
			$return['standWhite'] = $enablingArr[2];
			$return['standBlack'] = $enablingArr[3];
			$return['start'] = $enablingArr[4];
			$return['giveup'] = $enablingArr[5];
			$return['from'] = $enablingArr[6];
			$return['to'] = $enablingArr[7];
			$return['send'] = $enablingArr[8];
			$return['consoleEnabling'] = $enablingArr[9]; 
			$return['textboxEnabling'] = $enablingArr[10];
			
			$newName; 
			if ($_POST['type'] == "whitePlayer" || $_POST['type'] == "blackPlayer") $newName = $_POST['type']." ".$_SESSION['login'];
			else if ($_POST['type'] == "standUpWhite") $newName = "whitePlayer WHITE";
			else if ($_POST['type'] == "standUpBlack") $newName = "blackPlayer BLACK";
			else $newName = 'ERROR: unknown $_POST["type"]';
				
			$return['specialOption'] = "wsSend change ".$newName;
			$return['consoleAjax'] = $return['specialOption'];
		} 
		else $return['consoleAjax'] = 'unknown $_POST["type"] value ='.$_POST['type'];
	} 
	else $return['consoleAjax'] = '!isset($_POST["type"])';
	
	foreach($return as &$value) { if (is_null($value)) { $value = '-1'; }} unset($value);
	
	header('Content-type: application/json; charset=utf-8"');
	echo json_encode($return);
?>