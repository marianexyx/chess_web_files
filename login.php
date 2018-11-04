<div id="loginConsole" style="color:red; text-align:center; clear:both;"></div>

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
			$password = md5(sha1(vtxt($_POST['password']))); //powstaje zahaszowane haslo
			if (ctype_alnum($login)) //jeżeli zmienna jest alfanumeryczna
			{
				$query = row("SELECT * FROM users WHERE login = '$login' AND password = '$password'"); //pytamy się bazy czy jest taki gracz
				if ($query)
				{
					$_SESSION = array();
					$_SESSION['id'] = $query['id'];
					$_SESSION['login'] = $query['login'];
					$_SESSION['hash'] = $query['hash'];
					header('Location: index.php'); 
				} 
				else echo '<script>$("#loginConsole").html("<br/>Taki gracz nie istnieje lub hasło jest niepoprawne.")</script>';
			}
			else echo '<script>$("#loginConsole").html("<br/>Niepoprawna nazwa użytkownika. Login może składać się tylko z znaków alfanumerycznych.")</script>';
		} 
		else echo '<script>$("#loginConsole").html("<br/>Wypełnij pola poprawnie.");</script>';
	}
	
	if ($_GET['registered'] == true) 
		echo '<script>$("#loginConsole").html("<br/>Zarejestrowano poprawnie."); $("#loginConsole").css("color", "black");</script>';
?>

<br/>
<form action="index.php?a=login" method="POST"> 
	<div id="login" class="divTable">
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