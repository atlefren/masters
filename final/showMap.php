<?php
/**
 * Displays a full-screen map to browse routes and images
 * 
 * 
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">

<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="http://geomatikk.eksplisitt.net/atle/style/style.css" type="text/css" media="screen" />

	    <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=INSERT GOOGLE MAPS KEY" type="text/javascript"></script>
		<script src="code/js/external/lightbox/lightbox.js" type="text/javascript"></script>
		<script src="code/js/external/markermanager.js" type="text/javascript"></script>
		
	    <script src="code/js/mapSetup.js" type="text/javascript"></script>
	    <script src="code/js/xmlParse.js" type="text/javascript"></script>
	    <script src="code/js/routes.js" type="text/javascript"></script>
	    <script src="code/js/images.js" type="text/javascript"></script>
	    <script src="code/js/markers2.js" type="text/javascript"></script>
		
    	<script type="text/javascript">
    //<![CDATA[
    
	//the map
    var map;
    
    var gRoutes = Array();
    
    var gImages = Array();
    
    var gMarkers = Array();
    
    var markerManager;
    
    var aImage,aRoute;
    
    function load() {
    	
    	initLightbox();
    	if (GBrowserIsCompatible()) {
    		//setup
    		
    		//getParamsFromPHP();
    		
    		<?php
    		//check if image is specified..
    		if($_GET['image']){
    			echo "setupMap(" . $_GET['image'] . ",null);";
    		}
    		else if($_GET['route']){
    			echo "setupMap(null," . $_GET['route'] . ");";
    		}
    		else {
    			echo "setupMap();";
    		}
    		
    		?>
    		
    	}
    }
    //]]>
    </script>
</head>
<body  onload="load()" onunload="GUnload()">

	<!-- Wrap div START -->
	<div id="wrap">
	
	<div id="header">
		<h1>GMap Test</h1>
	</div>	
	
	<div id="sidebar">
		<div id="images">
		
		</div>
	</div>
	
	<div id="map">
	</div>

<!-- Wrap div END -->
</div>
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