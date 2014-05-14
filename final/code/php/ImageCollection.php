<?php

require_once("/var/www/atle/final/code/php/LatLonPoint.php");
require_once("/var/www/atle/final/code/php/configs.php");

/**
 * This class deals with parsing uploaded images, checking wether they are geo-referenced and 
 * displays the page that lets users enter various information about the image, in addition to showing a map 
 * that supports the task of veryfying pre-geotagged images or geotagging new ones.
 * 
 * @author Atle Frenvik Sveen <atle at frenviksveen dot net>
 * @version 1.0
 *
 */
class ImageCollection {

	//the image information, before and after parsing it
	private $files,$images;
	
	//misc
	private $urls;
	
	/**
    * Constructor 
    * Takes in upoaded images POST data and organizes the georeferencing of them
    * 
    * @see GeoRefImage
    * 
    * @access public
    * @param mixed $files POST data
    */
	public function __construct($files) {
		//gets used urls and paths
		$this->urls = new configClass(urlData());
		
		//the POST data
		$this->files = $files;
		
		//parse the post data and store as an array of GeoRefImage objects
		$this->images = $this->parseUpload($files);
		
	}
	
	/**
	 * Returns the JavaScript code for setting up pre-georeffed images on the map
	 *
	 * @access public
	 * @return string JavaScript code
	 */
	public function getMapInit() {
		//check if there are any pre-geotagged images among the uploaded ones
		$num = $this->getNumTaggedImages();
		if($num == 0) {
			$mapInit = "setupMap();";
		}
		else if ($num == 1) {
			$images = $this->getGeoTaggedImages();
			$image = $images[0];
			$mapInit = "setupMap(" . $image->getLat() . "," . $image->getLon() . ");";
		}
		else {	
			$bounds = $this->getBounds();
			$mapInit = "setupMap(" . $bounds['maxLat'] . "," . $bounds['maxLon'] . "," . $bounds['minLat'] . "," . $bounds['minLon'] . ");";
		}
		return $mapInit;
	}

	/**
	 * Returns an array of all images in the collection as GeoRefImage objects
	 *
	 * @access public
	 * @return mixed array of GeoRefImage objects
	 */
	public function getImages(){
		return $this->images;
	}

	/**
	 * Retuens an array of pre-geotagged images in the collection as GeoRefImage objects
	 *
	 * @access public
	 * @return mixed array of GeoRefImage objects
	 */
	public function getGeoTaggedImages() {
		$georef = Array();
		$i = 0;

		//loop through, test if georeffed
		foreach ($this->images as $key =>$image) {
			if ($image->isGeoref()){
				$georef[$i] = $image;
				$i++;
			}
		}
		//return
		return $georef;
	}

	/**
	 * returns the html for all image boxes
	 *
	 * @return string html 
	 */
	public function getImageBoxes() {
		$html = '';
		foreach ($this->images as $key =>$image) {
			$html .= $this->makeImageBoxFromGeoRef($image);
		}
		return $html;
	}

	/**
	 * Returns the number of images in the collection
	 *
	 * @access public
	 * @return int number of images
	 */
	public function getNumImages(){
		return sizeof($this->images);
	}

	/**
	 * Returns the number of geotaggedimages in the collection
	 *
	 * @access public
	 * @return int number of geotagged images
	 */	
	public function getNumTaggedImages(){
		//returns the number of images in the collection
		return sizeof($this->getGeoTaggedImages());
	}
	
	/**
	 * Create JavaScript code to initialize the geotagged images on the map
	 *
	 * @see getNumTaggedImages()
	 * @access public
	 * @return string|null javacript code or null if no geotagged images
	 */
	public function getMapMarkerSetup(){
		//check if there are any geotagged images
		if($this->getNumTaggedImages() > 0) {
			$printImages = "//setup points with initially given coords\n";
			foreach ($this->getGeoTaggedImages() as $key =>$image) {
				$printImages .= "createImageMarker(" . $image->getId() . "," . $image->getLat() . "," . $image->getLon() . ");\n\t\t";
			}
			return $printImages;
		}
		else {
			return null;
		}
	}
	
	/**
	 * Returns the bounds for the georeffed images in the collection
	 * 
	 * @see getNumTaggedImages()
	 * @access private
	 * @return mixed associative array of max and min lats and lons
	 */	
	private function getBounds() {
		
		//get geotagged images
		$images = $this->getGeoTaggedImages();
		foreach ($images as $key =>$image) {
			if ($image->isGeoref()){
				$lats[$key] = $image->getLat();
				$lons[$key] = $image->getLon();
			}
		}
		//create 
		return array('maxLat' => max($lats), 'maxLon' => max($lons), 'minLat' => min($lats), 'minLon' =>min($lons));
	}
	
