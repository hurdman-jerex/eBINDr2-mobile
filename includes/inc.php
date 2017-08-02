<?php
include "/home/serv/public_html/ebindr/includes/functions.php";

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
    include "/home/definitions.php"; // global definitions
}
if(file_exists("../definitions.php")) {
    include "../definitions.php"; // global definitions
}

include "/home/serv/includes/definitions.php"; // global definitions

include "/home/serv/public_html/m/includes/helpers.php";

include "/home/serv/public_html/m/_autoload/_autoload.php";
include "/home/serv/library/json.php";