<?php // plik do zabezpieczenia
	$user = 'xxxxxxxxxxxx'; // Nazwa użytkownika bazy danych
    $pass = 'xxxxxxxxxxxx'; // Hasło użytkownika bazy danych
    $host = 'xxxxxxxxxxx'; // Nazwa hosta (serwera) bazy danych
    $db = 'xxxxxxxxxxxxx'; // Nazwa naszej bazy danych
	
	mysqli_report(MYSQLI_REPORT_STRICT); //zostawi wyjątki, wyłączy wylewne ostrzeżenie dla użytkowników
	
	try
	{
		$con = new mysqli($host, $user, $pass, $db) or die ("Error " .mysqli_error($con));
		if ($con->connect_erro!=0) 
		{
			throw new Exception(mysqli_connect_errno())
		} 
		else
		{
			$con->close();
		}
	}
	catch(Exception $e)
	{
		echo '<span style="color:red;">Błąd serwera! Przepraszamy za niedogodności i prosimy o rejestrację w innym terminie.'</span>
		//echo '<br />Informacja developerska/dla admina: '.$e;
	}
	
	$con-> query("SET NAMES 'utf8' COLLATE 'utf8_polish_ci'");	
	require_once('func.php');
?>