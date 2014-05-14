<?php
require_once("/var/www/atle/final/code/php/configs.php");
require_once("/var/www/atle/final/code/php/psqlClass.php");

/**
 * This class generates a rather crude album (formatted with HTML-<table>) that shows either all images in the database, 
 * or images based on their filenames. 
 * 
 * Is used by ShowImage.php if no image is specified and by saveImages.php to tell the user that his images are uploaded.
 * 
 * 
 * @author Atle Frenvik Sveen <atle at frenviksveen dot net>
 * @version 1.0
 * 
 */
class ImageGallery{
	
	//the images to display in the gallery
	private $images = array();
	
	//misc
	private $urls;
	
	/**
    * Constructor 
    * 
    * Fetches the desiered images from the db and stores them in the $images array
    * 
    * @access public
    * @param mixed|bool $filenames array of filenames or false for all images (default=false)
    */
	public function __construct($filenames=false){
		$this->urls = new configClass(urlData());
		if($filenames){
			//check if filenames are specified
			$this->images = $this->imagesByFilename($filenames);
		}
		else{
			//get all images
			$this->images = $this->getImages();
		}
	}
	
	/**
	 * Prints the HTML-gallery based on the $images variable
	 *
	 * @access public
	 * @param int $width number of images wide the gallery should be
	 */
	public function printGallery($width=5){
		

		//get the images to print
		$images = $this->images;
		
		//control
		$i = 0;
		$run = true;

		//create table header
		$html = "<table cellpadding=\"2\" cellspacing=\"10\" border=\"0\">\n";
		while($run) {

			$html .= "\t<tr>\n";
			for ($j = 0;$j<$width;$j++){
				$html .= "\t\t<td>\n";

				if ($i<sizeof($images)) {
					$html .= "\t\t\t<a href=\"". $this->urls->imageUrl() . $images[$i]['id']  ."\">\n";
					$html .= "\t\t\t\t<img src=\"". $this->urls->imageResizeUrl() . $images[$i]['filename']  . "&width=200\" border=\"0\">\n";
					$html .= "\t\t\t</a>\n";

					if($images[$i]['title']) {
						$html .= "\t\t\t<p>" . $images[$i]['title'] . "</p>\n";
					}
					else {
						$html .= "\t\t\t<p>Ingen tittel</p>\n";
					}
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

		echo $html;
	}
	
	/**
	 * Fetches images from the database from filename
	 *
	 * @access private
	 * @param mixed $filenames array of filenames
	 * @return mixed array of image information
	 */
	private function imagesByFilename($filenames){
				
		//create database connection
		$dbConn = new psqlClass(configData(),tableData());
		$dbConn->dbConnect();

		//table in database for images
		$imageTable = $dbConn->imageTable();
		
		//make a comma-separated list of Filenames
		$fnList = "";
		foreach ($filenames as $key =>$filename) {
			$fnList .= "'" . $filename . "',";
		}
		$fnList = rtrim($fnList,",");

		//build query
		$query = "SELECT id,title,filename,username,season,description,AsText(the_geom) AS wkt FROM " .  $imageTable;
		$query .= " WHERE filename IN (" . $fnList . ")";

		//query database
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());
		
		//build result 
		$images = array();
		while ($row = pg_fetch_assoc($result)) {
			$image = array(
				'id' => $row['id'],
				'title' => $row['title'],
				'filename' => $row['filename']
			);
			array_push($images,$image);
		}
		
		//free result
		pg_free_result($result);
		
		//close connection
		$dbConn->dbClose();
		
		return $images;
	}
	
	/**
	 * Fetches all images from the database
	 *
	 * @access private
	 * @param mixed $filenames array of filenames
	 * @return mixed array of image information
	 */	
	private function getImages(){		
		//create database connection
		$dbConn = new psqlClass(configData(),tableData());
		$dbConn->dbConnect();

		//table in database for images
		$imageTable = $dbConn->imageTable();

		//build query
		$query = "SELECT id, title, filename FROM " .  $imageTable;

		//query database
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());

		//create result array
		$images = array();
		$i = 0;
		while ($row = pg_fetch_assoc($result)) {
			$image = array(
				'id' => $row['id'],
				'title' => $row['title'],
				'filename' => $row['filename']
			);
			array_push($images,$image);
		}

		//free result
		pg_free_result($result);
		
		//close conenction
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