	/**
	 * Parses the POST-data for the uploaded images
	 *
	 * @param mixed $files POST data
	 * @return mixed array of GeoRefImage objects
	 */
	private function parseUpload($files) {
	
		//the directory where the images are to be saved to
		//$temp_dir = '/media/disk1/www/data/img/tmp/';
		$temp_dir = $this->urls->tempDir();

		//initializing variables
		$html = "";
		
		//username dummy (via SESSION variable I would guess)
		$username = "atlefren";
		
		//loop through input
		foreach ($files["pictures"]["error"] as $key => $error) {
			//check if image is uploaded correctly
			if ($error == 0) {
		
				//get the filename of the uploaded file
				$filename[$key] = basename($files['pictures']['name'][$key]);
				
				//split filename
				$temp[$key] = explode(".", $filename[$key]);
				$base[$key] = $temp[$key][0];
				$ext[$key] = $temp[$key][1];
							
				//check filetype
				if ((($ext[$key] == "jpg") || ($ext[$key] == "JPG")) && ($files["pictures"]["type"][$key] == "image/jpeg")) {

					//create new filename as md5 of filename + timestamp + username
					$name = md5($base[$key] . time() . $username) . "." . strtolower($ext[$key]);
					
					//the temporary filename (where it is temporarily stored)
					$tmp_name = $files["pictures"]["tmp_name"][$key];

					//copy the uploaded image to a more persistent temp-storage
					if ((move_uploaded_file($files['pictures']['tmp_name'][$key],$temp_dir . $name))) {
						//assume that the image is not geotagged
						$tagged = false;

						//check wether the EXIF contains timestamp to determine season
						$season = $this->getSeason($temp_dir . $name);

						//create the a GeoRefImage object, using $key+1 as image id
						$data = array('filename' => $temp_dir . $name, 'id'=>$key+1, 'season' => $season);
						$img[$key] = new GeoRefImage($data);
					}
				}
				else {
					// wrong filetype, silent error
				}
			}
		}
		return $img;
	}

	/**
	 * Use EXIF-info to see wether season can be determined
	 *
	 * @access private 
	 * @param string $filename 
	 * @return int number of season (0 means not set)
	 */
	private function getSeason($filename){
		//get EXIF-INFO
		$exif_date = exif_read_data($filename, 'IFD0', 0);

		//we have data
		if ($exif_date['DateTimeOriginal'])	{

			//get dateTimeOriginal
			$edate = $exif_date['DateTimeOriginal'];

			//get the month
			$date = explode(" ",$edate);
			$month = explode(":",$date[0]);
			$month = $month[1];
			//decide the season (wikipedia rules)
			switch ($month) {
				case ($month=='12' || $month=='01' || $month=='02'):
				$season = 1;
				break;
				case ($month=='03' || $month=='04' || $month=='05'):
				$season = 2;
				break;
				case ($month=='06' || $month=='07' || $month=='08'):
				$season = 3;
				break;
				case ($month=='09' || $month=='10' || $month=='11'):
				$season = 4;
				break;
			}
		}
		else {
			//no season available
			$season= 0;
		}
		return $season;
	}
	
	/**
	 * Creates a single image-box (HTML)
	 *
	 * @param GeoRefImage $img image to make the box for
	 * @return string html for the box
	 */
	private function  makeImageBoxFromGeoRef($img) {
		
		//get info from the GeoRefImage object
		$id = $img->getId();
		$index = $id-1;
		$filename = $img->getFilename();
		$position = $img->isGeoref();
		$season = $img->getSeason();

		//different colors for image frames (could possibly be solved a lot prettier)
		$colors = array("#FF0000","#FFC403","#ABFF03","#01FF34","#06FFFB","#0558FF","#A011FF","#FF00D9","#D5FFAE");
		
		//start write html
		$html = "\n<!-- imagebox start (image " . $id . ")-->\n";
		$html .= "<div class=\"imagebox\" id=\"ib" . $id ."\">\n";
		$html .= "\t<div class=\"right\" id=\"ib_right" . $id . "\">\n";
		$html .= "\t\tTittel: <br />\n";
		$html .= "\t\t<input type=\"text\" id=\"title[" . $index . "]\" name=\"title[" . $index . "]\" class=\"textbox\" /><br />\n";
		$html .= $this->seasonSelectBox($season,$index);

		//check if image has coordinates in EXIF (ie: is it georeffed?)
		if($position){
			$html .= "\t\t<span id=\"image_status_" . $id . "\">Fra EXIF</span>";
			$html .= " (<span class=\"link\" id=\"image_status_btn_" . $id . "\"";
			$html .= " onClick=\"imageClick(" . $id.  ",'hide_exif')\">";
			$html .= "ok?</span>)\n";		
			
			//storage for coords from exif
			$html .= "\t\t<input type=\"hidden\" name=\"exif_lat[" . $index . "]\" id=\"exif_lat[" . $index . "]\" value=\"" . $img->getLat() . "\" />\n";
			$html .= "\t\t<input type=\"hidden\" name=\"exif_lon[" . $index . "]\" id=\"exif_lon[" . $index . "]\" value=\"" . $img->getLon() . "\" />\n";
			
		}
		else {
			//no coords are given
			$html .= "\t\t<span id=\"image_status_" . $id . "\">Ikke angitt</span>";
			$html .= " (<span class=\"link\" id=\"image_status_btn_" . $id . "\"";

			//ask user to place on the map
			$html .= " onClick=\"imageClick(" . $id.  ",'create')\">";
			$html .= "angi?</span>)\n";
		}

		// the coords of the image (filled in if present)
		//subject to be changed by javascript
		$html .= "\t\t<input type=\"hidden\" name=\"lat[" . $index . "]\" id=\"lat[" . $index . "]\" value=\"" . $img->getLat() . "\" />\n";
		$html .= "\t\t<input type=\"hidden\" name=\"lon[" . $index . "]\" id=\"lon[" . $index . "]\" value=\"" . $img->getLon() . "\" />\n";

		//the image id
		$html .= "\t\t<input type=\"hidden\" name=\"id[" . $index . "]\" id=\"id[" . $index . "]\" value=\"" . $id ."\" />\n";

		//the filename
		$html .= "\t\t<input type=\"hidden\" name=\"filename[" . $index . "]\" id=\"filename[" . $index . "]\" value=\"" . $filename ."\" />\n";
		$html .= "\t</div>\n";

		//show the image
		$html .= "\t<div class=\"img\" style=\"background:" . $colors[$id-1] . ";\" onClick=\"centerOnMarker(" . $id . ")\">\n";
		$html .= "\t\t<img src=\"" . $this->urls->thumbnailUrl() . $filename . "&size=150\" />\n";
		//$html .= "\t\t<img src=\"http://geomatikk.eksplisitt.net/atle/output/thumb.php?filename=" . $filename . "&size=150\" />\n";
		$html .= "\t</div>\n";
		$html .= "</div>\n";
		$html .= "<!-- imagebox end -->\n\n";
		
		//return the html 
		return $html;
	}
	
