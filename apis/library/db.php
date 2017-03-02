<?php

class db {

	private static $host = "localhost";
	private static $user = "hurdmantest";
	private static $pass = "7d4a34f241093ecd0fb01fdda975ead2";
	private static $commonhost = '';
	private static $commonuser = '';
	private static $commonpass = '';
	
	

	static $connect = NULL; // the connection to uber
	static $commonlink = NULL; // db linkt o common
	static $db = NULL; // the database we'll be querying on
	static $bbbid = NULL; // what bbbid we are connected to
	static $parameters = array(); // the bound parameters for queries
	static $dict = NULL; // dictionary associated with the mergequery
	static $error = ''; // error if there is one
	
	/**
	 * Make the construct private to prevent external instantiation
	 */
	private function __construct() {}
	
	/**
	 * Bind a key=>value
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return boolean
	 */
	public static function bind( $key, $value = null ) {
		if( is_array($key) ) {
			foreach( $key as $k => $v ) self::bind( $k, $v );
		} else {
			self::$parameters[ hurdman_query_escape($key) ] = hurdman_query_escape( $value );
		}
	}
	
	/**
	 * Run a query
	 *
	 * @param string $bbbid 
	 * @param string $query
	 *
	 * @return array
	 */
	public static function query( $query, $dbname = FALSE ) {
		if( !self::$connect ) self::connect();
		mysql_select_db(self::$db,self::$connect);
		$sql = mysql_query( self::resolve_pipes($query), self::$connect );
		self::$error = mysql_error(self::$connect);
		// make sure there were results
		if( mysql_num_rows($sql) < 1 ) {
			return array();
		} else {
			while( $row = mysql_fetch_assoc( $sql ) ) {
				$result[] = $row;
			}
		}
		
		return $result;
		
	}
	
	public static function common( $query, $select = true ) {
		if( !self::$commonlink ) self::commonconnect();
		mysql_select_db( 'common', self::$commonlink );
		$sql = mysql_query( self::resolve_pipes($query), self::$commonlink ) or die(self::resolve_pipes($query) . " - " . mysql_error());
		self::$error = mysql_error(self::$commonlink);
		if( $select ) {
			if( mysql_num_rows($sql) < 1 ) {
				return array();
			} else {
				while( $row = mysql_fetch_assoc($sql) ) {
					$result[] = $row;
				}
			}
		
			return $result;
		} else {
			return $sql;
		}
	}
	
	public static function bypass( $query, $dbname = 'hurdmantest' ) {
		if( $dbname == 'hurdmantest' ) $dbname = $_SERVER['hurdmantest']['db'];
		if( self::$connect ) {
			mysql_select_db($dbname,self::$connect);
			$sql = mysql_query( self::resolve_pipes($query), self::$connect ) or die(mysql_error(self::$connect));
			// make sur ethere were results
			if( mysql_num_rows($sql) < 1 ) {
				return array();
			} else {
				while( $row = mysql_fetch_assoc($sql) ) {
					$result[] = $row;
				}
			}
			
			return $result;
		}
	}
	
	/**
	 * Run a mergecode
	 *
	 * @param string $bbbid
	 * @param string $mergecode
	 * @param string $response
	 * @return array
	 */
	public static function run( $mergecode, $dbname = FALSE ) {
		if( !self::$connect ) self::connect();
		
		foreach( explode( "||", str_replace( "\r\n", " ", self::resolve_pipes(self::getquery($mergecode)) ) ) as $query ) {
			if( preg_match( "/directory2/i", $mergecode ) ) $query = "/* api:abpages2 " . $mergecode . " */ " . $query;
			mysql_select_db(self::$db,self::$connect);
//			if( $mergecode == 'e2mobile/api/business/complaints' ) echo $query . '<br /><br />';
			$sql = mysql_query( $query, self::$connect );
			if( mysql_error(self::$connect) ) {
				$result[] = array(
					"query" => $query,
					"error" => mysql_error(self::$connect)
				);
			}
			//echo $query;
		}
			
		// make sure there were results
		if( @mysql_num_rows($sql) < 1 ) {
			//echo mysql_error(self::$connect);
			return array();
		} else {
			while( $row = mysql_fetch_assoc( $sql ) ) {
				$result[] = $row;
			}
		}
		
		return $result;
	}
	
