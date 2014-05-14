<?php
/**
 * Handles database connectivity and returns names of the tables
 *
 * @author Atle Frenvik Sveen <atle at frenviksveen dot net>
 * @version 1.0
 * 
 */
class psqlClass {
	var $conf,$tables;

	/**
	 * Constructor. Saves information needed to connect
	 *
	 * @param mixed $config array with database information
	 * @param mixed $tables array of tables names
	 */
	public function __construct($config,$tables) {
		$this->conf = $config;
		$this->tables = $tables;
	}

	/**
     * Connect to database
     *
     * @return bool true if ok
     */
	public function dbConnect(){
		//establish Database Connection
		$this->conf['db_conn'] = pg_connect("host=". $this->conf['db_host'] . " dbname=" . $this->conf['db_data'] . " user=" . $this->conf['db_user'] . " password=" . $this -> conf['db_pass']) or die(pg_last_error());
		return true;
	}

	/**
     * Close database connection
     *
     * @return bool true if ok
     */
	public function dbClose(){
		pg_close($this -> conf['db_conn']) or die(pg_last_error());
		return true;
	}

	//all these returns a string with table name...

	public function imageTable(){
		return $this->tables['images'];
	}

	public function poiTable(){
		return $this->tables['pois'];
	}

	public function tripsTable(){
		return $this->tables['trips'];
	}

	public function trip_partsTable(){
		return $this->tables['trip_parts'];
	}

	public function routesTable(){
		return $this->tables['routes'];
	}

	public function route_partsTable(){
		return $this->tables['route_parts'];
	}

	public function route_dataTable(){
		return $this->tables['route_data'];
	}
}

//setup values for the database connection
function configData() {
	$conf = array(
	'db_host' => 'insert database host',
	'db_user' => 'database user',
	'db_pass' => 'password',
	'db_data' => 'database name'
	);
	return $conf;
}


//setup tables data
function tableData(){
	$tables = array(
	'images' => 'images',
	'pois' => 'pois',
	'trips' => 'trips',
	'trip_parts' => 'trip_parts',
	'routes' => 'dnt_routes',
	'route_parts' => 'dnt_route_parts',
	'route_data' => 'route_data',
	);
	return $tables;
}

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