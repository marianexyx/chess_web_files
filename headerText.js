var reCaptchaResponse;
function sendAjaxHeaderMsg(headerType)
{
	var ajaxArray;
	var ajaxUrl;
	if (headerType == "report")
	{
		ajaxArray = { reportFrom: $("#reportFrom").val(), reportUserMail: $("#reportUserMail").val(), reportMessage: $("#reportMessage").val() };
		$("#reportFrom").val('');
		$("#reportUserMail").val('');
		$("#reportMessage").val('');
		ajaxUrl = "report.php";
	}
	else if (headerType == "register")
	{
		ajaxArray = { registerLogin: $("#registerLogin").val(), registerPass: $("#registerPass").val(), registerPass2: $("#registerPass2").val(), registerEmail: $("#registerEmail").val(), captchaResponse: reCaptchaResponse };
		$("#registerLogin").val('');
		$("#registerPass").val('');
		$("#registerPass2").val('');
		$("#registerEmail").val('');
		ajaxUrl = "register.php";
	}
	else if (headerType == "login")
	{
		ajaxArray = { loginLogin: $("#loginLogin").val(), loginPassword: $("#loginPassword").val() };
		$("#loginLogin").val('');
		$("#loginPassword").val('');
		ajaxUrl = "login.php";
	}
	else return;
	
	$.ajax(
	{
		url: ajaxUrl,
		type: "POST",
		data: { arrayMsg: ajaxArray },
		success: function (response) { manageAjaxHeaderResponse(headerType, response); },
		error: function(xhr, status, error) { console.log('error: ' + error ); }
	});
}

function tryToLogin()
{
	//todo: check vars length here
	if ($("#loginLogin").val() && $("#loginPassword").val())
	{
		var regExp = /^[a-zA-Z0-9]+$/i;
		if (regExp.test($("#loginLogin").val()))
		{
			websocket.send("login " + $("#loginLogin").val() + "&" + $("#loginPassword").val());
			$("#loginPassword").val(''); //this will block multiple login button clicking
		}
		else $("#headerConsole").html("Konto nie istnieje. Wypełnij pola ponownie."); 
	}
	else $("#headerConsole").html("Wypełnij wszystkie pola."); 
}

function manageAjaxHeaderResponse(headerType, reportResponse)
{
	var success = reportResponse.substr(0, 2); 
	var msg = reportResponse.substr(3);
	
	if (success == "er")
	{
		$("#headerConsole").html(msg); 
		return;
	}
	else if (success != "ok")
	{
		$("#headerText").css('padding', '0px');
		$("#headerText").html(''); 
		return;
	}
	
	if (headerType == "report" || headerType == "register")
	{
		$("#headerText").html('<div id="headerConsole" style="color:black; text-align:center; clear:both;">' + msg + '</div>'); 
		setTimeout(function() 
		{ 
			$("#headerText").html('');
			$("#headerText").css('padding', '0px');
		}, 5000)
	}
	else if (headerType == "login")
	{
		$("#loggingSection").html('<a href="#" onClick="return websocket.send(\'logout\');">Wyloguj się</a>&nbsp;&nbsp;|');
		headerText("mainPage");
		if (websocket)
			websocket.send(msg);
	}
}

