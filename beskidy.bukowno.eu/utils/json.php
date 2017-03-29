<?php

require "const.php";
require "dane.php";
require "functions.php";
require "tar.php";

if (isset($_REQUEST["j"]))
	$mode = $_REQUEST["j"];
else
	$mode = "";

$responseJson = array();

switch($mode) {
	// Aktualnosci
	case 'a':
		$responseJson = getAktualnosci($wycieczki);
		break;
	// Lista lat
	case 'l':
		$responseJson = getLataDlaZestawienia($wycieczki);
		break;
	// Zestawienie
	case 'z':
		$isJson=true;
		require "zestawienie.php";
		break;
	// wyszukanie katalogu z galeria w/g podanej daty (w formacie YYYYMMDD
	case 'd':
		$d = $_REQUEST["d"];
		$data = substr($d,0,4).".".substr($d,4,2).".".substr($d,6,2);
		$responseJson = getGaleriaAtDate($wycieczki,$data);
		break;
	// wylosowanie zdjęcia dla tła strony (background) lub pokazu losowych slajdów
	case 'b':
		$responseJson = getRandomizeImage();
		$photo = $responseJson['r'].$responseJson['dir']."/".$responseJson['img'];
		$file_parts = pathinfo($photo); 
		if(is_tar($file_parts["dirname"])) {
			$tmpfname = extract_from_tar($file_parts["dirname"],$file_parts["basename"],true);
			$fp = fopen($tmpfname, 'rb');
			header("Content-Type: image/jpeg");
			header("Content-Length: " . filesize($tmpfname));
			fpassthru($fp);
			fclose($fp);
			exit(0);
		}
		break;		
	// ostatnia aktualizacja
	case 'o':
		$b_chdir = chdir("../../beskidy.bukowno.eu");
		$responseJson = array( 'data' => ostatniaAktualizacja() );
		break;
	// wylosowanie zdjęcia
	case 'r':
		$responseJson = getRandomizeImage();
		$photo = $responseJson['r'].$responseJson['dir']."/".$responseJson['img'];
		$file_parts = pathinfo($photo); 
		if(is_tar($file_parts["dirname"])) {
			$tmpfname = extract_from_tar($file_parts["dirname"],$file_parts["basename"],true);
			$props = getFolderProperties($file_parts["dirname"]);
			$responseJson = array('src' => $tmpfname, 'text' => $props['title']);
		} else {
			exit(0);
		}
		break;		
	// pobranie zdjęcia
	case 'f':
		$f = $_REQUEST["f"];
		if(file_exists($f))	{
			if(isJPG($f)) {
				$fp = fopen($f, 'rb');
				header("Content-Type: image/jpeg");
				header("Content-Length: " . filesize($f));
				fpassthru($fp);
				fclose($fp);
				exit(0);
			}
		}
		break;		
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
echo json_encode($responseJson, JSON_HEX_AMP);

?>
