/**
  * Sets up the map that shows a single route. 
  * Sets up the route itself, near routes, cabins and images
  * 
  * Computes map center and zoom-level based on the JSON-data
  * 
  * @param mixed json array with json-data for all the elements
  * 
  */
function showRoute(json){
	
	var routeJson = json['route'];
	var nearImagesJson = json['images'];
	var nearRoutesJson = json['nearRoutes'];
	var nearCabinsJson = json['cabins'];
	
	//setup map
	map = new GMap2(document.getElementById("map"));
	map.disableDragging();
	map.disableDoubleClickZoom();

	//setup this route	
	if(routeJson.length >0){
		var route = setupRoutes(routeJson,'main');
	}
	
	//setup nearby routes
	if(nearRoutesJson.length >0){
		//routeHandler = setupNearbyRoutes(nearRoutesJson);
		var nearRoutes = setupRoutes(nearRoutesJson,'near');
	}
	
	//setup nearby images
	if(nearImagesJson.length >0){
		var markers = setupNearbyImages(nearImagesJson);
	}
	
	//setup nearby cabins
	var cMarkers = setupNearbyCabins(nearCabinsJson);
	
	//get bounds from route
	var bounds = getRouteBounds(route);

	//get center from route
	var center = bounds.getCenter();

	//compute zoom-level
	var routeZoom = map.getBoundsZoomLevel(bounds);

	//get zoom-level from images
	var imageZoom = getImageZoom(nearImagesJson);
		
	//set center and zoom-level
	map.setCenter(center,compareZL(12,imageZoom,routeZoom));
	
	//add nearby routes to map
	addRoute(nearRoutes);
	
	//add this route to map
	addRoute(route);
		
	//merge all lines into one array
	routeHandler = mergeArrays(nearRoutes,route);
	
	//add cabin markers
	if(cMarkers.length>0){
		//GLog.write("adding");
		var mgr2 = new MarkerManager(map);
		mgr2.addMarkers(cMarkers,1);
		mgr2.refresh();
	}
	
	//add markers for images to map using a markerManager
	if(markers.length >0){
		mgr = new MarkerManager(map);
		mgr.addMarkers(markers,1);
		mgr.refresh();
	}
}

/**
  * Set a route active
  *
  * @param string code route code
  */
function activeRoute(code){
	activeLine(parts[code]);
}

/**
  * Set a route inactive
  *
  * @param string code route code
  */
function unactiveRoute(code){
	unactiveLine(parts[code]);
}

/**
  * Set a trip active
  *
  * @param int id trip id
  */
function activeTrip(id){
	activeLine(tripParts[id]);
}

/**
  * Set a trip inactive
  *
  * @param int id trip id
  */
function unactiveTrip(id){
	unactiveLine(tripParts[id]);
}

/**
  * Set a set of lines active by changing their color 
  *
  * @param mixed lines array of line ids
  */
function activeLine(lines){
	for(var id in lines){
		if(routeHandler[lines[id]] != undefined){
			routeHandler[lines[id]]['gPoly'].setStrokeStyle({'color':'#0000ff'});
			routeHandler[lines[id]]['active'] = true;
		}
	}
}

/**
  * Set a set of lines inactive by changing their color 
  *
  * Sets the color on the lines belonging to the main route to green, other to red
  * @param mixed lines array of line ids
  */
function unactiveLine(lines){
	for(var id in lines){
		if(routeHandler[lines[id]] != undefined){
			if(routeHandler[lines[id]]['type']=='this'){
				routeHandler[lines[id]]['gPoly'].setStrokeStyle({'color':'#00ff00'});
			}
			else if(routeHandler[lines[id]]['type']=='near'){
				routeHandler[lines[id]]['gPoly'].setStrokeStyle({'color':'#ff0000'});
			}
			routeHandler[lines[id]]['active'] = false;
		}
	}
}

/**
  * Set a set of lines inactive by changing their color 
  *
  * @param mixed lines array of line ids
  */
function unactiveLines(){
	for(var id in routeHandler){
		if(routeHandler[id]['active']){
			if(routeHandler[id]['type']=='this'){
				routeHandler[id]['gPoly'].setStrokeStyle({'color':'#00ff00'});
			}
			else if(routeHandler[id]['type']=='near'){
				routeHandler[id]['gPoly'].setStrokeStyle({'color':'#ff0000'});
			}
		}
	}
}

