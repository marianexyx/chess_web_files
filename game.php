<?php
checkUser($_SESSION['id']); //sprawdzmay funkcją czy gracz jest zalogowany. funkcja jest warunkiem. jak nie jest zaloowany to leci do indexu 
//todo: ogarnąć to w innych plikach
$user = getUser($_SESSION['id']); //przypisanie do zmiennej user funkcji getuser, która wyciąga dane gracza aktualnie zalogowanego

echo '<div align="center" style="float:right; margin:0 auto;"> <b>Użytkownik:</b> '.$user['login'].'&nbsp;&nbsp; </div>';

?>