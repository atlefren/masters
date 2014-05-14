<?php
/**
 * 
 * The starting page for uploading images. Presents 5 upload boxes. Nothing fancy at all...
 */
include("style/header.php");
echo "\n";
?>
</head>
<body>
<div id="wrap">

	<div id="header">
		<h1>Upload test</h1>
	</div>	
	
	<!-- Upload div START -->			
	<div id="upload" style="width:540px;">
		<h2>Last opp bilder</h2>
		<form action="georefImages.php" method="post" enctype="multipart/form-data" name="form1" id="form1">
			<div id="uploads">
			# 1 <input name="pictures[]" type="file" id="pictures[]" size="50" style="margin-top:4px;" /><br />
			# 2 <input name="pictures[]" type="file" id="pictures[]" size="50" style="margin-top:4px;" /><br />
			# 3 <input name="pictures[]" type="file" id="pictures[]" size="50" style="margin-top:4px;" /><br />
			# 4 <input name="pictures[]" type="file" id="pictures[]" size="50" style="margin-top:4px;" /><br />
			# 5 <input name="pictures[]" type="file" id="pictures[]" size="50" style="margin-top:4px;" /><br />
			</div>
			<div style="float:right;">
				<!--<span onclick="addUpload(); return false;">Flere..</span><br/>-->
				<input type="submit" name="Submit" id="submit" value="Upload" style="margin-top:10px;">
			</div>
			<div>
			<h4>MERK</h4>
			<p>Dette er en testversjon og har dermed en del feil/mangler. Vær oppmerksom på følgende:
			</p>
			<ul>
			<li>Kun jpeg-bilder støttes (ingen feilmelding gies)</li>
			<li>Posisjonsinformasjon lagret i EXIF støttes.</li>
			<li>Hvis du ikke velger noen bilder og trykker "Upload" tryner hele driten</li>
			<li>Søket bruker geonames.org, denne kan være treg som et uvær (og noen ganger tryne) Ha tolmdighet eller dropp det</li>
			<li>Hvis du ikke trykker "lagre" på neste side legges ikke bildene i databasen</li>
			<li>Javascript er kun testet med Firefox 3, kan godt tryne ellers ;)</li>
			</ul>
			</div>
		</form>
	</div>
	<!-- Upload div END -->			
<?php
include("style/footer.php");

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
?>