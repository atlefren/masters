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
<h1>Final Prototype</h1>
<p>Dette er inngangsportalen til prototypen utviklet i masteroppgaven min. Mer beskrivelse kommer. Følgende funksjonalitet kan testes:</p>
<ul>
	<li>Last opp ett eller flere bilder og geotagg dem: <a href="uploadImages.php">uploadImages.php</a>. For et bilde med EXIF-headerinformasjon, prøv f.eks: <a href="http://dl.getdropbox.com/u/860041/tagged_exiftool.jpg">dette</a></li>
	<li>Se et oversiktskart: <a href="showMap.php">showMap.php</a></li>
	<li>Se alle bilder:  <a href="showImage.php">showImage</a></li>
	<li>Se på infosiden for en rute (f.eks. <a href="http://geomatikk.eksplisitt.net/atle/final/showRoute.php?route=har138v">Dyranut-Hadlaskard</a> eller <a href="http://geomatikk.eksplisitt.net/atle/final/showRoute.php?route=har104v">Sandhaug-Litlos</a>)</li>
</ul>

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