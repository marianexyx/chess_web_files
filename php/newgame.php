<?php
	if(!isset($_SESSION)) session_start();
	require_once('../disabling.php'); 
	
	$return = array( 'whiteName'=>'-1', 'blackName'=>'-1',
	'whiteBtn'=>'-1', 'blackBtn'=>'-1', 'standWhite'=>'-1', 'standBlack'=>'-1', 'start'=>'-1', 'giveup'=>'-1', 'from'=>'-1', 'to'=>'-1', 'send'=>'-1', 'consoleEnabling'=>'-1', 'textboxEnabling'=>'-1',
	'consoleAjax'=>'-1', 'textboxAjax'=>'-1', 'specialOption'=>'-1' );
	
	$enablingArr = array();
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
	
	$return['specialOption'] = "wsSend newGame";
	$return['consoleAjax'] = $return['specialOption'];
	
	foreach($return as &$value) { if (is_null($value)) { $value = '-1'; }} unset($value);
	
	header('Content-type: application/json; charset=utf-8"');
	echo json_encode($return);
?>