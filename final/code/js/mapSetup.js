/**
  * Sets up the map. Centered on Norway. Gets images or routes if these are specified
  *
  * @param string image image to focus on
  * @param string route route to focus on
  */
function setupMap(image,route){
	map = new GMap2(document.getElementById("map"));

	map.addControl(new GLargeMapControl());
	map.addControl(new GMapTypeControl());
	map.setCenter(new GLatLng(65.5,13.0), 5);
	
	map.enableScrollWheelZoom();

	controlRouteZoom();
	
	if(image){
		aImage = image;
	}
	else if(route){
		aRoute = route;
	}
	else {
		aImage = null;
		aRoute = null;
	}
	
	addAllRoutes();
	addAllImages();
}

/**
  * Adds all routes to the map by fetching them from the API
  *
  */
function addAllRoutes() {	
	
	//api url
	var apiUrl = "http://geomatikk.eksplisitt.net/atle/final/api.php";
	
	//create request
	var request = GXmlHttp.create();
	
	//get xml
	request.open("GET", apiUrl + "?type=routes" , true);	
	
	//wait for return
	request.onreadystatechange = function() {
		if (request.readyState == 4) {
			//parse the XML
			parseRouteXML(request.responseXML);
  		}	
	}
	//finish
	request.send(null);	
}

/**
  * Adds all images to the map by fetching them from the API
  *
  */
function addAllImages(){
	//GLog.write("adding images");
	
	//api url
	var apiUrl = "http://geomatikk.eksplisitt.net/atle/final/api.php";
	
	//create request
	var request = GXmlHttp.create();
	
	//get xml
	request.open("GET", apiUrl + "?type=images" , true);	
	
	//wait for return
	request.onreadystatechange = function() {
		if (request.readyState == 4) {
			//parse the XML
			parseImageXML(request.responseXML);
  		}	
	}
	//finish
	request.send(null);	
}

/**
  * Ensures that routes are hidden when zoom-level is less than 9 
  * (rather crude way of doing it..)
  *
  */
function controlRouteZoom(){
	GEvent.addListener(map, "zoomend", function(oldLevel,newLevel) {
		if((newLevel >= 9) && (oldLevel < 9 )) {	
			showRoutes();
		}
		else if ((newLevel < 9 ) && (oldLevel >= 9)){
			hideRoutes();
		}
	});
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