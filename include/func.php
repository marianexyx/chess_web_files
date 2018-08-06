<?php     
	define("WHITE", "White");
	define("BLACK", "Black");
		
	define("NO_TURN", "noTurn");
	define("WHITE_TURN", "whiteTurn");
	define("BLACK_TURN", "blackTurn");
	$_SESSION['turn'] = NO_TURN;
	
    function call($sql) // Wywołanie zapytania do bazy (użytkownik)
	{
        global $con; //to nie jest deklaracja, tylko odwołanie się do zmiennej globalnej, bo funkcję łapią zasięgiem bodajże tylko zmienne lokalne
        return mysqli_query($con, $sql);
    }
     
    function row($sql) // Funkcja wybierająca cały szereg danych wyciąganych z bazy
	{
        global $con;
        return @mysqli_fetch_assoc(mysqli_query($con, $sql)); //fetch_assoc- odwoływanie się do kolumn po ich nazwach
    }
     
    function vtxt($var) // Funkcja zabezpieczająca dane wysyłane do bazy
	{
        global $con;
        return trim(mysqli_real_escape_string($con, strip_tags($var)));
		//trim() - Usuwa białe, puste znaki z początku oraz końca ciągu.
    }
     
	function getUser($id) // Funkcja wybierająca szereg danych o graczu z podanym ID
	{
		return row("SELECT * FROM users WHERE id = ".$id);
	}
	 
    function getPlayer($table_id)
	{
        return row("SELECT * FROM chess WHERE table_id =" .$table_id);
    }
	
	function checkUser($sid) // Funkcja weryfikująca stan gracza (czy zalogowany)        sid- session id
	{
        if(empty($sid)) // Jeżeli puste ID sesji...
		{
            return header("Location: index.php?a=login"); // ...przejście do strony logowania
        } 
		else  // Gdy ID sesji jest poprawne...
		{
            return $sid = (int)$sid; // ...zmiana lub utrzymanie stanu ID jako integer (postać numeryczna)
        }
	 }
	 
	function debugToConsole($data) 
	{
		$output = $data;
		if (is_array($output))
        $output = implode(',', $output);

		echo '<script> console.log( "Debug Objects: '.$output.'"); </script>';
	}
	
	function getLiveStreamID($type)
	{
		$videoId = null;

		$CHANNEL_ID = 'UCLVBCJh3oKqWR2qo58BVd-w';
		if ($data = file_get_contents('https://www.youtube.com/embed/live_stream?channel='.$CHANNEL_ID))
		{
			// Find the video ID in there
			if(preg_match('/\'VIDEO_ID\': \"(.*?)\"/', $data, $matches))
				$videoId = $matches[1];
			else $videoId = 'Couldn\'t find video ID';
		}
		else $videoId = 'Couldn\'t fetch data';

		if ($type == "video") return 'https://www.youtube.com/embed/'.$videoId
		.'?autoplay=1&enablejsapi=1controls=0&disablekb=0&fs=0&iv_load_policy=3&modestbranding=1&origin=http://budgames.pl&rel=0&showinfo=0';
		else if ($type == "chat") return 'https://www.youtube.com/live_chat?v='.$videoId.'&embed_domain=budgames.pl';
		else return $videoId;
	} 
	$liveStreamID = getLiveStreamID();
	$liveStreamVideoLink = getLiveStreamID('video');
	$liveStreamChatLink = getLiveStreamID('chat');
?>
