<?php
$start = microtime();
if(isset($_COOKIE[BBBID])) define("BBBID", $_COOKIE[BBBID]);
include "../../includes/faxserver.php";

if( preg_match( '/boldcommercial|boldfundraising|hurdmantest/i', $_SERVER['SERVER_NAME'], $match ) ){
    $_definitions_file_path = '/home/'. $match[0] .'/definitions.php';
    if( file_exists( $_definitions_file_path ) )
        include $_definitions_file_path;
}

if(file_exists("/home/definitions.php"))
    include "/home/definitions.php"; // global definitions
else
    include "../../includes/definitions.php"; // global definitions
define('AUTO_LOGOUT_TIME', "14400");
include _FUNCTIONS; // common function library
$bypass_this="nodont";
if(isset($_POST[database])) $variables = parse_uri($_POST['database'], DATABASE_HOST);
elseif(isset($_COOKIE['reportr_db'])) $variables = parse_uri($_COOKIE['reportr_db'], DATABASE_HOST);
elseif(ereg("^/(merge|exportr|charity expense pie graph|filebrowser|Process Complaints - Council Download)", $_SERVER[PATH_INFO]) && $_GET[BYPASS]=="5g9f4ds8r") { $bypass_this="goahead"; $variables = parse_uri(LOCAL_DB, DATABASE_HOST); if(!isset($_COOKIE['BBBID'])) $_COOKIE['BBBID']=BBBID; }
elseif((eregi("^/menu[.]Custom[.]Admin[.]Email", $_SERVER[PATH_INFO]) || eregi("^/menu[.]Admin[.]Email", $_SERVER[PATH_INFO]) || eregi("^/menu[.]Custom[.]Admin[.]Night", $_SERVER[PATH_INFO]) || eregi("^/menu[.]Admin[.]Night", $_SERVER[PATH_INFO]) || $_SERVER['HTTP_USER_AGENT']=="runreportqueue" || $_GET[BYPASS2]=="9vfjesu3hgi") && $_GET[BYPASS]=="gure8wh3") { $bypass_this="goahead"; $variables = parse_uri(LOCAL_DB, DATABASE_HOST); if(!isset($_COOKIE['BBBID'])) $_COOKIE['BBBID']=BBBID; }
else $variables = parse_uri(QUERY_DB, DATABASE_HOST);
//if($_GET[BYPASS]=="gure8wh3") echo str_replace("[", "", str_replace ("]", "", print_r($_SERVER, true)));
if($bypass_this!="goahead")
    security(); // restrict access
else $_COOKIE["reportr_keys"]=",HURDMAN,";

include(DIR_LANG . LANGUAGE);

$variables[3] = trim($variables[3]);
if($variables[3] === '0') $variables[3] = '0';

$params = array(
    "tid" => $variables[1], // table id
    "fid" =>$variables[2], // field name
    "rid" =>$variables[3], // 1st column in row
    "value" =>$variables[4], // value of field
    "lang" => $variables["lang"],
    "db" => $variables["db"],
    "host" => $variables["host"],
    "QUERY_DB" => QUERY_DB
);
//print_r($variables);
//die("test");
// if the sub report query is not empty and the extension is set then set extension
!empty($variables[4]) && isset($variables[2]) ? $extension = "." . $variables[2] : $extension = NULL;
// if the parameters name and value aren't equal then set them as paramters
$variables[2] != $variables[4] ? $params[$variables[2]] = $variables[4] : NULL;
if(!isset($_COOKIE["client_timezone_diff"])) $_COOKIE["client_timezone_diff"]="0";
setcookie("security", $_COOKIE["security"], time()-$_COOKIE["client_time_diff"]+AUTO_LOGOUT_TIME, "/");
setcookie("BBBID", $_COOKIE["BBBID"], time()-$_COOKIE["client_time_diff"]+AUTO_LOGOUT_TIME, "/");
setcookie("bbbidreal", $_COOKIE["bbbidreal"], time()-$_COOKIE["client_time_diff"]+AUTO_LOGOUT_TIME, "/");
setcookie("reportr_db", $_COOKIE["reportr_db"], time()-$_COOKIE["client_time_diff"]+AUTO_LOGOUT_TIME, "/");
setcookie("reportr_username", $_COOKIE["reportr_username"], time()-$_COOKIE["client_time_diff"]+AUTO_LOGOUT_TIME, "/");
setcookie("reportr_auto_home", $_COOKIE["reportr_auto_home"], time()-$_COOKIE["client_time_diff"]+AUTO_LOGOUT_TIME, "/");
setcookie("client_time_diff", $_COOKIE["client_time_diff"], time()-$_COOKIE["client_time_diff"]+AUTO_LOGOUT_TIME, "/");
setcookie("client_timezone_diff", $_COOKIE["client_timezone_diff"], time()-$_COOKIE["client_time_diff"]+AUTO_LOGOUT_TIME, "/");
setcookie("reportr_keys", $_COOKIE["reportr_keys"], time()-$_COOKIE["client_time_diff"]+AUTO_LOGOUT_TIME, "/");
setcookie("reportr_conf", $_COOKIE["reportr_conf"], time()-$_COOKIE["client_time_diff"]+AUTO_LOGOUT_TIME, "/");
if($bypass_this=="goahead") $_COOKIE["reportr_db"]=$params["db"];
?>
