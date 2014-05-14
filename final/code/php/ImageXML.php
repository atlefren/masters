<?php

require_once("/var/www/atle/final/code/php/LatLonPoint.php");
require_once("/var/www/atle/final/code/php/psqlClass.php");

/**
* Creates XML-markup for images
*
* @author Atle Frenvik Sveen <atle at frenviksveen dot net>
* @version 1.0
*/
class ImageXML {
	
	private $all,$ids,$near;
	private $routeId,$treshold;
	private $images;
	
	/**
    * Constructor
    * 
    * Queries the database for an array of GeoRefImage objects, based on parameters, parameters are:
    * all: get all images 
    * ids: get all images specified in the ids array
    * near: gets all images within a treshold of a route, 
    * 		if this is set to true, the routeId and treshold parameters must be provided
    *
    * @access public
    */
	public function __construct($params = array('all'=>null,'ids'=> null, 'near' => null, 'routeId' => null, 'treshold' => null)){
		$this->all = $params['all'];
		$this->ids = $params['ids'];
		$this->near = $params['near'];
		
		if($this->all){
			//get all images in database	
			$this->images = $this->getAllImages();
			
		}
		else if($this->ids) {
			//get images based on id
			$this->images = $this->getImagesById($this->ids);
		}
		else if($this->near) {
			//get images near a route
			$this->routeId = $params['routeId'];
			$this->treshold = $params['treshold'];	
			
			$this->images = $this->getImagesNearRoute($this->routeId,$this->treshold);
		}
		else {
			//something went awfully wrong
			$this->images = null;
		}
		
	}
	
	/**
    * Builds image XML for requested images, handles the case of an image collection if needed
    *
    * @access public
    * @return string|false returns a string with xml-formatted image data, false if no images
    */
	public function getImageXML(){

		$images = $this->images;
		
		//check if we have any images
		if ($images){
			
			$xml = "<images>\n";
					
			//write the <img>-element
			foreach ($images as $key => $image){
				$latlon = $image->getXML();
				$name = $image->getTitle();
				if(!$name){
					//check if title is set
					$name = "Uten navn";
				}
				$filename = $image->getFilename();
				$id =  $image->getId();

				$xml .= "\t\t<img>\n";
				$xml .= "\t\t\t<id>$id</id>\n";
				
				$xml .= "\t\t\t<title>$name</title>\n";
				$xml .= "\t\t\t". $latlon ."\n";
				$xml .= "\t\t\t<filename>$filename</filename>\n";
				$xml .= "\t\t</img>\n";
			}
			
			//end image tag
			$xml .=  "</images>\n";
			
			//return the xml
			return $xml;
		}
		else {
			//no data to return
			return false;
		}
		
	}

	/**
    * Gets all images from the database
    *
    * @access private
    * @return mixed array of GeoRefImage objects
    */
	private function getAllImages(){
		//create database connection
		$dbConn = new psqlClass(configData(),tableData());
		$dbConn->dbConnect();

		//table in database for images
		$imageTable = $dbConn->imageTable();

		//build query
		$query = "SELECT id, title, filename, username, season, description, AsText(the_geom) AS wkt FROM " .  $imageTable;

		//query database
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());

		//create result array
		$images = array();
		$i = 0;
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

			$images[$i] = new GeoRefImage($data);
			$i++;
		}

		//free result
		pg_free_result($result);
		
		//close conenction
		$dbConn->dbClose();
		
		return $images;
	}

	/**
    * Gets images from database based in image ids
    *
    * @access private
    * @param mixed $ids array of image ids
    * @return mixed|bool array of GeoRefImage objects false if no images 
    */
	private function getImagesById($ids){
		
		//create database connection
		$dbConn = new psqlClass(configData(),tableData());
		$dbConn->dbConnect();

		//table in database for images
		$imageTable = $dbConn->imageTable();
		
		//make a comma-separated list of IDs	
		$idList = "";
		foreach ($ids as $key =>$id) {
			$idList .= $id . ",";
		}
		$idList = rtrim($idList,",");

		//build query
		$query = "SELECT id,title,filename,username,season,description,AsText(the_geom) AS wkt FROM " .  $imageTable;
		$query .= " WHERE id IN (" . $idList . ")";

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
		
		//check if there are any images	
		if(sizeof($images) == 0){
			$images = false;
		}

		//free result
		pg_free_result($result);
		
		//close connection
		$dbConn->dbClose();
		
		return $images;
	}

	/**
    * Gets images from database based on treshold from route
    *
    * @access private
    * @param int $id id of route
    * @param int $treshold distance in meters to check
    * @return mixed array of GeoRefImage objects
    */
	private function getImagesNearRoute($id,$treshold){
		//create database connection
		$dbConn = new psqlClass(configData(),tableData());
		$dbConn->dbConnect();

		//table in database for images
		$imageTable = $dbConn->imageTable();
		$imageTable = $dbConn->routeTable();

		//build query
		$query = "SELECT " . $imageTable . ".id AS id, " . $imageTable . ".title AS title, " . $imageTable . ".filename AS filename, ";
		$query .= $imageTable . ".username AS username, " .  $imageTable . ".season AS season, ";
		$query .= $imageTable . ".description AS description, AsText(" . $imageTable . ".the_geom) AS wkt";
		$query .= " FROM " . $imageTable . ", "  . $routeTable;
		$query .= " WHERE ST_DWithin(transform(" . $imageTable . " .the_geom,32633),transform(" . $routeTable . ".the_geom,32633)," . $treshold .  " )";
		$query .= " AND " .$routeTable . ".id=" . $id;

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