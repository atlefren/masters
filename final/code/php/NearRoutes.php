<?php

require_once("/var/www/atle/final/code/php/psqlClass.php");
require_once("/var/www/atle/final/code/php/configs.php");
require_once("/var/www/atle/final/code/php/LatLonLine.php");
require_once("/var/www/atle/final/code/php/functions.php");


/**
*
* Gets routes near a route or image and returns these as a list or JSON-data for map display
* 
* 
* @author Atle Frenvik Sveen <atle at frenviksveen dot net>
* @version 1.0
* 
*/
class NearRoutes{
	
	private $near;
	private $lines;
	private $type; 
	private $urls;
	
	/**
	 * Constructor
	 * 
	 * Determines what we are searching near and gets the routes within a treshold
	 *
	 * @param string $type either "route" or "image"
	 * @param mixed $id id/code of image/route
	 */
	public function __construct($type,$id) {

		//urls and paths
		$this->urls = new configClass(urlData());
		
		//determine type
		$this->type=$type;
		if($type=='route'){
			//get route names
			$this->near = $this->nearRoute($id,100);
			
			//get lines
			$this->lines = $this->nearLines($id,100);
		}
		else if ($type = 'image') {
			$this->near = $this->nearImage($id,1000);
		}	
	}
	
	/**
    * Creates a html-list of the nearby routes
    *
    * @access public
    * @return string html list
    */	
	public function getList(){

		//the nearby routes
		$nearby = $this->near;

		if(sizeof($nearby)>0){
			$html = "<ul>\n";
			foreach($nearby as $route){
				$html .= "\t<li><a href=\"". $this->urls->routeUrl() . $route['code'] ."\"";

				//add javascript if route
				if($this->type == 'route'){
					$html .= "\" onmouseover=\"activeRoute('" . $route['code'] ."');\"";
					$html .= " onmouseout=\"unactiveRoute('" . $route['code'] ."');\"";
					$html .=">" . $route['name'] . "</a></li>\n";
				}
				//print distance if image
				else if($this->type == "image"){
					$html .=">" . $route['name'] . "</a> (" . convertDistance($route['distance']) . ")</li>\n";
				}
			}
			$html .= "</ul>\n";
		}
		else{
			$html = "<p>Ingen nærliggende ruter funnet</p>";
		}
		return $html;
	}
	
	/**
	 * Return the line segments of the nearby routes
	 *
	 * @access public
	 * @return mixed array of lines
	 */
	public function getLines(){
		return $this->lines;
	}
	
	/**
    * Finds the line segments that make up each of the routes
    *
    * @access public
    * @return mixed array of route ids and line ids
    */
	public function getRouteLines(){
		
		//the nearby routes
		$routes = $this->near;
		
		if(sizeof($routes) > 0){
					
			//create database connection
			$dbConn = new psqlClass(configData(),tableData());
			$dbConn->dbConnect();
		
			//make a string of route codes to use in IN() statement
			$string = "";
			foreach ($routes as $route){
				$string .= "'" . $route['code'] . "',";
			}
			$string  = rtrim($string,",");
			
			//get the route parts table
			$dntRoutes = $dbConn->route_partsTable();
			
			//build query
			$query = "SELECT dnt, gid from " . $dntRoutes . " where dnt IN (" . $string .") ORDER BY dnt";
			
			//execute query
			$result = pg_query($query) or die('Query failed: ' . pg_last_error());
			
			//make a sane representation
			$prev = null;
			$routeParts = array();
			while ($row = pg_fetch_assoc($result)) {
				
				//echo " " . $row['gid'] . " ";	
				
				if($row['dnt'] == $prev){
					array_push($routeParts[$row['dnt']],$row['gid']);
				}
				else {
					$routeParts[$row['dnt']] = array();
					array_push($routeParts[$row['dnt']],$row['gid']);
				}
				$prev = $row['dnt'];
			}
			
			//free result
			pg_free_result($result);

			//close connection
			$dbConn->dbClose();
			
			return $routeParts;
		}
	}
	
	/**
    * Finds routes within a given treshold of a specified route
    *
    * @access public
    * @param string $id id of the route
    * @param int $treshold distance to search (in meters)
    * @return mixed array of route ids and names
    */	
	private function nearRoute($id,$treshold){
				
		//create database connection
		$dbConn = new psqlClass(configData(),tableData());
		$dbConn->dbConnect();
		
		//table names in the database	
		$dntRoutes = $dbConn->routesTable();
		$link = $dbConn->route_partsTable();
		$routes_data = $dbConn->route_dataTable();
		
		//build query
		$query = "SELECT DISTINCT " . $dntRoutes . ".navn AS name, " . $dntRoutes .".kode AS code";
		$query .= " FROM " . $dntRoutes . "," . $link . "," . $routes_data . " as needle, " . $routes_data ." as haystack ";
		$query .= " WHERE ST_DWithin(transform(haystack.the_geom,32633),transform(needle.the_geom,32633)," . $treshold . ")";
		$query .= " AND " . $dntRoutes . ".kode=" . $link . ".dnt";
		$query .= " AND " . $link . ".gid = haystack.gid";
		$query .= " AND " . $dntRoutes . ".kode !='" . $id .  "'";
		$query .= " AND needle.gid IN (SELECT gid FROM " . $link . " WHERE dnt='" . $id .  "')";
	
		//query database
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());

