<?php

require "dane.php";

$rok_min=0;
$rok_max=0;
foreach ( $wycieczki as $klucz => $wycieczka ) {
	$rok = explode (".", $klucz);
	$rok = str_replace ("_", "", $rok[0] );
	if ( $rok < $rok_min or $rok_min == 0 )
		$rok_min = $rok;
	if ( $rok > $rok_max or $rok_max == 0 )
		$rok_max = $rok;
}

$tmp = file_get_contents("../template/zestawienie.txt");
$googlesearch = file_get_contents("../template/googlesearch.html");
$slowakluczowe = file_get_contents("../template/slowakluczowe.txt");
$meta_slowakluczowe = str_replace(" ",",",str_replace("\n"," ",str_replace("\r","",php_strip_whitespace("../template/slowakluczowe.txt"))));

$tmp = str_replace('{ROK_MIN}', $rok_min, $tmp);
$tmp = str_replace('{ROK_MAX}', $rok_max, $tmp);

$s_lata = "";
$s_rok = '<td style="vertical-align:top;">
			<table align="center" class="infotable" cellspacing="0" cellpadding="2">
				<tr><td colspan="2" style="text-align:center;border:solid 1px;">
					<table width="100%">
						<tr><td><a href="../raporty/images/{S_ROK}.jpg" title="{S_ROK} mapa góry"><b>{S_ROK} r.</b></a></td></tr>
						<tr><td>GOT {S_GOT}</td></tr>
						{S_FORMATTED_DYSTANS}
						{S_FORMATTED_PRZEWYZSZENIE}
						{S_FORMATTED_CZAS}
					</table>
				</td></tr>		
				<tr><td colspan="2" style="text-align:center;border:solid 1px;">
					<table width="100%">
						<tr><td><nobr>{S_JESIEN}{S_LATO}{S_WIOSNA}{S_ZIMA}</nobr></td></tr>
					</table>
				</td></tr>
				{S_WIERSZE}
			</table>
		</td>' ;

$s_wiersz = '<tr><td style="text-align:center;{S_BOTTOM}" valign="top" class="smalltxt">&nbsp;{S_DATA}&nbsp;</td><td style="text-align:center;{S_BOTTOM}" class="smalltxt"><a href="{S_LINK}" title="{S_OPIS}">{S_OPIS}</a></td></tr>{S_GPS}';
$s_gps = '<tr><td style="text-align:center;border-bottom:solid 1px;" class="smalltxt"><i style="color:green;">{S_SKITURA}</i><i style="color:red;">{S_FERRATA}</i><i style="color:green;">{S_DOGTREKKING}</i>{S_PROFIL}</td><td style="text-align:center;border-bottom:solid 1px;" class="smalltxt"><nobr>{S_DYSTANS} {S_PRZEWYZSZENIE} {S_CZAS}</nobr></td></tr>';

$s_formatted_dystans = "<tr><td><nobr>Dystans {S_DYSTANS}km</nobr></td></tr>";
$s_formatted_przewyzszenie = "<tr><td><nobr>Podejście {S_PRZEWYZSZENIE}km</nobr></td></tr>";
$s_formatted_czas = "<tr><td><nobr>Łączny czas {S_DNI}d {S_GODZIN}h</nobr></td></tr>";

$jsonLata = Array();

