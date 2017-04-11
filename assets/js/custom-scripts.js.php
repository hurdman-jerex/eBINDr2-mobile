<?php
$url = ( ( $_SERVER[HTTPS]=='on' ) ? 'https://' : 'http://' ) . $_SERVER['SERVER_NAME']."/m/api/custom/scripts";
//$__custom_scripts = json_decode( file_get_contents($url) )->results;
$__custom_scripts = file_get_contents($url);

$filename = 'ebindr-custom.js';

// Send HTTP headers
header("Cache-Control: must-revalidate");
header("Content-Type: text/javascript");
header('Content-Length: '.strlen($__custom_scripts));
header("Content-Disposition: inline; filename=$filename");

// Output merged code
echo $__custom_scripts;