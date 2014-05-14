/**
  * Hides an image marker from the map. Checks if it is already hidden
  *
  * @param string id image id
  */
function hideImageMarker(id){
	if(debug){
		GLog.write("Asked to hide the marker for image #" + id);
	}
	if (markers[id].isHidden()) {
		if(debug){
			GLog.write("The marker for image #" + id + " already hidden, doing nothing");
		}
	}
	else {
		if(debug){
			GLog.write("hiding marker for image #" + id);
		}
		//hide the marker
		markers[id].hide();
	}
}

/**
  * Shows an image marker, checks if it is already visible
  *
  * @param string id image id
  */
function showImageMarker(id){
	if(debug){
		GLog.write("asked to show the marker for  image #" + id);
	}
	if (!markers[id].isHidden()) {
		if(debug){
			GLog.write("The marker for image #" + id + " already on map, doing nothing");
		}
	}
	else {
		if(debug){
			GLog.write("showing the marker for  image #" + id);
		}
		//show the marker
		markers[id].show();

		//center on the image marker
		var point = markers[id].getLatLng();
		map.panTo(point);
	}
}


/**
  * Setting up an image marker (with no coords given)
  *
  * @param string id image id
  */
function setupImageMarker(id){
	if(debug){
		GLog.write("asked to create marker for image #" + id);
	}
	if (!markers[id]) {
		if(debug){
			GLog.write("Creating marker for image #" + id);
		}
		//get coords for map center
		var center = map.getCenter();
		var lat = center.lat();
		var lon = center.lng();

		//create the marker
		var marker = createDraggableMarker(id,lat,lon);
		//add marker to markers-array
		markers[id] = marker;
		//add the marker to the map
		map.addOverlay(marker);
	}
	else {
		if(debug){
			GLog.write("Image #" + id + " already has a marker");
		}
	}
}

/**
  * Moves the marker to a given place (latlon) and updates the references to is, 
  * as well as centering on the marker and setting appropriate zoom-level
  *
  * @param string id image id
  * @param float lat new latitude
  * @param float lon new longitude
  */
function moveMarker(id,lat,lon){
	if(debug){
		GLog.write("moving marker for image #" + id + " to (" + lat + ","  + lon + ")");
	}
	//update the position
	markers[id].setLatLng(new GLatLng(lat, lon));

	//change zoom-level
	var oldcenter = map.getCenter();

	//pan to the marker
	map.panTo(new GLatLng(lat, lon));

	//change zoom
	zoomChange(markers[id].getLatLng(),oldcenter);

	//update references to the image
	updateDomLatLon(id,lat,lon);

	//change status of image
	changeImageStatus(id,"choosing");
}


/**
  * Indicate that the marker has been moved by user by changing the references to it
  * and updating the status field in the DOM
  *
  * @param string id image id
  */
function markerMoved(id) {

	//get the new posistion
	var point = markers[id].getLatLng();
	var lat = point.lat();
	var lon = point.lng();

	if(debug){
		GLog.write("marger for image #" + id + " moved to (" + lat + ","  + lon + ")");
	}
	//update references to the image
	updateDomLatLon(id,lat,lon);

	//change status of image
	changeImageStatus(id,"choosing");
}

/**
  * Centers the map on the specified marker, but only
  * if the marker exists and are set to visible
  *
  * @param string id image id
  */
function centerOnMarker(id){
	if ((markers[id]) && (!markers[id].isHidden())) {
		if(debug){
			GLog.write("centering the map on the marker for image #" + id);
		}
		var point = markers[id].getLatLng();
		map.panTo(point);
	}
	else {
		if(debug){
			GLog.write("the marker for image #" + id + " is not visible (or not created), doing nothing");
		}
	}
}

/**
  * Create an image marker with given coords and 
  * add it to the markers-array and add it to the map
  *
  * @param string id image id
  * @param float lat latitude
  * @param float lon longitude
  */
function createImageMarker(id,lat,lon) {
	if(debug){
		GLog.write("creating marker at  (" + lat + "," + lon + ")");
	}
	//create a marker from given coords
	var marker = createDraggableMarker(id,lat,lon);

	//add marker to markers-array
	markers[id] = marker;

	//add the marker to the map
	map.addOverlay(marker);
}

/**
  * Creates a draggable marker with color corresponding to its id. 
  * Adds dragend and click listeners
  *
  * @param string id image id
  * @param float lat latitude
  * @param float lon longitude
  * @return GMarker the new marker
  */
function createDraggableMarker(id,lat,lon){

	//create a latlon point
	var latLng = new GLatLng(lat, lon);

	//create a custom icon with color based on id
	var icon = createMarkerIcon(id);

	//create the marker
	var marker = new GMarker(latLng, {draggable: true, icon: icon});

	//listener for dragend (i.e. marker is "dropped" after beeing dragged
	GEvent.addListener(marker, "dragend", function() {
		markerMoved(id);
	});

	//listener for when marker is clicked
	GEvent.addListener(marker, "click", function() {
		centerOnMarker(id);
	});

	//return the marker
	return marker;
}

/**
  * Creates a custom markerIcon with color depending on its id
  *
  * @param int id image id
  * @return MapIconMaker the new marker
  */
function createMarkerIcon(id){
	var colors = Array("#FF0000","#FFC403","#ABFF03","#01FF34","#06FFFB","#0558FF","#A011FF","#FF00D9","#D5FFAE");

	//make a new icon with color according to id
	var iconOptions = {};
	iconOptions.width = 32;
	iconOptions.height = 32;
	iconOptions.primaryColor = colors[id-1];
	iconOptions.cornerColor = colors[id-1];
	iconOptions.strokeColor = "#000000";

	var icon = MapIconMaker.createMarkerIcon(iconOptions);

	return icon
}

/**
  * Changes the zoom level to 8 when a new marker is created. 
  * Only if th distance between the current map center and
  * the new point is less than 2000 m (2km) and
  * the current zoom-level is larger than 8
  *
  * @param GLatLng point the new point
  * @param GLatLng center the image center
  */
function zoomChange(point,center){

	var dist = point.distanceFrom(center);
	var zoom = map.getZoom()

	if(debug){
		GLog.write("distance= " + dist  + " zoom= " + zoom);
	}

	if((map.getZoom() > 8) && (dist >2000)) {
		if(debug){
			GLog.write("zoomlevel changed");
		}
		map.setZoom(8);
	}
	else {
		if(debug){
			GLog.write("zoomlevel NOT changed");
		}
	}
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