<?php

/* REPORT EDITR */

$layout_template = 'editr';

$device->variable("postinputs", "");
$current_query = $variables[0];

if(!empty($variables[2])) $current_query .= "." . $variables[2];

$device->define("current_query", $current_query.".editr");
$device->define("bid", "'".$_REQUEST["bid"]."'");

// Init Mobile Editr
include _MOBILEEDITR;
$editr = new mobileEditr;

for($i=0; $i<sizeof($info);$i++) {
    $editr->params[strtolower($elements[$i])] = $info[$i];
}

$editr->params = array_merge($editr->params, $params, $parse->params);
$editr->database = (ereg("^masterlist",$current_query)?"masters":$variables["db"]);

list($query, $desc) = $editr->getquery($current_query . ".editr");
if($query=="default") {
    list($query, $desc) = $editr->getdefaultquery($current_query.".editr", $desc);
}

$task->get_row("SELECT mergecode FROM ".QUERY_DB.".reporthelp WHERE MergeCode = '" . $reportr->current_query . "'");

if($task->num_rows==0) $task->get_row("SELECT mergecode FROM reporthelp WHERE MergeCode = '" . $reportr->current_query . "'");
if($task->num_rows>0) $device->define("about_link", "Help");

$query = $editr->querygroup($parse->resolve_merge($query));
$editr->addparm("heading", str_replace("\r\n","\\r\\n",$desc));
$sql = $query[select];

if($_POST[STEP]==0) {
    for($i=0;$i<sizeof($query[update][0]);$i++)
        $query[update][0][$i]=$parse->resolve($query[update][0][$i]);
    //print_r($query); exit();
    $editr->runqueries($query[update][0]);
    if(ereg("HURDMAN",$editr->params["keys"]) && sizeof($editr->queriesrun)>0) $editr->mybindr_query("insert into reportquerylog (mergecode, day, staff, results) values ('".$reportr->current_query.".editr', now(), '".$_COOKIE["reportr_username"]."', '".addslashes(implode("\r\n",$editr->queriesrun))."')");
}

if(ereg("^FORCE(.+)$", $_POST["reportr"], $reportrregs)) $editr->params["reportr"]=$reportrregs[1];

if($_POST[STEP]==1 || $_GET['SUBMIT']=='yes') {
    for($i=0;$i<sizeof($query[update][1]);$i++) {
        $onequery=$parse->resolve($query[update][1][$i]);
        if(eregi("^use .+", $onequery)) $editr->runqueries(array($onequery));
    }
    if($sql>"") {

        $editr->mybindr_query($editr->setform($editr->ResolvePipes($parse->resolve($sql[0]))),$editr->database, false, true);
        if(mysql_affected_rows($editr->db)>0) {
            $reportr->background->select(NO_REPLICATE_DB);
            $reportr->background->query("replace into ".LOCAL_DB.".reportlog (mergecode, staff, day, count) select ifnull(mergecode,'".$reportr->current_query.".editr'), ifnull(staff,'".$_COOKIE["reportr_username"]."'), ifnull(day,curdate()), ifnull(count+1,1) from ".LOCAL_DB.".reportlog where mergecode='".$reportr->current_query.".editr' and staff='".$_COOKIE["reportr_username"]."' and day=curdate() having count(*) in (0,1)");
            $reportr->background->select(LOCAL_DB);
        }
    }

    for($i=0;$i<sizeof($query[update][1]);$i++) {
        $query[update][1][$i]=$parse->resolve($query[update][1][$i]);
        if(eregi("^use .+", $query[update][1][$i])) $editr->runqueries(array($query[update][1][$i]));
    }

    // Run Query Update
    $editr->runqueries($query[update][1]);
    if(ereg("HURDMAN",$editr->params["keys"])) $editr->mybindr_query("insert into reportquerylog (mergecode, day, staff, results) values ('".$reportr->current_query.".editr', now(), '".$_COOKIE["reportr_username"]."', '".addslashes(implode("\r\n",$editr->queriesrun))."')");

    $reportr_url = $editr->ResolvePipes(stripslashes(stripslashes($editr->params[reportr])));

    if( isset($_GET['eform']) && $eform = $_GET['eform'] ){
        $reportr_url = base64_decode( $_GET['eform'] );
    }

    // Let's Display success and reload the parent page.
    $device->define("content", "<div class='alert alert-success'>Changes have been saved</div><script type='text/javascript'>( function() { window.parent.scroll(0,0); window.parent.location = '". $reportr_url ."';}).delay(1000);</script>");
    echo $device->buffer($layout_template);
}

if($sql=="") { header("Location: ".$editr->params[reportr]); exit();}

for($i=0;$i<sizeof($sql);$i++)
    $sql[$i]=$parse->resolve($sql[$i]);

$editr->params = array_merge($editr->params, $params, $parse->params);
$device->define("content", $editr->getform($sql, 0, $desc));
echo $device->buffer($layout_template);