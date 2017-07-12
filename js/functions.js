function debugToGameTextArea(message) 
{
	debugTextArea.value += message + "\n";
	debugTextArea.scrollTop = debugTextArea.scrollHeight;
}

$("#dialog").dialog(
{
	autoOpen: false, 
	dialogClass: "no-close",
	buttons: 
	{
		'&#9819;': function() //hex: &#x265B;	js: \u265B	css: \00265B
		{
			websocket.send("promoteTo: q"); //queen
			console.log('clicked: promoteTo: q');
			$(this).dialog("close");
		}, 
		'&#9821;': function() 
		{
			websocket.send("promoteTo: b"); //bishop
			console.log('clicked: promoteTo: b');
			$(this).dialog("close");
		}, 
		'&#9822;': function() 
		{
			websocket.send("promoteTo: k"); //knight
			console.log('clicked: promoteTo: k');
			$(this).dialog("close");
		}, 
		'&#9820;': function() 
		{
			websocket.send("promoteTo: r"); //rook
			console.log('clicked: promoteTo: r');
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

function promoteToWhat()
{
	console.log("show promotion buttons window");
	$("#dialog").dialog('open');
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
		// TODO: co dalej?  na kurniku obu graczy deklalure remis bodajże
	}
	else console.log("endOfGame(): ERROR: unknown parameter");
}

$('#giveUpDialog').dialog({
    autoOpen: false,
	buttons: 
	{
		'tak': function() 
		{
			var request = $.ajax(
			{
				url: "giveup.php",
				type: "GET",			
				dataType: "html"
			});

			request.done(function() 
			{
				$(this).dialog("close");		
			});
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
	if (confirm("Czy na pewno chcesz się wylogować?")) 
	{
		var request = $.ajax(
		{
			url: "logout.php",
			type: "GET",			
			dataType: "html"
		});

		request.done(function() 
		{
			return true;		
		});
		
		request.fail(function() 
		{
			return false;		
		});
	}
	else return false;   
}
