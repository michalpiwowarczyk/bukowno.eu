<?php

function getFolderProperties($f) {
	$prop = file_get_contents($f."/meta.properties");
	$prop = repairProperties($prop,$f."/meta.properties",false);
	$prop_array = AnalyzeProperties($prop);
	return $prop_array;
}


function isJPG($f) {
	$path_parts = pathinfo($f);
	$ext = strtolower($path_parts['extension']);
	if($ext=="jpg" or $ext=="jpeg")
		return true;
	return false;
}


function getTarCount($filename) {
	// Zwraca liczbe plików w archiwum w oparciu o TARa i baze danych
	global $database_dir;

	$id=fileinode($filename);
	$datafile_name = $database_dir.$id."count.dat";
	$ok=true;
	if (!file_exists($datafile_name))
		$ok=false;
	else {
		if (filemtime($datafile_name)<filemtime($filename))
			$ok=false;
	}
	if ($ok) {
		// Odczytanie z pliku z danymi
		$fp=fopen($datafile_name,"r");
		$ile=(int)fgets($fp);
	} else {
		// Tworzenie pliku z danymi
		$tar_object = new Archive_Tar($filename);			
		$ile = sizeof($tar_object->listContent());
		$fp=fopen($datafile_name,"w");
		fputs($fp,$ile);
	}
	fclose($fp);
	return $ile;
}


function getTarList($filename) {
	// Zwraca tablice z nazwami i czasami plików w archiwum w oparciu o TARa i baze danych
	global $database_dir, $thumb_dir;

	$id=fileinode($filename);
	$datafile_name = $database_dir.$id."list.dat";
	$ok=true;
	if (!file_exists($datafile_name))
		$ok=false;
	else {
		if (filemtime($datafile_name)<filemtime($filename))
			$ok=false;
	}
	if ($ok) {
		// Odczytanie z pliku z danymi
		$fp=fopen($datafile_name,"r");
		$i=0;
		while(!feof($fp)) {
			$s=fgets($fp);
			$a=explode("~",$s);
			if(count($a)>3) {
				$ret[$i]['time']=$a[0];
				$ret[$i]['filename']=$a[1];
				$ret[$i]['filesize']=$a[2];
				++$i;
			}
		}		
	} else {
		// Tworzenie pliku z danymi
		$tar_object = new Archive_Tar($filename);
		if (($v_list=$tar_object->listContent()) != 0) {
			for ($i=0; $i<sizeof($v_list); $i++) {						
				$tmpfname = tempnam($thumb_dir, "beskidy");
				$handle = fopen($tmpfname, "w");
				fwrite($handle, $tar_object->extractInString($v_list[$i]['filename']));
				fclose($handle);
				$ret[$i]['time'] = photoTime($tmpfname);
				$ret[$i]['filename'] = $v_list[$i]['filename'];
				$ret[$i]['filesize'] = $v_list[$i]['size'];
				unlink($tmpfname);
			}
		}		
		$fp=fopen($datafile_name,"w");
		for ($i=0; $i<sizeof($ret); $i++)
			fputs($fp,$ret[$i]['time']."~".$ret[$i]['filename']."~".$ret[$i]['filesize']."~\n");
	}
	fclose($fp);
	return $ret;
}


function getFolderZdjecia($f) {
	$ret = 0;

	$ite=new RecursiveDirectoryIterator($f);	
	foreach (new RecursiveIteratorIterator($ite) as $filename=>$cur) {
		$path_parts = pathinfo($filename);
		if($path_parts["basename"]=="galeria.tar")
			$ret += getTarCount($filename);
		else {
			if(isJPG($path_parts["basename"]))
				++$ret;
		}
	}
	return $ret." ".deklinacja("zdjęć",$ret);
}


