/**
  * Changes the value of a node attribute
  *
  * @param string id the id of the elememt
  * @param string attr the attribute to change
  * @param string value the new value of the attibute
  */
function writeNodeValueDom(id,attr,value){
	node = document.getElementById(id);
	node.setAttribute(attr,value);
}

/**
  * Fetches a node in the DOM by id and changes its HTML
  *
  * @param string id the id of the elememt
  * @param string text the new html to insert
  */
function writeToDom(id,text){
	document.getElementById(id).innerHTML = text;
}


/**
  * Updates the lat-lon fields in the DOM
  *
  * @param string id id of the image
  * @param float lat the new latitude
  * @param float lon the new longitude
  */
function updateDomLatLon(id,lat,lon){
	if(debug){
		GLog.write("changing coords for image #" + id + " to (" + lat + "," + lon +") in hidden fields");
	}
	//set the hidden fields
	var index = id-1;
	//lat
	writeNodeValueDom('lat[' + index + ']','value',lat);
	//lon
	writeNodeValueDom('lon[' + index + ']','value',lon);
}

/**
  * Changes the action associated with the button for each imaghe, 
  * and indicates what is done.
  *
  * @param string id id of the image
  * @param string action what to do, either "show" or "hide"
  * @param string type "EXIF" indicates if the image has exif coordinates
  */
function changeAction(id,action,type){
	
	if(action =="hide"){
		writeToDom("imgbtn"+id,"ok?");	
	} 
	else if (action =="show"){
		writeToDom("imgbtn"+id,"endre?");	
	}
	
	if(type=="EXIF"){
		if(debug){
			GLog.write("exif");
		}
		var string = "editImage(" + id + ",'" + action + "',null,null,'EXIF')";	
	}
	else {
		var string = "editImage(" + id + ",'" + action + "')";
	}
	
	writeNodeValueDom('imgbtn' + id,'onClick',string);
}

/**
  * Check if coordinates are given for the images (upon pressing save)
  *
  * @param string check if this is set to "check" the check is done, if "skip" no test is carried out
  * @param int numImages number of images to check
  */
function formSubmit(check,numImages){
	if (check == "skip") {
		document.getElementById("saveImages").submit();
	}
	else if (check == "check") {
		var unReffed = new Array();

		for(var i = 0;i<numImages;i++){
			var lat = document.getElementById("lat[" + i + "]").getAttribute("value");
			var lon = document.getElementById("lon[" + i + "]").getAttribute("value");

			if((!lat) || (!lon) ){
				var img = i+1;
				unReffed.push(img);
			}
		}

		if (unReffed.length==0) {
			document.getElementById("saveImages").submit();
		}
		else {
			var meta = document.getElementById("meta");
			var html = '';
			if (unReffed.length == 1){
				html = "Bilde " + unReffed[0] + " har ikke angitt lokasjon, angi denne eller klikk \"lagre\" for å droppe";
			}
			else {
				var bilder = '';
				for(var i = 0;i<unReffed.length;i++) {	
					
					if (i != unReffed.length-1){
						bilder += unReffed[i] + ", ";	
					}
					else {
						bilder += unReffed[i];	
					}
				}		
				html = "Bildene " + bilder + " har ikke angitt lokasjon, angi denne eller klikk \"lagre\" for å droppe";
			}
			
			var saveButton = document.getElementById("saveBtn");
			saveButton.setAttribute('onClick','formSubmit(\'skip\',null)');
			
			text = document.createTextNode(html);
			meta.appendChild(text);
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