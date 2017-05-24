<?php

/*
When the data cache layer is written it needs to store the cache under the ID (per BBB) of the url. Like easternnc or utah instead of saltlake or raleigh. Reason being is then we don't need to have a database connection just to look up the cache.
*/

class hapi {

	public $outputtype = 'json';
	public $segments = array();
	public $method = '';
	public $bbb = null; // the write to local class
	public $bbburl = '';
	public $bbbid = null;
	public $path = '/home/serv/public_html/m/apis/';
	public $cached = FALSE;
	public $riak = null;
	public $connected = false;

	public function __construct() {
		global $output, $uri, $method;
		
		$this->outputtype = $output;
		$this->segments = $uri;
		$this->method = $method;
		list( $this->url, ) = explode( ".", $_SERVER['SERVER_NAME'] );	
		
	/*	if( !class_exists("RiakClient") ) include "/home/serv/public_html/m/apis/library/riak.php";
		if(!$this->riak) {
			$this->riak = new RiakClient("riakdb.app.hurdman.org", 8098);
			$this->riak->r=1;
			$this->riak->w=1;
			$this->riak->dw=1;
		}*/
			
	}
	
	public function riakGet( $key ) {
		return false;
//		$bucket = $this->riak->bucket('etogocache');
//		$item = $bucket->get($key);
//		return $item;
	}
	
	public function riakPut( $key, $value ) {
		# Choose a bucket name
		return false;
//		$bucket = $this->riak->bucket('etogocache');
//		$item = $bucket->newObject( $key, array(
//			'stamp' => time(),
//			'data' => $value
//		));
//		return $item->store();
	}
	
	public function output( $data, $type = null ) {
		header( "HTTP/1.1 200 OK" );
		if( is_string($data) ) {
			if( $this->outputtype == 'css' ) header( 'Content-type: text/css' );
			else if( $this->outputtype == 'js' ) header( 'Content-type: application/javascript' );
			else if( $this->outputtype == 'csv' ) {
				//header( "Content-Type: text/csv" );	
			}
			echo $data;
			return;
		} else {
			if( $this->outputtype == 'xml' ) {
				header( "Content-type: text/xml" );
				//$xml = Array2XML::createXML('results', $data);	
			} else {
				header("Content-type: text/plain;charset=iso-8859-1");
			}

			// check for results in there already and split it out
			if( isset($data['results'] ) ) {
				$return = array( 'results' => $data['results'] ); 
				// loop through each other one and add it in
				foreach( $data as $key => $value ) {
					if( $key != 'results' ) {
						$return [$key] = $value;
					}
				}
			} else $return = array( 'results' => $data );
			if( $this->cached ) {
				$return['cached'] = TRUE;
				$return['hash'] = $this->hash;
			}
			if( !isset($return['rows']) ) $return['rows'] = sizeof($return['results']);
		
			echo ( $this->outputtype == 'xml' ? $this->array2xml($data) : json_encode( $return ) );
		}
	}
	
	public function array2xml($array, $xml = false){
	    if($xml === false){
	        $xml = new SimpleXMLElement('<resultset/>');
	    }
	    foreach($array as $key => $value){
	        if(is_array($value)){
	            $this->array2xml($value, $xml->addChild((is_numeric($key) ? 'result' : $key ) ));
	        }else{
	            $xml->addChild($key, $value);
	        }
	    }
    	return $xml->asXML();
	}
	
	public function bind( $name, $value = null ) {
		if( is_array( $name ) ) {
			foreach( $name as $k => $v ) db::bind( $k, $v );
		} else {
			return db::bind( $name, $value );
		}
	}
	
	public function cache( $query, $expires ) {
		$this->hash = md5( sha1($query) . md5(serialize(db::$parameters)) . $_SERVER['SERVER_NAME'] );
		// see if there is an item in the cache
		$cache = $this->riakGet( $this->hash );
		if( $cache->exists ) {
			// cache exists, lets check to see if its expired
			if( $cache->data['stamp'] > (time()-$expires) ) {
				$this->cached = TRUE;
				return unserialize($cache->data['data']);
			}
		}
		return false;
		
		
		/*if( file_exists( $this->path . $this->hash ) ) {
			if( filemtime( $this->path . $name ) > (time()-$expires) ) {
				//echo 'cache';
				$this->cached = TRUE;
				return unserialize(file_get_contents( $this->path . $this->hash ));
			} else {
				unlink($this->path . $this->hash);
			}
		}
		return false;*/
	}
	
	public function read( $query, $expires = 1800, $bypass = FALSE, $db = NULL ) { // cache for 30 minutes
		// if there is a cache then return it
		if( $cache = $this->cache($query, $expires) ) {
			return $cache;
		}

		if( LOCAL_DB == 'hurdmantest' && preg_match( "/common./", $query ) && !preg_match("/^common::/", $query ) ) {
			$query = "common::" . $query;
		}

		if( !$this->connected ) $this->dbconnect(LOCAL_DB);

		if( $bypass ) $results = db::bypass( "/* api:etogo */ " . $query, $db );
		elseif( substr($query,0,1) == '[' ) $results = db::run( str_replace( array("[","]"), "", $query ) );
		else {
			if( preg_match( "/^common::/", $query ) ) $results = db::common( str_replace( "common::", "", $query ) );
			else $results = db::query( "/* api:etogo */ " . $query );
		}
		// write to the cache
//		$this->riakPut( $this->hash, serialize($results) );
		if( strlen( db::$error ) ) {
			$results['results'] = $results;
			$results['error'] = db::$error;
		}
		//file_put_contents( $this->path . $this->hash, serialize($results) );
		return $results;
	}
	
	public function write( $id, $query, $params = array() ) {
		if( !$this->connected ) $this->dbconnect(LOCAL_DB);
		if( substr($query,0,1) == '[' ) {
			$query = db::resolve_pipes(db::getquery(str_replace(array("[","]"),"",$query)));
		} else {
			$query = db::resolve_pipes($query);
		}
//		echo "/* $id */ " . $query;

		foreach( explode( "||", str_replace( "\r\n", "", $query ) ) as $q ) {
			//echo $query;
			if( preg_match("/^common::/", $id) ) $result = db::common( "/* $id */ " . $q, ( preg_match("/^select/", $q) ? true : false ) );
			else $result = db::query( "/* $id */ " . $q );// or die(mysql_error(self::$connect));
			//echo $query;
		}
		
		return array(
			"result" => $result,
			"error" => mysql_error(),
			"sql" => $query
		);
	}
	
	public function dbconnect() {
		db::connect(LOCAL_DB);
		$this->connected = TRUE;
	}
}

?>