function getFolderWycieczki($f) {
	$ret = "";
	$i = 0;
	$katalogi = Array();
	$jpg_metaprop = Array();

	$ite=new RecursiveDirectoryIterator($f);	
	foreach (new RecursiveIteratorIterator($ite) as $filename=>$cur) {		
		$path_parts = pathinfo($filename);
		if(!in_array($path_parts['dirname'],$katalogi)) {
			$katalogi[$i] = $path_parts['dirname'];
			$jpg_metaprop[$i]['jpg'] = FALSE;
			$jpg_metaprop[$i]['metaprop'] = FALSE;
			$jpg_metaprop[$i]['tar'] = FALSE;
			++$i;
		}
		if(in_array($path_parts['dirname'],$katalogi)) {
			$ind = array_search($path_parts['dirname'], $katalogi);
			if(!$jpg_metaprop[$ind]['jpg']) {
				if(isJPG($filename)) {
					$jpg_metaprop[$ind]['jpg'] = TRUE;
				}
			}
			if(!$jpg_metaprop[$ind]['metaprop']) {
				if($path_parts['basename']=="meta.properties") {
					$jpg_metaprop[$ind]['metaprop'] = TRUE;
				}
			}
			if(!$jpg_metaprop[$ind]['tar']) {
				if($path_parts['basename']=="galeria.tar") {
					$jpg_metaprop[$ind]['tar'] = TRUE;
				}
			}
		}
	}
	$ile = 0;
	for($i=0;$i<count($katalogi);++$i) {
		if($jpg_metaprop[$i]['jpg'] AND $jpg_metaprop[$i]['metaprop'])
			++$ile;
		else
		if($jpg_metaprop[$i]['tar'] AND $jpg_metaprop[$i]['metaprop'])
			++$ile;
	}
	if(!($ile==1 AND count($katalogi)==1))
		$ret=", ".$ile." ".deklinacja("wycieczek",$ile);
	return $ret;
}


function is_new($f) {
	global $new_days;
	$ite=new RecursiveDirectoryIterator($f);	
	foreach (new RecursiveIteratorIterator($ite) as $filename=>$cur) {
		$pos = strpos($filename, "meta.properties");
		if ( $pos !== false ) {
			$prop = file_get_contents($filename);
			$prop_array = AnalyzeProperties($prop);
			if(!empty($prop_array['folderIcon'])) {
				if(time()-($new_days*60*60*24)<filemtime($filename)) {
					return TRUE;
				}
			}
		}
	}
	return FALSE;
}


function cmp_dirs_mtime($a, $b) {
  if ($a['time'] == $b['time']) {
	if($a['file'] == $b['file'])
		return 0;
	return ($a['file'] < $b['file']) ? -1 : 1;
  }
  return ($a['time'] < $b['time']) ? -1 : 1;
}


function cmp_dirs_name($a, $b) {
  if ($a['file'] == $b['file']) {
	if($a['time'] == $b['time'])
		return 0;
	return ($a['time'] < $b['time']) ? -1 : 1;
  }
  return ($a['file'] < $b['file']) ? -1 : 1;
}


function AnalyzeProperties($prop) {
	$ret['title'] = "";
	$ret['description'] = "";
	$ret['folderIcon'] = "";
	
	$prop = str_replace("\\\r\n","",$prop);
	$prop = str_replace("\\\n","",$prop);
	$prop = str_replace("\r\n","\n",$prop);
	$array = explode("\n", $prop);
	foreach($array as $value) {
		if(!empty($value)) {
			if(substr($value,0,6)=="title=") $ret['title']=substr($value,6);
			if(substr($value,0,9)=="descript=") $ret['description']=substr($value,9);
			if(substr($value,0,11)=="folderIcon=") $ret['folderIcon']=substr($value,11);
		}
	}	
	return $ret;
}


function getPrzypis($dir) {
	global $template_dir;
	
	$przypisy = str_replace("\r\n","\n",file_get_contents($dir.$template_dir."przypisy.txt"));
	$array = explode("\n", $przypisy);

	$ile = count($array);
	$seconds = date('s');
	$pozycja = ($seconds)%($ile);
	$ret = $array[$pozycja];
	return $ret;
}


function make_thumbnail($src, $dst) {
	global $thumb_widthJson, $thumb_heightJson;

	if (!file_exists($src))
		return;
	if (filesize($src)==0)
		return;
		
    $img = imagecreatefromjpeg( $src );
    $width = imagesx( $img );
    $height = imagesy( $img );

	if($width>$height)
	    $thumbWidth = $thumb_widthJson;
	else
		$thumbWidth = $thumb_heightJson;
    $new_width = $thumbWidth;
    $new_height = floor( $height * ( $thumbWidth / $width ) );

    $tmp_img = imagecreatetruecolor( $new_width, $new_height );
    imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
    imagejpeg( $tmp_img, $dst );
	imagedestroy($tmp_img);
	
	imagedestroy($img);	
}


