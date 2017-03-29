<?php
function changeHTML($dir,$galeria_path) {	
	// TATRY WYSOKIE
	if($dir=="galeria/51_4_CentralneKarpatyZachodnie/514_5_TatryWysokie")
		header("Location: ".$galeria_path."galerie.html?tatrywysokie"); 
	// TATRY ZACHODNIE
	if($dir=="galeria/51_4_CentralneKarpatyZachodnie/514_5_TatryZachodnie")
		header("Location: ".$galeria_path."galerie.html?tatryzachodnie"); 
	// ALPY
	if($dir=="galeria/43_4_CentralneAlpyWschodnie")
		header("Location: ".$galeria_path."galerie.html?alpy1"); 
	if($dir=="galeria/43_4_PolnocneAlpyWapienne")
		header("Location: ".$galeria_path."galerie.html?alpy2"); 
	if($dir=="galeria/43_6_PoludnioweAlpyWapienne")
		header("Location: ".$galeria_path."galerie.html?alpy3"); 
	// GORY
	if($dir=="galeria")
		header("Location: ".$galeria_path."galerie.html?gory"); 
}
?>