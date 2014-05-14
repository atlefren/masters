<?php
require_once("/var/www/atle/final/code/php/psqlClass.php");
require_once("/var/www/atle/final/code/php/LatLonLine.php");

require_once("/var/www/atle/final/code/php/NearRoutes.php");
require_once("/var/www/atle/final/code/php/NearImages.php");
require_once("/var/www/atle/final/code/php/NearPois.php");
require_once("/var/www/atle/final/code/php/RelatedTrips.php");

/**
*
* This class takes care of displaying a single DNT-route. It provides all information needed
* to setup a page describing a route in detail.
* 
* Provides both HTML-elements for lists and descriptions, as well as JSON-formatted data to use for map-display.
* 
* @author Atle Frenvik Sveen <atle at frenviksveen dot net>
* @version 1.0
* 
* @see NearRoutes
* @see NearImages
* @see NearPois
* @see RelatedTrips
* @see LatLonLine
* @see psqlClass
*/
class DntRoute{
	
	//properties of this route
	private $code,$name,$description,$length,$season,$area;
	
	//nearby objects
	private $nearRoutes,$nearImages,$nearCabins,$relatedTrips;
		
	/**
    * Constructor
    * 
    * Sets up the route, by fetching database-information
    * 
    * @access public
    * @param string $routeCode the code of the route
    */
	public function __construct($routeCode) {
		//get route code specified when setting up the route
		$this->code = $routeCode;
		
		//get information on this route from DB
		$this->routeFromDb($routeCode);
		
		//find nearby routes, images and cabins and related trips
		$this->nearRoutes = new NearRoutes('route',$routeCode);
		$this->relatedTrips = new RelatedTrips('route',$routeCode);
		$this->nearImages = new NearImages('route',$routeCode);
		$this->nearCabins = new NearPois('route','cabins',$routeCode);		
	}

	/**
    * Returns the code (id) of the route (DNT-code)
    *
    * @access public
    * @return string the route code 
    */	 
	public function getCode(){
		return $this->code;
	}
		
	/**
    * Returns the name of the route
    *
    * @access public
    * @return string the route name
    */	 
	public function getName(){
		return $this->name;
	}
	
	/**
    * Returns a textual description of the route
    *
    * @access public
    * @return string the route description 
    */	 
	public function getDescription(){
		if($this->description){
			return $this->description;
		}
		else {
			return "<p>Denne ruten har ingen beskrivelse</p>";
		}
	}
	
	/**
    * Returns the length of the route (as stored in db)
    *
    * @access public
    * @return string the length of the route in KMs (DNT formatted)
    */	 
	public function getLength(){
		return $this->length;
	}
	
	/**
    * Returns the season of the route (DNT formatted)
    *
    * @access public
    * @return string the season
    */	 
	public function getSeason(){
		return $this->season;
	}
	
	/**
    * Returns a textual description of the area the route is in 
    *
    * @access public
    * @return string the area
    */	 
	public function getArea(){
		return $this->area;
	}
	
	/**
    * Prints the fact-list for the route
    *
    * @see getLength()
    * @see getArea()
    * @see getSeason()
    * 
    * @access public
    * @return string html for list
    */	
	public function printFactList(){
		
		if($this->getLength()!=''){
			$length = $this->getLength() . " km";
		}
		else{
			$length ="n/a";
		}
			
		$html = "<ul>\n";
		$html .= "\t<li>Lengde: " . $length . "</li>\n";
		$html .= "\t<li>Område: " . $this->getArea() . "</li>\n";
		$html .= "\t<li>Sesong: " . $this->getSeason() . "</li>\n";
		$html .= "</ul>\n";
		
		echo $html;
	}
	
	/**
    * Prints the list of related trips
    *
    * @see RelatedTrips::getList()
    * 
    * @access public
    * @return string html for list
    */		
	public function printRelatedTripsList(){
		echo $this->relatedTrips->getList();
	}
	
	/**
    * Prints the list of nearby routes
    *
    * @see NearRoutes::getList()
    * 
    * @access public
    * @return string html for list
    */		
	public function printNearRoutesList(){
		echo $this->nearRoutes->getList();
	}

	/**
    * Prints the list of nearby cabins
    *
    * @see Nearcabins::getList()
    * 
    * @access public
    * @return string html for list
    */		
	public function printNearCabinsList(){
		echo $this->nearCabins->getList();
	}
		
