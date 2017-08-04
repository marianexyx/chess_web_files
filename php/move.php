<?php
	require_once('../disabling.php'); 
	
	$return = array( 'whiteName'=>'-1', 'blackName'=>'-1',
	'whiteBtn'=>'-1', 'blackBtn'=>'-1', 'standWhite'=>'-1', 'standBlack'=>'-1', 'start'=>'-1', 'giveup'=>'-1', 'from'=>'-1', 'to'=>'-1', 'send'=>'-1', 'consoleEnabling'=>'-1', 'textboxEnabling'=>'-1',
	'consoleAjax'=>'-1', 'textboxAjax'=>'-1', 'specialOption'=>'-1' );
	
	$enablingArr = array('-1','-1','-1','-1','-1','-1','-1','-1','-1','-1');
	
	if (!empty($_POST) && !empty($_POST['pieceFrom']) && !empty($_POST['pieceTo']))
	{
		$pieceFrom = $_POST['pieceFrom'];
		$pieceTo = $_POST['pieceTo'];
		
		$squareLetters = ['a','b','c','d','e','f','g','h','A','B','C','D','E','F','G','H'];
		if (strlen($pieceFrom) == 2 && strlen($pieceTo) == 2 && $pieceFrom[1] <= 8 && $pieceTo[1] <= 8 && $pieceFrom[1] >= 1 && $pieceTo[1] >= 1 && in_array($pieceFrom[0],$squareLetters) && in_array($pieceTo[0],$squareLetters))
		{
			$strToSend = "move ".$pieceFrom.$pieceTo;
			
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
			
			$return['specialOption'] = "wsSend ".$strToSend; 
			$return['consoleAjax'] = $return['specialOption'];
			
			if ($return['specialOption'] != '-1' && substr($return['specialOption'],0,6) != 'wsSend') 
			$return['textboxAjax'] = "Błędnie wprowadzone zapytanie o ruch.";
		}
		else $return['consoleAjax'] = 'ERROR: move: unknown from/to: '.$pieceFrom.'/'.$pieceTo;
	}
	else $return['consoleAjax'] = 'ERROR: move: puste zmienne from/to';	
	
	foreach($return as &$value) { if (is_null($value)) { $value = '-1'; }} unset($value);
	
	header('Content-type: application/json; charset=utf-8"');
	echo json_encode($return);
	
?>