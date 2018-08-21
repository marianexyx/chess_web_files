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
		<link rel="stylesheet" type="text/css" href="css/logins.css">
		<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css"> 
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script src="http://code.jquery.com/jquery-latest.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script> 
		<script src='https://www.google.com/recaptcha/api.js'></script>
		<script src="websockets.js"></script>
		<script src="functions.js"></script>
	</head>
	<body>	
		<?
			session_start(); 
			require_once('include/inc.php');
			error_reporting( error_reporting() & ~E_NOTICE ); //wyłącz ostrzeżenia, że nieznana jest 'a', itd. //future: wyłączyć reportowanie w innych php'ach docelowo też
			//checkForLogout($_GET['a']);
			
			ob_start();	
			if ($_GET['a'] == 'logout' || $_GET['a'] == 'doubleLogin' || $_GET['a'] == 'wrongData')
			{
				$_SESSION = array();
				session_destroy();
				if ($_GET['a'] == 'doubleLogin')
					header("Location: index.php?a=doubleLoginAlert");
				else header("Location: index.php");
			}
			else if ($_GET['a'] == 'doubleLoginAlert')
			{			
				echo'
					<script> 
						window.history.pushState("", "", "/index.php");
						alert("Wylogowywanie: podwójny login"); 
					</script>
				'; 
			}
			
		ob_end_flush();
			/*ob_start();	
			if ($_GET['a'] == 'logout')
			{
				$_SESSION = array();
				session_destroy();
				if ($_GET['b'] == 'doubleLogin')
					header("Location: index.php?a=doubleLogin");
				else if ($_GET['b'] == 'wrongData')
					header("Location: index.php?a=logout");
				else header("Location: index.php");
			}
			ob_end_flush();*/
		?>
		<div id="mainDiv">
			<div id ="menu">
				<div id="info">
					<div id="contact"><a href="#" onClick="return info();">Kontakt</a>&nbsp;&nbsp;|</div>
					<div id="loggingSection">&nbsp;</div>
					<div id="serverStatus">
						|&nbsp;&nbsp;Serwer: 
						<span id="serverCSSCircleStatus" class="dot"></span> 
						<span id="serverStatusInfo">ŁĄCZENIE...</span>
					</div>
					<div id="user">&nbsp;</div>
				</div>
				<?
				if(empty($_SESSION['id'])) 
					echo '<script> $(function(){ showHeaderType("logging"," "); });</script>';
				else 
				{
					$user = getUser($_SESSION['id']);
					echo '<script> $(function(){ showHeaderType("logged","'.$user['login'].'"); });</script>';
				}
				
				ob_start();								
				if ($_GET['a'] == 'login')
					require_once('login.php');
				else if ($_GET['a'] == 'register')
					require_once('register.php');
				ob_end_flush();
				?>
			</div>
			<div id="content" align="center">
				<div id="game">				
					<script> 
						function sendFirstWsMsg() //todo: ajax?
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
							<div id="chessboard"> <? require_once('chessboard.php'); ?></div>
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