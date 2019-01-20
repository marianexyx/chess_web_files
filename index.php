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
		<link rel="stylesheet" type="text/css" href="css/tooltip.css">
		<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css"> 
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script src="http://code.jquery.com/jquery-latest.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script> 
		<script src='https://www.google.com/recaptcha/api.js'></script>
		<script src="websockets.js"></script>
		<script src="headerText.js"></script>
		<script src="functions.js"></script>
	</head>
	<body>	
		<?
			require_once('include/func.php');
			session_start(); 
			error_reporting( error_reporting() & ~E_NOTICE ); //wyłącz ostrzeżenia, że nieznana jest 'a', itd. //todo: wyłączyć reportowanie w innych php'ach docelowo też
		?>
		<div id="mainDiv">
			<div id ="menu">
				<div id="header">
					<div id="serverStatus">
						Serwer: 
						<span id="serverCSSCircleStatus" class="dot"></span> 
						<span id="serverStatusInfo">ŁĄCZENIE...</span>
					</div>
					<div id="mainPage">|&nbsp;&nbsp;<a href="#" onClick="return headerText('mainPage');">Strona główna</a>&nbsp;&nbsp;|</div>
					<div id="info"><a href="#" onClick="return headerText('info');">Informacje</a>&nbsp;&nbsp;|</div>
					<div id="contact"><a href="#" onClick="return headerText('contact');">Kontakt</a>&nbsp;&nbsp;|</div>
					<div id="reportBug"><a href="#" onClick="return headerText('report');">Zgłoś błąd</a>&nbsp;&nbsp;|</div>
					<div id="loggingSection"></div>
					<div id="playAsGuest"><button id="playAsGuestBtn" onClick="clickedBtn('sitOnNone')" hidden="hidden" disabled>Graj jako gość</button></div>
					<div id="user">&nbsp;</div>
				</div>
				<div id="headerText"></div>
			</div>
			<div id="content" align="center">
				<div id="game">						
					<div id="video" class="parent">
						<iframe id="ytplayer" type="text/html" width="854" height="480" src="<?= $liveStreamVideoLink ?>"></iframe>
						<div id="perspective">
							<div id="chessboard"><? require_once('chessboard.php'); ?></div>
						</div>
						<div id="shutter"></div>
					</div>
					<div id="additionalInfo"></div>
					<div id="table" align="center">
						<div id="playersBoxes">
							<div id="whitePlayerBox">
								<div id="whitePlayerSign">&#9817;</div>
								<div id='whitePlayerMiniBox'>
									<div id="whitePlayerBtns">
										<div id="whiteTime">Gracz Biały:&nbsp;&nbsp;-:--&nbsp;&nbsp;|&nbsp;&nbsp;30:00</div>
										<button id="whitePlayer" onClick="clickedBtn('sitOnWhite')" disabled>-</button> 
										<button id="standUpWhite" onClick="clickedBtn('standUp')" hidden="hidden" disabled>Wstań</button> 
									</div>
								</div>
							</div> 
							<div id="blackPlayerBox">
								<div id="blackPlayerSign">&#9823;</div>
								<div id='blackPlayerMiniBox'>
									<div id="blackPlayerBtns">
										<div id="blackTime">Gracz Czarny:&nbsp;&nbsp;-:--&nbsp;&nbsp;|&nbsp;&nbsp;30:00</div>
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
						&nbsp;&nbsp;
						<span class="tooltip"><button id="queuePlayer" onClick="clickedBtn('queueMe')" disabled>kolejkuj</button>
							<span class="tooltiptext">Kolejkować mogą się tylko zalogowani gracze, podczas gdy stół gry nie jest pełen.</span>
							</span>
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
		
		<script> initWebSocket(); </script>
	</body>
</html>																									