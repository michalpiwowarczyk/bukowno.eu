<?php

$rss_count = 40;
include("rss/FeedWriter.php");
require "dane.php";
$webslice_create = FALSE;
if(isset($_REQUEST['webslice']))
	$webslice_create = TRUE;

$s_tekst = '{S_DATA}<BR /><strong>{S_OPIS}</strong>{S_GPS}';
$s_gps = '<BR />{S_SKITURA}{S_FERRATA} dystans: {S_DYSTANS}, przewyższenie: {S_PRZEWYZSZENIE}, czas: {S_CZAS}';

$s_tekst_ws = '{S_DATA} <strong>{S_OPIS}</strong>{S_GPS}';
$s_gps_ws = ' {S_SKITURA}{S_FERRATA} dystans: {S_DYSTANS}, przewyższenie: {S_PRZEWYZSZENIE}, czas: {S_CZAS}';


function CompareWycieczki($value1, $value2) {
	$v1 = str_replace("_","",$value1);
	$v2 = str_replace("_","",$value2);
	return -strcmp($v1,$v2);
}

uksort($wycieczki, 'CompareWycieczki');

if ( $webslice_create == FALSE ) {
	$TestFeed = new FeedWriter(RSS2);
	$channel = 'beskidy.bukowno.eu';
	$link = 'http://beskidy.bukowno.eu/mpw/beskidy.bukowno.eu/index.html?galeria';
	$TestFeed->setTitle($channel);
	$TestFeed->setLink($link);
	$TestFeed->setDescription('RSS 2.0 for beskidy.bukowno.eu');
	$TestFeed->setImage($channel,$link,$link.'http://beskidy.bukowno.eu/mpw/beskidy.bukowno.eu/res/gif.gif');
	$k=0;
	while( list($klucz, $wycieczka) = each($wycieczki)  and  $k<$rss_count) {
		$jest_gps = 0;
		if(isset($wycieczka['dystans'])) {
			$s_dystans = $wycieczka['dystans']."km";
			++ $jest_gps;
		}
		else
			$s_dystans = '';
		if(isset($wycieczka['przewyzszenie'])) {
			$s_przewyzszenie = $wycieczka['przewyzszenie']."m";
			++ $jest_gps;
		}		
		else
			$s_przewyzszenie = '';
		if(isset($wycieczka['godz'])) {
			$s_czas = $wycieczka['godz'].'h';
			++ $jest_gps;
		}		
		else
			$s_czas = '';
		if(isset($wycieczka['minut'])) {
			if($wycieczka['minut']>0) {
				if($wycieczka['minut']<10)
					$s_czas .= "0";
				$s_czas .= $wycieczka['minut']."'";
			}
		}
		
		$s_tekst_work = $s_tekst;
		$s_gps_work = $s_gps;
		$s_data_wycieczki = str_replace("_", "", $klucz);
		$s_tekst_work = str_replace ( '{S_DATA}', $s_data_wycieczki, $s_tekst_work );
		$s_tekst_work = str_replace ( '{S_OPIS}', $wycieczka['opis'], $s_tekst_work );

		$s_skitura = "";
		if(isset($wycieczka['skitura']))
			if($wycieczka['skitura']=="tak")
				$s_skitura = "<i>Skitura</i><BR />";
		$s_gps_work = str_replace ( '{S_SKITURA}', $s_skitura, $s_gps_work );

		$s_ferrata = "";
		if(isset($wycieczka['ferrata']))
			if($wycieczka['ferrata']=="tak")
				$s_ferrata = "<i>Ferrata</i><BR />";
		$s_gps_work = str_replace ( '{S_FERRATA}', $s_ferrata, $s_gps_work );

		$s_gps_work = str_replace ( '{S_DYSTANS}', $s_dystans, $s_gps_work );
		$s_gps_work = str_replace ( '{S_PRZEWYZSZENIE}', $s_przewyzszenie, $s_gps_work );
		$s_gps_work = str_replace ( '{S_CZAS}', $s_czas, $s_gps_work );
		
		if ($jest_gps == 3)
			$s_tekst_work = str_replace ( '{S_GPS}', $s_gps_work, $s_tekst_work );
		else
			$s_tekst_work = str_replace ( '{S_GPS}', "", $s_tekst_work );

		$newItem = $TestFeed->createNewItem();
		$newItem->setTitle($s_data_wycieczki." ".$wycieczka['opis']);
		$newItem->setLink($wycieczka['link']);
		$dzien = str_replace("_","",$klucz);
		$data = date('D, d M Y H:i:s O',mktime (9, 0, 0, (int)substr($dzien,5,2), (int)substr($dzien,8,2), (int)substr($dzien,0,4)));
		$newItem->setDate($data);
		$newItem->setDescription($s_tekst_work);

		$TestFeed->addItem($newItem);
		++$k;
	}
	$TestFeed->genarateFeed();
}


