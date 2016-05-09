<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8"/>
		<title>Budgames - chess</title>
		<meta name="description" content="" />
		<script src="http://code.jquery.com/jquery-latest.min.js"></script>
		<script src="js/swfobject.js"></script>
		<!-- <script src="js/websocket.js"></script>  // nie mogę używać php w javascripcie (jakoś tam się da, ale to jest magia)-->
		<script src="js/functions.js"></script>
		<script> //stream  params
			var flashvars = {
			};
			var params = {
				menu: "false",
				scale: "noScale",
				allowFullscreen: "true",
				allowScriptAccess: "always",
				bgcolor: "",
				wmode: "direct" // can cause issues with FP settings & webcam
			};
			var attributes = {
				id:"CameraViewer"
			};
			swfobject.embedSWF(
			"CameraViewer.swf", 
			"altContent", "100%", "100%", "10.0.0", 
			"expressInstall.swf", 
			flashvars, params, attributes
			);
		</script>
		
		<style>
			html, body { height:100%; overflow:auto; }
			body { margin:0; }
			#CameraViewer {
			width: 640px;
			height: 480px; 
			}
		</style>
		
	</head>
	<body>	
		<table  align="center"  cellspacing="5" cellpadding="5" width="900" border="1">
			<tr align="center">
				<td colspan="2">
					<?
						ob_start();
						session_start(); //mechanizm sesji online
						require_once('include/inc.php');
						
						error_reporting( error_reporting() & ~E_NOTICE ); //wyłącz ostrzeżenie, że niezdefiniowana jest zmienna 'a' i inne tego typu
						
						if(empty($_SESSION['id'])){ //jeżeli id jest puste (gracz nie jest zalogowany, id=null) wyświetl pasek z rejestracją i loginem
							echo '<center><a href="index.php?a=register">Zarejestruj się</a> | 
							<a href="index.php?a=login">Zaloguj się</a></center>';
						} //else wyświetl stronę gracza zalogowanego
						else echo '<center><a href="index.php?a=game">Info</a> | 
						<a href="index.php?a=log_out">Wyloguj się</a></center>';
						
						switch($_GET['a']) //zmienna w pasku ustalająca stronę po ob_end_flush; 'a' pobierane z "a href'ów"
						{
							case 'home': require_once('home.php'); break; //domyślny ( w takim razie po co tutaj?)
							case 'login': require_once('login.php'); break;
							case 'register': require_once('register.php'); break;
							case 'game': require_once('game.php'); break;
							case 'log_out':
							$_SESSION = array(); // czyszczenie sesji pustą tablicą
							session_destroy(); // niszczenie sesji. resetuje się na nowe
							header("Location: index.php"); //header przenosi na stronę główną
							break;
							default: require_once('home.php'); break;
						}
						ob_end_flush(); // wyrzygaj stronę
					?>
				</td>
			</tr>
			<tr>
				<td align="center" valign="top">
					<? 
						$user = getUser($_SESSION['id']);
						/*$_SESSION['table_id'] = 1; //póki co jest tylko jeden stół, więc zmienna zbędna
							$player = getPlayer($_SESSION['table_id']); //wyciąga z bazy wiersz z danymi stołu !!! czy nie wywołuje tej samej funkcji kilka razy?
							$getWhitePlayer = $player['white_player_name']; 
							$getBlackPlayer = $player['black_player_name']; 
							$isWhiteTaken = $player['is_white_taken']; 
						$isBlackTaken = $player['is_black_taken']; */
						$loginUzytkownika = $user['login'];  
					?>
					
					<script>
						js_loginUzytkownika = <? echo json_encode($loginUzytkownika); ?>;
						var white_player_name;
						var black_player_name;
					</script>
					
					<script> 
						$(function() { //odpala funkcje dopiero po zaladowaniu sie strony
							var debugTextArea = document.getElementById("debugTextArea"); //konsola powiadomień
						});
						
						$(function(white_player_name, black_player_name, js_loginUzytkownika){ // ta funkcja jest tu prewencyjnie, bo istneiej to przy okazji checków
							if(white_player_name == js_loginUzytkownika)
							{ 
								if (document.getElementById("white_player").value == "Biały")
								document.getElementById("stand_up_white").disabled = false; //to guzik jest aktywny
							}
							else document.getElementById("stand_up_white").disabled = true;
							
							if(black_player_name == js_loginUzytkownika)
							{ 
								if (document.getElementById("black_player").value == "Czarny")
								document.getElementById("stand_up_black").disabled = false; //to guzik jest aktywny
							}
							else document.getElementById("stand_up_black").disabled = true;
						});
						
						function debugToGameTextArea(message) {
							debugTextArea.value += message + "\n";
							debugTextArea.scrollTop = debugTextArea.scrollHeight;
						}
						
						var wsUri = "ws://89.66.209.51:1234"; //parametry połączenia
						var websocket = null; //osobne połączenia
						
						function start_game_function(){
							// ! warunek by nie zaczynać gry, jeżeli gracz nie opuści gry !
							if (websocket != null && document.getElementById("start_game").disabled == false) { //jeżeli mamy połączenie i przycisk dało się wcisnąć
								document.getElementById("start_game").disabled = true; // jeżeli wcisnął gracz start raz, to przycisk się wyłącza 
								//@up !! w razie problemów przycisk musi ponownie zadziałać !!
								var wiadomosc = "new"; //wiadomość rozpoczynająca nową grę
								websocket.send(wiadomosc); //wyślij wiadomośc na serwer
								console.log("send 'new' websocket command to server");
							}
							else debugToGameTextArea("WebSocket is null");
						}
						
						function initWebSocket(){ //rozpocznij grę/połączenie. rozdzielić deklarację funkcji od wysłania waidomości "new"
							//console.log("start_game() function enabled");
							if ("WebSocket" in window){ //jeżeli przeglądarka obsługuje websockety
								if (websocket == null){ //jeżeli websocket niepołączony
									websocket = new WebSocket(wsUri); //nowe połączenie
									console.log("create new websocket connection");
								} else { console.log("Already connected to webscoket server"); }	
								
								websocket.onerror = function (evt) {
									debugToGameTextArea('WEBSCKT ERROR: ' + evt.data);
								};
								
								websocket.onclose = function (evt) {
									debugToGameTextArea("DISCONNECTED");
									//websocket = new WebSocket(wsUri); //!! experyment !!! wznawianie połączenia zawsze jak się z jakiegoś powodu rozłączymy z websocketami
									//console.log("reconnect to websocket server");
								};
								
								websocket.onmessage = function (evt) {
									var new_player_nickname;
									var what_is_checked;
									var checked_value;
									console.log('msg from websckt be4 case: ', evt.data); 
									if (evt.data.substr(0,9) == 'new_white' || evt.data.substr(0,9) == 'new_black'){ //jeżeli wiadomość zaczyna się od "nowy-gracz"
										new_player_nickname = evt.data.substr(10); //wyciągnij z wiadomości nick gracza
										if (evt.data.substr(0,9) == 'new_white')
										console.log('new_white: '+ new_player_nickname); 
										else if (evt.data.substr(0,9) == 'new_black')
										console.log('new_black: '+ new_player_nickname);
										wbsct_switch = evt.data.substr(0,9);
										console.log('wbsct_switch: ', wbsct_switch); 
									}
									else if (evt.data.substr(0,10) == 'whose_turn'){ //sprawdź czy wiadomość dotyczy aktualnej tury
										whose_turn = evt.data.substr(11); //wyciągnij z wiadomości czyja tura
										//console.log('whose_turn: '+ whose_turn); 
										wbsct_switch = evt.data.substr(0,10);
									}
									else if (evt.data.substr(0,7) == 'checked'){ //jeżeli sprawdzaliśmy wartość w core
										wbsct_switch = evt.data.substr(0,7); //w switchu będzie obsłuzony przypadek "changed"
										if (evt.data.substr(0,13) == 'checked_wp_is'){ //jeżeli sprawdzana wartość dotyczy białego gracza
											what_is_checked = evt.data.substr(0,13); //przypisujemy co chcemy sprawdzić
											checked_value = evt.data.substr(14); //a to jest wartość tego co chcieliśmy sprawdzić
										}
										else if (evt.data.substr(0,13) == 'checked_bp_is'){ //jeżeli sprawdzana wartość dotyczy czarnego gracza
											what_is_checked = evt.data.substr(0,13); //przypisujemy co chcemy sprawdzić
											checked_value = evt.data.substr(14); //a to jest wartość tego co chcieliśmy sprawdzić
										}
										else if (evt.data.substr(0,13) == 'checked_wt_is'){ //jeżeli sprawdzana wartość dotyczy aktualnej tury
											what_is_checked = evt.data.substr(0,13); //przypisujemy co chcemy sprawdzić
											checked_value = evt.data.substr(14); //a to jest wartość tego co chcieliśmy sprawdzić
										}
										else console.log('ERROR! BAD checked ENUM TYPE');
									}
									else if (evt.data.substr(0,16) == 'game_in_progress'){ //przyszło info o tym, że ruch został wykonany i jaki to ruch
										movement_made = evt.data.substr(17); 
										if (whose_turn == "white_turn"){
											wiadomoscNaTextArea = "Biały wykonał ruch: " + movement_made + ". Ruch wykonują Czarne.";
											debugToGameTextArea(wiadomoscNaTextArea);
										}
										else if (whose_turn == "black_turn"){
											wiadomoscNaTextArea = "Czarny wykonał ruch: " + movement_made + ". Ruch wykonują Białe.";
											debugToGameTextArea(wiadomoscNaTextArea);
										}
										else console.log('ERROR! Bad whose_turn value');
										switch_turn("next_turn"); //niech turę wykonuje przeciwnik
										console.log( 'Piece movement successed. Message received: ', evt.data );
									}
									else if (evt.data.substr(0,8) == 'BAD_MOVE'){
										if (document.getElementById("white_player").value == js_loginUzytkownika && whose_turn == "white_turn"){
											wiadomoscNaTextArea = "Błędne rządzenie ruchu: " + evt.data.substr(9) + "! Wpisz inny ruch.";
											debugToGameTextArea(wiadomoscNaTextArea);
										}
										
										else if (document.getElementById("black_player").value == js_loginUzytkownika && whose_turn == "black_turn"){
											wiadomoscNaTextArea = "Błędne rządzenie ruchu: " + evt.data.substr(9) + "! Wpisz inny ruch.";
											debugToGameTextArea(wiadomoscNaTextArea);
										}
									}
									else if (evt.data.substr(0,16) == 'connectionOnline'){ //jeżeli mamy odpowiedź z websocket servera, że połączenie
										//jest podtrzymane czyli wykonał odpowiedź na najprostsze zapytanie, to wyrzuć potwierdzenie na konsolę.
										//funkcja wykonywana jest przez switch niżej, bo mam tak niestety zbudowaną funkcję że wszystko tamtędy leci
										//EDIT: a może ten błąd był wywoływany tylko przez server websocket?
										wbsct_switch = 'connectionMaintained';
									}
									else wbsct_switch = evt.data;
									switch(wbsct_switch) { //!! warunek by nie wchodziło w switch, jeżeli wbsct_switch nie był zmieniany !!
										case 'connectionMaintained':
										console.log("connection with weboscket server maintained");
										break;
										case 'new_game': //jeżeli chenard server odpowie "OK" to znaczy że włączył nową grę
										debugToGameTextArea("Nowa gra rozpoczęta. Białe wykonują ruch.");
										document.getElementById("start_game").disabled = true;
										white_turn(); 
										break;
										case 'white_won': //białe wygrały
										switch_turn("end_game");
										debugToGameTextArea("Koniec gry: Białe wygrały");
										break;
										case 'black_won': //czarne wygrały
										switch_turn("end_game");
										debugToGameTextArea("Koniec gry: Czarne wygrały");
										break;
										case 'new_white': 
										if (new_player_nickname == "Biały") { //jak nikt już nie jest zalogowany na białym, tj. gracz który był na białym powstał
											document.getElementById("white_player").value = "Biały"; //to białe zostaje "Biały"m
											<? 	if(!empty($_SESSION['id'])){ //tylko dla zalogowanych
												echo 'document.getElementById("white_player").disabled = false; //da się usiąść na białym
												if (document.getElementById("black_player").value == "Czarny") //dodatkowo jak czarny to "Czarne" (tj. nikt)
												document.getElementById("black_player").disabled = false; //to czarny też da się wcisnąć i usiąść';
											} ?>
											document.getElementById("stand_up_black").disabled = true; //na czarnym też nie, ale to jest tylko zabezpieczenie
											document.getElementById("stand_up_white").disabled = true; //i jak nikt nie siedzi na białym to nikt nie może wstać na białym
											document.getElementById("start_game").disabled = true; //i jeżeli jakimś cudem dałoby się wcisnąć "start", no to się nie da
											debugToGameTextArea("Biały gracz opóścił stół"); 
											console.log('Biały gracz = "Biały"');
										}
										else { //jeżeli ktoś siada na białych, tj. przesłany był jego nick 
											document.getElementById("white_player").value = new_player_nickname; //wartość buttonu zmienia się na jego nick
											white_player_name = new_player_nickname; //zapamiętaj nazwę białego
											document.getElementById("white_player").disabled = true; //nie da się usiąść na białym
											if (document.getElementById("white_player").value == js_loginUzytkownika){ //jeżeli gracz biały jest zalogowanym
												document.getElementById("black_player").disabled = true; //to nie może on usiąść na czarnych 
												document.getElementById("stand_up_white").disabled = false; //tylko ten co siedzi może wstać
											}
											debugToGameTextArea("Gracz figur białych: "+ new_player_nickname);
											console.log('Biały gracz = jakiś nick (!="Biały") :', new_player_nickname);
											if (document.getElementById("white_player").value != "Biały" && document.getElementById("black_player").value != "Czarny") //jeżeli biały i czarny siedzą na stole
											document.getElementById("start_game").disabled = false; //to da się wcisnąć start
										}
										break;
										case 'new_black':
										if (new_player_nickname == "Czarny"){ //jak nikt nie zalogowany na czarnym
											document.getElementById("black_player").value = "Czarny";
											<? 	if(!empty($_SESSION['id'])){ //tylko dla zalogowanych
												echo 'document.getElementById("black_player").disabled = false; //da się usiąść na czarnym
												if (document.getElementById("white_player").value == "Biały") //dodatkowo jak biały to "Biały" (tj. nikt)
												document.getElementById("white_player").disabled = false; //to biały też da się wcisnąć i usiąść';
											} ?>
											document.getElementById("stand_up_white").disabled = true; //i jak nikt nie siedzi na białym to nikt nie może wstać na białym
											document.getElementById("stand_up_black").disabled = true; //nie da się wstać jak nikt nie siedzi
											document.getElementById("start_game").disabled = true; //to jest tylko zabezpieczenie
											debugToGameTextArea("Czarny gracz opóścił stół");
											console.log('Czarny gracz = "Czarny"');
										}
										else { //jeżeli ktoś siada na czarnych, tj. przesłany był jego nick 
											document.getElementById("black_player").value = new_player_nickname; //wartość buttonu czarnego zmienia się na jego nick
											black_player_name = new_player_nickname; //zapamiętaj nazwę czarnego
											document.getElementById("black_player").disabled = true; //nikt nie może usiąść na czarnym jak tam właśnie siadł gracz
											if (document.getElementById("black_player").value == js_loginUzytkownika){ //jeżeli gracz czarny jest zalogowanym
												document.getElementById("white_player").disabled = true; //to nie może on usiąść jednocześnie na białych
												document.getElementById("stand_up_black").disabled = false; //tylko ten co siedzi może wstać 
											}
											debugToGameTextArea("Gracz figur czarnych: "+ new_player_nickname);
											console.log('Czarny gracz = jakiś nick (!="Czarny") :', new_player_nickname);
											if (document.getElementById("white_player").value != "Biały" && document.getElementById("black_player").value != "Czarny") //jeżeli biały i czarny siedzą na stole
											document.getElementById("start_game").disabled = false; //to da się wcisnąć start
										}
										break;
										case 'draw': //remis
										// !!! co dalej?? !!
										debugToGameTextArea("Koniec gry: Remis");
										break;
										case 'whose_turn': //gdy core powie nam, że wartość tury się zmieniła
										if (whose_turn == "white_turn"){ //jeżeli teraz przypada tura białego
											//console.log("White player turn. Waiting for move...");
											if (document.getElementById("white_player").value == js_loginUzytkownika){ // zmiany w panelu białego gracza
												document.getElementById('pieceFrom').disabled = false;
												document.getElementById('pieceTo').disabled = false;
												document.getElementById('movePieceButton').disabled = false; // !! przycisk do wysyłania ma działać tylko gdy oba powyższe pola są dobrze wypełnione
												// !! zezwolenie na stronie/core by ruch mógł teraz wykonać tylko biały
												console.log("(white info) Ruch wykonuje teraz: Biały");
											}
											else if (document.getElementById("black_player").value = js_loginUzytkownika){ // zmiany w panelu czarnego gracza
												document.getElementById('pieceFrom').disabled = true;
												document.getElementById('pieceTo').disabled = true;
												document.getElementById('movePieceButton').disabled = true; // !! przycisk do wysyłania ma działać tylko gdy oba powyższe pola są dobrze wypełnione
												// !! zezwolenie na stronie/core by ruch mógł teraz wykonać tylko biały
												console.log("(black info) Ruch wykonuje teraz: Biały");
											}
											else console.log("ERROR: STATEMENT DOESNT MET- NO LOGGED PLAYER AVAILABLE (PLAYERS NICK VALUES ARE EMPTY- SHOULDNT BE POSSIBLE)");
										}
										else if (whose_turn == 'black_turn'){ //jeżeli teraz przypada tura białego
											//console.log('Black player turn. Waiting for move...');
											if (document.getElementById('white_player').value == js_loginUzytkownika){ // zmiany w panelu białego gracza
												document.getElementById('pieceFrom').disabled = true;
												document.getElementById('pieceTo').disabled = true;
												document.getElementById('movePieceButton').disabled = true; // !! przycisk do wysyłania ma działać tylko gdy oba powyższe pola są dobrze wypełnione
												// !! zezwolenie na stronie/core by ruch mógł teraz wykonać tylko biały
												console.log("(white info) Ruch wykonuje teraz: Czarny");
											}
											else if (document.getElementById("black_player").value == js_loginUzytkownika){ // zmiany w panelu czarnego gracza
												document.getElementById('pieceFrom').disabled = false;
												document.getElementById('pieceTo').disabled = false;
												document.getElementById('movePieceButton').disabled = false; // !! przycisk do wysyłania ma działać tylko gdy oba powyższe pola są dobrze wypełnione
												// !! zezwolenie na stronie/core by ruch mógł teraz wykonać tylko biały
												console.log("(black info) Ruch wykonuje teraz: Czarny");
											}
											else console.log('ERROR: STATEMENT DOESNT MET- NO LOGGED PLAYER AVAILABLE (PLAYERS NICK VALUES ARE EMPTY- SHOULDNT BE POSSIBLE)');
										}
										else if (whose_turn == 'no_turn'){
											console.log('End of game. No turn available. Waiting for new game...');	
											document.getElementById('pieceFrom').disabled = true;
											document.getElementById('pieceTo').disabled = true;
											document.getElementById('movePieceButton').disabled = true;
											// !! nikt nie może wykonać ruchu !! chyba ok, bo gra nic oprócz 'new' nie przyjmie
										}
										else console.log('ERROR: WRONG whose_turn VARIABLE');
										break;
										case 'checked': // !!!sprawdzić "new_player" pod kątem funkcji do skopiowania. brak sprawdzania dla ludzi niezalogowanych 
										if (what_is_checked == 'checked_wp_is'){ //jeżeli sprawdzana wartośc w core to gracz biały...
											white_player_name = checked_value;	
											document.getElementById('white_player').value = white_player_name; //...to nazwa białego jest tym kto siedzi na białym wg core...
											if (document.getElementById("white_player").value != "Biały") { //...i jeżeli na białym jest jakiś gracz...
												document.getElementById('white_player').disabled = true; //...to nikt nie może usiąść na białym...
												if(white_player_name == js_loginUzytkownika) //...i jeżeli sprawdzany gracz w core to gracz będący zalogowanym...
													document.getElementById("stand_up_white").disabled = false; //...to przycisk do wstawania jest aktywny.
												console.log('Biały gracz = jakiś nick (!="Biały") :',white_player_name);  
											}
											else if (document.getElementById("white_player").value == "Biały"){  //jednak jeżeli nikt nie siedzi na białym...
												<? if(!empty($_SESSION['id'])){ //...i klient jest zalogowany na stronie...
													echo 'document.getElementById("white_player").disabled = false; //...to mozna usiąść na białym...
													document.getElementById("stand_up_white").disabled = true; //...a guzik wstawania jest wyłączony.
													console.log("Biały gracz = Biały");';
												} ?>
											}
										}							
										else if (what_is_checked == 'checked_bp_is'){ //jeżeli sprawdzana wartość w core to gracz czarny...
											black_player_name = checked_value;
											document.getElementById('black_player').value = black_player_name; //...to nazwa czarnego jest tym kto siedzi na czarnym wg core...
											if (document.getElementById("black_player").value != "Czarny") { //...i jeżeli na czarnym jest jakiś gracz...
												document.getElementById('black_player').disabled = true; //...to nikt nie może usiąść na czarnym...
												if(black_player_name == js_loginUzytkownika) //...i jeżeli sprawdzany gracz w core to gracz będący zalogowanym...
													document.getElementById("stand_up_black").disabled = false; //...to przycisk do wstawania jest aktywny.
												console.log('Czarny gracz = jakiś nick (!="Czarny") :',black_player_name);  
											}
											else if (document.getElementById("black_player").value == "Czarny"){ <? //jednak jeżeli nikt nie siedzi na czarnym...
												if(!empty($_SESSION['id'])){ //i klient jest zalogowany na stronie...
													echo 'document.getElementById("black_player").disabled = false; //to mozna usiąść na czarnym...
													console.log("Czarny gracz = Czarny");';
												} ?>
											}
										}
										else if (what_is_checked == 'checked_wt_is'){ //jeżeli sprawdzana wartość to aktualna tura...
											// !! ?? w sumie nie wiem jeszcze jak to wykorzystać, ale przyda się na pewno- console.log('checked whose_turn is ' + checked_value);
										}
										else {
											console.log('ERROR! WRONG checked ENUM VALUE (this should never be possible...)');
											//console.log('ERROR! WRONG checked ENUM VALUE (this should never be possible...)');
										}
										break;
										case 'error': //chenard dał inną odpowiedź
										debugToGameTextArea('ERROR: Niedozwolony ruch');
										break;	
										default: //webscoket dał inną odpowiedź
										console.log( 'ERROR! Wrong WebSocket message received: ' + evt.data );
									}
								};
								
								websocket.onopen = function (evt) { 
									var stateStr;
									switch (websocket.readyState) {
										case 0: { stateStr = "CONNECTING"; break; }
										case 1: { stateStr = "OPEN"; break; }
										case 2: { stateStr = "CLOSING";	break; }
										case 3: { stateStr = "CLOSED"; break; }
										default: { stateStr = "UNKNOW"; break; }
									}
									console.log("WebSocket state = " + websocket.readyState + " ( " + stateStr + " )");
									check_core_var('white_player'); //zczytaj z core nazwe aktualnego gracza białego
									check_core_var('black_player'); //sczytaj z core nazwe aktualnego gracza czarnego
								}
							} else alert("WebSockets not supported on your browser.");
						}
						
						var piece_from;
						var piece_to;
						
						function wrongInputs(){
							debugToGameTextArea("Błędnie wprowadzone zapytanie o ruch.");
							document.getElementById("pieceFrom").value="";
							document.getElementById("pieceTo").value="";
							console.log('piece_from: ','"' + piece_from+'"');
							console.log('piece_from.length: ','"' + piece_from.length+'"');
							console.log('piece_to: ','"' + piece_to+'"');
							console.log('piece_to.length: ','"' + piece_to.length+'"');
						}
						
						function sendMessage() { //wysyłanie wiadomości o ruchu figurą
							piece_from = document.getElementById("pieceFrom").value;
							piece_to = document.getElementById("pieceTo").value;
							var strToSend;
							var squareLetters = ['a','b','c','d','e','f','g','h','A','B','C','D','E','F','G','H']; //tabela dozwolonych znaków dla 1go znaku obu inputów
							if (piece_from.length == 2 && piece_to.length == 2) { //jeżeli długość obu stringów jest odpowiednia
								if (piece_from.charAt(1) <= 8 && piece_to.charAt(1) <= 8 && piece_from.charAt(1) >= 1 && piece_to.charAt(1) >= 1){ 	//jeżeli druga litera obu inputów mieści się w zakresie (1-8):
									mainloop: 
									for (var n = 0; n < squareLetters.length; n++){
										if (piece_from.charAt(0) == squareLetters[n]) { //jeżeli 1sza litera 1go inputu jest między (a-h,A-H)
											for (var m = 0; m < squareLetters.length; m++){
												if (piece_to.charAt(0) == squareLetters[m]) { //jeżeli 1sza litera 2go inputu jest między (a-h,A-H)
													console.log("sendMessage() function engaged");
													strToSend = "move " + piece_from + piece_to;
													if ( websocket != null ){ // jeżeli połączenie websocketowe jest aktywne (a może nie być?)
														document.getElementById("pieceFrom").value = ""; //czyszczenie dla kolejnych zapytań
														document.getElementById("pieceTo").value = "";
														websocket.send( strToSend );
														console.log( "string sent :", '"'+strToSend+'"' );
														//debugToGameTextArea(strToSend);
													}
													else {
														debugToGameTextArea("Not connected to server");
														console.log("websocket not connected");
													}
													break mainloop;
												}
												else if (m == squareLetters.length && piece_from.charAt(0) != squareLetters[squareLetters.length]) wrongInputs();
												if (m > squareLetters.length) wrongInputs();
											}
										}
										else if (n == squareLetters.length && piece_from.charAt(0) != squareLetters[squareLetters.length]) wrongInputs();
										if (n > squareLetters.length) wrongInputs();
									}
								}
								else wrongInputs();
							}
							else wrongInputs();	
						}
						
						function stopWebSocket() { //wyłącz połączenie - nie wiem czy potrzebuje. trochę poniżej to samo prawie
							if (websocket)
							websocket.close();
						}
						
						function checkSocket() { //sprawdź stan połączenia - będzie do usuniecia kiedyś
							if (websocket != null) { 
								var stateStr;
								switch (websocket.readyState) {
									case 0: { stateStr = "CONNECTING"; break; }
									case 1: { stateStr = "OPEN"; break; }
									case 2: { stateStr = "CLOSING";	break; }
									case 3: { stateStr = "CLOSED"; break; }
									default: { stateStr = "UNKNOW"; break; }
								}
								debugToGameTextArea("WebSocket state = " + websocket.readyState + " ( " + stateStr + " )");
							} else debugToGameTextArea("WebSocket is null");
						}
						
						/*function end_game(){ //zakończ grę/połączenie !! jeżeli przypadkiem połączenie padnie, nawiąż je ponownie (czy tak można? w 
							//sensie czy websocket nie disconnectuje się przy wychodzeniu ze strony?
							if (websocket) {
							websocket.close();
							websocket = null; //to wyżej nie zeruje? potrzebne to wogle?
							}
						}*/
						
						//co ok. 4 min. podtrzymuj połączenie z websocketami (po 10 minutach następuje samoistne rozłączenie)
						setInterval(function(){
							websocket.send("keepConnected");
							console.log("Wysłana do websocket servera pusta wiadomość podtrzymującą połączenie");
						},250000); //1000 [ms]= 1s
						
						initWebSocket(); //połącz z websocketami (ważne to jest tutaj by pobrać startowe wartości strony) 
					</script> 	
					<div id="altContent">
						<h1>CameraViewer</h1>
						<p><a href="http://www.adobe.com/go/getflashplayer">Get Adobe Flash player</a></p><br/>
					</div>
					<p>
						<textarea readonly id="debugTextArea" style="width:400px;height:170px;"></textarea>
					</p>
					<p>
						Przemieść figurę z&nbsp;&nbsp;<input type="text" id="pieceFrom" maxlength="2" size="2" disabled />&nbsp;na&nbsp; 
						<input type="text" id="pieceTo" maxlength="2" size="2" onkeydown="if(event.keyCode==13)sendMessage();" disabled />&nbsp;&nbsp;
						<button id="movePieceButton" onClick="sendMessage();" disabled >Wyślij</button>
					</p>
				</td>  
				<td align="center" valign="top">
					<table width="100%" cellpadding="15">
						<td align= "left">
							<img src="grafiki/white_pawn.jpg" alt="w_pawn" />
							<input type="button" id="white_player" onClick="new_white_name()" value="Loading..." disabled />
							<input type="button" id="stand_up_white" onClick="leave_white()" value="Wstań" disabled />
						</td> 
						<td align="right">
							<img src="grafiki/black_pawn.jpg" alt="b_pawn" />
							<input type="button" id="black_player" onClick="new_black_name()" value="Loading..." disabled />
							<input type="button" id="stand_up_black" onClick="leave_black()" value="Wstań" disabled />
							<!-- <script>stand_up_enabling(white_player_name, black_player_name, js_loginUzytkownika);</script> -->
						</td>						
					</table>
					<table width="80%" cellpadding="15">
						<!-- <td align="left">Czas 1</td> -->
						<td align="center"><? echo '<input type="button" id="start_game" value="start" onclick="start_game_function()" disabled />'?> </td>
						<!-- <td align="right">Czas 2</td> -->
					</table> 
					<!-- chat -->
					<div id="chatarea" data-style="large"></div>
					<script type="text/javascript" src="chatfiles/chatfunctions.js"></script>
					<!-- /chat -->
				</td>
			</tr>
		</table>  
		<!--<p> przyciski do ręcznego operowania połączeia z websocketami
			<button onClick="initWebSocket();">Connect</button> 
			<button onClick="stopWebSocket();">Disconnect</button>
			<button onClick="checkSocket();">State</button> 
		</p>-->
		<p align="center">
			<font size="2">
				<a href="http://cosinekitty.com/chenard/">Chess engine</a> by <a href="http://cosinekitty.com/">Don Cross</a>
				| Camera stream by Michał Blaumann
			</font>
		</p>
	</body>
</html>																							