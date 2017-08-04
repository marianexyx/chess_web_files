<?		//todo: ogarn¹æ ostatni¹ funkcjê st¹d i j¹ wyrzuciæ
	function checkCoreVar($coreVar) 
	{
		echo '<script>websocket.send("check "'.$coreVar.');</script>';

		$consoleMsg = 'string sent: check '.$coreVar;
		debugToConsole($consoleMsg);
	}		
?>										