// Файл служит для предобработки названий предметов
// и пособработки обновления расписания

var repl = {
	"Математические методы": "Мат.методы",
	"Английский язык": "Англ.яз.",
	"МДК03.02. Инструментальные средства разработки ПО": "HTML",
	"Основы экономики": "Осн.экономики",
	"МДК02.02. Технология разработки и защиты БД": "Базы данных",
	"МДК01.01. Системное программирование": "Сист.Прог",
	"Русский язык и культура речи": "Русский язык",
	"МДК01.02. Прикладное программирование": "Прикл.прог",
	"МДК02.01. Инфокоммникационные системы и сети": "Сети",
	"МДК02.02-1Технология разработки и защиты баз данных": "Базы данных",
	"Информационная безопасность": "ИБ",
	"МДК03.01. Технолгия разработки ПО": "ТРПО",
	"(Мартинес М.Н.:1)": "",
	"(Ружицкая Т.В.:2)": "",
	"Физическая Культура": "Физ-ра",
	"Элементы математической логики": "Мат.логика",
	"Технические средства информации": "ТСИ",
	"Высшая математика": "Выш.мат.",
	"МДК04. Ввод иобработка цифровой информации": "МДК04",
	"Информационные технологии": "ИТ",
	"УП.04-1 Ввод и обработка йифровой информации": "УП-1",
	"УП.04 Ввод и обработка цифровой информации": "УП",
	"Теория алгоритмов": "Алгоритмы",
	"Операционные системы": "ОС",
	"Основы программирования": "Осн.прог",
	"Безопасность жизнедеятельности": "БЖ",
	"Архитектура компьютерных систем": "Архитектура",
	"Теория вероятности и статистика": "Теор.вер",
	"Учебная практика 01-1": "УП-Сист.Прог-1",
	"Учебная практика 01-2": "УП-Сист.Прог-2",
	"Учебная практика 01-3": "УП-Прога-3",
	"Учебная практика 01-4": "УП-Прога-4",
	"Учебная практика 02-1": "УП-БД-1",
	"Учебная практика 02-2": "УП-БД-2",
	"Учебная практика 02-3": "УП-БД-3",
	"Учебная практика 02-4": "УП-БД-4",
	"Учебная практика 03-1": "УП-ТРПО-1",
	"Учебная практика 03-2": "УП-ТРПО-2",
	"Учебная практика 03-4": "УП-HTML-4",
	"Учебная практика 03-3": "УП-HTML-3",
	"Средства создания Интернет приложений": "HTML-JS",
	"МДК03.03. Документирование и сертификация": "Документация",
	"Пакеты прикладных экономических программ": "1С: Бух",
	"Правовое обеспечение профессиональной деятельности": "Право",
	"Осн.экономики предприятия": "Осн.экономики+"
}
var timeview = false;
var time = ["","09:00 - ","10:55 - ","13:00 - ","14:45 - ","16:25 - "];
// Замена названий предметов
function releaseAll(){
	var html = $("#main").html();
	for(var i in repl) {
		
		var reg = new RegExp(i, "g");
		html = html.replace(reg, repl[i]);
		
	}	
	$("#main").html(html);	
}
// Замена номеров пар на время
function NumTime() {
	if(timeview) {
		TimeToNum();
		timeview = false;
	}
	else {
		NumToTime();		
		timeview = true;
	}
}

function NumToTime(){
	var html = $("#main").html();
	for(var i = 1; i < 6; i++) {
		var str = i+"\\) "; 
		var reg = new RegExp(str, "g");
		html = html.replace(reg, time[i]);
	}
	$("#main").html(html);		
}
function TimeToNum(){
	var html = $("#main").html();
	for(var i = 1; i < 6; i++) {
		var str = i+") "; 
		var reg = new RegExp(time[i], "g");
		html = html.replace(reg, str);
	}
	$("#main").html(html);		
}
// Расстановка дней недели так чтоб первым стоял самый ближний день
function removeWD(weekd){
	if(weekd < 1 || weekd > 6) return;
	var arr = {};
	for(var i = 1; i <= 6; i++) {
		arr[i] = $("#weekd"+i);
		$("#weekd"+i).remove();
	}

	for(var i = weekd; i <= 6; i++) 
		$("#rasp").append(arr[i]);

	for(var i = 1; i < weekd; i++)
		$("#rasp").append(arr[i]);
}

