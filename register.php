<?php
	$kapcza = "6Lf9PygUAAAAAMdD3z1hDGssDbz0obmT8aLJyHTj";
	$check = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$kapcza.'&response='.$_POST['g-recaptcha-response']);
	$response = json_decode($check);
	
	if (!empty($_POST))
	{
		if(!empty($_POST['login']) && !empty($_POST['pass']) && !empty($_POST['pass2']) && !empty($_POST['email']))
		{
			
			$login = vtxt($_POST['login']);
			$pass = vtxt($_POST['pass']);
			$email = vtxt($_POST['email']);
			if (strlen($login) < 3 || strlen($login) > 25)
			{
				echo 'Login nie mieści się w danym zakresie.';
				$loginLength = strlen($login);
				echo "login.length=$loginLength , login=$login)";
			}
			elseif (strlen($pass) < 1 || strlen($pass) > 20) echo 'Hasło nie mieści się w danym zakresie.';
			elseif (strlen($email) < 8 || strlen($pass) > 50) echo 'Adres e-mail nie mieści się w danym zakresie.';
			elseif ($login == $pass) echo 'Login nie może być taki sam jak hasło.';
			elseif ($pass != $_POST['pass2']) echo 'Podane hasła nie zgadzają się.';
			elseif (!($response->success)) echo 'Potwierdź, że nie jesteś botem.';
			else 
			{
				if (ctype_alnum($login))
				{
					if (filter_var($email, FILTER_VALIDATE_EMAIL))
					{
						$pass = md5(sha1($pass)); //future: podobno złamano md5 jakiś czas temu. 
						//password_hash($pass, PASSWORD_DEFAULT) - nowa lepsza funkcja (z solą- ponad 3 znaki używać). 
						$istnieje = row("SELECT id FROM users WHERE login ='$login' OR email = '$email'");
						if ($istnieje) echo 'Istnieje już gracz o takim samym loginie lub adresie email.';
						else
						{
							$randomHash = bin2hex(mcrypt_create_iv(10, MCRYPT_DEV_URANDOM));
							call("INSERT INTO users (login, password, email, hash) VALUES ('$login','$pass','$email','$randomHash')");
							header('Location: index.php?a=login&registered=true');
						}
					}
					else echo 'To nie nie jest poprawny adres e-mail.';
				}
				else echo 'Login zawiera niedozwolone znaki (cyfry lub polskie znaki).';
			}
		}
		else echo '<center><b>Wypełnij pola poprawnie.</b></center>';
	}
?>

<script> console.log("inside register.php"); </script>

<br/>
<br/>
<form action="index.php?a=register" method="POST">
<div class="divTable">
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