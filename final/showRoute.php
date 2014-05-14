<?php
/**
 * Shows a specified route, using a map and lists of related trips, nearby routes and cabins 
 * as well as a gallery of nearby images and basic facts and a description of the route.
 * 
 * Serves as a front-end for the DntRoute class, but uses JavaScript and google maps heavily..
 * 
 */

require_once("/var/www/atle/final/code/php/DntRoute.php");

//get route ID
$routeId = $_GET['route'];

if($routeId){
	$dntroute = new DntRoute($routeId);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">

<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="http://geomatikk.eksplisitt.net/atle/style/style2.css" type="text/css" media="screen" />
	<title><?=$dntroute->getName()?></title>
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=INSERT GOOGLE MAPS KEY" type="text/javascript"></script>

<script src="code/js/external/markermanager.js" type="text/javascript"></script>

<script src="code/js/staticMarkers.js" type="text/javascript"></script>
<script src="code/js/markers2.js" type="text/javascript"></script>
<script src="code/js/singleRoute.js" type="text/javascript"></script>

<script type="text/javascript">
//<![CDATA[

var markerHandler = Array();
var routeHandler = Array();
var parts = Array();
var tripParts = Array();
function loadMap(){
	if (GBrowserIsCompatible()) {
		
	//get all JSON-data needed (printed by php)	
	var json = Array();
	json['route'] = <?=$dntroute->getRouteParts() ?>;
	json['nearRoutes'] = <?=$dntroute->getNearRouteParts() ?>;
	json['images'] = <?=$dntroute->printNearImagesJson() ?>;
	json['cabins'] = <?$dntroute->printNearCabinsJson() ?>;
	
	
	parts = <?=$dntroute->printNearRoutePartsJson() ?>;
	tripParts = <?= $dntroute->printRelatedTripPartsJson() ?>;
		
	//setup the map
	showRoute(json);
	}
}
//]]>

</script>
</head>
<body onload="loadMap()" onunload="GUnload()">

<!-- Wrap div start -->
<div id="wrap">	

	<!-- BACKGROUND start -->	
	<div id="background">
		<div id="header">
			<h1><?=$dntroute->getName()?></h1>
		</div>			
		
		<!-- LEFTBOX start -->
		<div id="leftbox">
			
			<div id="map"></div>	
			
			<div id="infobox">
				<h4>Fakta:</h4>
				<?=$dntroute->printFactList() ?>
			</div>
			<div class="descriptionbox">
				<h4>Beskrivelse av ruten</h4>
				<?=$dntroute->getDescription() ?>
			</div>
			
			<div class="descriptionbox">
				<h4>Nærliggende bilder</h4>
				<?=$dntroute->printRelatedImagesGallery(4,0) ?>
			</div>
			
		<!-- LEFTBOX end -->	
		</div>
		
		<!-- RIGHTBOX start -->
		<div id="rightbox">
			
			<div class="infobox_right">
				<h4>Relaterte turer:</h4>
				<?=$dntroute->printRelatedTripsList() ?>
			</div>
			
			<div class="infobox_right">
				<h4>Nærliggende ruter:</h4>
				<?=$dntroute->printNearRoutesList() ?>
			</div>

			<div class="infobox_right">
				<h4>Nærliggende hytter:</h4>
				<?=$dntroute->printNearCabinsList() ?>
			</div>
		<!-- RIGHTBOX end -->				
		</div>
		<div id="footer"> </div>
	<!-- BACKGROUND end -->	
	</div>	
<!-- Wrap div END -->
</div>
</body>
</html>
<?php
}
else {
	echo "please specify a route..";
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