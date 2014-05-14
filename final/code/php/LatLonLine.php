<?php
require_once("/var/www/atle/final/code/php/LatLonPoint.php");

/**
* Class to handle latitude,longitude polylines,supports both Well-Known-Text and numerical representations
*
* @author Atle Frenvik Sveen <atle at frenviksveen dot net>
* @version 1.0
*/
class LatLonLine { 
	
	private $line = array();
	private $id, $name;
	
	
	/**
    * Constructor
    * 
    * Sets up the Line, based on either a WKT-string or an array of LatLonPoints
    * Additional arguments are id and name
    * 
    * @access public
    * @param mixed $params array with params:
    * 			array 	polyline 	array of latLonPoints
    * 			string 	wktString 	WKT-string with either MULTILINESTRING or LINESTRING reperesentation of line
    * 			id 		int			id of the line
    * 			name	string		name of the line
    */
	public function __construct($params = array('polyline' => null, 'wktString' => null, 'id' => null, 'name' => null)) {	
		
		//determine how geographical info is given
		if(array_key_exists('wktString',$params)) {
			//we are dealing with a WKT-definition of a linestring
			
			//extract parameters
			$line = $params['wktString'];			
			
			//find out if we are dealing with a LINESTRING or MULTILINESTRING
			if(strstr($line, "MULTILINESTRING")){
				//multilinestring, assume it only contains one line..
				$line = str_replace("MULTILINESTRING((", "", "$line");
				$line = str_replace("))", "", "$line");
				$line = explode(",",$line);
			}
			else {
				$line = str_replace("LINESTRING(", "", "$line");
				$line = str_replace(")", "", "$line");
				$line = explode(",",$line);
			}
		
			//split the string
			for($i=0; $i < sizeof($line); $i++) {
				$latln = explode(" ",$line[$i]);
				$dataSource=array('lat' => $latln[1], 'lon' => $latln[0]);
				$polyline[$i] = new LatLonPoint($dataSource);
			}
			
			//store as line
			$this->line=$polyline; 
		}
		else {
			//we are dealing with an array of LatLonPoints	
			
			//extract parameters	
			$polyline = $params['polyline'];
			
			$this->line = $polyline;
		}
		
		//other params
		if(array_key_exists('id',$params)) {
			$this->id = $params['id']; 
			
		}
		if(array_key_exists('name',$params)) {
			$this->name = $params['name']; 
			
		}
	} 
	
	/**
    * Returns the line as an array of LatLonPoints
    *
    * @access public
    * @return mixed array of LatLonPoints
    */
	public function getArray(){
		return $this->line;
	}
	
	/**
    * Returns the id of the line
    *
    * @access public
    * @return int id of line
    */	
	public function getId(){
		return $this->id;
	}
	
	/**
    * Returns the name of the line
    *
    * @access public
    * @return string name of line or false if not set
    */		
	public function getName(){
		if($this->name){
			return $this->name;
		}
		else {		
			return false;		
		}
	}	
	
	/**
    * Returns the LatLonPoint at node i
    *
    * @access public
    * @param int $i node to get
    * @return LatLonPoint at node i if exists, if not null
    */
	public function getNode($i){
		if($i < sizeof($this->line)){
			return $this->line[$i];
		}
		else {
			return null;
		}	
	}
	
	/**
    * Returns the line as an XML-formatted string, ie:
    *	<line>
    * 		<id>id</id>
    * 		<name>name</name>
	*		<point lat="Latitude" lng="Longitude"/>
   	*		<point lat="Latitude" lng="Longitude"/>
 	*	</line>
    *
    * @access public
    * @return string XML-formatted line-data
    */
	public function getXML(){
		
		//check if name is set
		$name = $this->getName();

		
		$xml = "\t<line>\n";
		$xml .= "\t\t<id>" . $this->getId() . "</id>\n";
 		if($name){
 		$xml .= "\t\t<name>" . $name . "</name>\n";
 		}
		for($i=0; $i < sizeof($this->line); $i++) {
			$xml .= "\t\t<point lat=\"" .  $this->line[$i]->getLat() . "\" lng=\"" . $this->line[$i]->getLon() . "\"/>\n";	
		}		
		$xml .= "\t</line>\n";
		
		return $xml;
	}
	
	/**
    * Returns the line as a WKT-formatted LINESTRING, ie:
	* LINESTRING(Longitude Latitude,Longitude Latitude)
    *
    * @access public
    * @return string WKT-formatted line-data
    */
	public function getWKT(){
		$wkt = "LINESTRING(";
		for($i=0; $i < sizeof($this->line); $i++) {
			if($i != (sizeof($this->line)-1)) {
				$wkt .= $this->line[$i]->getLon() . " " . $this->line[$i]->getLat() . ",";
			}
			else {
				$wkt .= $this->line[$i]->getLon() . " " . $this->line[$i]->getLat();
			}
		}
		$wkt .= ")";
		return $wkt;
	}	
	
	/**
    * Returns the line as JSON-data
    *
    * @access public
    * @param bool $encode wether or not to encode the line (default: true)
    * @return string WKT-formatted line-data
    */
	public function getJson($encode=true){
		$json = array();
		$json['id'] = $this->getId();
		if($this->getName()){
		$json['name'] = $this->getName();
		}
		for($i=0; $i < sizeof($this->line); $i++) {
			$json['points'][$i]['lat'] = $this->line[$i]->getLat();
			$json['points'][$i]['lon'] = $this->line[$i]->getLon();
		}
		if($encode){
			return json_encode($json);
		}
		else {
			return $json;
		}
	}
} //end class

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