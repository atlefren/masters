<?php

require_once("/var/www/atle/final/code/php/psqlClass.php");
require_once("/var/www/atle/final/code/php/LatLonPoint.php");
require_once("/var/www/atle/final/code/php/configs.php");
require_once("/var/www/atle/final/code/php/functions.php");

/**
* Finds nearby pois of given type to a route or image, and prints them back as a list and/or 
* JSON-representation for using on a map
* 
* @author Atle Frenvik Sveen <atle at frenviksveen dot net>
* @version 1.0
* 
* @see LatLonPoint
*/
class NearPois{
	
	private $pois;
	private $poitype;
	private $urls;
	
	/**
	 * Constructor
	 * Determines what we are searching near and gets the images within a treshold
	 *
	 * @access public
	 * @param string $type either "route" or "image"
	 * @param string $poitype the type of poi we want (e.g. "cabins")
	 * @param mixed $id id/code of route/image
	 */
	public function __construct($type,$poitype,$id) {
		//determine poi type
		$this->poitype = $poitype;
		
		//paths and urls
		$this->urls = new configClass(urlData());	
		
		//check if we search near image or route
		if($type=='route'){
			$this->pois = $this->nearRoute($id,20000);
		}
		else if ($type = 'image') {
			$this->pois = $this->nearImage($id,10000);
		}	
	}
	
	/**
	 * Gets the type of pois this object represents
	 * 
	 * @access public
	 * @return string description of poi type
	 */
	public function getPoiType(){
		return $this->poitype;
	}
	
	/**
    * Creates a html-list of the nearby pois and their distance to our image/route
    *
    * @access public
    * @return string html list
    */	
	public function getList(){
		$pois = $this->pois;
		$url = $this->urls->cabinUrl();

		if(sizeof($pois)>0){
			$html = "<ul>\n";

			foreach($pois as $poi){
				$html .= "\t<li><a href=\"" . $url . $poi['id'] . "\">" . $poi['name'] . "</a> (" . convertDistance($poi['distance']) . ")</li>\n";
			}
			$html .= "</ul>\n";
		}
		else{
			$html = "<p>Ingen nærliggende hytter funnet.</p>";
		}
		return $html;
	}
	
	/**
    * Returns poi information as JSON
    *
    * @access public
    * @return string JSON representation of the images
    */	
	public function getAsJSON(){
		return json_encode($this->pois);
	}
	
	/**
    * Finds pois within a given treshold of a specified route
    *
    * @access private
    * @param string $id id of the route
    * @param int $treshold distance to search (in meters)
    * @return mixed array of poi names and ids
    */	
	private function nearRoute($id,$treshold){
								
		//create database connection
		$dbConn = new psqlClass(configData(),tableData());
		$dbConn->dbConnect();
		
		$dntRoutes = $dbConn->routesTable();
		$link = $dbConn->route_partsTable();
		$routes_data = $dbConn->route_dataTable();
		$poiTable = $dbConn->poiTable();
		
		$poitype = $this->getPoiType();
		
		$subQuery = "SELECT (ST_Dump(the_geom)).geom As the_geom";
		$subQuery .= " FROM " . $routes_data . ", " . $link . ", " . $dntRoutes;
		$subQuery .= " WHERE " . $link . ".dnt = " . $dntRoutes . ".kode";
		$subQuery .= " AND " . $link . ".gid = " . $routes_data . ".gid";
		$subQuery .= " AND	" . $dntRoutes . ".kode = '" . $id . "'";
		
		$query = "SELECT " . $poiTable . ".id AS id, " . $poiTable . ".poi_name as name, AsText(" . $poiTable . ".the_geom) AS wkt,";
		$query .= " ST_Distance(transform(ST_Multi(ST_Collect(route.the_geom)),32633),transform(" . $poiTable . ".the_geom,32633)) as distance";
		$query .= " FROM (" . $subQuery . ") As route, pois";
		$query .= " GROUP BY " . $poiTable . ".the_geom," . $poiTable . ".poi_name," . $poiTable . ".id";
		$query .= " HAVING ST_Distance(transform(ST_Multi(ST_Collect(route.the_geom)),32633),transform(" . $poiTable . ".the_geom,32633)) < " . $treshold;
		$query .= " ORDER BY distance";
	
		//query database
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());

		//create result array
		$pois = array();
		while ($row = pg_fetch_assoc($result)) {
			$point = new LatLonPoint(array('wktString'=>$row['wkt']));
			$lat = $point->getLat();
			$lon = $point->getLon();
			$poi = array(
					'id' => $row['id'],
					'name' => $row['name'],
					'lat' => $lat,
					'lon' => $lon,
					'distance' => $row['distance']
			);
			array_push($pois,$poi);
		}
	
		//free result
		pg_free_result($result);

		//close connection
		$dbConn->dbClose();
		
		return $pois;
	}
	
	/**
    * Finds pois near an image
    *
    * @access private
    * @param string $id id of the image
    * @param int $treshold distance to search (in meters)
    * @return 
    */	
	private function nearImage($id,$treshold){
		
		//create database connection
		$dbConn = new psqlClass(configData(),tableData());
		$dbConn->dbConnect();
	
		//tables in database for images and pois	
		$poiTable = $dbConn->poiTable();
		$imageTable = $dbConn->imageTable();
		
		$poitype = $this->getPoiType();
		
		//build query
		$query = "SELECT " . $poiTable . ".id AS id, " . $poiTable . ".poi_name AS name, ";
		$query .= "ST_Distance(transform(" . $poiTable . ".the_geom,32633),transform(" . $imageTable .  ".the_geom,32633)) AS distance ";
		$query .= "FROM " . $imageTable . "," . $poiTable;
		$query .= " WHERE ST_DWithin(transform(" . $poiTable . ".the_geom,32633),transform(" . $imageTable . ".the_geom,32633)," . $treshold . ")";
		$query .= " AND " . $imageTable . ".id = " . $id;
		$query .= " AND " . $poiTable . ".poi_type='" . $poitype . "'";
		$query .= " ORDER BY distance";
		
		//query database
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());

		//build result array
		$pois = array();
		
		while ($row = pg_fetch_assoc($result)) {
			$poi = array(
			'id' => $row['id'],
			'name' => $row['name'],
			'distance' => $row['distance']
			);			
			array_push($pois,$poi);
			
		}
			
		//free result
		pg_free_result($result);

		//close connection
		$dbConn->dbClose();
		
		return $pois;
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