if ( $webslice_create == TRUE ) {
	$s_ws = '';
	$k=0;
	while( list($klucz, $wycieczka) = each($wycieczki)  and  $k<$rss_count) {
		$jest_gps = 0;
		if(isset($wycieczka['dystans'])) {
			$s_dystans = $wycieczka['dystans']."km";
			++ $jest_gps;
		}
		else
			$s_dystans = '';
		if(isset($wycieczka['przewyzszenie'])) {
			$s_przewyzszenie = $wycieczka['przewyzszenie']."m";
			++ $jest_gps;
		}		
		else
			$s_przewyzszenie = '';
		if(isset($wycieczka['godz'])) {
			$s_czas = $wycieczka['godz'].'h';
			++ $jest_gps;
		}		
		else
			$s_czas = '';
		if(isset($wycieczka['minut'])) {
			if($wycieczka['minut']>0) {
				if($wycieczka['minut']<10)
					$s_czas .= "0";
				$s_czas .= $wycieczka['minut']."'";
			}
		}
		
		$s_tekst_work = $s_tekst_ws;
		$s_gps_work = $s_gps_ws;
		$s_data_wycieczki = str_replace("_", "", $klucz);
		$s_tekst_work = str_replace ( '{S_DATA}', $s_data_wycieczki, $s_tekst_work );
		$s_tekst_work = str_replace ( '{S_OPIS}', $wycieczka['opis'], $s_tekst_work );

		$s_skitura = "";
		if(isset($wycieczka['skitura']))
			if($wycieczka['skitura']=="tak")
				$s_skitura = "<i>Skitura</i> ";
		$s_gps_work = str_replace ( '{S_SKITURA}', $s_skitura, $s_gps_work );

		$s_ferrata = "";
		if(isset($wycieczka['ferrata']))
			if($wycieczka['ferrata']=="tak")
				$s_ferrata = "<i>Ferrata</i> ";
		$s_gps_work = str_replace ( '{S_FERRATA}', $s_ferrata, $s_gps_work );

		$s_gps_work = str_replace ( '{S_DYSTANS}', $s_dystans, $s_gps_work );
		$s_gps_work = str_replace ( '{S_PRZEWYZSZENIE}', $s_przewyzszenie, $s_gps_work );
		$s_gps_work = str_replace ( '{S_CZAS}', $s_czas, $s_gps_work );
		
		if ($jest_gps == 3)
			$s_tekst_work = str_replace ( '{S_GPS}', $s_gps_work, $s_tekst_work );
		else
			$s_tekst_work = str_replace ( '{S_GPS}', "", $s_tekst_work );

		$dzien = str_replace("_","",$klucz);
		$data = date('D, d M Y H:i:s O',mktime (9, 0, 0, (int)substr($dzien,5,2), (int)substr($dzien,8,2), (int)substr($dzien,0,4)));
			
	    $webslice = '<div style="display:none;" class="entry-title">'.$s_data_wycieczki." ".$wycieczka['opis'].'</div><p class="entry-content" style="margin:0px;padding:0px;"><a href="'.$wycieczka['link'].'" style="font-family:Tahoma,Verdana,Arial;font-size:11px;margin:0px;padding:0px;text-decoration:none;">'.$s_tekst_work.'</a></p>';
		++$k;
		$s_ws .= $webslice;
	}
	
	$tmp = file_get_contents("webslice.txt");
	$tmp = str_replace ( '{S_WEBSLICE}', $s_ws, $tmp );
	echo $tmp;
}
   
?>
