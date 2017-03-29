<?php

/**
 * This class extends parent class by graphical output
 * 
 * @package TrackStats
 * @license GNU GPL 
 **/


class TrackStatsGfx extends TrackStats {

    public $elevation_label = "Elevation";
    public $distance_label = "Distance";
    public $enable_elevation = TRUE;
    public $enable_distance = TRUE;



    /**
     * Returns figure with time axis
     * 
     * @return image_resource
     **/
    function GetTimeFigure() {
       // configuration
       $fig_size_x = 900;
       $fig_size_y = 300;
       $img_size_x = $fig_size_x + 100;
       $img_size_y = $fig_size_y + 90;
       $img_fig_offset_x = 55;
       $img_fig_offset_y = 50;
       $fig_time_num_steps = 20;
       $fig_time_step_scale = $fig_size_x / $fig_time_num_steps;
       $fig_ele_num_steps = 10;
       $fig_ele_step_scale = $fig_size_y / $fig_ele_num_steps;
       $fig_dist_num_steps = 10;
       $fig_dist_step_scale = $fig_size_y / $fig_dist_num_steps;
       
       // time axis range&scale calculation
       $difference = $this->GetStopTime() - $this->GetStartTime();
       $fig_time_step = (int) round($difference / ($fig_time_num_steps-1));
       if ($fig_time_step == 0) $fig_time_step = 1;
       else if ($fig_time_step < 10) $fig_time_step = 10;
       else if ($fig_time_step < 60) $fig_time_step = ceil($fig_time_step / 10) * 10;
       else if ($fig_time_step < 300) $fig_time_step = ceil($fig_time_step / 60) * 60;
       else if ($fig_time_step < 3600) $fig_time_step = ceil($fig_time_step / 300) * 300;
       else $fig_time_step = round($fig_time_step / 3600) * 3600;
       $fig_time_min = (floor($this->GetStartTime() / $fig_time_step)) * $fig_time_step;
       $fig_time_max = (ceil($this->GetStopTime() / $fig_time_step)) * $fig_time_step;
       $fig_time_num_steps = floor(($fig_time_max - $fig_time_min) / $fig_time_step); // optimizing number of steps
       $fig_time_step_scale = $fig_size_x / $fig_time_num_steps;
       $fig_time_pixel_scale = $fig_time_step / $fig_time_step_scale;
       
       // elevation axis range&scale calculation
       $difference = $this->GetMaxElevation() - $this->GetMinElevation();
       $fig_ele_step = (int) round($difference / ($fig_ele_num_steps-1));
       if ($fig_ele_step == 0) $fig_ele_step = 1;
       else if ($fig_ele_step < 5) $fig_ele_step = 5;
       else if ($fig_ele_step < 10) $fig_ele_step = 10;
       else if ($fig_ele_step < 100) $fig_ele_step = ceil($fig_ele_step / 10) * 10;
       else if ($fig_ele_step < 1000) $fig_ele_step = ceil($fig_ele_step / 50) * 50;
       else $fig_ele_step = ceil($fig_ele_step / 1000) * 1000;
       $fig_ele_min = (floor($this->GetMinElevation() / $fig_ele_step)) * $fig_ele_step;
       $fig_ele_max = (ceil($this->GetMaxElevation() / $fig_ele_step)) * $fig_ele_step;
       $fig_ele_pixel_scale = $fig_ele_step / $fig_ele_step_scale;
       
       // distance axis range&scale calculation
       $difference = $this->GetTotalDistance();
       $fig_dist_step = (int) round($difference / $fig_dist_num_steps);
       if ($fig_dist_step == 0) $fig_dist_step = 1;
       else if ($fig_dist_step < 1000) $fig_dist_step = 1000;
       else if ($fig_dist_step < 10000) $fig_dist_step = ceil($fig_dist_step / 1000) * 1000;
       else $fig_dist_step = ceil($fig_dist_step / 10000) * 10000;
       $fig_dist_min = 0;
       $fig_dist_max = (ceil($this->GetTotalDistance() / $fig_dist_step)) * $fig_dist_step;
       $fig_dist_pixel_scale = $fig_dist_step / $fig_dist_step_scale;
       
       // image preparation
       $img = imagecreate($img_size_x, $img_size_y);
       $img_color_background = imagecolorallocate($img, 255, 255, 255);
       $img_color_grid = imagecolorallocate($img, 200, 200, 200);
       $img_color_legend = imagecolorallocate($img, 0, 0, 0);
       $img_color_plot_ele = imagecolorallocate($img, 255, 0, 0);
       $img_color_plot_ele_intlv = imagecolorallocate($img, 255, 128, 128);
       $img_color_plot_dist = imagecolorallocate($img, 255, 255, 128);
       
       // background
       imagefill($img, 0, 0, $img_color_background);
       
       // distance plot
       if ($this->enable_distance) {
       for ($i=1; $i<$fig_size_x; $i++) {
          if ($fig_time_min+($i*$fig_time_pixel_scale) <= $this->GetStopTime()) {
             $dist = $this->GetTrackDistance(Array("time_start" => $fig_time_min, "time_stop" => $fig_time_min+($i*$fig_time_pixel_scale)));
             $x = $img_fig_offset_x + $i;
             $y_base = $img_size_y-$img_fig_offset_y - 1;
             $y_value = $img_size_y-$img_fig_offset_y - 1 - round(($dist)/$fig_dist_pixel_scale);;
             imageline($img, $x, $y_base, $x, $y_value, $img_color_plot_dist);
          }
       }
       }
       
       // vertical grid
       if ($this->enable_elevation) {
          imagettftext($img, 10, 90, $img_fig_offset_x-40, $img_size_y-$img_fig_offset_y-($fig_size_y/2)+50, $img_color_legend, "inc/font/courbd.ttf", $this->elevation_label." [m]");
          imageline($img, $img_fig_offset_x-44, $img_size_y-$img_fig_offset_y-($fig_size_y/2)+80, $img_fig_offset_x-44, $img_size_y-$img_fig_offset_y-($fig_size_y/2)+58, $img_color_plot_ele);
       }
       if ($this->enable_distance) {
          imagettftext($img, 10, 90, $img_fig_offset_x+$fig_size_x+37, $img_size_y-$img_fig_offset_y-($fig_size_y/2)+50, $img_color_legend, "inc/font/courbd.ttf", (($fig_dist_step >= 1000) ? $this->distance_label." [km]" : $this->distance_label." [m]"));
          imagefilledrectangle($img, $img_fig_offset_x+$fig_size_x+29, $img_size_y-$img_fig_offset_y-($fig_size_y/2)+80, $img_fig_offset_x+$fig_size_x+37, $img_size_y-$img_fig_offset_y-($fig_size_y/2)+58, $img_color_plot_dist);
       }
       for ($i=0; $i<$fig_ele_num_steps; $i++) {
          imagerectangle($img, $img_fig_offset_x, $img_size_y-$img_fig_offset_y-round($fig_ele_step_scale*$i), $img_fig_offset_x+$fig_size_x, $img_size_y-$img_fig_offset_y-round($fig_ele_step_scale*($i+1)), $img_color_grid);
          if (($i!=0) and ($this->enable_elevation)) imagestring($img, 2, ($img_fig_offset_x-23), ($img_size_y-$img_fig_offset_y-round($fig_ele_step_scale*$i)-7), ($fig_ele_min+$i*$fig_ele_step), $img_color_legend);
          if (($i!=0) and ($this->enable_distance))
             if ($fig_dist_step >= 1000) {
                imagestring($img, 2, ($img_fig_offset_x+$fig_size_x+5), ($img_size_y-$img_fig_offset_y-round($fig_ele_step_scale*$i)-7), ($i*$fig_dist_step/1000), $img_color_legend);
             } else {
                imagestring($img, 2, ($img_fig_offset_x+$fig_size_x+5), ($img_size_y-$img_fig_offset_y-round($fig_ele_step_scale*$i)-7), ($i*$fig_dist_step), $img_color_legend);
             }
       }
       // horizontal grid
       for ($i=0; $i<$fig_time_num_steps; $i++) {
          imagerectangle($img, $img_fig_offset_x+round($fig_time_step_scale*$i), $img_size_y-$img_fig_offset_y, $img_fig_offset_x+round($fig_time_step_scale*($i+1)), $img_size_y-$img_fig_offset_y-$fig_size_y, $img_color_grid);
          imagestring($img, 2, ($img_fig_offset_x+round($fig_time_step_scale*$i)-14), ($img_size_y-$img_fig_offset_y+2), date("H:i", ($fig_time_min+$i*$fig_time_step)), $img_color_legend);
       }
       
       // title
       imagettftext($img, 13, 0, $img_fig_offset_x, $img_size_y-$img_fig_offset_y-$fig_size_y-13, $img_color_legend, "inc/font/courbd.ttf", $this->GetTrackName());
       
       // elevation plot
       if ($this->enable_elevation) {
       $x_last = FALSE;
       $y_last = FALSE;
       for ($i=1; $i<$fig_size_x; $i++) {
          $ele = $this->GetElevationAverage(Array("time_start" => $fig_time_min+(($i-1)*$fig_time_pixel_scale), "time_stop" => $fig_time_min+($i*$fig_time_pixel_scale)));
          if ($ele) {
             $x = $i+$img_fig_offset_x;
             $y = $img_size_y-$img_fig_offset_y-round(($ele-$fig_ele_min)/$fig_ele_pixel_scale);
             if (($x_last != FALSE) and ($y_last != FALSE)) {
                imageline($img, $x, $y, $x_last, $y_last, $img_color_plot_ele_intlv);
                imagesetpixel($img, $x_last, $y_last, $img_color_plot_ele);
             }
             $x_last = $x;
             $y_last = $y;
          }
       }
       }
       
       return $img;
    }

