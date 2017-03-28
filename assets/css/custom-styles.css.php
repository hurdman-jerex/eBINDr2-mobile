<?php
/* Custom Styles and JS */
$url = ( ( $_SERVER[HTTPS]=='on' ) ? 'https://' : 'http://' ) . $_SERVER['SERVER_NAME']."/m/api/custom/styles";
$__custom_styles = json_decode( file_get_contents($url) )->results;

$filename = 'ebindr-custom.css';

// Send HTTP headers
header("Cache-Control: must-revalidate");
header("Content-Type: text/css");
header('Content-Length: '.strlen($__custom_styles));
header("Content-Disposition: inline; filename=$filename");

// Output merged code
echo $code;