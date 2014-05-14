/**
  * Sets up the search-box and processes the search
  *
  * @param int id image id
  */
function search(id) {
	var textbox = "nameSearch" + id;
	var string = document.getElementById(textbox).value;

	var text = "you searched for the content in the textbox \""+ textbox + "\" - ";
	text += " search expression: \"" + string + "\"";

	//geonames search, limit to Norway
	var url = encodeURIComponent("http://ws.geonames.org/searchJSON?q=" + string + "&country=NO");
	
	//Pass through a proxy, in order to avoid security problems
	url = "http://geomatikk.eksplisitt.net/atle/final/jsonProxy.php?url=" + url;
	
	var a = new Ajax.Request(url, {
		method:'get',
		onFailure: function(){
			alert("En feil oppsto");
			if(debug){
				GLog.write("fail");
			}
		},

		onLoading: function() {
			//get the div
			var placeSearchDiv = document.getElementById("placeSearch" + id);
			//create div for message
			var loading = document.createElement("div");
			loading.setAttribute("id","loading" +id);

			//create the text
			var text = document.createTextNode("Søker..");

			var loading_gfx = document.createElement("img");
			loading_gfx.src = "http://geomatikk.eksplisitt.net/atle/gfx/indicator.white.gif"
			loading.appendChild(text);
			loading.appendChild(loading_gfx);

			//remove previous search results
			if(document.getElementById("dropdown" + id)){
				document.getElementById("dropdown" + id).remove();
			}
			//append
			placeSearchDiv.appendChild(loading);
		},

		onLoaded: function(){
			//remove the div again
			document.getElementById("loading" + id).remove();
		},

		onSuccess: function(transport){
			var json = transport.responseText.evalJSON(true);
			//alert(json);
			processJSON(json,id);
		}
	});
}

/**
  * Processes the search-result JSON and displays the search results
  *
  * @param JSON JData the search result returned
  * @param int id image id
  */
function processJSON(jData,id) {
	if (jData == null) {
		// There was a problem parsing search results
		return;
	}

	var searchRes = document.createElement("div");
	searchRes.setAttribute("class","dropdown");
	searchRes.setAttribute("id","dropdown" +id);

	var geonames = jData.geonames;
	if(geonames.length >0){
		for (i=0;i< geonames.length;i++) {
			var name = geonames[i];
			if (name.fcode != 'HTL'){

				option = document.createElement("div");

				text = document.createTextNode(name.name + " (" + name.fcode + ")");
				//(" + name.adminName1 +  ")

				option.appendChild(text);
				//editImage(id,action,lat,lon)
				option.setAttribute("onClick","moveMarker(" + id + "," +  name.lat + "," + name.lng + ");setSearchWord(" + id +",'" + name.name + "');");
				option.setAttribute("class","option");

				searchRes.appendChild(option);

			}
		}
	}
	else {
		option = document.createElement("div");
		searchRes.appendChild(option);
		text = document.createTextNode("Ingen treff. Søk igjen?");
		option.appendChild(text);
		searchRes.appendChild(option);
		setSearchWord(id,"");
	}
	document.getElementById("ib" + id).appendChild(searchRes);
}

/**
  * Shows the search box
  *
  * @param int id image id
  */
function showSearchBox(id){
	if(debug){
		GLog.write("showing search box for image #" + id);
	}
	var ib_right = document.getElementById("ib_right" + id);
	var placeSearch = document.createElement("div");
	placeSearch.setAttribute("id","placeSearch" + id);

	//create the text-field
	var textfield = document.createElement("input");
	textfield.setAttribute("type","text");
	textfield.setAttribute("id","nameSearch" + id);
	textfield.setAttribute("name","nameSearch" + id);
	textfield.setAttribute("class","textbox_short");
	//add to div
	placeSearch.appendChild(textfield);

	//create the button
	var button = document.createElement("input");
	button.setAttribute("type","button");
	button.setAttribute("id","nameSearchBtn" + id);
	button.setAttribute("name","nameSearchBtn" + id);
	button.setAttribute("value","Søk..");
	button.setAttribute("onClick","search(" + id + ")");
	//add to div
	placeSearch.appendChild(button);

	//add div to parent
	ib_right.appendChild(placeSearch);
}

/**
  * Hides the search box
  *
  * @param int id image id
  */
function hideSearchBox(id){
	if(debug){
		GLog.write("hiding search box for image #" + id);
	}
	if(document.getElementById("placeSearch" + id)){
		document.getElementById("placeSearch" + id).remove();
	}
	hideSearchResults(id);
}

/**
  * Hides the search results from the search box
  *
  * @param int id image id
  */
function hideSearchResults(id) {
	if(document.getElementById("dropdown" + id)){
		document.getElementById("dropdown" + id).remove();
	}
}

/**
  * Changes the value of the search field
  *
  * @param int id image id
  * @param string value the new value for the field
  */
function setSearchWord(id,value){
	var textfield = document.getElementById("nameSearch" + id);
	if(debug){
		GLog.write("changing value of textfield " + id + " to " + value);
	}
	textfield.value = value;
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