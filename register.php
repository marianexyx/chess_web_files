<?php
	if (isset($_POST['arrayMsg']))
	{
		$arrayMsg = $_POST['arrayMsg'];
		if (array_key_exists("registerLogin", $arrayMsg) && !empty($arrayMsg['registerLogin']) &&
			array_key_exists("registerPass", $arrayMsg) && !empty($arrayMsg['registerPass']) &&
			array_key_exists("registerPass2", $arrayMsg) && !empty($arrayMsg['registerPass2']) &&
			array_key_exists("registerEmail", $arrayMsg) && !empty($arrayMsg['registerEmail']))
		{	
			$kapcza = "6Lf9PygUAAAAAMdD3z1hDGssDbz0obmT8aLJyHTj";
			$check = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$kapcza.'&response='.$_POST['g-recaptcha-response']);
			$response = json_decode($check);
	
			require_once('illegalNicknames.php');
			require_once('include/func.php');
			
			$login = vtxt($arrayMsg['registerLogin']);
			$pass = vtxt($arrayMsg['registerPass']);
			$email = vtxt($arrayMsg['registerEmail']);
			if (strlen($login) < 3 || strlen($login) > 25) echo 'er:Login nie mieści się w danym zakresie.';
			else if (isLoginIllegal($login)) echo 'er:Istnieje już gracz o takim samym loginie.';
			else if (strlen($pass) < 1 || strlen($pass) > 20) echo 'er:Hasło nie mieści się w danym zakresie.';
			else if (strlen($email) < 8 || strlen($pass) > 50) echo 'er:Adres e-mail nie mieści się w danym zakresie.';
			else if ($login == $pass) echo 'er:Login nie może być taki sam jak hasło.';
			else if ($pass != $_POST['pass2']) echo 'er:Podane hasła nie zgadzają się.';
			else if (!($response->success)) echo 'er:Potwierdź, że nie jesteś botem.';
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
							echo 'er:Istnieje już gracz o takim samym loginie lub adresie email.';
						else
						{
							$randomHash = bin2hex(mcrypt_create_iv(10, MCRYPT_DEV_URANDOM)); //create new random login hash for user
							call("INSERT INTO users (login, password, email, hash) VALUES ('$login','$pass','$email','$randomHash')");
							echo 'ok:Zarejestrowano poprawnie.';
						}
					}
					else echo 'er:To nie nie jest poprawny adres e-mail.';
				}
				else echo 'er:Login zawiera niedozwolone znaki (cyfry lub polskie znaki).';
			}
		}
		else echo 'er:Wypełnij pola poprawnie.';
	}
	else echo 'er:Wypełnij pola poprawnie.';
?>