	/**
	 * Generates select season SELECT box for HTML 
	 * 
	 *
	 * @access private
	 * @param int $season season of the image
	 * @param int $index image index
	 * @return string html representing the SELECT-box
	 */
	private function seasonSelectBox($season,$index) {
		$html .= "\t\tÅrstid:<br />\n";
		$html .= "\t\t<SELECT name=\"season[" . $index . "]\" id=\"season[" . $index . "]\" class=\"textbox\">\n";
		if($season == 1){
			$html .= "\t\t\t<option value=\"0\">Velg årstid..</option>\n";
			$html .= "\t\t\t<OPTION VALUE=\"1\" selected=\"yes\">Vinter</OPTION>\n";
			$html .= "\t\t\t<OPTION VALUE=\"2\">Vår</OPTION>\n";
			$html .= "\t\t\t<OPTION VALUE=\"3\" >Sommer</OPTION>\n";
			$html .= "\t\t\t<OPTION VALUE=\"4\">Høst</OPTION>\n";
		}
		else if ($season == 2) {
			$html .= "\t\t\t<option value=\"0\">Velg årstid..</option>\n";
			$html .= "\t\t\t<OPTION VALUE=\"1\">Vinter\n";
			$html .= "\t\t\t<OPTION VALUE=\"2\" selected=\"yes\">Vår\n";
			$html .= "\t\t\t<OPTION VALUE=\"3\" >Sommer\n";
			$html .= "\t\t\t<OPTION VALUE=\"4\">Høst\n";
		}
		else if ($season == 3) {
			$html .= "\t\t\t<option value=\"0\">Velg årstid..</option>\n";
			$html .= "\t\t\t<OPTION VALUE=\"1\">Vinter</OPTION>\n";
			$html .= "\t\t\t<OPTION VALUE=\"2\">Vår</OPTION>\n";
			$html .= "\t\t\t<OPTION VALUE=\"2\" selected=\"yes\">Sommer</OPTION>\n";
			$html .= "\t\t\t<OPTION VALUE=\"4\">Høst</OPTION>\n";
		}
		else if ($season == 4) {
			$html .= "\t\t\t<option value=\"0\">Velg årstid..</option>\n";
			$html .= "\t\t\t<OPTION VALUE=\"1\">Vinter</OPTION>\n";
			$html .= "\t\t\t<OPTION VALUE=\"2\">Vår</OPTION>\n";
			$html .= "\t\t\t<OPTION VALUE=\"3\">Sommer</OPTION>\n";
			$html .= "\t\t\t<OPTION VALUE=\"4\" selected=\"yes\">Høst</OPTION>\n";
		}
		else {
			$html .= "\t\t\t<option value=\"0\" selected=\"yes\">Velg årstid..</option>\n";
			$html .= "\t\t\t<OPTION VALUE=\"1\">Vinter</OPTION>\n";
			$html .= "\t\t\t<OPTION VALUE=\"2\">Vår</OPTION>\n";
			$html .= "\t\t\t<OPTION VALUE=\"3\">Sommer</OPTION>\n";
			$html .= "\t\t\t<OPTION VALUE=\"4\">Høst</OPTION>\n";
		}

		$html .= "\t\t</SELECT><br />\n";
		$html .= "\t\tPosisjon:<br />\n";

		return $html;
	}

}//end class

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