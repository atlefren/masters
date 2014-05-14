//

/**
  * Creates an image, as an entry for the gMarkers array
  * sets initial values
  *
  * @param int id image id
  * @param string title image title
  * @param string filename image filename
  * @param GLatLng the coordinates of the image
  * @return mixed an array with image information
  */
function createImage(id,title,filename,latlon) {

	var image = Array();
	//the provided information
	image['id'] = id;
	image['title'] = title;
	image['filename'] = filename;
	
	//status and stuff
	image['status'] = "normal";
	image['orgMarker'] = createNormalMarker(id,latlon);
	image['largeMarker'] = false;
	image['activeMarker'] = false;
	
	return image;
}

/**
  * Changes the map to focus on an image and sets the image marker active
  *
  * @param int id image id
  */
function focusOnImage(id){
	var orgMarker = gMarkers[id]['orgMarker'];
	changeToActiveMarker(orgMarker,id);
	map.setCenter(orgMarker.getLatLng());
}

/**
  * Clears the content of the sidebar
  *
  */
function clearSidebar(){
	var sidebar = document.getElementById("images");
	sidebar.innerHTML = "";
}

/**
  * Shows a thumbnail of the image in the sidebar
  *
  * @param int id image id
  * @param int imgWidth width of image (in pixels)
  */
function showImageThumbnail(id,imgWidth){
	
	//get sidebar
	var sidebar = document.getElementById("images");
	
	//create a div for the image
	var imagediv = document.createElement("div");
		
	var html = "<a href=\"http://geomatikk.eksplisitt.net/atle/output/getImage2.php?filename=" + gMarkers[id]['filename'] + "&width=800\"rel=\"lightbox\" title=\"" + gMarkers[id]['title'] + "\">"
	html += "<img src=\"http://geomatikk.eksplisitt.net/atle/output/getImage2.php?filename=" + gMarkers[id]['filename'] + "&width=" + imgWidth + "\" border=\"0\"></a><br>";
	html += "<p>" + gMarkers[id]['title'] + " (<a href=\"showImage.php?image=" + id +  "\">mer informasjon</a>)</p>";
	
	imagediv.innerHTML = html;
		
	sidebar.appendChild(imagediv);
	updateLightbox();
}

/**
  * Show thumbnails for a collection of images
  *
  * @param mixed ids array of image ids
  */
function showImageThumbnails(ids){
	
	//get sidebar
	var sidebar = document.getElementById("images");
	
	var length = ids.length;
	
	var imagediv = document.createElement("div");
	
	if(ids.length > 4){
		length = 4;
		var html = "<p>Viser " + length + " av " + ids.length + " bilder.</p>";
	}
	else {
		var html = "<p>Viser alle bilder (" + length + ")</p>";
	}
	
	imagediv.innerHTML = html;
	sidebar.appendChild(imagediv);
	
	//GLog.write("l=" + length);
	for(var i = 0; i<length;i++){
		//create a div for the image
		showImageThumbnail(ids[i],200);
	}
	
	return sidebar.innerHTML;
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