<?php 
	include_once 'simple_html_dom.php';
	$replace = false; // Изначально считаем что замен нет
	if($file = @file_get_contents("http://5.19.239.65/xi/replacements/load.aspx")) {
		$html = str_get_html($file);
		if($html->innertext!='' and count($html->find('tr td'))) {
			foreach($html->find('.section') as $a) { // находим все td в tr c классом section
				if($a->plaintext == $curgr) { // если их содержимое совпадает с запрошеной группой значит замена есть
					$replace = true;
					break;
				}
			}
			if($replace) // если есть замена парсим ее
			{
				$countch = 0;
				$check = false; // 
				$num = 0;
				$repl .= "<table class='table table-hover table-bordered'>";
				$home = 5;
				$center = $html->find('center', 0);
				//$repl .= $center->plaintext;
				foreach($center->find('td') as $a) { // получаем все теги td
					if($a->class == "header") // если класс тега header выводим его содержимое заголовком
						$repl .= "<h4>".$a->plaintext."</h4>";
					if($a->class == "footer")
						$check = false;
					if($a->class == "section") { // если класс тега section
						if($a->plaintext == $curgr) // и его содержимое номер запрошенной группы 
							$check = true; // значит все последющие теги отностятня к этой группе
						else $check = false;
					}
					else if($check) { // если теги относятся к запрошеной группе выводим их
						$countch++;
						if($countch < 6) continue; // еще более эпичный костыль
						$num++; // эпичный костыль
						if($num == 1) $repl .= "<tr>";
						
						//echo $a->outertext."<br>";
						$repl .= $a->outertext;
						if($a->plaintext == "Дома" || $a->plaintext == "дома" || $a->plaintext == "Домой") $home = 4;
						if($num == $home) {
							$repl .= "</tr>";
							$num = 0; 
							$home = 5;
						}						
					}
				}
				$repl .= "</table>";
			}
		}
		$html->clear();
	}
	else {
		$repl = false;
	}
?>