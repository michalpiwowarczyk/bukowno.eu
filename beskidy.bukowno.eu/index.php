<?

$utils_dir="utils/";
require($utils_dir."const.php");
require($utils_dir."functions.php");
require($utils_dir."tar.php");

@mkdir($thumb_dir);
$ok = false;
$prop = "";
$showPhoto = false;
$byl_tar = false;
$b_json=false;
$json_r = "";
$curr_dir = getcwd();

if(isset($_REQUEST['j'])) {
	$b_json=true;
	$s_json_type=$_REQUEST['j'];
	if(isset($_REQUEST['r'])) {
		$json_r = $_REQUEST['r'];
		if(!($json_r=="../bukowno.eu.SE")) {
			$json_r = "";
		}			
	}
} else {
	$s_json_type="";
}

if(isset($_REQUEST['d']))
	$s_dir=$_REQUEST['d'];
else
	$s_dir=$start_dir;
if(isset($_REQUEST['f']))
	$s_photo=$_REQUEST['f'];
else
	$s_photo="";
if(isset($_REQUEST['g']))
	$get_photo=$_REQUEST['g'];
else
	$get_photo="";

if(!empty($get_photo)) {
	if(file_exists($get_photo)) {
		if(isJPG($get_photo)) {			
			header ("Location: ".str_replace("index.php?g=","",$_SERVER['REQUEST_URI']));
			exit(0);
		}
	} else {
		$file_parts = pathinfo($get_photo); 
		if(is_tar($file_parts["dirname"])) {
			$tmpfname = extract_from_tar($file_parts["dirname"],$file_parts["basename"],false);
			$fp = fopen($tmpfname, 'rb');
			header("Content-Type: image/jpeg");
			header("Content-Length: " . filesize($tmpfname));
			fpassthru($fp);
			fclose($fp);
			exit(0);
		}
	}
}
	
if(!empty($s_photo)) {
	if(file_exists($s_dir."/".$s_photo) or is_tar($s_dir)) {
		$showPhoto = true;
		$ok = true;

		$pozycja = -1;
		$lp = 0;
		$handler = opendir($s_dir);
		if($handler) {
			while ($file = readdir($handler)) {
				$f = $s_dir."/".$file;
				if($file == 'meta.properties') {
					$ok = true;
					$prop = file_get_contents($f);
				}
				if($file == 'galeria.tar') {
					$tar_lists = getTarList($s_dir.'/galeria.tar');
					for ($i=0; $i<sizeof($tar_lists); $i++) {
						$results[$lp]['time'] = $tar_lists[$i]['time'];
						$results[$lp]['file'] = $tar_lists[$i]['filename'];
						$results[$lp]['filesize'] = $tar_lists[$i]['filesize'];
						++$lp;
					}
					$byl_tar = true;
				}
				if ($file != '.' && $file != '..' && $file != 'index.php' && $file != 'meta.properties' && $file != 'galeria.tar') {
					if(!is_dir($f)) {
						if(isJPG($file)) {
							$results[$lp]['file'] = $file;
							$results[$lp]['time'] = photoTime($f);
							$results[$lp]['filesize'] = fsize($f);
							++$lp;
						}
					}
				}
			}
			closedir($handler);
		}
		usort($results, 'cmp_dirs_mtime');
		$i = 0;
		$jest = false;				
		while($i<$lp) {		
			$value = $results[$i];
			if($value['file']==$s_photo) {
				$jest = true;
				break;
			}
			++$i;
		}			
		if($jest) {
			$pos = $i;
			if(isset($_REQUEST['k'])) {
				if($_REQUEST['k']=="p" or $_REQUEST['k']=="n") {
					if($_REQUEST['k']=="n") {
						$pos=$i+1;
						if($pos==$lp) $pos=0;					
					}
					if($_REQUEST['k']=="p") {
						$pos=$i-1;
						if($pos==-1) $pos=$lp-1;					
					}
				}
			}
			$s_photo=$results[$pos]['file'];
		}
	}
}

