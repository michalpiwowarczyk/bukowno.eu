<?php

//=======================================================================
//									
// SYSTEM:		beskidy.bukowno.eu
//									
// AUTOR:		(MPW) Michal Piwowarczyk					
//									
// DATA:		14.04.2010						
//									
//=======================================================================

require "dane.php" ;

if (isset($_REQUEST["m"])) {
	$modeSE = true;
} else {
	$modeSE = false;
}
if (isset($_REQUEST["dogtrekking"])) {
	$modeDogtrekking = true;
} else {
	$modeDogtrekking = false;
}

$tmp = file_get_contents("../template/mapa.html");
$mapa_poi = "";
			
foreach ( $pois as $klucz => $poi )
{
	if ( $klucz>0 )
	{
		$dogtrekking = FALSE;
		$lat = $poi['latitude'];
		$lng = $poi['longitude'];
		if ( $lat=="0" and $lng=="0" ) {
			continue;
		}
		$opis = str_replace("'","\'",$poi['gora']);
		$color = $poi['color'];
		$wysokosc = $poi['wysokosc'];
		
		$mapa_poi_tmp = "";
		$mapa_poi_tmp .= "var point = new GLatLng(".$lat.",".$lng.");";
		$mapa_poi_tmp .= "\n";
		$mapa_poi_tmp .= "var marker = createMarker(point, { title: '".$opis.", ".$wysokosc."m', icon: myIcon_".$color." }, ";
		$mapa_poi_tmp .= "'<div class=\"mapa\"><strong>".$opis."<\/strong>,&nbsp;&nbsp;&nbsp;".$wysokosc."m<br>";
		foreach ( $laczniki as $klucz_l => $lacznik )
		{				
			if ( $lacznik['poi'] == $klucz )
			{
				$data = $lacznik['data'];
				foreach ( $wycieczki as $klucz_w => $wycieczka )
				{				
					if ( str_replace( '.', '', $klucz_w ) == $data )
					{
						$link = $wycieczka['link'];
						if($modeSE) {
							$link = "../../bukowno.eu.SE/index.html#!/?d=".str_replace('.','',str_replace('_','',$klucz_w));
						}
						$opis_w = str_replace("'","\'",$wycieczka['opis']);
						if ( $wycieczka["dogtrekking"] == "tak" && $modeDogtrekking ) {
							$dogtrekking = TRUE;
						}
						if ( ($wycieczka["dogtrekking"] == "tak" && $modeDogtrekking) ||  !$modeDogtrekking) {
							$mapa_poi_tmp .= str_replace('_','',$klucz_w)." <a href=\"".$link."\">".$opis_w."<\/a><br>";
						}
					}
				}
			}
		}
		$mapa_poi_tmp .= "<\/div>'";
		$mapa_poi_tmp .= "); ";
		$mapa_poi_tmp .= "\n";
		$mapa_poi_tmp .= "map.addOverlay(marker);";
		$mapa_poi_tmp .= "\n";
		$mapa_poi_tmp .= "\n";
		if ( $dogtrekking || !$modeDogtrekking ) {
			$mapa_poi .= $mapa_poi_tmp;
		}
		
	}
}

$tmp = str_replace('{MAPA_POI}', $mapa_poi, $tmp);
$tmp = str_replace ( '{SLOWA_KLUCZOWE}', "", $tmp );
$tmp = str_replace ( '{META_SLOWA_KLUCZOWE}', "", $tmp );
$tmp = str_replace ( '{JS}', "", $tmp );
$tmp = str_replace ( '{DIR}', '', $tmp );
if ( modeDogtrekking ) {
	$tmp = str_replace ( '{POWIEKSZENIE}', '9', $tmp );
} else {
	$tmp = str_replace ( '{POWIEKSZENIE}', '8', $tmp );
}
echo $tmp ;

?>