<p>REJESTRACJA</p>
<?php
	if (!empty($_POST))
	{
		if(!empty($_POST['login']) && !empty($_POST['pass']) && !empty($_POST['pass2']) && !empty($_POST['email']))
		{
			$login = vtxt($_POST['login']);
			$pass = vtxt($_POST['pass']);
			$email = vtxt($_POST['email']);
			if (strlen($login) < 3 || strlen($login) > 25) echo 'Login nie mieści się w danym zakresie.';
			elseif (strlen($pass) < 6 || strlen($pass) > 20) echo 'Hasło nie mieści się w danym zakresie.';
			elseif (strlen($email) < 8 || strlen($pass) > 50) echo 'Adres e-mail nie mieści się w danym zakresie.';
			elseif ($login == $pass) echo 'Login nie może być taki sam jak hasło.';
			elseif ($pass != $_POST['pass2']) echo 'Podane hasła nie zgadzają się.';
			else 
			{
				if (ctype_alnum($login))
				{
					if (filter_var($email, FILTER_VALIDATE_EMAIL))
					{
						$pass = md5(sha1($pass));
						$istnieje = row("SELECT id FROM users WHERE login ='$login' OR email = '$email'");
						if ($istnieje) echo 'Istnieje już gracz o takim samym loginie lub adresie email.';
						else
						{
							call("INSERT INTO users (login, password, email) VALUES ('$login','$pass','$email')");
							header('Location: index.php?a=login&registered=true');
						}
					}
					else echo 'To nie nie jest poprawny adres e-mail.';
				}
				else echo 'Login zawiera niedozwolone znaki. Zezwolone są tylko znaki alfanumeryczne.';
			}
		}
		else echo '<center><b>Wypełnij pola poprawnie.</b></center>';
	}
?>
<form action="index.php?a=register" method="POST">
	<table align="center">
		<tr>
			<td><b>Login użytkownika:</b></td>
			<td style ="pading: 10px"><input type="text" name="login"/></td>
			<td>(Od 3 do 25 znaków. )</td>
		</tr>
		<tr>
			<td><b>Hasło:</b></td>
			<td style ="pading: 10px"><input type="password" name="pass"/></td>
			<td>(Od 6 do 20 znaków)</td>
		</tr>
		<tr>
			<td><b>Powtórz hasło:</b></td>
			<td style ="pading: 10px"><input type="password" name="pass2"/></td>
			<td></td>
		</tr>
		<tr>
			<td><b>E-mail:</b></td>
			<td style ="pading: 10px"><input type="text" name="email"/></td>
			<td>(Od 8 do 50 znaków)</td>
		</tr>
		<tr>
			<td></td>
			<td><center><input type="submit" style="width: 100px" value="Zarejestruj się"/></center></td>
		</tr>
	</table>
</form>