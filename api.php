<?php
 
include "/home/serv/library/json.php";
include "/home/serv/public_html/m/apis/library/db.php";
include "/home/serv/public_html/m/apis/library/hapi.php";

//echo '<pre>'.print_r( $_SERVER, true ) .'</pre>';

echo " " . $_SERVER['REQUEST_URI'] . " ";
$path = "/home/serv/public_html/m/apis/" . str_replace( "/m/api/", "", $_SERVER['REQUEST_URI'] );
$url_parts = explode( "/", preg_replace( "/^\//", "", $path ) );
echo " " . $path . " ";
foreach( array_reverse(explode("/",$path)) as $i => $class ) {
	// look for that file in the path
	$_path = implode("/",explode("/", $path, -$i ));
	if( !file_exists( $_path . "/" . $class . ".php" ) ) {
		// look for that file in the previous path
		$_tmp_path = implode("/",explode("/",$_path,-1));
		$_tmp_class = end(explode("/",$_path));
		if( file_exists( $_tmp_path . "/" . $_tmp_class . ".php" ) ) {
			echo "here exist";
			$class_location = $_tmp_path . "/" . $_tmp_class . ".php";
			$class_name = $_tmp_class."_API";
			break;
		} else {
			$method_name = $_tmp_class;
			continue;
		}
	} else {
		$class_location = $_path . "/" . $class . ".php";
		$class_name = $class . "_API";
		break;
	}
}

include $class_location;
$api = new $class_name();
echo json_encode($api->$method_name());

?>