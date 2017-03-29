<?php

require_once("inc/trackstat/require.php");
$TrackStats = new TrackStatsGfx();

$gpxname = "../../raporty/inne/Przejscia/gpx/".$_REQUEST['name'].".gpx.gz";
$gzarray = gzfile($gpxname);
$gpxcontents = "";
for($i=0; $i<sizeof($gzarray); ++$i)
	$gpxcontents .= $gzarray[$i];
$gpx_xml = @simplexml_load_string($gpxcontents);

$TrackStats->SetTrackName($_REQUEST['name'].".gpx");  
foreach ($gpx_xml->trk as $track) {
  foreach ($track->trkseg as $tracksegment) {
    foreach ($tracksegment->trkpt as $trackpoint) {
       $lat = FALSE;
       $lon = FALSE;
       foreach($trackpoint->attributes() as $attr => $value) {
         if ($attr == "lat") $lat = (double) $value;
         if ($attr == "lon") $lon = (double) $value;
       }
       if (count($trackpoint->ele)) $ele = (int) $trackpoint->ele; else $ele = FALSE;
       if (count($trackpoint->time)) $timestamp = strtotime($trackpoint->time); else $timestamp = FALSE;
       $TrackStats->AddPoint(array("Latitude" => $lat, "Longitude" => $lon, "Elevation" => $ele, "Timestamp" => $timestamp));
    }
    $TrackStats->AddSegmentDelimiter();
  }
}

$img = $TrackStats->GetDistanceFigure();
//$img = $TrackStats->GetTimeFigure();
header('Content-Type: image/png');
imagepng($img);

?>