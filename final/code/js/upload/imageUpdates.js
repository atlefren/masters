/**
  * Controls behaviour of the image_status_btn
  * and changes the state of the map
  *
  * @param string id image id
  * @param string action the action that is responded to
  */
function imageClick(id,action){
	
	if (action == "change"){
		changeImageStatus(id,"choosing");
		//show the imagemarker
		showImageMarker(id);
		//show search box
		showSearchBox(id);
	}
	if (action == "change_exif"){
		changeImageStatus(id,"exif_show");
		//show the imagemarker
		showImageMarker(id);
		//show search box
		showSearchBox(id);
	}
	else if (action == "hide") {
		changeImageStatus(id,"chosen");
		//hide the imagemarker
		hideImageMarker(id);
		//hide search box
		hideSearchBox(id);
	}
	else if(action =="hide_exif"){
		changeImageStatus(id,"exif_hidden");
		//hide the imagemarker
		hideImageMarker(id);
		//hide search box
		hideSearchBox(id);
	}
	else if(action =="create"){
		changeImageStatus(id,"choosing");
		//create the imagemarker	
		setupImageMarker(id);
		//show search box
		showSearchBox(id);
		//update marker coords
		updateMarkerCoordsDom(id);
	}
}

/**
  * Changes the status of an image in the DOM
  *
  * @param string id image id
  * @param string state the state to give the image
  */
function changeImageStatus(id,state){
	var stateText;
	var btnText;
	var onClickAction;
	
	if(state =="chosen"){
		//image has coordinates set by user
		//not shown on map
		stateText = "Valgt";	
		btnText = "endre?";
		onClickAction = "change";
	}
	else if (state == "exif_show"){
		//image has coordinates from EXIF
		//shown on map
		stateText = "Fra EXIF";
		btnText = "ok?";
		onClickAction = "hide_exif";
	}
	else if (state == "exif_hidden"){
		//image has coordinates from EXIF
		//not shown on map
		stateText = "Fra EXIF";
		btnText = "endre?";
		onClickAction = "change_exif";
	}
	else if (state == "choosing") {
		//user is choosing coordinates
		//shown on map
		stateText = "Velger";
		btnText = "ok?";
		onClickAction = "hide";
	}
	
	//will this ever be used??
	else if (state == "notset") {
		//coordinates are not set
		//image not set on map
		stateText = "Ikke angitt";
		btnText = "angi?";
		onClickAction = "create";
	}
	//update in the DOM
	document.getElementById("image_status_btn_" +id).innerHTML = btnText;
	document.getElementById("image_status_" + id).innerHTML = stateText;
	document.getElementById("image_status_btn_" +id).setAttribute("onClick","imageClick(" + id +  ",'" +  onClickAction + "')");
}

/**
  * Changes the coordinates associated with each image in the DOM
  * Gets the coordinates from the marker
  *
  * @param string id image id
  */
function updateMarkerCoordsDom(id){
	//get current coords for the image
	var coords = markers[id].getLatLng();
	var lat = coords.lat();
	var lon = coords.lng();
	
	if(debug){
		GLog.write("changing coords for image #" + id + " to " + coords);
	}
	//update
	var index = id-1;
	document.getElementById('lat[' + index + ']').setAttribute('value',lat);
	document.getElementById('lon[' + index + ']').setAttribute('value',lon);
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