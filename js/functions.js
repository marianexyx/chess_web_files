var whose_turn; 
var white_player_name;
var black_player_name;
var js_loginUzytkownika 

function new_white_name(){
	if ( websocket != null ){ // jeżeli połączenie websocketowe jest aktywne (a może nie być?)
		// !! tutaj wstawić funkcje sprawdzającą nickname aktualnych graczy z core'a !!
		// !! oraz sprawdzanie czy gracz jeszcze jest zalogowany !! (nie robi się to z każdym wywołaniem funkcji?)
		//if ($getBlackPlayer != $loginUzytkownika){ 
		//var userLogin = "<? echo $loginUzytkownika ?>";
		websocket.send("white_player_name "+ js_loginUzytkownika);
		console.log( "string sent: white_player_name "+ js_loginUzytkownika);
	}
	else console.log("ERROR! websocket == null");
}

function new_black_name(){
	if ( websocket != null ){ // jeżeli połączenie websocketowe jest aktywne (a może nie być?)
		// !! tutaj wstawić funkcje sprawdzającą nickname aktualnych graczy z core'a !!
		// !! oraz sprawdzanie czy gracz jeszcze jest zalogowany !! (nie robi się to z każdym wywołaniem funkcji?)
		//if ($getBlackPlayer != $loginUzytkownika){ 
		//var userLogin = "<? echo $loginUzytkownika ?>";
		websocket.send("black_player_name "+ js_loginUzytkownika);
		console.log( "string sent: black_player_name "+ js_loginUzytkownika);
	}
	else console.log("ERROR! websocket == null");
}

function leave_white(){ //zalogowany na białym opuszcza grę
	// ! dodać alert z zapytaniem czy na pewno chce opuścić grę !
	// ! jeżeli gracz uciekł, to drugi gracz który został ma mozliwość zakończenia gry, badź grania dalej z robotem(później) !
	// ! sprawdź czy biały to mój login !
	console.log("bialy wstawaj");
	if ( websocket != null ){ // jeżeli połączenie websocketowe jest aktywne (a może nie być?)
		// !! tutaj wstawić funkcje sprawdzającą nickname aktualnych graczy z core'a !!
		// !! oraz sprawdzanie czy gracz jeszcze jest zalogowany !! (nie robi się to z każdym wywołaniem funkcji?)
		//if ($getBlackPlayer != $loginUzytkownika){ 
		websocket.send("white_player_name Biały");
		console.log("string sent: white_player_name Biały");
	}
	else console.log("ERROR! websocket == null");
}

function leave_black(){ //zalogowany na czarnym opuszcza grę
	// ! dodać alert z zapytaniem czy na pewno chce opuścić grę !
	// ! jeżeli gracz uciekł, to drugi gracz który został ma mozliwość zakończenia gry, badź grania dalej z robotem(później) !
	// ! sprawdź czy czarny to mój login !
	console.log ("czarny wstawaj");
	if ( websocket != null ){ // jeżeli połączenie websocketowe jest aktywne (a może nie być?) a co jeśli nie?
		// !! tutaj wstawić funkcje sprawdzającą nickname aktualnych graczy z core'a !!
		// !! oraz sprawdzanie czy gracz jeszcze jest zalogowany !! (nie robi się to z każdym wywołaniem funkcji?)
		//if ($getBlackPlayer != $loginUzytkownika){ 
		websocket.send("black_player_name Czarny");
		console.log("string sent: black_player_name Czarny");
	}
	else console.log("ERROR! websocket == null");
}

function check_core_var(coreVar){ //sprawdzanie wartości zmiennej na kompie
	if ( websocket != null ){ // jeżeli połączenie websocketowe jest aktywne (a może nie być?)
		// !! sprawdzanie czy gracz jeszcze jest zalogowany !! (nie robi się to z każdym wywołaniem funkcji?)
		console.log( "string to send: check " + coreVar);
		websocket.send("check " + coreVar);
		console.log( "string sent: check " + coreVar);
	}
	else console.log("ERROR! websocket == null");
}

function sendMessage() { //wysyłanie wiadomości
	var piece_from = document.getElementById("pieceFrom").value;
	var piece_to = document.getElementById("pieceTo").value;
	var strToSend = "move " + piece_from + piece_to;
	console.log("sendMessage() function engaged");
	if ( websocket != null ) { // jeżeli połączenie websocketowe jest aktywne (a może nie być?)
		document.getElementById("pieceFrom").value = ""; //czyszczenie dla kolejnych zapytań
		document.getElementById("pieceTo").value = "";
		websocket.send( strToSend );
		console.log( "string sent :", '"'+strToSend+'"' );
		debugToGameTextArea(strToSend); // !! przenieść do odpowiedzi
	}
	else console.log("ERROR! websocket == null");
}

////////////////tura gry		
function white_turn(){
	if (websocket != null){ // jeżeli połączenie websocketowe jest aktywne (a może nie być?)
		websocket.send("whose_turn white_turn");
		console.log("string sent: whose_turn white_turn");
	}
	else console.log("ERROR! websocket == null");
}

function black_turn(){
	if (websocket != null){ // jeżeli połączenie websocketowe jest aktywne (a może nie być?)
		websocket.send("whose_turn black_turn");
		console.log("string sent: whose_turn black_turn");
	}
	else console.log("ERROR! websocket == null");
}

function no_turn(){
	if (websocket != null){ // jeżeli połączenie websocketowe jest aktywne (a może nie być?)
		websocket.send("whose_turn no_turn");
		console.log("string sent: whose_turn no_turn");
	}
	else console.log("ERROR! websocket == null");
}

function switch_turn(turn_state){ 
	if(turn_state == "next_turn"){
		if (whose_turn == "white_turn") black_turn();
		else if (whose_turn == "black_turn") white_turn();
		else console.log("ERROR: WRONG whose_turn VARIABLE VALUE: " + whose_turn);
	}
	else if (turn_state == "end_game") no_turn();
	else console.log("ERROR: WRONG turn_state VARIABLE VALUE: " + turn_state);
}

