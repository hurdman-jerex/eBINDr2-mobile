<?php
if(session_id() == '') {
    session_start();
}
error_reporting(1);

$_SESSION['bid'] = $_GET['bid'];

include "/home/serv/public_html/m/includes/inc.php";
include "/home/serv/public_html/m/includes/http.php";

/**
 * LOCAL DB
 */
include '/home/serv/library/mybindr.php';
$mybindr = new mybindr;
$mybindr->database = LOCAL_DB;
mysql_select_db($mybindr->database, $mybindr->db);
$__business_info = array();

$mybindr->addparm('bid', $_GET['bid'] );

$mybindr->addparm('staff', $_COOKIE["reportr_username"]);
list($q) = $mybindr->getquery("e2m e button info");
$q = $mybindr->ResolvePipes($q);

foreach( explode( "||", str_replace( "\r\n", "", $q ) ) as $query )
    ( $result = mysql_fetch_assoc( mysql_db_query(LOCAL_DB, $query, $mybindr->db) ) ) ? $__business_info = array_merge( $__business_info, $result ) : null;

//$__business_info['info_js'] = preg_grep('/js_/', array_keys( $__business_info ));

/* Lets get basic info from API */
/*$url= $___ebindr2mobile_http[ 'api_url' ] .'business/basic/staff/update/'.$_GET['bid'].'/'.$_SESSION['user_info']->sid;
$bbapi->get($url);

$business_info = json_decode( $bbapi->get( $___ebindr2mobile_http[ 'api_url' ] .'business/info/'. $_GET['bid'] ) )->results;*/

/*ob_start();
include "/home/serv/public_html/m/templates/business/info.php";
$html = ob_get_contents();
ob_end_clean();*/

$results = array(
    //'business' => $business_info[0],
    'info' => $__business_info,
    //'html' => $html,
    'success' => true
);

header('Content-Type: application/json; charset=utf-8');
echo print_r( json_encode( $results ) , true );
?>
