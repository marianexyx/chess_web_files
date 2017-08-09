<?		//todo: ogarnąć ostatnią funkcję stąd i ją wyrzucić
	function checkCoreVar($coreVar) 
	{
		echo '<script>websocket.send("check "'.$coreVar.');</script>';

		$consoleMsg = 'string sent: check '.$coreVar;
		debugToConsole($consoleMsg);
	}		
?>										