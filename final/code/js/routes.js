/**
  * Creates a route for displaying on the large map. 
  *
  * @param int id route id
  * @param string name route name
  * @param mixed points array of GLatLon objects
  * @return mixed array holding route information
  */
function createRoute(id,name,points) {
	//GLog.write("creating route with id=" + id +  ", name=" + name + " and " + points.length + " points");
	
	//default values
	var color = '#ff0000';
	var width = 3;
	var opacity = 0.5;
	
	//create the "route"
	var route = Array();
	route['id'] = id;
	route['name'] = name;
	route['active'] = false;
	route['gpolyline'] = createGPolyLine(points,id,color,width);
	
	route['sidebar'] = null;
	route['nearby'] = null;
	return route;
}

/**
  * Adds all routes in the gRoutes array to the map
  *
  */
function showRoutes() {
	//GLog.write("showing routes");
	for(id in gRoutes){
		map.addOverlay(gRoutes[id]['gpolyline']);
	}
}

/**
  * Hides all routes in the gRoutes array from the map
  *
  */
function hideRoutes(){
	//GLog.write("removing routes");
	for(id in gRoutes){
		map.removeOverlay(gRoutes[id]['gpolyline']);
	}
}

/**
  * Sets all routes in gRoutes that are active inactive
  * and clears the sidebar
  */
function setRoutesInactive(){
	
	clearSidebar();
	
	for(id in gRoutes){
		if(gRoutes[id]['active'] == true){
			gRoutes[id]['gpolyline'].setStrokeStyle({'color':'#ff0000'});
			gRoutes[id]['active'] = false;	
		}
	}
}

/**
  * Gets the id of the currently active route
  *
  * @return int id of active route
  */
function getActiveRoute(){
	var activeId = false;
	for(id in gRoutes){
		if(gRoutes[id]['active'] == true){
			activeId = id;
		}
	}
	return activeId;
}


/**
  * Creates a GPolyline with listeners
  *
  * @param mixed pts An array of GLatLng points representing the line
  * @param int id the database id of the route, this is exclusively used to reference the route
  * @param string color the color to draw the line in (hex)
  * @param int width the width of the line (px)
  * @param float opacity opacity of the line (0-1)
  * @return GPolyLine polyline the created polyline
  */
function createGPolyLine(points,id,color,width,opacity) {
	//GLog.write("Create Route Called");

	//create the polyline
	var polyline = new GPolyline(points,color,width,opacity);
	
	//add listeners
	GEvent.addListener(polyline, "mouseover", function(){
		//GLog.write("Polyline " + id + " onmouseovered");
		polyline.setStrokeStyle({'opacity':'1.0'});
	});

	GEvent.addListener(polyline, "mouseout", function(){
		//GLog.write("Polyline " + id + " onmouseouted");
		polyline.setStrokeStyle({'opacity':'0.5'});
	});
	
	GEvent.addListener(polyline,'click',
	function(){
		//insert functionality here
		
			if(gRoutes[id]['active']){
				//do nothing
			}
			else {
				setRoutesInactive();
				focusOnLine(id);
			}
	});	
	return polyline;
}

/**
  * Focuses the map on the specified line
  * and writes a list of routes related to this line in the sidebar
  * 
  * @param int id the route id
  * 
  */
function focusOnLine(id){
	//get bounds for route
	var bounds = gRoutes[id]['gpolyline'].getBounds();
	
	//set zoom level
	map.setZoom(map.getBoundsZoomLevel(bounds)-1);
	
	//GLog.write("zoom-level= " + map.getBoundsZoomLevel(bounds))
	
	//center map on route
	map.setCenter(bounds.getCenter());
		
	//mark as active
	gRoutes[id]['active'] = true;
			
	//change style of  line
	gRoutes[id]['gpolyline'].setStrokeStyle({'color':'#00ff00'});
	
	if(gRoutes[id]['content']){
	
		var sidebar = document.getElementById("images");
		sidebar.innerHTML = gRoutes[id]['content'];
	}
	else{
		//GLog.write("getting");
		getRoutes(id);
	}
}

/**
  * Gets images near a specified route from the db (not in use..)
  * 
  * @param int id the route id
  * 
  */
function getNearbyImages(id){
	
	//api url
	var apiUrl = "http://geomatikk.eksplisitt.net/atle/output/api.php";
	
	//create request
	var request = GXmlHttp.create();
	
	//get xml
	request.open("GET", apiUrl + "?type=images&near=" + id + "&treshold=100" , true);	
	
	//wait for return
	request.onreadystatechange = function() {
		if (request.readyState == 4) {
			//parse the XML
			parseNearByImageXML(id,request.responseXML);
  		}	
	}
	//finish
	request.send(null);	
}

/**
  * Gets the routes that uses the specified line from DB (via API)
  * 
  * @param int id the line id
  * 
  */
function getRoutes(id){
	
	//api url
	var apiUrl = "http://geomatikk.eksplisitt.net/atle/final/api.php";
	
	//create request
	var request = GXmlHttp.create();
	
	//get xml
	request.open("GET", apiUrl + "?type=consists&id=" + id , true);	
	
	//wait for return
	request.onreadystatechange = function() {
		if (request.readyState == 4) {
			//parse the XML
			parseConsistsXML(id,request.responseXML);
  		}	
	}
	//finish
	request.send(null);	
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