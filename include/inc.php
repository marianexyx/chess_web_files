<?php // plik do zabezpieczenia
	$user = 'xxxxxxxxxxxx'; // Nazwa u¿ytkownika bazy danych
    $pass = 'xxxxxxxxxxxx'; // Has³o u¿ytkownika bazy danych
    $host = 'xxxxxxxxxxx'; // Nazwa hosta (serwera) bazy danych
    $db = 'xxxxxxxxxxxxx'; // Nazwa naszej bazy danych
	
	$con = new mysqli($host, $user, $pass, $db) or die ("Error " .mysqli_error($con));
 
    if ($con->connect_error) {
		die("Connection failed: " . $con->connect_error);
	} 
	
	$con-> query("SET NAMES 'utf8' COLLATE 'utf8_polish_ci'");	
	require_once('func.php');
?>