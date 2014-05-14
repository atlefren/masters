/**
  * Creates a normal, small marker with basic mouseover-actions
  * (used for the route map) 
  *
  * @param GlatLon latlon coordinates
  * @param int id id of marker
  * @return GMarker the new marker
  */
function createPlainMarker(latlon,id){
	
	//create the marker
	var marker = new GMarker(latlon,{icon:cameraMarkerSmall()});

	//add listener
    GEvent.addListener(marker, "mouseover", function() {	
		focusOnMarker(id);
    });
        
	return marker;
}

/**
  * Creates a normal, small marker with basic mouseover-actions
  * (used in...?) 
  *
  * @param GlatLon latlon coordinates
  * @param int id id of marker
  * @return GMarker the new marker
  */
function createNormalMarker(id,latlon){
	
	//create the marker
	var marker = new GMarker(latlon,{icon:cameraMarkerSmall()});
		
	//mouseover listener
	GEvent.addListener(marker, "mouseover", function() {	
		changeToHoverMarker(id);
    });
    
    //return the marker so that it can be included in the markermanager and GMarkers array
    // (see createImage() and parseImageXML())
	return marker;
}

/**
  * Changes the marker to a hovering one (ie: a large marker with highest z-index)
  * this marker listens for clicks, not the normal ones (as a mouseover always preceedes a click)
  *
  * @param int id id of marker
  */
function changeToHoverMarker(id){
	
	//GLog.write("changing to hover marker " + id);
	
	//create the hovering marker
	var latlon = gMarkers[id]['orgMarker'].getLatLng();
	var marker = new GMarker(latlon,{icon:cameraMarkerLarge(),zIndexProcess:importanceOrder});
	marker.importance = 3;

	//assume no click
	var click = false;
	
	//onclick listener, set marker active
	GEvent.addListener(marker, "click", function() {
		changeToActiveMarker(marker,id);
		//indicate that we had a click, to prevent mouseout-actions	
		click = true;
    });
    
    //mouseout listener, only perform if the marker was not clicked
    GEvent.addListener(marker, "mouseout", function() {
    	if (!click){
			mouseOutNormal(marker,id);
    	}
    });
    
     //show hover marker
    map.addOverlay(marker);
	marker.show();
	marker.pleaseHideLater = false;  
    
    //hide the original marker
    //check if size og "orignial" marker
    if(gMarkers[id]['largeMarker']){
    	//large marker
    	largeMarker = gMarkers[id]['largeMarker'];
		largeMarker.hide();
		largeMarker.pleaseHideLater = true;    	
    }
    else {
    	//normal marker
    	orgMarker = gMarkers[id]['orgMarker'];
   		orgMarker.hide();
		orgMarker.pleaseHideLater = true;	
    }
}

/**
  * Changes the marker to an active one (to indicate that it is clicked)
  *
  * @param GMarker orgMarker the original marker
  * @param int id id of marker
  * 
  */
function changeToActiveMarker(orgMarker,id){
	
	//GLog.write("changing to active marker");
	
	setRoutesInactive();
	
	//reset other active markers (so that this becomes the only one active)
	resetActiveMarkers();
	
	//displaying a thumbnail of the image in the sidebar
	clearSidebar();
	showImageThumbnail(id,300);
	
	//center map in the marker
	map.panTo(gMarkers[id]['orgMarker'].getLatLng());
	
	//create an active marker
	var latlon = gMarkers[id]['orgMarker'].getLatLng();
	var marker = new GMarker(latlon,{icon:cameraMarkerActive(),zIndexProcess:importanceOrder});
	marker.importance = 3;
	
	//add a click listener (to disable again)
	GEvent.addListener(marker, "click", function() {
		disableActiveMarker(id);
    });
    
    //store details
    gMarkers[id]['status'] = "active";
    gMarkers[id]['activeMarker'] = marker;
    
    //show active marker
    map.addOverlay(marker);
	marker.show();
	marker.pleaseHideLater = false;  

	//hide the original marker
	orgMarker.hide();
	orgMarker.pleaseHideLater = true;    	
}

