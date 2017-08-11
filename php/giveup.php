<?
	if(!isset($_SESSION)) session_start();
	require_once('../disabling.php'); 
	
	$enablingArr = array();
	$consoleAjax = '-1';
	$textboxAjax = '-1'; 
	$specialOption = '-1';
	$queueMsg = '-1'; 
	$queueList = '-1';
	
	if ($_SESSION['white'] == $_SESSION['login'])
	{
		//todo: brakuje tu komunikatu dla wszystkich mówiącego, że gracz uciekł, a drugi wygrał
		$_SESSION['white'] = 'WHITE';
		$enablingArr = enabling('whiteEmpty');
		$textboxAjax = "Gracz biały opuścił stół. Wygrywa gracz czarny.";  //todo: to widzi tylko 1 gracz
		$specialOption = "wsSend change whitePlayer ".WHITE;
	}
	else if ($_SESSION['black'] == $_SESSION['login']) 
	{
		//todo: brakuje tu komunikatu dla wszystkich mówiącego, że gracz uciekł, a drugi wygrał
		$_SESSION['black'] = 'BLACK';
		$enablingArr = enabling('blackEmpty');
		$textboxAjax = "Gracz czarny opuścił stół. Wygrywa gracz biały.";
		$specialOption = "wsSend change blackPlayer ".BLACK; 
		//todo: sprawdzić te zmienne BLACK vs 'BLACK' których bodajże błędnie użyłem naprzemiennie (white too)
	}
	else $specialOption = 'ERROR: player != logged';
	
	$consoleAjax = $specialOption;
	
	$return = array( $_SESSION['white'], $_SESSION['black'], $consoleAjax, $textboxAjax, $specialOption, 
	$enablingArr[0], $enablingArr[1], $enablingArr[2], $enablingArr[3], $enablingArr[4], $enablingArr[5], $enablingArr[6], $enablingArr[7], $enablingArr[8], $enablingArr[9], $enablingArr[10], $enablingArr[11], $enablingArr[12],
	$queueMsg, $queueList );
	
	foreach($return as &$value) { if (is_null($value)) { $value = '-1'; }} unset($value);
	
	header('Content-type: application/json; charset=utf-8"');
	echo json_encode($return);
?>