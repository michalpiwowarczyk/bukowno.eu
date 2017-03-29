<?php

require "dane.php" ;

function dni_mies($mies,$rok) 
{
	$dni = 31;
	while (!checkdate($mies, $dni, $rok)) $dni--;
	return $dni;
}


function dzien_tyg_nr($mies,$rok) 
{
	$dzien = date("w", mktime(0,0,0,$mies,1,$rok));
	if($dzien == 0) $dzien=7;	
	return $dzien;
}


function miesiac_pl($mies) 
{
	$mies_pl = array(1=>"Styczeń", "Luty", "Marzec", "Kwiecień", "Maj", "Czerwiec", "Lipiec", "Sierpień", "Wrzesień", "Październik", "Listopad", "Grudzień");
	return $mies_pl[$mies];
}


function LiczbaWycieczek($rok)
{	global $wycieczki ;
	
	$sum = 0;
	foreach ($wycieczki as $klucz => $wycieczka)
	{
		if(substr($klucz,0,4)==$rok)
		  ++$sum ;
	}
	return $sum;
}


function GetOpis($rrrrmmdd,$dzien)
{	global $wycieczki;
	global $laczniki;
	global $pois;
	
	$ret = array("0",$dzien);
	foreach ($wycieczki as $klucz => $wycieczka)
	{
		if($klucz == $rrrrmmdd)
		{
			$kolor="#B4EAFF";
			foreach ($laczniki as $klucz_poi => $poi)
			{
				if($poi['data'] == str_replace('.','',$klucz))
				{
					$szczyt=$pois[$poi['poi']];
					if($szczyt['color']=="red")
						$kolor="#FF9933";
					if($szczyt['color']=="gray")
						$kolor="#778899";
					if($szczyt['color']=="green")
						$kolor="#40E0D0";
					if($szczyt['color']=="yellow")
						$kolor="#DAA520";
				}
			}	
			if($wycieczka["skitura"] == "tak")
				$kolor="#99FF99";			
			if($wycieczka["dogtrekking"] == "tak")
				$kolor="lightgreen";			
			$ret = array($wycieczka["link"],$wycieczka["opis"],$kolor);
			break;
		}
	}
	return $ret;
}


