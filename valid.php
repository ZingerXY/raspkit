<?
// http://raspkit.ru/valid.php?id=1&hash=5930174738d68
include "config.php";
if(isset($_GET["id"]) and isset($_GET["hash"])) {
	$id =  mysqli_real_escape_string($mysqli, $_GET["id"]);
	$hash =  mysqli_real_escape_string($mysqli, $_GET["hash"]);
	$result = mysqli_query($mysqli, "SELECT * FROM users WHERE id = '$id'");
	if($result) {
		$data = mysqli_fetch_assoc($result);
		if($hash == $data["hash"]) {
			mysqli_query($mysqli, "UPDATE users SET email_valid=1 WHERE id='$id'");
			$_SESSION["msg"] = array("text"=>"Email подтвержден","type"=>"alert-success");
			if(isset($_SESSION['user']))
				$_SESSION['user']['email_valid'] = 1;
			header("Location: http://raspkit.ru/?profile");			
			//echo "Почта подтверждена<br>";
		} else {
			$_SESSION["msg"] = array("text"=>"Email не подтвержден.<br>Код подтверждения не верный.","type"=>"alert-danger");
			header("Location: http://raspkit.ru/?profile");
			//echo "Код подтверждения не верный<br>";
		}
	}
} elseif(isset($_GET["send_valid_msg"]) and isset($_SESSION['user'])) {
	$user_id = $_SESSION["user"]["id"];
	$user_hash = $_SESSION["user"]["hash"];
	$user_login = $_SESSION["user"]["login"];
	$user_email = $_SESSION["user"]["email"];
	$user_email_valid = $_SESSION["user"]['email_valid'];
	if(!$user_email_valid) {
		$valid_url = "http://raspkit.ru/valid.php?id=$user_id&hash=$user_hash";
		$to= $user_email;
		$head="Content-Type: text/html; charset=UTF-8\r\n";
		$head.="From: Расписание СПБКИТ <info@raspkit.ru>\r\n";
		$head.="X-Mailer: PHP/".phpversion()."\r\n";
		$subject='Подтверждение Email';
		$msg="<!DOCTYPE HTML>
		<table border='0' cellpadding='0' cellspacing='0' style='margin:0; padding:0' width='100%'>
			<tr><td>
				<center style='width: 100%;'>
					<table border='0' cellpadding='0' cellspacing='0' style='margin:0; padding:0; max-width: 600px;' width='100%;'>
						<tr><td>
							<div style='line-height: 30px;'>
							<br />Здравтсвуйте, <b>$user_login</b>
							<br />Вы зарегестрированы на сайте Расписание СПБКИТ raspkit.ru
							<br />Для подтверждения почты, пожалуйста, подтвердите ваш электронный адрес.
							<br /><a href='$valid_url' style='background-color:#337ab7;border-radius:4px;color:#fff;display:inline-block;font-family: Helvetica, Arial;font-size:14px;font-weight:normal;line-height:36px;text-align:center;text-decoration:none;width:184px;-webkit-text-size-adjust:none;'>
							Подтвердить адрес
							</a>
							<br />Если вы не регистрировались на сайте Расписание СПБКИТ — просто проигнорируйте это письмо.
							<br /><hr>
							<small>Письмо было сформировоно автоматически, на него необязательно отвечать.</small>
							<div>
						</td></tr>
					</table>
				</center>
			</td></tr>
		</table>";
		$mail_send = mail($to,$subject,$msg,$head);
		$_SESSION["msg"] = array("text"=>"Email для подтверждения отправлен.","type"=>"alert-success");
	} else {
		$_SESSION["msg"] = array("text"=>"Ваш Email уже подтвержден.","type"=>"alert-warning");		
	}
	header("Location: http://raspkit.ru/?profile");
} else {	
	header("Location: http://raspkit.ru/");
}
?>