function getThumbnailImageFromTar($folder,$image,$mode) {
	return getThumbnailImageFromTarJson($folder,$image,$mode,false);
}

function getThumbnailImageFromTarJson($folder,$image,$mode,$is_json) {
	global $thumb_dir, $thumb_width, $thumb_height;

	$width = $thumb_width;
	$height = $thumb_height;
	$thumb_name = "images/folder.gif";
	
	$tar_file = $folder.'/galeria.tar';
	$id = fileinode($tar_file);
	$thumb_name = $thumb_dir.$id."-".$image;
	if($is_json) {
		$thumb_name = "../".$thumb_name;
	}
	$ok=true;
	if (!file_exists($thumb_name))
		$ok=false;
	else {
		if(filemtime($thumb_name)<filemtime($tar_file))
			$ok=false;
	}
	if(!$ok) {
		$f = extract_from_tar($folder,$image,$is_json);
		make_thumbnail($f,$thumb_name);
	}
	if (file_exists($thumb_name)) {
		$img = imagecreatefromjpeg( $thumb_name );
		$width = imagesx( $img );
		$height = imagesy( $img );
		imagedestroy($img);
	}	

	if($width>$height) {
	    $thumbWidth = $thumb_width;
		$thumbHeight = $thumb_height;
	} else {
		$thumbWidth = $thumb_height;
		$thumbHeight = $thumb_width;
	}
		
	if($mode) {
		return 'class="image" src="'.$thumb_name.'" width="'.$thumbWidth.'"';
	}
	return $thumb_name;
}


function getFolderImage($folder,$image,$mode,$tar,$tmpfile) {
	global $thumb_dir, $thumb_width, $thumb_height;

	$width = $thumb_width;
	$height = $thumb_height;
	$thumb_name = "images/folder.gif";
	
	if(is_tar($folder))
		return getThumbnailImageFromTar($folder,$image,$mode);

	if($tar)
		$f = $tmpfile;
	else
		$f = $folder."/".$image;
	$ok=false;
	if (file_exists($f)) 
		$ok=true;
	else {
		$file_parts = pathinfo($f);
		if(is_tar($file_parts["dirname"])) {
			$tar=true;			
			$f = extract_from_tar($file_parts["dirname"],$file_parts["basename"],false);
			$ok=true;
		}
	}		
	if($ok) {
		if($tar)
			$id = fileinode($folder.'/galeria.tar')."-".$image;
		else
			$id = fileinode($f)."-";
		$thumb_name = $thumb_dir.$id.".jpg";
	    if (!file_exists($thumb_name))
			make_thumbnail($f,$thumb_name);
		if(filemtime($thumb_name)<filemtime($f))
			make_thumbnail($f,$thumb_name);

		if (file_exists($thumb_name)) {
			$img = imagecreatefromjpeg( $thumb_name );
			$width = imagesx( $img );
			$height = imagesy( $img );
			imagedestroy($img);
		}
	}

	if($width>$height) {
	    $thumbWidth = $thumb_width;
		$thumbHeight = $thumb_height;
	} else {
		$thumbWidth = $thumb_height;
		$thumbHeight = $thumb_width;
	}
		
	if($mode) {
		return 'class="image" src="'.$thumb_name.'" width="'.$thumbWidth.'"';
	}
	return $thumb_name;
}


function getUpDir($dir) {
	$ret = "";
	
	$arr = explode("/",$dir);
	for($i=0;$i<count($arr)-1;++$i) {
		if($i>0)
			$ret .= "/";
		$ret .= $arr[$i];		
	}
	return $ret;
}


function explodeDirectory($dir,$mode) {
	$ret = "";
	$last = "";
	$wzor = '<a href="index.php?d={S_DIR}">{S_TITLE}</a>{S_NEXT}';
	$okruszkiJson = array();
	$next = ' &raquo; ';
	
	$dir = str_replace("//","/",rtrim($dir,"/"));
	$arr = explode("/",$dir);
	for($i=0;$i<count($arr);++$i) {
		if($i>0)
			$last .= "/";
		$last .= $arr[$i];
		$s = $wzor;
		$s = str_replace("{S_DIR}", $last, $s);
		$props = getFolderProperties($last);
		$s = str_replace("{S_TITLE}", $props['title'], $s);
		$okruszekJson = array('href' => $last, 'text' => $props['title']);
		if($i<count($arr)-1)
			$s = str_replace("{S_NEXT}", $next, $s);			
		else
			$s = str_replace("{S_NEXT}", "", $s);
		$ret .= $s;
		$okruszkiJson[] = $okruszekJson;
	}
	if ( $mode )
		return $okruszkiJson;
	return $ret;
}


