<?php
/*if($_SERVER['REQUEST_URI'] == "/index.php?gr=") {
	header("Location: /",TRUE,301);
	exit();
}*/
// Файл служит для отображения сохраненных локльных расписаний,
// для парсинга текущих замен

include "config.php";
header("Cache-Control: no-store, no-cache, must-revalidate");

//var_dump($_SESSION);

$user_auth = false;
$user_id = 0;
$user_hash = "";
$user_login = "";
$user_email = "";
$user_email_valid = 0;
$user_alert = 0;
$user_gr = 0;

$msg_show = false;
$msg_str = "";
$msg_type = "";
// СООБЩЕНИЕ
if(isset($_SESSION["msg"])){
	$msg_show = true;
	$msg_str = $_SESSION["msg"]["text"];
	$msg_type = $_SESSION["msg"]["type"];
	//var_dump($_SESSION);
	unset($_SESSION["msg"]);
}
// ВХОД
if(isset($_POST['do_signup'])) {
	$errors = false;
	if(empty(trim($_POST['login']))) { // Проверка логина
		$errors = true;
		$msg_show = true;
		$msg_str = "Введите логин";
		$msg_type = "alert-warning";
	}
	elseif(empty($_POST['pass'])) { // Проверка пароля
		$errors = true;
		$msg_show = true;
		$msg_str = "Введите пароль";
		$msg_type = "alert-warning";
	}	
	if(!$errors){
		$login = mysqli_real_escape_string($mysqli, $_POST['login']);
		$pass = mysqli_real_escape_string($mysqli, $_POST['pass']);
		$ip = $_SERVER['REMOTE_ADDR'];
		
		# Вытаскиваем из БД запись, у которой логин равняеться введенному
		$query = mysqli_query($mysqli, "SELECT * FROM users WHERE login='$login' LIMIT 1");
		$data = mysqli_fetch_assoc($query);
		
		$id = $data['id'];
		$email = $data['email'];
		$gr = $data['gr'];
		$user_email_valid = $data['email_valid'];
		$alert = $data['alert'];
		# Сравниваем пароли
		if(password_verify($pass, $data['pass']))
		{
			# Генерируем идендификатор
			$hash = uniqid();
		 
			# Записываем в БД новый хеш авторизации и IP
			mysqli_query($mysqli, "UPDATE users SET hash='$hash', ip='$ip' WHERE id='$id'");

			# Создаем переменную в ссесии
			$_SESSION["user"] = array("id"=>$id,"hash"=>$hash,"login"=>$login,"email"=>$email,"gr"=>$gr,"email_valid"=>$user_email_valid,"alert"=>$alert);

			# ВХОД ВЫПОЛНЕН УСПЕШНО
			//echo "Вход выполнен успешно";
			$msg_show = true;
			$msg_str = "Вход выполнен успешно.";
			$msg_type = "alert-success";
		}
		else
		{
			$msg_show = true;
			$msg_str = "Логин или пароль не верный.";
			$msg_type = "alert-danger";
		}
	} else {
		// найдены ошибки
	}
} // РЕГИСТРАЦИЯ
elseif(isset($_POST['do_reg'])) {
	$errors = false;
	
	if(isset($_POST["g-recaptcha-response"])) { // ПРОВЕРКА КАПТЧИ
		$url = 'https://www.google.com/recaptcha/api/siteverify';
		$data = array('secret' => '6LfnviMUAAAAAJKeq8MGNGTjsp8n3PbYjDROF3n7',
					'response' => $_POST["g-recaptcha-response"], 
					'remoteip' => $_SERVER["REMOTE_ADDR"],);

		$options = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'content' => http_build_query($data)
			)
		);
		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		if ($result === FALSE) { 
			$errors = true;
			$_SESSION["msg"] = array("text"=>"Проверка reCaptcha не пройдена","type"=>"alert-danger");
		}
		else {
			$res = (array)json_decode($result);
			//var_dump($res);
			if($res["success"])
				$errors = false;
			else {
				$errors = true;
				$_SESSION["msg"] = array("text"=>"Проверка reCaptcha не пройдена","type"=>"alert-danger");
			}				
		}
	}						
	elseif(empty(trim($_POST['login']))) {
		$errors = true;
		$_SESSION["msg"] = array("text"=>"Введите логин","type"=>"alert-warning");
	}
	elseif(empty(trim($_POST['email']))) {
		$errors = true;
		$_SESSION["msg"] = array("text"=>"Введите E-mail","type"=>"alert-warning");
	}
	elseif(empty($_POST['pass1'])) {
		$errors = true;
		$_SESSION["msg"] = array("text"=>"Введите пароль","type"=>"alert-warning");
	}
	elseif(empty($_POST['pass2'])) {
		$errors = true;
		$_SESSION["msg"] = array("text"=>"Введите повторный пароль","type"=>"alert-warning");
	}
	elseif($_POST['pass2'] != $_POST['pass1']) {
		$errors = true;
		$_SESSION["msg"] = array("text"=>"Пароли не равны","type"=>"alert-warning");
	}
	
	if(!$errors){
		// проверки пройдены
		$login = mysqli_real_escape_string($mysqli, $_POST['login']);
		$pass = mysqli_real_escape_string($mysqli, $_POST['pass1']);
		$email = mysqli_real_escape_string($mysqli, strtolower($_POST['email']));
		$ip = $_SERVER['REMOTE_ADDR'];
		$hash = uniqid();
		
		$result = mysqli_query($mysqli, "SELECT * FROM users WHERE login = '$login'");	
		if(mysqli_num_rows($result) == 0){
			// Получаем хеш пароля
			$pass = password_hash($pass, PASSWORD_BCRYPT, $options);
			// Регистрация
			$result = mysqli_query($mysqli, "INSERT INTO users SET login='$login', email='$email', pass='$pass', hash='$hash', ip='$ip'");
			
			$query = mysqli_query($mysqli, "SELECT id, pass FROM users WHERE email='$email' LIMIT 1");
			$data = mysqli_fetch_assoc($query);
			
			$id = $data['id'];
			
			$_SESSION["user"] = array("id"=>$id,"hash"=>$hash,"login"=>$login,"email"=>$email,"gr"=>0,"email_valid"=>0,"alert"=>0);
			
			$valid_url = "http://raspkit.ru/valid.php?id=$id&hash=$hash";
			$to= $email;
			$head="Content-Type: text/html; charset=UTF-8\r\n";
			$head.="From: Расписание СПБКИТ <info@raspkit.ru>\r\n";
			$head.="X-Mailer: PHP/".phpversion()."\r\n";
			$subject='Регистрация на сайте Расписание СПБКИТ';
			$msg="<!DOCTYPE HTML>
			<table border='0' cellpadding='0' cellspacing='0' style='margin:0; padding:0' width='100%'>
				<tr><td>
					<center style='width: 100%;'>
						<table border='0' cellpadding='0' cellspacing='0' style='margin:0; padding:0; max-width: 600px;' width='100%;'>
							<tr><td>
								<div style='line-height: 30px;'>
								<br />Здравтсвуйте, <b>$login</b>
								<br />Вы зарегестрировались на сайте Расписание СПБКИТ raspkit.ru
								<br />Для завершения регистрации, пожалуйста, подтвердите ваш электронный адрес.
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
			$_SESSION["msg"] = array("text"=>"Регистрация прошла успешно.<br>Проверьте почту для подтверждения email","type"=>"alert-success");			
			header("Location: http://raspkit.ru/"); 
		}
		else {
			// Логин уже есть
			$errors = true;
			$_SESSION["msg"] = array("text"=>"Пользователь с этим логином уже есть.","type"=>"alert-danger");
		}
	}
	if($errors)
		header("Location: http://raspkit.ru/?reg");
} // ОБНОВЛЕНИЕ ДАННЫХ В ПРОФИЛЕ
elseif(isset($_POST['do_update'])) { 
	$errors = false;
	/*if(empty(trim($_POST['login']))) {
		$errors = true;
		$_SESSION["msg"] = array("text"=>"Введите логин","type"=>"alert-warning");
	}*/
	if(empty(trim($_POST['email']))) {
		$errors = true;
		$_SESSION["msg"] = array("text"=>"Введите E-mail","type"=>"alert-warning");
	}
	elseif($_POST['pass1'] != $_POST['pass2']) {
		$errors = true;
		$_SESSION["msg"] = array("text"=>"Пароли не равны","type"=>"alert-warning");
	}
	elseif(empty($_POST['pass'])) { // Проверка пароля
		$errors = true;
		$_SESSION["msg"] = array("text"=>"Введите текущий пароль","type"=>"alert-warning");
	}	
	
	if(!$errors){
		$uplogin = $_SESSION["user"]["login"];//mysqli_real_escape_string($mysqli, $_POST['login']);
		$uppass = mysqli_real_escape_string($mysqli, $_POST['pass']);
		$ip = $_SERVER['REMOTE_ADDR'];
		
		$newpass = mysqli_real_escape_string($mysqli, $_POST['pass1']);
		$upemail = mysqli_real_escape_string($mysqli, $_POST['email']);
				
		# Вытаскиваем из БД запись, у которой логин равняеться введенному
		$query = mysqli_query($mysqli, "SELECT * FROM users WHERE login='$uplogin' LIMIT 1");
		$data = mysqli_fetch_assoc($query);
		
		$id = $data['id'];
		$email = $data['email'];
		$gr = $data['gr'];
		$email_valid = $data['email_valid'];
		
		if($upemail != $email) {
			$email_valid = false;
		}
		if(empty($newpass)) {
			$newpass = $data['pass'];
		} else {
			// Получаем хеш пароля
			$newpass = password_hash($newpass, PASSWORD_BCRYPT, $options);
		}
		# Сравниваем пароли
		if(password_verify($uppass, $data['pass']) or true)
		{		 
			# Записываем в БД обновленные данные
			$result = mysqli_query($mysqli, "UPDATE users SET email='$upemail', email_valid='$email_valid', pass='$newpass', ip='$ip' WHERE id='$id'");
			if($result) {
				$_SESSION["user"]["email"] = $upemail;
				$_SESSION["user"]['email_valid'] = $email_valid;
				# ДАННЫЕ УСПЕШНО ОБНОВЛЕНЫ
				$_SESSION["msg"] = array("text"=>"Данные успешно обновлены.","type"=>"alert-success");
			} else {
				$_SESSION["msg"] = array("text"=>"Ошибка! Попробуйте еще раз.","type"=>"alert-danger");
			}
		}
		else
		{
			$_SESSION["msg"] = array("text"=>"Текущий пароль не верный.","type"=>"alert-danger");
		}
	}
	header("Location: http://raspkit.ru/?profile");
}
elseif(isset($_POST['do_setting'])) {
	if($_POST['alert']=='on') {
		$alert = true;
	} else {
		$alert = false;
	}	
	$id = $_SESSION["user"]["id"];
	$gr = mysqli_real_escape_string($mysqli, $_POST['gr']);
	$result = mysqli_query($mysqli, "UPDATE users SET gr='$gr', alert='$alert' WHERE id='$id'");
	if($result) {
		$_SESSION["user"]["gr"] = $gr;
		$_SESSION["user"]['alert'] = $alert;
		$_SESSION["msg"] = array("text"=>"Данные успешно обновлены.","type"=>"alert-success");
	} else {
		$_SESSION["msg"] = array("text"=>"Ошибка! Попробуйте еще раз.","type"=>"alert-danger");
	}
	header("Location: http://raspkit.ru/?settings");
} // ВЫХОД
elseif(isset($_REQUEST['do_logout'])) {
	unset($_SESSION['user']);
	header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
}

