<?php
checkUser($_SESSION['id']); //sprawdzmay funkcją czy gracz jest zalogowany. funkcja jest warunkiem. jak nie jest zaloowany to leci do indexu 
//todo: ogarnąć to w innych plikach
$user = getUser($_SESSION['id']); //przypisanie do zmiennej user funkcji getuser, która wyciąga dane gracza aktualnie zalogowanego

echo '<div align="right" style="float:right; padding-right: 50px"> <b>Użytkownik:</b> '.$user['login'].'&nbsp;&nbsp; </div>';

/*<table align="center">
	<tr>
		<td><b>Użytkownik:</b></td>
		<td style="padding: 10px">'.$user['login'].'&nbsp;&nbsp;
		</td>
	</tr>
</table>';*/
?>