function ShowProperties($p) {
	$ret = "";
	$ret .= "TITLE=[".$p['title']."]<br />";	
	$ret .= "FOLDERICON=[".$p['folderIcon']."]<br />";
	$ret .= "DESCRIPTION=[".$p['description']."]<br />";
	return $ret;
}


function getPhotoParameters($pos,$photo,$dir,$tab,$mode) {
	$ret['ret'] = false;
	
	$i = 0;
	$jest = false;				
	$lp = count($tab);
	while($i<$lp) {		
		$value = $tab[$i];
		if($value['file']==$photo) {
			$jest = true;
			break;			
		}
		++$i;
	}	
	if($jest) {
		$n_pos = $i + $pos;
		if($n_pos<0 or $n_pos>=$lp) return $ret;
		$ret['ret'] = true;
		$ret['name_zdjecia'] = $tab[$n_pos]['file'];
		$ret['href_do_zdjecia'] = "index.php?d=".$dir."&f=".$tab[$n_pos]['file'];		
		if($mode) {
			$size = $tab[$n_pos]['filesize'];
			$rozmiar = round($size/1024.0,0)." kB";
			$ret['rozmiar_zdjecia'] = $rozmiar;
			if(is_tar($dir))
				$ret['thumb_do_zdjecia'] = getThumbnailImageFromTar($dir,$tab[$n_pos]['file'],false);
			else
				$ret['thumb_do_zdjecia'] = getFolderImage($dir,$tab[$n_pos]['file'],false,false,'');
		}
	}

	return $ret;
}


function getPhotoPosition($photo,$tab) {
	$i = 0;
	$jest = false;				
	$lp = count($tab);
	while($i<$lp) {		
		$value = $tab[$i];
		if($value['file']==$photo) {
			$jest = true;
			break;
		}
		++$i;
	}	
	if($jest)
		return $i;
	return -1;
}


function exifData($photo) {
	$ret['make'] = '?';
	$ret['model'] = '?';
	$ret['date'] = '?';
	
	$exif_ifd0 = @exif_read_data($photo ,'IFD0' ,0);   
	if ( $exif_ifd0===false ) {
		return $ret;
	}
    $exif_exif = @exif_read_data($photo ,'EXIF' ,0);
	if ( $exif_exif===false ) {
		return $ret;
	}
  
    $notFound = "Unavailable";
 
    if (@array_key_exists('DateTimeOriginal', $exif_ifd0)) {
		$camDate = $exif_ifd0['DateTimeOriginal'];
		$camDate[4]='.';
		$camDate[7]='.';
		$camDate = substr($camDate,0,16);
	} else {
		$data = filemtime($photo);
		$camDate = date('Y.m.d H:i', $data);		
	}

	if (@array_key_exists('Make', $exif_ifd0)) {
		$camMake = $exif_ifd0['Make'];
	} else 
		$camMake = $notFound;

	if (@array_key_exists('Model', $exif_ifd0)) {
		$camModel = $exif_ifd0['Model'];
	} else 
		$camModel = $notFound;

	$ret['make'] = $camMake;
	$ret['model'] = $camModel;
	$ret['date'] = $camDate;
	return $ret;
}


function photoTime($f) {
	$camera = exifData($f);
	
	$exifPieces = explode(".", $camera['date']);
	$newExifString = $exifPieces[0] . "-" . $exifPieces[1] . "-" . $exifPieces[2] ;
	return strtotime($newExifString);
}


function ostatniaAktualizacja() {
	global $utils_dir, $start_dir;

	$data = 0;
	$f = $utils_dir."dane.php";
	if(file_exists($f))
		$data = filemtime($f);

	$ite=new RecursiveDirectoryIterator($start_dir);	
	foreach (new RecursiveIteratorIterator($ite) as $filename=>$cur) {
		$pos = strpos($filename, "meta.properties");
		if ( $pos !== false ) {
			if($data<filemtime($filename)) {
				$data = filemtime($filename);
			}
		}
	}
	
	if($data==0) $data = time();
	return date('Y.m.d H:i', $data);		
}


