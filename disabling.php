<?
	if(!isset($_SESSION)) session_start();
	
	function isChairEmpty($playerType)
	{
		$chair = false;
		if ($playerType == WHITE) 
		{
			if ($_SESSION['white'] == WHITE) $chair = true;
			else $chair = false;
		}
		else if ($playerType == BLACK) 
		{
			if ($_SESSION['black'] == BLACK) $chair = true;
			else $chair = false;
		}
		/*else 
			{
			$consoleMsg = 'ERROR: isChairEmpty(): unknown playerType: '.$playerType;
			debugToConsole($consoleMsg);
		}*/
		return $chair;
	}
	
	function isLoggedPlayerOnChair($playerType)
	{
		$loggedPlayerOnChair = false;
		if ($playerType == WHITE) 
		{
			if ($_SESSION['white'] == $_SESSION['login']) $loggedPlayerOnChair = true;
			else $loggedPlayerOnChair = false;
		}
		else if ($playerType == BLACK) 
		{
			if ($_SESSION['black'] == $_SESSION['login']) $loggedPlayerOnChair = true;
			else $loggedPlayerOnChair = false;
		}
		/*else
			{
			$consoleMsg = 'ERROR: isPlayerOnChair(): unknown playerType: '.$playerType;
			debugToConsole($consoleMsg);
		}*/
		return $loggedPlayerOnChair;
	}
	
	function isLoggedPlayerOnAnyChair()
	{
		if (isLoggedPlayerOnChair(WHITE) || isLoggedPlayerOnChair(BLACK)) return true;
		else return false;
	}
	
	function are2PlayersOnChairs()
	{
		if (!isChairEmpty(WHITE) && !isChairEmpty(BLACK)) return true;
		else return false;
	}
	
	function isGameInProgress()
	{
		if ($_SESSION['turn'] == WHITE_TURN || $_SESSION['turn'] == BLACK_TURN) return true;
		else return false;
	}
	
	function isClientInQueue()
	{
		$queueTempArr = explode(",", $_SESSION['queue']);
		if (in_array($_SESSION['login'], $queueTempArr)) 
			return true; 
		else return false;
	}
	
	function enabling($state)
	{
		//Auto disabling all in cases: notLoggedIn, noTurn, clicked: white/black chair, start, sendMove, standup white/black, giveUp, logOut
		
		$consoleEnabling = '-1'; //return[5], enabling[0]
		$textboxEnabling = '-1'; //return[6], enabling[1]
		$whitePlayerBtn = false; //return[7], enabling[2]
		$blackPlayerBtn = false; //return[8], enabling[3]
		$whiteStandUp = false; //return[9], enabling[4]
		$blackStandUp = false; //return[10], enabling[5]
		$startBtn = false; //return[11], enabling[6]
		$giveUpBtn = false; //return[12], enabling[7]
		$pieceFromInput = false; //return[13], enabling[8]
		$pieceToInput = false; //return[14], enabling[9]
		$sendBtn = false; //return[15], enabling[10]
		$queuePlayer = false; //return[16], enabling[11]
		$leaveQueue = false; //return[17], enabling[12]
		
		if (!empty($_SESSION['id']))
		{
			$consoleEnabling = 'disabling val = '.$state;
			
			if (are2PlayersOnChairs() && !isLoggedPlayerOnAnyChair() && !isClientInQueue()) $queuePlayer = true;
			else if (isClientInQueue()) $leaveQueue = true;
			
			switch ($state)
			{
				case 'loggedIn':
				if (isChairEmpty(WHITE) && !isLoggedPlayerOnChair(BLACK)) $whitePlayerBtn = true;
				if (isChairEmpty(BLACK) && !isLoggedPlayerOnChair(WHITE)) $blackPlayerBtn = true;
				if (isLoggedPlayerOnChair(WHITE) && $_SESSION['turn'] == NO_TURN) $whiteStandUp = true;
				if (isLoggedPlayerOnChair(BLACK) && $_SESSION['turn'] == NO_TURN) $blackStandUp = true;
				break;
				
				case 'whiteEmpty':
				if (!isLoggedPlayerOnChair(BLACK) && isChairEmpty(WHITE)) $whitePlayerBtn = true;
				if (isChairEmpty(BLACK)) $blackPlayerBtn = true;
				if (isLoggedPlayerOnChair(BLACK)) $blackStandUp = true;
				break;
				
				case 'blackEmpty':
				if (!isLoggedPlayerOnChair(WHITE) && isChairEmpty(BLACK)) $blackPlayerBtn = true;
				if (isChairEmpty(WHITE)) $whitePlayerBtn = true;
				if (isLoggedPlayerOnChair(WHITE)) $whiteStandUp = true;
				break;
				
				case 'newWhite':
				if (!isLoggedPlayerOnChair(WHITE) && isChairEmpty(BLACK)) $blackPlayerBtn = true;
				if (isLoggedPlayerOnChair(WHITE) && $_SESSION['turn'] == NO_TURN) $whiteStandUp = true;
				if (isLoggedPlayerOnChair(BLACK) && $_SESSION['turn'] == NO_TURN) $blackStandUp = true;
				if (are2PlayersOnChairs())
				{
					if (!isGameInProgress() && isLoggedPlayerOnAnyChair()) 
					{
						$startBtn = true;
						$textboxEnabling = "Wciśnij START, aby rozpocząć grę.";
					}
					if (isGameInProgress() && isLoggedPlayerOnAnyChair()) $giveUpBtn = true;
					if (isGameInProgress() && (($_SESSION['turn'] == WHITE_TURN && isLoggedPlayerOnChair(WHITE)) ||
					($_SESSION['turn'] == BLACK_TURN && isLoggedPlayerOnChair(BLACK))))
					{
						$pieceFromInput = true;
						$pieceToInput = true;
						$sendBtn = true;
					}
				}
				break;
				
				case 'newBlack':
				if (!isLoggedPlayerOnChair(BLACK) && isChairEmpty(WHITE)) $whitePlayerBtn = true;
				if (isLoggedPlayerOnChair(WHITE) && $_SESSION['turn'] == NO_TURN) $whiteStandUp = true;
				if (isLoggedPlayerOnChair(BLACK) && $_SESSION['turn'] == NO_TURN) $blackStandUp = true;
				if (are2PlayersOnChairs())
				{
					if (!isGameInProgress() && isLoggedPlayerOnAnyChair()) 
					{
						$startBtn = true;
						$textboxEnabling = "Wciśnij START, aby rozpocząć grę.";
					}
					if (isGameInProgress() && isLoggedPlayerOnAnyChair()) $giveUpBtn = true;
					if (isGameInProgress() && (($_SESSION['turn'] == WHITE_TURN && isLoggedPlayerOnChair(WHITE)) ||
					($_SESSION['turn'] == BLACK_TURN && isLoggedPlayerOnChair(BLACK))))
					{
						$pieceFromInput = true;
						$pieceToInput = true;
						$sendBtn = true;
					}
				}
				break;
				
				case 'newGame':
				if (isLoggedPlayerOnAnyChair()) $giveUpBtn = true;
				if (isLoggedPlayerOnChair(WHITE)) 
				{
					$pieceFromInput = true;
					$pieceToInput = true;
					$sendBtn = true;
				}
				break;
				
				case 'badMove':
				case 'gameInProgress':
				if (isLoggedPlayerOnAnyChair()) $giveUpBtn = true;
				if (($_SESSION['turn'] == WHITE_TURN && isLoggedPlayerOnChair(WHITE)) ||
				($_SESSION['turn'] == BLACK_TURN && isLoggedPlayerOnChair(BLACK)))
				{
					$pieceFromInput = true;
					$pieceToInput = true;
					$sendBtn = true;
				}
				break;
				
				case 'endOfGame':
				if (isChairEmpty(WHITE) && !isLoggedPlayerOnChair(BLACK)) $whitePlayerBtn = true;
				if (isChairEmpty(BLACK) && !isLoggedPlayerOnChair(WHITE)) $blackPlayerBtn = true;
				if (isLoggedPlayerOnChair(WHITE)) $whiteStandUp = true;
				if (isLoggedPlayerOnChair(BLACK)) $blackStandUp = true;
				if (are2PlayersOnChairs() && isLoggedPlayerOnAnyChair()) $startBtn = true;
				break;
				
				case 'resetComplited':
				if (isChairEmpty(WHITE) && !isLoggedPlayerOnChair(BLACK)) $whitePlayerBtn = true;
				if (isChairEmpty(BLACK) && !isLoggedPlayerOnChair(WHITE)) $blackPlayerBtn = true;
				if (isLoggedPlayerOnChair(WHITE)) $whiteStandUp = true;
				if (isLoggedPlayerOnChair(BLACK)) $blackStandUp = true;
				$whatNow;
				if (are2PlayersOnChairs()) 
				{
					$startBtn = true;
					$whatNow = "\nWciśnij START, aby rozpocząć grę.";
				}
				else $whatNow = "Oczekiwanie na graczy...";
				$textboxEnabling = "Plansza zrestartowana. ".$whatNow;
				break;
				
				//w zasadzie te warunki to wypisanie po prostu wszystkich możliwości, bo ten przypadek
				//powinien działać tylko na 2 przyciski nie ruszając w ogóle reszty. powinno to być
				//raczej w oddzielnym pliku php
				case 'queueEmpty':
				case 'queueNotEmpty':
				if (isChairEmpty(WHITE) && !isLoggedPlayerOnChair(BLACK)) $whitePlayerBtn = true;
				if (isChairEmpty(BLACK) && !isLoggedPlayerOnChair(WHITE)) $blackPlayerBtn = true;
				if (isLoggedPlayerOnChair(WHITE)) $whiteStandUp = true;
				if (isLoggedPlayerOnChair(BLACK)) $blackStandUp = true;
				if (isGameInProgress() && isLoggedPlayerOnAnyChair()) $giveUpBtn = true;
				if (isGameInProgress() && (($_SESSION['turn'] == WHITE_TURN && isLoggedPlayerOnChair(WHITE)) ||
					($_SESSION['turn'] == BLACK_TURN && isLoggedPlayerOnChair(BLACK))))
					{
						$pieceFromInput = true;
						$pieceToInput = true;
						$sendBtn = true;
					}
				break;
				
				case 'noTurn':
				case 'clickedBtn':
				case 'promote':
				default: break;
			}
		}
		else $consoleEnabling ='ERROR: Empty session ID';
		
		return array( $consoleEnabling, $textboxEnabling, !$whitePlayerBtn, !$blackPlayerBtn, !$whiteStandUp, !$blackStandUp, !$startBtn, !$giveUpBtn, !$pieceFromInput, !$pieceToInput, !$sendBtn, !$queuePlayer, !$leaveQueue);
	}
?>							