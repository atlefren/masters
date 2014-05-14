<?php
/**
 * This takes care of presenting the requested image from the image storage, 
 * optionally the image can be resized (but only to given widths). A sub-folder with the 
 * width must exist in order to return a resized version. If a resized image is already made, 
 * this is used.
 * 
 * Takes the parameter filename, which is a plain filename on the form filename.ext, no folders given, 
 * and the optional width-param as described above
 * 
 * Only works with jpg-images..
 * 
 */

//get paths and urls
require_once("/var/www/atle/final/code/php/configs.php");
$urls = new configClass(urlData());

//$path = "/media/disk1/www/data/img/online/";
$path = $urls->permDir();

//$resized_path = "/media/disk1/www/data/img/online/resized/";
$resized_path = $urls->resizedDir();

//check if filename is given
if (!$_GET[filename]) {
	trigger_error("No input image specified", E_USER_WARNING);
	exit();
}

//check if input file exists
$filename = $path . $_GET[filename];
if (!file_exists($filename)) {	
	trigger_error("File <b>" . $filename . "</b> does not exist.", E_USER_WARNING);
	exit();
}


//set image header
Header('Content-type: image/jpg');

if (!$_GET[width]) {
	//requesting original image, this should exist
	$image = $filename;
}
else {
	$width = $_GET[width];
	
	//check if width is allowed
	if (!file_exists($resized_path . $width)) {
		trigger_error("Cannot resize image to width " . $width . "px", E_USER_WARNING);
		exit();
	}

	$newfilename = $resized_path . $width . "/" . $_GET[filename];

	if(file_exists($newfilename)){
		//this image exists in the given width
		$image = $newfilename;
	}
	else {
		//we have to resize
		require_once($urls->imageResizeFunction());
		$image = resize($filename, $width, $newfilename);
	}
}

//get the image contents and display it..
$im = file_get_contents($image);
echo $im;

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