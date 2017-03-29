<?php

$tmp = file_get_contents("../template/googlesearch.txt");
$googlesearch = file_get_contents("../template/googlesearch.html");

$tmp = str_replace ( '{S_GOOGLE_SEARCH}', $googlesearch, $tmp );
echo $tmp;

?>