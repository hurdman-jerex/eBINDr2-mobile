<?
//date_default_timezone_set('America/Denver');
//ini_set('display_errors','1');
include "/home/serv/library/json.php";
//session_start();
// get rid of the get query string
if( strpos($_SERVER['REQUEST_URI'],"?") ) list( $_SERVER['REQUEST_URI'], $get ) = explode( "?", $_SERVER['REQUEST_URI'] );

if(file_exists("/home/definitions.php")) include "/home/definitions.php"; // global definitions
if(file_exists("../definitions.php")) include "../definitions.php"; // global definitions
include "/home/serv/includes/definitions.php"; // global definitions

// check for the output
$output = 'json';
$outputtypes = array('json','xml','css','js','txt');
if( preg_match( "/.json$|.xml$|.css$|.js$|.txt$/i", $_SERVER['REQUEST_URI'] ) ) {
	//$output = str_replace( ".", "", substr( $_SERVER['REQUEST_URI'], -4 ) );
	$output = end(explode(".", $_SERVER['REQUEST_URI']));
	if( !in_array($output,$outputtypes ) ) $output = 'json';
	$_SERVER['REQUEST_URI'] = str_replace($outputtypes, "", $_SERVER['REQUEST_URI'] );
}

// parse the url
// echo "_SERVER['REQUEST_URI']: " . $_SERVER['REQUEST_URI'] . " <br/>";
// $_SERVER['REQUEST_URI'] = str_replace( "/apis/", "", preg_replace( "/\/$/", "", preg_replace( "/\.$/", "", $_SERVER['REQUEST_URI'] ) ) );
// echo "_SERVER['REQUEST_URI']: " . $_SERVER['REQUEST_URI'] . " <br/>";
$uri = explode( "/", $_SERVER['REQUEST_URI'] );
$limit = count($uri);
// echo count($uri);
define( '_ROOT', '/home/serv/public_html/m/apis' );

$path = _ROOT;
for ($i = 3; $i <= $limit; $i++) {
// echo $path . "/" . $uri[$i];
	if( is_dir($path . "/" . $uri[$i]) && $i != $limit - 1 ) {
		$path .= "/" . $uri[$i];
	}
	if( file_exists( $path . "/" . $uri[$i] . ".php" ) ) {
		$class = $uri[$i];
		$method = $uri[$i + 1];
	}
} 

if ( ! $method ) {
	$method = "index";
}
//$path =  substr ( $path , 0 , $lastslash );
// echo " <br/>path: " . $path . " <br/>";
// echo " <br/>class: " . $class . " <br/>";
// echo " <br/>method: " . $method . " <br/>";
//construct segment section
$segment_section = str_replace( "m/api/", "", $_SERVER['REQUEST_URI'] );
if ( trim($segment_section) == "") {
	$segment_section = "/m/api/";
}
$methodIndex = strrpos ( $segment_section , $method);
$segment_section = substr ( $segment_section , $methodIndex + strlen ( $method ) + 1 );
// echo " <br/>segment_section: " . $segment_section . " <br/>";
// echo " <br/>methodIndex: " . $methodIndex . " <br/>";
$seg = explode( "/", $segment_section );

if( file_exists( $path . $class . ".php" ) ) {
	// do the sub class and run its method
	$path .= $class .'.php';
	$class .= '_API';
	//$method = ( $uri[5] ? $uri[5] : 'index' );
}
// echo " <br/>path: " . $path . " <br/>";
// echo " <br/>path: " . $path . " <br/>";
// echo " <br/>class: " . $class . " <br/>";
// echo " <br/>method: " . $method . " <br/>";


//echo "path: " . $path . " <br/>";
// include hapi (hurdman api class)
include _ROOT . "/library/db.php";
include _ROOT . "/library/hapi.php";
// include the class
include $path;

// make sure the method exists
if( !in_array($method,get_class_methods($class)) ) {
	echo "No such method $class::$method";
	exit;
}

/*class Base {
	static $segments = array();
}

Base::$segments = $uri;*/
// print_r($seg);
$api = new $class();
$api->segments = $seg;
$api->output($api->$method());

?>