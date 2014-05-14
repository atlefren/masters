<?php

/**
 * Class to handle latitude,lognitude points
 * supports both Well-Known-Text and numerical representations
 * returns XML-folrmatted, WKT and numerical representations
 * 
 * 
 * @author Atle Frenvik Sveen <atle at frenviksveen dot net>
 * @version 1.0
 *
 */
class LatLonPoint {

	protected $lat,$lon;

	/**
	 * Constructor
	 * Creates a point, either based on lat/lon ints or a wkt-string
	 *
	 * @access public
     * @param mixed $params array with params:
     * 			float lat latitude of point
     * 			float lon longitude of point
     * 			string 	wktString 	WKT-string with POINT representation of point
	 */
	public function __construct($params = array('lat'=> null, 'lon' => null, 'wktString' => null)) {
		//constructor
		if(array_key_exists('wktString',$params)) {
			//we are dealing with a WKT-string
			//set the values of a LatLonPoint from a WKT-definition from PostGIS
			//WBK assumed to be in the "POINT(Lognitude Latitude)"-format

			//extract params
			$wktString = $params['wktString'];

			//split the string
			$point = str_replace("POINT(", "", "$wktString");
			$point = str_replace(")", "", "$point");
			$latlon = explode(" ", $point);

			//set lat/lon
			$this->lat=$latlon[1];
			$this->lon=$latlon[0];
		}
		else {
			//we are dealing with a (Lat,Lon)-float-pair
			$this->lat=$params['lat'];
			$this->lon=$params['lon'];
		}
	}

	/**
	 * Returns the latitude of the point if set
	 *
	 * @access public
	 * @return float|bool latitude or false
	 */
	public function getLat() {
		if($this->lat){
			return $this->lat;
		}
		else {
			return false;
		}

	}

	/**
	 * Returns the longitude of the point if set
	 *
	 * @access public
	 * @return float|bool longitude or false
	 */
	public function getLon() {
		if($this->lon){
			return $this->lon;
		}
		else {
			return false;
		}
	}

	/**
	 * returns the lat-lon-pair in XML format, e.g.:
	 * <point lat="Latitude" lng="Lognitude"/>
	 * 
	 * @access public
	 * @return string|bool XML string or false
	 */
	public function getXML(){
		//
		if($this->lat && $this->lon){
			$xml = "<point lat=\"" . $this->lat . "\" lng=\"" .  $this->lon . "\"/>";
			return $xml;
		}
		else {
			return false;
		}
	}

	/**
	 * returns the lat-lon-pair formatted as GLatLon from Gmaps, eg:
	 * new GLatLng(lat,lon)
	 * 
	 * @access public
	 * @return string|bool GlatLon string or false
	 */
	public function getGLatLng(){
		$GLatLng = "new GLatLng(" . $this->lat . "," . $this->lon . ")";
		return $GLatLng;
	}

	/**
	 * returns the lat-lon-pair formatted as WKT POINT, eg:
	 * POINT(lon lat)
	 * 
	 * @access public
	 * @return string|bool WKT string or false
	 */
	public function getWKT(){
		if($this->lat && $this->lon){
			$wkt = "POINT(". $this->lon . " " . $this->lat . ")";
			return $wkt;
		}
		else {
			return false;
		}
	}

	/**
	 * Sets the latitude of the point
	 *
	 * @access public
	 * @param float $lat latitude
	 * @return bool true
	 */
	public function setLat($lat){
		$this->lat = $lat;
		return true;
	}

	/**
	 * Sets the longitude of the poiint
	 *
	 * @access public
	 * @param float $lon longitude
	 * @return bool true
	 */	
	public function setLon($lon){
		$this->lon = $lon;
		return true;
	}
}//end class


/**
 * 
 * Extends the LatLonPoint class to handle image specific information such as name, filename and wether it is geotagged
 * 
 * @see LatLonPoint
 * @author Atle Frenvik Sveen <atle at frenviksveen dot net>
 * @version 1.0
 *
 */
class GeoRefImage extends LatLonPoint {

	private $title,$filename,$user,$description,$id = null;

	/**
	 * Constructor
	 * Sets up values from params-array
	 * 
	 * @access public
     * @param mixed $params array with params:
     * 				string title image title
     * 				string filename 
     * 				string user username/photographer name
     * 				int id image id
     * 				int season numerical representation of season
     * 				string description image description
     *
	 */
	public function __construct($params = array('title'=> null,'filename'=> null,'user'=> null,'id'=> null,'season' => null,'description' => null)) {
		parent::__construct($params);

		//fetch params from array
		$this->title = $params['title'];
		$this->filename = $params['filename'];
		$this->user = $params['user'];
		$this->id = $params['id'];
		$this->season = $params['season'];
		$this->description = $params['description'];

		//if the filename is an actual file, get EXIF-info
		if (file_exists($this->filename)){
			$this->exif_getLatLon($this->filename);
		}
	}

	/**
	 * Return username/photographer
	 *
	 * @access public
	 * @return string username
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * Return image title
	 *
	 * @access public
	 * @return string title of image
	 */
	public function getTitle() {
		return $this->title;
	}

