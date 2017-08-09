<?php
	if(!isset($_SESSION)) session_start();
	require_once('../disabling.php'); 
	require_once('../incoming_ws_msg.php');
	
	$return = array( 'whiteName'=>'-1', 'blackName'=>'-1',
	'whiteBtn'=>'-1', 'blackBtn'=>'-1', 'standWhite'=>'-1', 'standBlack'=>'-1', 'start'=>'-1', 'giveup'=>'-1', 'from'=>'-1', 'to'=>'-1', 'send'=>'-1', 'consoleEnabling'=>'-1', 'textboxEnabling'=>'-1',
	'consoleAjax'=>'-1', 'textboxAjax'=>'-1', 'specialOption'=>'-1' );
	
	$tempArr = array();
	
	$newName = '-1';
	
	if ($_SESSION['white'] == $_SESSION['login'])
	{
		$tempArr = newWhite(WHITE);
		
		$return['whiteName'] = $tempArr[0];
		$return['blackName'] = $tempArr[1];
		$return['whiteBtn'] = $tempArr[2];
		$return['blackBtn'] = $tempArr[3];
		$return['standWhite'] = $tempArr[4];
		$return['standBlack'] = $tempArr[5];
		$return['start'] = $tempArr[6];
		$return['giveup'] = $tempArr[7];
		$return['from'] = $tempArr[8];
		$return['to'] = $tempArr[9];
		$return['send'] = $tempArr[10];
		$return['consoleEnabling'] = $tempArr[11];
		$return['textboxEnabling'] = $tempArr[12];
		$return['consoleAjax'] = $tempArr[13];
		$return['textboxAjax'] = $tempArr[14];
		$return['specialOption'] = $tempArr[15];
		
		$return['textboxAjax'] = "Gracz biały opuścił stół. Wygrywa gracz czarny. Resetowanie planszy...";  //todo: to widzi tylko 1 gracz
		
		$return['specialOption'] = "wsSend change whitePlayer ".WHITE;
	}
	else if ($_SESSION['black'] == $_SESSION['login']) 
	{
		$tempArr = newBlack(BLACK);
		
		$return['whiteName'] = $tempArr[0];
		$return['blackName'] = $tempArr[1];
		$return['whiteBtn'] = $tempArr[2];
		$return['blackBtn'] = $tempArr[3];
		$return['standWhite'] = $tempArr[4];
		$return['standBlack'] = $tempArr[5];
		$return['start'] = $tempArr[6];
		$return['giveup'] = $tempArr[7];
		$return['from'] = $tempArr[8];
		$return['to'] = $tempArr[9];
		$return['send'] = $tempArr[10];
		$return['consoleEnabling'] = $tempArr[11];
		$return['textboxEnabling'] = $tempArr[12];
		$return['consoleAjax'] = $tempArr[13];
		$return['textboxAjax'] = $tempArr[14];
		$return['specialOption'] = $tempArr[15];
		
		$return['textboxAjax'] = "Gracz czarny opuścił stół. Wygrywa gracz biały. Resetowanie planszy...";
		$return['specialOption'] = "wsSend change blackPlayer ".BLACK; 
		//todo: sprawdzić te zmienne BLACK vs 'BLACK' których bodajże błędnie użyłem naprzemiennie (white too)
	}
	else $return['specialOption'] = 'ERROR: player != logged';
	
	$return['consoleAjax'] = $return['specialOption'];
	
	foreach($return as &$value) { if (is_null($value)) { $value = '-1'; }} unset($value);
	
	header('Content-type: application/json; charset=utf-8"');
	echo json_encode($return);
?>