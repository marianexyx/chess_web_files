function debugToGameTextArea(message) 
{
	debugTextArea.value += message + "\n";
	debugTextArea.scrollTop = debugTextArea.scrollHeight;
}

function gameInProgress(move, turn)
{
	enabling('gameInProgress', turn);
	
	if (turn == "wt")
	{
		wiadomoscNaTextArea = "Czarny wykonał ruch: " + move + ". Ruch wykonują Białe.";
		debugToGameTextArea(wiadomoscNaTextArea);
	}
	else if (turn == "bt")
	{
		wiadomoscNaTextArea = "Biały wykonał ruch: " + move + ". Ruch wykonują Czarne.";
		debugToGameTextArea(wiadomoscNaTextArea);
	}
	else console.log('ERROR. Unknown turn value = ' + turn);
}

$("#dialog").dialog(
{
	autoOpen: false, 
	dialogClass: "no-close",
	buttons: 
	{
		'hetman': function() 
		{
			websocket.send("promoteTo: q"); //queen
			console.log('sent to core: promoteTo: q');
			$(this).dialog("close");
		}, 
		'goniec': function() 
		{
			websocket.send("promoteTo: b"); //bishop
			console.log('sent to core: promoteTo: b');
			$(this).dialog("close");
		}, 
		'skoczek': function() 
		{
			websocket.send("promoteTo: k"); //knight
			console.log('sent to core: promoteTo: k');
			$(this).dialog("close");
		}, 
		'wieża': function() 
		{
			websocket.send("promoteTo: r"); //rook
			console.log('sent to core: promoteTo: r');
			$(this).dialog("close");
		}
	},
	title: "Promocja",
	position: 
	{
		my: "center",
		at: "center",
		of: window
	}
});

function openDialogPromote()
{
	console.log("show promotion buttons window");
	$("#dialog").dialog('open');
}

function promoteToWhat()
{
	//prośbę o promocję dostanie tylko ten gracz który wysyłał ruch- TODO: sprawdzić
	if (document.getElementById("whitePlayer").value == js_loginUzytkownika) 
	{
		openDialogPromote();
	}
}

function endOfGame(checkmate, endType)
{
	if (endType == "whiteWon")
	{
		console.log("endOfGame(): whiteWon");
		debugToGameTextArea("Koniec gry: Białe wygrały wykonując ruch: " + checkmate);
	}
	else if( endType == "blackWon")
	{
		console.log("endOfGame(): blackWon");
		debugToGameTextArea("Koniec gry: Czarne wygrały wykonując ruch: " + checkmate);
	}
	else if( endType == "draw")
	{
		console.log("endOfGame(): draw");
		debugToGameTextArea("Koniec gry: Remis");
		// TODO: co dalej?
		// na kurniku obu graczy deklalure remis bodajże
	}
	else console.log("endOfGame(): ERROR: unknown parameter");
}

$('#giveUpDialog').dialog({
    autoOpen: false,
	buttons: 
	{
		'tak': function() 
		{
			enabling('clickedBtn');
			
			if (whitePlayerName == js_loginUzytkownika)
			{
				change("whitePlayer", "White")
			}
			else if (blackPlayerName == js_loginUzytkownika)
			{
				change("blackPlayer", "Black") 
			}
			
			resetBoard();

			$(this).dialog("close");
		}, 
		'nie': function() 
		{
			$(this).dialog("close");
		}
	},
	title: 'Opuszczanie stołu',
	position: 
	{
		my: "center",
		at: "center",
		of: window
	}
});

$('#openGiveUpDialogButton').click(function() 
{
    $('#giveUpDialog').dialog('open');
    return false;
});

function deleteask()
{
	if (confirm('Czy na pewno chcesz się wylogować?')) 
	{
		if (document.getElementById("whitePlayer").value == js_loginUzytkownika) change('whitePlayer', 'White');
		else if (document.getElementById("blackPlayer").value == js_loginUzytkownika) change('blackPlayer', 'Black');
		
		enabling('clikedBtn');
		
		console.log('logged out');

		return true;
	}
	else return false;   
  }
  
  
