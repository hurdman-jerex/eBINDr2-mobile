<?php
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

$_segment_count = count( $___ebindr2mobile_http[ 'segments' ] );
$___ebindr2mobile_http[ 'segments' ][ $_segment_count - 1 ] = str_replace( '.php', '', str_replace( '.html', '', $___ebindr2mobile_http[ 'segments' ][ $_segment_count - 1 ] ) );