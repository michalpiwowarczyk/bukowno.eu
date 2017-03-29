<?php

require("const.php");

$f_ulubione = "../raporty/inne/ulubione.txt";
if( file_exists ( $f_ulubione ) ) {
	$ulubione = file_get_contents($f_ulubione);
} else $ulubione = "";

$ulubione .= "d=".$_REQUEST['d']."&f=".$_REQUEST['f']."\n";

file_put_contents($f_ulubione,$ulubione);
Header("Location: ../index.php?d=".$_REQUEST['d']."&f=".$_REQUEST['f']);

?>