function repairProperties($prop,$file,$komunikat) {
	$changed = false;
	
	$array = Array ( 
					array ( "co" => '../../../raporty/', "naco" => 'raporty/pdf_html/' ),
					array ( "co" => 'raporty/pdf_html/inne/KML/kml.php', "naco" => 'utils/kml.php' ),
					array ( "co" => 'document.writeln(ProfilHREF(3,', "naco" => 'document.writeln(ProfilHREF(0,' ),
					array ( "co" => 'document.writeln(ProfilDIV(3,', "naco" => 'document.writeln(ProfilDIV(0,' ),
					array ( "co" => '<b style="color:red;">Geotagging</b>', "naco" => '<strong style="color:white;">Geotagging</strong>' )
					);
	
	foreach ( $array as $zamieniacz ) {
		$pos = strpos($prop, $zamieniacz['co']);
		if($pos !== false)
			$changed = true;
		$prop = str_replace ( $zamieniacz['co'], $zamieniacz['naco'], $prop );
	}
	$changed = false;
	if($changed) { file_put_contents($file, $prop); if($komunikat) echo "zamiana properties ".$file."<br />"; }
	return $prop;
}


function convertAllProperties($dir) {
	$ite=new RecursiveDirectoryIterator($dir);	
	foreach (new RecursiveIteratorIterator($ite) as $filename=>$cur) {
		$pos = strpos($filename, "meta.properties");
		if ( $pos !== false ) {
			$prop = file_get_contents($filename);
			repairProperties($prop,$filename,true);		
		}
	}
}


function getLiczbaZdjecwFolderze($folder) {
	$ret = 0;

	if(is_tar($folder)) {
		$ret = getTarCount($folder.'/galeria.tar');
	} else {
		$handler = opendir($folder);
		if($handler) {
			while ($file = readdir($handler)) {
				$f = $folder."/".$file;
				if(!is_dir($f)) 
					if(isJPG($f))
						++$ret;
			}
			closedir($handler);
		}
	}
	return $ret;
}


function repair_properties_mtime() {
	$s_dir="../galeria";
	$ite=new RecursiveDirectoryIterator($s_dir);	
	foreach (new RecursiveIteratorIterator($ite) as $filename=>$cur) {
	$pos = strpos($filename, "meta.properties");
		if ( $pos !== false ) {
			echo $filename." ".filemtime($filename).", ".date ("F d Y H:i:s.", filemtime($filename))." : ".filectime($filename).", ".date ("F d Y H:i:s.", filectime($filename))."<br>";
			$directory=dirname($filename);
			$czas=filemtime($directory);
			echo $directory." ".filemtime($directory).", ".date ("F d Y H:i:s.", filemtime($directory))." : ".filectime($directory).", ".date ("F d Y H:i:s.", filectime($directory))."<br>";
			touch($filename,$czas);
		}
	}
}


function extract_from_tar($tar_dir,$file_name,$is_json) {
	global $images_dir;
	
	$tar_file = $tar_dir.'/galeria.tar';
	$inode = fileinode($tar_file);
	$tmpfname = $images_dir."beskidy".$inode.$file_name;	
	if($is_json) {
		$tmpfname = "../".$tmpfname;
	}
	$ok=true;
	if (!file_exists($tmpfname)) {
		$ok=false;
	} else {
		if (filemtime($tmpfname)<filemtime($tar_file)) {
			$ok=false;
		}
		if (filesize($tmpfname)==0) {
			$ok=false;
		}
	}
	if (!$ok) {
		$tar_object = new Archive_Tar($tar_file);
		$handle = fopen($tmpfname, "w");
		fwrite($handle, $tar_object->extractInString($file_name));
		fclose($handle);
	}
	return $tmpfname;
}


function is_tar($s_dir) {
	return file_exists($s_dir.'/galeria.tar');
}


