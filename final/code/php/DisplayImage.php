<?php
require_once("/var/www/atle/final/code/php/psqlClass.php");
require_once("/var/www/atle/final/code/php/configs.php");

require_once("/var/www/atle/final/code/php/NearRoutes.php");
require_once("/var/www/atle/final/code/php/NearImages.php");
require_once("/var/www/atle/final/code/php/NearPois.php");


/**
*
* This class takes care of displaying a single image. It provides all information needed
* to setup a page describing the image in detail.
* 
* Provides both HTML-elements for lists and descriptions, as well as JSON-formatted data to use for map-display.
* 
* @author Atle Frenvik Sveen <atle at frenviksveen dot net>
* @version 1.0
* 
* @see NearRoutes
* @see NearImages
* @see NearPois
* @see configClass
* @see psqlClass
*/
class DisplayImage {
	
	//properties of this image
	private $id, $title, $season, $photographer, $filename, $description;
	
	//nearby objects
	private $nearRoutes, $nearImages, $nearCabins;
	
	//misc
	private $urls;
	
	/**
    * Constructor
    * 
    * Sets up the image, by fetching database-information and storing the information the the variables. 
    * Also gets information on nearby objects.
    * 
    * @access public
    * @param string $routeCode the image id
    */
	public function __construct($imageId) {
		//the image id specified when creating the object
		$this->id = $imageId;
		
		//get image information from db
		$this->imageFromDb($imageId);
		
		//find nearby routes, images and cabins
		$this->nearRoutes = new NearRoutes('image',$imageId);
		$this->nearImages = new NearImages('image',$imageId);
		$this->nearCabins = new NearPois('image','cabins',$imageId);
		
		//get the various urls and paths used
		$this->urls = new configClass(urlData());
	}
	
	/**
	 * Get the title of the image
	 *
	 * @return string image title
	 */
	public function getTitle(){
		return $this->title;
	}
	
	/**
	 * Get the id of the image
	 *
	 * @return int image id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Get the season of the image (as textual representation)
	 *
	 * @return string the season name
	 */
	public function getSeason(){
		return $this->getTextualSeason($this->season);
	}

	/**
	 * Returns the full url for the image with given width,
	 * see getImage.php
	 * 
	 * @param int $width requested with of image
	 * @return string url to image
	 */
	public function getUrl($width){
		$url = $this->urls->imageResizeUrl();
		//$url = "http://geomatikk.eksplisitt.net/atle/output/getImage2.php?filename=";
		$size = "&width=" . $width; 
		return $url . $this->filename . $size;
	}

	/**
	 * Gets the name of the photographer
	 *
	 * @return string photographer name
	 */
	public function getPhotographer(){
		return $this->photographer;	
	}
	
	/**
	 * Gets an url to show the image in showMap.php
	 *
	 * @return string url for map display
	 */
	public function getMapLink(){
		$url = $this->urls->mapUrl();
		return "<a href=\"" . $url ."?image=" . $this->getId() ." \">Se i kart</a>";
	}
	
	/**
	 * Returns the html-code that make up the nearby images gallery
	 *
	 * @return string html for gallery
	 */
	public function getNearImageGallery() {
		return $this->nearImages->createGallery(5,0,150);	
	}

	/**
	 * Returns a html-formatted list of nearby cabins and their distance to the image
	 *
	 * @return string html list
	 */
	public function getNearCabins(){
		return $this->nearCabins->getList();
	}

	/**
	 * Returns a html-formatted list of nearby routes
	 *
	 * @return string html list
	 */
	public function getNearRoutes(){
		return $this->nearRoutes->getList();
	}
	
	/**
	 * Queries the database (using the psqlClass class) and populates the class variables with information on the image
	 * Called from the constructor
	 * 
	 * 
	 * @param int $imageId id of the image in question
	 */
	private function imageFromDb($imageId){
		$dbConn = new psqlClass(configData(),tableData());
		$dbConn->dbConnect();
		
		$imageTable = $dbConn->imageTable();
		
		$query = "SELECT title,filename,username,season,description FROM " .  $imageTable;
		$query .= " WHERE id = " . $imageId ;
				
		//query database
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());
		
		//get the first (and only) row
		$image = pg_fetch_row($result);
		
		//populate class variables
		$this->title = $image[0];
		$this->filename = $image[1];
		$this->photographer = $image[2];
		$this->season = $image[3];
		$this->description = $image[4];
		
		//free result
		pg_free_result($result);
		
		//close connection
		$dbConn->dbClose();
	}
	
	/**
	 * Converts a numerical representation of season to a norwegian textual representation
	 *
	 * @param int $season integer representing season
	 * @return string norwegian name of season
	 */
	private function getTextualSeason($season){
		switch ($season) { 
    	case (1): 
        	$textSeason = "Vinter";
        	break; 
    	case (2): 
        	$textSeason = "Vår";
        	break; 
    	case (3): 
        	$textSeason = "Sommer";
        	break; 
    	case (4): 
        	$textSeason = "Høst";
        	break; 
        default:
        	$textSeason = "Ikke angitt";
        	break;
    	}
    	return $textSeason;
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