/**
  * Changes the marker to a large one (to indicate nearness to a selected route)
  *
  * @param int id id of marker
  * 
  */
function changeToLargeMarker(id) {
	
	//GLog.write("changing to large marker");
	
	//create a large marker
	var latlon = gMarkers[id]['orgMarker'].getLatLng();
	var marker = new GMarker(latlon,{icon:cameraMarkerLarge(),zIndexProcess:importanceOrder});
	marker.importance = 1;	
	
	//add listener
	GEvent.addListener(marker, "mouseover", function() {
		changeToHoverMarker(id);
    });
	
    //store the marker details
    gMarkers[id]['status'] = "large";
    gMarkers[id]['largeMarker'] = marker;
    
    //show the large marker
    map.addOverlay(marker);
	marker.show();
	marker.pleaseHideLater = false;  
	
	//hide the original marker
    orgMarker = gMarkers[id]['orgMarker'];
    orgMarker.hide();
	orgMarker.pleaseHideLater = true;
}

/**
  * Disables a large marker
  *
  * @param int id id of marker
  * 
  */
function disableLargeMarker(id){
	
	//GLog.write("disable Large");
	
	//get the large marker currently displayed on the map
	var largeMarker = gMarkers[id]['largeMarker'];
	
	//set status to normal
	gMarkers[id]['status'] = "normal";
	gMarkers[id]['largeMarker'] = false;
	
	//show the normal marker
	var orgMarker = gMarkers[id]['orgMarker'];
	map.addOverlay(orgMarker);
	orgMarker.show();
	orgMarker.pleaseHideLater = false;		
	
	//hide the large marker
	largeMarker.hide();
	largeMarker.pleaseHideLater = true;	
}

/**
  * Disables an active marker (ie: reverts it to what it prevoiusly was
  *
  * @param int id id of marker
  * 
  */
function disableActiveMarker(id){
	
	//GLog.write("disableActive");
	
	//get the marker in question based on id
	var activeMarker = gMarkers[id]['activeMarker'];
	
	//clear image in sidebar
	clearSidebar();
	
	if(getActiveRoute()){
		var sidebar = document.getElementById("images");
		sidebar.innerHTML = gRoutes[getActiveRoute()]['sidebar'];
	}	
	//check wether we are resetting to a normal or large marker
	if(gMarkers[id]['largeMarker']){
		//large marker
		//GLog.write("resetting to large marker");
		
		//set status back to large
		gMarkers[id]['status'] = "large";
		
		//show the large marker
		var largeMarker = gMarkers[id]['largeMarker'];
		map.addOverlay(largeMarker);
		largeMarker.show();
		largeMarker.pleaseHideLater = false;
	}
	else {
		//normal marker
		//GLog.write("resetting to normal marker");
		
		//set status to normal
		gMarkers[id]['status'] = "normal";
		
		//show the normal marker
		var orgMarker = gMarkers[id]['orgMarker'];
		map.addOverlay(orgMarker);
		orgMarker.show();
		orgMarker.pleaseHideLater = false;		
	}
	
	//hide the active marker
	activeMarker.hide();
	activeMarker.pleaseHideLater = true;	
}

/**
  * Actions when mousing out on a marker, either normal or large
  *
  * @param GMarker hoverMarker the marker we are hovering out from
  * @param int id id of marker
  * 
  */
function mouseOutNormal(hoverMarker,id){
	
	//GLog.write("mouseout");
	
	//hide the hover marker
	hoverMarker.hide();
	hoverMarker.pleaseHideLater = true;
	
	//check if the "original marker" is a large or normal marker
	if(gMarkers[id]['largeMarker']){
		//we have a large marker
		//GLog.write("resetting to large marker");
		
		//set status to large
		gMarkers[id]['status'] = "large";
		
		//show the large marker
		var largeMarker = gMarkers[id]['largeMarker'];
		map.addOverlay(largeMarker);
		largeMarker.show();
		largeMarker.pleaseHideLater = false;
	}
	else {
		//we have a normal marker
		//GLog.write("resetting to normal marker");
		
		//set status to normal
		gMarkers[id]['status'] = "normal";
		
		//show the normal marker
		var orgMarker = gMarkers[id]['orgMarker'];
		map.addOverlay(orgMarker);
		orgMarker.show();
		orgMarker.pleaseHideLater = false;		
	}
}

