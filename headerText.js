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
		$("#headerText").html('Gra rozgrywana jest przy użyciu prawdziwej szachownicy. Dwóch graczy kolejno wykonuje ruchy bierkami po szachownicy przy użyciu ramienia robota w czasie rzeczywistym. Przebieg gry jest transmitowany na żywo. <br/><br/>Ruchy realizowane są poprzez klikanie myszką przez aktywnego gracza na polach szachownicy transmitowanego obrazu. <br/><br/>Można grać jako gość- bez rejestrowania i logowania się. Jeżeli stół jest zajęty przez innych graczy, to można ustawić się w kolejce do następnej gry. Kolejkować mogą się zarejestrowani i zalogowani gracze.<br/><br/>Jeżeli gracz zajmuje miejsce przy stole i połączenie zostanie zerwane, nastąpi usunięcie gracza z krzesła. Gra zostaje przerwana jeżeli była w toku i następuje resetowanie planszy. Zerwanie połączenia następuje poprzez: odświerzenia strony, zamknięcia zakładki z grą, utratę połączenia z internetem i podwójne logowanie.<br/><br/>Ze względu na trwające ciągłe testy i ulepszenia gra dostępna jest okresowo.<br/><br/>W razie pytań zapraszam do kontaktu.');
	}
	else if (reference == "contact")
	{
		$("#headerText").css('padding', '20px');
		$("#headerText").html('Zapraszam do kontaktu za pośrednictwem mojego maila: <br/><b>mariusz.pak.89@gmail.com</b>');
	}
}	