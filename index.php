<!DOCTYPE HTML>
<html lang="pl">
	<head>
		<meta charset="utf-8"/>
		<meta name="description" content="" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<title>Budgames- Szachy</title>
		
		<link rel="stylesheet" type="text/css" href="css/style.css">
		<link rel="stylesheet" type="text/css" href="css/tooltip.css">
		<link rel="stylesheet" type="text/css" href="css/chessboardOnVideo.css">
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
						echo '<div id="info" align="center"> 
								<a href="index.php?a=register">Zarejestruj się</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="index.php?a=login">Zaloguj się</a> 
							  </div>
							  <script>$(function() { $("#additionalInfo").html("Musisz być zalogowany, aby móc grać."); });</script>
							  ';
					else 
						echo '<div id="info" align="center">
								<a href="#" onClick="return info();">Kontakt</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="index.php?a=logout" onclick="return confirmLogout();">Wyloguj się</a>
							  </div>
							  <script>$(function() { $("#additionalInfo").html(" "); });</script>
							  '; 
					//to będzie mogło być poza php'em jeżeli login; rejestracja nie będą w tabelach		  
					echo '
					<div id="serverStatus">
						|&nbsp;&nbsp;Serwer: 
						<span id="serverCSSCircleStatus" class="dot"></span> 
						<span id="serverStatusInfo">ŁĄCZENIE...</span>
					</div>
					';		  
					
					switch($_GET['a']) //zmienna w pasku ustalająca stronę po ob_end_flush; 'a' pobierane z "a href'ów"
					{
						case 'home': require_once('home.php'); break; //domyślny (todo: w takim razie po co tutaj?)
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
							var clientPlainTextWindow = $("#clientPlainTextWindow");
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
									console.log('WEboscket error:', evt.data);
									serverStatus("offline");
								};
								
								websocket.onclose = function (evt) 
								{
									console.log('Socket is closed. Reconnect will be attempted in 1 second.', evt.reason);
									serverStatus("offline");
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
										case 0: { stateStr = "CONNECTING"; serverStatus("connecting"); break; }
										case 1: { stateStr = "OPEN"; serverStatus("online"); break; }
										case 2: { stateStr = "CLOSING";	serverStatus("offline"); break; }
										case 3: { stateStr = "CLOSED"; serverStatus("offline"); break; }
										default: { stateStr = "UNKNOW"; serverStatus("offline"); break; }
									}
									console.log("WebSocket state = " + websocket.readyState + " ( " + stateStr + " )");
									
									<? if(isset($_SESSION['login']) && !empty($_SESSION['login'])) echo 'websocket.send("im '.$_SESSION['id'].'&'.$_SESSION['hash'].'");';
									else echo 'websocket.send("getTableDataAsJSON");'; ?>
								}
							} else alert("WebSockets not supported on your browser.");
						}
						
						function stopWebSocket() { if (websocket) websocket.close(); }
						
						setInterval(function() { websocket.send("keepConnected"); }, 250000); //[ms]
						
						initWebSocket(); //połącz z websocketami (ważne to jest tutaj by pobrać startowe wartości strony) 
					</script> 	
					
					<div id="video" class="parent">
						<iframe id="ytplayer" type="text/html" width="854" height="480"
						  src="https://www.youtube.com/embed/live_stream?channel=UCLVBCJh3oKqWR2qo58BVd-w&autoplay=1&enablejsapi=1&origin=http://example.com">
						</iframe>
						<div id="perspective">
							<div id="chessboard">
								<div>
									<div class="white_square" id="h1" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="g1" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="f1" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="e1" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="d1" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="c1" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="b1" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="a1" onclick="clickBoardField(this);"></div>
								</div>
								<div>
									<div class="black_square" id="h2" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="g2" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="f2" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="e2" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="d2" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="c2" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="b2" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="a2" onclick="clickBoardField(this);"></div>
								</div>
								<div>
									<div class="white_square" id="h3" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="g3" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="f3" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="e3" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="d3" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="c3" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="b3" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="a3" onclick="clickBoardField(this);"></div>
								</div>
								<div>
									<div class="black_square" id="h4" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="g4" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="f4" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="e4" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="d4" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="c4" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="b4" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="a4" onclick="clickBoardField(this);"></div>
								</div>
								<div>
									<div class="white_square" id="h5" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="g5" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="f5" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="e5" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="d5" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="c5" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="b5" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="a5" onclick="clickBoardField(this);"></div>
								</div>
								<div>
									<div class="black_square" id="h6" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="g6" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="f6" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="e6" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="d6" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="c6" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="b6" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="a6" onclick="clickBoardField(this);"></div>
								</div>
								<div>
									<div class="white_square" id="h7" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="g7" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="f7" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="e7" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="d7" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="c7" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="b7" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="a7" onclick="clickBoardField(this);"></div>
								</div>
								<div>
									<div class="black_square" id="h8" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="g8" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="f8" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="e8" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="d8" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="c8" onclick="clickBoardField(this);"></div>
									<div class="black_square" id="b8" onclick="clickBoardField(this);"></div>
									<div class="white_square" id="a8" onclick="clickBoardField(this);"></div>
								</div>
							</div>
						</div>
					</div>
					<div id="additionalInfo"></div>
					<div id="table" align="center">
						<div id="playersBoxes">
							<div id="whitePlayerBox">
								<div id="whitePlayerSign">&#9817;</div>
								<div id='whitePlayerMiniBox'>
									<div id="whitePlayerBtns">
										<div id="whiteTime">Gracz Biały: 30:00</div>
										<button id="whitePlayer" onClick="clickedBtn('sitOnWhite')" disabled>-</button> 
										<button id="standUpWhite" onClick="clickedBtn('standUp')" hidden="hidden" disabled>Wstań</button> 
									</div>
								</div>
							</div> 
							<div id="resign">
								<div id="giveUpDialog" hidden="hidden">Czy chcesz opuścić grę?</div> <!-- todo: czy to tu musi być? czy to może być spanem? czy mozę być z resztą dialogów?-->
							</div>
							<div id="blackPlayerBox">
								<div id="blackPlayerSign">&#9823;</div>
								<div id='blackPlayerMiniBox'>
									<div id="blackPlayerBtns">
										<div id="blackTime">Gracz Czarny: 30:00</div>
										<button id="blackPlayer" onClick="clickedBtn('sitOnBlack')" disabled>-</button> 
										<button id="standUpBlack" onClick="clickedBtn('standUp')" hidden="hidden" disabled>Wstań</button> 
									</div>	
								</div>
							</div>
							<div style="clear:both"></div>
						</div>
						<div id="promotionContent"></div> 
						<div style="clear:both"></div>
					</div>
				</div>  
				<div id="textBoxes">
					<div id="clientPTE">
						<textarea readonly id="clientPlainTextWindow"></textarea>
					</div>	
					<div id="pteType">
						<button id="infoPTE" onClick="changePTEsource('infoPTE')" disabled>stół</button> 
						<button id="historyPTE" onClick="changePTEsource('historyPTE')">historia</button> 
						<button id="queuePTE" onClick="changePTEsource('queuePTE')">kolejka</button> 
						&nbsp;&nbsp;<button id="queuePlayer" onClick="clickedBtn('queueMe')" disabled>kolejkuj</button>
						<button id="leaveQueue" onClick="clickedBtn('leaveQueue')" disabled>opuść</button>
					</div>
					<!-- todo: sprawdzić czy mogę to wywalić gdzieś na granice kodu -->
					<span id="promoteDialog"></span> <!-- bez tego nie chce mi działać dialog-promote-->
					<span id="startGameDialog" hidden="hidden">Wciśnij start, by rozpocząć grę. Pozostały czas: 120</span> 
					<span id="endOfGameDialog" hidden="hidden">Koniec gry.</span> 
					<div id="ytChat" align="center"> 
						<iframe width="330px" height="420px" src="https://www.youtube.com/live_chat?v=i6EPyaxc-GI&embed_domain=budgames.pl">
						</iframe>
					</div>
				</div>
			</div>
			<div id="footage" align="center">
				<a href="http://cosinekitty.com/chenard/">Chess engine</a> by <a href="http://cosinekitty.com/">Don Cross</a>
			</div>
		</div>  
				
		<? enabling("notLoggedIn"); /*TODO: TO TU DOBRZE?*/ ?>
	</body>
</html>																									