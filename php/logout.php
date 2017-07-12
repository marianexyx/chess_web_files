<?php
	if ($_SESSION['white'] == $loginUzytkownika) change('whitePlayer', WHITE);
	else if ($_SESSION['black'] == $loginUzytkownika) change('blackPlayer', BLACK);
		
	enabling('clikedBtn');
		
	debugToConsole('logged out');
?>