function PiszMiesiac($mies,$rok)
{
	$style_div     = 'style="width:220px;border:0px;padding:5px;margin:0px;text-align:center;"';
	$style_td      = 'style="color: #888; background-color: #efefef; text-align: center;"';
	$style_table   = 'style="border:#bbb 1px solid;background-color:#ddd;"';
	$style_tr_1    = 'style="background-color:#bbb;"';
	$style_tr_2    = 'style="background-color:#ddd;"';
	$style_th      = 'style="padding:5px;"';
	$style_td_link = 'style="background-color: {S_KOLOR}; text-align: center;"';
	$style_a       = 'style="color: #36f; text-decoration: none;"';
	
	echo '
	<td valign="top">
		<div '.$style_div.'>
			<table border=0 width="210" '.$style_table.'>
				<tr '.$style_tr_1.'>
					<th colspan=7 '.$style_th.'>'.miesiac_pl($mies).' '.$rok.'</th>
				</tr>
				<tr '.$style_tr_2.'>
					<td>Pn</td>
					<td>Wt</td>
					<td>Śr</td>
					<td>Cz</td>
					<td>Pt</td>
					<td>Sb</td>
					<td>N&nbsp;</td>					
			</tr>';
			
	echo '<tr>';
	$dzien=1;
	for($i=0; $i<dzien_tyg_nr($mies,$rok)-1; $i++)
	{
		echo '<td '.$style_td.'>&nbsp;</td>';
		++$dzien;
	}
	for($i=1; $i<=dni_mies($mies,$rok); $i++) 
	{
		$i_dzien = $i;
		if ($i<10) 
			$i = '0'.$i;
		if($dzien==1)
			echo '<tr>';
		if ($mies<10) 
			$m = '0'.$mies;		
		else
			$m = $mies;
		$data = $rok.'.'.$m.'.'.$i;
		$wycieczka = GetOpis($data,$i);
		if($wycieczka[0]=="0")
		{
			$opis = $i_dzien;
			$styl = $style_td;
		}
		else
		{
			$opis = '<a href="'.$wycieczka[0].'" title="'.$wycieczka[1].'" '.$style_a.'>'.$i_dzien.'</a>';
			$styl = $style_td_link;
			$styl = str_replace('{S_KOLOR}',$wycieczka[2],$styl);
		}
		echo '<td '.$styl.'>'.$opis.'</td> ';
		if($dzien==7)
		{
			echo '</tr>';
			$dzien=1;
		}
		else
			++$dzien;		
	}
	if($dzien>1)
	{
		for ($i=$dzien; $i<=7; ++$i)
			echo '<td '.$style_td.'>&nbsp;</td>';
		echo '</tr>';
	}
	
	echo '
			</table>
		</div>
	</td>' ;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="pl" xml:lang="pl">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
<?php
	$rok = date("Y");
	if (isset($_GET["r"]))
		$rok = $_GET["r"];
	$ok = true;
	for ( $z=2006; $z<=date("Y"); ++$z) {
		if($rok==$z) {
			$ok = true;
		}
	}
	if(!$ok)
		$rok = date("Y");
	echo '<title>Kalendarz '.$rok.' r.</title>';
	$slowakluczowe = file_get_contents("../template/slowakluczowe.txt");
	$meta_slowakluczowe = str_replace(" ",",",str_replace("\n"," ",str_replace("\r","",php_strip_whitespace("../template/slowakluczowe.txt"))));
	echo '<meta name="Keywords" content="'.$meta_slowakluczowe.'" />';
?>
<meta name="Author" content="Michał Piwowarczyk, michal.piwowarczyk@bukowno.eu" />
<link rel="shortcut icon" href="../images/favicon.ico" type="image/x-icon">
<link rel="icon" href="../images/favicon.ico" type="image/x-icon">	
<script language="javascript">
function setCalYear(select)
{
	document.location.href=select.value;
}
</script>
<script type="text/javascript" src="../js/beskidy.js"></script>
</head>

<body>
	<table width="710px" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td style="text-align:center;"><a href="../index.php" title="Góry Photo Galeria">Galeria</a>&nbsp;&nbsp;&nbsp;<a href="zestawienie.php" title="Zestawienie zbiorcze">Zestawienie zbiorcze</a></td>
			<td style="text-align:center;"><a href="mapa.php" title="Google Maps track">Google Maps</a>&nbsp;&nbsp;&nbsp;<a href="../raporty/inne/gory.kmz" title="Google Earth KMZ KML ślad">Google Earth</a></td>
			<td style="text-align:center;">Mapa <?php echo '<a href="../raporty/images/'.$rok.'.jpg" title="mapa za rok '.$rok.'">'.$rok.'</a>'; ?> r.&nbsp;&nbsp;&nbsp;
				<select onchange='setCalYear(this);'>
				<?php
				for ($r=date("Y"); $r>=2006; --$r)
				{
					echo '<option value="kalendarz.php?r='.$r.'"';
					if($r==$rok) 
						echo ' selected'; 
					echo '>'.$r.' ('.LiczbaWycieczek($r).')';
					echo '</option>';
				}
				?>
				</select>					
			</td>
		</tr>
		<tr>
			<td colspan="3"><hr></td>
		</tr>

	<?php
	for ($mies=1; $mies<=12; ++$mies)
	{
		if ($mies==1 or $mies==4 or $mies==7 or $mies==10)
			echo '<tr>';
		PiszMiesiac($mies,$rok);
		if ($mies==3 or $mies==6 or $mies==9 or $mies==12)
			echo '</tr>';
	}	
	?>
	
	</table>
	<span style="font-size: 9pt;text-align: right">Based on <a href="http://www.hikr.org" title="hikr.org">hikr.org</a></span>

	<!-- WEB SLICE -->
	<div class="hslice" id="bukowno-eu" style="display:none;">
		<p class="entry-title">bukowno.eu - Aktualności</p>
		<div class="entry-content">bukowno.eu - Aktualności</div>
		<a rel="feedurl" href="rss.php?webslice">bukowno.eu - Aktualności</a>
	</div>
	<div style="display:none;">
	<?php
	echo $slowakluczowe;
	?>
	</div>
</body>
</html>