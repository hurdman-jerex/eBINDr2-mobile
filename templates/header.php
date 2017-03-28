<?php
session_start();
error_reporting(0);
include "/home/serv/public_html/ebindr/includes/functions.php";
if(file_exists($_SERVER["DOCUMENT_ROOT"]."/../definitions.php")) {
    include $_SERVER["DOCUMENT_ROOT"]."/../definitions.php"; // global definitions
}
if(file_exists("../../definitions.php")) {
    include "../../definitions.php"; // global definitions
}
if(file_exists("/home/definitions.php")) {
    include "/home/definitions.php"; // global definitions
}
if(file_exists("../definitions.php")) {
    include "../definitions.php"; // global definitions
}
include "/home/serv/includes/definitions.php"; // global definitions

$___ebindr2mobile_http = array(
    'uri' => '',
    'args' => '',
    'protocol' => ( ( $_SERVER[HTTPS]=='on' ) ? 'https://' : 'http://' ),
    'segments' => array(),
    'url' => '',
    'servername' => $_SERVER[ 'SERVER_NAME' ]
);
// get rid of the get query string
if( strpos($_SERVER['REQUEST_URI'],"?") ) list( $_SERVER['REQUEST_URI'], $___ebindr2mobile_uri[ 'args' ] ) = explode( "?", $_SERVER['REQUEST_URI'] );

$___ebindr2mobile_http['uri'] = $_SERVER['REQUEST_URI'];
$___ebindr2mobile_http['segments'] = array_slice( explode( "/", $_SERVER['REQUEST_URI'] ), 2 );

//echo '<pre>'.print_r( $___ebindr2mobile_http['segments'], true ).'</pre>';

if( $_SERVER['SERVER_NAME'] == 'seatac.ebindr.com' ) $___ebindr2mobile_http['servername'] = $_SERVER["SERVER_NAME"] = 'localhost';

$myHost = $___ebindr2mobile_http['protocol'];
if (strpos($_SERVER['SERVER_NAME'],'vancouver') !== false) $___ebindr2mobile_http['protocol'] = $myHost = 'https://';
if (strpos($_SERVER['SERVER_NAME'],'mbc') !== false) $___ebindr2mobile_http['protocol'] = $myHost = 'https://';

// Finally set our Base URL
$___ebindr2mobile_http[ 'url' ] = $___ebindr2mobile_http[ 'protocol' ] . $___ebindr2mobile_http[ 'servername' ] . '/m/';
$___ebindr2mobile_http[ 'api_url' ] = $___ebindr2mobile_http[ 'protocol' ] . $___ebindr2mobile_http[ 'servername' ] . '/m/api/';
$___ebindr2mobile_http[ 'report_url' ] = $___ebindr2mobile_http[ 'protocol' ] . $___ebindr2mobile_http[ 'servername' ] . '/report/';

include "/home/serv/public_html/m/_autoload/_autoload.php";
include "/home/serv/library/json.php";

function securityCheck($accessRequired, $regular_expression = false) {
    $granted = false;
    $security_keys = explode(",", $_SESSION['user']['reportr_keys']);
    if ($regular_expression) {
        foreach($security_keys as $security_key) {
            if (preg_match ( $accessRequired, $security_key )) $granted = true;
        }
    } else {
        foreach($security_keys as $security_key) {
            if ($accessRequired == $security_key) $granted = true;
        }
    }
    return $granted;
}

if ( !isset($_SESSION['user']) ) {
    header("Location: " . $___ebindr2mobile_http[ 'url' ] . "sign-in.html");
}

$_segment_count = count( $___ebindr2mobile_http[ 'segments' ] );
$___ebindr2mobile_http[ 'segments' ][ $_segment_count - 1 ] = str_replace( '.php', '', str_replace( '.html', '', $___ebindr2mobile_http[ 'segments' ][ $_segment_count - 1 ] ) );

// Set Page Title
if( $___ebindr2mobile_http[ 'segments' ][ $_segment_count - 1 ] == 'business' && isset( $_GET[ 'info' ] ) ) {
    $_business_info = str_replace( '-', ' ', $_GET[ 'info' ] );
    $___ebindr2mobile_http['segments'][$_segment_count - 1] = $___ebindr2mobile_http['segments'][$_segment_count - 1] . ' ' . $_business_info;
}

$__page_title =  implode( " ", $___ebindr2mobile_http[ 'segments' ] );
$__page_title = ' - ' . ucwords( str_replace( 'index', '', $__page_title ) );

/**
 * LOCAL DB
 */
include '/home/serv/library/mybindr.php';
if( isset( $_SESSION['bid'] ) && is_numeric( $_SESSION['bid'] ) && $_SESSION['bid'] > 0 ) {
    $mybindr = new mybindr;
    $mybindr->database = LOCAL_DB;
    mysql_select_db($mybindr->database, $mybindr->db);
    $mybindr->addparm('bid', $_SESSION['bid']);
    $mybindr->addparm('staff', $_COOKIE["reportr_username"]);
    list($q) = $mybindr->getquery("e2m e button info");
    $q = $mybindr->ResolvePipes($q);

    $__business_info = array();
    foreach( explode( "||", str_replace( "\r\n", "", $q ) ) as $query )
        ( $result = mysql_fetch_assoc( mysql_db_query(LOCAL_DB, $query, $mybindr->db) ) ) ? $__business_info = array_merge( $__business_info, $result ) : null;

    //echo '<pre>'.print_r( $__business_info, true ).'</pre>';
}

/**
 * Get Main Menu
 */

$__main_menu = array();
$__main_menu['api'] = json_decode( $bbapi->get( $___ebindr2mobile_http['api_url'] . 'menu/main' ) );
//echo '<pre>'.print_r( $__main_menu, true ).'</pre>';
if( $__main_menu['api']->rows > 0 ){
    $__main_menu['ul'] = array();
    foreach( $__main_menu['api']->results[0] as $item => $value ){
        $dm = explode( '.', $item );
        if( isset( $dm[1] ) )
            $__main_menu['ul'][$dm[0]][$dm[1]] = $value;
        else
            $__main_menu['ul'][$dm[0]] = $value;
    }

    //echo '<pre>'.print_r( $__main_menu['ul'], true ).'</pre>';
}