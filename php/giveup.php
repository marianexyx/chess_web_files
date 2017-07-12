<?php
	enabling('clickedBtn');
			
	if ($_SESSION['white'] == $loginUzytkownika)
	{
		change("whitePlayer", WHITE)
	}
	else if ($_SESSION['black'] == $loginUzytkownika)
	{
		change("blackPlayer", BLACK) 
	}
			
	resetBoard();
?>