var isMobile = {
    Android: function() {
        return navigator.userAgent.match(/Android/i);
    },
    BlackBerry: function() {
        return navigator.userAgent.match(/BlackBerry/i);
    },
    iOS: function() {
        return navigator.userAgent.match(/iPhone|iPad|iPod/i);
    },
    Opera: function() {
        return navigator.userAgent.match(/Opera Mini/i);
    },
    Windows: function() {
        return navigator.userAgent.match(/IEMobile/i);
    },
    any: function() {
        return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
    }
};
// Провека логина
function checkName(obj){
	chfl1=false;
	if(typeof obj == 'undefined') obj = document.getElementById('reglogin');
	var id = obj.id;
	if (!obj.value.match(/^[а-яА-Яa-zA-Z0-9]{3,15}$/)){
		obj.setCustomValidity('Такой логин не подходит.');
		$(obj.parentNode).addClass('has-error').removeClass('has-success');
		$(obj.parentNode.querySelector(".form-control-feedback")).addClass('glyphicon-remove').removeClass('glyphicon-ok');
	}else {
		chfl1=true;
		obj.setCustomValidity('');
		$(obj.parentNode).addClass('has-success').removeClass('has-error');
		$(obj.parentNode.querySelector(".form-control-feedback")).addClass('glyphicon-ok').removeClass('glyphicon-remove');		
	}
	checkLogin(obj.value);
	return chfl1;
}
// Проверка логина на доступность
function checkLogin(str) {
	/*$.ajax({
	type: "POST",
	url: "checkMail.php",
	data: "mail="+str,
	success: function(msg){
			if(!msg)
				$('#regemail_stat').html('E-mail занят другим пользователем').fadeIn().parent().addClass('incorrect').removeClass('correct');
			}
	});*/
}
// Проверка пароля 1
function checkPass1(p){
	chfl2 = false;
	passOK = false;
	if(!p) p = document.getElementById('regpass1');	
	var sign='';
	if (p.value.match(/^(.)\\1*$/)){sign='Пароль должен содержать различные символы';}
	else if (p.value.length>15){sign='Максимальная длина пароля 15 символов';}
	else if (p.value.length<6){sign='Минимальная длина пароля 6 символов';}
	else if (p.value.match(/[^a-zA-Z0-9\\-_]/)){sign='В пароле присутствуют недопустимые символы';}
	else if (p.value.match(/^[0-9]+$/)){sign='Слишком простой пароль';}
	else {passOK=true;}
	var id = p.id;
	if (!passOK){
		p.setCustomValidity(sign);
		$(p.parentNode).addClass('has-error').removeClass('has-success');
		$(p.parentNode.querySelector(".form-control-feedback")).addClass('glyphicon-remove').removeClass('glyphicon-ok');
	}else {
		chfl2 = true;
		p.setCustomValidity('');
		$(p.parentNode).addClass('has-success').removeClass('has-error');
		$(p.parentNode.querySelector(".form-control-feedback")).addClass('glyphicon-ok').removeClass('glyphicon-remove');
	}
	return chfl2;
}
// Проверка пароля 2 на совпадение
function checkPass2(){
	passOK = false;
	p1 = document.getElementById('regpass1');
	p2 = document.getElementById('regpass2');
	var sign='';
	if (p1.value != p2.value){sign='Пароли не совпадают.';}
	else {passOK=true;}
	if (!passOK){
		p2.setCustomValidity("Пароли не совпадают");
		$(p2.parentNode).addClass('has-error').removeClass('has-success');
		$(p2.parentNode.querySelector(".form-control-feedback")).addClass('glyphicon-remove').removeClass('glyphicon-ok');
	}else {
		chfl2 = true;
		p2.setCustomValidity('');
		$(p2.parentNode).addClass('has-success').removeClass('has-error');
		$(p2.parentNode.querySelector(".form-control-feedback")).addClass('glyphicon-ok').removeClass('glyphicon-remove');
	}
	return chfl2;
}
// Проверка up пароля 2 на совпадение
function checkUpPass2(){
	passOK = false;
	p1 = document.getElementById('uppass1');
	p2 = document.getElementById('uppass2');
	var sign='';
	if (p1.value != p2.value){sign='Пароли не совпадают.';}
	else {passOK=true;}
	if (!passOK){
		p2.setCustomValidity("Пароли не совпадают");
		$(p2.parentNode).addClass('has-error').removeClass('has-success');
		$(p2.parentNode.querySelector(".form-control-feedback")).addClass('glyphicon-remove').removeClass('glyphicon-ok');
	}else {
		chfl2 = true;
		p2.setCustomValidity('');
		$(p2.parentNode).addClass('has-success').removeClass('has-error');
		$(p2.parentNode.querySelector(".form-control-feedback")).addClass('glyphicon-ok').removeClass('glyphicon-remove');
	}
	return chfl2;
}
// Проверка E-mail
function checkEmail(obj){
	chfl1=false;
	if(typeof obj == 'undefined') obj = document.getElementById('regemail');
	var id = obj.id;
	if (!obj.value.match(/^[a-zA-Z0-9_\.\-]+\@[a-zA-Z0-9\.\-]+\.[a-zA-Z0-9]{2,8}$/)){
		obj.setCustomValidity("E-mail указан неправильно");
		$(obj.parentNode).addClass('has-error').removeClass('has-success');
		$(obj.parentNode.querySelector(".form-control-feedback")).addClass('glyphicon-remove').removeClass('glyphicon-ok');
	}else {
		chfl1=true;
		obj.setCustomValidity('');
		$(obj.parentNode).addClass('has-success').removeClass('has-error');
		$(obj.parentNode.querySelector(".form-control-feedback")).addClass('glyphicon-ok').removeClass('glyphicon-remove');	
	}
	return chfl1;
}
// Проверка всей формы
function formRegValidate(){
	return checkEmail() && checkPass1() && checkPass2() && checkName();
}

function main() //главная функция
{
	releaseAll();
	removeWD(weekday);
	if(isMobile.any())
		$("body").on("touchstart","#groop",NumTime);	
	else
		$("body").on("mousedown","#groop",NumTime);
}

main();
//$(document).ready(main);

//window.addEventListener('DOMContentLoaded', main);
//window.onload = main();
//window.addEventListener("load", main);