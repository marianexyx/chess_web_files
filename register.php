<p>REJESTRACJA</p>
<?php
	//todo: sprawdzić logowanie i resejtrowanie
	//todo: akceptuje regulamin
	//todo: przywróć dane jeżeli wpisałeś coś źle (oprócz tego co źle)  https://youtu.be/fMJw90n8M60?list=PLOYHgt8dIdox81dbm1JWXQbm2geG1V2uh&t=6843  
	
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
			elseif (strlen($pass) < 6 || strlen($pass) > 20) echo 'Hasło nie mieści się w danym zakresie.';
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
						$pass = md5(sha1($pass)); //todo: pdoobno złamano md5 jakiś czas temu. 
						//password_hash($pass, PASSWORD_DEFAULT) - nowa lepsza funkcja (z solą- ponad 3 znaki używać). 
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
				else echo 'Login zawiera niedozwolone znaki (cyfry lub polskie znaki).';
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
			<td colspan="2"><div class="g-recaptcha" data-sitekey="6Lf9PygUAAAAAEPWjrGrWkXqkKbK6_uxtW64eKDj"></div></td>
		</tr>
		<tr>
			<td></td>
			<td><center><input type="submit" style="width: 100px" value="Zarejestruj się"/></center></td>
		</tr>
	</table>
</form>