/**
  * Resets all active markers to the way they where
  *
  */
function resetActiveMarkers(){
	for(id in gMarkers){
		if(gMarkers[id]['status']=="active"){
			disableActiveMarker(id);
		}
	}
}

/**
  * Resets all large markers to normal ones
  *
  */
function resetLargeMarkers(){
	for(id in gMarkers){
		if(gMarkers[id]['status']=="large"){
			disableLargeMarker(id);
		}
	}
}

/**
  * Hackety-hack in order to handle z-index
  *
  * @param GMarker marker the marker
  * @param mixed b unknown..
  */
function importanceOrder (marker,b) {
	return GOverlay.getZIndex(marker.getPoint().lat()) + marker.importance*1000000;
}

/**
  * Creates a marker manager of an array of images. Sets hide-zoom-level to 12
  *
  * @param mixed images array of images
  * @return MarkerManager the markermanager
  */
function createMarkerManager(images){
	
	var mgr = new MarkerManager(map);
	var imageMarkers = new Array();
	
	for(id in images){
		imageMarkers.push(images[id]['orgMarker']);
	}
	mgr.clearMarkers();
	mgr.addMarkers(imageMarkers,12);
	mgr.refresh();
	return mgr	
}

/**
  * Creates a small camera marker icon
  *
  * @return GIcon the new icon
  */
function cameraMarkerSmall(){
	var url = "http://geomatikk.eksplisitt.net/atle/gfx/";
    var icon = new GIcon();
    icon.image = url + "camera-small.png";
    icon.shadow = url +  "shadow-camera-small.png";
    icon.iconSize = new GSize(26.0, 25.0);
    icon.shadowSize = new GSize(39.0, 25.0);
    icon.iconAnchor = new GPoint(5.0, 26.0);
    icon.infoWindowAnchor = new GPoint(5.0, 26.0);
    
    return icon;
}

/**
  * Creates a large camera marker icon
  *
  * @return GIcon the new icon
  */
function cameraMarkerLarge(){
	
	var url = "http://geomatikk.eksplisitt.net/atle/gfx/";
	
	var icon = new GIcon();
    icon.image = url + "camera.png";
    icon.shadow = url + "shadow-camera.png";
    icon.iconSize = new GSize(35.0, 35.0);
    icon.shadowSize = new GSize(53.0, 35.0);
    icon.iconAnchor = new GPoint(6.0, 35.0);
    icon.infoWindowAnchor = new GPoint(6.0, 35.0);
    
    return icon;
}

/**
  * Creates an active camera marker icon
  *
  * @return GIcon the new icon
  */
function cameraMarkerActive(){
	
	var url = "http://geomatikk.eksplisitt.net/atle/gfx/";
	
	var icon = new GIcon();
    icon.image = url + "camera-active.png";
    icon.shadow = url + "shadow-camera.png";
    icon.iconSize = new GSize(35.0, 35.0);
    icon.shadowSize = new GSize(53.0, 35.0);
    icon.iconAnchor = new GPoint(6.0, 35.0);
    icon.infoWindowAnchor = new GPoint(6.0, 35.0); 
    return icon;
}

/**
  * Creates a cabin marker icon
  *
  * @return GIcon the new icon
  */
function cabinMarker(){
	
	var url = "http://geomatikk.eksplisitt.net/atle/gfx/";
	
	var icon = new GIcon();
    icon.image = url + "cabin2.png";
    icon.iconSize = new GSize(20.0, 18.0);
    icon.iconAnchor = new GPoint(10.0, 9.0);
    icon.infoWindowAnchor = new GPoint(9.0, 0.0); 
    return icon;
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