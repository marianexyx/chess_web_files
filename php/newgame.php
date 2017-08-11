<?php
	require_once('../disabling.php'); 
	
	$enablingArr = array();
	$consoleAjax = '-1';
	$textboxAjax = '-1'; 
	$specialOption = '-1';
	$queueMsg = '-1'; 
	$queueList = '-1';
	
	$enablingArr = enabling('clickedBtn');
	
	$specialOption = "wsSend newGame";
	$consoleAjax = $specialOption;
	
	$return = array( '-1', '-1', $consoleAjax, $textboxAjax, $specialOption, 
	$enablingArr[0], $enablingArr[1], $enablingArr[2], $enablingArr[3], $enablingArr[4], $enablingArr[5], $enablingArr[6], $enablingArr[7], $enablingArr[8], $enablingArr[9], $enablingArr[10], $enablingArr[11], $enablingArr[12],
	$queueMsg, $queueList );
	
	foreach($return as &$value) { if (is_null($value)) { $value = '-1'; }} unset($value);
	
	header('Content-type: application/json; charset=utf-8"');
	echo json_encode($return);
?>