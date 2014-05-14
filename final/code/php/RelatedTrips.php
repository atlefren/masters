<?php

require_once("/var/www/atle/final/code/php/psqlClass.php");
require_once("/var/www/atle/final/code/php/LatLonLine.php");

/**
*
* Gets trips related to a given route or near a given image. Returns a list and JSON-formatted data to use in a map
* 
* @author Atle Frenvik Sveen <atle at frenviksveen dot net>
* @version  1.0
* 
*/
class RelatedTrips{
	
	//the nearby trips
	private $trips;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $type "image" or "route"
	 * @param mixed $id id/code of image/route
	 */
	public function __construct($type,$id) {
		//determine type
		if($type=='route'){
			$this->trips = $this->relatedToRoute($id);
		}
		else if ($type = 'image') {
			$this->trips = $this->nearImage($id,1000);
		}	
	}
	
	/**
    * Creates a html-list of the related trips
    *
    * @access public
    * @return string html list or "error message"
    */	
	public function getList(){
		
		$related = $this->trips;
		if($related){
			$html = "<ul>\n";
			foreach($related as $trip){
				$html .= "\t<li><a href=\"showTrip.php?id=" . $trip['id'] . "\" onmouseover=\"activeTrip('" . $trip['id'] ."');\" onmouseout=\"unactiveTrip('" . $trip['id'] ."');\">"  . $trip['name'] . "</a></li>\n";
			}
			$html .= "</ul>\n";
		}
		else {
			$html = "<p>Denne ruten har ingen tilknyttede turer.</p>";
		}
		return $html;
	}
	
	/**
    * Gets the ids of the line parts that make up each of the related trips
    *
    * @access public
    * @param string $id route id
    * @return mixed array of line ids sorted by trip id
    */
	public function getTripLines($id){
		
		//create database connection
		$dbConn = new psqlClass(configData(),tableData());
		$dbConn->dbConnect();

		//tables in the database
		$link = $dbConn->route_partsTable();
		$compositeRoutes = $dbConn->tripsTable();
		$trip_parts = $dbConn->trip_partsTable();
		
		//build query
		$query = "SELECT DISTINCT";
		$query .=" haystack.tripid AS id," . $link . ".gid AS gid";
 		$query .=" FROM " . $compositeRoutes . ", " . $link . ", " . $trip_parts . " AS needle, " . $trip_parts . " AS haystack";
		$query .=" WHERE " . $compositeRoutes . ".id = needle.tripid";
		$query .=" AND needle.dntkode='" . $id . "'";
		$query .=" AND needle.tripid=haystack.tripid";
		$query .=" AND haystack.dntkode = " . $link . ".dnt";
		
		//query database
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());
		
		$ids = array();
		while ($row = pg_fetch_assoc($result)) {
			if(array_key_exists($row['id'],$ids)){
				array_push($ids[$row['id']],$row['gid']);	
			}
			else {
				$ids[$row['id']] = array();
				array_push($ids[$row['id']],$row['gid']);
			}
		}
		
		//free result
		pg_free_result($result);

		//close connection
		$dbConn->dbClose();	
		
		return $ids;
	}
	
	/**
    * Finds trips related to a route
    *
    * @access public
    * @param string $id id of the route
    * @return mixed array of route ids and names
    */	
	private function relatedToRoute($id){
			
		//create database connection
		$dbConn = new psqlClass(configData(),tableData());
		$dbConn->dbConnect();

		//tables in the database
		$compositeRoutes = $dbConn->tripsTable();
		$trip_parts = $dbConn->trip_partsTable();
		
		//build query
		$query = "SELECT DISTINCT ON (" .$compositeRoutes . ".id) " .$compositeRoutes . ".name AS name, " . $trip_parts . ".position AS position, " .$compositeRoutes . ".id AS id";
		$query .= " FROM " .$compositeRoutes . ", " . $trip_parts . " WHERE ";
		$query .= $compositeRoutes . ".id = " . $trip_parts . ".tripid AND " . $trip_parts . ".dntkode='" . $id ."'";
			
		//query database
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());

		//create result array
		$trips = array();
		while ($row = pg_fetch_assoc($result)) {
			$trip = array(
					'id'=> $row['id'],
					'name' => $row['name'],
					'position' => $row['position']
			);
			array_push($trips,$trip);
		}
	
		//free result
		pg_free_result($result);

		//close connection
		$dbConn->dbClose();
		
		return $trips;			
	}
	
	/**
    * Finds trips near an image
    *
    * @access public
    * @param string $id id of the route
    * @param int $treshold distance to search (in meters)
    * @return mixed resulting trips
    * @todo IMPLEMENT (not done in prototype)
    */	
	private function nearImage($id,$treshold){
		return null;
	}
		
}// end class
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