if(!$showPhoto) {
	if(!isset($_REQUEST['ch'])) {
		require($utils_dir."galerie.php");
		changeHTML($s_dir,$template_dir);
	}
	$lp = 0;	
	$byl_jpg = false;
	if(!empty($json_r)) {
		$b_chdir = chdir($json_r);
		if(!$b_chdir) {
			chdir($curr_dir);
			$json_r = "";
		}			
	}
	$handler = opendir($s_dir);
	if($handler) {
		$byl_katalog = false;		
		while ($file = readdir($handler)) {
			$f = $s_dir."/".$file;
			if($file == 'meta.properties') {
				$prop = file_get_contents($f);
				$ok = true;
			}						
			if($file == 'galeria.tar') {
				$tar_lists = getTarList($s_dir.'/galeria.tar');
				for ($i=0; $i<sizeof($tar_lists); $i++) {						
					$results[$lp]['dir'] = false;
					$results[$lp]['time'] = $tar_lists[$i]['time'];
					$results[$lp]['d'] = $s_dir;
					$results[$lp]['file'] = $tar_lists[$i]['filename'];
					$results[$lp]['filesize'] = $tar_lists[$i]['filesize'];
					++$lp;
				}
				$byl_tar = true;
			}						
		    if ($file != '.' && $file != '..' && $file != 'index.php' && $file != 'meta.properties' && $file != 'galeria.tar') {
				if(is_dir($f)) {
					$byl_katalog = true;
					$results[$lp]['dir'] = true;
					$results[$lp]['time'] = 0;
					$results[$lp]['d'] = $s_dir;
					$results[$lp]['file'] = $file;
					$results[$lp]['filesize'] = 0;
				} else {
					if(isJPG($f)) {
						$results[$lp]['dir'] = false;
						$results[$lp]['time'] = photoTime($f);
						$results[$lp]['d'] = $s_dir;
						$results[$lp]['file'] = $file;
						$results[$lp]['filesize'] = filesize($f);
						$byl_jpg = true;
					}
				}
				++$lp;
			}
		}
		closedir($handler);
		// Tworzenie TARa
		/*
		if(!$byl_tar && $byl_jpg) {
			$handler = opendir($s_dir);
			if($handler) {
				$phar_arch = new PharData($s_dir.'/galeria.tar');
				while ($file = readdir($handler)) {
					$f = $s_dir."/".$file;
					if ($file != '.' && $file != '..' && $file != 'index.php' && $file != 'meta.properties' && $file != 'galeria.tar') {
						if(!is_dir($f)) {
							if(isJPG($f)) {
								$phar_arch->addFile($f,$file);
							}
						}				
					}
				}
				closedir($handler);			
			}
		}
		*/
	}
}

