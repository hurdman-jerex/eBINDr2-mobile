<?php
ob_start();
if( preg_match( '/boldcommercial|boldfundraising|hurdmantest/i', $_SERVER['SERVER_NAME'], $match ) ){
    $_definitions_file_path = '/home/'. $match[0] .'/definitions.php';
    if( file_exists( $_definitions_file_path ) )
        include $_definitions_file_path;
}

if(file_exists($_SERVER["DOCUMENT_ROOT"]."/../definitions.php")) {
    include $_SERVER["DOCUMENT_ROOT"]."/../definitions.php"; // global definitions
}
if(file_exists("../../definitions.php")) {
    include "../../definitions.php"; // global definitions
}
if(file_exists("/home/definitions.php")) {
    $codes = file_get_contents( "/home/definitions.php" ); // global definitions
}
if(file_exists("../definitions.php")) {
    include "../definitions.php"; // global definitions
}

echo '<pre>'.print_r( $codes, true ).'</pre>';
$content = ob_get_contents();
ob_end_clean();

echo '<pre>'.print_r( $content, true ).'</pre>';
exit();