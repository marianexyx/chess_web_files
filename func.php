<?php     
	define("WHITE", "Białe");
	define("BLACK", "Czarne");
	$_SESSION['white'] = 'WHITE';
	$_SESSION['black'] = 'BLACK';
	
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
?>
