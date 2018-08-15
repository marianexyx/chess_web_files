<?php
checkUser($_SESSION['id']);
$user = getUser($_SESSION['id']); //wyciąganie danych gracza zalogowanego

echo '<div id="user" align="center"> <b>Użytkownik:</b> '.$user['login'].'&nbsp;&nbsp; </div>';
?>