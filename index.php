<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8"/>
		<title>Budgames - chess</title>
		<meta name="description" content="" />
		
		<style> //todo: do zewnętrznego pliku dać
			html, body { height:100%; overflow:auto; }
			body { margin:0; }
			#CameraViewer 
			{
			width: 640px;
			height: 480px; 
			}
		</style>
		<link rel="stylesheet" type="text/css" href="css/dialogNoClose.css">
		<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
		
		<script src="http://code.jquery.com/jquery-latest.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
		<script src="js/swfobject.js"></script>
		<!-- <script src="js/websocket.js"></script>  // todo: zmyślnie umieścić kod websocketów w zewnętrznym pliku -->
		<script src="js/functions.js"></script>
		<script> //stream  params //todo: do zewnętrznego pliku dać
			var flashvars = {};
			
			var params = 
			{
				menu: "false",
				scale: "noScale",
				allowFullscreen: "true",
				allowScriptAccess: "always",
				bgcolor: "",
				wmode: "direct" // can cause issues with FP settings & webcam
			};
			
			var attributes = { id:"CameraViewer" };
			
			swfobject.embedSWF
			(
			"CameraViewer.swf", 
			"altContent", "100%", "100%", "10.0.0", 
			"expressInstall.swf", 
			flashvars, params, attributes
			);
		</script>
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
						<a href="index.php?a=logout" onclick="return deleteask();">Wyloguj się</a></center>';
						
						switch($_GET['a']) //zmienna w pasku ustalająca stronę po ob_end_flush; 'a' pobierane z "a href'ów"
						{
							case 'home': require_once('home.php'); break; //domyślny ( w takim razie po co tutaj?)
							case 'login': require_once('login.php'); break;
							case 'register': require_once('register.php'); break;
							case 'game': require_once('game.php'); break;
							case 'logout':
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
						$player = getPlayer($_SESSION['table_id']); //wyciąga z bazy wiersz z danymi stołu !!!TODO: czy nie wywołuje tej samej funkcji kilka razy?*/
						$loginUzytkownika = $user['login'];  
						
						require_once('incoming_ws_msg.php');
						require_once('outgoing_ws_msg.php');
						require_once('disabling.php');
					?>
					
					<script>
						js_loginUzytkownika = <? echo json_encode($loginUzytkownika); ?>; //TODO: kiedy to się zmienia i czy trzeba to kontrolowac?
						
						$.getScript("js/functions.js", function(){});
						
						$(function()  //odpala funkcje dopiero po zaladowaniu sie strony 
						{
							var debugTextArea = document.getElementById("debugTextArea"); //konsola powiadomień
						});
						
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
								} else { console.log("Already connected to webscoket server. websocket =" + websocket); }	
								
								websocket.onerror = function (evt) 
								{
									debugToGameTextArea('WEBSCKT ERROR: ' + evt.data);
								};
								
								websocket.onclose = function (evt) 
								{
									debugToGameTextArea("DISCONNECTED");
									console.log('Socket is closed. Reconnect will be attempted in 1 second.', evt.reason);
									websocket = null;
									setTimeout(function() { initWebSocket(); }, 1000)
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
									
									checkCoreVar('tableData'); 
								}
							} else alert("WebSockets not supported on your browser.");
						}
						
						function stopWebSocket() 
						{
							if (websocket)
							websocket.close();
						}
						
						setInterval(function()
						{
							websocket.send("keepConnected");
							console.log("maintain websocket connection with core");
						}, 250000); //[ms]
						
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
					
					<div id="dialog" title="Promocja"> <p>Promuj piona na:</p> </div> <!-- bez tego nie chce mi działać dialog-promote-->
					
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
						</td>						
					</table>
					<table width="80%" cellpadding="15">
						<td align="center">
							<input type="button" id="startGame" value="start" onclick="newGame()" disabled />
							
							<button id="openGiveUpDialogButton" disabled>rezygnuję</button>
							<div id="giveUpDialog"><p>Czy chcesz opóścić grę?</p></div>
						</td>
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
		
		<script> 
			document.getElementById("pieceFrom").onkeyup = function() {pieceFromOnKeyPress()};
			document.getElementById("pieceTo").onkeyup = function() {pieceToOnKeyPress()};
			
			function pieceFromOnKeyPress() 
			{
				if (document.getElementById("pieceFrom").value.length >= 2) document.getElementById("pieceTo").focus();
			}
			
			function pieceToOnKeyPress() 
			{
				if (document.getElementById("pieceFrom").value.length >= 2 && document.getElementById("pieceTo").value.length >= 2) 
				document.getElementById("movePieceButton").focus();
			}
		</script>
		
		<script> enabling("notLoggedIn"); /*TODO: TO TU DOBRZE?*/ </script>
	</body>
</html>																							