/**
  * Merges two arrays
  *
  * @param mixed array1 first array
  * @param mixed array2 second array
  * @return mixed the combined array
  */
function mergeArrays(array1,array2){
	
	for(var id in array2){	
		array1[id]=array2[id];
	}
	return array1;
}

/**
  * Compares zoom-levels from routes, images and a maximum to choose the right one
  *
  * @param int max max zl allowed
  * @param int images zl from images
  * @param int route zl from routes
  * @return int the resulting zoom-level
  */
function compareZL(max,images,route){
	//GLog.write("comparing");
	var zoom = route;
	
	if(images != null){
		//GLog.write("Zoom level computed from route=" + route);
		//GLog.write("Zoom level computed from images=" + images);
		if(images < route){
			zoom = images;
			//GLog.write("using images zoom level (=" + images + ")");
		}
		else {
		//GLog.write("keeping route zoom level (=" + route + ")");
		}
	}
	//GLog.write("using zl=" + zoom);
	if(zoom > max){
		//GLog.write("zoom level too low, using max (=" + max + ")");
		zoom = max;
	}
	
	return zoom;
}

/**
  * Sets up a route
  *
  * @param JSON routeJson the json for the lines that make up the route(s)
  * @param string type of route ("main" or "near")
  * @return mixed array of GPolyLine objects
  */
function setupRoutes(routeJson,type){
		
	var routes = Array();
	//loop trough all routes
	for(var i=0;i<routeJson.length;i++){
		
		//get id
		var id = routeJson[i].id;
		
		//get points
		var latlons = Array();
		for(var j=0;j<routeJson[i].points.length;j++){
			latlons.push(new GLatLng(routeJson[i].points[j].lat,routeJson[i].points[j].lon));
		}
		//create route
		if(type=='main'){
			var line = Array();
			line['type'] = 'this';
			line['active'] = false;
			line['gPoly'] = new GPolyline(latlons,'#00ff00',2,1);			
			routes[id] = line;	
		}
		else if (type=='near'){
			var line = Array();
			line['type'] = 'near';
			line['active'] = false;
			line['gPoly'] = new GPolyline(latlons,'#ff0000',2,1);			
			routes[id] = line;
		}
	}	
	return routes;
}

/**
  * Adds routes to map
  *
  * @param mixed routes array of routes
  */
function addRoute(routes){
	for(var id in routes){	
		map.addOverlay(routes[id]['gPoly']);
	}
}

/**
  * Finds the bounds for a route
  *
  * @param mixed array of lines that make up the route
  * @return GLatLngBounds the bounds for the route
  */
function getRouteBounds(route){
	
	var first = true;
	for(var id in route){	
	//	GLog.write(id + ": " + route[id].getBounds());	
		if(first){
			var maxLat = route[id]['gPoly'].getBounds().getNorthEast().lat();
			var minLat = route[id]['gPoly'].getBounds().getSouthWest().lat();
	
			var maxLon = route[id]['gPoly'].getBounds().getNorthEast().lng();
			var minLon = route[id]['gPoly'].getBounds().getSouthWest().lng();			
			first = false;
		}
		else {
			
			if(maxLat < route[id]['gPoly'].getBounds().getNorthEast().lat()){
				maxLat = route[id]['gPoly'].getBounds().getNorthEast().lat();
			}
			if(minLat > route[id]['gPoly'].getBounds().getSouthWest().lat()){
				minLat = route[id]['gPoly'].getBounds().getSouthWest().lat();
			}
			if(maxLon < route[id]['gPoly'].getBounds().getNorthEast().lng()){
				maxLon = route[id]['gPoly'].getBounds().getNorthEast().lng();
			}
			if(minLon > route[id]['gPoly'].getBounds().getSouthWest().lng()){
				minLon = route[id]['gPoly'].getBounds().getSouthWest().lng();
			}
		}
	}
	var bounds = new GLatLngBounds(new GLatLng(minLat,minLon),new GLatLng(maxLat,maxLon))
	//GLog.write("final: " + bounds);
	return bounds;
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