	/**
    * Prints a gallery of nearby images
    *
    * @see NearImages::createGallery()
    * 
    * @access public
    * @param int $width number of images horisontally (default 5)
    * @param int $height number of images vertically (0 = no limit, default 0)
    * @return string html for gallery
    */
	public function printRelatedImagesGallery($width=5,$height=0){
		echo $this->nearImages->createGallery($width,$height);
	}
	
	/**
    * Prints the nearby routes as JSON
    *
    * @see NearRoutes::getRouteLines()
    * 
    * @access public
    * @return string JSON
    */		
	public function printNearRoutePartsJson(){
		echo json_encode($this->nearRoutes->getRouteLines());
	}

	/**
    * Prints the line ids associated with the related trips as JSON
    *
    * @see RelatedTrips::getTripLines()
    * 
    * @access public
    * @return string JSON
    */
	public function printRelatedTripPartsJson(){
		echo json_encode($this->relatedTrips->getTripLines($this->getCode()));
	}
		
	/**
	 * Prints information for the nearby images as JSON 
	 * (for displaying on the map)
	 *
	 * @see NearImages::getImagesAsJSON()
	 * 
	 * @access public
     * @return string JSON
	 */
	public function printNearImagesJson(){
		echo $this->nearImages->getImagesAsJSON();
	}

	/**
	 * Prints information for the nearby cabins as JSON 
	 * (for displaying on the map)
	 *
	 * @see NearCabins::getAsJSON()
	 * 
	 * @access public
     * @return string JSON
	 */
	public function printNearCabinsJson(){
		echo $this->nearCabins->getAsJSON();	
	}
	
	/**
    * Gets the line geometries that makes up the route as JSON
    * by querying the database
    *
    * @access public
    * @return string JSON-formatted line objects
    */	 
	public function getRouteParts(){
		//create database connection
		$dbConn = new psqlClass(configData(),tableData());
		$dbConn->dbConnect();

		//database tables
		$dntRoutes = $dbConn->routesTable();
		$link = $dbConn->route_partsTable();
		$lines = $dbConn->route_dataTable();
			
		//subquery to get line ids
		$subQuery = "SELECT gid FROM " . $link . " WHERE dnt='" . $this->getCode() . "'";

		//build query
		$query = "SELECT gid, AsText(the_geom) AS wkt FROM " . $lines;
		$query .= " WHERE gid IN (" . $subQuery . ")";
		
		//query database
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());
		
		//create an array of latLonLine objects to hold the result
		$lines = array();
		while ($row = pg_fetch_assoc($result)) {
			$data = array(
				'wktString' => $row['wkt'],
				'id' => $row['gid']
			);
			array_push($lines,new LatLonLine($data));
		}
		
		//free result
		pg_free_result($result);
		
		//close conenction
		$dbConn->dbClose();
		
		//create the JSON-array
		$json = array();
		foreach($lines as $key=>$line){
			$json[$key] = $line->getJson(false);
		}
		
		//encode as JSON-string and return it
		//echo json_encode($json);
		return json_encode($json);
	}
	
	/**
	 * Gets the line segments that makes up the nearby routes as JSON
	 * (excluding those that are part of this route)
	 * 
     * @access public
	 * @return string JSON-formatted line objects
	 */
	public function getNearRouteParts(){
		$lines = $this->nearRoutes->getLines();
			
		//create the JSON-array
		$json = array();
		foreach($lines as $key=>$line){
			$json[$key] = $line->getJson(false);
		}
		
		//encode as JSON-string and return it
		//echo json_encode($json);	
		return json_encode($json);	
	}

	
	/**
    * Gets route information from the database to make the DntRoute object. 
    * Stores the information in the object variables
    *
    * @access private
    * @param string the route code
    */	 
	private function routeFromDb($routeCode){
		
		//create database connection
		$dbConn = new psqlClass(configData(),tableData());
		$dbConn->dbConnect();

		//name of the table for the route info
		$dntRoutes = $dbConn->routesTable();
		
		//build query
		$query = "SELECT navn,lengde,sesong,omraade,beskrivelse FROM " . $dntRoutes .  " WHERE kode='" . $routeCode . "'";
		
		//query database
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());
		
		//get the first (and only) row
		$route = pg_fetch_row($result);

		//populate the object
		$this->name = $route[0];
		$this->length = $route[1];
		$this->season = $route[2];
		$this->area = $route[3];
		$this->description = $route[4];
		
		//free result
		pg_free_result($result);
		
		//close conenction
		$dbConn->dbClose();
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