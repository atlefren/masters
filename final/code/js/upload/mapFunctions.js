
/**
  * Sets up the map. Center and zoom-levels varies dependant on three cases:
  * - no coords given: centering at Norway
  * - coordinates for one point given: centering at point, zoomlevel 13
  * - bounds for several points given: centering at bounds center and appropriate zoomlevel for bounds
  *
  * @param float maxLat max latitude (if bounds) or lat of point (if single)
  * @param float maxLon max longitude (if bounds) or lon of point (if single)
  * @param float minLat min latitude
  * @param float minLon min longitude
  */
function setupMap(maxLat,maxLon,minLat,minLon) {

	map = new GMap2(document.getElementById("map"));
	map.enableScrollWheelZoom();
	map.addControl(new GMapTypeControl());
	map.addControl(new GLargeMapControl());
	map.addControl(new GOverviewMapControl());

	if ((minLat == null) && (minLon== null) && (maxLat == null) && (maxLon== null)) {
		//setting up map, no points given, centering on Norway
		if(debug){
			GLog.write("setting up map, one w/o coords")
		}
		var lat = maxLat;
		var lon = maxLon;

		map.setCenter(new GLatLng(65.5,13.0), 5);
	}
	else if((minLat == null) && (minLon== null)) {
		//single point specified, center map on this point
		if(debug){
			GLog.write("setting up map, one point..")
		}
		var lat = maxLat;
		var lon = maxLon;

		//center the map on the point at zoom level 13
		map.setCenter(new GLatLng(lat,lon), 13);
	}
	else {
		//set of bounds given, center on area and calculate zoom-level
		if(debug){
			GLog.write("setting up  map, several points..")
		}
		//calculate bounds and zl
		var bounds = new GLatLngBounds;
		bounds.extend(new GLatLng(minLat, minLon));
		bounds.extend(new GLatLng(maxLat, maxLon));

		//center the map on center of bounds
		map.setCenter(bounds.getCenter(), map.getBoundsZoomLevel(bounds)-1);
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