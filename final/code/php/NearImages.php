<?php
require_once("/var/www/atle/final/code/php/psqlClass.php");
require_once("/var/www/atle/final/code/php/LatLonPoint.php");
require_once("/var/www/atle/final/code/php/configs.php");

/**
* Finds nearby images to a route or image, and prints them back as a gallery and/or 
* JSON-representation for using on a map
* 
* @author Atle Frenvik Sveen <atle at frenviksveen dot net>
* @version 1.0
* 
* @see GeoRefImage
*/
class NearImages{
	
	
	private $images,$type;
	
	private $urls;
	
	/**
	 * Constructor
	 * Takes in the what we are searching near and the id of it, and queries the database to get the images in question
	 *
	 * @access public
	 * @param string $type "route" or "image" 
	 * @param mixed $id the id / code of the route or image
	 */
	public function __construct($type,$id) {
		
		//determine type
		$this->type = $type;
		
		//get paths and urls
		$this->urls = new configClass(urlData());
		
		//check what type we are dealing with and get images up to 1000 meters  (1 km) away 
		if($type=='route'){
			$this->images = $this->nearRoute($id,1000);
		}
		else if ($type = 'image') {
			$this->images = $this->nearImage($id,1000);
		}	
	}
	
	/**
	 * Get the array of images
	 *
	 * @return mixed array of GeoRefImage objects
	 */
	public function getImages(){
		return $this->images;
	}
	
	/**
    * Creates a gallery of nearby images
    *
    * @access public    
    * @param int $width number of images horisontally 
    * @param int $height number of images vertically (0=no limit)
    * @param int $imgwidth width of images in gallery in pixels (default 175px)
    * @return string html for the gallery
    */
	public function createGallery($width,$height,$imgwidth=175){
		$images = $this->getImages();

		$url = $this->urls->imageResizeUrl();
		$imageUrl = $this->urls->imageUrl();	
		
		if(sizeof($images)>0){
			$html = "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\">\n";
	
			$i = 0;
			$run = true;
			while($run) {

				$html .= "\t<tr>\n";
				for ($j = 0;$j<$width;$j++){
					$html .= "\t\t<td>\n";

					if ($i<sizeof($images)) {
						
						$html .= "\t\t\t\t<a href=\"". $imageUrl . $images[$i]->getId()  ."\">\n";
						$html .= "\t\t\t\t\t<img src=\"" . $url . $images[$i]->getFilename()  . "&width=" . $imgwidth . "\"";
						$html .= " border=\"0\" id=\"image_" . $images[$i]->getId() . "\" class=\"normal\"";
						
						//add javascript for map interaction if route
						if($this->type=="route"){
							$html .= " onmouseover=\"focusOnMarker(". $images[$i]->getId() .");\" onmouseout=\"removeFocus();\"";
						}
						$html .= " >\n";
						$html .= "\t\t\t\t</a>\n";
						//$html .= "\t\t\t</div>\n";
					}
					else {
						$run = false;
						$html .= "\t\t\t&nbsp;\n";
					}
					$html .= "\t\t</td>\n";
					$i++;
				}
				$html .= "\t</tr>\n";
			}

			$html .= "</table>";

		}
		else {
			$html = "<p>Ingen bilder funnet</p>";
		}
		
		return $html;
	}
	
	/**
    * Returns image information as JSON
    *
    * @access public
    * @return string JSON representation of the images
    */	
	public function getImagesAsJSON(){
		$images = $this->getImages();
		
		//"decode" the objects..
		$json = array();
		foreach ($images as $key =>$image) {
			$imageJson = array('id'=> $image->getId(), 'lat'=> $image->getLat(), 'lon'=>$image->getLon());
			array_push($json,$imageJson);
			
		}
		return json_encode($json);
	}
	
	/**
    * Finds images within a given treshold of a specified route
    *
    * @access public
    * @param string $id id of the route
    * @param int $treshold distance to search (in meters)
    * @return mixed array of GeoRefImage objects
    */	
	private function nearRoute($id,$treshold){
				
		//create database connection
		$dbConn = new psqlClass(configData(),tableData());
		$dbConn->dbConnect();

		$dntRoutes = $dbConn->routesTable();
		$link = $dbConn->route_partsTable();
		$routes_data = $dbConn->route_dataTable();
		$imageTable = $dbConn->imageTable();
		
		$query = "SELECT DISTINCT " . $imageTable . ".id AS id, " . $imageTable . ".title AS title, " . $imageTable . ".filename AS filename, " . $imageTable . ".username AS username, " . $imageTable . ".season AS season, " . $imageTable . ".description AS description, AsText(" . $imageTable . ".the_geom) AS wkt";
		$query .= " FROM " . $imageTable . ", " . $routes_data;
		$query .= " WHERE ST_DWithin(transform(" . $imageTable . ".the_geom,32633),transform(". $routes_data .".the_geom,32633)," . $treshold .")";
		$query .= " AND ". $routes_data .".gid IN (";
		$query .= " SELECT ". $routes_data .".gid";
		$query .= " FROM ". $routes_data ."," . $link;
		$query .= " WHERE " . $link . ".gid=". $routes_data .".gid"; 
		$query .= " AND " . $link . ".dnt='" . $id ."')";
		
		//query database
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());

		//create result array
		$images = array();
		while ($row = pg_fetch_assoc($result)) {
			$data = array(
				'wktString' => $row['wkt'],
				'id' => $row['id'],
				'title' => $row['title'],
				'filename' => $row['filename'],
				'user' => $row['username'],
				'season' => $row['season'],
				'description' => $row['description']
			);
			array_push($images,new GeoRefImage($data));
		}
	
		//free result
		pg_free_result($result);

		//close connection
		$dbConn->dbClose();
		
		return $images;
	}

	/**
    * Finds images near an image
    *
    * @access public
    * @param string $id id of the route
    * @param int $treshold distance to search (in meters)
    * @return mixed array of GeoRefImage objects
    */	
	private function nearImage($id,$treshold){
		//create database connection
		$dbConn = new psqlClass(configData(),tableData());
		$dbConn->dbConnect();

		//table in database for images
		$imageTable = $dbConn->imageTable();

		//build query
		$query = "SELECT haystack.id AS id, haystack.title AS title, haystack.filename AS filename, haystack.username AS username, ";
		$query .= "haystack.season AS season, haystack.description AS description, AsText(haystack.the_geom) AS wkt";
		$query .= " FROM " . $imageTable .  " AS needle, " . $imageTable . " AS haystack";
		$query .= " WHERE ST_DWithin(transform(haystack.the_geom,32633),transform(needle.the_geom,32633)," . $treshold  . ")";
		$query .= " AND needle.id=" . $id . " AND haystack.id != " . $id;

		//query database
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());

		//create result array		
		$images = array();
		while ($row = pg_fetch_assoc($result)) {
			$data = array(
				'wktString' => $row['wkt'],
				'id' => $row['id'],
				'title' => $row['title'],
				'filename' => $row['filename'],
				'user' => $row['username'],
				'season' => $row['season'],
				'description' => $row['description']
			);
			array_push($images,new GeoRefImage($data));
		}
		
		//free result
		pg_free_result($result);
	
		//close connection
		$dbConn->dbClose();
		
		return $images;
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