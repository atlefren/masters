/**
  * Parses an xml-string for routes and creates routes to display
  * 
  * Populates the gRoutes array
  * 
  * @param string xmlDoc the xml to parse
  */
function parseRouteXML(xmlDoc){
	
	//get route nodes from XML
	var routes = xmlDoc.documentElement.getElementsByTagName("line");
	
	//get the number of routes in xml
	var numRoutes = routes.length;
	if(numRoutes > 0){
		//get each route in the xml
		for (var i = 0; i < numRoutes; i++) {

			//get route id
			var id = routes[i].getElementsByTagName("id")[0].childNodes[0].nodeValue;

			//get route name
			//var name = routes[i].getElementsByTagName("name")[0].childNodes[0].nodeValue;
			var name = null;	
			//get the points that make up the route and store in an array
			var points = routes[i].getElementsByTagName("point");
			
			var pointArray = Array();
			for(var j = 0; j <points.length;j++){
				pointArray[j] = new GLatLng(parseFloat(points[j].getAttribute("lat")),parseFloat(points[j].getAttribute("lng")));
			}

			//create a route with id, name and the points, add it to the gRoutes array
			gRoutes[id] = createRoute(id,name,pointArray);
		}
	}

	//show the created routes if zoom-level is right
	if(map.getZoom()>= 12){
		showRoutes();
	}
	
	if(aRoute){
		focusOnRoute(aRoute);
	}
}

/**
  * Parses an xml-string for images and creates images to display
  * 
  * Creates a markermanager with the image markers
  * 
  * @param string xmlDoc the xml to parse
  */
function parseImageXML(xmlDoc){
	var images = xmlDoc.documentElement.getElementsByTagName("img");
	var numImages = images.length;
		if(numImages > 0){
			for (var i = 0; i < numImages; i++) {
				
				//id for image
				var id = images[i].getElementsByTagName("id")[0].childNodes[0].nodeValue;
				
				//title for image
				var title = images[i].getElementsByTagName("title")[0].childNodes[0].nodeValue;
				
				//filename for image
				var filename = images[i].getElementsByTagName("filename")[0].childNodes[0].nodeValue;
				
				//GLatLon for image
				var point = images[i].getElementsByTagName("point")[0];
				var latlon = new GLatLng(parseFloat(point.getAttribute("lat")),parseFloat(point.getAttribute("lng")));
				
				//create a marker for the image and add it to the gMarkers array
				gMarkers[id] = createImage(id,title,filename,latlon);
			}
		}
		
		//create a markerManager in order to easily display the images on the map
		markerManager = createMarkerManager(gMarkers);
	
		if(aImage){
			focusOnImage(aImage);
			map.setZoom(12);
		}
}

/**
  * Parses an xml-string for images near a route
  * 
  * (not in use)
  * 
  * @param int routeId the route the images belong to
  * @param string xmlDoc the xml to parse
  */
function parseNearByImageXML(routeId,xmlDoc){
	
	//GLog.write("fetching images near route #" + routeId);
	
	var images = xmlDoc.documentElement.getElementsByTagName("img");
	var numImages = images.length;
		if(numImages > 0){
		//	GLog.write(numImages + " images");
			var ids = new Array();
			for (var i = 0; i < numImages; i++) {
				//id for image
				ids[i] = images[i].getElementsByTagName("id")[0].childNodes[0].nodeValue;
				
			}
			
			//display and store thumbnails
			gRoutes[routeId]['sidebar'] = showImageThumbnails(ids);
			
			//store nearby image ids
			gRoutes[routeId]['nearby'] = ids;
			
			//enlarge markers
			for(var i=0;i<ids.length;i++){
				changeToLargeMarker(ids[i]);
				
			}	
				
		}
		else {
			//GLog.write("no images");
			var sidebar = document.getElementById("images");
			var imagediv = document.createElement("div");
			imagediv.innerHTML = "<p>Ingen bilder funnet</p>";
		
			sidebar.appendChild(imagediv);
		}		
}

/**
  * Parses an xml-string for information on what routes a line is part of
  * 
  * 
  * @param int id id of the line
  * @param string xmlDoc the xml to parse
  */
function parseConsistsXML(id,xmlDoc){
	
	var routes = xmlDoc.documentElement.getElementsByTagName("route");
	var numRoutes = routes.length;
	
	var html = "<h4>Tilknyttede ruter</h4><ul>";
	
	for(var i = 0; i < numRoutes; i++) {
		//id for image
		var code = routes[i].getElementsByTagName("code")[0].childNodes[0].nodeValue;
		//title for image
		var name = routes[i].getElementsByTagName("name")[0].childNodes[0].nodeValue;
			
		html += "<li><a href=\"http://geomatikk.eksplisitt.net/atle/final/showRoute.php?route=" + code + "\">" + name + "</a></li>";
		
	}
	
	html += "</ul>";

	gRoutes[id]['content'] = html;
	
	var sidebar = document.getElementById("images");
	
	sidebar.innerHTML = gRoutes[id]['content'];
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