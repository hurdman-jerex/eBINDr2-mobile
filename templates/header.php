<?php
session_start();
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

if( $_SERVER['SERVER_NAME'] == 'seatac.ebindr.com' ) $___ebindr2mobile_http['servername'] = $_SERVER["SERVER_NAME"] = 'localhost';

$myHost = $___ebindr2mobile_http['protocol'];
if (strpos($_SERVER['SERVER_NAME'],'vancouver') !== false) $___ebindr2mobile_http['protocol'] = $myHost = 'https://';
if (strpos($_SERVER['SERVER_NAME'],'mbc') !== false) $___ebindr2mobile_http['protocol'] = $myHost = 'https://';

// Finally set our Base URL
$___ebindr2mobile_http[ 'url' ] = $___ebindr2mobile_http[ 'protocol' ] . $___ebindr2mobile_http[ 'servername' ] . '/m/';
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
?>