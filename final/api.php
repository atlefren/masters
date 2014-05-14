<?php
/**
*
* This is the API front-end used by JavaScript to render the map in ShowMap.php
* it returns XML formatted data upon requests specified by parameters
* 
* requests on the form 
*  api.php&type=[1]
*  where [1] is the type of data you want. Recognized types are:
* 	- routes
* 		this is basic route (line) XML
* 		params: none (only returns all routes)
* 	- consists
* 		the DNT routes that uses a specified line
* 		params: id (required), id (gid) of the line in question
* 	- images
* 		this is basic image XML
* 		params: id id of images to return XML for
* 				near id of a line to get images near that
* 				none: gets XML for all images in DB
**/

require_once("/var/www/atle/final/code/php/ImageXML.php");
require_once("/var/www/atle/final/code/php/RouteXML.php");

//get parameters
$type = $_GET['type'];
$id = $_GET['id'];
$near = $_GET['near'];
$treshold = $_GET['treshold'];

//routes requested
if($type == 'routes') {
	header("Content-Type: text/xml");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	//return route XML
	
	//get all routes
	$data = array('all'=>true);
	$xml = new RouteXML($data);
	echo $xml->getRouteXML();	
}

//route names requested
else if($type == 'consists'){
	if($id){
		header("Content-Type: text/xml");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		$data = array('gid' => $id);
		$xml = new RouteXML($data);
		echo $xml->getRelatedRoutesXML();
	}
}

//images requested
else if ($type == 'images'){
	header("Content-Type: text/xml");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	
	//get image(s) with specified id(s)
	if($id){
		$data = array('ids' => array($id));
		$xml = new ImageXML($data);
		echo $xml->getImageXML();
	}
	//get images near a route
	else if($near){
		$data = array('near' => true, 'routeId' => $near, 'treshold'=>$treshold);
		$xml = new ImageXML($data);
		echo $xml->getImageXML();		
	}
	//get all images
	else {
		$data = array('all'=>true);
		$xml = new ImageXML($data);
		echo $xml->getImageXML();
	}
}

//unknown type (error)
else {
	echo "unknown type!\n";
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