<?php
/*REPORT IMPORTR*/

set_time_limit(3600);
include _MYBINDR;
$current_query = $variables[0];
$mybindr = new mybindr;
$mybindr->database=$_COOKIE["reportr_db"];
$mybindr->params = array_merge($mybindr->params, $parse->params);
if(list($queries, $setup) = $mybindr->getquery($current_query.".importr")) {
//                eval($mybindr->ResolvePipes($setup));
    eval(str_replace("|staff|", $mybindr->params["staff"], $setup));
    //echo $mybindr->ResolvePipes($setup);
    if(sizeof($_FILES)>0) {
        $errors = $mybindr->LoadData($tablename, $fieldnames, $_FILES[attachment][tmp_name], $ignorelines, $delimiter, $enclosure, $fixedwidth);
        if(ereg("HURDMAN",$mybindr->params["keys"]) && sizeof($mybindr->queriesrun)>0) $mybindr->mybindr_query("insert into reportquerylog (mergecode, day, staff, results) values ('".$reportr->current_query.".importr', now(), '".$_COOKIE["reportr_username"]."', '".addslashes(implode("\r\n",$mybindr->queriesrun))."')");
        $queries = $mybindr->querygroup($mybindr->ResolvePipes($queries));
        $mybindr->runqueries($queries["update"][0]);
        if(ereg("HURDMAN",$mybindr->params["keys"])) $mybindr->mybindr_query("insert into reportquerylog (mergecode, day, staff, results) values ('".$reportr->current_query.".importr', now(), '".$_COOKIE["reportr_username"]."', '".addslashes(implode("\r\n",$mybindr->queriesrun))."')");
        if(isset($_GET["reportr"])) {
            header("Location: ".stripslashes(stripslashes($_GET["reportr"])));
            exit();
        } elseif($_POST[referrer]>'') {
            header("Location: ".$_POST[referrer]);
            exit();
        }
    }
}
if($parse->advance()) {
    $device->define("content", "</form>".$importrmessage."
        <form enctype=\"multipart/form-data\" method=\"post\" action=\"\">
        <table border='1' cellpadding='2'>
                <tr><td nowrap>
                        <input type='hidden' name='referrer' value=\"".$_SERVER[HTTP_REFERER]."\">
                        <input type='file' name='attachment'>
                        <input type='submit' value='Import File'>
                </td></tr>
        </table>
        </form>
");
} else $parse->adopt(); // we need some parameters
echo $device->buffer($layout_template);