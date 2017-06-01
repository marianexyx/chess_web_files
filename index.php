<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8"/>
		<title>Budgames - chess</title>
		<meta name="description" content="" />
		
		<script src="http://code.jquery.com/jquery-latest.min.js"></script>
		<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
		
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
							$player = getPlayer($_SESSION['table_id']); //wyciąga z bazy wiersz z danymi stołu !!!TODO: czy nie wywołuje tej samej funkcji kilka razy?
							$getWhitePlayer = $player['whitePlayerName']; 
							$getBlackPlayer = $player['blackPlayerName']; 
							$isWhiteTaken = $player['is_white_taken']; 
						$isBlackTaken = $player['is_black_taken']; */
						$loginUzytkownika = $user['login'];  
						
						require_once('incoming_ws_msg.php');
						require_once('outgoing_ws_msg.php');
					?>
					
					<script>
						js_loginUzytkownika = <? echo json_encode($loginUzytkownika); ?>; //TODO: kiedy to się zmienia i czy trzeba to kontrolowac?
						var whitePlayerName; //todo: W functions.js są te same zmienne
						var blackPlayerName;

						$(function()  //odpala funkcje dopiero po zaladowaniu sie strony 
						{
							var debugTextArea = document.getElementById("debugTextArea"); //konsola powiadomień
						});
												
						$(function(whitePlayerName, blackPlayerName, js_loginUzytkownika) // ta funkcja jest tu prewencyjnie, bo istnieje to przy okazji checków
						{
							if(whitePlayerName == js_loginUzytkownika)
							{ 
								if (document.getElementById("whitePlayer").value == "Biały")
								document.getElementById("standUpWhite").disabled = false; //to guzik jest aktywny
							}
							else document.getElementById("standUpWhite").disabled = true;
							
							if(blackPlayerName == js_loginUzytkownika)
							{ 
								if (document.getElementById("blackPlayer").value == "Czarny")
								document.getElementById("standUpBlack").disabled = false; //to guzik jest aktywny
							}
							else document.getElementById("standUpBlack").disabled = true;
						});
						
						function debugToGameTextArea(message) 
						{
							debugTextArea.value += message + "\n";
							debugTextArea.scrollTop = debugTextArea.scrollHeight;
						}
						
						var wsUri = "ws://89.66.209.51:1234"; //parametry połączenia 
						var websocket = null; //osobne połączenia
												
						function initWebSocket() 
						{
							if ("WebSocket" in window) //jeżeli przeglądarka obsługuje websockety
							{
								if (websocket == null) 
								{
									websocket = new WebSocket(wsUri); 
									console.log("create new websocket connection");
								} else { console.log("Already connected to webscoket server"); }	
								
								websocket.onerror = function (evt) 
								{
									debugToGameTextArea('WEBSCKT ERROR: ' + evt.data);
								};
								
								websocket.onclose = function (evt) 
								{
									debugToGameTextArea("DISCONNECTED");
									//websocket = new WebSocket(wsUri); //TODO: experyment -wznawianie połączenia zawsze jak się z jakiegoś powodu rozłączymy z websocketami
									//console.log("reconnect to websocket server");
								};
								
								websocket.onmessage = function (evt) { onMessage(evt) };
								
								websocket.onopen = function (evt) 
								{ 
									var stateStr;
									switch (websocket.readyState) 
									{
										case 0: { stateStr = "CONNECTING"; break; }
										case 1: { stateStr = "OPEN"; break; }
										case 2: { stateStr = "CLOSING";	break; }
										case 3: { stateStr = "CLOSED"; break; }
										default: { stateStr = "UNKNOW"; break; }
									}
									console.log("WebSocket state = " + websocket.readyState + " ( " + stateStr + " )");
									checkCoreVar('whitePlayer'); //zczytaj z core nazwe aktualnego gracza białego
									checkCoreVar('blackPlayer'); //sczytaj z core nazwe aktualnego gracza czarnego
								}
							} else alert("WebSockets not supported on your browser.");
						}
						
						var piece_from;
						var piece_to;
											
						function stopWebSocket()  //wyłącz połączenie - nie wiem czy potrzebuje. trochę poniżej to samo prawie
						{
							if (websocket)
							websocket.close();
						}
						
						/*function end_game(){ //zakończ grę/połączenie. TODO: jeżeli przypadkiem połączenie padnie, nawiąż je ponownie (czy tak można? w 
							//sensie czy websocket nie disconnectuje się przy wychodzeniu ze strony?
							if (websocket) {
							websocket.close();
							websocket = null; //TODO: to wyżej nie zeruje? potrzebne to wogle?
							}
						}*/
						
						setInterval(keepConnected),250000); //[ms]. Co 4 min podtrzymuj połączenie (po 10 min następuje WS sam się rozłączy)
						
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
						<input type="text" id="pieceTo" maxlength="2" size="2" onkeydown="if(event.keyCode==13)movePiece();" disabled />&nbsp;&nbsp;
						<button id="movePieceButton" onClick="movePiece();" disabled >Wyślij</button>
					</p>
					
					<!--<div id="dialog-promote" title="promotion">Promuj piona na:</div> --><!-- testy okienka poup z buttonami-->
					<!--<button id="opener-promote">Promocja</button> <!--  okienko popup z buttonami-->
					
				</td>  
				<td align="center" valign="top">
					<table width="100%" cellpadding="15">
						<td align= "left">
							<img src="grafiki/white_pawn.jpg" alt="w_pawn" />
							<input type="button" id="whitePlayer" onClick="newWhiteName()" value="Loading..." disabled />
							<input type="button" id="standUpWhite" onClick="leaveWhite()" value="Wstań" disabled />
						</td> 
						<td align="right">
							<img src="grafiki/black_pawn.jpg" alt="b_pawn" />
							<input type="button" id="blackPlayer" onClick="newBlackName()" value="Loading..." disabled />
							<input type="button" id="standUpBlack" onClick="leaveBlack()" value="Wstań" disabled />
							<!-- <script>stand_up_enabling(whitePlayerName, blackPlayerName, js_loginUzytkownika);</script> -->
						</td>						
					</table>
					<table width="80%" cellpadding="15">
						<!-- <td align="left">Czas 1</td> -->
						<td align="center"><? echo '<input type="button" id="startGame" value="start" onclick="newGame()" disabled />'?> </td>
						<!-- <td align="right">Czas 2</td> -->
					</table> 
					<!-- chat -->
					<div id="chatarea" data-style="large"></div>
					<script type="text/javascript" src="chatfiles/chatfunctions.js"></script>
					<!-- /chat -->
				</td>
			</tr>
		</table>  

		<p align="center">
			<font size="2">
				<a href="http://cosinekitty.com/chenard/">Chess engine</a> by <a href="http://cosinekitty.com/">Don Cross</a>
				| Camera stream by Michał Blaumann
			</font>
		</p>
	</body>
</html>																							