$tmpfname="";
if($ok) {
	$prop_array = AnalyzeProperties($prop);	
	$page = file_get_contents($curr_dir."/".$template_dir."page.txt");
	$slowa_kluczowe = file_get_contents($curr_dir."/".$template_dir."slowakluczowe.txt");
	$przypis = getPrzypis($curr_dir."/");
	$page_tr = file_get_contents($curr_dir."/".$template_dir."page_table_tr.txt");
	$page_td = file_get_contents($curr_dir."/".$template_dir."page_table_td.txt");
	$folder_tail = file_get_contents($curr_dir."/".$template_dir."folder_tail.txt");
	$isnew = file_get_contents($curr_dir."/".$template_dir."is_new.txt");
	
	$json_title_template = file_get_contents($curr_dir."/".$template_dir."page_photo_bottom_json.txt");	

	$arr_json = array('url' => "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'], 'directory' => $_REQUEST['d'], 'title' => "", 'opis' => "", 'liczba_zdjec' => 0, 'liczba_wycieczek' => 0);
	$arr_json_foldery = array();
	$arr_json_zdjecia = array();
	
	if(!$showPhoto) {
		if($byl_katalog)
			usort($results, 'cmp_dirs_name');
		else
			usort($results, 'cmp_dirs_mtime');
		
		$s_tr = $page_tr;
		$s_tr_all = "";
		$z = 0;
		$lz = 0;

		for ($i=0; $i<count($results); ++$i) {
			$value = $results[$i];
			$s_td = $page_td;		

			if($value['dir']) {
				$folder_props = getFolderProperties($value['d'].'/'.$value['file']);
				if(!empty($folder_props['description']))
					$folder_title=$folder_props['description'];
				else
					$folder_title=$folder_props['title'];
				$folder_title=$folder_props['title'];				
				if(!empty($folder_props['folderIcon'])) {
					$fi = getFolderImage($value['d'].'/'.$value['file'],$folder_props['folderIcon'],true,false,'');
					$json_tmp = getFolderImage($value['d'].'/'.$value['file'],$folder_props['folderIcon'],false,false,'');
				} else {
					$fi = $folderImage;
					$json_tmp = $folderImageJSON;					
				}				
				$s_zdjecia = getFolderZdjecia($value['d'].'/'.$value['file']);
				$s_wycieczki = getFolderWycieczki($value['d'].'/'.$value['file']);
				$s_td = str_replace('{S_FOLDER_TAIL}',$folder_tail,$s_td);			
				$s_td = str_replace('{S_TD_FOLDER_TITLE}',$folder_title,$s_td);
				$s_td = str_replace('{S_FOLDER_ZDJECIA}',$s_zdjecia,$s_td);
				$s_td = str_replace('{S_FOLDER_WYCIECZKI}',$s_wycieczki,$s_td);						
				$s_td = str_replace('{S_DIR}',$value['d'].'/'.$value['file'],$s_td);
				$s_td = str_replace('{S_FOLDER_IMAGE}',$fi,$s_td);
				if(is_new($value['d'].'/'.$value['file']))
					$s_td = str_replace('{S_IS_NEW}',$isnew,$s_td);
				$arr_json_folder = array ( 'directory' => $value['d'].'/'.$value['file'], 'img' => "../".$json_tmp, 'opis' => $folder_title, 'liczba_zdjec' => trim($s_zdjecia), 'liczba_wycieczek' => trim($s_wycieczki) );
				$arr_json_foldery[] = $arr_json_folder;
			} else {
				if($byl_tar) {
					$fi = getThumbnailImageFromTar($value['d'],$value['file'],true);
					$json_tmp = getThumbnailImageFromTar($value['d'],$value['file'],false);
				} else {
					$fi = getFolderImage($value['d'],$value['file'],true,false,'');
					$json_tmp = getFolderImage($value['d'],$value['file'],false,false,'');
				}
				$s_td = str_replace('{S_FOLDER_TAIL}',"",$s_td);
				$s_td = str_replace('{S_TD_FOLDER_TITLE}',"",$s_td);
				$s_td = str_replace('{S_FOLDER_ZDJECIA}',"0",$s_td);
				$s_td = str_replace('{S_FOLDER_WYCIECZKI}',"0",$s_td);
				$s_td = str_replace('{S_DIR}',$value['d'].'&f='.$value['file'],$s_td);
				$s_td = str_replace('{S_FOLDER_IMAGE}',$fi,$s_td);
				++$lz;
				// Przygotowanie TITLE do zdjecia dla JSON-a
				$json_page_photo_bottom = "";
				if($b_json) {
					$json_s_dir = $value['d'];
					$json_s_photo = $value['file'];
					
					$json_page_photo_bottom = $json_title_template;
					
					$json_photo = getPhotoParameters(0,$json_s_photo,$json_s_dir,$results,true);
					
					if(is_tar($json_s_dir)) {
						$json_tmpfname = extract_from_tar($json_s_dir,$json_s_photo,false);
						$json_camera = exifData($json_tmpfname);
					} else {
						$json_camera = exifData($json_s_dir."/".$json_s_photo);
					}
					
					$json_page_photo_bottom = str_replace ( "{S_PHOTO_NUMBER}", $lz, $json_page_photo_bottom );
					$json_page_photo_bottom = str_replace ( "{S_PHOTO_NUMBERS}", getLiczbaZdjecwFolderze($json_s_dir), $json_page_photo_bottom );
					$json_page_photo_bottom = str_replace ( "{S_PHOTO_NAZWA_PLIKU}", $json_photo['name_zdjecia'], $json_page_photo_bottom );
					$json_page_photo_bottom = str_replace ( "{S_PHOTO_DATA}", $json_camera['date'], $json_page_photo_bottom );
					$json_page_photo_bottom = str_replace ( "{S_PHOTO_ROZMIAR}", $json_photo['rozmiar_zdjecia'], $json_page_photo_bottom );
					if($json_camera['make']=="Unavailable")
						$json_aparat = "";
					else
						$json_aparat = " | Aparat: " . $json_camera['make'] . " " . $json_camera['model'];
					$json_aparat = str_replace ("Canon Canon", "Canon", $json_aparat );
					$json_page_photo_bottom = str_replace ( "{S_PHOTO_APARAT}", $json_aparat, $json_page_photo_bottom );
					$json_page_photo_bottom .= " | by Michał Piwowarczyk";
				}				
				
				$arr_json_zdjecie = array ( 'href' => '?g='.$value['d'].'/'.$value['file'], 'img' => "../".$json_tmp, 'title' => $json_page_photo_bottom );
				$arr_json_zdjecia[] = $arr_json_zdjecie;
			}
			$s_td = str_replace('{S_IS_NEW}',"",$s_td);

			$s_td = str_replace('{S_TD_SPAN}',"{S_TD_SPAN_".($z+1)."}",$s_td);
			$s_tr = str_replace('{S_PAGE_TD_'.($z+1).'}',$s_td,$s_tr);
			++$z;
			if($z==$td_num) {
				for($zz=1;$zz<=$td_num;++$zz)
					$s_tr = str_replace("{S_TD_SPAN_".$zz."}","",$s_tr);
				$s_tr_all .= $s_tr;
				$s_tr = $page_tr;
				$z = 0;
			}
		}
		if($z>0) {
			for($zz=1;$zz<=$z-1;++$zz)
				$s_tr = str_replace("{S_TD_SPAN_".$zz."}","",$s_tr);
			$s_tr = str_replace("{S_TD_SPAN_".$z."}",'colspan="'.($td_num-$z+1).'"',$s_tr);
			for($i=$z;$i<=$td_num;++$i) {
				$s_tr = str_replace('{S_PAGE_TD_'.($i).'}','',$s_tr);
			}
			$s_tr_all .= $s_tr;
		}
		
		if($s_dir==$start_dir) {
			$page = str_replace ( "{S_TITLE_TABLE}", "", $page );
			$page = str_replace ( "{S_PAGE_UP}", "", $page );
			$show_properties = "";
		} else {
			$page = str_replace ( "{S_TITLE_TABLE}", file_get_contents($curr_dir."/".$template_dir."title_table.txt"), $page );		
			$page = str_replace ( "{S_PAGE_UP}", file_get_contents($curr_dir."/".$template_dir."page_up.txt"), $page );
			$up_dir = getUpDir($s_dir);
			$page = str_replace ( "{S_UP_DIR}", $up_dir, $page );
			$props = getFolderProperties($s_dir);
			$properties = $props;
			if(!empty($props['description'])) {
				$page = str_replace ( "{S_TITLE_DESCRIPT}", $props['description'], $page );
				$arr_json['opis'] = $props['description'];
				$arr_json['opis'] = str_replace("utils/kml.php","../beskidy.bukowno.eu/utils/kml.php",$arr_json['opis']);
				$arr_json['opis'] = str_replace("raporty/pdf_html","../beskidy.bukowno.eu/raporty/pdf_html",$arr_json['opis']);
			} else {
				$page = str_replace ( "{S_TITLE_DESCRIPT}", "{S_TITLE}", $page );
				$arr_json['opis'] = $arr_json['title'];
			}
			$page = str_replace ( "{S_TITLE_DESCRIPT_STYLE}", "comment", $page );		
			$show_properties = ShowProperties($properties);
		}
		$page = str_replace ( "{S_PAGE_INDX}", "", $page );
		$page = str_replace ( "{S_PAGE_NEXT_PREV}", "", $page );		
		$page = str_replace ( "{S_PAGE_PHOTO_BOTTOM}", "", $page );		
		$s_mapa = "";
		$page = str_replace ( "{PRAWA_AUTORSKIE_EMAIL}", $pa_email, $page );		
		$page = str_replace ( "{PRAWA_AUTORSKIE}", $pa_autor, $page );		
		if($lz>0)
			$page = str_replace ( "{S_LICZBA_ZDJEC}", $lz." zdjęć | ", $page );				
		else
			$page = str_replace ( "{S_LICZBA_ZDJEC}", "", $page );
		$arr_json['foldery'] = $arr_json_foldery;
		$arr_json['zdjecia'] = $arr_json_zdjecia;
		if($lz>0) {
			$arr_json['liczba_zdjec'] = $lz;
		}
	} else {		
		$page = str_replace ( "{S_PAGE_UP}", "", $page );
		$page = str_replace ( "{S_PAGE_INDX}", file_get_contents($curr_dir."/".$template_dir."page_indx.txt"), $page );
		$page = str_replace ( "{S_PAGE_NEXT_PREV}", file_get_contents($curr_dir."/".$template_dir."page_next_prev.txt"), $page );
		$page = str_replace ( "{S_PHOTO}", $s_photo, $page );
		
		$page = str_replace ( "{S_UP_DIR}", $s_dir, $page );
		$page = str_replace ( "{S_TITLE_TABLE}", file_get_contents($curr_dir."/".$template_dir."title_table.txt"), $page );		
		
		$s_mapa = file_get_contents($curr_dir."/".$template_dir."map_photo.txt");
	
		$photo = getPhotoParameters(-1,$s_photo,$s_dir,$results,false);
		if($photo['ret']) 
			$s_mapa = str_replace ( "{HREF_DO_ZDJECIA_PREV}", $photo['href_do_zdjecia'], $s_mapa );
		else
			$s_mapa = str_replace ( "{HREF_DO_ZDJECIA_PREV}", "index.php?d=".$s_dir."&f=".$s_photo."&k=p", $s_mapa );
			
		$photo = getPhotoParameters(1,$s_photo,$s_dir,$results,false);
		if($photo['ret']) 
			$s_mapa = str_replace ( "{HREF_DO_ZDJECIA_NEXT}", $photo['href_do_zdjecia'], $s_mapa );
		else		
			$s_mapa = str_replace ( "{HREF_DO_ZDJECIA_NEXT}", "index.php?d=".$s_dir."&f=".$s_photo."&k=n", $s_mapa );
		$s_mapa = str_replace ( "{S_DIR}", $s_dir, $s_mapa );		
		
		$s_tr_all = file_get_contents($template_dir."page_photo.txt");		
		$s_tr_all = str_replace ( "{NAME_ZDJECIA}", $s_photo, $s_tr_all );
		$s_tr_all = str_replace ( "{POLOZENIE_ZDJECIA}", $s_dir."/".$s_photo, $s_tr_all );
	
		if(is_tar($s_dir)) {
			$tmpfname = extract_from_tar($s_dir,$s_photo,false);
			$img = imagecreatefromjpeg($tmpfname);		
		} else
			$img = imagecreatefromjpeg( $s_dir."/".$s_photo );
		$photo_width = imagesx( $img );
		$photo_height = imagesy( $img );
		imagedestroy($img);
		$s_tr_all = str_replace ( "{S_PHOTO_WIDTH}", $photo_width, $s_tr_all );
		
		$s_photo_details = file_get_contents($curr_dir."/".$template_dir."page_photo_tr.txt");
		for($i=-5;$i<=5;++$i) {
			if($i==0) continue;
			$photo = getPhotoParameters($i,$s_photo,$s_dir,$results,true);

			if($photo['ret']) {
				$tmp_photo = $s_photo_details;
				$tmp_photo = str_replace("{HREF_DO_ZDJECIA}",$photo['href_do_zdjecia'],$tmp_photo);
				$tmp_photo = str_replace("{THUMB_DO_ZDJECIA}",$photo['thumb_do_zdjecia'],$tmp_photo);
				$tmp_photo = str_replace("{NAME_ZDJECIA}",$photo['name_zdjecia'],$tmp_photo);

				$img = imagecreatefromjpeg( $photo['thumb_do_zdjecia'] );
				$photo_width = imagesx( $img );
				$photo_height = imagesy( $img );
				if($photo_width>$photo_height) {
					$th_width=$thumb_width_2;
					$th_height=$thumb_height_2;
				} else {
					$th_width=$thumb_height_2;
					$th_height=$thumb_width_2;
				}
				imagedestroy($img);
				$tmp_photo = str_replace("{S_THUMB_WIDTH_2}",$th_width,$tmp_photo);
				$tmp_photo = str_replace("{S_THUMB_HEIGHT_2}",$th_height,$tmp_photo);
				$tmp_photo = str_replace("{NAME_ZDJECIA}",$photo['name_zdjecia'],$tmp_photo);
			} else
				$tmp_photo = "";
			$s_tr_all = str_replace ( "{S_PAGE_PHOTO_TR_".$i."}", $tmp_photo, $s_tr_all );			
		}
		// PASEK Z NUMERACJA ZDJEC
		$s_numeracja = "zdjęcie ";
		$pozycja = getPhotoPosition($s_photo,$results);		
		$odstep = 10;
		$start = $pozycja-$odstep;
		$stop = $pozycja+$odstep;
		if($start<0) { 
			$roznica=$start; 
			$start=0; 
			$stop+=(-$roznica); 
		}
		
		if($start>1) {
			$photo = getPhotoParameters(-$pozycja,$s_photo,$s_dir,$results,false);
			if($photo['ret'])
				$s_numeracja .= '|<a href="'.$photo['href_do_zdjecia'].'" alt="Pierwsza strona" title="Pierwsza strona"> &laquo; </a>';
		}
		if($start>0) {
			$photo = getPhotoParameters($start-$pozycja-1,$s_photo,$s_dir,$results,false);
			if($photo['ret'])
				$s_numeracja .= '|<a href="'.$photo['href_do_zdjecia'].'" alt="Poprzednia strona" title="Poprzednia strona"> < </a>';
		}

		$lp = 0;
		for($i=$start;$i<=$stop;++$i) {
			if($lp>=2*$odstep)
				continue;
			$photo = getPhotoParameters($i-$pozycja,$s_photo,$s_dir,$results,false);
			if($i==$pozycja) {
				$s_numeracja .= '| <span class="current"> '.($i+1).' </span>';
				++ $lp;
				continue;
			}			
			if($photo['ret']) {
				$s_numeracja .= '|<a href="'.$photo['href_do_zdjecia'].'"> '.($i+1).' </a>';
				++ $lp;
			}
		}

		if($i<count($results)-1) {
			$photo = getPhotoParameters($i-$pozycja-1,$s_photo,$s_dir,$results,false);
			if($photo['ret'])
				$s_numeracja .= '|<a href="'.$photo['href_do_zdjecia'].'" alt="Następna strona" title="Następna strona"> > </a>';
		}
		if($i<count($results)-2) {
			$photo = getPhotoParameters(count($results)-$pozycja-1,$s_photo,$s_dir,$results,false);
			if($photo['ret'])
				$s_numeracja .= '|<a href="'.$photo['href_do_zdjecia'].'" alt="Ostatnia strona" title="Ostatnia strona"> &raquo; </a>';
		}

		$page = str_replace ( "{S_TITLE_DESCRIPT}", $s_numeracja, $page );		
		$page = str_replace ( "{S_TITLE_DESCRIPT_STYLE}", "smalltxt", $page );		
		// PASEK Z NUMERACJA ZDJEC -- KONIEC
		
		$page_photo_bottom = file_get_contents($curr_dir."/".$template_dir."page_photo_bottom.txt");		
		$photo = getPhotoParameters(0,$s_photo,$s_dir,$results,true);
		if(is_tar($s_dir))
			$camera = exifData($tmpfname);
		else
			$camera = exifData($s_dir."/".$s_photo);			
		$page_photo_bottom = str_replace ( "{S_PHOTO_NUMBER}", $pozycja+1, $page_photo_bottom );		
		$page_photo_bottom = str_replace ( "{S_PHOTO_NUMBERS}", getLiczbaZdjecwFolderze($s_dir), $page_photo_bottom );		
		$page_photo_bottom = str_replace ( "{S_PHOTO_NAZWA_PLIKU}", $photo['name_zdjecia'], $page_photo_bottom );		
		$page_photo_bottom = str_replace ( "{S_PHOTO_DATA}", $camera['date'], $page_photo_bottom );		
		$page_photo_bottom = str_replace ( "{S_PHOTO_ROZMIAR}", $photo['rozmiar_zdjecia'], $page_photo_bottom );
		if($camera['make']=="Unavailable")
			$aparat = "";
		else
			$aparat = " | Aparat: " . $camera['make'] . " " . $camera['model'];
		$aparat = str_replace ("Canon Canon", "Canon", $aparat ) ;
		$page_photo_bottom = str_replace ( "{S_PHOTO_APARAT}", $aparat, $page_photo_bottom );		
		
		$page = str_replace ( "{S_PAGE_PHOTO_BOTTOM}", $page_photo_bottom, $page );		
		$show_properties = "";

		require($utils_dir."prawaautorskie.php");
		$prawa = PrawaAutorskie($s_dir,$s_photo);
		$page = str_replace ( "{PRAWA_AUTORSKIE_EMAIL}", $prawa['email'], $page );		
		$page = str_replace ( "{PRAWA_AUTORSKIE}", $prawa['autor'], $page );		

		if($_SERVER["SERVER_ADDR"]=="10.5.0.240")
			$ulubione_add = file_get_contents($curr_dir."/".$template_dir."page_photo_ulubione_add.txt");
		else
			$ulubione_add = "";
		$ulubione_add = str_replace ( "{S_DIR}", $s_dir, $ulubione_add );		
		$ulubione_add = str_replace ( "{S_PHOTO}", $s_photo, $ulubione_add );		
		$page = str_replace ( "{S_ULUBIONE_ADD}", $ulubione_add, $page );		
		$page = str_replace ( "{S_LICZBA_ZDJEC}", "", $page );
	}	

	$opis_gorny = explodeDirectory($s_dir,false);	
	
	$page = str_replace ( "{S_OPIS_GORNY}", $opis_gorny, $page );
	$page = str_replace ( "{S_SLOWA_KLUCZOWE}", $slowa_kluczowe, $page );
	$page = str_replace ( "{S_TITLE}", $prop_array['title'], $page );
	$page = str_replace ( "{S_PRZYPIS}", $przypis, $page );
	$page = str_replace ( "{S_PAGE_TR_S}", $s_tr_all, $page );		
	$page = str_replace ( "{S_PROPERTIES}", $show_properties, $page );		
	$page = str_replace ( "{S_MAPA}", $s_mapa, $page );		
	$page = str_replace ( "{OSTATNIA_AKTUALIZACJA}", ostatniaAktualizacja(), $page );		
	
	if($b_json) {
		$arr_json['okruszki'] = explodeDirectory($s_dir,true);
		$arr_json['title'] = $prop_array['title'];
		$arr_json['galeriaDir'] = $json_r;
		header('Content-Type: application/json');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
		echo json_encode($arr_json, JSON_HEX_AMP);
	} else {
		echo $page;
	}
}
?>
