<?php
require_once("/var/www/atle/final/code/php/LatLonLine.php");
require_once("/var/www/atle/final/code/php/psqlClass.php");

/**
* Creates XML-markup for routes
*
* @author Atle Frenvik Sveen <atle at frenviksveen dot net>
* @version 1.0
* 
* @see LatLonLine
*/
class RouteXML {
	private $all,$gid;
	private $routes,$consists;
	
	/**
    * Constructor
    * 
    * Queries the database for an array of LatLonLine objects, based on parameters, parameters are:
    * all: get all images 
    * gid: 
    *
    * @access public
    */
	public function __construct($params = array('all'=>null,'gid'=>null)){
		$this->all = $params['all'];
		$this->gid = $params['gid'];
		if($this->all){
			//get all routes in database	
			$this->routes = $this->getAllRoutes();	
		}
		else if($this->gid){
			$this->consists = $this->getRoutesByGid($this->gid);
		}
	}
	
	/**
    * Creates an XML-structure for routes/lines based on an array of LatLonLine objects
    *
    * @access public
    * @return string|false returns a string with xml-formatted route data, false if no routes
    */
	public function getRouteXML(){
	
		//get the routes
		$routes = $this->routes;

		//check if there are any routes
		if($routes) {
			$xml = "<routes>\n";
			foreach ($routes as $key => $route){
				$xml .= $route->getXML();
			}
			$xml .= "</routes>\n";
		}
		else {
			$xml = false;
		}
		return $xml;
	}
	
	/**
    * Creates an XML-structure for related routes/lines based on an array of LatLonLine objects
    *
    * @access public
    * @return string|false returns a string with xml-formatted route data, false if no routes
    */	
	public function getRelatedRoutesXML(){
		
		//get the related routes
		$routes = $this->consists;
		
		if($routes){
			$xml = "<routes>\n";
			foreach ($routes as $key=> $route){
				$xml .= "\t<route>\n\t\t<code>" . $route['code'] . "</code>\n\t\t<name>" . $route['name'] . "</name>\n\t</route>\n";
			}
			$xml .= "</routes>\n";
		}
		else {
			$xml = false;
		}
		return $xml;
	}
	
	/**
    * Gets all routes from database
    *
    * @access private
    * @return mixed array of LatLonLine objects
    */
	private function getAllRoutes() {
		
		//create database connection
		$dbConn = new psqlClass(configData(),tableData());
		$dbConn->dbConnect();

		//table in database for routes
		$routeTable = $dbConn->route_dataTable();
		
		//build query
		//$query = "SELECT id, route_name, AsText(the_geom) AS wkt FROM " .  $routeTable;
		$query = "SELECT gid, AsText(the_geom) AS wkt FROM " .  $routeTable; //. " WHERE gid IN (77,46,139,137)";
		
		//query database
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());

		//create result array
		$routes = array();
		
		while ($row = pg_fetch_assoc($result)) {
			$data = array(
				'wktString' => $row['wkt'],
				'id' => $row['gid']				
			);
			array_push($routes,new LatLonLine($data));
		}
		
		//free result
		pg_free_result($result);

		//close connection
		$dbConn->dbClose();
		
		return $routes;	
	}

	/**
    * Gets routes from database based on route id (gid)
    *
    * @access private
    * @param mixed array of route ids 
    * @return mixed array with route information
    */
	private function getRoutesByGid($gid){
		
		//create database connection
		$dbConn = new psqlClass(configData(),tableData());
		$dbConn->dbConnect();
		
		$dntRoutes = $dbConn->routesTable();
		$link = $dbConn->route_partsTable();
		
		$query = "SELECT kode, navn";
		$query .= " FROM " . $dntRoutes . ", " . $link;
		$query .= " WHERE " . $dntRoutes . ".kode = " . $link . ".dnt";
		$query .= " AND	" . $link . ".gid = " . $gid;

		//query database
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());

		//create result array
		$routes = array();
		
		while ($row = pg_fetch_assoc($result)) {
			$route = array(
				'code' => $row['kode'],
				'name' => $row['navn']				
			);
			array_push($routes,$route);
		}
		
		//free result
		pg_free_result($result);

		//close connection
		$dbConn->dbClose();
		
		return $routes;	
	}
	
}//end class>

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