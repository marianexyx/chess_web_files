<!DOCTYPE HTML>
<html lang="pl">
	<head>
		<meta charset="utf-8"/>
		<meta name="description" content="" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<title>Budgames- Szachy</title>
		
		<link rel="stylesheet" type="text/css" href="css/style.css">
		<link rel="stylesheet" type="text/css" href="css/chessboardOnVideo.css">
		<link rel="stylesheet" type="text/css" href="css/dialogNoClose.css">
		<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css"> 
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script src="http://code.jquery.com/jquery-latest.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script> 
		<script src='https://www.google.com/recaptcha/api.js'></script>
		<script src="websockets.js"></script>
		<script src="functions.js"></script>
	</head>
	<body>	
		<div align="center" id="mainDiv">
			<div id ="menu">
				<?
					ob_start();
					session_start(); 
					require_once('include/inc.php');
					
					//todo: wyłączyć reportowanie w innych php'ach docelowo też
					error_reporting( error_reporting() & ~E_NOTICE ); //wyłącz ostrzeżenie, że niezdefiniowana jest zmienna 'a' i inne tego typu
					
					if(empty($_SESSION['id'])) 
						echo 
							'<div id="info" align="center"> 
								<a href="index.php?a=register">Zarejestruj się</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="index.php?a=login">Zaloguj się</a> 
							</div>
							<script>$("#additionalInfo").html("Musisz być zalogowany, aby móc grać.");</script>';
							//<script>$(function() { $("#additionalInfo").html("Musisz być zalogowany, aby móc grać."); });</script>';
					else 
						echo 
							'<div id="info" align="center">
								<a href="#" onClick="return info();">Kontakt</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="index.php?a=logout" onclick="return confirmLogout();">Wyloguj się</a>
							</div>
							<script>$("#additionalInfo").html(" ");</script>'; 
							//<script>$(function() { $("#additionalInfo").html(" "); });</script>'; 
					
					//todo: to będzie mogło być poza php'em jeżeli login i rejestracja nie będą w tabelach		  
					echo '
					<div id="serverStatus">
						|&nbsp;&nbsp;Serwer: 
						<span id="serverCSSCircleStatus" class="dot"></span> 
						<span id="serverStatusInfo">ŁĄCZENIE...</span>
					</div>
					';		  
					
					switch($_GET['a']) //zmienna w pasku ustalająca stronę po ob_end_flush; 'a' pobierane z "a href'ów"
					{
						case 'login': require_once('login.php'); break;
						case 'register': require_once('register.php'); break;
						case 'game': require_once('game.php'); break;
						case 'doubleLogin': 
							echo'<script> window.history.pushState("", "", "/index.php");
							alert("Wylogowywanie: podwójny login"); </script>'; 
							break;
						case 'logout':
							$_SESSION = array(); //czyszczenie sesji
							session_destroy(); //niszczenie sesji. resetuje się na nowe
							if ($_GET['b'] == 'doubleLogin')
								header("Location: index.php?a=doubleLogin");
							else if ($_GET['b'] == 'wrongData')
								header("Location: index.php?a=logout");
							else header("Location: index.php"); //header przenosi na stronę główną
							break;
					}
					ob_end_flush(); //wyrzygaj stronę
					?>
			</div>
			<div id="content">
				<div id="game">				
					<script> 
						function sendFirstWsMsg() 
						{  
							<? if(isset($_SESSION['login']) && !empty($_SESSION['login'])) 
								echo 'websocket.send("im '.$_SESSION['id'].'&'.$_SESSION['hash'].'");';
						   else echo 'websocket.send("getTableDataAsJSON");'; ?>
						}
												
						initWebSocket();
					</script> 	
					
					<div id="video" class="parent">
						<iframe id="ytplayer" type="text/html" width="854" height="480" src="<?= $liveStreamVideoLink ?>"></iframe>
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
							<div id="blackPlayerBox">
								<div id="blackPlayerSign">&#9823;</div>
								<div id='blackPlayerMiniBox'>
									<div id="blackPlayerBtns">
										<div id="blackTime">Gracz Czarny: 30:00</div> <!-- todo: naprawić css -->
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
					<div id="ytChat" align="center"> 
						<iframe width="330px" height="420px" src="<?= $liveStreamChatLink ?>"></iframe>
					</div>
				</div>
			</div>
			<div id="footage" align="center">
				<a href="http://cosinekitty.com/chenard/">Chess engine</a> by <a href="http://cosinekitty.com/">Don Cross</a>
			</div>
		</div>  
		
		<span id="giveUpDialog" hidden="hidden">Czy chcesz opuścić grę?</span>
		<span id="promoteDialog"></span>
		<span id="startGameDialog" hidden="hidden">Wciśnij start, by rozpocząć grę. Pozostały czas: 120</span> 
		<span id="endOfGameDialog" hidden="hidden">Koniec gry.</span> 
	</body>
</html>																									