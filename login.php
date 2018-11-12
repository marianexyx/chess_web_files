<?php
	if (!empty($_SESSION['id'])) 
		echo 'er:Jesteś już zalogowany'; 
	
	if (isset($_POST['arrayMsg']))
	{
		$arrayMsg = $_POST['arrayMsg'];
		if (array_key_exists("loginLogin", $arrayMsg) && !empty($arrayMsg['loginLogin']) &&
			array_key_exists("loginPassword", $arrayMsg) && !empty($arrayMsg['loginPassword']))
		{
			require_once('include/func.php');
			
			//future: $login = htmlentities($login, ENT_QUOTES, "UTF-8"); - hasło też. sprawdzić czy, jak i gdzie dodać to zabezpieczenie i jak to się ma do vtxt
			//sprintf
			//https://youtu.be/Pp578w7C9hE?t=5764
			$login = vtxt($arrayMsg['loginLogin']); //powstaje zmienna login
			$password = md5(sha1(vtxt($arrayMsg['loginPassword']))); //powstaje zahaszowane haslo
			if (ctype_alnum($login)) //jeżeli zmienna jest alfanumeryczna
			{
				$query = row("SELECT * FROM users WHERE login = '$login' AND password = '$password'"); //pytamy się bazy czy jest taki gracz
				if ($query)
				{
					session_start();
					$_SESSION = array();
					$_SESSION['id'] = $query['id'];
					$_SESSION['login'] = $query['login'];
					$_SESSION['hash'] = $query['hash'];
					$specialOption = 'im '.$_SESSION['id'].'&'.$_SESSION['hash'] ;
					echo 'ok:'.$specialOption; 
				} 
				else echo 'er:Taki gracz nie istnieje lub hasło jest niepoprawne.';
			}
			else echo 'er:Niepoprawna nazwa użytkownika. Login może składać się tylko z znaków alfanumerycznych. Login = '.$login;
		}
		else echo 'er:Wypełnij pola poprawnie.';
	}
	else echo 'er:Wypełnij pola poprawnie.';
?>