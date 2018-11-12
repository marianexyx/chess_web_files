<?php 
	if (isset($_POST['arrayMsg']))
	{
		$arrayMsg = $_POST['arrayMsg'];
		if (array_key_exists("reportMessage", $arrayMsg) && !empty($arrayMsg['reportMessage']))
		{			
			$to = "mariusz.pak.89@gmail.com";
			$subject = "Budgames chess report";
			$from;
			if(!isset($_SESSION)) 
				session_start();
			if (array_key_exists("reportFrom", $arrayMsg))
				$from .= "User (if given): " . $arrayMsg["reportFrom"];
			if (!empty($_SESSION['login']))
				$from .= ' ($_SESSION["login"] = ' . $_SESSION['login'] . ')';
			
			$user_email;
			if (array_key_exists("reportUserMail", $arrayMsg))
				$user_email = $arrayMsg["reportUserMail"];
			
			$message = $from . " wrote the following msg:\n\n" . $arrayMsg['reportMessage'] . "\n\n\n Sender email (if given):" . $user_email;
			$headers = "From: " . $from; //propably useless, but let it be here
			mail($to, $subject, $message, $headers);
			echo "ok:Wiadomość została wysłana.";
		}
		else echo 'er:Wypełnij pola poprawnie.';
	}
	else echo 'er:Wiadomość pusta.';
?>