		//create result array
		$nearRoutes = array();

		//make result array
		while ($row = pg_fetch_assoc($result)) {
			$route = array(
				'name' => $row['name'],
				'code' => $row['code']
			);
			array_push($nearRoutes,$route);
		}
		
		//free result
		pg_free_result($result);

		//close connection
		$dbConn->dbClose();
		
		return $nearRoutes;
	}

	/**
    * Gets the line segments that make up the nearby routes, 
    * but excludes line segments that are used by the line itself
    *
    * @access public
    * @param string $id 
    * @param int $treshold
    * @return mixed array of LatLonLine objects
    */
	private function nearLines($id,$treshold){
				
		//create database connection
		$dbConn = new psqlClass(configData(),tableData());
		$dbConn->dbConnect();

		//tables in database
		$dntRoutes = $dbConn->routesTable();
		$link = $dbConn->route_partsTable();
		$routes_data = $dbConn->route_dataTable();
		
		
		//build query
		$query = "SELECT DISTINCT ON (link2.gid) link2.gid AS gid, AsText(result.the_geom) AS wkt";
		$query .= " FROM " . $routes_data . " as haystack,";
		$query .= $routes_data . " as needle, ";
		$query .= $routes_data . " as result, ";
		$query .= $link . " as link, ";			
		$query .= $link . " as link2";
		$query .= " WHERE ST_DWithin(transform(needle.the_geom,32633),transform(haystack.the_geom,32633)," . $treshold . ")";
		$query .= " AND needle.gid IN(SELECT gid FROM " . $link . " WHERE dnt='" . $id . "')"; 
		$query .= " AND haystack.gid = link.gid";
		$query .= " AND link.dnt=link2.dnt";
		$query .= " AND link2.gid NOT IN(SELECT gid FROM " . $link . " WHERE dnt='" . $id . "')"; 
		$query .= " AND link2.gid = result.gid";

		//query database
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());

		//create result array
		$nearRoutes = array();		
		while ($row = pg_fetch_assoc($result)) {
			$data = array(
				'id' => $row['gid'],
				'wktString' => $row['wkt']
			);
			array_push($nearRoutes,new LatLonLine($data));
		}
		
		//free result
		pg_free_result($result);

		//close connection
		$dbConn->dbClose();
		
		return $nearRoutes;		
	}
	
	/**
    * Finds routes near an image
    *
    * @access public
    * @param string $id id of the route
    * @param int $treshold distance to search (in meters)
    * @return mixed array of route names, codes and distances
    */	
	private function nearImage($id,$treshold){
		
		//create database connection
		$dbConn = new psqlClass(configData(),tableData());
		$dbConn->dbConnect();
		
		//tables in the database
		$dntRoutes = $dbConn->routesTable();
		$link = $dbConn->route_partsTable();
		$routes_data = $dbConn->route_dataTable();
		$images = $dbConn->imageTable();

		//build qubquery
		$subQuery = "SELECT (ST_Dump(the_geom)).geom As the_geom, ". $dntRoutes . ".kode as kode, ". $dntRoutes . ".navn as navn";
		$subQuery .= " FROM " . $routes_data . ", " . $link . ", ". $dntRoutes;
		$subQuery .= " WHERE " . $link . ".dnt = ". $dntRoutes . ".kode";
		$subQuery .= " AND " . $link . ".gid = " . $routes_data . ".gid";
	
		//build query
		$query = "SELECT navn, kode,";	
		$query .= " ST_Distance(transform(ST_Multi(ST_Collect(route.the_geom)),32633),transform(" . $images . ".the_geom,32633)) as distance";
		$query .= " FROM (". $subQuery. ") As route," . $images;
		$query .= " WHERE " . $images . ".id=" . $id;
		$query .= " GROUP BY kode, navn, " . $images . ".id, " . $images . ".the_geom";
		$query .= " HAVING ST_Distance(transform(ST_Multi(ST_Collect(route.the_geom)),32633),transform(" . $images . ".the_geom,32633)) <" . $treshold;
		$query .= " ORDER BY distance";
		
		//query database
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());

		//create result array
		$nearRoutes = array();
		while ($row = pg_fetch_assoc($result)) {
			$route = array(
				'name' => $row['navn'],
				'code' => $row['kode'],
				'distance' => $row['distance']
			);
			array_push($nearRoutes,$route);
		}
		
		//free result
		pg_free_result($result);

		//close connection
		$dbConn->dbClose();
		
		return $nearRoutes;
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