	/*
		Get a single value
	*/
	public static function value( $mergecode ) {
		if( substr( $mergecode, 0, 1 ) == '[' ) $result = self::run( $mergecode );
		else $result = self::query( $bbbid, $mergecode );
		if( $result ) {
			$i=0;
			foreach( $result[0] as $field )
				if( $i == 0 ) return $field;
		}
	}
	

	/**
	 * Get Global definitions
	 */
	public static function setGlobalCredentials(){
		try{	
			if(file_exists($_SERVER["DOCUMENT_ROOT"]."/../definitions.php")) {     
			include $_SERVER["DOCUMENT_ROOT"]."/../definitions.php"; // global definitions     
			}
			if(file_exists("../../../definitions.php")) {
				include "../../../definitions.php"; // global definitions
			}
			if(file_exists("/home/definitions.php")) {
				include "/home/definitions.php"; // global definitions
			}
			if(file_exists("../definitions.php")) {
				include "../definitions.php"; // global definitions
			}
			include "/home/serv/includes/definitions.php"; // global definitions

			self::$host=DATABASE_HOST;
			self::$user=DATABASE_USER;
			self::$pass=DATABASE_PASS;
			self::$db='hurdmantest';
			
			self::$commonhost = COMMON_HOST;
			self::$commonuser = COMMON_USER;
			self::$commonpass = COMMON_PASS;
			/*
			echo "use global definitions";
			echo " <br/>host = ". self::$host;
			echo " <br/>user = " . self::$user;
			echo " <br/>password = " . self::$pass;
			echo " <br/>db = ". $_SERVER['hurdmantest']['db'];
			echo "<pre>";
			print_r($_SERVER);
			*/
		} catch (Exception $e){
			echo "<p>Error in getQuery($name) <br/>Details: ". $e->getMessage();
		}
	}
	
	/**
	 * Connect to uber
	 */
	public static function connect( $dbname = 'hurdmantest' ) {
		
		self::setGlobalCredentials(); 
		
		//if( $dbname == 'etogo' ) $dbname = $_SERVER['etogo']['db'];
		if( $dbname == 'hurdmantest' ) $dbname = $_SERVER['hurdmantest']['db'];
				
		if( is_null(self::$connect) ) self::$connect = mysql_connect( self::$host, self::$user, self::$pass );
		// if there is no connection trigger an error
		if( !self::$connect ) {
			trigger_error("Cannot connect to mysql");
		} else {	
			mysql_select_db( $_SERVER['hurdmantest']['db'], self::$connect );
			// get the database by bbbid
			if( !is_null($dbname) ) {
				self::$db = $dbname;
			}
			return self::$connect;
		}
	}
	
	
	/**
	 * Connect to common
	 */
	public static function commonconnect( $dbname = 'common' ) {
		
		self::setGlobalCredentials(); 
				
		if( is_null(self::$commonlink) ) self::$commonlink = mysql_connect( self::$commonhost, self::$commonuser, self::$commonpass );
		// if there is no connection trigger an error
		if( !self::$commonlink ) {
			trigger_error("Cannot connect to common mysql");
		} else {	
			mysql_select_db( 'common', self::$commonlink );
			return self::$connect;
		}
	}
	
