<!DOCTYPE HTML>
<html lang="pl">
	<head>
		<meta charset="utf-8"/>
		<meta name="description" content="" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<title>Budgames- Szachy</title>
		
		<link rel="stylesheet" type="text/css" href="css/style.css">
		<link rel="stylesheet" type="text/css" href="css/dialogNoClose.css">
		<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css"> 
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script src="http://code.jquery.com/jquery-latest.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script> 
		<script src='https://www.google.com/recaptcha/api.js'></script>
		<script src="swfobject.js"></script>
		<!-- <script src="js/websocket.js"></script>  // todo: zmyślnie umieścić kod websocketów w zewnętrznym pliku -->
		<script src="functions.js"></script>
	</head>
	<body>	
		<div align="center" id="mainDiv">
			<div id ="menu">
				<?
					ob_start();
					session_start(); 
					require_once('include/inc.php');
					require_once('disabling.php');
					
					error_reporting( error_reporting() & ~E_NOTICE ); //wyłącz ostrzeżenie, że niezdefiniowana jest zmienna 'a' i inne tego typu
					
					if(empty($_SESSION['id'])) 
						echo '<div align="center" style="float:left; margin:0 auto;"> 
								<a href="index.php?a=register">Zarejestruj się</a> | <a href="index.php?a=login">Zaloguj się</a> 
							  </div>';
					else 
						echo '<div id="info" align="center" style="float:left; margin:0 auto;">
								<a href="#" onClick="return info();">Kontakt</a> | <a href="index.php?a=logout" onclick="return confirmLogout();">Wyloguj się</a>
							  </div>'; 
					
					switch($_GET['a']) //zmienna w pasku ustalająca stronę po ob_end_flush; 'a' pobierane z "a href'ów"
					{
						case 'home': require_once('home.php'); break; //domyślny ( w takim razie po co tutaj?)
						case 'login': require_once('login.php'); break;
						case 'register': require_once('register.php'); break;
						case 'game': require_once('game.php'); break;
						case 'logout':
						$_SESSION = array(); // czyszczenie sesji
						session_destroy(); // niszczenie sesji. resetuje się na nowe //todo: czy msie rozni od session_unset?
						header("Location: index.php"); //header przenosi na stronę główną
						break;
						default: require_once('home.php'); break;
					}
					ob_end_flush(); // wyrzygaj stronę
					?>
			</div>
			<div id="content">
				<div id="game"><!-- todo: dlaczego wogle ten cały kod php/js poniżej jest w indexie? -->
					<? 
						$user = getUser($_SESSION['id']);
						/*$_SESSION['table_id'] = 1; //póki co jest tylko jeden stół, więc zmienna zbędna*/
						$_SESSION['login'] = $user['login'];   
						echo '<script> var js_login = "'.$_SESSION['login'].'";</script>';
					?>
					
					<script> 
						$(function()  //odpala funkcje dopiero po zaladowaniu sie strony 
						{
							var clientPlainTextWindow = document.getElementById("clientPlainTextWindow");
							clientPlainTextWindow.value = "";
						});
						
						var wsUri = "ws://89.72.9.69:1234"; 
						var websocket = null; //osobne połączenia
						
						function initWebSocket() 
						{
							if ("WebSocket" in window) 
							{
								if (websocket == null) 
								{
									websocket = new WebSocket(wsUri); 
									console.log("create new websocket connection");
								} else { console.log("Already connected to webscoket server. websocket =" + websocket); }	
								
								websocket.onerror = function (evt) 
								{
									addMsgToClientPlainTextWindow('WEBSCKT ERROR: ' + evt.data, "info");
								};
								
								websocket.onclose = function (evt) 
								{
									addMsgToClientPlainTextWindow("DISCONNECTED", "info");
									console.log('Socket is closed. Reconnect will be attempted in 1 second.', evt.reason);
									websocket = null;
									setTimeout(function() { initWebSocket(); }, 1000)
								};
								
								websocket.onmessage = function (evt) 
								{ 
									console.log('msg from core: ' + evt.data);
									if (evt.data != 'connectionOnline' && evt.data != 'logout:doubleLogin')
									{
										$.ajax(
										{
											url: "on_ws_msg.php",
											type: "POST",			
											dataType: "json",
											data: { wsMsg: evt.data },
											success: function (data) 
											{ 
												if(typeof data == 'object') data = $.map(data, function(el) { return el; });
												console.log('ajax: on_ws_msg.php- success: ' + data); 
												ajaxResponse(data);
											},
											error: function(xhr, status, error) 
											{
												var err = eval("(" + xhr.responseText + ")");
												alert(err.Message);
											}
										});
									}
									else if (evt.data == 'logout:doubleLogin')
									{
										disableAll();
										stopWebSocket(); //todo: to chyba robi blad. sockety same sie wylacza po wylogowaniu
										setTimeout(function() { window.location.href = 'index.php?a=logout'; }, 5000) //todo: przetestować
										alert("Wylogowywanie: podwójny login"); //todo: to musi być przekazywane jako parametr w gecie
										window.location.href = 'index.php?a=logout';
									}
								};
								
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
									
									<? if(isset($_SESSION['login']) && !empty($_SESSION['login'])) echo 'websocket.send("im '.$_SESSION['login'].'");';
									else echo 'websocket.send("getTableDataAsJSON");'; ?>
								}
							} else alert("WebSockets not supported on your browser.");
						}
						
						function stopWebSocket() { if (websocket) websocket.close(); }
						
						setInterval(function() { websocket.send("keepConnected"); }, 250000); //[ms]
						
						initWebSocket(); //połącz z websocketami (ważne to jest tutaj by pobrać startowe wartości strony) 
					</script> 	
					
					<div id="video">
						<iframe id="ytplayer" type="text/html" width="854" height="480"
						  src="https://www.youtube.com/embed/live_stream?channel=UCLVBCJh3oKqWR2qo58BVd-w&autoplay=1&enablejsapi=1&origin=http://example.com">
						  </iframe>
					</div>
					<div id="table" align="center">
						<div id="playersBoxes">
							<div id="whitePlayerBox">
								<div id="whiteTime"><br/>30:00</div>
								<div id="whitePlayerSign"><font size="10">&#9817;</font></div>
								<div id="whitePlayerBtns">
									Gracz Biały<br/>
									<button id="whitePlayer" onClick="clickedBtn('sitOnWhite')" disabled>Loading...</button> 
									<button id="standUpWhite" onClick="clickedBtn('standUp')" disabled>Wstań</button> 
								</div>
							</div> 
							<div id="resign">
								<div id="giveUpDialog" hidden="hidden">Czy chcesz opuścić grę?</div> <!-- czy to tu musi być? -->
								<br/><button id="giveUpBtn" onClick="giveUp()" disabled>zrezygnuj</button>
							</div>
							<div id="blackPlayerBox">
								<div id="blackTime" align="center"><br/>30:00</div>
								<div id="blackPlayerSign"><font size="10">&#9823;</font></div> <!--css-->
								<div id="blackPlayerBtns">
									Gracz Czarny<br/>
									<button id="blackPlayer" onClick="clickedBtn('sitOnBlack')" disabled>Loading...</button> 
									<button id="standUpBlack" onClick="clickedBtn('standUp')" disabled>Wstań</button> 
								</div>	
							</div>
							<div style="clear:both"></div>
						</div>
						<div id="moveSection" align="center">
							Przemieść bierkę z&nbsp;&nbsp;
							<input type="text" id="pieceFrom" name="pieceFrom" maxlength="2" size="2" disabled />&nbsp;na&nbsp; 
							<input type="text" id="pieceTo"   name="pieceTo"   maxlength="2" size="2" disabled />&nbsp;&nbsp;
							<button id="movePieceButton" onClick="movePiece();" disabled >Wyślij</button> 
						</div>
					</div>
				</div>  
				<div style=" float: left;">
					<div id="clientPTE">
						<textarea readonly id="clientPlainTextWindow"></textarea>
					</div>	
					<div id="promotionContent" style="float:left"></div> <!-- todo: czy to możę być gdzie indziej? i czy musi mieć float.left?-->
					<div style="clear: both;">
						<button id="infoPTE" onClick="changePTEsource('infoPTE')" disabled>informacje</button> 
						<button id="historyPTE" onClick="changePTEsource('historyPTE')">historia</button> 
						<button id="queuePTE" onClick="changePTEsource('queuePTE')">kolejka</button> 
						&nbsp;&nbsp;<button id="queuePlayer" onClick="clickedBtn('queueMe')" disabled>Zakolejkuj</button>
						<button id="leaveQueue" onClick="clickedBtn('leaveQueue')" disabled>Opuść</button>
					</div>
					<div id="promoteDialog"> </div> <!-- bez tego nie chce mi działać dialog-promote-->
					<div id="startGameDialog" hidden="hidden">Wciśnij start, by rozpocząć grę. Pozostały czas: 120</div> 
					<div id="endOfGameDialog" hidden="hidden">Koniec gry.</div> 
					<div align="center"> 
						<iframe width="350px" height="500px" src="https://www.youtube.com/live_chat?v=i6EPyaxc-GI&embed_domain=budgames.pl">
						</iframe>
					</div>
				</div>
			</div>
			<div id="footage" align="center">
				<font size="2">
					<a href="http://cosinekitty.com/chenard/">Chess engine</a> by <a href="http://cosinekitty.com/">Don Cross</a>
				</font>
			</div>
		</div>  
		
		
		<script> //todo: zamknąć to i przenieść poza index
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
		
		<? enabling("notLoggedIn"); /*TODO: TO TU DOBRZE?*/ ?>
	</body>
</html>																									