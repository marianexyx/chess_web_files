<?php
if (!empty($_POST)){ //jak globalna zmienna nie jest pusta, tj. formularz poniżej był wysłany
	if (!empty($_POST['login']) && !empty($_POST['password'])){ //sprawdzamy czy w formularzu wypełniono login i hasło
		$login = vtxt($_POST['login']); //powstaje zmienna login
		$password = md5(sha1(vtxt($_POST['password']))); //powstaje zmienna hasło
		if (ctype_alnum($login)){ //jeżeli zmienna jest alfanumeryczna (tj. nie zawiera dzikich znaków !@#$%^&* )
			$zapytanie = row("SELECT * FROM users WHERE password = '$password' AND login = '$login'"); //pytamy się bazy czy jest taki gracz
			if ($zapytanie){ // jeżeli jest to
				$_SESSION = array(); //czyścimy superglobalną tablicę
				$_SESSION['id'] = $zapytanie['id']; // i przypisujemy superglobalną numerem gracza z bazy
				$_SESSION['login'] = $zapytanie['login'];
				header('Location: index.php?a=game'); //posiadając gracza wszędzie tam gdzie jest "session_start()" przechodzimy do indexu. skrypt tu się urywa
			} else echo 'Taki gracz nie istnieje lub hasło jest niepoprawne.';
		} else echo 'Niepoprawna nazwa użytkownika';
	} else echo '<center><b>Wypełnij pola poprawnie.</b></center>';
}

if ($_GET['registered'] == true) echo 'Zarejestrowano poprawnie.';
?>

<p>LOGOWANIE</p>
<form action="index.php?a=login" method="POST"> <!-- formularz typu post-->
	<table align="center">
		<tr>
			<td><b>Login użytkownika:</b></td>
			<td style="padding: 10px"><input type ="text" name="login"/></td>
		</tr>
		<tr>
			<td><b>Hasło:</b></td>
			<td style="padding: 10px"><input type ="password" name="password"/></td>
		</tr>
		<tr>
			<td></td>
			<td><center><input type="submit" style="width: 100px" value="Zaloguj się"/></center></td>
		</tr>
	</table>
</form>