<?
//
chdir ("/home/v/vitality/raspkit.ru/public_html/");
include 'simple_html_dom.php';
$page = "";
$arrgr = array();
$wd = array("Понедельник","Вторник","Среда","Четверг","Пятница","Суббота");
$file = @file_get_contents("http://www.spbkit.edu.ru/index.php?option=com_timetable&Itemid=82");
if($file) {	
	$html = str_get_html($file);
	$group = "main";
	$kurs = 1;
	$jsongr = "";
	if($html->innertext!='' and count($html->find('div')))		
		foreach($html->find('div[id^=kursi]') as $a) {
			$pagekurs = "";
			if($kurs > 4) break;
			//$pagekurs .= "<div class=\"kurs\">";
			$pagekurs .= "<div class='page-heder'><h2>$kurs курс</h2></div>";
			//$page .= "<ul>";
			$numb = 1;
			$pagegrop = "";
			foreach( $a->find('a[href^=#gruppi]') as $b) {					
				$gr = trim($b->plaintext);
				if($gr) {
					$pagegrop .= "<a href=\"/?gr=$gr\" class=\"btn btn-primary btn-lg nbr\">$gr</a>\n";
					$numb++;
					array_push($arrgr, $gr);
				}	
			}
			if($pagegrop)
				$page .= $pagekurs.$pagegrop;				
			$kurs++;
		}
	if($arrgr) {
		$fg = @fopen("./gr/allgr", "w");			
		fwrite($fg, json_encode($arrgr));
	}
	if($page && $group) {
		echo "$group - ";
		$filename = "./gr/$group";
		$fp = @fopen($filename, "r+");
		if($fp){
			$md5_1 = md5($page);
			$newpage = fread($fp, filesize($filename));
			$md5_2 = md5($newpage);
			//echo $md5_1."<br>".$md5_2;
			if($md5_1 == $md5_2)
				echo "Обновления не требуется<br>\n";//"Обновления не требуется";
			else {
				//echo "Требуется обновление";
				rewind($fp);
				ftruncate($fp, 0);
				if(fwrite($fp, $page)) 
					echo $page;
			}
			fclose($fp);
		}
		else {
			// файл не найден создаем новый файл
			$fp = fopen($filename, "w");
			if(fwrite($fp, $page)) 
					echo $page;
			fclose($fp);
		}
	}
	foreach($arrgr as $num_gr) {
		$page = "";
		$group = "";					
		if($html->innertext!='' and count($html->find('div')))
			foreach($html->find('div[id^=gruppi]') as $a) {
				$id = $a->id;
				foreach( $html->find('a[href$='.$id.']') as $b) {
					$gr = trim($b->plaintext);
					if($gr == $num_gr) {
						$group = $gr;
					}
				}					
				if($group) {
					$wdi = 0;				
					foreach( $a->find('div[id^=dni]') as $с) {
						$page .= "<div id=\"weekd".($wdi+1)."\">";
						$page .= '<h3>'.$wd[$wdi].'</h3>';
						$pair = 1;
						foreach( $с->find('b') as $d) {
							if($pair%2) {
								$arr = explode("//", $d->outertext);
								$newstr = "";
								$numbr = 0;
								if(count($arr) == 1) $numbr = "";
								foreach ($arr as $a) {
									$newstr .= ($numbr ? "//" : "")."<span class=\"w$numbr\">".$a."</span>";
									$numbr = 1;
								}	
								//$newstr = $d->outertext;
								$page .= ceil($pair/2).") ".$newstr."<br>";
							}								
							$pair++;
						}											
						//$page .= $с->innertext;
						$page .= "</div>";
						$wdi++;
					}
					break;
				}				
				$g = false;
			}
		if($page && $group) {
			echo "$group - ";
			$filename = "./gr/$group";
			$fp = @fopen($filename, "r+");
			if($fp){
				$md5_1 = md5($page);
				$newpage = fread($fp, filesize($filename));
				$md5_2 = md5($newpage);
				//echo $md5_1."<br>".$md5_2;
				if($md5_1 == $md5_2)
					echo "Обновления не требуется<br>\n";//"Обновления не требуется";
				else {
					//echo "Требуется обновление";
					rewind($fp);
					ftruncate($fp, 0);
					if(fwrite($fp, $page)) 
						echo $page;
				}
				fclose($fp);
			}
			else {
				// файл не найден создаем новый файл
				$fp = fopen($filename, "w");
				if(fwrite($fp, $page)) 
						echo $page;
				fclose($fp);
			}
		}
	}
	$html->clear(); // подчищаем за собой
}
?>