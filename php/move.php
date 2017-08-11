<?php
	require_once('../disabling.php'); 
		
	$enablingArr = array();
	$consoleAjax = '-1';
	$textboxAjax = '-1'; 
	$specialOption = '-1';
	$queueMsg = '-1'; 
	$queueList = '-1';
	
	if (!empty($_POST) && !empty($_POST['pieceFrom']) && !empty($_POST['pieceTo']))
	{
		$pieceFrom = $_POST['pieceFrom'];
		$pieceTo = $_POST['pieceTo'];
		
		$squareLetters = ['a','b','c','d','e','f','g','h','A','B','C','D','E','F','G','H'];
		if (strlen($pieceFrom) == 2 && strlen($pieceTo) == 2 && $pieceFrom[1] <= 8 && $pieceTo[1] <= 8 && $pieceFrom[1] >= 1 && $pieceTo[1] >= 1 && in_array($pieceFrom[0],$squareLetters) && in_array($pieceTo[0], $squareLetters))
		{
			$enablingArr = enabling('clickedBtn');
			
			$strToSend = "move ".$pieceFrom.$pieceTo;
			$specialOption = "wsSend ".$strToSend; 
			$consoleAjax = $specialOption;
			
			if ($specialOption != '-1' && substr($specialOption,0,6) != 'wsSend') 
			$textboxAjax = "Błędnie wprowadzone zapytanie o ruch.";
		}
		else $consoleAjax = 'ERROR: move: unknown from/to: '.$pieceFrom.'/'.$pieceTo;
	}
	else $consoleAjax = 'ERROR: move: puste zmienne from/to';	
	
	$return = array( '-1', '-1', $consoleAjax, $textboxAjax, $specialOption, 
	$enablingArr[0], $enablingArr[1], $enablingArr[2], $enablingArr[3], $enablingArr[4], $enablingArr[5], $enablingArr[6], $enablingArr[7], $enablingArr[8], $enablingArr[9], $enablingArr[10], $enablingArr[11], $enablingArr[12],
	$queueMsg, $queueList );
	
	foreach($return as &$value) { if (is_null($value)) { $value = '-1'; }} unset($value);
	
	header('Content-type: application/json; charset=utf-8"');
	echo json_encode($return);
	
?>