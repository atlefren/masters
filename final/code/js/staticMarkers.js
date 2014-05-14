/**
  * Sets up nearby images to the route
  *
  * @param json nearImagesJson JSON-data for the nearby images
  * @return mixed array of GMarkers
  */
function setupNearbyImages(nearImagesJson){
	
	//create a marker for each induvidual image
	var markers = new Array();
	for(var i=0;i<nearImagesJson.length;i++){
		
		//get id
		var id = nearImagesJson[i].id;
		
		//get point
		var latlon = new GLatLng(nearImagesJson[i].lat,nearImagesJson[i].lon);
		
		//create marker
		var marker = createPlainMarker(latlon,id);

		//create markerHandler instance
		var image = Array();
		image['marker'] = marker;
		image['activeMarker'] = null;
		image['active'] = false;
		markerHandler[id] = image;

		//add the marker to marker array (used to populate MarkerManager)
		markers.push(marker);
	}
	
	//return the markers
	return markers;
}

/**
  * Sets focus on a marker (by changing z-index and icon)
  *
  * @param int id image id
  */
function focusOnMarker(id){
	//GLog.write("focusing on image#" + id);
	document.getElementById("image_" + id).setAttribute("class","active");
	if(markerHandler[id] != null){
		if(markerHandler[id]['activeMarker']){
			markerHandler[id]['activeMarker'].show();
			markerHandler[id]['marker'].hide();
			markerHandler[id]['active'] = true;
		}
		else {
			var latlon = markerHandler[id]['marker'].getLatLng();

			markerHandler[id]['marker'].hide();

			var activeMarker = new GMarker(latlon,{icon:cameraMarkerActive(),zIndexProcess:importanceOrder});
			activeMarker.importance = 3;
			
			GEvent.addListener(activeMarker, "mouseout", function() {	
				//GLog.write("mouseout " + id);
				removeFocus();
    		});
    		
    		GEvent.addListener(activeMarker,"click",function(){
    			var link = "http://geomatikk.eksplisitt.net/atle/final/showImage.php?image=" + id;
    			location.href=link;
    			
    		});
			
			map.addOverlay(activeMarker);

			markerHandler[id]['activeMarker'] = activeMarker;
			markerHandler[id]['active'] = true;
		}
	}
	else {
		GLog.write("error");
	}
}

/**
  * Removes focus from any image that is active
  * 
  */
function removeFocus(){
	for(var id in markerHandler){
		if(markerHandler[id]['active']== true){
			document.getElementById("image_" + id).setAttribute("class","normal");
			markerHandler[id]['activeMarker'].hide();
			markerHandler[id]['marker'].show();
			markerHandler[id]['active'] = false;
		}
	}
}

/**
  * Computes the zoom-level based on the bounds of the images
  *
  * @param json nearImagesJson JSON-data for the nearby images
  * @return int computed zoom-level
  */
function getImageZoom(nearImagesJson){
	if(nearImagesJson.length > 0){
		var maxLat = nearImagesJson[0].lat;
		var minLat = nearImagesJson[0].lat;
		var maxLon = nearImagesJson[0].lon;
		var minLon = nearImagesJson[0].lon;

		for(var i=1;i<nearImagesJson.length;i++){

			if(nearImagesJson[i].lat > maxLat){
				maxLat = nearImagesJson[i].lat;
			}
			if(nearImagesJson[i].lat < minLat){
				minLat = nearImagesJson[i].lat;
			}

			if(nearImagesJson[i].lon > maxLon){
				maxLon = nearImagesJson[i].lon;;
			}
			if(nearImagesJson[i].lon < minLon){
				minLon = nearImagesJson[i].lon;;
			}
		}
		var sw = new GLatLng(minLat,minLon);
		var ne = new GLatLng(maxLat,maxLon);
		var bounds = new GLatLngBounds(sw,ne);

		var zoom = map.getBoundsZoomLevel(bounds);
		return zoom;
	}
	else {
		return null;
	}
}

/**
  * Sets up markers for the nearby cabins
  *
  * @param json nearCabinsJson JSON-data for the nearby cabins
  * @return mixed array of GMarker objects for the cabins
  */
function setupNearbyCabins(nearCabinsJson){
	
	//create a marker for each cabin
	var markers = new Array();
	for(var i=0;i<nearCabinsJson.length;i++){
		
		//get id
		var id = nearCabinsJson[i].id;
		
		//get point
		var latlon = new GLatLng(nearCabinsJson[i].lat,nearCabinsJson[i].lon);
		
		var name = nearCabinsJson[i].name;
		
		//create marker
		var marker = new GMarker(latlon,{icon:cabinMarker(),title:name});
		//GLog.write(latlon);
		//add the marker to marker array (used to populate MarkerManager)
		markers.push(marker);
	}
	
	//return the markers
	return markers;
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