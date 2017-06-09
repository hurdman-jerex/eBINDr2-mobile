<?php
/* FINDR2 REPORT */
$layout_template = 'noheader_findr2';
if(isset($_GET["external_gateway"]) && !empty($_GET["external_gateway"])) {
    $task->num_rows = 1;
    $temp = read_tmp_file('query');
    $find["query"] = $temp["qvq"] . "\r\n||\r\n" . $temp["query"];
} else {
    if(ereg("^mycomplaints",$reportr->current_query)) {
        $find = $task->get_row("SELECT sqlstatement AS query, description FROM ".QUERY_TABLE . " WHERE MergeCode in ('" . $reportr->current_query . "','". $reportr->current_query. " ".$parse->params["reportr_flag"]. "') order by length(mergecode) desc limit 1");
    } elseif(isset($_GET["ebindr2"]))	$find = $task->get_row("SELECT sqlstatement AS query, description FROM ".QUERY_TABLE . " WHERE MergeCode in ('" . $reportr->current_query . "','e2." . $reportr->current_query . "') order by mergecode like 'e2.%' desc limit 1");
    else $find = $task->get_row("SELECT sqlstatement AS query, description FROM ".QUERY_TABLE . " WHERE MergeCode = '" . $reportr->current_query . "'");
    $iscustomcommon=(!eregi("[.]custom[.]", $reportr->current_query) && QUERY_DB=="common");
    if($task->num_rows < 1) { // we find a query with that name
        $iscustomcommon=false;
        if(isset($_GET["ebindr2"])) $find = $task->get_row("SELECT sqlstatement AS query, description FROM ".QUERY_DB.".".QUERY_TABLE . " WHERE MergeCode in ('" . $reportr->current_query . "','e2." . $reportr->current_query . "') order by mergecode like 'e2.%' desc limit 1");
        else $find = $task->get_row("SELECT sqlstatement AS query, description FROM ".QUERY_DB.".".QUERY_TABLE . " WHERE MergeCode = '" . $reportr->current_query . "'");
    }
}
if($task->num_rows == 1) { // we find a query with that name
    $device->define("current_query_desc", $task->get_var(null,1));
    $device->define("bid", "'".$_REQUEST["bid"]."'");
    $device->define("customcommon_message", ($iscustomcommon?"CUSTOM REPORT":""));

    if(!isset($_GET[NODEFAULT])) $parse->setreportdefaults();
    $parse->resolve($find["query"]);
    if(isset($_GET["USEDEFAULTS"])) {
        if(isset($_GET["USED_PARAMETERS"])) $parse->used_parameters=unserialize(base64_decode($_GET["USED_PARAMETERS"]));
        $parse->adopt(true); // we need some parameters
    }
    $parse->resolve($find["query"]);
    $task->get_row("SELECT mergecode FROM ".QUERY_DB.".reporthelp WHERE MergeCode = '" . $reportr->current_query . "'");
    if($task->num_rows==0) $task->get_row("SELECT mergecode FROM reporthelp WHERE MergeCode = '" . $reportr->current_query . "'");
    if($task->num_rows>0) $device->define("about_link", "Help");
    if($parse->advance() || isset($_GET["USEDEFAULTS"])) { // parameters are satisfied
        $parse->resolve_double($parse->output); // parse out query groups
        $reportr->control(); // looping control and template additions
    }
    else // prompt for parameters
        $parse->adopt(); // we need some parameters

    $results = array(
        'html' => $device->buffer($layout_template),
        'success' => true,
        'message' => 'Success'
    );
}else{
    $results = array(
        'error' => true,
        'message' => 'Error loading the mergecode'
    );
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode( $results );
?>
