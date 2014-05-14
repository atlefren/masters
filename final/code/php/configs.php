<?php
/**
 * 
 * Keeps track of varoius urls and paths used throughout the prototype. 
 * Rather crude, but saves a lot of hassle compared to entering things manually all over..
 *
 */
class configClass {
	var $conf;

	public function __construct($config) {
		$this->conf = $config;
    }
    
    public function routeUrl(){
    	return $this->conf['route'];
    }

    public function imageUrl(){
    	return $this->conf['image'];
    }
    
    public function cabinUrl(){
    	return $this->conf['cabin'];
    }
    
    public function tripUrl(){
    	return $this->conf['trip'];
    }
    
    public function mapUrl(){
    	return $this->conf['map'];
    }
    
    public function imageResizeUrl(){
    	return $this->conf['image_resize'];
    }
    
    public function imageResizeFunction(){
    	return $this->conf['resize'];
    }
    
    public function thumbnailUrl(){
    	return $this->conf['thumbnail'];
    }
    
    public function tempDir(){
    	return $this->conf['tempdir'];
    }
    
    public function permDir(){
    	return $this->conf['permanent_dir'];
    }
    
    public function resizedDir(){
    	return $this->conf['resized_dir'];
    }
}
    
//setup values for the database connection
function urlData() {
	$conf = array(
       		'route' => 'http://geomatikk.eksplisitt.net/atle/final/showRoute.php?route=',
       		'image' => 'http://geomatikk.eksplisitt.net/atle/final/showImage.php?image=',
       		'cabin' => 'http://geomatikk.eksplisitt.net/atle/final/showCabin.php?cabin=',
       		'trip' => 'http://geomatikk.eksplisitt.net/atle/final/showTrip.php?trip',
       		'map' => 'http://geomatikk.eksplisitt.net/atle/final/showMap.php',
       		'image_resize' => 'http://geomatikk.eksplisitt.net/atle/final/getImage.php?filename=',
       		'thumbnail' => 'http://geomatikk.eksplisitt.net/atle/final/getThumb.php?filename=',
       		'resize' => '/var/www/atle/final/code/php/functions.php',
       		'tempdir' => '/media/disk1/www/data/img/tmp/',
       		'permanent_dir' => '/media/disk1/www/data/img/online/',
       		'resized_dir' => '/media/disk1/www/data/img/online/resized/'
	);
	return $conf;
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