	public function getFilename() {
		return basename($this->filename);
	}

	/**
	 * Return image id
	 *
	 * @access public
	 * @return int image id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Return season as int
	 *
	 * @access public
	 * @return int season
	 */
	public function getSeason() {
		return $this->season;
	}

	/**
	 * Return description
	 * 
	 * @access public
	 * @return string description
	 */
	public function getDescription(){
		return $this->description;
	}

	/**
	 * Check if coordinates are set
	 *
	 * @access public
	 * @return bool true if lat and lon are set, else false
	 */
	public function isGeoref(){
		if (($this->getLat()) && ($this->getLon())){
			return true;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Gets image coordinates from EXIF, saves them in lat/lon 
	 * 
	 * @access private
	 * @param string $filename full path to image
	 * 
	 */
	private function exif_getLatLon($filename){

		$exif = exif_read_data($filename, 'GPS',true);

		if ($exif==true) {
			//get latitude info (hours, mins, secs)
			$latitude = Array();
			$latitude['hours'] = $this->getFloat($exif['GPS']['GPSLatitude'][0]);
			$latitude['mins'] = $this->getFloat($exif['GPS']['GPSLatitude'][1]);
			$latitude['secs'] = $this->getFloat($exif['GPS']['GPSLatitude'][2]);

			//get longitude info (hours, mins, secs)
			$longitude = Array();
			$longitude['hours'] = $this->getFloat($exif['GPS']['GPSLongitude'][0]);
			$longitude['mins'] = $this->getFloat($exif['GPS']['GPSLongitude'][1]);
			$longitude['secs'] = $this->getFloat($exif['GPS']['GPSLongitude'][2]);

			//decimal-degrees
			$this->lat = $this->getDeg($latitude);
			$this->lon = $this->getDeg($longitude);
		}

	}

	/**
	 * Converts EXIF number format to float
	 *
	 * @access private
	 * @param string $exifString 
	 * @return float number as float
	 */
	private function getFloat($exifString){

		//split the string
		$temp = explode("/", $exifString);

		//divide it to get float
		return $temp[0]/$temp[1];
	}

	/**
	 * convert from hours,mins,secs-array to decimal-degrees
	 *
	 * @access private 
	 * @param mixed $deg array with hours mins sec representation of angle
	 * @return float floating point representation of angle
	 */
	private function getDeg($deg){
		return $deg['hours'] + $deg['mins']/60 + $deg['secs']/(60*60);
	}
	
	
	//remove the following?? (old junk)
	
	/**
	 * Do I need this?
	 * (I doubt it..)
	 *
	 * @param unknown_type $treshold
	 * @return unknown
	 */
	public function getRoutesNearImage($treshold){

		$imageid = $this->getId();

		$query = new DbQuery();

		//echo $imageid;
		//echo $treshold;

		$routes = $query->getRoutesNearImage($imageid,$treshold);

		return $routes;
	}

	/**
	 * same here... remove?
	 *
	 * @param unknown_type $limit
	 * @param unknown_type $max
	 * @return unknown
	 */
	public function getRoutesNearImage2($limit=5,$max=10){

		$imageid = $this->getId();

		$query = new DbQuery();

		$routes = $query->getRoutesByDistanceToImage($imageid,$limit,$max);

		//convert distances to human-readable strings
		foreach ($routes as $key=>$route) {
			$routes[$key]['distance'] = $this->convertDistance($route['distance']);
		}

		return $routes;

	}

	/**
	 * again...?
	 *
	 * @param unknown_type $treshold
	 * @return unknown
	 */
	public function getPOINearImage($treshold){

		$imageid = $this->getId();

		$query = new DbQuery();
		$pois = $query->getPoisNearImage($imageid,$treshold);
		$query->getPoisByDistanceToImage($imageid);
		return $pois;
	}

	/**
	 * hmmmmmmmm
	 *
	 * @param unknown_type $limit
	 * @param unknown_type $max
	 * @return unknown
	 */
	public function getPOINearImage2($limit,$max){

		$imageid = $this->getId();

		$query = new DbQuery();
		$pois = $query->getPoisByDistanceToImage($imageid,$limit,$max);

		//convert distances to human-readable strings
		foreach ($pois as $key=>$poi) {
			$pois[$key]['distance'] = $this->convertDistance($poi['distance']);
		}
		return $pois;
	}

	public function numNearbyImages($treshold){

		$imageid = $this->getId();

		$query = new DbQuery();
		$numNearby = $query->getNumImagesNearImage($imageid,$treshold);

		return $numNearby;
	}

	public function getNearbyImages($treshold) {

		$imageid = $this->getId();

		$query = new DbQuery();
		$images = $query->getImagesNearImage($imageid,$treshold);

		return $images;
	}

	private function convertDistance($distance){

		if($distance <1000){
			//under 1 km, displaying meters rounded to nearest 10 meters
			return round($distance,-1) . " m";
		}
		else {
			//over 1 km, display km with one decimal
			return str_replace(".",",",round($distance/1000,1) . " km");
		}
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