function deklinacja($rzeczownik,$liczba) {
	$wynik=$rzeczownik;
	
	if($rzeczownik=="wycieczek") {
		if($liczba==1) $wynik="wycieczka";
		else
		if($liczba % 100 >= 10 && $liczba % 100 <= 20) $wynik="wycieczek";
		else
		if(in_array($liczba%10,array(2,3,4))) $wynik="wycieczki";		
	}
	if($rzeczownik=="zdjęć") {
		if($liczba==1) $wynik="zdjęcie";
		else
		if($liczba % 100 >= 10 && $liczba % 100 <= 20) $wynik="zdjęć";
		else
		if(in_array($liczba%10,array(2,3,4))) $wynik="zdjęcia";		
	}

	return $wynik;
}

function getAktualnosci($wycieczki) {
	global $thumb_dir;
	
	$aktualnosciJson = array();
	$i = 0;
	foreach ( $wycieczki as $klucz => $wycieczka ) {
		if($i>3)
			break;
		$folder = str_replace("../index.php?d=","",$wycieczka['link']);
		$data = $klucz;
		$props = getFolderProperties("../".$folder);
		$thumb_name = $thumb_dir.fileinode("../".$folder.'/galeria.tar')."-".$props['folderIcon'];
		getThumbnailImageFromTarJson("../".$folder,$props['folderIcon'],false,true);
		$aktualnoscJson = array('img' => $thumb_name, 'text' => $props['title'], 'href' => $folder, 'button' => $wycieczka['opis'], 'data' => $data);
		$aktualnosciJson[] = $aktualnoscJson;
		++$i;
	}
	return $aktualnosciJson;
}

function getLataDlaZestawienia($wycieczki) {
	$lata = array();
	
	foreach ( $wycieczki as $klucz => $wycieczka ) {
		$rok = explode (".", $klucz);
		$rok = str_replace ("_", "", $rok[0] );
		if (!in_array($rok,$lata)) {
			$lata[] = $rok;
		}
	}
	return $lata;
}

function getGaleriaAtDate($wycieczki,$data) {
	$galeria['katalog'] = "galeria";
	foreach ( $wycieczki as $klucz => $wycieczka ) {
		if ( $klucz == $data ) {
			$galeria['katalog'] = $folder = str_replace("../index.php?d=","",$wycieczka['link']);
			break;
		}
	}
	return $galeria;
}

function getInodeTarArray($folder,$r) {
	$directory = new RecursiveDirectoryIterator($folder);
	$iterator = new RecursiveIteratorIterator($directory);
	$files = array();
	foreach ($iterator as $info) {
		if ($info->getBasename()=="galeria.tar") {
			$files[] = array('inode'=>$info->getInode(),'dir'=>$info->getPath(),'r'=>$r);
		}
	}
	return $files;
}

function getInodeFromThumbFileName($thumb_name) {
	$ret = "";
	for($i=0;$i<strlen($thumb_name);++$i) {
		if(is_numeric($thumb_name[$i])) {
			$ret .= $thumb_name[$i];
		}		
		else {
			break;
		}
	}
	return $ret;
}

function getRandomizeImage() {
	global $database_dir, $thumb_dir;
	
	$fileNames[] = array();
	$ile = 0;
	if ($handle = opendir("../".$thumb_dir)) {
        while (($file = readdir($handle)) !== false){
			if (!in_array($file, array('.', '..')) && !is_dir($dir.$file) && (strstr($file,".jpg") || strstr($file,".JPG"))) {
                $ile++;
				$fileNames[] = $file;
			}
        }
    }
	if ($ile==0) {
		return;
	}
	$pos = mt_rand(0,$ile-1);
	$inode = getInodeFromThumbFileName($fileNames[$pos]);	
	$ret = array_merge(getInodeTarArray("../galeria","../"), getInodeTarArray("../../bukowno.eu.SE/galeria",""));
	$dir = "";
	$r = "";
	$ok = false;
	foreach ($ret as $wartosc) {		
		if($wartosc['inode']==$inode) {
			$dir = $wartosc['dir'];
			$r = $wartosc['r'];
			$ok = true;
			break;
		}
	}
	$ret = array('img'=>"",'dir'=>"",'r'=>"");
	if($ok) {
		$ret = array('img'=>str_replace("Json.",".",substr($fileNames[$pos],strlen($inode)+1)),'dir'=>str_replace("../galeria/","galeria/",$dir),'r'=>$r);
	}
	return $ret;
}
?>