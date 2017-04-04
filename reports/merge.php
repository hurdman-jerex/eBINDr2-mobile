<?php

$layout_template = "merge".$e2m;
/* REPORT MERGE */

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