function manageReportRespnse(reportResponse)
{
	var success = reportResponse.substr(0, 2); 
	var msg = reportResponse.substr(3);
	
	if (success == "ok")
	{
		$("#headerText").html('<div id="emailConsole" style="color:black; text-align:center; clear:both;">' + msg + '</div>'); 
		
		setTimeout(function() 
		{ 
			$("#headerText").html('');
			$("#headerText").css('padding', '0px');
		}, 5000)
	}
	else if (success == "er")
	{
		$("#emailConsole").html(msg); 
	}
	else 
	{
		$("#headerText").css('padding', '0px');
		$("#headerText").html(''); 
	}
}

function sendReport()
{
	var reportArray = { reportFrom: $("#reportFrom").val(), reportUserMail: $("#reportUserMail").val(), reportMessage: $("#reportMessage").val() };
	
	$("#reportFrom").val('');
	$("#reportUserMail").val('');
	$("#reportMessage").val('');

	$.ajax(
	{
		url: "report.php",
		type: "POST",
		data: { arrayMsg: reportArray },
		success: function (response) { manageReportRespnse(response); },
		error: function(xhr, status, error) { console.log('error: ' + error ); }
	});
}

function headerText(reference)
{	
	if (reference == "mainPage")
	{
		$("#headerText").css('padding', '0px');
		$("#headerText").html("");
	}
	else if (reference == "info")
	{
		$("#headerText").css('padding', '20px');
		$("#headerText").html('Gra rozgrywana jest przy użyciu prawdziwej szachownicy. Dwóch graczy kolejno wykonuje ruchy bierkami po szachownicy przy użyciu ramienia robota w czasie rzeczywistym. Przebieg gry jest transmitowany na żywo.<br/><br/>\
		\
		Ruchy realizowane są poprzez klikanie myszką przez aktywnego gracza na polach szachownicy transmitowanego obrazu. <br/><br/>\
		\
		Można grać jako gość- bez rejestrowania i logowania się. Jeżeli stół jest zajęty przez innych graczy, to można ustawić się w kolejce do następnej gry. Kolejkować mogą się zarejestrowani i zalogowani gracze.<br/><br/>\
		\
		Jeżeli gracz zajmuje miejsce przy stole i połączenie zostanie zerwane, nastąpi usunięcie gracza z krzesła. Gra zostaje przerwana jeżeli była w toku i następuje resetowanie planszy. Zerwanie połączenia następuje poprzez: odświerzenia strony, zamknięcia zakładki z grą, utratę połączenia z internetem i podwójne logowanie.<br/><br/>\
		\
		Ze względu na trwające ciągłe testy i ulepszenia gra dostępna jest okresowo.');
	}
	else if (reference == "contact")
	{
		$("#headerText").css('padding', '20px');
		/*$("#headerText").html('Zapraszam do kontaktu za pośrednictwem mojego maila:<br/>\
		<b>mariusz.pak.89@gmail.com</b>');*/
		$("#headerText").html('\
							<div style="text-align:center; clear:both;">\
								Zapraszam do kontaktu za pośrednictwem mojego maila:<br/>\
								<a href="mailto:mariusz.pak.89@gmail.com"><b>mariusz.pak.89@gmail.com</b></a>\
							</div>\
							'); 
	}
	else if (reference == "report")
	{
		$("#headerText").css('padding', '20px');
		$("#headerText").html('\
		<div id="emailConsole" style="color:red; text-align:center; clear:both;"></div>\
		<br/>\
		<div id="email" class="divTable">\
			<div class="divTableBody">\
				<div class="divTableRow">\
					<div class="divTableCell">&nbsp;</div>\
					<div class="divTableCell" style="font-size: 150%">ZGŁOŚ BŁĄD LUB AWARIĘ</div>\
				</div>\
				<div class="divTableRow">\
					<div class="divTableCell"><b>Od:</b></div>\
					<div class="divTableCell"><input type="text" id="reportFrom"/></div>\
				</div>\
				<div class="divTableRow">\
					<div class="divTableCell"><b>Twój email (opcjonalnie):</b></div>\
					<div class="divTableCell"><input type="text" id="reportUserMail"/></div>\
				</div>\
				<div class="divTableRow">\
					<div class="divTableCell"><b>Wiadomość:</b></div>\
					<div class="divTableCell"><textarea rows="5" id="reportMessage" cols="30"></textarea></div>\
				</div>\
				<div class="divTableRow">\
					<div class="divTableCell">&nbsp;</div>\
					<div class="divTableCell"><button id="sendReport" onClick="sendReport()">Wyślij</button></div>\
				</div>\
			</div>\
		</div>\
		');
	}
	else
	{
		$("#headerText").css('padding', '0px');
		$("#headerText").html("");
	}
}	