<div id="registerConsole" style="color:red; text-align:center; clear:both;"></div>

<?php
	$kapcza = "6Lf9PygUAAAAAMdD3z1hDGssDbz0obmT8aLJyHTj";
	$check = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$kapcza.'&response='.$_POST['g-recaptcha-response']);
	$response = json_decode($check);
	
	if (!empty($_POST))
	{
		if(!empty($_POST['login']) && !empty($_POST['pass']) && !empty($_POST['pass2']) && !empty($_POST['email']))
		{
			require_once('illegalNicknames.php');
			
			$login = vtxt($_POST['login']);
			$pass = vtxt($_POST['pass']);
			$email = vtxt($_POST['email']);
			if (strlen($login) < 3 || strlen($login) > 25) echo '<script>$("#registerConsole").html("<br/>Login nie mieści się w danym zakresie.")</script>';
			else if (isLoginIllegal($login) == true) echo '<script>$("#registerConsole").html("<br/>Istnieje już gracz o takim samym loginie.")</script>';
			else if (strlen($pass) < 1 || strlen($pass) > 20) echo '<script>$("#registerConsole").html("<br/>Hasło nie mieści się w danym zakresie.")</script>';
			else if (strlen($email) < 8 || strlen($pass) > 50) echo '<script>$("#registerConsole").html("<br/>Adres e-mail nie mieści się w danym zakresie.")</script>';
			else if ($login == $pass) echo '<script>$("#registerConsole").html("<br/>Login nie może być taki sam jak hasło.")</script>';
			else if ($pass != $_POST['pass2']) echo '<script>$("#registerConsole").html("<br/>Podane hasła nie zgadzają się.")</script>';
			else if (!($response->success)) echo '<script>$("#registerConsole").html("<br/>Potwierdź, że nie jesteś botem.")</script>';
			else 
			{
				if (ctype_alnum($login))
				{
					if (filter_var($email, FILTER_VALIDATE_EMAIL))
					{
						$pass = md5(sha1($pass)); //future: podobno złamano md5 jakiś czas temu. 
						//future: password_hash($pass, PASSWORD_DEFAULT) - nowa lepsza funkcja (z solą- ponad 3 znaki używać). 
						$bIsClientExists = row("SELECT id FROM users WHERE login ='$login' OR email = '$email'");
						if ($bIsClientExists) 
							echo '<script>$("#registerConsole").html("<br/>Istnieje już gracz o takim samym loginie lub adresie email.")</script>';
						else
						{
							$randomHash = bin2hex(mcrypt_create_iv(10, MCRYPT_DEV_URANDOM)); //create new random login hash for user
							call("INSERT INTO users (login, password, email, hash) VALUES ('$login','$pass','$email','$randomHash')");
							header('Location: index.php?a=login&registered=true');
						}
					}
					else echo '<script>$("#registerConsole").html("<br/>To nie nie jest poprawny adres e-mail.")</script>';
				}
				else echo '<script>$("#registerConsole").html("<br/>Login zawiera niedozwolone znaki (cyfry lub polskie znaki).")</script>';
			}
		}
		else echo '<script>$("#registerConsole").html("<br/>Wypełnij pola poprawnie.")</script>';
	}
?>

<br/>
<form action="index.php?a=register" method="POST">
	<div id="register" class="divTable">
		<div class="divTableBody">
			<div class="divTableRow">
				<div class="divTableCell">&nbsp;</div>
				<div class="divTableCell" style="font-size: 150%">REJESTRACJA</div>
				<div class="divTableCell">&nbsp;</div>
			</div>
			<div class="divTableRow">
				<div class="divTableCell"><b>Login użytkownika:</b></div>
				<div class="divTableCell"><input type="text" name="login"/>&nbsp;&nbsp;&nbsp;(od 3 do 25 znaków)</div>
			</div>
			<div class="divTableRow">
				<div class="divTableCell"><b>Hasło:</b></div>
				<div class="divTableCell"><input type="password" name="pass"/>&nbsp;&nbsp;&nbsp;(od 1 do 20 znaków)</div>
			</div>
			<div class="divTableRow">
				<div class="divTableCell"><b>Powtórz hasło:</b></div>
				<div class="divTableCell"><input type="password" name="pass2"/></div>
			</div>
			<div class="divTableRow">
				<div class="divTableCell"><b>E-mail:</b></div>
				<div class="divTableCell"><input type="text" name="email"/>&nbsp;&nbsp;&nbsp;(od 8 do 50 znaków)</div>
			</div>
			<div class="divTableRow">
				<div class="divTableCell">&nbsp;</div>
				<div class="divTableCell"><div class="g-recaptcha" data-sitekey="6Lf9PygUAAAAAEPWjrGrWkXqkKbK6_uxtW64eKDj"></div></div>
			</div>
			<div class="divTableRow">
				<div class="divTableCell">&nbsp;</div>
				<div class="divTableCell"><input type="submit" style="width: 100px" value="Zarejestruj się"/></div>
			</div>
		</div>
	</div>
</form>