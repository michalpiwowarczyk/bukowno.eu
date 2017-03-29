<?php

function GetBasePath() { 
	$path_parts = pathinfo($_SERVER['SCRIPT_NAME']);
	return $path_parts['dirname'];
}	

$thumbs_dir = "../../../../tmp/thumbnails/";
$thumb_size = 180;
$pix_size = 640;
$pix_size = 1024;
$picsperpage = 20;
$picsperrow = 5;

$body='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
 <title>Galeria zdjęć</title>
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
 <meta name="keywords" content="Galeria">
 <meta name="Author" content="Michał Piwowarczyk, michal.piwowarczyk@bukowno.eu" />		
 <link rel="shortcut icon" href="../../../duma.bukowno.eu/dog.ico" type="image/x-icon">
 <link rel="icon" href="../../../duma.bukowno.eu/dog.ico" type="image/x-icon">		 
 <style TYPE="text/css">

    A:link { font-family:verdana, arial, sans-serif; font-size:8pt; COLOR: #000000; TEXT-DECORATION: none;	}
    A:visited {font-family:verdana, arial, sans-serif; font-size:8pt; color: #000000; TEXT-DECORATION: none; }
    A:active {font-family:verdana, arial, sans-serif; font-size:8pt; COLOR: #089ACB; TEXT-DECORATION: none; }
    A:hover {font-family:verdana, arial, sans-serif; font-size:8pt; COLOR: #969F79; TEXT-DECORATION: none; }
    body {
            font-family:Verdana,arial, sans-serif; font-size:7pt;
            background: #BCC4AA;
            COLOR: #000000;
            font-size: 12px;
            margin: 1px;
            padding: 0px;
            scrollbar-arrow-color: #000000;
            scrollbar-track-color: #BCC4AA;
            scrollbar-face-color: #BCC4AA;
            scrollbar-highlight-color: #BCC4AA;
            scrollbar-3dlight-color: #000000;
            scrollbar-darkshadow-color: #BCC4AA;
            scrollbar-shadow-color: #000000;
         }
    td
        {
            font-family:Verdana,arial, sans-serif; font-size:7pt;
            border-width:1px;
            border-color: #FFFFFF;
        }

    IMG {
	       border-style: solid;
	       border-width:1;
	       border-color: #000000;
        }
    .table_border
            {
                margin: 10px;
                padding: 5px;
                border: 1px solid #595959;
            }
    .comments_up
            {
	           color:inherit;
	           background-color: #B1B89C;
	           width:100%;
	           margin: 5px;
	           padding: 3px 3px 3px 3px;
	           font-weight:bold;
	           letter-spacing:1px;
            }
    .input_border
            {
               color : #000000;
               background-color : #FFFFFF;
               BORDER-STYLE : groove;
               border-left-width: 1px;
               border-right-width: 1px;
               border-top-width: 1px;
               border-bottom-width: 1px;
               BORDER-color :#595959;
               overflow: auto;
            }
    .button
            {
                background-color:#969F79;
                border: 1px;
                border-color:#595959;
                BORDER-STYLE :groove;
                color : #000000;
                FONT-FAMILY: Verdana, Arial, Sans-serif;
                FONT-SIZE:9px;
            }
 </style>
</head>
<body>';

$header='
<table class="table_border" width="90%" align="center" border="0" cellpadding="0" cellspacing="0" >
  <tr>
    <td>
';

$footer='
    </td>
  </tr>
</table>
</body>
</html>';


function make_thumbnail($src, $dst) {
    $img = imagecreatefromjpeg( $src );
    $width = imagesx( $img );
    $height = imagesy( $img );

    $thumbWidth = 180;
    $new_width = $thumbWidth;
    $new_height = floor( $height * ( $thumbWidth / $width ) );

    $tmp_img = imagecreatetruecolor( $new_width, $new_height );
    imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
    imagejpeg( $tmp_img, $dst );
	imagedestroy($img);
	imagedestroy($tmp_img);	
}


@mkdir($thumbs_prfx);
$gallery_dir="/home/mpw1968/domains/bukowno.eu/public_html".$_REQUEST['dir'];
if($_SERVER["SERVER_ADDR"]=="10.5.0.240")
	$gallery_dir="/sip0".$_REQUEST['dir'];
if($_SERVER["SERVER_ADDR"]=="192.168.102.249" or $_SERVER["SERVER_ADDR"]=="95.48.140.26")
	$gallery_dir="/home/bukowno.eu".$_REQUEST['dir'];
$s_dir=$_REQUEST['dir'];
	
$opdir=opendir($gallery_dir);
$i=0;
while ($file = readdir($opdir)) {
	if (!is_dir($file)) {
		$path_parts = pathinfo($file);
		$ext = strtolower($path_parts['extension']);
		if ($file != "." && $file != ".." && ($ext== "jpg" || $ext == "jpeg" || $ext == "png"))
		$filelist[$i++] = $file;
	}
}
closedir($opdir);
if (!$filelist) {  $html=$body.$header."Brak zdjęć w katalogu".$footer; echo $html; exit;}
@sort ($filelist);

// for big picture viewing
if (isset($_GET['p']) && $_GET['p']!=='' && isset($_GET['pg'])){
	$p = $_REQUEST['p'];
	$html=$body.$header;
	$sek = 0;
	$atg = count($filelist)-1;
	foreach ( $filelist as $key => $value ) {
		if ($value==$p) {	
			$sek=$key+1;
			$atg=$key-1;
			if($sek==count($filelist)) $sek=0;
			if($atg==-1) $atg=count($filelist)-1;
		}  
	}
	$html.='
<table  border="0" cellpadding="0" cellspacing="0" align="center">
  <tr>
    <td align="center" valign="middle">
                <a href="gallery.php?dir='.$s_dir.'&pg='.$_GET['pg'].'">Powrót do indeksu zdjęć</A><br />
                <a href="gallery.php?dir='.$s_dir.'&p='.$filelist[$atg].'&pg='.$_GET['pg'].'">Poprzednie</a> |
                <a href="gallery.php?dir='.$s_dir.'&p='.$filelist[$sek].'&pg='.$_GET['pg'].'">Następne</a><br /><br />
				<table><tr>
					<td><a href="gallery.php?dir='.$s_dir.'&p='.$filelist[$atg].'&pg='.$_GET['pg'].'"><img src="../../images/g_prev.gif" style="border:0px;vertical-align: middle;" /></a></td>
					<td><img src="'.$s_dir."/".$_GET['p'].'" border="0" alt="'.$_GET['p'].'" width="'.$pix_size.'"></td>
					<td><a href="gallery.php?dir='.$s_dir.'&p='.$filelist[$sek].'&pg='.$_GET['pg'].'"><img src="../../images/g_next.gif" style="border:0px;vertical-align: middle;" /></a></td>
				</tr></table>
				<br />
                <a href="gallery.php?dir='.$s_dir.'&pg='.$_GET['pg'].'">Powrót do indeksu zdjęć</A><br />
                <a href="gallery.php?dir='.$s_dir.'&p='.$filelist[$atg].'&pg='.$_GET['pg'].'">Poprzednie</a> |
                <a href="gallery.php?dir='.$s_dir.'&p='.$filelist[$sek].'&pg='.$_GET['pg'].'">Następne</a>
    </td>
  </tr>
</table>';
	$html.=$footer;
	echo $html;
	exit;
}

// show all pictures per page
$psl=0;
if(isset($_REQUEST['psl']))
	if(!empty($_REQUEST['psl']))
		$psl=$_REQUEST['psl'];

$max = count ($filelist);
if($psl==0) {
	$navig = "Poprzednia strona";
} else {
	$tmp = $psl -1; 
	$navig = '<a href="gallery.php?dir='.$s_dir.'&amp;psl='.$tmp.'"><< Poprzednia strona</a>';
}
$tmp = $psl * $picsperpage + $picsperpage;
$navig .= ' | ';
$puslapiu=(int)($max/$picsperpage);
$kp=0;
for ($i=0; $i<=$puslapiu;$i++) {
	$kp++;
  	$navig .=' -<a href="gallery.php?dir='.$s_dir.'&amp;psl='.$i.'">'.$kp.'</a>- ';
}
$navig .= ' | ';
if ($max > $tmp) {
	$tmp = $psl +1; 
	$navig .= '<a href="gallery.php?dir='.$s_dir.'&amp;psl='.$tmp.'"> Następna strona >></a>';
} else { 
	$navig .= "Następna strona";
}
$startas = $psl * $picsperpage;
$endaz = $psl * $picsperpage + $picsperpage;
if ($endaz > $max) {
	$endaz=$max;
}
$html=$body.$header;

$html.='<div style="visibility:hidden;">';
for ($u=0; $u<$startas;) {
    $pixas = $filelist[$u];
	$html.= '<A HREF="'.$s_dir.$pixas.'" title="'.$pixas.'"></A>';
	$u++;
}
$html.='</div>
	<table style="margin: 10px;padding: 5px;" width="90%" align="center" border="0" cellpadding="0" cellspacing="0" >
    <tr>';

$counter = 0;
for ($u=$startas; $u<$endaz;) {
    $pixas = $filelist[$u];
	$f=$gallery_dir."/".$pixas;	
	if(file_exists($f)) {
		$id = fileinode($f);	
		$thumb_name = $thumbs_dir.$id.".jpg";
		if (!file_exists($thumb_name)) {
			make_thumbnail($f,$thumb_name);
		}
		$thumb_src = $thumb_name;
	} 
   	$html.= '<td align="center" valign="middle" >';
	$html.= '<A HREF="gallery.php?pg=1&p='.$pixas.'&dir='.$s_dir.'" title="'.$pixas.'">';
    $html.= '<IMG SRC="'.$thumb_src.'" border="1" alt="show"></A>';
    $html.='</td>';
    $counter++;
	$u++;
	if ($u == $endaz) {
		$counter = 0;
		$html.= '</tr>';
	}
    if ($counter == $picsperrow) {
		$counter = 0; 
		$html.= '</tr><tr>';
	}
}

$html.='
    <tr>
        <td height="10" style="border-top: 1px #595959 solid;" colspan="5" width="100%" align="center" valign="bottom">'.$navig.'</td>
    </tr>
  <tr>
    <td align="center" valign="middle" colspan="5" width="100%">	
                <a href="'.$s_dir.'.." border="0" alt="Powrót">Powrót</a>                
    </td>
  </tr>	
</table>
<div style="visibility:hidden;">';

for ($u=$endaz; $u<$max;) {
    $pixas = $filelist[$u];
	$html.= '<A HREF="'.$_REQUEST['dir'].$pixas.'" rel="milkbox[gall1]" title="'.$pixas.'"></A>';
	$u++;
}

$html.='</div>';

$html.= $footer;
echo $html;
?>