if(isset($_SESSION["user"])) {
	$user_id = $_SESSION["user"]["id"];
	$user_hash = $_SESSION["user"]["hash"];
	$user_login = $_SESSION["user"]["login"];
	$user_email = $_SESSION["user"]["email"];
	$user_gr = $_SESSION["user"]["gr"];
	$user_email_valid = $_SESSION["user"]['email_valid'];
	$user_alert = $_SESSION["user"]['alert'];
	$query = mysqli_query($mysqli, "SELECT * FROM users WHERE id='$user_id' LIMIT 1");
	$data = mysqli_fetch_assoc($query);
	if($user_hash == $data["hash"]) {
		$user_auth = true;
	}
	else {
		$user_auth = false;
	}
}
$filter = array(
	'options' => array(
		'default' => 0, // значение, возвращаемое, если фильтрация завершилась неудачей
		// другие параметры
		'min_range' => 0
	),
	'flags' => FILTER_FLAG_ALLOW_OCTAL,
);

date_default_timezone_set('Europe/Moscow');
$curgr = filter_var(def($_GET['gr']), FILTER_VALIDATE_INT, $filter);

$title = "";	
if($curgr) $title .= " группы ".$curgr;
$weekday = date('N');
$hour = date('H');
$weeks = date('W');
if($weekday > 6 || $weekday == 6 && $hour >= 16) {
	$weekday = 1;
	$weeks++;
}
else if ($hour >= 16) $weekday++;	
$weekn = $weeks % 2;
?>
<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="favicon.ico">

    <title>Расписание СПбКИТ<?echo $title;?></title>

    <!-- Bootstrap core CSS -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/style.css" rel="stylesheet">
	<style>
		div[id ^= "weekd"] {
			opacity: 0.6; 
		}
		#weekd<?echo $weekday;?> {
			opacity: 1;
		}
		.w<?echo $weekn;?> {
			opacity: 0.4;
		}
		#groop {
			-moz-user-select: -moz-none;
			-o-user-select: none;
			-khtml-user-select: none;
			-webkit-user-select: none;
			user-select: none;     
		}
	</style>
	<script type="text/javascript">
		var groop= <?if($curgr) echo "\"gr=".$curgr."\""; else echo "\"\"";?>;
		var weekday = <?echo $weekday;?>;
	</script>
    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
	<!-- Yandex.Metrika counter -->
	<script type="text/javascript">
		(function (d, w, c) {
			(w[c] = w[c] || []).push(function() {
				try {
					w.yaCounter35113450 = new Ya.Metrika({
						id:35113450,
						clickmap:true,
						trackLinks:true,
						accurateTrackBounce:true
					});
				} catch(e) { }
			});

			var n = d.getElementsByTagName("script")[0],
				s = d.createElement("script"),
				f = function () { n.parentNode.insertBefore(s, n); };
			s.type = "text/javascript";
			s.async = true;
			s.src = "https://mc.yandex.ru/metrika/watch.js";

			if (w.opera == "[object Opera]") {
				d.addEventListener("DOMContentLoaded", f, false);
			} else { f(); }
		})(document, window, "yandex_metrika_callbacks");
	</script>
	<noscript><div><img src="https://mc.yandex.ru/watch/35113450" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
	<!-- /Yandex.Metrika counter -->
	<script src='https://www.google.com/recaptcha/api.js'></script>
  </head>

  <body>

    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/">RaspKIT</a>
        </div>
        <div class="collapse navbar-collapse">
			<ul class="nav navbar-nav">
				<!--li><a href="">Главная</a></li-->
			</ul>
			<form action="<?echo $_SERVER['PHP_SELF'];?>" method="post" class="navbar-form navbar-right" role="form">
			<?
			if($user_auth) {
			?>
				<input name="do_logout" type="submit" id="logout" class="btn btn-success" value="Выход"> 
			<?
			}
			else { // ВХОД
			?>
				<div class="form-group">
					<input id="logname" class="form-control" placeholder="Логин" name="login" type="text" maxlength="30"  required="required" autocomplete="off" value="<?echo @$_POST['login'];?>">
				</div>
				<div class="form-group">
					<input id="logpass" class="form-control" placeholder="Пароль" name="pass" type="password" maxlength="15"  required="required" autocomplete="off" value="">
				</div>
				<input name="do_signup" type="submit" id="logsubmit" class="btn btn-success" value="Войти">
				<a href="/?reg" class="btn btn-success" role="button">Регистрация</a>
			<?
			}
			?>
			</form>
			<?
			if($user_auth) {
			?>
			<ul class="nav navbar-nav navbar-right">
				<li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?echo $user_login;?><b class="caret"></b></a>
				  <ul class="dropdown-menu">
					<li><a href="/?profile">Профиль</a></li>
					<li class="divider"></li>
					<li><a href="/?settings">Настройки</a></li>
					<li class="divider"></li>
					<li><a href="/?do_logout">Выход</a></li>
				  </ul>
				</li>
			</ul>
			<?
			}
			?>
        </div><!--/.nav-collapse -->
      </div>
    </div>
    <div id="main" class="container">
		<div class="row">
			<div id="rasp" class="col-xs-10 col-xs-offset-1 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4">
			<?
			if ($msg_show) { // СООБЩЕНИЕ
			?>			
				<div class="alert <?echo $msg_type;?> alert-dismissable" style="margin-top: 5px;">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
					<?echo $msg_str;?>
				</div>
			<?
			}
			if(isset($_GET["reg"])) { // РЕГИСТРАЦИЯ
			?>
				<h1>Регистрация</h1>			
				<form id="regform" action="<?echo $_SERVER['PHP_SELF'];?>" method="post" onsubmit="return formRegValidate();" role="form">
					<div class="form-group has-feedback">
						<label for="reglogin">Логин</label>
						<input id="reglogin" class="form-control" placeholder="Логин" name="login" type="text" maxlength="30" required="required" autocomplete="off" onblur="checkName(this)" value="<?echo @$_POST['login'];?>">
						<span class="glyphicon form-control-feedback"></span>
					</div>
					<div class="form-group has-feedback">
						<label for="regemail">E-mail</label>
						<input id="regemail" class="form-control" placeholder="E-mail" name="email" type="text" maxlength="30"  required="required" autocomplete="off" onblur="checkEmail(this)" value="<?echo @$_POST['email'];?>">
						<span class="glyphicon form-control-feedback"></span>
					</div>
					<div class="form-group has-feedback">
						<label for="regpass1">Пароль</label>
						<input id="regpass1" class="form-control" placeholder="Пароль" name="pass1" type="password" maxlength="15"  required="required" autocomplete="off" onblur="checkPass1(this)"  value="">
						<span class="glyphicon form-control-feedback"></span>
					</div>
					<div class="form-group has-feedback">
						<label for="regpass2">Подтверждение пароля:</label>
						<input id="regpass2" class="form-control" placeholder="Подтверждение пароля" name="pass2" type="password" maxlength="15"  required="required" autocomplete="off" onblur="checkPass2(this)"  value="">
						<span class="glyphicon form-control-feedback"></span>
					</div>
					<div class="g-recaptcha" data-sitekey="6LfnviMUAAAAAFeZ7siT1A5pKJHV0SJM4wBEcUe_"></div>
					<input name="do_reg" type="submit" id="regsubmit" class="btn btn-success" value="Регистрация">							
				</form>
			<?
			} elseif(isset($_GET["profile"]) && $user_auth) { // ПРОФИЛЬ
			?>
				<h1>Профиль</h1>
				<!--div class="alert alert-danger">
					<strong>Сообщение.</strong> Эта форма пока не работает.
				</div-->				
				<form id="upform" action="<?echo $_SERVER['PHP_SELF'];?>" method="post" role="form">
					<div class="form-group has-feedback">
						<label for="uplogin">Логин</label>
						<input id="uplogin" class="form-control" placeholder="Логин" name="login" type="text" maxlength="30" autocomplete="off" value="<?echo $user_login;?>" disabled>
					</div>
					<div class="form-group has-feedback">
						<label for="upemail">E-mail
						<?
						if($user_email_valid) {
							echo "<span style='color: green'> Подтвержден</span>";
						}
						else {
							echo "<span style='color: red'> Не подтвержден</span>";
						}						
						?></label>
						<input id="upemail" class="form-control" placeholder="E-mail" name="email" type="text" maxlength="30" autocomplete="off" onblur="checkEmail(this)" value="<?echo $user_email;?>">
						<span class="glyphicon form-control-feedback"></span>
						<? if(!$user_email_valid) {
							echo "<a href='valid.php?send_valid_msg' class='btn btn-warning btn-sm' style='margin-top: 5px'>Отправить Email для подтверждения</a>";
						}
						?>
					</div>
					<div class="form-group has-feedback">
						<label for="uppass1">Новый пароль</label>
						<input id="uppass1" class="form-control" placeholder="Новый пароль" name="pass1" type="password" maxlength="15" autocomplete="off" value=""><!--onblur="checkPass1(this)"-->
						<span class="glyphicon form-control-feedback"></span>
					</div>
					<div class="form-group has-feedback">
						<label for="uppass2">Подтверждение нового пароля:</label>
						<input id="uppass2" class="form-control" placeholder="Подтверждение нового пароля" name="pass2" type="password" maxlength="15" autocomplete="off" onblur="checkUpPass2(this)"  value="">
						<span class="glyphicon form-control-feedback"></span>
					</div>
					<hr>
					<div class="form-group has-feedback">
						<label for="uppass">Текущий пароль:</label>
						<input id="uppass" class="form-control" placeholder="Текущий пароль" name="pass" type="password" maxlength="15" autocomplete="off" value="">
					</div>
					<input name="do_update" type="submit" id="upsubmit" class="btn btn-success" value="Сохранить">								
				</form>
			<?
			} elseif(isset($_GET["settings"]) && $user_auth) { // НАСТРОЙКИ
				$arrgr = (array)json_decode(file_get_contents("gr/allgr"));
				//var_dump($arrgr);
			?>
				<h1>Настройки</h1>
				<!--div class="alert alert-danger">
					<strong>Сообщение.</strong> Эта форма пока не работает.
				</div-->				
				<form id="upform" action="<?echo $_SERVER['PHP_SELF'];?>" method="post" role="form">
					<div class="form-group has-feedback">
						<label for="uplogin">Группа</label>
						<?/*<input id="upgr" class="form-control" placeholder="Группа" name="gr" type="text" maxlength="30" autocomplete="off" value="<?echo ($user_gr ? $user_gr : "");?>">*/?>
						<select class="form-control" name="gr">
							<option value="0">-</option>
							<?
							foreach ($arrgr as $ngr) {
								if($ngr == $user_gr)
									echo "<option selected value='$ngr'>$ngr</option>";
								else
									echo "<option value='$ngr'>$ngr</option>";
							}
							?>
						</select>
					</div>
					<div class="checkbox">
						<label><input type="checkbox" name="alert" <?echo $user_alert?"checked":"";?>> Получать сообщения на Email о заменах</label>
					</div>
					<!--div class="alert alert-danger">
						<strong>Внимание.</strong> Оповещения о заменах пока не работают.
					</div-->	
					<input name="do_setting" type="submit" id="upsubmit" class="btn btn-success" value="Сохранить">								
				</form>
			<?
			} elseif(isset($_GET["contact"])) {
			?>
				<h1>Контакты</h1>
				<h3><span class="glyphicon glyphicon-envelope"></span> Email: info@raspkit.ru</h3>
				<p>По всем вопросам можно обращаться на Email</p>
			<?
			}
			else {
				$repl = ""; // Сюда собираем замены
				$page = ""; // Сюда собираем расписание

				$w = array("//четная", "нечетная//");
				$week = $w[$weekn];
				if ($curgr) // Если есть запрос группы ищем группу в заменах
				{
					echo "<h3 id='groop'>".$curgr.' гр. '.$week.' неделя</h3>';
					include 'replace.php';
				}
				if($repl)
					echo $repl;
				// инклудим расписание из файла
				$file = "";
				if($curgr) $file = $curgr;
				else $file = "main";
				$filename = "./gr/$file";
				if(file_exists($filename))
					include $filename;
				else {
					$fp = @fopen("./gr/allgr", "r");
					$arrgr = json_decode(fread($fp, filesize("./gr/allgr")));
					if(array_search($file,$arrgr))
						echo "";
					else
						header('Location: index.php');
				}
				if ($file == "main") {
					echo "";
				}
			}
			?>
			</div>
		</div>
		<hr>
		<footer>
			<p><a href="\?contact">Контакты</a> © RaspKIT 2017</p>
		</footer>
    </div><!-- /.container -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <!--script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script-->
	<script type="text/javascript" src="jquery-3.1.0.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="app.js"></script>
  </body>
</html>