for ( $rok = $rok_max; $rok >= $rok_min; --$rok ) {
	$s_rok_work = $s_rok;
	$dystans = 0;
	$przewyzszenie = 0;
	$czas = 0;
	$got = 0;
	$s_jesien = "";
	$s_wiosna = "";
	$s_lato = "";
	$s_zima = "";
	$s_wiersze = "";
	$n_byla_jesien = 0;
	$n_bylo_lato = 0;
	$n_byla_wiosna = 0;
	$jsonWycieczki = Array();
	foreach ( $wycieczki as $klucz => $wycieczka ) {
		$a_wycieczka = explode ( ".", str_replace("_","",$klucz) );
		if($a_wycieczka[0] == $rok ) {
			if(isset($wycieczka['dystans'])) {
				$dystans += $wycieczka['dystans'];
				$s_dystans = $wycieczka['dystans']."km";
			} else
				$s_dystans = '';
			if(isset($wycieczka['przewyzszenie'])) {
				$przewyzszenie += $wycieczka['przewyzszenie'];
				$s_przewyzszenie = $wycieczka['przewyzszenie']."m";
			} else
				$s_przewyzszenie = '';
			if(isset($wycieczka['godz'])) {
				$czas += 60*$wycieczka['godz'];
				$s_czas = $wycieczka['godz'].'h';
			} else
				$s_czas = '';
			if(isset($wycieczka['minut'])) {
				$czas += $wycieczka['minut'];
				if($wycieczka['minut']>0) {
					if($wycieczka['minut']<10)
						$s_czas .= "0";
					$s_czas .= $wycieczka['minut']."'";
				}
			}
			if(isset($wycieczka['got']))
				$got += $wycieczka['got'];
			$pora_roku = (int)($a_wycieczka[1].$a_wycieczka[2]);
			if($pora_roku>=923 and $pora_roku<=1231) {
				$s_jesien = '<a href="../raporty/images/Autumn {S_ROK}.jpg" title="Autumn Jesień {S_ROK}">Jesień</a>';
				++$n_byla_jesien;
			}
			if($pora_roku>=622 and $pora_roku<923) {
				$s_lato = '<a href="../raporty/images/Summer {S_ROK}.jpg" title="Summer Lato {S_ROK}">Lato</a>';
				++$n_bylo_lato;
			}
			if($pora_roku>=321 and $pora_roku<622) {
				$s_wiosna = '<a href="../raporty/images/Spring {S_ROK}.jpg" title="Spring Wiosna {S_ROK}">Wiosna</a>';
				++$n_byla_wiosna;
			}
			if($pora_roku<321) {
				$s_zima = '<a href="../raporty/images/Winter {S_ROK}.jpg" title="Winter Zima {S_ROK}">Zima</a>';
			}
			$s_wiersz_work = $s_wiersz;
			$jsonWycieczka = array('data' => str_replace("_", "", $klucz), 'opis' => $wycieczka['opis'], 'href' => str_replace("../index.php?d=","",$wycieczka['link']), 'up' => "", 'km' => "", 'czas' => "" );
			$s_wiersz_work = str_replace ( '{S_DATA}', str_replace("_", "", $klucz), $s_wiersz_work );
			$s_wiersz_work = str_replace ( '{S_OPIS}', $wycieczka['opis'], $s_wiersz_work );
			$s_wiersz_work = str_replace ( '{S_LINK}', $wycieczka['link'], $s_wiersz_work );
			if($s_przewyzszenie!="" and $s_dystans!="" and $s_czas!="") {
				$s_gps_work = $s_gps;
				$s_gps_work = str_replace ( '{S_PRZEWYZSZENIE}', $s_przewyzszenie, $s_gps_work );
				$s_gps_work = str_replace ( '{S_DYSTANS}', $s_dystans, $s_gps_work );
				$s_gps_work = str_replace ( '{S_CZAS}', $s_czas, $s_gps_work );
				$s_bottom = "";
				$jsonWycieczka['up'] = $s_przewyzszenie;
				$jsonWycieczka['km'] = $s_dystans;
				$jsonWycieczka['czas'] = $s_czas;
			} else {
				$s_gps_work = "";
				$s_bottom = "border-bottom:solid 1px;";
			}
			$s_wiersz_work = str_replace ( '{S_GPS}', $s_gps_work, $s_wiersz_work );
			$s_wiersz_work = str_replace ( '{S_BOTTOM}', $s_bottom, $s_wiersz_work );
			$b_br=false;
			$s_skitura = "&nbsp;";
			$jsonWycieczka['skitura'] = false;
			if(isset($wycieczka['skitura']))
				if($wycieczka['skitura']=="tak") {
					$s_skitura = "skitura";			
					$b_br=true;
					$jsonWycieczka['skitura'] = true;
				}

			$s_ferrata = "&nbsp;";
			$jsonWycieczka['ferrata'] = false;
			if(isset($wycieczka['ferrata']))
				if($wycieczka['ferrata']=="tak") {
					$s_ferrata = "ferrata";			
					$b_br=true;
					$jsonWycieczka['ferrata'] = true;
				}

			$s_dogtrekking = "&nbsp;";
			$jsonWycieczka['dogtrekking'] = false;
			if(isset($wycieczka['dogtrekking']))
				if($wycieczka['dogtrekking']=="tak") {
					$s_dogtrekking = "dogtrekking";
					if(isset($wycieczka['skitura']))
						if($wycieczka['skitura']=="tak")
							$s_skitura .= "<BR>";
					$b_br=true;
					$jsonWycieczka['dogtrekking'] = true;
				}
				
			$kolor="B4EAFF";
			foreach ($laczniki as $klucz_poi => $poi)
			{
				if($poi['data'] == str_replace('.','',$klucz))
				{
					$szczyt=$pois[$poi['poi']];
					if($szczyt['color']=="red")
						$kolor="FF9933";
					if($szczyt['color']=="gray")
						$kolor="778899";
					if($szczyt['color']=="green")
						$kolor="40E0D0";
					if($szczyt['color']=="yellow")
						$kolor="DAA520";
				}
			}	
			if($wycieczka["skitura"] == "tak")
				$kolor="99FF99";			
			if($wycieczka["dogtrekking"] == "tak")
				$kolor="lightgreen";	
			$jsonWycieczka['kolor'] = $kolor;
			
			$s_wiersz_work = str_replace ( '{S_SKITURA}', $s_skitura, $s_wiersz_work );
			$s_wiersz_work = str_replace ( '{S_FERRATA}', $s_ferrata, $s_wiersz_work );
			$s_wiersz_work = str_replace ( '{S_DOGTREKKING}', $s_dogtrekking, $s_wiersz_work );

			$s_profil = "&nbsp;";
			if(isset($wycieczka['plik'])) {
				switch($wycieczka['plik'].'-'.str_replace(".", "", str_replace("_", "", $klucz))) {
					case 'Velka Cantoryje-20090509': 	$s_gps_track = "WielkaCzantoria-20090509";
														break;
					case 'Hala Labowska-20080713': 		$s_gps_track = "Labowska-20080713";
														break;
					case 'Wielka Rycerzowa-20080907': 	$s_gps_track = "Rycerzowa-20080907";
														break;
					case 'Kamionna-Lopusze-20090329': 	$s_gps_track = "KamionnaLopusze-20090329";
														break;
					case 'Veterny vrch-20090503': 		$s_gps_track = "VeternyVrch-20090503";
														break;
					case 'Hochstul-20100729': 			$s_gps_track = "Hochstuhl-20100729";
														break;
					case 'Mirow Bobolice-20090709': 	$s_gps_track = "Mirow-Bobolice-20090709";
														break;
					default: 							$s_gps_track = 	str_replace("'",'',str_replace(' ','',$wycieczka['plik'])).'-'
																		.str_replace(".", "", str_replace("_", "", $klucz));
				}
				if(file_exists("../raporty/inne/Przejscia/gpx/".$s_gps_track.".gpx.gz")) {					
					$s_profil = "<a href='javascript:ShowProfile_Zestawienie(1,\"".$s_gps_track."\")' title=\"Profil trasy GPS\" onmouseover=\"showLayer_Zestawienie('profil','".$s_gps_track."')\" onmouseout=\"showLayer_Zestawienie('profil','".$s_gps_track."')\"><i style=\"color:green;\">profil</i></a>";
					if($b_br)
						$s_profil = "<br>".$s_profil;
				}
			}
			$s_wiersz_work = str_replace ( '{S_PROFIL}', $s_profil, $s_wiersz_work );
			$s_wiersze .= $s_wiersz_work ;
			$jsonWycieczki[] = $jsonWycieczka;
		}
	}
	$up = (int)($przewyzszenie/1000.0);
	$dni = (int)($czas/(60.0*24.0));
	$godzin = (int)(($czas-($dni*24*60))/60.0);
	$jsonRok = array('rok' => $rok, 'got' => 0, 'dystans' => "?", 'podejscie' => "?", 'czas' => "?h" );
	$s_rok_work = str_replace ( '{S_DYSTANS}', $dystans, $s_rok_work );
	if($dystans>0) {
		$tmp2 = $s_formatted_dystans;
		$tmp2 = str_replace ( '{S_DYSTANS}', $dystans, $tmp2 );
		$s_rok_work = str_replace ( '{S_FORMATTED_DYSTANS}', $tmp2, $s_rok_work );
		$jsonRok['dystans'] = $dystans;
	} else
		$s_rok_work = str_replace ( '{S_FORMATTED_DYSTANS}', '', $s_rok_work );
	if($przewyzszenie>0) {
		$tmp2 = $s_formatted_przewyzszenie;
		$tmp2 = str_replace ( '{S_PRZEWYZSZENIE}', $up, $tmp2 );
		$s_rok_work = str_replace ( '{S_FORMATTED_PRZEWYZSZENIE}', $tmp2, $s_rok_work );
		$jsonRok['podejscie'] = $up;
	} else
		$s_rok_work = str_replace ( '{S_FORMATTED_PRZEWYZSZENIE}', '', $s_rok_work );
	if($czas>0) {
		$tmp2 = $s_formatted_czas;
		$tmp2 = str_replace ( '{S_DNI}', $dni, $tmp2 );
		$tmp2 = str_replace ( '{S_GODZIN}', $godzin, $tmp2 );
		$s_rok_work = str_replace ( '{S_FORMATTED_CZAS}', $tmp2, $s_rok_work );
		$jsonRok['czas'] = $dni."d ".$godzin."h";
	} else
		$s_rok_work = str_replace ( '{S_FORMATTED_CZAS}', '', $s_rok_work );
	$s_rok_work = str_replace ( '{S_GOT}', $got, $s_rok_work );
	$jsonRok['got'] = $got;

	if($n_byla_jesien>0 and $s_lato!="" )
		$s_lato = ", ".$s_lato;
	if(($n_byla_jesien>0 or $n_bylo_lato>0) and $s_wiosna!="" )
		$s_wiosna = ", ".$s_wiosna;
	if(($n_byla_jesien>0 or $n_bylo_lato>0 or $n_byla_wiosna>0) and $s_zima!="" )
		$s_zima = ", ".$s_zima;
	$s_rok_work = str_replace ( '{S_JESIEN}', $s_jesien, $s_rok_work );
	$s_rok_work = str_replace ( '{S_LATO}', $s_lato, $s_rok_work );
	$s_rok_work = str_replace ( '{S_WIOSNA}', $s_wiosna, $s_rok_work );
	$s_rok_work = str_replace ( '{S_ZIMA}', $s_zima, $s_rok_work );
	$s_rok_work = str_replace ( '{S_ROK}', $rok, $s_rok_work );
	$s_rok_work = str_replace ( '{S_WIERSZE}', $s_wiersze, $s_rok_work );
	
	$s_lata .= $s_rok_work;
	$jsonRok['wycieczki'] = $jsonWycieczki;
	$jsonLata[] = $jsonRok;
}

$tmp = str_replace ( '{S_LATA}', $s_lata, $tmp );
$tmp = str_replace ( '{S_GOOGLE_SEARCH}', $googlesearch, $tmp );
$tmp = str_replace ( '{SLOWA_KLUCZOWE}', $slowakluczowe, $tmp );
$tmp = str_replace ( '{META_SLOWA_KLUCZOWE}', $meta_slowakluczowe, $tmp );
$b_json = false;
if(isset($isJson)) {
	if($isJson) {
		$responseJson = $jsonLata;
		$b_json = true;
	}
}

if ( !$b_json )
	echo $tmp;

?>