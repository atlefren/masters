<?php
/**
 * This is the page for placing images on the map, it serves as front-end for the ImageCollection class
 * and uses JavaScript rather heavily. 
 * 
 * This page must be called from uploadImages.php in order to have some images to georeference..
 * 
 */
require_once("/var/www/atle/final/code/php/ImageCollection.php");

if($_GET['debug']=='true'){
	$debug = "true";
}
else {
	$debug = "false";
}

//initially no error
$error = false;

//check if any data is submitted
if(sizeof($_FILES) != 0){
	//make image collection
	$collection = new ImageCollection($_FILES);
} 
else {
	//indicate error
	$error = true;
}

include("style/header.php");
?>
	<!-- Google Maps -->
	<script src="http://maps.google.com/maps?file=api&amp;v=2.123&amp;key=INSERT GOOGLE MAPS KEY" type="text/javascript"></script>
		
	<!-- internal JavaScript -->
	<script src="code/js/upload/search.js" type="text/javascript"></script>
	<script src="code/js/upload/manageMarkers.js" type="text/javascript"></script>
	<script src="code/js/upload/editDom.js" type="text/javascript"></script>
	<script src="code/js/upload/mapFunctions.js" type="text/javascript"></script>
	<script src="code/js/upload/imageUpdates.js" type="text/javascript"></script>
	
	<!-- The map icon maker (from google)-->
	<script src="code/js/external/mapiconmaker.js" type="text/javascript"></script>
	
	<!-- prototype -->
	<script src="code/js/external/prototype-1.6.0.2.js" type="text/javascript"></script>
	
	<script type="text/javascript">
		//the map itself
		var map;
		//markers on the map
		var markers = Array();
		
		//debug OFF
		var debug = <?php echo $debug; ?>;
		if(debug){
			GLog.write("debug mode ON");
		}
	</script>
</head>
<body>
<div id="wrap">

	<div id="header">
		<h1>Upload test</h1>
	</div>	
	<?php 
	//we have no error, proceed..
	if(!$error){ 
		?>
	<!-- Sidebar div START -->
	<div id="sidebar">
		<form action="saveImages.php" method="post" enctype="multipart/form-data" name="saveImages" id="saveImages">		
		<?=$collection->getImageBoxes() ?>

		<div class="uploadimage" id="meta">	
			
		</div>
		
		<div class="uploadimage" id="save">	
			<input type="button" name="saveBtn" id="saveBtn" value="Lagre.." onClick="formSubmit('check',<?=$collection->getNumImages() ?>);"/>
			</FORM>
		</div>		
	</div>
	<!-- Sidebar div END -->
	
	<div id="map">
	</div>
	
	<!--setting up the map -->
	<script type="text/javascript">				
		//initialize the map
		<?=$collection->getMapInit() ?>
		
		//setup the markers if any
		<?=$collection->getMapMarkerSetup() ?>
	</script>

<?php
	}
else {
	//indicate that something went wrong
	echo "en feil oppstod (du lasta ikke opp noen filer?)<br>";
	echo "<a href=\"uploadfile.php\">prøv igjen?</a>";
}

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