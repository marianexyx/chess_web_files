<?php
	if(!empty($_SESSION['id'])) return header("Location: index.php"); // Jeśli gracz jest zalogowany to przejdź do strony gry      
	
	if (!empty($_POST)) //jak globalna zmienna nie jest pusta, tj. formularz poniżej był wysłany
	{
		if (!empty($_POST['login']) && !empty($_POST['password'])) //sprawdzamy czy w formularzu wypełniono login i hasło
		{
			//future: $login = htmlentities($login, ENT_QUOTES, "UTF-8"); - hasło też. sprawdzić czy, jak i gdzie dodać to zabezpieczenie i jak to się ma do vtxt
			//sprintf
			//https://youtu.be/Pp578w7C9hE?t=5764
			$login = vtxt($_POST['login']); //powstaje zmienna login
			$password = md5(sha1(vtxt($_POST['password']))); //powstaje zmienna hasło
			if (ctype_alnum($login)) //jeżeli zmienna jest alfanumeryczna (tj. nie zawiera dzikich znaków !@#$%^&* )
			{
				$zapytanie = row("SELECT * FROM users WHERE password = '$password' AND login = '$login'"); //pytamy się bazy czy jest taki gracz
				if ($zapytanie) // jeżeli jest to
				{
					$_SESSION = array(); //czyścimy superglobalną tablicę
					$_SESSION['id'] = $zapytanie['id']; // i przypisujemy superglobalną numerem gracza z bazy
					$_SESSION['login'] = $zapytanie['login'];
					$_SESSION['hash'] = $zapytanie['hash'];
					header('Location: index.php'); //posiadając gracza wszędzie tam gdzie jest "session_start()" przechodzimy do indexu. skrypt tu się urywa
				} 
				else echo 'Taki gracz nie istnieje lub hasło jest niepoprawne.';
			}
			else echo 'Niepoprawna nazwa użytkownika. Login może składać się tylko z znaków alfanumerycznych.';
		} 
		else echo '<center><b>Wypełnij pola poprawnie.</b></center>';
	}
	
	if ($_GET['registered'] == true) echo 'Zarejestrowano poprawnie.';
?>

<br/>
<br/>
<form action="index.php?a=login" method="POST"> 
<div class="divTable">
	<div class="divTableBody">
		<div class="divTableRow">
			<div class="divTableCell">&nbsp;</div>
			<div class="divTableCell" style="font-size: 150%">LOGOWANIE</div>
		</div>
		
		<div class="divTableRow">
			<div class="divTableCell"><b>Login użytkownika:</b></div>
			<div class="divTableCell"><input type ="text" name="login"/></div>
		</div>
		<div class="divTableRow">
			<div class="divTableCell"><b>Hasło:</b></div>
			<div class="divTableCell"><input type ="password" name="password"/></div>
		</div>
		<div class="divTableRow">
			<div class="divTableCell">&nbsp;</div>
			<div class="divTableCell"><input type="submit" style="width: 100px" value="Zaloguj się"/></div>
		</div>
		
	</div>
</div>
</form>