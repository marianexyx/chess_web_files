<?php     
	//variables
	$white = "Białe"; //TODO: ! make static ! change to "white" !
	$black = "Czarne"; //TODO: ! make static ! change to "black" !
	
	//functions
    function call($sql) // Wywołanie zapytania do bazy (użytkownik)
	{
        global $con;
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
            return header("Location: index.php?a=login"); // ...Przejście do strony logowania
			exit();
        } 
		else  // Gdy ID sesji jest poprawne...
		{
            return $sid = (int)$sid; // ...Zmiana lub utrzymanie stanu ID jako integer (postać numeryczna)
        }
	 }
?>









