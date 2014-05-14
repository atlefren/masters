<?php
/**
 * Displays a single image with additional information and lists of nearby routes, cabins and other images
 * 
 * Serves as a front-end for the DisplayImage class
 * 
 * If no image id is specified a (rather crude) gallery of all images in the DB is shown
 * 
 * 
 */

require_once("/var/www/atle/final/code/php/DisplayImage.php");
require_once("/var/www/atle/final/code/php/ImageGallery.php");

//get the image id
$imageId = $_GET['image'];
if($imageId){
	$image = new DisplayImage($imageId);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">

<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="http://geomatikk.eksplisitt.net/atle/style/style.css" type="text/css" media="screen" />

</head>
<body>
<?php
	//is image id specified?
	if($image){
?>

<!-- Wrap div START -->
<div id="wrap">

	<div id="header">
		<h1>Enkeltbilde</h1>
	</div>	
	
	
	<div id="image<?=$image->getId(); ?>" class="singleimage" style="width:800px;">
		
	<img src="<?=$image->getUrl(800); ?>" border="0" />
		
		<div id="imagemetadata">
			
			<div id="general_info" class="imagecontainer_left">
				<h4>Generell Info</h4>
				<p>Tittel: <em><?=$image->getTitle(); ?></em></p>

				<p>Fotograf: <em><?=$image->getPhotographer(); ?></em></p>
				<p>Årstid: <em><?=$image->getSeason(); ?></em></p>
				<p>Lokasjon: <em><?=$image->getMapLink(); ?></em></p>
			</div>
		
			<div id="nearby_routes" class="imagecontainer">
					<h4>Nærliggende Ruter</h4>
					<?=$image->getNearRoutes(); ?>
				</div>
			
				<div id="nearby_POIS" class="imagecontainer">
					<h4>Nærliggende Hytter</h4>
					<?=$image->getNearCabins(); ?>
				</div>
				
				<div id="nearby_images">
					<h4>Bilder nær dette bildet</h4>
					<?=$image->getNearImageGallery(); ?>
				</div>

			</div>
		</div>

	<!-- Wrap div END -->
	</div>
	<?php
	}
	else {
		//show gallery
		$gallery = new ImageGallery();
		$gallery->printGallery();
	}
	?>
	
</body>
</html>
<?php

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