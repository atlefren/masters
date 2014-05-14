<?php
require_once("/var/www/atle/final/code/php/LatLonPoint.php");
require_once("/var/www/atle/final/code/php/ImageGallery.php");
require_once("/var/www/atle/final/code/php/psqlClass.php");
require_once("/var/www/atle/final/code/php/configs.php");

/**
* Saves information on uploaded images to the database and moves them to permanent storage. 
* Also displays the images as a gallery.
* 
* @author Atle Frenvik Sveen <atle at frenviksveen dot net>
* @version 1.0
*/
class SaveImage{
	
	private $images;
	private $urls;
	
	/**
    * Constructor
    * 
    * Parses the POST-data to make an array of GeoRefImage objects
    * 
    * @access public
    * @param $post information via http post
    */
	public function __construct($post) {
		$this->urls = new configClass(urlData());
		$this->images = $this->parsePost($post);
		//var_dump($post);
	}

	/**
	 * Inserts the image information into the database
	 *
	 * @return bool true if success
	 */
	public function dbInsert(){
		
		//get the GeoRefImage objects
		$images=$this->images;

		//connect to db
		$dbConn = new psqlClass(configData(),tableData());
		$dbConn->dbConnect();
		
		//build query(es)
		$query ="";
		foreach ($images as $key=>$image) {
			
			//get params
			
			$title = $image->getTitle();
			$filename = $image->getFilename();
			$username = $image->getUser();
			$season = $image->getSeason();
			$description = $image->getDescription();
			if($image->isGeoref()) {
				$the_geom = "GeomFromText('" . $image->getWKT() . "',4326)";
			}
			else {
				$the_geom = null;
			}

			//check if fields are present
			$fields = "";
			$values = "";

			if ($title){
				$fields = "title,filename,username,season";
				$values = "'" . $title . "','" . $filename . "','" . $username . "'," . $season;
			}
			else {
				$fields .= "filename,username,season";
				$values = "'" . $filename . "','" . $username . "'," . $season;
			}
			
			if($description) {
				$fields .=",description";
				$values .= ",'" . $description . "'";
			}

			if($the_geom){
				$fields .=",the_geom";
				$values .= "," . $the_geom;
			}
			//build query
			$query .= "INSERT INTO images (" . $fields . ") VALUES (" . $values . ");";
		}

		//submit result to db
		$result = pg_query($query);

		//check for errors (probably not good enough.. but this thing WORKS ;))
		if (!$result) {
			die("Error '$query': " . pg_last_error());
			//close
			$dbConn->dbClose();
			return false;
		}
		else {
			//close
			$dbConn->dbClose();
			return true;
		}
	}
	
	/**
	 * Moves the uploaded images from the temp dir to permanent storage
	 *
	 * @access public
	 * @return bool true on success
	 */
	public function moveImages(){
		
		//the images
		$images = $this->images;
		
		//move the images
		foreach ($images as $key =>$image) {
			//$temp_dir = '/media/disk1/www/data/img/tmp/';
			$temp_dir = $this->urls->tempDir();
			//$permanent_dir = '/media/disk1/www/data/img/online/';
			$permanent_dir = $this->urls->permDir();
			$filename = $image->getFilename();
			rename($temp_dir . $filename, $permanent_dir . $filename);
		}
		return true;
	}
	
	/**
	 * Prints a gallery of the uploaded images
	 * 
	 * @see ImageGallery
	 * @return bool true on success
	 */
	public function showImages(){
		
		//get the images
		$images = $this->images;
		
		//greate an array of filenames
		$filenames = array();	
		foreach ($images as $key => $image) {
			array_push($filenames,$image->getFilename());
		}
		
		//make the gallery and print
		$gallery = new ImageGallery($filenames);
		$gallery->printGallery();	
		
		return true;	
	}
	
	/**
	 * Parses the POST-data into an array of GeoRefImage objects
	 *
	 * @access private
	 * @param mixed $post POST-data
	 * @return mixed array of GeoRefImage objects
	 * 
	 */
	private function parsePost($post){
	
		//set username (hc svnt dracones)
		$username = "atlefren";
		
		//create array of images
		$images = array();
		foreach ($post["id"] as $key=>$value) {
			$data = array(
				'lat' => $post["lat"][$key], 
				'lon' => $post["lon"][$key],
				'title' => $post["title"][$key], 
				'filename' => $post["filename"][$key],
				'user' => $username, 
				'season' => $post["season"][$key]
			);	
			array_push($images,new GeoRefImage($data));
		}
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