<?php // plik do zabezpieczenia
	$user = 'xxx'; // Nazwa u�ytkownika bazy danych
    $pass = 'xxx'; // Has�o u�ytkownika bazy danych
    $host = 'xxx'; // Nazwa hosta (serwera) bazy danych
    $db = 'xxx'; // Nazwa naszej bazy danych
	
	$con = @new mysqli($host, $user, $pass, $db) or die ("Error " .mysqli_error($con));
 
	if ($con->connect_error) 
	{
		die("Connection failed: " . $con->connect_errno);
	} 
	
	$con-> query("SET NAMES 'utf8' COLLATE 'utf8_polish_ci'");	
	require_once('func.php');

	//sexowny koid m.zelenta kt�ry mi nei zadzia�a�
	/*mysqli_report(MYSQLI_REPORT_STRICT); //zostawi wyj�tki, wy��czy wylewne ostrze�enie dla u�ytkownik�w
	
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
		echo '<span style="color:red;">B��d serwera! Przepraszamy za niedogodno�ci i prosimy o rejestracj� w innym terminie.'</span>
		//echo '<br />Informacja developerska/dla admina: '.$e;
	}
	
	$con-> query("SET NAMES 'utf8' COLLATE 'utf8_polish_ci'");	
	require_once('func.php');*/
?>