<?php     
	//variables
	$white = "Białe"; //TODO: ! make static ! change to "white" !
	$black = "Czarne"; //TODO: ! make static ! change to "black" !
	
	//functions
    function call($sql){ // Wywołanie zapytania do bazy (użytkownik)
        global $con;
        return mysqli_query($con, $sql);
    }
     
    function row($sql){ // Funkcja wybierająca cały szereg danych wyciąganych z bazy
        global $con;
        return @mysqli_fetch_assoc(mysqli_query($con, $sql));
    }
     
    function vtxt($var){ // Funkcja zabezpieczająca dane wysyłane do bazy
        global $con;
        return trim(mysqli_real_escape_string($con, strip_tags($var)));
    }
     
	function getUser($id){ // Funkcja wybierająca szereg danych o graczu z podanym ID
		return row("SELECT * FROM users WHERE id = ".$id);
	}
	 
    function getPlayer($table_id){ 
        return row("SELECT * FROM chess WHERE table_id =" .$table_id);
    }
	
	function checkUser($sid){ // Funkcja weryfikująca stan gracza (czy zalogowany)        sid- session id
        if(empty($sid)){ // Jeżeli puste ID sesji...
            return header("Location: index.php?a=login"); // ...Przejście do strony logowania
        } else { // Gdy ID sesji jest poprawne...
            return $sid = (int)$sid; // ...Zmiana lub utrzymanie stanu ID jako integer (postać numeryczna)
        }
	 }
	 
	function player_enabling(){ //przygaś guziki !!!!!!!!!WARUNKI MUSZĄ BYĆ OSOBNE I TAK ...co to ja miałem tutaj na myśli? na pewno funkcja aktualnie nic nei robi
		if(!isset($_SESSION['id'])){ 
			return 'disabled="enabled"';
		}
	}
	
	function stand_up_white_enabling($white_player_name, $login){
		if(isset($_SESSION['id'])){ //jeżeli gracz jest zalogowany
			if($white_player_name == $login){ //i graczem białym jest zalogowany
				return ''; //to biały guzik jest aktywny
			}
			else return 'disabled'; //ale jeżeli biały nie jest zalogowanym to guzik jest nieaktywny
		}
		else return 'disabled'; //a jeżeli gracz nie jest zalogowany, to guzik biały nieaktywny
	}
	
	function stand_up_black_enabling($black_player_name, $login){
		if(isset($_SESSION['id'])){ //jeżeli gracz jest zalogowany
			if($black_player_name == $login){ //i graczem czarnym jest zalogowany
				return ''; //to czarny guzik jest aktywny
			}
			else return 'disabled'; //ale jeżeli czarny nie jest zalogowanym to guzik jest nieaktywny
		}
		else return 'disabled"'; //a jeżeli gracz nie jest zalogowany, to guzik czarny nieaktywny
	}
	
	function start_enabling($is_white_taken, $is_black_taken){
		if ($is_white_taken == 1 || $is_black_taken == 1){
			return '';
		}
		else return 'disabled';
	}
?>