    function GetDistanceFigure() {
       // configuration
       $fig_size_x = 900;
       $fig_size_y = 300;
       $img_size_x = $fig_size_x + 100;
       $img_size_y = $fig_size_y + 90;
       $img_fig_offset_x = 55;
       $img_fig_offset_y = 50;
       $fig_time_num_steps = 20;
       $fig_time_step_scale = $fig_size_x / $fig_time_num_steps;
       $fig_ele_num_steps = 10;
       $fig_ele_step_scale = $fig_size_y / $fig_ele_num_steps;
       $fig_dist_num_steps = 20;
       $fig_dist_step_scale = $fig_size_x / $fig_dist_num_steps;
       
       // time axis range&scale calculation
       $difference = $this->GetStopTime() - $this->GetStartTime();
       $fig_time_step = (int) round($difference / ($fig_time_num_steps-1));
       if ($fig_time_step == 0) $fig_time_step = 1;
       else if ($fig_time_step < 10) $fig_time_step = 10;
       else if ($fig_time_step < 60) $fig_time_step = ceil($fig_time_step / 10) * 10;
       else if ($fig_time_step < 300) $fig_time_step = ceil($fig_time_step / 60) * 60;
       else if ($fig_time_step < 3600) $fig_time_step = ceil($fig_time_step / 300) * 300;
       else $fig_time_step = round($fig_time_step / 3600) * 3600;
       $fig_time_min = (floor($this->GetStartTime() / $fig_time_step)) * $fig_time_step;
       $fig_time_max = (ceil($this->GetStopTime() / $fig_time_step)) * $fig_time_step;
       $fig_time_num_steps = floor(($fig_time_max - $fig_time_min) / $fig_time_step); // optimizing number of steps
       $fig_time_step_scale = $fig_size_x / $fig_time_num_steps;
       $fig_time_pixel_scale = $fig_time_step / $fig_time_step_scale;
       
       // elevation axis range&scale calculation
       $difference = $this->GetMaxElevation() - $this->GetMinElevation();
       $fig_ele_step = (int) round($difference / ($fig_ele_num_steps-1));
       if ($fig_ele_step == 0) $fig_ele_step = 1;
       else if ($fig_ele_step < 5) $fig_ele_step = 5;
       else if ($fig_ele_step < 10) $fig_ele_step = 10;
       else if ($fig_ele_step < 100) $fig_ele_step = ceil($fig_ele_step / 10) * 10;
       else if ($fig_ele_step < 1000) $fig_ele_step = ceil($fig_ele_step / 50) * 50;
       else $fig_ele_step = ceil($fig_ele_step / 1000) * 1000;
       $fig_ele_min = (floor($this->GetMinElevation() / $fig_ele_step)) * $fig_ele_step;
       $fig_ele_max = (ceil($this->GetMaxElevation() / $fig_ele_step)) * $fig_ele_step;
       $fig_ele_pixel_scale = $fig_ele_step / $fig_ele_step_scale;
       
       // distance axis range&scale calculation
       $difference = $this->GetTotalDistance();
       $difference = ceil($difference/1000.0)*1000.0;
       $fig_dist_step = round($difference / $fig_dist_num_steps);
       $fig_dist_min = 0;
       $fig_dist_max = (ceil($this->GetTotalDistance() / $fig_dist_step)) * $fig_dist_step;
       $fig_dist_pixel_scale = $fig_dist_step / $fig_dist_step_scale;
       $fig_dist_step = $fig_dist_step/1000.0;
       
       // image preparation
       $img = imagecreate($img_size_x, $img_size_y);
       $img_color_background = imagecolorallocate($img, 255, 255, 255);
       $img_color_grid = imagecolorallocate($img, 200, 200, 200);
       $img_color_legend = imagecolorallocate($img, 0, 0, 0);
       $img_color_plot_ele = imagecolorallocate($img, 255, 0, 0);
       $img_color_plot_ele_intlv = imagecolorallocate($img, 255, 128, 128);
       $img_color_plot_dist = imagecolorallocate($img, 255, 255, 128);
       
       // background
       imagefill($img, 0, 0, $img_color_background);
       
       // vertical grid
       imagettftext($img, 10, 90, $img_fig_offset_x-40, $img_size_y-$img_fig_offset_y-($fig_size_y/2)+50, $img_color_legend, "inc/font/courbd.ttf", $this->elevation_label." [m]");
       imageline($img, $img_fig_offset_x-44, $img_size_y-$img_fig_offset_y-($fig_size_y/2)+80, $img_fig_offset_x-44, $img_size_y-$img_fig_offset_y-($fig_size_y/2)+58, $img_color_plot_ele);
       for ($i=0; $i<$fig_ele_num_steps; $i++) {
          imagerectangle($img, $img_fig_offset_x, $img_size_y-$img_fig_offset_y-round($fig_ele_step_scale*$i), $img_fig_offset_x+$fig_size_x, $img_size_y-$img_fig_offset_y-round($fig_ele_step_scale*($i+1)), $img_color_grid);
          if ($i!=0) {
          	imagestring($img, 2, ($img_fig_offset_x-23),            ($img_size_y-$img_fig_offset_y-round($fig_ele_step_scale*$i)-7), ($fig_ele_min+$i*$fig_ele_step), $img_color_legend);
          	imagestring($img, 2, ($img_fig_offset_x+$fig_size_x+5), ($img_size_y-$img_fig_offset_y-round($fig_ele_step_scale*$i)-7), ($fig_ele_min+$i*$fig_ele_step), $img_color_legend);
          }
       }
       
       // horizontal grid
       imagettftext($img, 10, 0, $img_size_x-$img_fig_offset_x-($fig_size_x/2), $img_size_y-20, $img_color_legend, "inc/font/courbd.ttf", $this->distance_label." [km]");       
       for ($i=1; $i<=$fig_dist_num_steps; $i++) {
       	  if($i<$fig_dist_num_steps)
          	imagerectangle($img, $img_fig_offset_x+round($fig_dist_step_scale*$i), $img_size_y-$img_fig_offset_y, $img_fig_offset_x+round($fig_dist_step_scale*($i+1)), $img_size_y-$img_fig_offset_y-$fig_size_y, $img_color_grid);
          if ($i!=0) {
            if ($fig_dist_step >= 1000)
            	imagestring($img, 2, ($img_fig_offset_x+round($fig_dist_step_scale*$i)-14), ($img_size_y-$img_fig_offset_y+2), ($i*$fig_dist_step)/1000, $img_color_legend);
          	else
          		imagestring($img, 2, ($img_fig_offset_x+round($fig_dist_step_scale*$i)-14), ($img_size_y-$img_fig_offset_y+2), ($i*$fig_dist_step), $img_color_legend);
          }
       }
       
       // title
       imagettftext($img, 13, 0, $img_fig_offset_x, $img_size_y-$img_fig_offset_y-$fig_size_y-13, $img_color_legend, "inc/font/courbd.ttf", $this->GetTrackName());
       
       // elevation plot
       $x_last = FALSE;
       $y_last = FALSE;       
       for ($i=0; $i<$fig_size_x; $i++) {
          if ($fig_time_min+($i*$fig_time_pixel_scale) <= $this->GetStopTime()) {
	         $ele = $this->GetElevationAverage(Array("time_start" => $fig_time_min+(($i-1)*$fig_time_pixel_scale), "time_stop" => $fig_time_min+($i*$fig_time_pixel_scale)));
    	     if ($ele) {
             	$dist = $this->GetTrackDistance(Array("time_start" => $fig_time_min, "time_stop" => $fig_time_min+($i*$fig_time_pixel_scale)));
             	$x = $img_fig_offset_x+round(($dist)/$fig_dist_pixel_scale);
    	     	$y = $img_size_y-$img_fig_offset_y-round(($ele-$fig_ele_min)/$fig_ele_pixel_scale);
    	     	if (($x_last != FALSE) and ($y_last != FALSE)) {
                	imageline($img, $x, $y, $x_last, $y_last, $img_color_plot_ele_intlv);
                	imageline($img, $x, $y+1, $x_last, $y_last+1, $img_color_plot_ele_intlv);
                	imageline($img, $x, $y-1, $x_last, $y_last-1, $img_color_plot_ele_intlv);
                	imagesetpixel($img, $x_last, $y_last, $img_color_plot_ele);
	            }
				$x_last = $x;
				$y_last = $y;				
             }
          }
       }
       
       return $img;
    }
}
?>
