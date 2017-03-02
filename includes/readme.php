<?php
$start = microtime();
if(isset($_COOKIE[BBBID])) define("BBBID", $_COOKIE[BBBID]);
include "faxserver.php";
if(file_exists("/home/definitions.php"))
    include "/home/definitions.php"; // global definitions
else
    include "definitions.php"; // global definitions
define('AUTO_LOGOUT_TIME', "14400");
include _FUNCTIONS; // common function library

?>