function headerText(reference)
{	
	if (reference == "mainPage" || reference == "wrongData")
	{
		$("#headerText").css('padding', '0px');
		$("#headerText").html("");
		
		if (reference == "wrongData")
			stopWebSocket();
	}
	else if (reference == "doubleLogin")
	{
		$("#headerText").css('padding', '20px');
		$("#headerText").html('<div id="headerConsole" style="color:red; text-align:center; clear:both;">Wylogowywanie: podwójny login</div>'); 
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
		Jeżeli gracz zajmuje miejsce przy stole i połączenie zostanie zerwane, nastąpi usunięcie gracza z krzesła. Gra zostaje przerwana jeżeli była w toku i następuje resetowanie planszy. Zerwanie połączenia następuje poprzez: odświeżenia strony, zamknięcia zakładki z grą, utratę połączenia z internetem i podwójne logowanie.<br/><br/>\
		\
		Ze względu na trwające ciągłe testy i ulepszenia gra dostępna jest okresowo.');
	}
	else if (reference == "contact")
	{
		$("#headerText").css('padding', '20px');
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
			<div id="headerConsole" style="color:red; text-align:center; clear:both;"></div>\
			<br/>\
			<div id="email" class="divTable">\
				<div class="divTableBody">\
					<div class="divTableRow">\
						<div class="divTableCell">&nbsp;</div>\
						<div class="divTableCell" style="font-size: 150%">ZGŁOŚ BŁĄD LUB AWARIĘ</div>\
					</div>\
					<div class="divTableRow">\
						<div class="divTableCell"><b>Od:</b></div>\
						<div clas ="divTableCell"><input type="text" id="reportFrom"/></div>\
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
						<div class="divTableCell"><button onClick="sendAjaxHeaderMsg(\'report\')">Wyślij</button></div>\
					</div>\
				</div>\
			</div>\
		');
	}
	else if (reference == "register")
	{
		$("#headerText").css('padding', '20px');
		$("#headerText").html('\
			<div id="headerConsole" style="color:red; text-align:center; clear:both;"></div>\
			<br/>\
			<div id="register" class="divTable">\
				<div class="divTableBody">\
					<div class="divTableRow">\
						<div class="divTableCell">&nbsp;</div>\
						<div class="divTableCell" style="font-size: 150%">REJESTRACJA</div>\
						<div class="divTableCell">&nbsp;</div>\
					</div>\
					<div class="divTableRow">\
						<div class="divTableCell"><b>Login użytkownika:</b></div>\
						<div class="divTableCell"><input type="text" id="registerLogin"/>&nbsp;&nbsp;&nbsp;(od 3 do 25 znaków)</div>\
					</div>\
					<div class="divTableRow">\
						<div class="divTableCell"><b>Hasło:</b></div>\
						<div class="divTableCell"><input type="password" id="registerPass"/>&nbsp;&nbsp;&nbsp;(od 1 do 20 znaków)</div>\
					</div>\
					<div class="divTableRow">\
						<div class="divTableCell"><b>Powtórz hasło:</b></div>\
						<div class="divTableCell"><input type="password" id="registerPass2"/></div>\
					</div>\
					<div class="divTableRow">\
						<div class="divTableCell"><b>E-mail:</b></div>\
						<div class="divTableCell"><input type="text" id="registerEmail"/>&nbsp;&nbsp;&nbsp;(od 8 do 50 znaków)</div>\
					</div>\
					<div class="divTableRow">\
						<div class="divTableCell">&nbsp;</div>\
						<div class="divTableCell"><div id="captcha_container" class="g-recaptcha"></div></div>\
					</div>\
					<div class="divTableRow">\
						<div class="divTableCell">&nbsp;</div>\
						<div class="divTableCell"><button onClick="sendAjaxHeaderMsg(\'register\')">Zarejestruj się</button></div>\
					</div>\
				</div>\
			</div>\
		');
		grecaptcha.render('captcha_container', 
		{ 
			'sitekey': '6Lf9PygUAAAAAEPWjrGrWkXqkKbK6_uxtW64eKDj', 
			'callback':  function(response) 
			{ 
				reCaptchaResponse = response; 
			}
		});
	}
	else if (reference == "login")
	{
		$("#headerText").css('padding', '20px');
		$("#headerText").html('\
			<div id="headerConsole" style="color:red; text-align:center; clear:both;"></div>\
			<br/>\
			<div id="login" class="divTable">\
				<div class="divTableBody">\
					<div class="divTableRow">\
						<div class="divTableCell">&nbsp;</div>\
						<div class="divTableCell" style="font-size: 150%">LOGOWANIE</div>\
					</div>\
					<div class="divTableRow">\
						<div class="divTableCell"><b>Login użytkownika:</b></div>\
						<div class="divTableCell"><input type ="text" id="loginLogin"/></div>\
					</div>\
					<div class="divTableRow">\
						<div class="divTableCell"><b>Hasło:</b></div>\
						<div class="divTableCell"><input type ="password" id="loginPassword"/></div>\
					</div>\
					<div class="divTableRow">\
						<div class="divTableCell">&nbsp;</div>\
						<div class="divTableCell"><button onClick="tryToLogin()">Zaloguj się</button></div>\
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