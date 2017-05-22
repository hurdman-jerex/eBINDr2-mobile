<?php

include _PARSER;

if(!class_exists('mobileParse'))
{
    class mobileParse extends parse {

        public function __construct()
        {
            $this->parse();
        }

        /**
         * @return void
         * @desc prompts for post filling of parameters
         */
        function adopt($setdefaultparms=false)
        {
            global $device;
            $device->define("prompt", "Yes");
            // template handler (own instance)
            $display = new mobileDisplay(array("table", "prompt", "prompt_selector"));
            $this->html_prompt = '<table border="0">' . "\n";
//			if($setdefaultparms && sizeof($this->orphan)==0) die("no orphans!");
            for($i=0; $i<sizeof($this->orphan); $i++)
            {
//				echo $this->orphan[$i]."<br>\r\n";
                $defaultvalue=""; unset($selectoptions);
                if($this->instructions[$this->orphan[$i]]!="") {
                    $display->variable("instructions", "<tr><td colspan=3>".$this->instructions[$this->orphan[$i]]."</td></tr>");
                } else $display->variable("instructions", "");
                if(empty($this->pipe_options[$this->orphan[$i]])) $defaults = '';
                elseif(sizeof($this->pipe_options[$this->orphan[$i]]) == 1 && !eregi("^\*", $this->orphan[$i]))
                {
                    $defaults = '';
                    $value[$i] = $this->pipe_options[$this->orphan[$i]][0];
                }
                else
                {
                    $value[$i] = array($this->pipe_options[$this->orphan[$i]][0][1], (empty($this->pipe_options[$this->orphan[$i]][0][0]) ? $this->pipe_options[$this->orphan[$i]][0][1]:$this->pipe_options[$this->orphan[$i]][0][0]));
                    $display->variable("element", ($i+1));
                    if(sizeof($this->pipe_options[$this->orphan[$i]])>99) $display->variable("selectsize",8); else $display->variable("selectsize",5);
                    for($l=0; $l<sizeof($this->pipe_options[$this->orphan[$i]]); $l++) {
                        if(!isset($this->pipe_options[$this->orphan[$i]][$l][0])) {
                            $defaults .= "<option ".($this->pipe_options[$this->orphan[$i]][$l][2]=="selected"?'selected':'')." value=\"" . $this->pipe_options[$this->orphan[$i]][$l][1] . "\">" . $this->pipe_options[$this->orphan[$i]][$l][1]. "</option>";
                            if($defaultvalue=="") $defaultvalue=$this->pipe_options[$this->orphan[$i]][$l][1];
                            $selectoptions[]=$this->pipe_options[$this->orphan[$i]][$l][1];
                        } else {
                            $defaults .= "<option ".($this->pipe_options[$this->orphan[$i]][$l][2]=="selected"?'selected':'')." value=\"" . $this->pipe_options[$this->orphan[$i]][$l][0] . "\">" . $this->pipe_options[$this->orphan[$i]][$l][1] . "</option>";
                            if($defaultvalue=="") $defaultvalue=$this->pipe_options[$this->orphan[$i]][$l][0];
                            $selectoptions[]=$this->pipe_options[$this->orphan[$i]][$l][0];
                        }
                    }
                    $display->variable("defaults", $defaults);
                }
                if(eregi("(^| )date",$this->orphan[$i])) {
                    if(COUNTRY=="CANADA") $display->define("country_var","&country=Canada");
                    $display->templates["prompt"]="prompt_date".(isset($_GET["ebindr2"])?"2":"").".php";
                    $display->addtemplate("prompt_layout", "prompt_date".(isset($_GET["ebindr2"])?"2":"").".php");
                } elseif((strtoupper($this->orphan[$i])!=$this->orphan[$i] && sizeof($this->pipe_options[$this->orphan[$i]])>1) || eregi("^\*", $this->orphan[$i])) {
                    $display->templates["prompt"]="prompt_hidden.php";
                    $display->addtemplate("prompt_layout", "prompt_hidden.php");
                } elseif(eregi("^\@", $this->orphan[$i])) {
                    $display->templates["prompt"]="prompt_big.php";
                    $display->addtemplate("prompt_layout", "prompt_big.php");
                } else {
                    $display->templates["prompt"]="prompt.php";
                    $display->addtemplate("prompt_layout", "prompt.php");
                }
                if(eregi("^\*", $this->orphan[$i])) {
                    $display->templates["prompt_selector"]="prompt_selector_multi.php";
                    $display->addtemplate("prompt_selector_layout", "prompt_selector_multi.php");
                    if(!$dontchangemsg) $display->variable("selection_message", "*Pressing CTRL while clicking in the list will allow you to select multiple items.");
                } else {
                    $display->templates["prompt_selector"]="prompt_selector.php";
                    $display->addtemplate("prompt_selector_layout", "prompt_selector.php");
                }
                if(is_array($this->pipe_options[$this->orphan[$i]][0]) && in_array("ALL*",$this->pipe_options[$this->orphan[$i]][0])) {
                    $display->variable("selection_message", "*Pressing CTRL while clicking in the list will allow you to select multiple items. If you choose the item ALL*, any additional items selected will NOT be included.");
                    $dontchangemsg=true;
                }

                // validate the entered data to make sure its correctly entered
                if(VALIDATE_ENTERED_DATA == 1) {
                    if($this->pipe_validate[$this->orphan[$i]] == $this->orphan[$i]) {
                        // javascript validation
                        $on_blur = "CompareDate(format_date('" . $value[$i][1] . "'), format_date(document.getElementById('prompt_value_" . ($i+1) . "').value), 'adoptee');";
                        $do_validate = true;
                    }
                }

                // get the previous entered variables if there are any.
                if(STORE_ENTERED_VARS == 1) {
                    $previous_vars = read_tmp_file();
                    // if it finds a previous entered variable with the same name as the orphan,
                    // set the temp value for the text box
                    if(!$setdefaultparms && $value[$i][1]=="" && !ereg("^\*",$this->orphan[$i]) && !empty($previous_vars[str_replace(" ", "_", $this->orphan[$i])]))
                        $temp_value = $previous_vars[str_replace(" ", "_", $this->orphan[$i])];
                    // otherwise just set it to the value of the pipe defaults if there are any
                    // set in the query.
                    else
                        $temp_value = $value[$i][1];
                }

                $display->variable("on_blur", $on_blur);
                if(eregi("date",$this->orphan[$i]) && $temp_value!="") {
                    if(isset($_GET["ebindr2"])) $display->variable("date_value",date("m/d/Y", strtotime($temp_value)));
                    else $display->variable("date_value",date("d-M-Y", strtotime($temp_value)));
                    $defaultvalue=date("Y-m-d", strtotime($temp_value));
                }

                if(!eregi("^\*", $this->orphan[$i])) {
                    $display->variable("value", $temp_value);
                    if($defaultvalue=="") $defaultvalue=$temp_value;
                } else {
                    $display->variable("value", "'".$temp_value."'");
//					if($defaultvalue=="") $defaultvalue="'".$temp_value."'";
                    if($defaultvalue=="ALL*") $defaultvalue="'***ALLSELECTED***','".implode("','", $selectoptions)."'";
                    elseif($defaultvalue=="ALL") $defaultvalue="'".$defaultvalue."'";
                    else $defaultvalue="'".$temp_value."'";
                }
                $display->variable("id", "prompt_value_" . ($i+1));
                $display->variable("title", $this->orphan[$i]);
                $display->variable("name", $this->orphan[$i]);
                if(!empty($defaults))
                    $display->variable("default_options", $display->buffer("prompt_selector"));
                else
                    $display->variable("default_options", "");

                if($setdefaultparms && $defaultvalue=="" && isset($this->used_parameters[$this->orphan[$i]])) $this->params[$this->orphan[$i]]=$this->used_parameters[$this->orphan[$i]];
                elseif($setdefaultparms) $this->params[$this->orphan[$i]]=$defaultvalue;


                $this->html_prompt .= $display->buffer("prompt");
                $defaults = '';
                $on_blur = '';
            }

            if(!$setdefaultparms) {
                $this->html_prompt .= '</table>';
                $display->variable("data", $this->html_prompt);
                $this->html_prompt = $display->buffer("table");

                $device->define("content", $this->html_prompt);
                $device->define("head_desc", "Please enter the required information.");

                if($do_validate) $device->define("submit", '<br /><input id="adoptee" type="submit" value="Continue" disabled></form>');
                else $device->define("submit", '<br /><script>var submitted=false;</script><input id="adoptee" type="submit" value="Continue" onclick="if(submitted) { alert(\'Your request has already been submitted... please wait.\'); this.disabled=true; return false; }; submitted=true;"></form>');
            }// else print_r($this->params);
            return true;
        }

        /**
         * @return string
         * @param mytext string
         * @desc splits into multiple queries if there is a double bar
         */
        function resolve_double($mytext)
        {
            global $reportr, $device;

            $mytext = html_entities($mytext);
            if(isset($_GET["ebindr2"])) $query["params"][] = array("ebindr2","y");
            $loop=0;
            $dontdo=array();//false;
            $starttime_tot=explode(" ",microtime());
            list($lastrun,$lastpid,$lastrecent)=explode(",",$reportr->background->get_var("select concat(howlongago(day,now()),',',id,',',day>now()-interval 10 minute) from processlist where mergecode='".$reportr->current_query."' and userid='".$_COOKIE['reportr_username']."' and mergecode not like 'auth%' and mergecode not like 'exportr%' and mergecode not like 'Process Complaints%' and mergecode not like 'menu.sales.Hot%' order by day desc limit 1"));
            if($lastrun>"" && !eregi("^menu.admin.night",$reportr->current_query)) {
                $reportr->background->query("kill $lastpid");
                $reportr->background->query("delete from processlist where id=$lastpid", OBJECT, false);
                if($lastrecent && eregi("^menu",$reportr->current_query)) echo "You ran this report $lastrun ago and did not let it complete. The previous request has been cancelled.";
            }
            $reportr->background->query("insert into reportlogtime (mergecode, day, staff) values ('".$reportr->current_query."', now(), '".$_COOKIE['reportr_username']."')", OBJECT, false);
            $this->reportlogid=$reportr->background->get_var("select last_insert_id()");
            if(!ereg("^(auth|exportr)",$reportr->current_query) && !isset($_GET[SHOWGRAPHNUM])) $reportr->background->query("insert into processlist(mergecode, day, id, userid) values ('".$reportr->current_query."', now(), connection_id(), '".$_COOKIE['reportr_username']."')", OBJECT, false);
            if(strpos($mytext, "||"))
            {
                $mytext = explode("||", $mytext);
                $logbin=true;
                for($i=0; $i<sizeof($mytext); $i++)
                {
                    $mytext[$i] = preg_replace("'([\r\n])+'", " ", trim($mytext[$i], "\r\n")); // get rid of carriage returns and line breaks
                    if(eregi("^use database (.*)", $mytext[$i], $regs))
                    {
                        $reportr->use_database_list = explode(",",str_replace("'","",$regs[1]));
                    }
                    elseif(ereg("^GoogleAnalyticsImport ([^ ]+) (.*)", $mytext[$i], $regs)) {
                        $_method = $regs[1];
                        $_query = $this->resolve_pipes($regs[2]);

                        // run the query to find the start and end dates
                        $reportr->background->query( $_query );
                        if( sizeof( $reportr->background->last_result)<1 ) {
                            // no result from the query
                            $this->update_errors .= "<!--\r\nGoogleAnalyticsImport: $_method $_query\r\nRESULT: 0 rows\r\n-->";
                        } else {
                            // look at the first result and get the end and start values for our range
                            foreach( $reportr->background->last_result[0] as $key => $value ) {
                                if(strtolower($key) == 'start' ) $start = $value;
                                if(strtolower($key) == 'end' ) $end = $value;
                            }
                            if( in_array(str_ireplace( "Council", "", $_method ), array('siteWideMonthly','directorySource') ) ) {
                                $tmp = $this->googleAnalyticsImport( $_method, $start, $end );
                                $rows += $tmp;
                            } else {
                                // loop through and import data according to the method and date range
                                $rows = 0;
                                $date = $start;
                                while( strtotime($date) <= strtotime($end) ) {
                                    $tmp = $this->googleAnalyticsImport( $_method, $date );
                                    //if( $tmp == 'nomethod' ) {
                                    //	$this->update_errors .= "<!--\r\nGoogleAnalyticsImport::$_method doesn't exist.\r\n-->";
                                    //	break;
                                    $rows += $tmp;
                                    $date = date("Y-m-d", strtotime($date . "+1 day" ) );
                                }
                            }
                            // show the results in a comment
                            $this->update_errors .= "<!--\r\nGoogleAnalyticsImport: $_method $_query\r\nDATE RANGE: $start to $end\r\nROWS: $rows\r\n-->";
                        }
                    }
                    elseif(ereg("^ENDDOIF", $mytext[$i], $regs)){
                        unset($dontdo[sizeof($dontdo)-1]); //$dontdo=false;
                        $this->update_errors.="<!--\r\nENDDOIF: ".print_r($dontdo,true)."\r\n-->";
                    }
                    elseif($dontdo[sizeof($dontdo)-1] && ereg("^DOIF (.*)", $mytext[$i], $regs)) {
                        $dontdo[sizeof($dontdo)]=true;
                        $this->update_errors.="<!-- SKIPPING DOIF QUERY: ".$mytext[$i]." -->\r\n";
                        continue;
                    }
                    elseif($dontdo[sizeof($dontdo)-1]) {
                        $this->update_errors.="<!-- SKIPPING QUERY: ".$mytext[$i]." -->\r\n";
                        continue;
                    }
                    elseif(ereg("^LOG QUERIES IF (.*)", $mytext[$i], $regs)) {
                        $doifquery=$this->resolve_pipes("select ".$regs[1]);
                        $doifquery=str_replace("row_count()", $lastaffectedrows, $doifquery);
                        $doifval=$reportr->background->get_var($doifquery);
                        if($doifval) $this->logqueries=true;
                    }
                    elseif(ereg("^DOIF (.*)", $mytext[$i], $regs)) {
                        $doifquery=$this->resolve_pipes("select ".$regs[1]);
                        $doifquery=str_replace("row_count()", $lastaffectedrows, $doifquery);
                        $doifval=$reportr->background->get_var($doifquery);
                        if(!($doifval>0)) $dontdo[sizeof($dontdo)]=true; else $dontdo[sizeof($dontdo)]=false;
                        /* if(ereg(",HURDMAN,",$this->params["keys"])) */$this->update_errors.="<!--\r\nDOIF: $doifquery\r\nRESULT: $doifval\r\n".print_r($dontdo,true)."\r\n-->";
                    }
                    elseif(ereg("^ENDDOWHILE", $mytext[$i], $regs)) {
                        $whilevar=$reportr->background->get_var($this->resolve_pipes(str_replace("row_count()",$lastaffectedrows,"select ".$dowhile["condition"])));
                        if($whilevar && $dowhile["iterations"]<100) {
                            $i=$dowhile["startpos"];
                            $dowhile["iterations"]++;
                            /* if(ereg(",HURDMAN,",$this->params["keys"])) */$this->update_errors.="<!--\r\n ***************** Done Iteration: #$dowhile[iterations], \"".$dowhile["condition"]."\"=\"$whilevar\"  ******************\r\n-->";
                        } else {
                            /* if(ereg(",HURDMAN,",$this->params["keys"])) */$this->update_errors.="<!--\r\n ***************** ENDDOWHILE: ".$dowhile["condition"]."=$whilevar - ".($dowhile["iterations"]+1)." iteration(s) ******************\r\n-->";
                        }
                    }
                    elseif(ereg("^DOWHILE (.*)", $mytext[$i], $regs)) {
                        $dowhile=array("condition"=>$regs[1], "startpos"=>$i, "iterations"=>0);
                        /* if(ereg(",HURDMAN,",$this->params["keys"])) */$this->update_errors.="<!--\r\n **********************  Begin DOWHILE  ***********************\r\n-->";
                    }
                    elseif(eregi("^use (.*)", $mytext[$i], $regs))
                        $reportr->background->select($regs[1]);
                    elseif(eregi("^IMAGEDATA:(.*)", $mytext[$i], $regs)) {
                        header("Content-type: image/unknown");
//						echo $this->resolve_pipes($regs[1]);
                        echo $reportr->background->get_var($this->resolve_pipes($regs[1]));
                        exit();
                    } elseif(eregi("^table", $mytext[$i], $regs))
                    {
                        $loop++;
                        $query[$loop]["options"] = explode(",", eregi_replace("table,", "", $mytext[$i]), 4);
                        if(strtoupper($regs[0])==$regs[0]) {
                            $query[$loop]["graphoptions"]=explode(",", eregi_replace("TABLE,", "", $mytext[$i]), 4);
                            $query[$loop]["graphoptions"][0]='v';
                            $query[$loop]["graphoptions"][1]=600;
                            $query[$loop]["graphoptions"][2]=250;
                        }
                    }
                    /* Datatables */
                    elseif(eregi("^datatable", $mytext[$i], $regs))
                    {
                        $loop++;
                        $query[$loop]["options"] = explode(",", eregi_replace("datatable,", "", $mytext[$i]), 4);
                        $query[$loop]["options"][4] = 'init_datatables';
                    }
                    elseif(eregi("^#", $mytext[$i])) $this->update_errors.="\r\n<!-- ".$mytext[$i]." -->\r\n";
                    elseif(eregi("^graph", $mytext[$i]))
                    {
                        $loop++;
                        $query[$loop]["options"] = explode(",", eregi_replace("graph,", "", $mytext[$i]), 4);
                        $query[$loop]["graph"]=true;
                    }
                    elseif(eregi("^OTHERSERVER:([^:]*):([^:]*):([^:]*)", $mytext[$i], $regs)) {
                        $this->params["OTHERHOST"]=$regs[1];
                        $this->params["OTHERUSER"]=$regs[2];
                        $this->params["OTHERPASS"]=$regs[3];
                    }
                    elseif(ereg("^GAIMPORTMOBILE (.*)", $mytext[$i], $regs)) {
                        $importquery = $this->resolve_pipes($regs[1]);
                        $reportr->background->query( $importquery );
                        if(sizeof($reportr->background->last_result)<1) {
                            $this->update_errors.="<!--\r\nGAIMPORTMOBILE: $importquery\r\nRESULT: 0 rows\r\n-->";
                        } else {
                            foreach($reportr->background->last_result[0] as $key=>$value) {
                                if(strtolower($key)=="start") $start=$value;
                                if(strtolower($key)=="end") $end=$value;
                            }
                            $rows=0;
                            $date=$start;
                            while(strtotime($date)<=strtotime($end)) {
                                $rows+=$this->GAImportMobile($date);
                                $date=date("Y-m-d", strtotime($date."+1 day") );
                            }
                            $this->update_errors.="<!--\r\nGAIMPORTMOBILE: $importquery\r\nDATE RANGE: $start to $end\r\nROWS: $rows\r\n-->";
                        }
                    }
                    elseif(ereg("^PHP (.*)", $mytext[$i], $regs)) {
                        $this->update_errors.="<!-- ".$this->resolve_pipes($regs[1])." -->";
                        eval($this->resolve_pipes($regs[1]));
                    }
                    elseif(ereg("^APIDATA (.*)", $mytext[$i], $regs)) {
                        $apiquery = $this->resolve_pipes($regs[1]);
                        $reportr->background->query( $apiquery );
                        if(sizeof($reportr->background->last_result)<1) {
                            $this->update_errors.="<!--\r\nAPIDATA: $apiquery\r\nRESULT: 0 rows\r\n-->";
                        } else {
                            foreach($reportr->background->last_result[0] as $key=>$value) {
                                $urlparams=$value;
                            }
                            include_once(dirname(__FILE__)."/cbbbapi.class.php");
                            $bbbapi = new cbbbapi();
                            $check=json_encode($bbbapi->search($urlparams));
                            $apiquery2 = "SET @APIDATA:='".addslashes($check)."'";
                            $reportr->background->query($apiquery2);
                            $this->update_errors.="<!--\r\nAPIDATA: $apiquery\r\nURLPARAMS:$urlparams\r\nJSON QUERY: $apiquery2\r\n-->";
                        }
                    }
                    elseif(ereg("^GAIMPORT (.*)", $mytext[$i], $regs)) {
                        $importquery = $this->resolve_pipes($regs[1]);
                        $reportr->background->query( $importquery );
                        if(sizeof($reportr->background->last_result)<1) {
                            $this->update_errors.="<!--\r\nGAIMPORT: $importquery\r\nRESULT: 0 rows\r\n-->";
                        } else {
                            foreach($reportr->background->last_result[0] as $key=>$value) {
                                if(strtolower($key)=="start") $start=$value;
                                if(strtolower($key)=="end") $end=$value;
                            }
                            $rows=0;
                            $date=$start;
                            while(strtotime($date)<=strtotime($end)) {
                                $rows+=$this->GAImport($date);
                                $date=date("Y-m-d", strtotime($date."+1 day") );
                            }
                            $this->update_errors.="<!--\r\nGAIMPORT: $importquery\r\nDATE RANGE: $start to $end\r\nROWS: $rows\r\n-->";
                        }
                    }
                    elseif(ereg("^CIBR SEARCH (.+)$", $mytext[$i], $regs)) {
                        $thistime=time();
                        $starttime=explode(" ",microtime());
                        $myquery=$this->resolve_pipes($regs[1]);
                        $reportr->background->query($myquery, ARRAY_N);
                        foreach($reportr->background->last_result as $row) {
                            $row=array_values($row);
                            list($thisbid, $search)=$row;
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
                                echo "<!-- Incorrect search parameters: ".print_r($row,true)."/$myquery/$search -->";
                            }
                            $thissearch=array_keys($search);
                            $thissearch=$thissearch[0];
                            include_once(DIR_LIBRARY."cibr.php");
                            $c = new cibr();
                            $c->harvestdata=true;
                            $c->paramfile="/var/tmp/cibr_params";
                            $c->noheader=true;
                            $c->InternalSearch($search);
                            $reportr->background->query($cibrqueries[]="create temporary table if not exists cibr_search_results (BID int not null, BBBID char(6), SearchType char(20), ReportURL char(255), DBA char(150), Street char(60), City char(50), State char(3), PostalCode char(10), Distance char(10), HQ char, AB tinyint, typeofbusiness char(255), key(bid))");
                            foreach($c->records as $record) {
                                $reportr->background->query($cibrqueries[]="insert into cibr_search_results (BID, bbbid, SearchType, ReportURL, DBA, Street, City, State, PostalCode, Distance, hq, ab, typeofbusiness) values ($thisbid, '".$record["bbbid"]."', '".$thissearch."', '".addslashes($record["reporturl"])."', '".addslashes($record["dba"])."', '".addslashes($record["street"])."', '".addslashes($record["city"])."', '".$record["state"]."', '".$record["postalcode"]."', '".$record["miles"]."', '".$record["hq"]."', '".$record["ab"]."', '".addslashes($record["tob"])."')");
                            }

                        }
                        $endtime=explode(" ",microtime());
                        $timetook=number_format($endtime[0]+$endtime[1]-$starttime[0]-$starttime[1],3);
                        /* if(ereg(",HURDMAN,",$this->params["keys"])) */$this->update_errors.="<!--\r\n".print_r($cibrqueries,true)."\r\nTIME: ".$timetook." seconds\r\n-->";
                        unset($c, $cibrqueries);
                    }
                    elseif(eregi("^assigncomplaint.+", $mytext[$i]))
                    {
                        include_once(_MYBINDR);
                        $mybindr=new mybindr();
                        $mybindr->runqueries(array($mytext[$i]));
                    }
                    elseif(eregi("^setdoctable", $mytext[$i]))
                    {
                        $reportr->SetDocTable("cid", $this->params["cid"]);
                    }
                    elseif(eregi("^JSONDECODE (.+)", $mytext[$i], $regs ) ) {
                        $this->update_errors.="<!--\r\nQUERY: $regs[1]\r\n";
                        if(eregi("^select", $regs[1])) $url=$reportr->background->get_var($regs[1]);
                        else $url=$regs[1];
                        $this->update_errors.="URL: $url\r\n";
                        if(eregi("^http", $regs[1])) $json=json_decode(file_get_contents($url)); else $json=json_decode($regs);
                        if(is_array($json)) {
                            foreach($json as $oneson) {
                                list($creates[], $inserts[]) = $this->ObjToMySQL($oneson);
                            }
                        } else list($creates, $inserts) = $this->ObjToMySQL($json);
                        $queriestorun=array_merge($creates,$inserts);
                        die(print_r($queriestorun, true));
                        foreach($queriestorun as $onequery) {
                            $reportr->background->query($onequery);
                            $this->update_errors.="QUERY: $onequery\r\n";
                        }
                        $this->update_errors.="-->";
                    }
                    elseif(eregi("^JSON-I (.+)", $mytext[$i], $regs ) ) {
                        if( !function_exists('json_decode') ) include "/home/serv/library/json.php";
                        //echo 'query: '. $regs[1];
                        $reportr->background->query( $regs[1] );
                        //print_r($reportr->background->last_result);
                        $url = $reportr->background->last_result[0]['url'];
                        $response = json_decode(file_get_contents($url));
                        // get the keys (field names)
                        $mfields = array();
                        foreach( $response as $i => $row ) {
                            foreach( $row as $key => $value ) {
                                if( $mfields[$key]['type'] != 'text' ) $mfields[$key]['type'] = ( !is_numeric($value) ? 'text' : 'int' );
                                if( preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $value) ) $mfields[$key]['type'] = 'date';
                                if( strlen($value) > $mfields[$key]['length'] ) $mfields[$key]['length'] = strlen($value);
                            }
                        }

                        // make a create table statement
                        $mquery = "create temporary table myjsoni (";

                        foreach( $mfields as $name => $options ) {
                            $type = $options['type'];
                            $len = $options['length'];
                            $mquery .= $name . " ";
                            if( $type == 'text' ) {
                                if( $len < 255 ) $mquery .= "varchar($len)";
                                else $mquery .= "text ";
                            } else {
                                $mquery .= $type;
                            }
                            $mquery .= ", ";
                        }

                        // finalize the query statement
                        $mquery .= ")";
                        $mquery = str_replace( ", )", ")", $mquery );

                        // now we need to insert data
                        foreach( $response as $i => $row ) {
                            $ins[$i] = "insert into myjsoni values (";
                            foreach( $row as $key => $value ) {
                                $ins[$i] .= "'" . $value . "', ";
                            }
                            $ins[$i] .= ")";
                            $ins[$i] = str_replace( ", )", ")", $ins[$i] );
                        }

                        // create the query
                        $reportr->background->query( $mquery );
                        // insert the data
                        foreach( $ins as $i => $sq ) $reportr->background->query( $sq );
                    }
                    elseif(eregi("^G-ANALYTICS (.+)", $mytext[$i], $regs ) ) {
                        $_parameters = array();
                        // lets get the google analytics information
                        $reportr->background->query( "select id, profile from common.ganalyticsid where bbbid = if(setup(318)='9999','1166',setup(318)) limit 1" );
                        $regs[1] = $regs[1] . ";id:" . $reportr->background->last_result[0]["id"] . ";profile:" . $reportr->background->last_result[0]["profile"];
                        foreach( explode(";", $regs[1] ) as $p ) {
                            list( $name, $value ) = explode( ":", $p );
                            $_parameters[urlencode($name)] = urlencode($value);
                            $urlparams[] = urlencode($name) . "=" . urlencode($value);
                        }
                        $url = "http://" . LOCAL_HOST . "/ebindr/g-analytics.php?" . implode( "&", $urlparams );
                        $results = unserialize(file_get_contents( $url ) );
                        $this->update_errors.="<!--\r\n".$mytext[$i]."\r\nURL:$url\r\n-->";
                        // create the temporary table
                        $reportr->background->query( "create temporary table ganalytics ( path varchar(200), pageviews int not null, uniquepageviews int not null, avgtimeonpage char(8), entrances int not null, bouncerate int not null, percentexit int not null, pagevalue int not null, primary key(path) )" );

                        // go through the results and populate the table
                        if( sizeof($results) > 0 && is_array($results) ) {
                            foreach( $results as $row ) {
                                $reportr->background->query( "insert into ganalytics ( path, pageviews, uniquepageviews, avgtimeonpage, entrances, bouncerate, percentexit, pagevalue ) values ( '" . $row['path'] . "', '" . $row['pageviews'] ."', '" . $row['uniquepageviews'] . "', '" . $row['avgtimeonsite'] . "', '" . $row['entrances'] . "', '" . $row['visitbouncerate'] . "', '" . $row['exitrate'] . "', '" . $row['pagevalue'] . "' )" );
                            }
                        }

                        //$reportr->background->query("select @test2:='".$regs[1]."'");
                    }
                    elseif(eregi("^GEOCODE (.+)", $mytext[$i], $regs))
                    {
                        $geoquery="";

                        $url="http://maps.google.com/maps/api/geocode/xml?sensor=false&address=".urlencode(trim($regs[1],"%"));
                        $ch = curl_init();
                        $agent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 2.0.50727; OfficeLiveConnector.1.3; OfficeLivePatch.0.0)";
                        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_URL, $url);
                        $buffer=str_replace(array(chr(10),chr(13)),"",curl_exec($ch));
                        curl_close($ch);

                        if(strpos($buffer, "OVER_QUERY_LIMIT")!==false) {
                            $reportr->background->query("Select * from branch where main = 'y'");
                            $geobranching = $reportr->background->last_result[0];
                            $url="http://dev.virtualearth.net/REST/v1/Locations?CountryRegion=US&adminDistrict=" . $geobranching['stateprov'] . "&postalCode=" . $geobranching['postalcode'] . "&addressLine=" . urlencode(trim($regs[1],"%")) . "&o=xml&key=AqU4sFWO5PRPEg_r_ig3glk6dvttjksSfPnYT2rRkFe-2SXaJNnyKErFtFPGL_gO";
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_USERAGENT, $agent);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($ch, CURLOPT_URL, $url);
                            $buffer=str_replace(array(chr(10),chr(13)),"",curl_exec($ch));
                            curl_close($ch);
                            if(ereg("<Point>[^<>]*<Latitude>([^<>]*)</Latitude>[^<>]*<Longitude>([^<>]*)</Longitude>", $buffer, $regs)) {
                                list(,$lat,$lng)=$regs;
                                $geoquery=" SELECT @latitude:=$lat, @longitude:=$lng";
                            }
                            if(ereg("<FormattedAddress>([^<>]*)</FormattedAddress>", $buffer, $regs)) {
                                list(,$geoaddress)=$regs;
                                $geoquery=" SELECT @latitude:=$lat, @longitude:=$lng, @geoaddress:='".addslashes($geoaddress)."'";
                            }
                            /*
                             * Yahoo APIs geocode service no longer available
                             */
                            // if(strpos($buffer, "OVER_QUERY_LIMIT")!==false) {
                            // 	$url="http://where.yahooapis.com/geocode?q=".urlencode(trim($regs[1],"%")."&appid=oneStep1");
                            // 	$ch = curl_init();
                            // 	curl_setopt($ch, CURLOPT_USERAGENT, $agent);
                            // 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            // 	curl_setopt($ch, CURLOPT_URL, $url);
                            // 	$buffer=str_replace(array(chr(10),chr(13)),"",curl_exec($ch));
                            // 	curl_close($ch);
                            // 	$results = simplexml_load_string($buffer);
                            // 	if(isset($results->Result->latitude) && $results->Result->latitude<>0) {
                            // 		$lat=$results->Result->latitude;
                            // 		$lng=$results->Result->longitude;
                            // 		$geoquery=" SELECT @latitude:=$lat, @longitude:=$lng, @geoaddress:='".addslashes(trim($regs[1],"%"))."'";
                            // 	}
                        } else {
                            if(ereg("<location>[^<>]*<lat>([^<>]*)</lat>[^<>]*<lng>([^<>]*)</lng>", $buffer, $regs)) {
                                list(,$lat,$lng)=$regs;
                                $geoquery=" SELECT @latitude:=$lat, @longitude:=$lng";
                            }
                            if(ereg("<formatted_address>([^<>]*)</formatted_address>", $buffer, $regs)) {
                                list(,$geoaddress)=$regs;
                                $geoquery=" SELECT @latitude:=$lat, @longitude:=$lng, @geoaddress:='".addslashes($geoaddress)."'";
                            }
                        }
                        if($geoquery!="") {
                            $reportr->background->query($geoquery);
                            $this->update_errors.="<!--\r\n".$mytext[$i]."\r\nURL:$url\r\nQUERY: ".$geoquery."\r\nROWS: ".$reportr->background->affected_rows."\r\nTIME: ".$timetook." seconds\r\n-->";
                        } else $this->update_errors.="<!--\r\n".$mytext[$i]."\r\nURL:$url\r\n".htmlentities($buffer)."\r\n-->";
                    }
                    elseif(eregi("^replicate ([^ ]+)", $mytext[$i], $regs))
                    {
                        $outfile=tempnam("/tmp",$regs[1]);
                        list($host,$port)=explode(":",DATABASE_HOST);
                        if($port=="") $port="3306";
                        $this->update_errors.="<!-- REPLICATE ".$regs[1]." -->\r\n";
                        shell_exec("/usr/bin/mysqldump -h$host -u".DATABASE_USER." -p".DATABASE_PASS." -P$port -c --lock-tables=false --triggers=false ".LOCAL_DB." ".$regs[1]." 1> $outfile 2>>$outfile.err");
                        shell_exec("echo \"Exported file: \"`dir -l $outfile` >> $outfile.err");
                        shell_exec("mysql -h$host -u".DATABASE_USER." -p".DATABASE_PASS." -P$port ".LOCAL_DB." < ".$outfile." 2>>$outfile.err");
                        $this->update_errors.="<!-- RESULTS: ".htmlspecialchars(file_get_contents($outfile.".err"))." -->";
                        unlink($outfile);
                        unlink($outfile.".err");
                    }
                    elseif(eregi("^SENDTABLETOCOMMON ([^ ]+)", $mytext[$i], $regs))
                    {
                        $outfile=tempnam("/tmp",$regs[1]);
                        list($host,$port)=explode(":",DATABASE_HOST);
                        if($port=="") $port="3306";
                        $this->update_errors.="<!-- REPLICATE ".$regs[1]." -->\r\n";
                        shell_exec("/usr/bin/mysqldump -h$host -u".DATABASE_USER." -p".DATABASE_PASS." -P$port -c --lock-tables=false --triggers=false ".LOCAL_DB." ".$regs[1]." 1> $outfile 2>>$outfile.err");
                        shell_exec("echo \"Exported file: \"`dir -l $outfile` >> $outfile.err");
                        list($host,$port)=explode(":",COMMON_HOST);
                        if($port=="") $port="3306";
                        shell_exec("mysql -h$host -u".COMMON_USER." -p".COMMON_PASS." -P$port common < ".$outfile." 2>>$outfile.err");
                        $this->update_errors.="<!-- RESULTS: ".htmlspecialchars(file_get_contents($outfile.".err"))." -->";
                        unlink($outfile);
                        unlink($outfile.".err");
                    }
                    elseif(eregi("^exec (.+)", $mytext[$i], $regs))
                    {
                        $thistime=time();
                        $starttime=explode(" ",microtime());
                        $myresults=shell_exec($regs[1].(ereg(">",$regs[1])?"":" 2>&1"));
                        $endtime=explode(" ",microtime());
                        $timetook=number_format($endtime[0]+$endtime[1]-$starttime[0]-$starttime[1],3);
                        $reportr->query("set @execresults='".addslashes($myresults)."'");
                        /* if(ereg(",HURDMAN,",$this->params["keys"])) */$this->update_errors.="<!--\r\n$regs[1]\r\nTIME: ".$timetook." seconds\r\n-->";
                    }
                    elseif(eregi("^licenseupdate (.+)", $mytext[$i], $regs))
                    {
                        $this->LicenseUpdate($regs[1]);
                    }
                    elseif(eregi("^mergetextfield (.+)", $mytext[$i], $regs))
                    {
                        $thistime=time();
                        $starttime=explode(" ",microtime());
                        $numrows=0;
                        if(ereg(" from ([a-z_]+) where", $regs[1], $myregs)) $tablename=$myregs[1]; else continue;
                        $result = $reportr->background->query($this->resolve_pipes($regs[1]));
                        $results=$reportr->background->last_result;
                        if(sizeof($results)>0) {
                            include_once(_MYBINDR);
                            $mybindr=new mybindr();
                            $mybindr->database=$reportr->background->selected_db;
                            $mybindr->begincode="\[";$mybindr->endcode="\]";$mybindr->overrideshowerror=true;
                        }
                        if( count($results) > 0 ) {
                            foreach($results as $row) {
                                $fields=sizeof($row); $ii=1; unset($keys, $text);
                                foreach($row as $key=>$value) {
                                    if(eregi("initials",$key)) $mybindr->params["staff"]=$value;
                                    if($ii==$fields) { $value=str_replace("[[", "&#91;", str_replace("]]", "&#93;", $value)); $text=$mybindr->ResolveMerge($value); $textname=$key; }
                                    else { $keys[]="$key='".$value."'"; }
                                    $ii++;
                                }
                                $text=ereg_replace("--Can't find merge code: ([^-]+)--", "&#91;\\1&#93;", $text);
                                $reportr->background->select($reportr->background->selected_db);
                                $reportr->background->query("update $tablename set $textname='".addslashes($text)."' where ".implode(" and ", $keys));
                                $numrows+=$reportr->background->affected_rows;
                            }
                        }
                        $endtime=explode(" ",microtime());
                        $timetook=number_format($endtime[0]+$endtime[1]-$starttime[0]-$starttime[1],3);
                        /* if(ereg(",HURDMAN,",$this->params["keys"])) */$this->update_errors.="<!--\r\nmergetextfield update process\r\nROWS: ".$numrows."\r\nTIME: ".$timetook." seconds\r\n-->";
                    }
                    elseif(eregi("^caliberupdate", $mytext[$i], $regs))
                    {
                        $thistime=time();
                        $starttime=explode(" ",microtime());
                        $numrows=0;
                        require_once('nusoap.php');
                        $client = new nusoapclient('http://66.240.197.236/CompanyRating.asmx', true, false, false, false, false, 0, 600);
                        while(true) {
                            $result = $reportr->background->query($this->resolve_pipes("select BID, BusinessStartDate, TOBScore, GovtActScore, LicenseScore, Unanswered, Unresolved, Serious, Total, MembershipStatus, AdReviewScore, BackgroundScore from caliberrating where bid>0 and ifnull(lastchange>lastupdate,1) order by rating, lastchange limit 1000"));
                            if(sizeof($reportr->background->last_result)>0) {
                                list($mybid, $businessstartdate, $tob, $govtact, $license, $unans, $unres, $serious, $cmpl, $membership, $adrev, $background) = array_values($reportr->exceptionlist=$reportr->background->last_result[0]);
                                $param = array("AlgorithmID"=>1, "BusinessStartDate"=>date("n/j/Y",strtotime($businessstartdate)), "TobScore"=>$tob, "GovernmentActionScore"=>$govtact, "LicenseScore"=>$license, "UnansweredComplaintCount"=>$unans, "UnresolvedComplaintCount"=>$unres, "SeriousComplaintCount"=>$serious, "TotalComplaintCount"=>$cmpl, "MemberShipStatus"=>$membership, "AdReviewScore"=>$adrev, "BackgroundScore"=>$background, "sCompositScore"=>0, "ErrMessage"=>"");
                                $result = $client->call('RateCompany',$param,"http://tempuri.org/", "http://tempuri.org/RateCompany", false, null, "rpc", "literal");
                                $reportr->background->query("update caliberrating set rating='".$result["RateCompanyResult"]."', RatingValue='".$result["sCompositScore"]."', lastchange=ifnull(lastchange,now()), lastupdate=now(), callsmade=callsmade+1 where bid=$mybid");
                                $numrows++;
                            } else {
                                unset($client);
                                break;
                                //return "$numrows rows updated.";
                            }
                        }
                        $endtime=explode(" ",microtime());
                        $timetook=number_format($endtime[0]+$endtime[1]-$starttime[0]-$starttime[1],3);
                        /* if(ereg(",HURDMAN,",$this->params["keys"])) */$this->update_errors.="<!--\r\nCaliber update process\r\nROWS: ".$numrows."\r\nTIME: ".$timetook." seconds\r\n-->";
                    }
                    elseif(eregi("^set,", $mytext[$i]))
                    {
                        list($name, $value) = explode("=", eregi_replace("set,", "", $mytext[$i]), 2);
                        if(ereg("^select", $value)) { $value = $reportr->background->get_var($value); }
                        $query["params"][] = array($name, $value);
                    }
                    elseif(ereg("^(BEGIN|END)ASYNC (.+)$", $mytext[$i], $regs))
                    {
                        if($regs[1]=="BEGIN") {
                            $asynctry=0;
                            do {
                                if($asynctry>0) sleep(1);
                                $reportr->background->query("CREATE TABLE `async_".$regs[2]."` SELECT connection_id() as thread");
                                if(!$reportr->background->result && $asynctry>120) {
                                    $asyncid = $reportr->background->get_var("select * from `async_".$regs[2]."`");
                                    $reportr->background->query("KILL $asyncid");
                                    $reportr->background->query("DROP TABLE `async_".$regs[2]."`");
                                    $reportr->background->query("CREATE TABLE `async_".$regs[2]."` SELECT connection_id() as thread");
                                }
                            } while (!$reportr->background->result && ($asynctry++)<121);
                        } else {
                            $reportr->background->query("DROP TABLE `async_".$regs[2]."`");
                        }
                    }
                    elseif(ereg("^runqueriesfromweb (.+)$",$mytext[$i], $regs)) {
                        $this->update_errors.="<!--\r\nQUERY: $regs[1]\r\n";
                        if(eregi("^select", $regs[1])) $url=$reportr->background->get_var($regs[1]);
                        else $url=$regs[1];
                        $this->update_errors.="URL: $url\r\n";
                        $queriestorun=explode("\r\n||\r\n", file_get_contents($url));
                        //die(print_r($queriestorun, true));
                        foreach($queriestorun as $onequery) {
                            $reportr->background->query($onequery);
                            $this->update_errors.="QUERY: $onequery\r\n";
                        }
                        $this->update_errors.="-->";
                    } elseif(ereg("^ CALL ",$mytext[$i]) || ereg("^CALL ", $mytext[$i])) {
                        list($host,$port)=explode(":",DATABASE_HOST);
                        if($port=="") $port="3306";
                        $mycallquery=$reportr->background->get_var("select ifnull(@_CALL_QUERY,'')");
                        $myresult=shell_exec("mysql -h$host -P$port -u".DATABASE_USER." -p".DATABASE_PASS." -vvv ".LOCAL_DB." -e\"SET SQL_LOG_BIN=".$logbin.";".str_replace('"','\\"',$mycallquery.$mytext[$i])."\"");
                        /* if(ereg(",HURDMAN,",$this->params["keys"])) */$this->update_errors.="<!--\r\nQUERY: SET SQL_LOG_BIN=".$logbin.";".$mycallquery.$mytext[$i]."\r\n".$myresult;
                    }
                    elseif(eregi("^replace", $mytext[$i]) || eregi("^ select", $mytext[$i]) || eregi("^(delete|prepare|deallocate|execute)", $mytext[$i]) || eregi("^insert", $mytext[$i]) || eregi("^update", $mytext[$i]) || eregi("^create", $mytext[$i]) || eregi("^drop", $mytext[$i]) || eregi("^alter", $mytext[$i]) || eregi("^truncate", $mytext[$i]) || eregi("^lock", $mytext[$i]) || eregi("^unlock", $mytext[$i]) || eregi("^flush", $mytext[$i]) || eregi("^call ", $mytext[$i]) || eregi("^ call ", $mytext[$i]) || eregi("^start ", $mytext[$i]) || eregi("^set ", $mytext[$i]) || eregi("^repair", $mytext[$i]) || eregi("^optimize", $mytext[$i]))
                    {
                        if(eregi("^set sql_log_bin=0", $mytext[$i])) $logbin=false;
                        if(eregi("^set sql_log_bin=1", $mytext[$i])) $logbin=true;
                        $mydb=$reportr->background->selected_db;
                        if(eregi("create table ([^ ]+) if (.*)older than ([0-9 ]*)([^ ]+)", $mytext[$i], $regs)) {
                            if($regs[3]>0) {
                                $thistable=$regs[1];
                                $reportr->background->query("show table status like '$thistable'");
                                $update2=$reportr->background->last_result[0]["Update_time"];
                                if($update2<date("Y-m-d H:i:s", strtotime($regs[3].$regs[4]." ago"))) $reportr->background->query("drop table if exists $thistable");
                            } else {
                                $comparetable=$regs[4]; $fromdatabase="";
                                if(eregi("(.+)[.](.+)", $regs[4], $regs2)) { $fromdatabase=" from ".$regs2[1]; $comparetable=$regs2[2]; }
                                $thistable=$regs[1];
                                $reportr->background->query("show table status $fromdatabase like '$comparetable'");
                                $update1=$reportr->background->last_result[0]["Update_time"];
                                $reportr->background->query("show table status like '$thistable'");
                                $update2=$reportr->background->last_result[0]["Update_time"];
                                if($update2<date("Y-m-d H:i:s", strtotime($update1." ".$regs[2]."ago"))) $reportr->background->query("drop table if exists $thistable");
                            }
                            $mytext[$i]=eregi_replace("(create table [^ ]+)( if (.*)older than ([0-9 ]*)[^ ]+)", "\\1", $mytext[$i]);
                        }
                        if(eregi("create table ([^ ]+) /[*] if (.*)older than ([0-9 ]*)([^ ]+) [*]/", $mytext[$i], $regs)) {
                            if($regs[3]>0) {
                                $thistable=$regs[1];
                                $reportr->background->query("show table status like '$thistable'");
                                $update2=$reportr->background->last_result[0]["Update_time"];
                                if($update2<date("Y-m-d H:i:s", strtotime($regs[3].$regs[4]." ago"))) $reportr->background->query("drop table if exists $thistable");
                            }
                        }
                        if(eregi("^create( temporary | )table ([^ ]+)( select .* from | )(OTHERSERVER:[^:]*:[^:]*:[^:]*:[^:.]*[.]show .*|show .*)$", $mytext[$i], $regs)) {
                            $tabledef=""; $valuedef="";
                            ereg("(OTHERSERVER:[^:]*:[^:]*:[^:]*:[^:.][.]).*", $regs[4], $otherregs);
                            $reportr->background->query($regs[4]);
                            foreach($reportr->background->col_info as $onecol) {
                                switch($onecol->type) {
                                    case "string": $coldef="CHAR(".$onecol->max_length.")".($onecol->not_null?" NOT NULL":""); break;
                                    default: $coldef=strtoupper($onecol->type).($onecol->not_null?" NOT NULL":""); break;
                                }
                                $tabledef.="`".$onecol->name."` ".$coldef.", \r\n";
                            }
                            $tabledef="CREATE".strtoupper($regs[1])."TABLE ".$otherregs[1].$regs[2].($regs[3]==" "?"":"_temp")." (".substr($tabledef,0,strlen($tabledef)-4).")";
                            if(sizeof($reportr->background->last_result)>0) {
                                $valuedef="INSERT INTO ".$otherregs[1].$regs[2].($regs[3]==" "?"":"_temp")." VALUES ";
                                foreach($reportr->background->last_result as $row) {
                                    $valuedef.="(";
                                    foreach($row as $key=>$value) {
                                        $valuedef.="'".addslashes($value)."',";
                                    }
                                    $valuedef=ereg_replace(",$", "),",$valuedef);
                                }
                                $valuedef=ereg_replace(",$", "",$valuedef);
                            } else $valuedef="";
//							echo $valuedef;
                            $reportr->background->query($tabledef);
                            if($regs[3]!=" ") {
                                $reportr->background->query($valuedef);
                                $mytext[$i]="CREATE".strtoupper($regs[1])."TABLE ".$regs[2]." ".$regs[3]." ".$regs[2]."_temp";
                            } else $mytext[$i]=$valuedef;
                        } elseif(eregi("^(drop table .+) if (.*)$", $mytext[$i], $regs)) {
                            if(eregi("^older than (.+)", $regs[2], $newregs)) {
                                if(eregi("^[0-9]", $newregs[1])) {
                                    $update1=date("Y-m-d H:i:s", strtotime($newregs[1]." ago"));
                                } else {
                                    $comparetable=$newregs[1]; $fromdatabase="";
                                    if(eregi("(.+)[.](.+)", $newregs[1], $regs2)) { $fromdatabase=" from ".$regs2[1]; $comparetable=$regs2[2]; }
                                    $reportr->background->query("show table status $fromdatabase like '$comparetable'");
                                    $update1=$reportr->background->last_result[0]["Update_time"];
                                }
                                eregi("^drop table (.+)", $regs[1], $regs3);
                                $thistable=str_replace("`","",$regs3[1]);
                                $reportr->background->query("show table status like '$thistable'");
                                $update2=$reportr->background->last_result[0]["Create_time"];
                                if($update2<$update1) $mytext[$i]="drop table if exists `$thistable`"; else $mytext[$i]="";
                            } else {
                                $reportr->background->query((eregi("^select",$regs[2])?"":"select ").$regs[2]);
                                foreach($reportr->background->last_result[0] as $key=>$value) {
                                    if($value==0) $mytext[$i]=""; else $mytext[$i]=$regs[1];
                                    break;
                                }
                            }
                        }
                        if($reportr->use_database_list[0]!="") {
                            foreach($reportr->use_database_list as $nextdb) {
                                if(isset($olddbh)) $reportr->background->dbh=$olddbh;
                                unset($olddbh);
                                if(ereg(" [(]viawest[)]",$nextdb)) {
                                    $nextdb=ereg_replace(" [(]viawest[)]","",$nextdb);
                                    $olddbh=$reportr->background->dbh;
                                    list($myhost, $myport, $myuser, $mypass)=mysql_fetch_row(mysql_db_query($mydb, "select mysqlwebhost, mysqlwebport, mysqlwebuser, mysqlwebpassword from servers where bbb_db='".$nextdb."' limit 1", $olddbh)) or die(mysql_error());
                                    $reportr->background->dbh = mysql_connect($nextdb.".hurdman.org", $myuser, $mypass) or die($nextdb.".hurdman.org".":".$nextdb.":".mysql_error());
                                    list($myhost, $myuser, $mypass)=array($nextdb.".hurdman.org", $myuser, $mypass);
                                } elseif(ereg(" [(]local[)]",$nextdb)) {
                                    $nextdb=ereg_replace(" [(]local[)]","",$nextdb);
                                    $olddbh=$reportr->background->dbh;
                                    list($myhost, $myport, $myuser, $mypass)=mysql_fetch_row(mysql_db_query($mydb, "select if(mysqlhost=mysqlwebhost and mysqlport=mysqlwebport,concat(bbb_db,'.hurdman.org'),mysqlhost), if(mysqlhost=mysqlwebhost and mysqlport=mysqlwebport,'3306',mysqlport), mysqluser, mysqlpassword from servers where bbb_db='".$nextdb."' limit 1", $olddbh)) or die(mysql_error());
                                    $reportr->background->dbh = mysql_connect($myhost.":".$myport, $myuser, $mypass);// or die($myhost.":".$myport.":".$nextdb.":".mysql_error());
                                    if(!$reportr->background->dbh) { echo $myhost.":".$myport.":".$nextdb.":Can't connect! ".mysql_error(); continue; }
                                    list($myhost, $myuser, $mypass)=array($myhost.":".$myport, $myuser, $mypass);
                                } elseif($nextdb=="common") {
                                    $olddbh=$reportr->background->dbh;
                                    $reportr->background->dbh = mysql_connect(COMMON_HOST, COMMON_USER, COMMON_PASS); // or die(COMMON_HOST.":".mysql_error());
                                    list($myhost, $myuser, $mypass)=array(COMMON_HOST, COMMON_USER, COMMON_PASS);
                                }
                                $reportr->background->query("select @thisdb:='".$nextdb."'");
                                $reportr->background->select($nextdb);
                                $starttime=explode(" ",microtime());
                                $myresult="";
                                if(ereg("^call", $mytext[$i])) {
                                    $myport="";
                                    list($myhost,$myport)=explode(":",$myhost);
                                    if($myport=="") $myport="3306";
                                    $myresult=shell_exec("mysql -h".$myhost." -P$myport -u".$myuser." -p".$mypass." -vvv $nextdb -e\"".str_replace('"','\\"',$mytext[$i])."\" &2>1");
                                } else $reportr->background->query($mytext[$i]);
                                $endtime=explode(" ",microtime());
                                $timetook=number_format($endtime[0]+$endtime[1]-$starttime[0]-$starttime[1],3);

                                if($reportr->background->use_error>"") $this->update_errors.="<!-- ***ERROR***\r\nQUERY: ".$mytext[$i]."\r\nERROR: ".$reportr->background->use_error."\r\n-->\r\n";
                                else $this->update_errors.="<!--\r\nQUERY: ".$mytext[$i]."\r\nROWS: ".$reportr->background->affected_rows."\r\nTIME: ".$timetook." seconds\r\n-->";

                                $this->params[$nextdb." rows affected ".md5($mytext[$i])]=($myresult?$myresult."<br>":"").($reportr->background->use_error?addslashes($reportr->background->use_error)."<br>":"").($reportr->background->affected_rows)." rows and took ".$timetook." seconds";
                            }
                            $reportr->background->select($mydb);
                            $query[$loop]["query"][] = "select 'Query #".(++$usedbquerynum)." affected |rows affected ".md5($mytext[$i])."|.' as ''";
//							print_r($this->params);
                        } else {
                            $thistime=time();
                            $starttime=explode(" ",microtime());
                            $reportr->background->query($mytext[$i]);
                            $endtime=explode(" ",microtime());
                            $timetook=number_format($endtime[0]+$endtime[1]-$starttime[0]-$starttime[1],3);
                            if(/*ereg(",HURDMAN,",$this->params["keys"]) && */$reportr->background->use_error>"") $this->update_errors.="<!-- ***ERROR***\r\nQUERY: ".$mytext[$i]."\r\nERROR: ".$reportr->background->use_error."\r\n-->\r\n";
                            else/* if(ereg(",HURDMAN,",$this->params["keys"])) */ $this->update_errors.="<!--\r\nQUERY: ".$mytext[$i]."\r\nROWS: ".$reportr->background->affected_rows."\r\nTIME: ".$timetook." seconds\r\n-->";
                            $lastaffectedrows=$reportr->background->affected_rows;
                        }
                    }
                    elseif(eregi("transform (.*) (select.*) (from.*) pivot (.*)", $mytext[$i], $regs))
                    {
                        /*
transform sum(amount) select bid from ledger where bid>0 group by bid pivot entrytype;


transform [transformfield] [selectclause] [fromclause] pivot [pivotfield] [pivotclause]

select distinct [pivotfield] [fromclause] having [pivotclause];

[selectclause], [transformfield*pivotfield=pivotvalue] as [pivotvalue], ... [fromclause];
                        */
                    }
                    else
                    {
                        if($mytext[$i]>"") $query[$loop]["query"][] = $mytext[$i];
                    }
                }
                $this->query = $query;
            }
            else
                if($mytext>"") $this->query[]["query"][] = $mytext;
            $this->num_queries = sizeof($this->query);
            $endtime=explode(" ",microtime());
            $timetook=number_format($endtime[0]+$endtime[1]-$starttime_tot[0]-$starttime_tot[1],3);
            $this->timetook=$timetook;
            $this->update_errors.="\r\n<!-- TOTAL TIME TOOK: ".$timetook." seconds -->";
        }
    }
}