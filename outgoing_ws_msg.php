<?		//todo: ogarn�� ostatni� funkcj� st�d i j� wyrzuci�
	function checkCoreVar($coreVar) 
	{
		echo '<script>websocket.send("check "'.$coreVar.');</script>';

		$consoleMsg = 'string sent: check '.$coreVar;
		debugToConsole($consoleMsg);
	}		
?>										