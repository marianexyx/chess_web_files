<div id="emailConsole" style="color:red; text-align:center; clear:both;"></div>

<?php 
	$kapcza = "6Lf9PygUAAAAAMdD3z1hDGssDbz0obmT8aLJyHTj";
	$check = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$kapcza.'&response='.$_POST['g-recaptcha-response']);
	$response = json_decode($check);

	if (isset($_POST['submit']))
	{
		if (!empty($_POST['from']) && !empty($_POST['message']))
		{
			if ($response->success) 
			{
				$to = "mariusz.pak.89@gmail.com";
				$user_email = $_POST['email'];
				$from = $_POST['from'];
				$subject = "Budgames chess contact from: " . $from;
				$message = $from . " wrote the following:" . "\n\n" . $_POST['message'] . "\n\n Sender email (if given):" . $user_email;
				$headers = "From:" . $from;
				mail($to, $subject, $message, $headers);
				echo "<center><b>Wiadomość została wysłana.</b></center>";
				//header('Location: index.php?a=login&registered=true');
				// You can also use header('Location: thank_you.php'); to redirect to another page.
			}
			else echo '<script>$("#emailConsole").html("<br/>Potwierdź, że nie jesteś botem.")</script>';
		}
		else echo '<script>$("#emailConsole").html("<br/>Wypełnij pola poprawnie.")</script>';
    }
?>

<br/>
<form action="" method="POST"> 
	<div id="email" class="divTable">
		<div class="divTableBody">
			<div class="divTableRow">
				<div class="divTableCell">&nbsp;</div>
				<div class="divTableCell" style="font-size: 150%">ZGŁOŚ BŁĄD LUB AWARIĘ</div>
			</div>
			<div class="divTableRow">
				<div class="divTableCell"><b>Od:</b></div>
				<div class="divTableCell"><input type ="text" name="from"/></div>
			</div>
			<div class="divTableRow">
				<div class="divTableCell"><b>Twój email (opcjonalnie):</b></div>
				<div class="divTableCell"><input type ="text" name="user_email"/></div>
			</div>
			<div class="divTableRow">
				<div class="divTableCell"><b>Wiadomość:</b></div>
				<div class="divTableCell"><textarea rows="5" name="message" cols="30"></textarea></div>
			</div>
			<div class="divTableRow">
				<div class="divTableCell">&nbsp;</div>
				<div class="divTableCell"><div class="g-recaptcha" data-sitekey="6Lf9PygUAAAAAEPWjrGrWkXqkKbK6_uxtW64eKDj"></div></div>
			</div>
			<div class="divTableRow">
				<div class="divTableCell">&nbsp;</div>
				<div class="divTableCell"><input type="submit" style="width: 100px" value="Wyślij"/></div>
			</div>
		</div>
	</div>
</form>