	// get the query from the mergequery table
	static function getquery( $name ) {
		try{
			if( empty(self::$db) ) self::$db = $_SERVER['hurdmantest']['db'];
			mysql_select_db(self::$db,self::$connect);
			$find = mysql_query( "select sqlstatement from mergequery where mergecode = '".$name."'", self::$connect );
			//$find = mysql_query( "select m.sqlstatement, m2.sqlstatement as dict from mergequery m left join mergequery m2 on concat(m.mergecode,'.dict') = m2.mergecode where m.mergecode = '".$name."'", self::$connect ) or die(mysql_error());
			// if we couldn't get it from the local
			if( @mysql_num_rows($find) != 1 ) {
				$find = mysql_db_query( "common", "select sqlstatement from mergequery where mergecode = '".$name."'", self::$connect );
			}

			
			list($sql,$dict) = mysql_fetch_row( $find );	 
			if( strlen($dict) > 0 ) self::$dict = json_decode($dict );
			$sql = str_replace( ", ~", "", str_replace( ",~", "", str_replace( ", ^", "", str_replace( ",^", "", str_replace( "^,", "", preg_replace( "/\]$/", "", preg_replace( "/^\[/", "", $sql ) ) ) ) ) ) );
			
			return $sql;
		} catch (Exception $e){
			echo "<p>Error in getQuery($name) <br/>Details: ". $e->getMessage();
		}
	}
	
	/**
	 * @return string
	 * @param mytext string
	 * @desc gets the next merge code in sequence
	 */
	static function get_next_merge_code($mytext) {
		preg_match ("/\[[^]]*\]/",  $mytext, $returned);
		return $returned[0];
	}
	
	/*
		This is used in the loop to get the next parameter name and value
	*/
	static function get_next_pipe($mytext) {
		preg_match ("/[^|](\|[^|]+\|)([^|]|$)/",  $mytext, $returned);
		return ( isset($returned[1]) ? $returned[1] : '' );
	}
	
	/*
		This function will loop through the sqlstatement string and try to find
		parameters and will resolve them based on get_param();
	*/		
	static function resolve_pipes($mytext) {
		$mytext = str_replace( "{PIPE}", "|", $mytext );
		while ($param = self::get_next_pipe($mytext))
		{
			$new_param = self::get_param(str_replace("|","",$param));
			if($new_param == 'NULL')
				$new_param = '';
			else
				$new_param = $new_param;
			$mytext = substr_replace($mytext, $new_param, strpos($mytext,$param), strlen($param));
		}
		return $mytext;
	}
	
	/**
	 * @return string
	 * @param mytext string
	 * @desc resolves the merge code and sets it up to move through each code removing the [ ]  around each code
	 */
	static function resolve_merge( $mytext )
	{
		$mergecode = self::get_next_merge_code($mytext);
		do
		{
			if(!$mergecode) break;
			$mytext = str_replace($mergecode, self::merge_code($mergecode), $mytext);
		}
		while ($mergecode = self::get_next_merge_code($mytext));
		return $mytext;
	}
	
	/*
		Retrieves the possible parameter values to replace into the queries
	*/
	static function get_param($myparam) {
		return ( isset(self::$parameters[$myparam]) ? self::$parameters[$myparam] : '' );
	}
	
	/**
	 * @return string
	 * @param code string
	 * @desc gets the sql query according to merge code from the databaes and executes it for its content
	 */
	static function merge_code($code) {
	
		$code = str_replace("[","",str_replace("]","",$code));
		$param = self::get_param( $code );
		
		return self::get_param(str_replace("[","",str_replace("]","",$code)));
	}

}


function hurdman_query_escape($value) {

	if (get_magic_quotes_gpc()) {
		$value = stripslashes($value);
	}
	// Escape if not integer
	if (!is_numeric($value)) {
		$value = mysql_escape_string($value);
	}
	return $value;

	//return mysql_escape_string($value);
    $return = '';
    for($i = 0; $i < strlen($value); ++$i) {
        $char = $value[$i];
        $ord = ord($char);
        if($char !== "'" && $char !== "\"" && $char !== '\\' && $ord >= 32 && $ord <= 126)
            $return .= $char;
        else
            $return .= '\\x' . dechex($ord);
    }
    return $return;
}