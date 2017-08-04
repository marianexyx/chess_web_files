<?
	/*powinno byæ 5 zmiennych odpowiedzialnych za specjalne informacje i opcje:
		-consoleEnabling
		-textboxEnabling
		-consoleAjax
		-textboxAjax
		-specialOption
		
	informacje enabling powinny mieæ pierwszeñstwo przed informacjami z ajax
	ma to zastosowanie dla ka¿dego pliku php odpalanego z ajaxa, który zwraca ci¹g zmiennych $return*/
	
	require_once('../disabling.php');
	require_once('../incoming_ws_msg.php'); //checked/change/...
	
	$return = array( 'whiteName'=>'-1', 'blackName'=>'-1',
	'whiteBtn'=>'-1', 'blackBtn'=>'-1', 'standWhite'=>'-1', 'standBlack'=>'-1', 'start'=>'-1', 'giveup'=>'-1', 'from'=>'-1', 'to'=>'-1', 'send'=>'-1', 'consoleEnabling'=>'-1', 'textboxEnabling'=>'-1',
	'consoleAjax'=>'-1', 'textboxAjax'=>'-1', 'specialOption'=>'-1' );
	
	if(isset($_POST['wsMsg']))
	{
		$evt = $_POST['wsMsg']; 
		
		if 		(substr($evt,0,8) == 'newWhite') 	{ $return = newWhite(substr($evt,9)); }
		else if (substr($evt,0,8) == 'newBlack') 	{ $return = newBlack(substr($evt,9)); }
		else if	($evt == 'newOk') 					{ $return = newGameStarted(); }
		else if	(substr($evt,0,6) == 'moveOk') 		{ $return = moveRespond(substr($evt,7)); }
		else if ($evt == 'reseting')				{ $return['textboxAjax'] = "Resetownie planszy..."; }
		else if	($evt == 'ready') 					{ $return = coreIsReady(); }
		else if	(substr($evt,0,7) == 'checked') 	{ $return = checked(substr($evt,7)); }
		else if	(substr($evt,0,8) == 'promoted') 	{ $return = promoted(substr($evt,9)); }
		else if	(substr($evt,0,7) == 'badMove') 	{ $return = badMove(substr($evt,8)); }
		else $return['consoleAjax'] = 'ERROR. Unknown onMessage value = '.$evt;
	}
	else $return['consoleAjax'] = 'ERROR: !isset($_POST["wsMsg"]';
	
	foreach($return as &$value) { if (is_null($value)) { $value = '-1'; }} unset($value);
	
	header('Content-type: application/json; charset=utf-8"');
	echo json_encode($return); //, JSON_UNESCAPED_UNICODE); - polskie znaki?
?>