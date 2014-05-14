<?php
/**
 * Saves an image to the database, copies it to permanent storage and displays a gallery of the uploaded images.
 * Must be called from georefImages.php, to ensure that there are POST-data to work with
 * 
 * Serves as a front-end for the SaveImage class
 * 
 */


/*
require_once("/var/www/atle/code/php/classes/DbQuery.php");
require_once("/var/www/atle/code/php/classes/ImageGallery.php");
require_once("/var/www/atle/code/php/functions/dbInsert.php");
*/

require_once("/var/www/atle/final/code/php/SaveImage.php");


$saveImages = new SaveImage($_POST);
if($saveImages->dbInsert()){
	$saveImages->moveImages();
	$saved = true;
}

include("style/header.php");
echo "\n";
?>
</head>
<body>

<!-- Wrap div START -->
<div id="wrap">

	<div id="header">
		<h1>Bildene lastet opp</h1>
	</div>	
	<div>
	<?php 
	if($saved){
		//display images
		$saveImages->showImages();
	}
	?>
	
	</div>
	
<?php
include("style/footer.php");

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