<?php

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
        if($_POST[STEP]==1) {
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
?>
