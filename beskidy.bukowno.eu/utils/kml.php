<?php

//=======================================================================
//									
// SYSTEM:		beskidy.bukowno.eu
//									
// AUTOR:		(MPW) Michal Piwowarczyk					
//									
// DATA:		17.06.2009						
//									
//=======================================================================

if (isset($_GET["d"]))
	$s_data=$_GET["d"];
else
	if (isset($argv[1]))
		$s_data=$argv[1];
	else
		die("beskidy.bukowno.eu");

require "dane.php" ;
require "const.php";
require "tar.php";

$data=" ";
if($s_data[0]=="_" and strlen($s_data)==9)
	$data=$s_data[1].$s_data[2].$s_data[3].$s_data[4].".".$s_data[5].$s_data[6].".".$s_data[7].$s_data[8];
if($s_data[0]!="_" and strlen($s_data)==8)
	$data=$s_data[0].$s_data[1].$s_data[2].$s_data[3].".".$s_data[4].$s_data[5].".".$s_data[6].$s_data[7];

if (!isset($wycieczki[$data]))
	die("beskidy.bukowno.eu");
$parametry = $wycieczki[$data];
$data = str_replace('_', '', $data);

// Utworzenie plików KML/Z z archiwum TAR
$s_d=str_replace('.','',$data);
$s_f = $parametry["plik"];
$s_f = str_replace("'",'',$s_f);
$s_f = str_replace(" ",'',$s_f);

$archiwum = "../raporty/KML/KML.tgz";

for ($typ=0; $typ<2; ++$typ) {
	switch ($typ) {
		case 0: $s_m = "kmz"; break;
		case 1: $s_m = "kml"; break;
	}
	$plik = "KML".$s_f.$s_d.".";
	if($s_m=="kmz")
		$plik .= "kmz";
	else
		$plik .= "kml";
	$tmpfname = "../".$thumb_dir.$plik;
	$lokacje[$typ] = $tmpfname;
	$ok=true;
	if (!file_exists($tmpfname))
		$ok=false;
	else {
		if (filemtime($tmpfname)<filemtime($archiwum))
			$ok=false;
	}	
	//$ok=false;
	if (!$ok) {	
		$tar_object = new Archive_Tar($archiwum,true);		
		$handle = fopen($tmpfname, "w");
		if($s_m=="kmz")
			fwrite($handle, $tar_object->extractInString($s_f."_track-".$s_d.".kmz"));
		else {			
			$link = $parametry["link"];
			$arch = str_replace("../index.php?d=galeria/","../galeria/",$link)."/galeria.tar";
			$id = fileinode($arch);
			$thumby = Array();
			$tar_object2 = new Archive_Tar($arch);
			if (($v_list=$tar_object2->listContent()) != 0) {
				for ($i=0; $i<sizeof($v_list); $i++) {
					$thumby[$i] = $v_list[$i]['filename'];
				}
			}

			$s1 = '<description>&lt;img src="images/'.strtolower($s_f).'_'.$s_d.'_';
			$s3 = '<description>&lt;img src="images/'.$s_d.'-'.strtolower($s_f).'_';
			$s2 = 'images/thumb_'.strtolower($s_f).'_'.$s_d.'_';
			$s2_2014 = 'images2014/thumb_'.strtolower($s_f).'_'.$s_d.'_';
			$s2_2015 = 'images2015/thumb_'.strtolower($s_f).'_'.$s_d.'_';
			
			$kml_orig = $tar_object->extractInString($s_f."-".$s_d.".kml");
			$kml = $kml_orig;
			$kml = str_replace('_jpg.jpeg">&lt;br/></description>','.jpg">&lt;br/></description>',$kml);
			$kml = str_replace('_jpg.jpeg</href>','.jpg</href>',$kml);			
			
			if($_SERVER['HTTP_HOST']=='pyton.sl.mofnet.gov.pl/' or $_SERVER['SERVER_ADDR']=="10.5.0.240") {
				$kml = str_replace($s1,'<description>&lt;img src="/beskidy/tmp/thumbnails/'.$id,$kml);
				$kml = str_replace($s2,'/beskidy/tmp/thumbnails/'.$id,$kml);
				$kml = str_replace($s2_2014,'/beskidy/tmp/thumbnails/'.$id,$kml);
				$kml = str_replace($s2_2015,'/beskidy/tmp/thumbnails/'.$id,$kml);
			} else {
				$kml = str_replace($s1,'<description>&lt;img src="/tmp/thumbnails/'.$id,$kml);
				$kml = str_replace($s3,'<description>&lt;img src="/tmp/thumbnails/'.$id,$kml);
				$kml = str_replace($s2,'/tmp/thumbnails/'.$id,$kml);
				$kml = str_replace($s2_2014,'/tmp/thumbnails/'.$id,$kml);
				$kml = str_replace($s2_2015,'/tmp/thumbnails/'.$id,$kml);
			}
			
			for($i=0;$i<count($thumby);++$i) {
				$kml = str_replace(strtolower($thumby[$i]),$thumby[$i],$kml);
			}
			fwrite($handle, $kml);
		}
		fclose($handle);
	}
}

// Przygotowanie strony z mapą google
$tmp = file_get_contents("../template/kml.txt");
$APIkey='AIzaSyCObw-6lSSQuIvZeB5OT3YyZ3uDyVvXvFM';

$tmp = str_replace('{KML_LATITUDE}', $parametry["latitude"], $tmp);
$tmp = str_replace('{KML_LONGITUDE}', $parametry["longitude"], $tmp);
$tmp = str_replace('{KML_DATA_SHORT}', str_replace('.','',$data), $tmp);
$tmp = str_replace('{API_KEY}', $APIkey, $tmp);
$tmp = str_replace('{KML_DATA}', $data, $tmp);
$tmp = str_replace('{KML_OPIS}', $parametry["opis"], $tmp);
$tmp = str_replace('{S_LOKACJA_KMZ}', $lokacje[0], $tmp);
$tmp = str_replace('{S_LOKACJA_KML}', $lokacje[1], $tmp);
$tmp = str_replace('{S_RAND}', rand(), $tmp);

$s = $parametry["plik"];
$s = str_replace("'",'',$s);
$s = str_replace(" ",'',$s);
$tmp = str_replace('{KML_PLIK}', $s, $tmp);

$tmp = str_replace('{KML_SIZE}', $parametry["size"], $tmp);
$tmp = str_replace('{KML_MAP}', $parametry["map"], $tmp);

$tmp = str_replace('{S_URL}', "http://".$_SERVER['HTTP_HOST'].str_replace("kml.php","",$_SERVER['SCRIPT_NAME']), $tmp);

$slowakluczowe = file_get_contents("../template/slowakluczowe.txt");
$meta_slowakluczowe = str_replace(" ",",",str_replace("\n"," ",str_replace("\r","",php_strip_whitespace("../template/slowakluczowe.txt"))));
$tmp = str_replace ( '{SLOWA_KLUCZOWE}', $slowakluczowe, $tmp );
$tmp = str_replace ( '{META_SLOWA_KLUCZOWE}', $meta_slowakluczowe, $tmp );

echo $tmp ;
?>