<?php

include "_m.php";

// Library
include _MOBILEHELPERS;
include _DATABASE;
include _MOBILETEMPLATE;
include _MOBILEPARSE;
include _MOBILEREPORTR;
include _MOBILECONFIG;

if(isset($_COOKIE["reportr_username"]) && !(strstr($_COOKIE["reportr_keys"],"a*") xor strstr($_COOKIE["reportr_keys"],"ak"))) {
    $find = $task->get_row("SELECT allowed FROM reportaccess WHERE allowed='n' and staff='".$_COOKIE["reportr_username"]."' and (MergeCode = '" . $reportr->current_query .(isset($_GET["editr"]) || isset($_POST[editr])?".editr":""). "' or '" . $reportr->current_query .(isset($_GET["editr"]) || isset($_POST[editr])?".editr":""). "' like concat(mergecode,'.%'))");
    if($task->num_rows > 0) {
        $find = $task->get_row("SELECT allowed FROM reportaccess WHERE allowed='y' and staff='".$_COOKIE["reportr_username"]."' and (mergecode like '" . $reportr->current_query .(isset($_GET["editr"]) || isset($_POST[editr])?".editr":""). ".%' || mergecode = '" . $reportr->current_query .(isset($_GET["editr"]) || isset($_POST[editr])?".editr":""). "')");
        if($task->num_rows > 0) $task->num_rows=0; else $task->num_rows=1;
    }
    if($task->num_rows > 0) { $reportr->current_query="access denied"; $variables[0]="access denied"; }
    if($reportr->current_query=="exportr") {
        $find = $task->get_row("SELECT allowed FROM reportaccess WHERE allowed='n' and staff='".$_COOKIE["reportr_username"]."' and (MergeCode = '" . $parse->params["exportrquery"] . "' or '" . $parse->params["exportrquery"] . "' like concat(mergecode,'.%'))");
        if($task->num_rows > 0) {
            $find = $task->get_row("SELECT allowed FROM reportaccess WHERE allowed='y' and staff='".$_COOKIE["reportr_username"]."' and (mergecode like '" . $parse->params["exportrquery"].".%' or mergecode = '" . $parse->params["exportrquery"]."')");
            if($task->num_rows > 0) $task->num_rows=0; else $task->num_rows=1;
        }
        if($task->num_rows > 0) { $parse->params["exportrquery"]="access denied"; }
    }
}

$device->define("about_link", "About");
$device->define("APPLICATION_FILENAME", APPLICATION_FILENAME);

$reportr->background->select(NO_REPLICATE_DB);
$reportr->background->query("replace into ".LOCAL_DB.".reportlog (mergecode, staff, day, count) select ifnull(mergecode,'".$reportr->current_query."'), ifnull(staff,'".$_COOKIE["reportr_username"]."'), ifnull(day,curdate()), ifnull(count+1,1) from ".LOCAL_DB.".reportlog where mergecode='".$reportr->current_query."' and staff='".$_COOKIE["reportr_username"]."' and day=curdate() having count(*) in (0,1)");
$reportr->background->select(LOCAL_DB);

if(ereg("mycomplaints",$_SERVER["REQUEST_URI"]) && ($_COOKIE["reportr_flag"]=="" || $_COOKIE["reportr_flag"]==" "))
    $parse->params["reportr_flag"]=$reportr->background->get_var("select bid from staff where initials='".$_COOKIE["reportr_username"]."' union select bid from commonstaff where initials='".$_COOKIE["reportr_username"]."' union select bid from mclogin where uid='".$_COOKIE["reportr_username"]."' limit 1");

// start reportr processing
if($variables[0] == 'auth') login(_prompt); // prompt to login to the system

if(isset($_COOKIE["reportr_username"]) && $reportr->filename=='report' && ereg("auth",$_SERVER["HTTP_REFERER"]) && ereg("^,[0-9]+,", $_COOKIE["reportr_keys"])) $device->define("iptracking", "<script>window.parent.open(\"http://www.bbbmetrics.com/setips.html?database=".LOCAL_DB."&bypass=jv53gh8029f43ig5j290\", \"setip\", \"status=no, scrollbars=yes, addressbar=none, toolbars=none, resizable=no, width=10, height=10\");</script>");
else $device->define("iptracking", "");

if($reportr->current_query!="report access" and (strstr($_COOKIE["reportr_keys"],"a*") xor strstr($_COOKIE["reportr_keys"],"ak"))) {
    $device->define("restriction_link", "<div style='display:block;position:absolute;left:0px;top:0px;'><a target=\"_blank\" href=\"/report/report access/?noheader&mymergecode=".$reportr->current_query.(isset($_GET["editr"]) || isset($_POST[editr])?".editr":"")."\"><img border=0 src=\"/images/key.gif\"></a></div>");
    if(eregi("^qvq ",$reportr->current_query) || ereg("(advertising|mypl|investigationslist|statusreview|relatedreports|button mycomplaints|special projects|lite button bg[.]sales)",$reportr->current_query)) $device->define("restriction_link_noheader", "<div style='display:block;position:absolute;left:0px;top:0px;'><a target=\"_blank\" href=\"/report/report access/?noheader&mymergecode=".$reportr->current_query.(isset($_GET["editr"]) || isset($_POST[editr])?".editr":"")."\"><img border=0 src=\"/images/key.gif\"></a></div>");
}

if(ereg("^/filebrowser", $_SERVER[PATH_INFO]))
    require "/home/serv/library/browse.php";

elseif(ereg("^/cibr/(.+)$", $_SERVER[PATH_INFO], $regs))
    include MOBILE_REPORT_URI . 'cibr.php';

elseif(ereg("^/calendar", $_SERVER[PATH_INFO]) && isset($_GET["ebindr2"]))
    require "/home/serv/public_html/ebindr/views/calendar.html";

elseif(ereg("^/calendar", $_SERVER[PATH_INFO]))
    require "/home/serv/includes/calendar.php";

elseif($variables[0] == 'api')
    $reportr->api(); // roll api call

elseif(isset($_GET["importr"]))
    include MOBILE_REPORT_URI . 'importr.php';

elseif(isset($_GET["editr"]) || isset($_POST[editr])){

    if(isset($_GET["callresult"]) && $_GET["callresult"] == 'EM' && isset($_GET['crm']))
        include MOBILE_REPORT_URI . 'crm.php';
    else
        include MOBILE_REPORT_URI . 'editr.php';

}

elseif(ereg("^/open", $_SERVER[PATH_INFO]))
    include MOBILE_REPORT_URI . 'open.php';

elseif(ereg("^/merge", $_SERVER[PATH_INFO]))
    include MOBILE_REPORT_URI . 'merge.php';

else
    include MOBILE_REPORT_URI . 'report.php';

/* PRINTWORD */
if(isset($_GET["printword"])) {
    header('Expires: ' . gmdate('D, d M Y H:i:s',time()-4800) . ' GMT');
    header('Content-Disposition: attachment; filename="wordtest.rtf"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    ob_end_flush();
}