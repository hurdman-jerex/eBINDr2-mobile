<?php
session_start();
$myHost = 'http://';
if( $_SERVER['SERVER_NAME'] == 'seatac.ebindr.com' ) $_SERVER["SERVER_NAME"] = 'localhost';
if (strpos($_SERVER['SERVER_NAME'],'vancouver') !== false) $myHost = 'https://';
if (strpos($_SERVER['SERVER_NAME'],'mbc') !== false) $myHost = 'https://';
include "/home/serv/public_html/m/_autoload/_autoload.php"; 
include "/home/serv/library/json.php";

//print_r($_SESSION['user']);

function securityCheck($accessRequired, $regular_expression = false) {
    $granted = false;
    $security_keys = explode(",", $_SESSION['user']->securitykey);
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
//$test = array();
//echo securityCheck('b*');

if ( !isset($_SESSION['user']) ) {
  if (strpos($_SERVER['SERVER_NAME'],'vancouver') !== false) header("Location: https://" . $_SERVER['SERVER_NAME'] . "/m/sign-in.html");
  if (strpos($_SERVER['SERVER_NAME'],'mbc') !== false) header("Location: https://" . $_SERVER['SERVER_NAME'] . "/m/sign-in.html");
    header("Location: http://".$_SERVER['SERVER_NAME']."/m/sign-in.html");
}

if ( !isset($_SESSION['bid']) ) {
    $_SESSION['bid'] = 2000099;
}
if ( !isset($_SESSION['user']->name) ) {
    $_SESSION['user']->name = "hurdmantest";
}
//print_r($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>eBINDr2go</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Le styles -->
    <!--<link href="/m/assets/css/bootstrap.css" rel="stylesheet">-->
    <link href="/m/assets/bootstrap3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <style type="text/css">
      /*body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }
      .span4.contain {
          border-bottom: 1px solid #ccc;
          margin-bottom: 3px;
      }*/
    </style>
    <link href="/m/assets/css/bootstrap-responsive.css" rel="stylesheet">
    <link href="/m/assets/bootstrap3.3.7/css/bootstrap-theme.min.css" rel="stylesheet">
    
    <link href="/m/assets/css/m.css" rel="stylesheet">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="/m/assets/img/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="/m/assets/img/mobile-apps-144.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="/m/assets/img/mobile-apps-114.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="/m/assets/img/mobile-apps-72.png">
    <link rel="apple-touch-icon-precomposed" href="/m/assets/img/mobile-apps-57.png">
    <!--<script src="/m/assets/js/jquery.js"></script>-->
    <script src="https://code.jquery.com/jquery-3.1.0.min.js"></script>
    <script type="text/javascript">jQuery.noConflict();</script>
  </head>

  <body>
    <div id="force_success" style="
        color: white; padding: 10px; position: absolute; height: 20px; margin-left: 40%; 
        font-weight: bold; font-size: 18px; width: 245px; background: #b4e391;
    border-color: #9999FF; border-radius: 10px 10px 10px 10px; border-style: solid;
    border-width: 1px; display: none;    
        background: -moz-linear-gradient(top, #b4e391 0%, #61c419 50%, #b4e391 100%);
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#b4e391), color-stop(50%,#61c419), color-stop(100%,#b4e391));
        background: -webkit-linear-gradient(top, #b4e391 0%,#61c419 50%,#b4e391 100%);
        background: -o-linear-gradient(top, #b4e391 0%,#61c419 50%,#b4e391 100%);
        background: -ms-linear-gradient(top, #b4e391 0%,#61c419 50%,#b4e391 100%)
        background: linear-gradient(to bottom, #b4e391 0%,#61c419 50%,#b4e391 100%);
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#b4e391', endColorstr='#b4e391',GradientType=0 );">
        Force update complete
        <div id="force_exit" style="    
            background: none repeat scroll 0 0 red;
            border-radius: 12px 12px 12px 12px;
            cursor: pointer;
            float: right;
            padding-bottom: 5px;
            text-align: center;
            width: 20px;">x</div>
    </div>