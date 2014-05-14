<?php
function convertDistance($distance){

	if($distance <1000){
		//under 1 km, displaying meters rounded to nearest 10 meters
		return round($distance,-1) . " m";
	}
	else {
		//over 1 km, display km with one decimal
		return str_replace(".",",",round($distance/1000,1) . " km");
	}
}

function resize($img, $thumb_width, $newfilename) 
{ 
  $max_width=$thumb_width;

    //Check if GD extension is loaded
    if (!extension_loaded('gd') && !extension_loaded('gd2')) 
    {
        trigger_error("GD is not loaded", E_USER_WARNING);
        return false;
    }

    //Get Image size info
    list($width_orig, $height_orig, $image_type) = getimagesize($img);
    
    switch ($image_type) 
    {
        case 1: $im = imagecreatefromgif($img); break;
        case 2: $im = imagecreatefromjpeg($img);  break;
        case 3: $im = imagecreatefrompng($img); break;
        default:  trigger_error('Unsupported filetype!', E_USER_WARNING);  break;
    }
    
    /*** calculate the aspect ratio ***/
    $aspect_ratio = (float) $height_orig / $width_orig;

    /*** calulate the thumbnail width based on the height ***/
    $thumb_height = round($thumb_width * $aspect_ratio);
    

    while($thumb_height>$max_width)
    {
        $thumb_width-=10;
        $thumb_height = round($thumb_width * $aspect_ratio);
    }
    
    $newImg = imagecreatetruecolor($thumb_width, $thumb_height);
    
    /* Check if this image is PNG or GIF, then set if Transparent*/  
    if(($image_type == 1) OR ($image_type==3))
    {
        imagealphablending($newImg, false);
        imagesavealpha($newImg,true);
        $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
        imagefilledrectangle($newImg, 0, 0, $thumb_width, $thumb_height, $transparent);
    }
    imagecopyresampled($newImg, $im, 0, 0, 0, 0, $thumb_width, $thumb_height, $width_orig, $height_orig);
    
    //Generate the file, and rename it to $newfilename
    switch ($image_type) 
    {
        case 1: imagegif($newImg,$newfilename); break;
        case 2: imagejpeg($newImg,$newfilename);  break;
        case 3: imagepng($newImg,$newfilename); break;
        default:  trigger_error('Failed resize image!', E_USER_WARNING);  break;
    }
 
    return $newfilename;
}

/*
Copyright (c) 2009 Atle Frenvik Sveen

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

*/
?>