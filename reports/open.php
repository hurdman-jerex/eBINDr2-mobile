<?php

$layout_template = "open".$e2m;
/* REPORT OPEN */

$title=$variables[0].(isset($variables[1])?",":"").$variables[1].(isset($variables[2])?",":"").$variables[2];
$_GET[title]=$title;
include _MYBINDR;
$mybindr = new mybindr;
$mybindr->database = $variables["db"];
$mybindr->begincode="\[";
$mybindr->endcode="\]";
$device->define("heirarchy", $title);
if($_POST["mergesubmit"]=="YES") {
    if(strpos($_SERVER[HTTP_USER_AGENT],"Windows NT 5.1")>0 && !eregi("scanr$", $title)) $attachornot='inline'; else $attachornot='attachment';
    $attachornot="attachment";
    if(eregi("(rtf|xls|csv|scanr|zip|wav)$", $title)) {
        header( "Content-Type: application/octet-stream" );
        header('Content-Disposition: '.$attachornot.'; filename="'.($_GET["version"]?"Version".$_GET["version"]."_":"").str_replace(" ","%20",$title).'"');
    }
    if($_GET["version"]) echo $mybindr->GetDoc($title, $_GET["version"]); else echo $mybindr->GetDoc($title);
} else {
    $device->define("merge_message", "The document you requested is being generated... please wait.");
    $parse->adopt(); // we need some parameters
//<script>submitted=true;limit.submit();</script>');
    echo $device->buffer($layout_template);
}