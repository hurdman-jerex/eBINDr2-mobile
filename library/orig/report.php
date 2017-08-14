<?
if(isset($_POST["ebindr2"])) $_GET["ebindr2"]="y";

if(trim($_COOKIE["reportr_keys"])=="" and ereg("^/report/.+",$_SERVER["REQUEST_URI"])) {
    setcookie("reportr_username", "", time()-28800, "/");
    unset($_COOKIE["reportr_username"]);
}
if(isset($_GET["printword"])) { $_GET["print"]="true"; ob_start(); }
/*Header("Cache-Control: no-store");
$offset = 60 * 60 * 24 * 3;
$ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", time() - $offset) . " GMT";
Header($ExpStr);*/
include "../includes/readme.php";

// Library
include _DATABASE;
include _TEMPLATE;
include _PARSER;
include _REPORTR;

include _CONFIG;

if(isset($_COOKIE["reportr_username"]) && !(strstr($_COOKIE["reportr_keys"],"a*") xor strstr($_COOKIE["reportr_keys"],"ak"))) {
    $find = $task->get_row("SELECT allowed FROM reportaccess WHERE allowed='n' and staff='".$_COOKIE["reportr_username"]."' and (MergeCode = '" . $reportr->current_query .(isset($_GET["editr"]) || isset($_POST[editr])?".editr":""). "' or '" . $reportr->current_query .(isset($_GET["editr"]) || isset($_POST[editr])?".editr":""). "' like concat(mergecode,'.%'))");
    if($task->num_rows > 0) {
        $find = $task->get_row("SELECT allowed FROM reportaccess WHERE allowed='y' and staff='".$_COOKIE["reportr_username"]."' and (mergecode like '" . $reportr->current_query . ".%" . (isset($_GET["editr"]) || isset($_POST[editr])?".editr":""). "' || mergecode = '" . $reportr->current_query .(isset($_GET["editr"]) || isset($_POST[editr])?".editr":""). "')");
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
$reportr->background->query("replace into ".LOCAL_DB.".reportlog (mergecode, staff, day, count)
select ifnull(mergecode,'".$reportr->current_query."'),
ifnull(staff,'".$_COOKIE["reportr_username"]."'), ifnull(day,curdate()), ifnull(count+1,1) from ".LOCAL_DB.".reportlog where
mergecode='".$reportr->current_query."' and
staff='".$_COOKIE["reportr_username"]."' and day=curdate() having count(*) in (0,1)");
$reportr->background->select(LOCAL_DB);
if(ereg("mycomplaints",$_SERVER["REQUEST_URI"]) && ($_COOKIE["reportr_flag"]=="" || $_COOKIE["reportr_flag"]==" "))
    $parse->params["reportr_flag"]=$reportr->background->get_var("select bid from staff where initials='".$_COOKIE["reportr_username"]."' union select bid from commonstaff
where initials='".$_COOKIE["reportr_username"]."' union select bid from mclogin where uid='".$_COOKIE["reportr_username"]."' limit 1");
// start reportr processing
if($variables[0] == 'auth') login(_prompt); // prompt to login to the system
if(isset($_COOKIE["reportr_username"]) && $reportr->filename=='report' && ereg("auth",$_SERVER["HTTP_REFERER"]) && ereg("^,[0-9]+,", $_COOKIE["reportr_keys"])) $device->define("iptracking", "<script>window.parent.open(\"http://www.bbbmetrics.com/setips.html?database=".LOCAL_DB."&bypass=jv53gh8029f43ig5j290\", \"setip\", \"status=no, scrollbars=yes, addressbar=none, toolbars=none, resizable=no, width=10, height=10\");</script>");
else $device->define("iptracking", "");

if($reportr->current_query!="report access" and (strstr($_COOKIE["reportr_keys"],"a*") xor strstr($_COOKIE["reportr_keys"],"ak"))) {
    $device->define("restriction_link", "<div style='display:block;position:absolute;left:0px;top:0px;'><a target=\"_blank\" href=\"/report/report access/?noheader&mymergecode=".$reportr->current_query.(isset($_GET["editr"]) || isset($_POST[editr])?".editr":"")."\"><img border=0 src=\"/images/key.gif\"></a></div>");
    if(eregi("^qvq ",$reportr->current_query) || ereg("(advertising|mypl|investigationslist|statusreview|relatedreports|button mycomplaints|special projects|lite button bg[.]sales)",$reportr->current_query)) $device->define("restriction_link_noheader", "<div style='display:block;position:absolute;left:0px;top:0px;'><a target=\"_blank\" href=\"/report/report access/?noheader&mymergecode=".$reportr->current_query.(isset($_GET["editr"]) || isset($_POST[editr])?".editr":"")."\"><img border=0 src=\"/images/key.gif\"></a></div>");
}

if(ereg("^/filebrowser", $_SERVER[PATH_INFO])) {
//        include _MYBINDR;
//        $reportr->mybindr = new mybindr;
//        $mybindr->database=$_COOKIE["reportr_db"];
//        $mybindr->params = array_merge($mybindr->params, $parse->params);
    require "/home/serv/library/browse.php";

} elseif(ereg("^/cibr/(.+)$", $_SERVER[PATH_INFO], $regs)) {
    $search=urldecode($regs[1]);
    if(ereg("^([^0-9]+)-([0-9]{5})$", $search, $regs)) {
        $search=array("category"=>$regs[1], "zip"=>$regs[2]);
    } elseif(isset($_GET["namesearch"])) {
        $search=array("name"=>$regs[1]);
    } elseif(ereg("^([-() .0-9]+)$", $search, $regs)) {
        $search=array("phone"=>ereg_replace("[^0-9]","",$regs[1]));
    } elseif(ereg("^(.+@.+[.].+)$", $search, $regs)) {
        $search=array("email"=>$regs[1]);
    } elseif(ereg("^(.+[.].+)$", $search, $regs)) {
        $search=array("url"=>$regs[1]);
    } elseif(ereg("^(.+)$", $search, $regs)) {
        $search=array("name"=>$regs[1]);
    } else {
        echo "Incorrect search parameters";
        exit();
    }
    require DIR_LIBRARY."cibr.php";
    $c = new cibr();
    $c->paramfile="/var/tmp/cibr_params";
    $c->noheader=isset($_GET["noheader"]);
    $c->InternalSearch($search);
    echo $c->results;
} elseif(ereg("^/calendar", $_SERVER[PATH_INFO]) && isset($_GET["ebindr2"])) {
    require "/home/serv/public_html/ebindr/views/calendar.html";
} elseif(ereg("^/calendar", $_SERVER[PATH_INFO])) {
    require "/home/serv/includes/calendar.php";
} elseif($variables[0] == 'api') $reportr->api(); // roll api call
elseif(isset($_GET["importr"])) {
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
} elseif(isset($_GET["editr"]) || isset($_POST[editr]))
{
    $device->variable("postinputs", "");
    $current_query = $variables[0];
    if(!empty($variables[2])) $current_query .= "." . $variables[2];
    $device->define("current_query", $current_query.".editr");
    $device->define("bid", "'".$_REQUEST["bid"]."'");
    include _MYBINDR;
    include _EDITR;

    $editr = new editr;

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


    //$parse->params=array_merge($parse->params, $editr->params);
//      $query = $editr->querygroup($query);
    $query = $editr->querygroup($parse->resolve_merge($query));
//        $query = $editr->querygroup($query);
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
                $reportr->background->query("replace into ".LOCAL_DB.".reportlog (mergecode, staff, day, count)
select ifnull(mergecode,'".$reportr->current_query.".editr'),
ifnull(staff,'".$_COOKIE["reportr_username"]."'), ifnull(day,curdate()), ifnull(count+1,1) from ".LOCAL_DB.".reportlog where
mergecode='".$reportr->current_query.".editr' and
staff='".$_COOKIE["reportr_username"]."' and day=curdate() having count(*) in (0,1)");
                $reportr->background->select(LOCAL_DB);
            }
        }
        for($i=0;$i<sizeof($query[update][1]);$i++) {
            $query[update][1][$i]=$parse->resolve($query[update][1][$i]);
            if(eregi("^use .+", $query[update][1][$i])) $editr->runqueries(array($query[update][1][$i]));
        }
//print_r($query);
        $editr->runqueries($query[update][1]);
        if(ereg("HURDMAN",$editr->params["keys"])) $editr->mybindr_query("insert into reportquerylog (mergecode, day, staff, results) values ('".$reportr->current_query.".editr', now(), '".$_COOKIE["reportr_username"]."', '".addslashes(implode("\r\n",$editr->queriesrun))."')");
        list($success_page)=mysql_fetch_row($editr->mybindr_query("select @success_page"));
        if($success_page>"") { header("Location: $success_page"); exit(); }
        elseif(ereg("^https*://([^/]+)/ebindr/*$", $editr->params[reportr])) {
            $device->define("content", "<script type='text/javascript'>function ChangesMade() { return ''; }; ( function() { window.parent.ebindr.growl( 'Saved', 'Changes have been saved' ); var iframeid = window.parent.ebindr.window.library.focusedWindow.options.id + '_iframe';window.parent.ebindr.button.toolbar.escape( window.parent.ebindr.window.library.focusedWindow, window.parent.ebindr.window.iframe(iframeid), false ); }).delay(100);</script>");
            echo $device->buffer($layout_template);
            exit();
        } else { header("Location: ".$editr->ResolvePipes(stripslashes(stripslashes($editr->params[reportr])))); exit(); }
        //exit();
    }
    if($sql=="") {
        //if($editr->params[reportr]=="") $editr->params[reportr]=$_SERVER["HTTP_REFERER"];
        if(ereg("^https*://([^/]+)/ebindr/*$", $editr->params[reportr])) {
            $device->define("content", "<script type='text/javascript'>function ChangesMade() { return ''; }; ( function() { window.parent.ebindr.growl( 'Saved', 'Changes have been saved' ); var iframeid = window.parent.ebindr.window.library.focusedWindow.options.id + '_iframe';window.parent.ebindr.button.toolbar.escape( window.parent.ebindr.window.library.focusedWindow, window.parent.ebindr.window.iframe(iframeid), false ); }).delay(100);</script>");
            echo $device->buffer($layout_template);
            exit();
        } else {
            header("Location: ".$editr->params[reportr]);
            exit();
        }
    }
    for($i=0;$i<sizeof($sql);$i++)
        $sql[$i]=$parse->resolve($sql[$i]);
    $editr->params = array_merge($editr->params, $params, $parse->params);
    $device->define("content", $editr->getform($sql, 0, $desc));
    echo $device->buffer($layout_template);
} elseif(ereg("^/open", $_SERVER[PATH_INFO])) {
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
} elseif(ereg("^/merge", $_SERVER[PATH_INFO])) {
    $title=$variables[0].(isset($variables[1])?",":"").$variables[1].(isset($variables[2])?",":"").$variables[2];
    $title=trim($title);
    $_GET[title]=$title;
    include _MYBINDR;
    $mybindr = new mybindr;
    $mybindr->params = array_merge($mybindr->params, $parse->params);
    $mybindr->database = $variables["db"];
    $mybindr->begincode="\[";
    $mybindr->endcode="\]";

    if(isset($_GET['WAIT_FOR_TEMP_FILE'])) {
        if(isset($_GET['DOWNLOAD_FILE'])) {
            header( "Content-Type: application/octet-stream" );
            if(eregi("rtf$", $title)) header('Content-Disposition: attachment; filename="'.$title.'"');
            $fp=fopen($_GET['WAIT_FOR_TEMP_FILE']."_fonttbl","r");
            $start=true;
            while($data=fread($fp,4000)) echo $data;
            fclose($fp);
            exit();
        }
        $device->define("heirarchy", $title);
        $device->define("temp_timeout", "yes");
        $device->define("temp_file_name", $_GET['WAIT_FOR_TEMP_FILE']);
        $device->define("merge_message", "The document you requested is being generated... please wait.");
        $parse->adopt(); // we need some parameters
        echo $device->buffer($layout_template);
        exit();
    }

    $mybindr->fixPost();
    $asks=$mybindr->GetAsks($title);

    if(sizeof($asks)>0) {
        $query="table,h,100,100,";
        foreach($asks as $ask) {
            $ask[1]=$mybindr->ResolvePipes($ask[1]);
            if(isset($mybindr->params[$ask[0]]))
                $mybindr->params[$ask[0]]=stripslashes($mybindr->params[$ask[0]]);
            else
                $query.="|$ask[0]:$ask[1]| ";
        }
    }

    $parse->resolve($query);
    $device->define("heirarchy", $title);
    if($parse->advance()) { // parameters are satisfied
        if( $_POST["mergesubmit"]=="YES" || isset($_GET["NOASK"]) ) {
            if($_GET["CONVERT_SCANS"]=="YES") $reportr->SetDocTable("cid", $mybindr->params["cid"]);
            if(isset($_GET["RUNQUERY"])) {
                list($myquery,) = $mybindr->getquery($_GET["RUNQUERY"]);
                $myquery = $mybindr->querygroup($parse->resolve_merge($myquery));
                for($i=0;$i<sizeof($myquery[update][0]);$i++) $myquery[update][0][$i]=$parse->resolve($myquery[update][0][$i]);
                $mybindr->runqueries($myquery[update][0]);
            }
            if(strpos($_SERVER[HTTP_USER_AGENT],"Windows NT 5.1")>0 && !eregi("scanr$", $title)) $attachornot='inline';
            else $attachornot='attachment';
            $attachornot="attachment";
            if(isset($_GET["EMAILRESULTS"])) {
                if(eregi("(pdf|rtf|txt|xls|csv|scanr|zip|wav)$", $title)) {
                    if( $_GET['google'] != 'yes' ) {
                        header( "Content-Type: application/octet-stream" );
                        header('Content-Disposition: '.$attachornot.'; filename="'.$title.'"');
                    }
                }
                $myresults=$mybindr->merge($title);
                if(isset($_GET["EMAILQUEUE"])) {
                    $emails=$mybindr->params["EMAIL_TO"].($mybindr->params["EMAIL_CC"]!=""?",".$mybindr->params["EMAIL_CC"]:"").($mybindr->params["EMAIL_BCC"]!=""?",".$mybindr->params["EMAIL_BCC"]:"");
                    $mybindr->mybindr_query("insert into emailqueue (day, emails, subject, message) values (now(), '".$emails."', '".addslashes($mybindr->params["EMAIL_SUBJECT"])."', '".addslashes($myresults)."')");
                } elseif (isset($_GET["NOPEAR"])) {
                    $mybindr->SendEmail($mybindr->params["EMAIL_TO"], $mybindr->params["EMAIL_TO_NAME"], $mybindr->params["EMAIL_FROM"], $mybindr->params["EMAIL_FROM_NAME"], $mybindr->params["EMAIL_CC"], $mybindr->params["EMAIL_BCC"], $mybindr->params["EMAIL_SUBJECT"], $myresults, true);
                } elseif(isset($_GET["ATTACHMENT_NAME"]))	{
                    $sentpear=$mybindr->SendEmailPearAttach($mybindr->params["EMAIL_TO"], $mybindr->params["EMAIL_TO_NAME"], $mybindr->params["EMAIL_FROM"], $mybindr->params["EMAIL_FROM_NAME"], $mybindr->params["EMAIL_CC"], $mybindr->params["EMAIL_BCC"], $mybindr->params["EMAIL_SUBJECT"], $myresults, $mybindr->params["ATTACHMENT_NAME"], $mybindr->params["ATTACHMENT_FILE"]);
                }	else {
                    $sentpear=$mybindr->SendEmailPear($mybindr->params["EMAIL_TO"], $mybindr->params["EMAIL_TO_NAME"], $mybindr->params["EMAIL_FROM"], $mybindr->params["EMAIL_FROM_NAME"], $mybindr->params["EMAIL_CC"], $mybindr->params["EMAIL_BCC"], $mybindr->params["EMAIL_SUBJECT"], $myresults, true, (isset($mybindr->params["EMAIL_REPLYTO"]) ? $mybindr->params["EMAIL_REPLYTO"] : null ));
                }
                if(isset($sentpear) && !$sentpear && isset($mybindr->params["EMAIL_FAILED"])) {
                    $parse->params["EMAIL_ERROR"]=$mybindr->params["EMAIL_ERROR"];
                    list($myquery,) = $mybindr->getquery($mybindr->params["EMAIL_FAILED"]);
                    $myquery = $mybindr->querygroup($parse->resolve_merge($myquery));
                    for($i=0;$i<sizeof($myquery[update][0]);$i++)	$myquery[update][0][$i]=$parse->resolve($myquery[update][0][$i]);
                    $mybindr->runqueries($myquery[update][0]);
                }
                echo "<b>The following email has been sent:</b><br><hr><br>";
                echo $myresults;
            } else {
                if(eregi("(pdf|txt|xls|csv|scanr|zip|wav)$", $title)) {
                    if( $_GET['google'] != 'yes' ) {
                        header( "Content-Type: application/octet-stream" );
                        header('Content-Disposition: '.$attachornot.'; filename="'.($_GET["ATTACHMENT_NAME"]?$_GET["ATTACHMENT_NAME"]:$title).'"');
                    }
                }
                if(isset($_GET[NOASK]) && $mybindr->bufferName=="") $mybindr->bufferName=tempnam("/var/tmp", "MERGE");
                if( in_array($_GET['google'], array('both','yes')) && in_array(substr($title,-3), array('rtf','xls','csv') ) ) {
                    include "/home/serv/public_html/ebindr/includes/googleapps.php";
                    $tmpalan = $mybindr->MergeNoBuffer($title);
                    googledrive::import( $title, $tmpalan );
                }

                if( in_array($_GET['alpdf'], array('both','yes')) && in_array(substr($title,-3), array('rtf','xls','csv') ) ) {
                    $tmpalan = $mybindr->MergeNoBuffer($title);
                    $altitle = $title;
                    include "/home/serv/library/rtf2htm/rtf2htm.php";
                    header("Content-type:application/pdf");
                    header('Content-Disposition:attachment;filename="'.$altitle.'.pdf"');
                    echo $mybindr->alpdf($altitle . ".html");
                    exit;
                }

                if( $_GET['google'] == 'yes' ) {
                    echo 'Saved to Google Apps';
                    exit;
                } else {
                    echo $mybindr->merge($title);
                }
                if($mybindr->bufferName>"") {

                    if(eregi("rtf$", $title)) {
                        if( $_GET['google'] != 'yes' ) {
                            header( "Content-Type: application/octet-stream" );
                            header('Content-Disposition: '.$attachornot.'; filename="'.$title.'"');
                        }
                        $mybindr->StopBuffering();
                    }

                    rename($mybindr->bufferName, $mybindr->bufferName."_done");
                    $fp=fopen($mybindr->bufferName."_done","r");
                    $fp2=fopen($mybindr->bufferName."_fonttbl","w");
                    $start=true;
                    while($data=fread($fp,4000)) {
                        if($start) {
                            fwrite($fp2,str_replace("___FIXED_COLOR_TABLE___", $mybindr->MakeColortbl(), str_replace("___FIXED_FONT_TABLE___", $mybindr->MakeFonttbl(), $data)));
                            $start=false;
                        } else {
                            fwrite($fp2,$data);
                        }
                    }
                    fclose($fp);
                    fclose($fp2);
                    unlink($mybindr->bufferName."_done");
                    // google apps export start
                    if( in_array($_GET['google'], array('both','yes')) && in_array(substr($title,-3), array('rtf','xls','csv') ) ) {
                        include "/home/serv/public_html/ebindr/includes/googleapps.php";
                        googledrive::import( $title, file_get_contents($mybindr->bufferName.( substr($title,-3) == 'rtf' ? "_fonttbl" : "" )) );
                    }

                    if( in_array($_GET['alpdf'], array('both','yes')) && in_array(substr($title,-3), array('rtf','xls','csv') ) ) {
                        $tmpalan = $mybindr->MergeNoBuffer($title);
                        $altitle = $title;
                        include "/home/serv/library/rtf2htm/rtf2htm.php";
                        header("Content-type:application/pdf");
                        header('Content-Disposition:attachment;filename="'.$altitle.'.pdf"');
                        echo $mybindr->alpdf($altitle . ".html");
                        exit;
                    }

                    if( $_GET['google'] == 'yes' ) {
                        echo 'Saved to Google Apps';
                        exit;
                    }

                    if( $_GET['google'] != 'yes' ) {
                        $fp=fopen($mybindr->bufferName."_fonttbl","r");
                        $start=true;
                        while($data=fread($fp,4000)) {
                            echo $data;
                        }
                        fclose($fp);
                    }
                }
            }
        } else {
            $device->define("temp_file_name", tempnam("/var/tmp", "MERGE"));
            $device->define("merge_message", "The document you requested is being generated... please wait.");
            $parse->adopt(); // we need some parameters
            echo $device->buffer($layout_template);
        }
    }  else {
        $device->define("temp_file_name", tempnam("/var/tmp", "MERGE"));
        $device->define("merge_message", "Please supply the following information:");
        $parse->adopt(); // we need some parameters
        echo $device->buffer($layout_template);
    }
} else {
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

        echo $device->buffer($layout_template);
    }
}
if(isset($_GET["printword"])) {
    header('Expires: ' . gmdate('D, d M Y H:i:s',time()-4800) . ' GMT');
    header('Content-Disposition: attachment; filename="wordtest.rtf"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    ob_end_flush();
//	ob_end_clean();
}

/*class googleapps {

	public function __construct( $returnurl = null ) {

		if( !function_exists('json_encode') ) include "/home/serv/library/json.php";
		include "/home/serv/library/google-api/Google_Client.php";
		include "/home/serv/library/google-api/contrib/Google_DriveService.php";
		include "/home/serv/library/google-api/contrib/Google_Oauth2Service.php";
		if( is_null($returnurl) ) $this->current_uri = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

		$this->client = new Google_Client();
		$this->client->setClientId('102612312942.apps.googleusercontent.com');
		$this->client->setClientSecret('DIS-kRl7NUyjC9yIWl4V7dCZ');
		$this->client->setRedirectUri('https://www.ebindr.com/oauth2callback');
		$this->client->setState(base64_encode(urlencode($this->current_uri)));
		$this->client->setScopes(array(
			'https://www.googleapis.com/auth/drive',
			'https://www.googleapis.com/auth/drive.file',
			'https://www.googleapis.com/auth/userinfo.profile',
			'https://www.googleapis.com/auth/userinfo.email'
		));

		$this->docs = new Google_DriveService($this->client);
		$this->user_info = new Google_Oauth2Service($this->client);
		$this->authUrl = $this->client->createAuthUrl();

	}

	public function authed( $bbbid, $initials ) {
		$tokens = json_decode(file_get_contents("http://hurdman.app.bbb.org/private-api/bbb/googleappsids/get/$bbbid/$initials/825389d7be28caf705475a763b6701aa"));
		if( is_null($tokens->access_token) || is_null($tokens->refresh_token) ) {
			return false;
		} else {
			$this->tokens = $tokens;
			$this->client->setAccessToken(json_encode($tokens));
			return $this->user_info->userinfo->get();
		}
	}

	public function auth( $bbbid, $initials ) {
		$this->bbbid = $bbbid;
		$this->initials = $initials;
		// if we have an access token then let's authenticate and save it
		if( isset($_GET["authcode"]) && strlen($_GET["authcode"]) > 0 ) {
			$accessToken = $this->client->authenticate(urldecode($_GET["authcode"]));
			$this->client->setAccessToken($accessToken);
			// now let's save to the api
			$this->save_auth(json_decode($accessToken));
			$tokens = json_decode($accessToken);
		} else {
			date_default_timezone_set('America/Denver');
			// get the info from the service api
			$tokens = json_decode(file_get_contents("http://hurdman.app.bbb.org/private-api/bbb/googleappsids/get/$bbbid/$initials/825389d7be28caf705475a763b6701aa"));

		}

		if( is_null($tokens->access_token) || is_null($tokens->refresh_token) ) {
			return false;
		} else {
			$this->access_token = $tokens->access_token;
			$this->refresh_token = $tokens->refresh_token;

			$this->client->setAccessToken(json_encode($tokens));
			return $this->user_info->userinfo->get();
		}

	}

	public function save_auth( $tokens ) {

		$user = $this->user_info->userinfo->get();

		$fields = array(
			"googleid" => $user['id'],
			"email" => $user['email'],
			"given_name" => $user['given_name'],
			"family_name" => $user['family_name'],
			"gender" => $user['gender'],
			"birthday" => $user['birthday'],
			"locale" => $user['locale'],
			"access_token" => $tokens->access_token,
			"refresh_token" => $tokens->refresh_token
		);

		foreach( $fields as $key => $value ) $_f .= $key ."=" . urlencode($value) . "&";
		rtrim($_f, '&' );

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, 'http://hurdman.app.bbb.org/private-api/bbb/googleappsids/store/'.$this->bbbid.'/'.$this->initials.'/825389d7be28caf705475a763b6701aa');
		curl_setopt($ch,CURLOPT_POST, count($_f));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $_f);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);

	}

	public function login() {
		//echo 'redirect: '. $this->authUrl;
		header( "Location: " . $this->authUrl );
		exit;
	}

	public function savedrive( $hash, $filename ) {
		echo file_get_contents( "http://hurdman.app.bbb.org/private-api/bbb/googleappsids/savedrive/".$this->bbbid."/".$this->initials."/".urlencode($filename)."/".$hash );
	}


	public function transferdrive( $filename, $filedata, $contenttype = 'application/pdf', $convert = false ) {
		$file = new Google_DriveFile();
		$file->setTitle($filename);
		$file->setDescription($filename);
		$file->setMimeType($contenttype);

		$createdFile = $this->docs->files->insert($file, array(
      		'data' => $filedata,
      		'mimeType' => $contenttype,
      		'convert' => ( $convert ? true : false )
    	));
	}
}*/

?>
