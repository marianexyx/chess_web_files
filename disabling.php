<script>
	function isChairEmpty(playerType)
	{
		var chair = false;
		if (playerType == 'white') 
		{
			if (document.getElementById("whitePlayer").value == "White") chair = true;
			else chair = false;
		}
		else if (playerType == 'black') 
		{
			if (document.getElementById("blackPlayer").value == "Black") chair = true;
			else chair = false;
		}
		else console.log('ERROR: isChairEmpty(): unknown playerType: ', playerType);
		
		return chair;
	}
	
	function isLoggedPlayerOnChair(playerType)
	{
		var loggedPlayerOnChair = false;
		if (playerType == 'white') 
		{
			if (document.getElementById("whitePlayer").value == js_loginUzytkownika) loggedPlayerOnChair = true;
			else loggedPlayerOnChair = false;
		}
		else if (playerType == 'black') 
		{
			if (document.getElementById("blackPlayer").value == js_loginUzytkownika) loggedPlayerOnChair = true;
			else loggedPlayerOnChair = false;
		}
		else console.log('ERROR: isPlayerOnChair(): unknown playerType: ', playerType);
		
		return loggedPlayerOnChair;
	}
	
	function isLoggedPlayerOnAnyChair()
	{
		if (isLoggedPlayerOnChair('white') || isLoggedPlayerOnChair('black')) return true
		else return false;
	}
	
	function are2PlayersOnChairs()
	{
		if (!isChairEmpty('white') && !isChairEmpty('black')) return true;
		else return false;
	}
	
	function isGameInProgress(turn)
	{
		if (turn == "wt" || turn == "bt") return true;
		else return false;
	}
	
	//console.log('ZAKLADKA');
	
	function enabling(state, whoseTrun = 'nt')
	{
		//auto disable all. cases: notLoggedIn, noTurn, clicked: white/black chair, start, sendMove
		var whitePlayerBtn = false;
		var blackPlayerBtn = false;
		var whiteStandUp = false;
		var blackStandUp = false;
		var startBtn = false;
		var giveUpBtn = false;
		var pieceFromInput = false;
		var pieceToInput = false;
		var sendBtn = false;
		
		var logged = <? if (empty($_SESSION['id'])) { echo 'false;';} else {echo 'true;';}; ?>
		console.log('logged = ', logged);
		
		if (logged)
		{
			switch (state)
			{
				case 'loggedIn':
				if (isChairEmpty('white') && !isLoggedPlayerOnChair('black')) whitePlayerBtn = true;
				if (isChairEmpty('black') && !isLoggedPlayerOnChair('white')) blackPlayerBtn = true;
				if (isLoggedPlayerOnChair('white')) whiteStandUp = true;
				if (isLoggedPlayerOnChair('black')) blackStandUp = true;
				break;
				
				case 'whiteEmpty':
				if (!isLoggedPlayerOnChair('black')) whitePlayerBtn = true;
				if (isChairEmpty('black')) blackPlayerBtn = true;
				if (isLoggedPlayerOnChair('black')) blackStandUp = true;
				break;
				
				case 'blackEmpty':
				if (!isLoggedPlayerOnChair('white')) blackPlayerBtn = true;
				if (isChairEmpty('white')) whitePlayerBtn = true;
				if (isLoggedPlayerOnChair('white')) whiteStandUp = true;
				break;
				
				case 'newWhite':
				if (!isLoggedPlayerOnChair('white')) blackPlayerBtn = true;
				if (isLoggedPlayerOnChair('white')) whiteStandUp = true;
				if (isLoggedPlayerOnChair('black')) blackStandUp = true;
				if (are2PlayersOnChairs())
				{
					if (!isGameInProgress() && isLoggedPlayerOnAnyChair()) startBtn = true;
					if (isGameInProgress() && isLoggedPlayerOnAnyChair()) giveUpBtn = true;
					if (isGameInProgress() && ((whoseTrun == 'wt' && isLoggedPlayerOnChair('white')) ||
					(whoseTrun == 'bt' && isLoggedPlayerOnChair('black'))))
					{
						pieceFromInput = true;
						pieceToInput = true;
						sendBtn = true;
					}
				}
				break;
				
				case 'newBlack':
				if (!isLoggedPlayerOnChair('black')) whitePlayerBtn = true;
				if (isLoggedPlayerOnChair('white')) whiteStandUp = true;
				if (isLoggedPlayerOnChair('black')) blackStandUp = true;
				if (are2PlayersOnChairs())
				{
					if (!isGameInProgress() && isLoggedPlayerOnAnyChair()) startBtn = true;
					if (isGameInProgress() && isLoggedPlayerOnAnyChair()) giveUpBtn = true;
					if (isGameInProgress() && ((whoseTrun == 'wt' && isLoggedPlayerOnChair('white')) ||
					(whoseTrun == 'bt' && isLoggedPlayerOnChair('black'))))
					{
						pieceFromInput = true;
						pieceToInput = true;
						sendBtn = true;
					}
				}
				break;
				
				case 'newGame':
				if (isLoggedPlayerOnChair('white')) whiteStandUp = true;
				if (isLoggedPlayerOnChair('black')) blackStandUp = true;
				if (isLoggedPlayerOnAnyChair()) giveUpBtn = true;
				if (isLoggedPlayerOnChair('white')) 
				{
					pieceFromInput = true;
					pieceToInput = true;
					sendBtn = true;
				}
				break;
				
				case 'badMove':
				case 'gameInProgress':
				if (isLoggedPlayerOnChair('white')) whiteStandUp = true;
				if (isLoggedPlayerOnChair('black')) blackStandUp = true;
				if (isLoggedPlayerOnAnyChair()) giveUpBtn = true;
				if ((whoseTrun == 'wt' && isLoggedPlayerOnChair('white')) ||
				(whoseTrun == 'bt' && isLoggedPlayerOnChair('black')))
				{
					pieceFromInput = true;
					pieceToInput = true;
					sendBtn = true;
				}
				break;
				
				case 'endOfGame':
				if (isChairEmpty('white') && !isLoggedPlayerOnChair('black')) whitePlayerBtn = true;
				if (isChairEmpty('black') && !isLoggedPlayerOnChair('white')) blackPlayerBtn = true;
				if (isLoggedPlayerOnChair('white')) whiteStandUp = true;
				if (isLoggedPlayerOnChair('black')) blackStandUp = true;
				if (are2PlayersOnChairs() && isLoggedPlayerOnAnyChair()) startBtn = true;
				break;
				
				default: break;
				}
			}
		
		document.getElementById("whitePlayer").disabled = !whitePlayerBtn; 
		document.getElementById("blackPlayer").disabled = !blackPlayerBtn; 
		document.getElementById("standUpWhite").disabled = !whiteStandUp; 
		document.getElementById("standUpBlack").disabled = !blackStandUp; 
		document.getElementById("startGame").disabled = !startBtn; 
		document.getElementById("openGiveUpDialogButton").disabled = !giveUpBtn; 
		document.getElementById("pieceFrom").disabled = !pieceFromInput; 
		document.getElementById("pieceTo").disabled = !pieceToInput; 
		document.getElementById("movePieceButton").disabled = !sendBtn; 
	}
</script>								