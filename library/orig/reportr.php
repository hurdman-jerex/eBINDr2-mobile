<?php
require_once(DIR_LIBRARY."/graph.php");

if(!class_exists('reportr'))
{
	class reportr extends db
	{
		var $lang; // curent program language
		var $options; // table def options
		var $do_limiting; // whether we want to limit a query
		
		var $class;
		var $field; // field index realtionship array
		var $linked; // field names that are linked
		var $skip; // sub-program skip array

	    var $currentbid='';

		var $filename = APPLICATION_FILENAME;
		var $background; // database background querying object
		var $key_words = array(); // key word linking array
		var $query_run = 0; // query internal pointer for query groups
		var $ident = 0;
		var $row_run = 0;
		var $related_queries; // realted queries found
		var $related_desc; // realted queries descriptions found
		var $display; // template wrapper
		var $num_transform; // where the transform table is at in its looping
		var $loop_at; // internal looping pointer for transform statements
		var $data; // data returned from query
		var $extension; // parameter variables (POST, GET, SET) appended to links
		
		var $output; // temp HTML output variable
		var $buffer; // buffer variable 
		
		var $query_tbl = QUERY_TABLE;
		var $query_db = QUERY_DB;
		var $skipped_fields;
		var $loop_db="";
		var $use_database_list=array("");
		var $exceptionlist="";
		/**
		 * @return void
		 * @param dbname string
		 * @param dbhost string
		 * @desc class constructor. connects to mysql and sets programatic settings
		 */
		function reportr($dbname, $dbhost) {
			global $variables, $task, $key_words, $parse;
			
			$this->background = $task;
			$this->variables = $variables;
			$this->lang = $this->variables["lang"];
			$this->key_words = $key_words;
			$this->current_query = $this->variables[0];
			// set what the name of the exportr query would be
			$parse->params["exportrquery"] = $this->variables[1];
			$this->db($dbname, $dbhost);
			if(isset($_POST["THREADID"])) $this->threadid=$_POST["THREADID"];
			$parse->params["THREADID"] = $this->threadid;
			$this->display = new display(array("description", "table", "back", "back_active", "next", "next_active", "table_prefix"));
			$this->related_queries = $this->related();
			$this->variable_set();
			$this->networkfolder=$this->background->get_var("select setup(733)");
			if(isset($_POST["EXCEPTIONLIST"])){ 
				if($_POST["EXCEPTIONLIST"]=="CLEAR")
					$this->background->query("DELETE from exceptionlist where mergecode=\"".$this->current_query."\"");
				elseif($_POST["EXCEPTIONLIST"]=="VIEW")
					$parse->params["EXCEPTION LIST"]="-99999999999";
				elseif(ereg("^CLEAR(.+)",$_POST["EXCEPTIONLIST"], $regs)) {
					$mylist = explode(",", $regs[1]);
					foreach($mylist as $mylistitem) $this->background->query("UPDATE exceptionlist SET List=REPLACE(List,'$mylistitem','-999999999999') where mergecode=\"".$this->current_query."\"");					
				} else
					$this->background->query("REPLACE INTO exceptionlist select ifnull(mergecode,\"".$this->current_query."\"), ifnull(concat(list,\",".$_POST["EXCEPTIONLIST"]."\"),\"".$_POST["EXCEPTIONLIST"]."\") from exceptionlist where mergecode=\"".$this->current_query."\" having count(*) in (0,1)");
			}
			set_time_limit(1800);
		}
		
		/**
		 * @return void
		 * @desc handles query group looping and template additions
		 */
		function control()
		{
			global $parse, $device, $find;
			foreach($this->use_database_list as $this->loop_db) {
				if(ereg("SELECTED", $this->loop_db)) continue;
				if(isset($olddbh)) $this->dbh=$olddbh;
				unset($olddbh);
				if(ereg(" [(]viawest[)]",$this->loop_db)) {
					$this->loop_db=ereg_replace(" [(]viawest[)]","",$this->loop_db);
					$olddbh=$this->dbh;
					list($myhost, $myport, $myuser, $mypass)=mysql_fetch_row(mysql_db_query($this->selected_db, "select mysqlwebhost, mysqlwebport, mysqlwebuser, mysqlwebpassword from servers where bbb_db='".$this->loop_db."' limit 1", $olddbh)) or die(mysql_error());
					$this->dbh = mysql_connect($this->loop_db.".hurdman.org", $myuser, $mypass); // or die($this->loop_db.".hurdman.org".":".$this->loop_db.":".mysql_error());
					if(!$this->dbh) { echo $this->loop_db.".hurdman.org".":".$this->loop_db.":".mysql_error(); continue; }
				} elseif(ereg(" [(]local[)]",$this->loop_db)) {
					$this->loop_db=ereg_replace(" [(]local[)]","",$this->loop_db);
					$olddbh=$this->dbh;
					list($myhost, $myport, $myuser, $mypass)=mysql_fetch_row(mysql_db_query($this->selected_db, "select if(mysqlhost=mysqlwebhost and mysqlport=mysqlwebport,concat(bbb_db,'.hurdman.org'),mysqlhost), if(mysqlhost=mysqlwebhost and mysqlport=mysqlwebport,'3306',mysqlport), mysqluser, mysqlpassword from servers where bbb_db='".$this->loop_db."' limit 1", $olddbh)); // or die(mysql_error());
					$this->dbh = mysql_connect($myhost.":".$myport, $myuser, $mypass); // or die($myhost.":".$myport.":".$this->loop_db.":".mysql_error());
					if(!$this->dbh) { echo $myhost.":".$myport.":".$this->loop_db.":Can't connect!"; continue; }
				} elseif($this->loop_db=="common") {
					$olddbh=$this->dbh;
					$this->dbh = mysql_connect(COMMON_HOST, COMMON_USER, COMMON_PASS) or die(COMMON_HOST.":".mysql_error());
				}
				$this->select($this->loop_db);
//				echo $this->dbh."\r\n";
				for($i=0; $i<=sizeof($parse->query); $i++)
				{
					$this->num_transform = sizeof($parse->query[$i]["query"]);
					$this->display->variable("firsttid", ($this->query_run+1));
					for($c=0; $c<sizeof($parse->query[$i]["query"]); $c++)
					{
						// if we are exporting or printing we do not want to run insert/update/delete statements
						if((eregi("^delete", $parse->query[$i]["query"][$c]) || eregi("^insert", $parse->query[$i]["query"][$c]) || eregi("^update", $parse->query[$i]["query"][$c])) && (($_GET["print"] == "true") && ($this->variables[0] == "exportr"))) {
							continue;
						/*} elseif ($parse->query[$i]["graph"] || $parse->query[$i]["graphoptions"]) {
							$this->loop_at = $c;
							if(isset($_GET[SHOWGRAPHNUM]) && $_GET[SHOWGRAPHNUM]==$i) {
								$this->MakeGraph($this->results($parse->resolve($parse->query[$i]["query"][$c])), ($parse->query[$i]["graphoptions"]?$parse->query[$i]["graphoptions"]:$parse->query[$i]["options"]), isset($_GET[EXCLUDETOTAL]));
								exit();
							} else {
								if(ereg("\?",$_SERVER[REQUEST_URI],$regs)) $mychar="&"; else $mychar="/?";
								$imglink=$_SERVER[REQUEST_URI].$mychar."SHOWGRAPHNUM=$i&".$this->poststr;
								if($parse->query[$i]["graphoptions"]) {
									$this->buffer .= "<a href=\"$imglink&EXCLUDETOTAL\" target=\"_new\" title=\"Click to show graph\"><img src=\"/css/graph.gif\" border=0></a>";
									$parse->query[$i]["graphoptions"]=false;
									$c--;
								} else $this->buffer .= "<img src=\"$imglink\" border=0>";
							}
							continue;*/
						} else {
							$this->doorderlinks=false;
							$_tmp=$parse->query[$i]["query"][$c];
							while(eregi("order by (.*)$",$_tmp, $regs)) $_tmp=$regs[1];
//							if(eregi("order by (.*)$",$parse->query[$i]["query"][$c], $regs)) {
							if($_tmp!=$parse->query[$i]["query"][$c]) {
								$this->doorderlinks=true;
								$regs[1]=ereg_replace(" limit.*","",$regs[1]);
								if(isset($_GET["FIELD_ORDERBY"])) {
									$_REQUEST["FIELD_ORDERBY"]=$_GET["FIELD_ORDERBY"];
									$_REQUEST["TABLE_ORDERBY"]=$_GET["TABLE_ORDERBY"];
									$_REQUEST["FIELD_ORDERDIR"]=$_GET["FIELD_ORDERDIR"];
								}
								if(isset($_REQUEST["FIELD_ORDERBY"]) && $_REQUEST["TABLE_ORDERBY"]==($this->query_run+1)) {
									$tempsql=eregi_replace(" as "," \x02 ",$parse->query[$i]["query"][$c]);
									$this->current_order_display=$_REQUEST["FIELD_ORDERBY"];
									if(eregi("date_format\(max\(([^,]*))[^\x02]*) \x02 ".$_REQUEST["FIELD_ORDERBY"], $tempsql, $regs2)) $this->current_order=$regs2[1];
									elseif(eregi("date_format\(([^,]*)[^\x02]*) \x02 ".$_REQUEST["FIELD_ORDERBY"], $tempsql, $regs2)) $this->current_order=$regs2[1]; 
									else $this->current_order=$_REQUEST["FIELD_ORDERBY"]; 
//									echo "order by ".str_replace("(","\(",str_replace(")","\)",$regs[1]));
									//echo "order by `".str_replace(".","`.`",$this->current_order)."` ".$this->current_order_direction;
									$this->current_order_direction=$_REQUEST["FIELD_ORDERDIR"];
									$parse->query[$i]["query"][$c]=str_ireplace("order by ".str_replace("(","(",str_replace(")",")",$regs[1])),"order by `".str_replace(".","`.`",$this->current_order)."` ".$this->current_order_direction,$parse->query[$i]["query"][$c]);

									//echo "order by `".str_replace(".","`.`",$this->current_order)."` ".$this->current_order_direction;

									//$parse->query[$i]["query"][$c]=eregi_replace("order by ".str_replace("(","\(",str_replace(")","\)",$regs[1])),"order by `".str_replace(".","`.`",$this->current_order)."` ".$this->current_order_direction,$parse->query[$i]["query"][$c]);
									//echo 'reorder: '. $parse->query[$i]["query"][$c];
								}
							}
							$this->loop_at = $c;
							if($_POST["allrecords"]=='YES' && $_GET["print"]=="true")
								$this->results(calc_found_rows(limit($parse->resolve($parse->query[$i]["query"][$c]), 10000)));
							else
								$this->results(calc_found_rows(limit($parse->resolve($parse->query[$i]["query"][$c]), $parse->query[$i]["options"][1])));
							$this->construct($parse->query[$i]["options"], $api, array($parse->query[$i]["graph"], $parse->query[$i]["graphoptions"]));
							if($parse->query[$i]["graphoptions"]) { $parse->query[$i]["graphoptions"]=false; $this->display->variable("firsttid", ($this->query_run+1)); $c--; }
						}
					}
				}
				$device->define("output_head", $this->output_head);
				$device->define("head_desc", $find["description"]);
				$device->define("content", $this->buffer);
				$this->background->query("update reportlogtime set timetook=".$parse->timetook." where id=".$parse->reportlogid, OBJECT, false);
				$this->background->query("delete from processlist where id=connection_id()", OBJECT, false);
				if($parse->logqueries) {
					$this->background->query("insert into reportquerylog (mergecode, day, staff, results) values ('".addslashes($this->current_query)."', now(), '".$parse->parms["staff"]."', compress('".addslashes($parse->update_errors)."'))");
				}
				if(ereg(",HURDMAN,",$parse->params["keys"]) || ((int) ereg_replace("^[^0-9]*,([0-9]+),.*$", "\\1", $parse->params["keys"]) & 134217728) ) {
					$device->define("MYSQL_ERRORS", $parse->update_errors);
				} else $parse->update_errors="";
				$device->define("USED_PARAMETERS", base64_encode(serialize($parse->usedparameters)));
			}
		}
		
		/**
		 * @return void
		 * @param options array
		 * @desc builds the html table to display the query result set
		 */
		function construct($options = false, $api=false, $graph=false) {
			global $parse, $device;
			authorize(); // make sure the current user is authorized
			$this->variable_set();
			if(!is_array($options) || !$options || empty($options)) {
				$options = array("h", "25", "200", "");
			}
			// matrix navigation controler
			if(eregi("^m", $options[0])) {
				list($options[0], $this->matrix_next) = explode(":", $options[0]);
			}
			$this->options = $options;
			if($this->options[1] > MAX_LIMIT && MAX_LIMIT!="MAX_LIMIT") $this->options[1]=MAX_LIMIT;
			$this->query_run++;
			$device->define("query_run", $this->query_run);
			if(!empty($this->limit_vars[1])) {
				$this->ident = $this->limit_vars[1];
			} else $this->ident=0;
			
			$this->get_data($this->options[0]);
			if ($graph[0] || $graph[1]) {
			if(!$graph[0]) { $options[1]=800; $options[2]=400; }
//			print_r($this);exit;
				if(eregi("^select ", $options[3]))
					// if our title has a select at the beginning then execut it
					$title = $this->background->get_var($options[3]);
				else
					// otherwise just set the title according to what it is
					$title = $options[3];

				foreach($this->col_info as $col) if($col->name!="style") $columns[]="'".$col->name."'";
				foreach($this->data as $onerow) {
					$ii=0;$style=", 'color:#729fcf;opacity:0.9'";$vals=array();
					foreach($onerow as $val) {
						if($this->col_info[$ii]->name=='style') {
							$style=", '$val'";
						} else {
							if($this->col_info[$ii]->numeric && $ii>0) $vals[]=$val; 
							elseif($this->col_info[$ii]->type=="date" && $ii==0) {
								list($yr,$mn,$dy)=explode('-',$val);
								$vals[]="new Date(".$yr.", ".($mn-1).", ".$dy.")";
							} else $vals[]="'".addslashes($val)."'";
						}
						$ii++;
					}
					$chartarray[]="[".implode(", ", $vals).$style."]";
				}
				$columns=implode(", ", $columns);
				switch($options[0]) {
					case "p":$charttype="Pie";break;
					case "l":$charttype="Line";break;
					case "v":
					case "c":
					default:$charttype="Column";break;
				}
//				if(!$this->hasgraph) $this->buffer.= "";
				$this->buffer.="<script type=\"text/javascript\">
				function reportchart".$this->query_run."() {
		//define columns
      var dataTable = google.visualization.arrayToDataTable([
        [$columns, { role: 'style' } ],
		".implode(", ",$chartarray)."
      ]);				
	  
      var view = new google.visualization.DataView(dataTable);

                //instantiate our chart object
                var chart = new google.visualization.".$charttype."Chart(document.getElementById('reportchart".$this->query_run."'));
 
                //define options for visualization
                var options = {width: ".$options[1].", height: ".$options[2].", legend: {position:'none'}, title: '".ereg_replace("<graphoptions>.*</graphoptions>","",str_replace("'","\\'",$title))."'/*, chartArea:{left:50,top:50,width:\"80%\",height:\"80%\"}*/".(ereg("<graphoptions>(.*)</graphoptions>",$title,$regs)?",".$regs[1]:"")."};
 
                //draw our chart
                chart.draw(view, options);
				}
				</script>";
				
				$this->buffer.="<input type=\"hidden\" name=\"limit".$this->query_run."\" value=\"".(!$graph[0]?$options[1]:"25")."\" /><div class='reportchart' id='reportchart".$this->query_run."' style='margin-bottom:20px;".($graph[0]?"":"display:none;")."'></div>";
				if(!$graph[0]) $this->buffer.="<img style='cursor:hand;' onclick='$(\"reportchart".$this->query_run."\").setStyle(\"display\",($(\"reportchart".$this->query_run."\").getStyle(\"display\")==\"none\"?\"\":\"none\"));' src=\"/css/graph.gif\" border=0>";
				$this->hasgraph=true;
				return;
				//print_r($this->data); print_r($options); exit; 
			}

			// we are running the exportr sub program. all other processes will
			// die at this spot once exportr is completed
			if($this->col_info[0]->name == 'exportr') {
				$parse->resolve($this->last_result[0]["exportr"]);
				$parse->resolve_double($parse->output);
				// see how many queries we are running
				for($i=0; $i<sizeof($parse->query); $i++) {
					// how many query-sets
					for($s=0; $s<sizeof($parse->query[$i]["query"]); $s++) {
						if (ereg (" [$][`]$", $parse->query[$i]["query"][$s]))
							$runExportOld = true;
						elseif(isset($_POST["newexport"]))  
							$runExportOld =  false;
						else 
							$runExportOld =  true;
					}
				}
				if($_GET[EXPORT_OUTPUT]=='rtf') $runExportOld=true;
				if(isset($_GET["json"])) 
					$this->export_json();
				elseif(!$runExportOld || $_GET[EXPORT_OUTPUT] == 'apps' || $_GET[EXPORT_OUTPUT] == 'xlsx') 
					$this->exportv2();
				else
					$this->export();
				exit();
			}
			if($this->col_info && (!$this->use_error) && ($this->num_rows > 0 || (!isset($_GET[noheader]) && !isset($_GET[hideempty])))) {
				$this->parameters();
				$this->fields(); // build field names
				$this->data(); // bring in data rows
			}
			else {
				if($this->use_error_no > 0)
					$this->output = error_gen();
			}

			if(((($this->num_transform-1) == $this->loop_at) || ($this->use_error))) {
				//NWT removed 10/15/08 because it was affecting database master
//				if($this->num_rows > 0) {
					$ext = "</tbody></table>";
					/*	eBindr1 & 2
					 *	Findr Feature : open company on first row
					 *	By: Alan
					 */					
/*					if(isset($_GET["ebindr2"])) { 
						$ext .= "<script> 
											if ( $('2-1-3') != null ) { 
												if ($('2-1-3').get('onclick') != null) {
													var toBeEval = $('2-1-3').get('onclick').replace('this,','$(\"2-1-3\"),');
													eval( toBeEval );
												}
											} 
										</script>";
					} else {
						$ext .= "<script>
											var findrTarget = document.getElementById('2-1-3');
											if ( findrTarget != null ) {		
												var toBeEval = ( findrTarget.getAttribute('onclick') ).replace('this,','document.getElementById(\"2-1-3\"),');
												eval( toBeEval );
											}
										</script>";
					}*/
					// end Findr Feature
//				}
				unset($limit,$limitback);
				if(isset($_GET["limit" . $this->query_run]) && !isset($_POST["limit" . $this->query_run])) $_POST["limit" . $this->query_run]=$_GET["limit" . $this->query_run];
				if(isset($_GET["which_table"]) && !isset($_POST["which_table"])) $_POST["which_table"]=$_GET["which_table"];
				if(empty($_POST["limit" . $this->query_run])) {
					$limit = 0+$this->options[1];
					$limitback=0;
				}	else {
					if(($_POST["which_table"] == $this->query_run) && !empty($_POST["which_table"])) {
						$limit = ($_POST["limit" . $this->query_run]+$this->options[1]);
						$limitback = ($_POST["limit" . $this->query_run]-$this->options[1]);
					}	else {
						$limit = $this->options[1];
						$limitback=0;
					}
				}
				$this->display->variable("tid", $this->query_run); //table id
				$this->display->variable("limit", $limit);
				$device->variable("auto_scroll", $_POST['which_table']);
				$this->display->variable("limitback", $limitback);
				$this->display->variable("data", $this->output . $ext);
	
				if(!isset($_GET["noheader"])) $break = "<br />";
				else $break = NULL;

				$this->buffer .= $this->display->buffer("table") . $break;
				$this->output = null;
				unset($this->auto_devide); //This fixes the auto_devide problem 2004-10-14
			}
			$this->linked = array();
			unset($this->skip);
		}
		
		/**
		 * @return string
		 * @param options array
		 * @desc sets class global parameter options and queries titles and descriptions
		 */
		function parameters()
		{
			global $device;
			$device->define("printperpage", $this->options[1]);
			list($this->options[2], $colwidths) = explode(":",$this->options[2],2);
			$this->colwidths=explode(":",$colwidths);
			$i=0;
			foreach($this->colwidths as $mywidth) {
				$this->colalign[$i]=strtolower(ereg_replace("[^lrLR]", "", $mywidth));
				$this->colwidths[$i++]=ereg_replace("[^0-9]", "", $mywidth);
			}
			if(!empty($this->options[2])) $this->width = $this->options[2]; else $this->width = "";
			if($this->options[1]==0) $this->options[1]=25;	
			$this->display->variable("query_run", $this->query_run);
			$this->display->variable("calc_rows", $this->calc_rows);
			$this->page_links="";
			$this->num_pages=ceil($this->calc_rows/$this->options[1]);
			$this->current_page=floor($this->ident/$this->options[1])+1;
			$startnum=1;$endnum=$this->num_pages;
			if($this->num_pages>15) $endnum=15;
			if($this->current_page>7 && $this->num_pages>15) { 
				$startnum=$this->current_page-7;
				if($this->num_pages-$startnum<14) $startnum=$this->num_pages-14;
				$endnum=$startnum+14;
			}
			for($i=$startnum;$i<=$endnum;$i++) $this->page_links.=(($i==$this->current_page)?"<font color=black>":"<font color=blue><a onmouseover='this.style.cursor=\"hand\"' onmouseout='this.style.cursor=\"default\"' onclick=\"document.limit.limit".$this->query_run.".value=".(($i-1)*$this->options[1]).";do_submit('".$this->query_run."');\">")."$i</a></font> ";
			if($endnum<$this->num_pages) { $i=$this->num_pages; $this->page_links.="... <font color=blue><a onmouseover='this.style.cursor=\"hand\"' onmouseout='this.style.cursor=\"default\"' onclick=\"document.limit.limit".$this->query_run.".value=".(($i-1)*$this->options[1]).";do_submit('".$this->query_run."');\">$i</a></font> "; }
			if($startnum>1) { $i=1; $this->page_links="<font color=blue><a onmouseover='this.style.cursor=\"hand\"' onmouseout='this.style.cursor=\"default\"' onclick=\"document.limit.limit".$this->query_run.".value=".(($i-1)*$this->options[1]).";do_submit('".$this->query_run."');\">$i</a></font> ... ".$this->page_links; }
			$this->display->variable("page_list", "<b>".$this->page_links."</b>| ");
			$this->display->variable("ident", ($this->ident+1));
			$this->display->variable("column_span", (count($this->col_info)+1));
			$this->display->variable("width", $this->width);
			if(!empty($this->options[3]))
			{
				if($this->options[0] == 'v')
					// vertical table needs a different colspan count (data)
					$this->display->variable("colspan", (count($this->data)+1));
				else
					// horizonal table has a colspan count of the columns
					$this->display->variable("colspan", (count($this->col_info)+1));
					
				if(eregi("^select ", $this->options[3]))
					// if our title has a select at the beginning then execut it
					$title = $this->background->get_var($this->options[3]);
				else
					// otherwise just set the title according to what it is
					$title = $this->options[3];

				// replace the <rr> tag with the number of records returned (only avaliable in the title)
				$title = str_replace("<rr>", $this->calc_rows, $title);
				$this->display->variable("title", (($this->use_database_list[0]!="") ? "<div align='left'>Database: ".$this->loop_db."</div> " : "").$title);
				$this->display->variable("description", $this->display->buffer("description"));
			}
			else $this->display->variable("description", '');
			if($this->loop_at == 0)
			{
				if($this->do_limiting)
				{
					if(($this->calc_rows > $this->num_rows) && $_GET["print"] != "true") $this->display->variable("show", "style=\"display:\"");
					else $this->display->variable("show", "style=\"display:none\"");
				}
				else
					$this->display->variable("show", "style=\"display:none\"");
				 if($this->options[0]==strtoupper($this->options[0])) $this->display->variable("show", "style=\"display:\"");
				// back and next buttons
				if($this->ident > 0)
					$this->display->variable("back", $this->display->buffer("back_active"));
				else
					$this->display->variable("back", $this->display->buffer("back"));
					
				if($this->calc_rows <= ($this->ident+$this->num_rows))
					$this->display->variable("next", $this->display->buffer("next"));
				else
					 $this->display->variable("next", $this->display->buffer("next_active"));
				$this->display->variable("last_row", $this->ident+$this->num_rows);
				if($this->options[0] == 'v') $this->display->variable("table_class", "dataset_v");
				else $this->display->variable("table_class", "dataset");
				$this->output .= $this->display->buffer("table_prefix");
			} else { 
				//$this->query_run--;
			}
			$this->ident=0;
		}
		
		/**
		 * @return void
		 * @desc builds the field name html table for display. Also handles the fields if there needs to be data
		 * displayed vertically it will add it to that row and push the next field to a new <tr>
		 */
		function fields()
		{
			$this->skipped_fields=0;
			unset($this->identityrow);
			unset($this->groupindex);
			for ($i=0; $i<count($this->col_info); $i++)
			{
				if($this->colwidths[$i]>0) $this->col_info[$i]->width=$this->colwidths[$i];
				$this->col_info[$i]->hidezeros=false;
				if(ereg("^\(.*\)$",$this->col_info[$i]->name)) {
					$this->groupindex=$i;
				}
				if(ereg(" [$]$",$this->col_info[$i]->name) || ereg("^[0-9]+$",$this->col_info[$i]->name)) {
					$this->col_info[$i]->hidezeros=true;
					if(count($this->data)==1) {
//					print_r($this);
	//				exit();
					}
					for($z=0; $z<count($this->data); $z++)
					{
						if(null($this->data[$z][$i])!=0) $this->col_info[$i]->hidezeros=false;
					}
				}
				$this->col_info[$i]->name = trim($this->col_info[$i]->name);
				// setting the field alignment for integer fields
				if($this->col_info[$i]->type == 'int' || $this->col_info[$i]->type == 'real' || ereg("%$",$this->col_info[$i]->name))
					$this->align[$i] = ' align="right"'; else $this->align[$i] = '';
				if($this->colalign[$i]=="r") $this->align[$i] = ' align="right"'; 
				elseif($this->colalign[$i]=="l") $this->align[$i] = ' align="left"'; 
				// checking to see if hte field is a primary key
				if(SHOW_PRIMARY_KEY_ICON == 1) {
					if($this->col_info[$i]->primary_key)
						$this->icon = "<img src=\"" . $this->sub_dir . "/images/key.gif\" title=\"" . PRIMARY_KEY . "\">"; else $this->icon = "";
				}
				
				if($i == 0) // get the first value in the row
					$first = $this->col_info[$i]->name;
				
				// entering HTML empty data
				if((empty($this->col_info[$i]->name) || !$this->col_info[$i]->name) && (!isset($_GET[noheader]) || !isset($_GET[hideempty])))
					$this->col_info[$i]->name = ' &nbsp;'; 
					
				$this->class = 'table_heading';
				if($this->loop_at == 0) // check to see if our loop is at the start
				{
					// if the field name starts with a $ or ends with a $ then we automatically devide by 100
					if($this->col_info[$i]->name{0} == "$" || substr($this->col_info[$i]->name, -1) == '$')
						$this->auto_devide[$i] = $this->col_info[$i]->name;

					if(eregi("^[(]*(cid|bid)[)]*$", $this->col_info[$i]->name, $regs)) $this->identityrow[strtolower($regs[1])]=$i;
					if(eregi("^[*](cid|bid)$", $this->col_info[$i]->name, $regs)) $this->identityrow[strtolower($regs[1])]=$i;
					// if the current field is found as a related query; we need to link it.
					if(ereg("(BID|CID|MED|SC|MISC|VORP|VIP)FILES", $this->col_info[$i]->name, $regs))
					{
						$this->field[$i] = "";
						$this->skipped_fields++;
						$this->field_cell($i, "", false);
						$this->col_info[$i]->files=$regs[1];
					}
					elseif(@in_array($this->col_info[$i]->name, $this->related_queries))
					{
						$this->class = "table_heading_link";
						$this->field[$i] = $this->col_info[$i]->name;
						$this->linked[$i] = $this->col_info[$i]->name;
						$this->field_cell($i, $this->col_info[$i]->name, false);
					}
					// if the current field is found as an editr link, we link it accordingly
					elseif(@in_array($this->col_info[$i]->name . ".editr", $this->related_queries))
					{
						$this->class = "table_heading_link_editr";
						$this->field[$i] = $this->col_info[$i]->name;
						$append = ".editr";
						$this->linked[$i] = $this->col_info[$i]->name . ".editr";
						$this->field_cell($i, $this->col_info[$i]->name, true);
					}
					// if the current field is found in the Key Words array we link it a special way
					elseif(@in_array($this->col_info[$i]->name, $this->key_words))
					{
						$this->class = "table_heading_link";
						$this->field[$i] = $this->col_info[$i]->name;
						$this->linked[$i] = $this->col_info[$i]->name;
						$this->field_cell($i, $this->col_info[$i]->name, false);
					}
					// if the current field is hidden with a "*" we need to setup the skip sub-program
					elseif($this->col_info[$i]->name{0} == "*" || $this->col_info[$i]->name{0} == "&" || $this->col_info[$i]->hidezeros)
					{
						$this->skipped_fields++;
						$this->field[$i] = $this->col_info[$i]->name;
						$this->skip[$i] = $this->col_info[$i]->name;
					}
					// all other possibles are handled here. That includes normal data and matrix table setups
					else
					{
						$this->field[$i] = $this->col_info[$i]->name;
						$this->field_cell($i, $this->col_info[$i]->name, false);
					}
					// the description for linked fields
					$this->related_desc[$i] = $this->background->get_var("select description from " . QUERY_DB . "." . QUERY_TABLE . " where mergecode='" . $this->current_query . "." . $this->col_info[$i]->name . $append . "'");

				} else { // This is for subtables, so fields can be hidden
					if($this->col_info[$i]->name{0} == "*" || $this->col_info[$i]->name{0} == "&" || $this->col_info[$i]->hidezeros)
					{
						$this->skipped_fields++;
						$this->field[$i] = $this->col_info[$i]->name;
						$this->skip[$i] = $this->col_info[$i]->name;
					}
				}
			} 
			if($this->loop_at==0) $this->output.="</tr><tbody id=\"thebody$this->query_run\">";
		}	
		
		/**
		 * @return void
		 * @param i integer
		 * @param innerHTML string
		 * @param link boolean
		 * @desc constructs the field cell and puts data in that row is the table is verticle
		 */
		function field_cell($i, $innerHTML, $link=false)
		{
			// if we have empty data we need to skip the building of field cell's also
			// if we are running a matrix we need to skip this check and build all cells and
			// all fields in the table
			// set classes and the starting html tags for vertical data
			if(isset($_GET[suppressfieldheaders])) return;
			if($this->options[0] == 'v')
			{
				$this->class = 'v_heading';
				$this->output .= "<tr>\n";
			}
			if($this->options[0] != "v") {
				//if(is_array($this->orderbys) && in_array($innerHTML, $this->orderbys)) { // $this->current_order==$innerHTML) {
				if($this->doorderlinks && !eregi("^<",$innerHTML)) { // && !isset($_GET[noheader])) {
					$neworderdir=$this->current_order_direction=="asc" && $this->current_order_display==$innerHTML ? "desc" : (eregi("(open|close)", ereg_replace("^\((.*)\)$","\\1",$innerHTML) && $this->current_order_direction!="desc")?"desc":"asc");
					$innerHTML="<a title='Click to sort by this column' onclick='do_submit_order(this.href);return false;' href='".ereg_replace("&*TABLE_ORDERBY=[^&]*(&|$)","",ereg_replace("&*FIELD_ORDERBY=[^&]*(&|$)","",ereg_replace("&*FIELD_ORDERDIR=[^&]*(&|$)","",ereg_replace("[\]{0,1}'","%27",$_SERVER[REQUEST_URI])))).((strpos($_SERVER[REQUEST_URI],"?")>0)?"":"/?")."&TABLE_ORDERBY=".$this->query_run."&FIELD_ORDERBY=$innerHTML&FIELD_ORDERDIR=".$neworderdir."'>".ereg_replace("^\((.*)\)$","\\1",$innerHTML)."</a>"; //.(!isset($_REQUEST["FIELD_ORDERBY"])?"&".$this->poststr:"")
				}
				//} 
				if($this->col_info[$i]->numeric) {
						if(ALLOW_TABLE_FILTERING == 1 && false)
								$this->output .= "<td ondblclick=\"Filter('thebody$this->query_run', ".($i-$this->skipped_fields).")\" onclick=\"document.body.style.cursor = 'wait';var myparams='TableSort(\\'thebody$this->query_run\\', ".($i-$this->skipped_fields).", \\'n\\')'; var tid=setTimeout(myparams,100);\" nowrap align=left valign=top class=\"" . $this->class . "\" id=\"" . $this->class . "\"><b>" . ereg_replace("^\((.*)\)$","\\1",$innerHTML) . $this->icon . "</b></td>\n";
						else
								$this->output .= "<td nowrap ".($this->col_info[$i]->width>0?"width=".$this->col_info[$i]->width." ":"")."align=left valign=top class=\"" . $this->class . "\" id=\"" . $this->class . "\"><b>" . ereg_replace("^\((.*)\)$","\\1",$innerHTML) . $this->icon . "</b></td>\n";
				} else {
						if(ALLOW_TABLE_FILTERING == 1 && false)
								$this->output .= "<td ".($this->col_info[$i]->width>0?"width=".$this->col_info[$i]->width." ":"")."ondblclick=\"Filter('thebody$this->query_run', ".($i-$this->skipped_fields).")\" onclick=\"document.body.style.cursor = 'wait';var myparams='TableSort(\\'thebody$this->query_run\\', ".($i-$this->skipped_fields).", \\'ai\\')'; var tid=setTimeout(myparams,100);\" nowrap align=left valign=top class=\"" . $this->class . "\" id=\"" . $this->class . "\"><b>" . ereg_replace("^\((.*)\)$","\\1",$innerHTML) . $this->icon . "</b></td>\n";
						else
								$this->output .= "<td nowrap ".($this->col_info[$i]->width>0?"width=".$this->col_info[$i]->width." ":"")."align=left valign=top class=\"" . $this->class . "\" id=\"" . $this->class . "\"><b>" . ereg_replace("^\((.*)\)$","\\1",$innerHTML) . $this->icon . "</b></td>\n";
				}
			}
			else
				$this->output .= "<td nowrap align=left ".($this->col_info[$i]->width>0?"width=".$this->col_info[$i]->width." ":"")."valign=top class=\"" . $this->class . "\" id=\"" . $this->class . "\"><b>" . ereg_replace("^\((.*)\)$","\\1",$innerHTML) . $this->icon . "</b></td>\n";				// if we have a vertical table we need to display all data in this
			
			// result-set in the single row
			if($this->options[0] == 'v')
			{
				// if the data is not linked then display normally
				if(!$link)
				{
					for($z=0; $z<count($this->data); $z++)
						$this->output .= "<td class=\"v_data\" valign=top>" . $this->data[$z][$i] . "</td>\n";
				}
				// otherwise loop through and setup the RID for outputting
				else
				{
					for($z=0; $z<count($this->data); $z++)
					{
						if($z==0) $first_item = null($this->data[$z][$i]);
						$this->output .= "<td class=\"v_data\" valign=top><a class=\"heading\" href=\"" . $this->sub_dir . "/" . $this->filename . "/" . $this->current_query . '.' . $this->col_info[$i]->name . "," . $this->query_run . "," . $this->col_info[$i]->name . "," . $first_item . "," . $this->data[$z][$i] . "\">" . $this->data[$z][$i] . "</a></td>\n";
					}
				}
				$this->output .= "</tr>\n";
			}
		}
		
		function is_empty_dir($path) {
			if($d = @opendir($path)) {
				while(false !== ($filename = readdir($d))) {
					if(!in_array($filename, array(".","..","trash")) && !ereg("^[.]", $filename) && is_dir($path."/".$filename)) {
						if(!$this->is_empty_dir($path."/".$filename)) {
							@closedir($d);
							return false;
						}
					} elseif(!in_array($filename, array(".","..","trash")) && !ereg("^[.]", $filename)) {
						@closedir($d);
						return false;
					}
				}
			}
			@closedir($d);
			return true;
		}
		/**
		 * @return void
		 * @desc return formated data array elements
		 */
		function data()
		{
			// if there is a result-set and the table is horizonal or a matrix and
			// there are more than 0 results
			if($this->last_result && (strtolower($this->options[0]) == 'h' || strtolower($this->options[0]) == 'm' || empty($this->options[0])) && ($this->num_rows > 0))
			{
				// handles the table next/back tabulation
				if(!empty($this->limit_vars[1]))
					$ident = $this->limit_vars[1];
				$background='#FFFFFF';
				foreach ($this->data as $one_row)
				{
					if(isset($this->groupindex)) {
						if($one_row[$this->groupindex]!=$previtem) $background=($background=='#FFFFFF'?'#E6E6E6':'#FFFFFF');
						$previtem=$one_row[$this->groupindex];
					}
					if(isset($_GET["ebindr2"])) $this->row_data2($one_row, 0, $background); else $this->row_data($one_row, 0, $background);
					$this->ident++;
				}
			}
		}
		
		/**
		 * @return void
		 * @param item string
		 * @param location integer
		 * @param rowid integer		 
		 * @desc builds the row of data according to item looping
		 */
		function row_data($one_row, $location=0, $background='#FFFFFF')
		{
			//if(!isset($_GET["noheader"])) $mouseover = "onmouseover=\"show_row('" . ($this->ident+1) . "', '" . $this->query_run . "');\"";
			//else 
			global $parse, $browse_auto_bid_dir;
			$mouseover = ""; 
			$this->output .= "<tr id=\"" . ($this->ident+1) . "-" . $this->query_run . "\" " . $mouseover . ">\n";
			$this->currentbid="";
			$listremove="";$rowonclick="";
			foreach ($one_row as $item)
			{
				$item = $this->auto_devide($item, $location);
				$item = preg_replace(array("/\xe2\xae/", "/\xae/", "/\xe0/", "/\xe6/", "/\xe7/", "/\xe8/", "/\xc9/", "/\xe9/", "/\xea/", "/\xeb/", "/\xee/", "/\xef/", "/\xf4/", "/\xfb/", "/\xfc/"), array("&reg;", "&reg;", "&agrave;", "&aelig;", "&ccedil;", "&egrave;", "&Eacute;", "&eacute;", "&ecirc;", "&euml;", "&icirc;", "&iuml;", "&ocirc;", "&ucirc;", "&uuml;"), $item); //str_replace(chr(233),"&eacute;", $item);
				if(!empty($this->auto_devide[$location])) {
					if($item<0) $item="<font color='red'>(".number_format(abs($item),2).")</font>";				}
				if($this->col_info[$location]->name=='$Total') $item="<b>".$item."</b>";
				if((ereg("^\?", $this->col_info[$location]->name) || ereg("\?$", $this->col_info[$location]->name)) && ($item=='y' || $item=='n')) {
					$this->align[$location] = ' align="center"';
					if(strpos($this->linked[$location], "editr"))
						$editr_link = $this->filename . '/' . $this->current_query . "," . $this->query_run . ",yn," . (str_replace('/','',$value_first)) . "," . (str_replace('"','%22',str_replace('#','%23',str_replace('/','',$item)))) . $this->extension ."&editr";
					$item = "<input type=\"checkbox\" onclick=\"this.checked=".(($item=='y')?"true":"false").";".($editr_link?"window.location='$editr_link';":"")."\"".(($item=='y')?"checked":"").">";
				}
				if($location == 0)
					$value_first = null($item);
				// if the data is linked and its not in the skip sub-program and not a matrix
				if($listremove=="" && eregi("^[*]*(b|c|e)id$", $this->col_info[$location]->name) && isset($parse->params["EXCEPTION LIST"])) {
					$listremove="<td><input title='Remove this item from the list' type=checkbox id='except$item'".(ereg("(^|,)".$item."(,|$)",$this->exceptionlist)?"checked":"")."></td>";
					$this->display->variable("exceptionlist", "<input title=\"Remove checked items from list\" type=button value='Remove' onclick='Except()'> <input title=\"Show removed items\" type=button value='Show' onclick='ShowExcept()'".(ereg("[0-9]",$parse->params["EXCEPTION LIST"])?"":" disabled")."> <input title=\"Restore removed items\" type=button value='Restore' onclick='ClearExcept()'".(($this->exceptionlist>"" || ereg("[0-9]",$parse->params["EXCEPTION LIST"])) && $_POST["EXCEPTIONLIST"]=="VIEW"?"":" disabled").">");
				}

				if(eregi("^[(]*bid[)]*$", $this->col_info[$location]->name)) {
					$dblclick = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\" onclick='window.parent.setbid(\"$item\");'";
					$jumpback = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\" style='text-decoration:underline;' onclick='window.parent.setbid(\"$item\"); window.parent.dopage(\"records\");'";
					$this->currentbid=$item;
				} elseif(eregi("^[(]*cid[)]*$", $this->col_info[$location]->name)) {
					if($this->currentbid=="") $this->currentbid=$one_row[0];
					$dblclick = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\" onclick='window.parent.cid=\"$item\"; window.parent.setbid(\"".$this->currentbid."\"); window.parent.cid=\"$item\";'";
					$jumpback = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\" style='text-decoration:underline;' onclick='window.parent.cid=\"$item\"; window.parent.setbid(\"".$this->currentbid."\"); window.parent.cid=\"$item\"; window.parent.dopage(\"records\");'";
				} else {
					$dblclick="";
					$jumpback="";
				}
				if(eregi("^[*]ONCLICK$", $this->col_info[$location]->name)) {
					$rowonclick=$item;
				}
				if(!empty($this->linked[$location]) && empty($this->skip[$location]))
				{
					$atitle=$this->related_desc[$location];
					while(ereg("\[FIELD ([0-9])\]",$atitle,$regs))
						$atitle=ereg_replace("\[FIELD ".$regs[1]."\]", strip_tags($one_row[$regs[1]]),$atitle);
					// key word linking
					if(@in_array($this->linked[$location], $this->key_words))
						$this->output .= "<td $dblclick id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "', '$rowonclick');\" nowrap class=\"data\" valign=top " . $this->align[$location] . "><a title=\"".$this->related_desc[$location]."\" href=\"" . $this->sub_dir . "/" . $this->filename . "/" . $this->current_query . '.' . str_replace('/','',$item) . "," . $this->query_run . "," . (str_replace('/','',$item)) . "," . (str_replace('/','',$value_first)) . "," . (str_replace('/','',$item)) . $this->extension . "\">" . $item . "&nbsp;</a></td>\n";
					// editr linking
					elseif(strpos($this->linked[$location], "editr"))
					{
						$editr_link = $this->filename . '/' . $this->current_query . "," . $this->query_run . "," . str_replace(".editr", "", $this->linked[$location]) . "," . (str_replace('/','',$value_first)) . "," . (str_replace('"','%22',str_replace('#','%23',str_replace('/','',$item)))) . $this->extension ."&editr";
//						$dblclick="ondblclick='window.parent.key1=\"\";' ";
						$this->output .= "<td $dblclick ondblclick='window.parent.key1=\"\";' id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "', '$rowonclick');\" nowrap class=\"data\" valign=top " . $this->align[$location] . "><a ondblclick='window.parent.key1=\"\";' title=\"".$atitle."\" href=\"/" . $editr_link . "\">" . $item . "</a></td>\n";
					}
					// normal related query linking
					else
						$this->output .= "<td $dblclick id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "', '$rowonclick');\" nowrap class=\"data\" valign=top " . $this->align[$location] . "><a title=\"".$atitle."\" href=\"" . $this->sub_dir . "/" . $this->filename . "/" . $this->current_query . '.' . $this->linked[$location] . "," . $this->query_run . "," . $this->linked[$location] . "," . (str_replace('/','',$value_first)) . "," . (str_replace('/','',$item)) . $this->extension . "\">" . $item . "&nbsp;</a></td>\n";
				}
				// not in the skip sub-program or matrix table
				elseif(empty($this->skip[$location]))
				{
					// normal data
					if($this->options[0] != 'm') {
						if(ereg("(BID|CID|MED|SC|MISC|VORP|VIP)FILES", $item, $regs)) {
							$filesexist=false; $filename="";
							$filecid=$this->data[$this->ident][$this->identityrow[strtolower(($regs[1]=="VORP" || $regs[1]=="MED" || $regs[1]=="SC" || $regs[1]=="MISC" || $regs[1]=="VIP"?"CID":$regs[1]))]];
							if(strlen($filecid)<3) $filecid.="XX";
							if(MAKE_ALL_FOLDERS=="YES") {
								@mkdir(DOCS_BASE_DIR.'/'.strtolower($regs[1]));
								@mkdir(DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2));
								@mkdir(DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2));
								$basedir=DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2);
								@chmod(DOCS_BASE_DIR.'/'.strtolower($regs[1]), 0777);
								@chmod(DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2), 0777);
								@chmod(DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2), 0777);
								if(is_array($browse_auto_bid_dir) && ereg("BID", $regs[1]))
									foreach($browse_auto_bid_dir as $onedir) if(!file_exists($basedir."/".$onedir) && (!eregi("member",$onedir))) mkdir($basedir."/".$onedir);
								if(!file_exists($basedir."/trash")) {
									mkdir($basedir."/trash");
								}
							}
							$directory = DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2);
							if($d = @opendir($directory)) {
								while(false !== ($filename = readdir($d)))
									if ($filename != "." && $filename != ".." && $filename!='trash' && !ereg("^[.]", $filename)) { $filelist[]=$filename; $filesexist=true; }
							}
							if(!$this->is_empty_dir($directory)) $item="docs"; else $item="docsnone";
							$context='';
							if($this->networkfolder>'') {
								$context='window.clipboardData.setData("Text","'.str_replace("\\", "\\\\", $this->networkfolder).$regs[1]."\\\\".substr($filecid,strlen($filecid)-2)."\\\\".substr($filecid,0,strlen($filecid)-2).'"); return false;';
							}
							$item="<a oncontextmenu='$context' title=\"Click here to add/manage/view documents associated with this record.\" href=\"javascript:window.parent.FileBrowser('".strtolower($regs[1])."', '".str_replace("X","",$filecid)."')\"><img src='/css/$item.gif' border=0></a>";
					// onclick='document.getElementById(\"FILELIST$filecid\").style.display=\"block\"'><span style=\"display:none;position:absolute\" id='FILELIST$filecid' onmouseout='this.style.display=\"none\"'>$myfiles</span>"//							$item=$directory;
						 } 
						$this->output .= "<td $jumpback id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "', '$rowonclick');\" nowrap class=\"data\" style=\"background:$background\" valign=top " . $this->align[$location] . "  targetBid=\"" . str_replace('\"','',addslashes($one_row[0])) . "\">" . $item . ($item==""?"&nbsp;":"")."</td>\n";
					}
					// matrix linking of data (run on all cells)
					else
					{ // added the started linking variable to bypass invisible columns in 1st field skipping in the matrix tables
						if($location === 0 || ($location == 1 && ($this->col_info[$location]->name{0} == "&" || $this->col_info[$location]->name{0} == "*" ||  $this->col_info[$location]->hidezeros)) || (!$this->started_linking[$this->ident])) {
							$this->output .= "<td id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "', '$rowonclick');\" nowrap class=\"data\" valign=top " . $this->align[$location] . ">" . $item . "&nbsp;</td>\n";
						} else {
							$this->output .= "<td $jumpback id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "');\" nowrap class=\"data\" valign=top " . $this->align[$location] . "><a title=\"".$this->related_desc[$location]."\" href=\"" . $this->sub_dir . "/" . $this->filename . "/" . $this->current_query . '.' . $this->matrix_next . "," . $this->query_run . "," . $this->field[$location] . "," . (str_replace('/','',$value_first)) . "," . (str_replace('/','',$item)) . $this->extension . "\">" . $item . "&nbsp;</a></td>\n";
						}
						// if the next field isn't hidden then we need to start linking
						if($this->col_info[($location+1)]->name{0} != "*") {
							$this->started_linking[$this->ident] = true;
						}
					}
				}
				$location++;
			}
			$this->output .= $listremove."</tr>\n";
		}

		/**
		 * @return void
		 * @param item string
		 * @param location integer
		 * @param rowid integer		 
		 * @desc builds the row of data according to item looping
		 */
		function row_data2($one_row, $location=0, $background='#FFFFFF')
		{
			//if(!isset($_GET["noheader"])) $mouseover = "onmouseover=\"show_row('" . ($this->ident+1) . "', '" . $this->query_run . "');\"";
			//else 
			global $parse, $browse_auto_bid_dir;
			$mouseover = ""; 
			$this->output .= "<tr id=\"" . ($this->ident+1) . "-" . $this->query_run . "\" " . $mouseover . ">\n";
			$this->currentbid="";
			$listremove="";$rowonclick="";
			foreach ($one_row as $item)
			{
				$item = $this->auto_devide($item, $location);
				$item = preg_replace(array("/\xe2\xae/", "/\xae/", "/\xe0/", "/\xe6/", "/\xe7/", "/\xe8/", "/\xc9/", "/\xe9/", "/\xea/", "/\xeb/", "/\xee/", "/\xef/", "/\xf4/", "/\xfb/", "/\xfc/"), array("&reg;", "&reg;", "&agrave;", "&aelig;", "&ccedil;", "&egrave;", "&Eacute;", "&eacute;", "&ecirc;", "&euml;", "&icirc;", "&iuml;", "&ocirc;", "&ucirc;", "&uuml;"), $item); //str_replace(chr(233),"&eacute;", $item);
				if(!empty($this->auto_devide[$location])) {
					if($item<0) $item="<font color='red'>(".number_format(abs($item),2).")</font>";				}
				if($this->col_info[$location]->name=='$Total') $item="<b>".$item."</b>";
				if((ereg("^\?", $this->col_info[$location]->name) || ereg("\?$", $this->col_info[$location]->name)) && ($item=='y' || $item=='n')) {
					$this->align[$location] = ' align="center"';
					if(strpos($this->linked[$location], "editr"))
						$editr_link = $this->filename . '/' . $this->current_query . "," . $this->query_run . ",yn," . (str_replace('/','',$value_first)) . "," . (str_replace('"','%22',str_replace('#','%23',str_replace('/','',$item)))) . $this->extension ."&editr";
					$item = "<input type=\"checkbox\" onclick=\"this.checked=".(($item=='y')?"true":"false").";".($editr_link?"window.location='$editr_link';":"")."\"".(($item=='y')?"checked":"").">";
				}
				if($location == 0)
					$value_first = null($item);
				// if the data is linked and its not in the skip sub-program and not a matrix
				if($listremove=="" && eregi("^[*]*(b|c|e)id$", $this->col_info[$location]->name) && isset($parse->params["EXCEPTION LIST"])) {
					$listremove="<td><input title='Remove this item from the list' type=checkbox id='except$item'".(ereg("(^|,)".$item."(,|$)",$this->exceptionlist)?"checked":"")."></td>";
					$this->display->variable("exceptionlist", "<input title=\"Remove checked items from list\" type=button value='Remove' onclick='Except()'> <input title=\"Show removed items\" type=button value='Show' onclick='ShowExcept()'".(ereg("[0-9]",$parse->params["EXCEPTION LIST"])?"":" disabled")."> <input title=\"Restore removed items\" type=button value='Restore' onclick='ClearExcept()'".(($this->exceptionlist>"" || ereg("[0-9]",$parse->params["EXCEPTION LIST"])) && $_POST["EXCEPTIONLIST"]=="VIEW"?"":" disabled").">");
				}

				if(eregi("^[(]*bid[)]*$", $this->col_info[$location]->name)) {
					$dblclick = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\" onclick='window.parent.ebindr.openBID(\"$item\");'";
					$dblclickeditr = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\" onclick='window.parent.ebindr.openBID(\"$item\", null, null, false);'";
					$jumpback = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\" style='text-decoration:underline;' onclick='window.parent.ebindr.openBID(\"$item\"); window.parent.dopage(\"records\");'";
					$this->currentbid=$item;
				} elseif(eregi("^[(]*cid[)]*$", $this->col_info[$location]->name)) {
					if($this->currentbid=="") $this->currentbid=$one_row[0];
					$dblclick = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\" onclick='window.parent.ebindr.openBID(\"".$this->currentbid."\",false,\"$item\");'";
					$dblclickeditr = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\" onclick='window.parent.ebindr.openBID(\"".$this->currentbid."\",false,\"$item\",false);'";
					$jumpback = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\" style='text-decoration:underline;' onclick='window.parent.ebindr.openBID(\"".$this->currentbid."\",false,\"$item\"); window.parent.dopage(\"records\");'";
				} else {
					$dblclick="";
					$jumpback="";
				}
				if(eregi("^[*]ONCLICK$", $this->col_info[$location]->name)) {
					$rowonclick=$item;
				}
				if(!empty($this->linked[$location]) && empty($this->skip[$location]))
				{
					$atitle=$this->related_desc[$location];
					while(ereg("\[FIELD ([0-9])\]",$atitle,$regs))
						$atitle=ereg_replace("\[FIELD ".$regs[1]."\]", strip_tags($one_row[$regs[1]]),$atitle);
					// key word linking
					if(@in_array($this->linked[$location], $this->key_words))
						$this->output .= "<td $dblclick id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "', '$rowonclick');\" nowrap class=\"data\" valign=top " . $this->align[$location] . "><a title=\"".$this->related_desc[$location]."\" href=\"" . $this->sub_dir . "/" . $this->filename . "/" . $this->current_query . '.' . str_replace('/','',$item) . "," . $this->query_run . "," . (str_replace('/','',$item)) . "," . (str_replace('/','',$value_first)) . "," . (str_replace('/','',$item)) . $this->extension . "\">" . $item . "&nbsp;</a></td>\n";
					// editr linking
					elseif(strpos($this->linked[$location], "editr"))
					{
						$editr_link = $this->filename . '/' . $this->current_query . "," . $this->query_run . "," . str_replace(".editr", "", $this->linked[$location]) . "," . (str_replace('/','',$value_first)) . "," . (str_replace('"','%22',str_replace('#','%23',str_replace('/','',$item)))) . $this->extension ."&editr";
//						$dblclick="ondblclick='window.parent.key1=\"\";' ";
						if ( strpos($editr_link, 'lite button by') > -1 ) {
							$this->output .= "<td $dblclickeditr ondblclick='window.parent.key1=\"\";' id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "', '$rowonclick');\" nowrap class=\"data\" valign=top " . $this->align[$location] . ">" . $item . "</td>\n";
						} else {
							$this->output .= "<td $dblclickeditr ondblclick='window.parent.key1=\"\";' id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "', '$rowonclick');\" nowrap class=\"data\" valign=top " . $this->align[$location] . "><a ondblclick='window.parent.key1=\"\";' title=\"".$atitle."\" href=\"/" . $editr_link . "\">" . $item . "</a></td>\n";
						}
					}
					// normal related query linking
					else
						$this->output .= "<td $dblclick id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "', '$rowonclick');\" nowrap class=\"data\" valign=top " . $this->align[$location] . "><a title=\"".$atitle."\" href=\"" . $this->sub_dir . "/" . $this->filename . "/" . $this->current_query . '.' . $this->linked[$location] . "," . $this->query_run . "," . $this->linked[$location] . "," . (str_replace('/','',$value_first)) . "," . (str_replace('/','',$item)) . $this->extension . "\">" . $item . "&nbsp;</a></td>\n";
				}
				// not in the skip sub-program or matrix table
				elseif(empty($this->skip[$location]))
				{
					// normal data
					if($this->options[0] != 'm') {
						if(ereg("(BID|CID|MED|SC|MISC|VORP|VIP)FILES", $item, $regs)) {
							$filesexist=false; $filename="";
							$filecid=$this->data[$this->ident][$this->identityrow[strtolower(($regs[1]=="VORP" || $regs[1]=="MED" || $regs[1]=="SC" || $regs[1]=="MISC" || $regs[1]=="VIP"?"CID":$regs[1]))]];
							if(strlen($filecid)<3) $filecid.="XX";
							if(MAKE_ALL_FOLDERS=="YES") {
								@mkdir(DOCS_BASE_DIR.'/'.strtolower($regs[1]));
								@mkdir(DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2));
								@mkdir(DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2));
								$basedir=DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2);
								@chmod(DOCS_BASE_DIR.'/'.strtolower($regs[1]), 0777);
								@chmod(DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2), 0777);
								@chmod(DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2), 0777);
								if(is_array($browse_auto_bid_dir) && ereg("BID", $regs[1]))
									foreach($browse_auto_bid_dir as $onedir) if(!file_exists($basedir."/".$onedir) && (!eregi("member",$onedir))) mkdir($basedir."/".$onedir);
								if(!file_exists($basedir."/trash")) {
									mkdir($basedir."/trash");
								}
							}
							$directory = DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2);
							if($d = @opendir($directory)) {
								while(false !== ($filename = readdir($d)))
									if ($filename != "." && $filename != ".." && $filename!='trash' && !ereg("^[.]", $filename)) { $filelist[]=$filename; if(!is_dir($filename)) $filesexist=true; }
							}
							if(!$this->is_empty_dir($directory)) $item="docs"; else $item="docsnone";
							$context='';
							if($this->networkfolder>'') {
								$context='window.clipboardData.setData("Text","'.str_replace("\\", "\\\\", $this->networkfolder).$regs[1]."\\\\".substr($filecid,strlen($filecid)-2)."\\\\".substr($filecid,0,strlen($filecid)-2).'"); return false;';
							}
							$item="<a oncontextmenu='$context' title=\"Click here to add/manage/view documents associated with this record.\" href=\"javascript:window.parent.FileBrowser('".strtolower($regs[1])."', '".str_replace("X","",$filecid)."')\"><img src='/css/$item.gif' border=0></a>";
					// onclick='document.getElementById(\"FILELIST$filecid\").style.display=\"block\"'><span style=\"display:none;position:absolute\" id='FILELIST$filecid' onmouseout='this.style.display=\"none\"'>$myfiles</span>"//							$item=$directory;
						 } 
						$this->output .= "<td $jumpback id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "', '$rowonclick');\" nowrap class=\"data\" style=\"background:$background\" valign=top " . $this->align[$location] . ">" . $item . ($item==""?"&nbsp;":"")."</td>\n";
					}
					// matrix linking of data (run on all cells)
					else
					{ // added the started linking variable to bypass invisible columns in 1st field skipping in the matrix tables
						if($location === 0 || ($location == 1 && ($this->col_info[$location]->name{0} == "&" || $this->col_info[$location]->name{0} == "*" ||  $this->col_info[$location]->hidezeros)) || (!$this->started_linking[$this->ident])) {
							$this->output .= "<td id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "', '$rowonclick');\" nowrap class=\"data\" valign=top " . $this->align[$location] . ">" . $item . "&nbsp;</td>\n";
						} else {
							$this->output .= "<td $jumpback id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "');\" nowrap class=\"data\" valign=top " . $this->align[$location] . "><a title=\"".$this->related_desc[$location]."\" href=\"" . $this->sub_dir . "/" . $this->filename . "/" . $this->current_query . '.' . $this->matrix_next . "," . urlencode($this->query_run) . "," . urlencode($this->field[$location]) . "," . urlencode(str_replace('/','',$value_first)) . "," . urlencode(str_replace('/','',$item)) . $this->extension . "\">" . $item . "&nbsp;</a></td>\n";
						}
						// if the next field isn't hidden then we need to start linking
						if($this->col_info[($location+1)]->name{0} != "*") {
							$this->started_linking[$this->ident] = true;
						}
					}
				}
				$location++;
			}
			$this->output .= $listremove."</tr>\n";
		}
		
		/**
		 * @return void
		 * @param query string
		 * @param output string
		 * @desc reportr wrapper on the get results gateway method. to handle db querying
		 */
		function results($query=null, $output = OBJECT)
		{
			global $parse;
			$this->use_error="";
			if($this->loop_db) $database=$this->loop_db;
			elseif(empty($_COOKIE["reportr_db"])) $database = $_POST["database"];
			elseif(!empty($_COOKIE["reportr_db"])) $database = $_COOKIE["reportr_db"];
			else $database = "hurdman";
			$starttime=explode(" ",microtime());
			$this->select($database);
			$temp = $this->get_results($query, $output);
			$this->select($this->variables["db"]);

			$endtime=explode(" ",microtime());
			$timetook=number_format($endtime[0]+$endtime[1]-$starttime[0]-$starttime[1],3);
			$parse->timetook+=$timetook;
//			if($this->use_error>"") die($this->last_query.":".$this->use_error);
			if($query>""/* && ereg(",HURDMAN,",$parse->params["keys"])*/ && $this->use_error>"") $parse->update_errors.="<!-- ***ERROR***\r\nQUERY: ".htmlspecialchars($query)."\r\nERROR: ".htmlspecialchars($this->use_error)."\r\n-->\r\n";
			elseif($query>""/* && ereg(",HURDMAN,",$parse->params["keys"])*/) $parse->update_errors.="<!--\r\nQUERY: ".htmlspecialchars($query)."\r\nROWS: ".$this->affected_rows."\r\nTIME: ".$timetook." seconds\r\n-->";
			return $temp;
		}

		/**
		 * @return string
		 * @desc set class global limiting variables
		 */		
		function limit_vars()
		{
			if(!empty($this->limit_vars[1]))
				$this->ident = $this->limit_vars[1];
			else
				$this->ident = 0;
		}
		
		/**
		 * @return string
		 * @desc finds related queries according to naming standards
		 */
		function related()
		{
			$allowed = (sizeof(explode(".", $this->current_query))); // allowed locations
			$related = $this->background->get_results("SELECT MergeCode as name FROM " . $this->query_db."." .$this->query_tbl . " WHERE MergeCode LIKE '" . $this->current_query . ".%'");
//			if($this->background->num_rows == 0)
				$relatedlocal = $this->background->get_results("SELECT MergeCode as name FROM " .$this->query_tbl . " WHERE MergeCode LIKE '" . $this->current_query . ".%'");
            $related=array_merge((!is_array($related)?array():$related),(!is_array($relatedlocal)?array():$relatedlocal));
			if(sizeof($related) > 0)
			{
				$i=0;
				foreach($related as $row)
				{
					$name = explode(".", $row["name"]);
					$location = (sizeof($name)-1);
					if($location == $allowed)
						$related_src[$i] = $name[$location];
					if($name[$location] == "editr" && $location == ($allowed+1))
						$related_src[$i] = $name[($location-1)] . '.' . $name[$location];
					$i++;
				}
				$this->related_queries = $related_src;
			}
			return $related_src;
		}
		
		/**
		 * @return string
		 * @desc returns the heirarchy links for navigation
		 */
		function heirarchy()
		{
			// heirarchy linking options;
			$hier = explode(".", $this->current_query);
			$heirarchy .= '<table border="0" cellpadding="0" cellspacing="0"><tr><td class="tabs" id="deselected">&nbsp;</td>';
			$heirarchy2 = '<div class="breadcrumbs">';
			for($h=0; $h<sizeof($hier); $h++)
			{
				if(($h+1) == sizeof($hier))
				{
					if(!empty($this->variables[4]) && $hier[$h] != $this->variables[4]) {
						$heirarchy .= '<td class="tabs" id="selected"><b><a class="heirarchy" href="#">' . $hier[$h] . ' [' . $this->variables[4] . ']</a></b></td>';
						$heirarchy2 .= '<div class="active"><a href="#">' . $hier[$h] . ' [' . $this->variables[4] . ']</a></div>';
					} else {
						$heirarchy .= '<td class="tabs" id="selected"><b><a class="heirarchy" href="#">' . $hier[$h] . '</a></b></td>';
						$heirarchy2 .= '<div class="active"><a href="#">' . $hier[$h] . '</a></div>';
					}
				}
				else
				{
					if(!empty($hier[$h-1]))
						$last .= $hier[$h-1] . '.';
					$heirarchy .= '<td class="tabs" id="deselected"><a class="heirarchy" href="' . $this->sub_dir . '/' . $this->filename . '/' . $last .  $hier[$h] . '">' . $hier[$h] . '</a></td>';
					$heirarchy2 .= '<div><a href="' . $this->sub_dir . '/' . $this->filename . '/' . $last .  $hier[$h] . '?ebindr2=y">' . $hier[$h] . '</a></div>';
				}
			}
			$heirarchy .= '</tr></table>';
			$heirarchy2 .= '</div>';
			return ( isset($_GET['ebindr2']) ? $heirarchy2 : $heirarchy );	
		}
		
		/**
		 * @return void
		 * @param data string
		 * @param loop integer
		 * @desc checks to see if we need to auto devide it and does the math work and returns cell value
		 */
		function auto_devide($data, $loop) {
			if(!empty($this->auto_devide[$loop])) {
//				if($data=='A & A Express, Inc.') print_r($this->auto_devide);
				if($data == '0') $cell_value = '-';
				elseif($data>0) $cell_value = number_format(($data/100), 2);
				else $cell_value = $data/100;
				return $cell_value;
			} else {
				return $data;
			}
		}
		
		/**
		 * @return array
		 * @desc fetches data from already queried result set
		 */
		function get_data()
		{
				return $this->data = $this->results(null,ARRAY_N);
		}
		
		/**
		 * @return void
		 * @desc sets the variable extensions
		 */
		function variable_set()
		{
			global $parse, $device;
			$this->extension = "/?";
			for($i=0; $i<count($parse->query["params"]); $i++)
			{
				if($i == (sizeof($parse->query["params"])-1)) $this->extension .= $parse->query["params"][$i][0] . "=" . $parse->query["params"][$i][1];
				else $this->extension .= $parse->query["params"][$i][0] . "=" . $parse->query["params"][$i][1] . "&";
				$setinputs.="<input type=hidden name='".$parse->query["params"][$i][0]."' id='".$parse->query["params"][$i][0]."' value=\"".stripslashes($parse->query["params"][$i][1])."\">\r\n";
			}
			$device->variable("setinputs", $setinputs);
		}
		
		/**
		 * @return void
		 * @desc recieves api calls and returns serialized data
		 */
		function api()
		{
			global $task, $parse, $variables, $reportr;
			if(ALLOW_API_CALLS == 1) {
				$query_name = $variables[1];
				if(isset($_GET["USEDEFAULTS"])) $parse->adopt(true); // we need some parameters
				$parse->resolve_double($parse->resolve($task->get_var("SELECT sqlstatement AS query FROM " . QUERY_TABLE . " WHERE MergeCode = '" . $query_name . "'")));
				if($parse->advance() || isset($_GET["USEDEFAULTS"])) // parameters are satisfied
				{
					$parse->resolve_double($parse->output); // parse out query groups
					$reportr->control('api'); // looping control and template additions
				}
				else // prompt for parameters
					$parse->adopt(); // we need some parameters
	
				echo $reportr->buffer;
			}
		}
		
/* OLD Export
		/**
		 * @return void
		 * @desc will parse through queries and query groups to find results and push them to an
		 * xls (excel spreadsheet) export
		function export()
		{
			global $parse;
			for($i=1; $i<=sizeof($parse->query); $i++)
			{
				$this->ex_table_header[$this->row_run]=$parse->query[$i]["options"][3];
				for($c=0; $c<sizeof($parse->query[$i]["query"]); $c++)
				{
					$this->results($parse->resolve(calc_found_rows(limit($parse->query[$i]["query"][$c], $parse->query[$i]["options"][1]))));
					$this->construct_export();
				}
			}
			if(!isset($_GET[EXPORT_OUTPUT])) $_GET[EXPORT_OUTPUT]='xls';
			header('Expires: ' . gmdate('D, d M Y H:i:s',time()-4800) . ' GMT');
			if(!isset($_GET[EXPORT_FILENAME])) $_GET[EXPORT_FILENAME]=$parse->params["exportrquery"];
			header('Content-Disposition: attachment; filename="' . $_GET[EXPORT_FILENAME] . '.'.$_GET[EXPORT_OUTPUT].'"');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');			
				
			for($h=0; $h<count($this->ex_field_names); $h++)
			{
				if(eregi("^select ", $this->ex_table_header[$h]))
					print strip_tags(ereg_replace("<br>","\n",$this->background->get_var($this->ex_table_header[$h])))."\r\n";
				elseif($this->ex_table_header[$h])
					print strip_tags(ereg_replace("<br>","\n",$this->ex_table_header[$h]))."\r\n";
				if(!isset($_GET[suppressfieldheaders])) print strip_tags(ereg_replace("<br>","\n",$this->ex_field_names[$h])) . "\r\n";
				for($u=0; $u<count($this->ex_items[$h]); $u++)
				{
					for($t=0; $t<count($this->ex_items[$h][$u]); $t++) {
							print str_replace("&lt;","<",str_replace("&gt;",">",strip_tags($this->ex_items[$h][$u][$t])));
					}
					print "\r\n";
				}
			}
			
			exit();
		}
		
		/**
		 * @return void
		 * @desc constructs the fields and result sets for proper exporting replaces this->construct for export purposes
		function construct_export()
		{
			if($_GET[EXPORT_OUTPUT]=='csv') { $encloser='"'; $delimiter=","; } else { $encloser=""; $delimiter="\t"; }
			if($this->col_info)
			{
//			print_r($this->col_info);
	//		exit();
				$temparray=$this->results(null,ARRAY_N);
				for($i=0; $i<count($this->col_info); $i++)
				{
					$this->col_info[$i]->hidezeros=false;
					if(ereg(" [$]$", $this->col_info[$i]->name)) {
						$this->col_info[$i]->hidezeros=true;
						for($z=0; $z<count($temparray); $z++)
						{
							if(null($temparray[$z][$i])!=0) $this->col_info[$i]->hidezeros=false;
						}
					} 
					if(!$this->col_info[$i]->hidezeros) {
						$this->ex_field_names[$this->row_run] .= '"'.$this->col_info[$i]->name.'"';
						if(($i+1) != count($this->col_info))
							$this->ex_field_names[$this->row_run] .= $delimiter;
					}
				}
				if($this->last_result)
				{
					$g=0;
					foreach ($temparray as $one_row)
					{
						$d=0; $hiddenfields=0;
						for($jj=0;$jj<sizeof($one_row);$jj++)
						{
							$item=$one_row[$jj];
							if(strpos($this->col_info[$d]->name,"$")>-1) $item=number_format($item/=100,2);
							$item = eregi_replace("<[^>]*>","",$item);
							if($encloser=="" && ereg("\r\n",$item)) $item=ereg_replace("\r\n", "\n",ereg_replace("^(.*\r\n.*)$", "\"\\1\"",str_replace('"','""', $item)));
							if($this->col_info[$d]->hidezeros)
								$hiddenfields++;
							else
								$this->ex_items[$this->row_run][$g][$d-$hiddenfields] = $encloser.str_replace($encloser, $encloser.$encloser, $item).$encloser.($jj==(sizeof($one_row)-1)?"":$delimiter); //str_replace(",", "", $item) . "\t";
							$d++;
						}
							
						$g++;
					}
				}
				//}
			}
			$this->row_run++;
		}
*/
function exportxml() {
	global $parse;
	// default export output type
	if(!isset($_GET[EXPORT_OUTPUT]))
		$_GET[EXPORT_OUTPUT] = 'xls';
	$_GET[EXPORT_OUTPUT]=str_replace("xls","xml",$_GET[EXPORT_OUTPUT]);
	// default delimiters for csv
	if($_GET[EXPORT_OUTPUT]=='csv') {
		$encloser = '"';
		$delimiter = ",";
	} elseif($_GET[EXPORT_OUTPUT]=="xml") {
		$encloser = '';
		$delimited = '';
	} else {
		$encloser = "";
		$delimiter = "\t";
	}
	unset($this->col_info);
	
	// set the time headers for the export
	header('Expires: ' . gmdate('D, d M Y H:i:s',time()-4800) . ' GMT');
	// if the export filename isn't availiabe get it from the parser prameters for "exportquery"
	if(!isset($_GET[EXPORT_FILENAME]))
		$_GET[EXPORT_FILENAME] = $parse->params["exportrquery"];
	// force the headers to attache a file
	header('Content-Disposition: attachment; filename="' . $_GET[EXPORT_FILENAME] . '.' . str_replace("xml","xls",$_GET[EXPORT_OUTPUT]) . '"');
	// force the browse to revalidate and make it public
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	if($_GET[EXPORT_OUTPUT] == "xml") {
		// get the xml file properties from the database
		$query = mysql_query("select replace(mergecode, 'xml.setting.', '') as `name`, sqlstatement from mergequery where mergecode like 'xml.setting.%'");
		while($row = mysql_fetch_row($query)) {
			$xmloptions[$row[0]] = $row[1];
		}

		echo $xmloptions["header"];
		echo $xmloptions["properties"];
		echo $xmloptions["workbook"];
		echo str_replace("[xml.setting.dynamic styles]", "", $xmloptions["styles"]);

		print '<Worksheet ss:Name="Export">';
		print '<Table ss:ExpandedColumnCount="1000" ss:ExpandedRowCount="64000" ';
		print 'x:FullColumns="1" x:FullRows="1">';
	}
	// main loop. finds all the queries beging run. parser found them
	for ($q=1; $q<=sizeof($parse->query); $q++) {
	
		// find multiple queries || loop #2
		for ($j=0; $j<sizeof($parse->query[$q]["query"]); $j++) {

			// find the results for the current query
			$tmp = mysql_query (
					$parse->resolve (
						calc_found_rows (
							limit ($parse->query[$q]["query"][$j], $parse->query[$q]["options"][1])
						)
					)
				);
			unset($columnsizes);

			$y = 0;
			while($y < @mysql_num_fields($tmp)) {
				$this->col_info[$y] = @mysql_fetch_field($tmp);
				$columnsizes[$y]=0;
				if($columnsizes[$y]<strlen($this->col_info[$y]->name)) $columnsizes[$y]=strlen($this->col_info[$y]->name);
				$y++;
			}
				
			$numFields = (@mysql_num_fields($tmp)-1);

			$rowcount=0;
			while(($data = mysql_fetch_row($tmp)) && $rowcount<100) {
				$columncount=0;
				foreach($data as $datum) { 
					if($columnsizes[$columncount]<strlen($datum)) $columnsizes[$columncount]=strlen($datum);
					$columncount++;
				}
				$rowcount++;
			}
			foreach($columnsizes as $columnsize) echo "<Column ss:Width=\"".($columnsize*6)."\"/>\n";
			mysql_data_seek($tmp,0);
			// display table header (and see if there is a select in it)
			if (eregi("^select ", $parse->query[$q]["options"][3])) {
				if($_GET[EXPORT_OUTPUT] == "xml") {
					print	'<Row><Cell ss:MergeAcross="' . $numFields . '"';
					print	' ss:StyleID="table_description"><Data ss:Type="String">';
					print	strip_tags (
								ereg_replace("<br>", "\n" , $this->background->get_var (
									$parse->query[$q]["options"][3])
								)
							);
					print	'</Data></Cell></Row>';
				} else {
					print	strip_tags (
								ereg_replace("<br>" , "\n" , $this->background->get_var (
									$parse->query[$q]["options"][3])
								)
							) . "\r\n";
				}
			} elseif ($parse->query[$q]["options"][3]) { // if there is a header w/o a select in it
				if($_GET[EXPORT_OUTPUT] == "xml") {
						print	'<Row><Cell ss:StyleID="table_description"><Data ss:Type="String">';
						print	strip_tags (
									ereg_replace("<br>", "\n" , $this->background->get_var (
										$parse->query[$q]["options"][3])
									)
								) . "\r\n";
						print	'</Data></Cell></Row>';
				} else {
					print	strip_tags (
								ereg_replace("<br>" , "\n" , $parse->query[$q]["options"][3])
							) . "\r\n";
				}
			} else // otherwise there is no header
				print	"";
						
			// find the field name details
			if (!isset( $_GET[suppressfieldheaders])) {
				if($_GET[EXPORT_OUTPUT] == "xml") {
					print	'<Row>';
					for ($i=0; $i<count($this->col_info); $i++)
					{
						print	'<Cell ss:StyleID="field_name"><Data ss:Type="String">';
						print	strip_tags(
									ereg_replace("<br>" , "\n" , $this->col_info[$i]->name)
								) . ((($i+1)!=count($this->col_info)) ? $delimiter : "");
						print	'</Data></Cell>';
					}
					print "</Row>";
				} else {
					for ($i=0; $i<count($this->col_info); $i++)
					{
						print	strip_tags(
									ereg_replace("<br>" , "\n" , '"' . $this->col_info[$i]->name . '"')
								) . ((($i+1)!=count($this->col_info)) ? $delimiter : "");
					}
					print "\r\n";
				}
			}
			while($data = mysql_fetch_row($tmp)) {
				// DATA loop
				// each field loop
				// field loop identifier
				$loop = 0; $hiddenfields = 0;
				if($_GET[EXPORT_OUTPUT] == "xml") {
					print	'<Row>';
				}
				foreach($data as $set) {
					$numberofFields = sizeof ($data);
					if(strpos($this->col_info[$loop]->name,"$")>-1)
						$set = number_format ($set/=100,2);
					
					$set = eregi_replace ("<[^>]*>", "", $set);

					if ($encloser == "" && ereg("\r\n" , $set))
						$set =	ereg_replace ("\r\n", "\n", ereg_replace (
									"^(.*\r\n.*)$", "\"\\1\"", str_replace(
										'"', '""', $set)
									)
								);
								
					if ($this->col_info[$loop]->hidezeros)
						$hiddenfields++;
					else {
						if($_GET[EXPORT_OUTPUT] == "xml") {
							print '<Cell><Data ss:Type="String">';
						}
						print	str_replace("&lt;", "<", 
									str_replace (
										"&gt;", ">", strip_tags (
											$encloser . 
											str_replace($encloser, $encloser.$encloser, $set) . 
											$encloser . 
											($numberofFields == (sizeof($set)-1) ? "" : $delimiter)
										)
									)
								);
						if($_GET[EXPORT_OUTPUT] == "xml") {
							print '</Data></Cell>';
						}
					}
					$loop++;
				}
				if($_GET[EXPORT_OUTPUT] != "xml") { print "\r\n"; } else { print	'</Row>'; }
			}
		}
		@mysql_free_result($tmp);
		unset($tmp, $this->ex_field_names, $hiddenfields, $loop, $set);
	}
	if($_GET[EXPORT_OUTPUT] == "xml") {
		print '</Table>';
		print '<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">';
		print '<Selected/>';
		print '<ProtectObjects>False</ProtectObjects>';
		print '<ProtectScenarios>False</ProtectScenarios>';
		print '</WorksheetOptions>';
		print '</Worksheet>';
		echo $xmloptions["footer"];
	}
}

function export_json() {
	global $parse;
	unset($this->col_info);
	// main loop. finds all the queries beging run. parser found them
	for ($q=1; $q<=sizeof($parse->query); $q++) {
	
		// find multiple queries || loop #2
		for ($j=0; $j<sizeof($parse->query[$q]["query"]); $j++) {

			// display table header (and see if there is a select in it)
			if (eregi("^select ", $parse->query[$q]["options"][3]))
				$json[]=array("desc"=>strip_tags (
							ereg_replace("<br>" , "\n" , $this->background->get_var (
								$parse->query[$q]["options"][3])
							)
						));
			elseif ($parse->query[$q]["options"][3])
				$json[]=array("desc"=>strip_tags (ereg_replace("<br>" , "\n" , $parse->query[$q]["options"][3])));
			else
				$json[]=array("desc"=>"");

						
			// find the results for the current query
			if(ereg("OTHERSERVER:([^:]*):([^:]*):([^:]*):([^:.]*)[.]", $parse->query[$q]["query"][$j], $regs) || ereg("OTHERSERVER[.]", $parse->query[$q]["query"][$j])) {
				if(sizeof($regs)>1) {
					$this->otherhost=$regs[1];
					$this->otheruser=$regs[2];
					$this->otherpass=$regs[3];
					$this->otherdbname=$regs[4];
				}
				if(!$this->otherdbh) $this->otherdbh=mysql_connect(str_replace(";",":",$this->otherhost), $this->otheruser, $this->otherpass);
				$mylink=$this->otherdbh;
				$parse->query[$q]["query"][$j]=str_replace("OTHERSERVER.", "", ereg_replace("OTHERSERVER:[^:]*:[^:]*:[^:]*:[^:.]*[.]","",$parse->query[$q]["query"][$j]));
			} else $mylink=$this->dbh;
			$mysqlstatement=$parse->resolve (
						calc_found_rows (
							limit ($parse->query[$q]["query"][$j], $parse->query[$q]["options"][1])
						)
					);
			$tmp = mysql_query (
					$mysqlstatement,$mylink
				);
			if(!$tmp) echo "//".$mysqlstatement."\r\n\r\n".mysql_error($mylink);
			while(@$row=mysql_fetch_array($tmp, MYSQL_ASSOC)) $json[sizeof($json)-1]["data"][]=$row;

		}
		@mysql_free_result($tmp);
		unset($tmp, $this->ex_field_names, $hiddenfields, $loop, $set);
	}
	if(empty($json)) $content="//No data";
	else {
		$obj = new stdClass;
		$obj->resultset = $json;
		$content=json_encode($obj);
		$content = preg_replace(array("/\xe2\xae/", "/\xae/", "/\xe0/", "/\xe6/", "/\xe7/", "/\xe8/", "/\xc9/", "/\xe9/", "/\xea/", "/\xeb/", "/\xee/", "/\xef/", "/\xf4/", "/\xfb/", "/\xfc/"), array("&reg;", "&reg;", "&agrave;", "&aelig;", "&ccedil;", "&egrave;", "&Eacute;", "&eacute;", "&ecirc;", "&euml;", "&icirc;", "&iuml;", "&ocirc;", "&ucirc;", "&uuml;"), $content); //str_replace(chr(233),"&eacute;", $item);
	}
	echo $content;
}

function exportv2() {
	global $parse;
	if($_GET[EXPORT_OUTPUT]=="xml") return $this->exportxml();
	// default export output type
	if(!isset($_GET[EXPORT_OUTPUT]))
		$_GET[EXPORT_OUTPUT] = 'xls';
	// default delimiters for csv
	if($_GET[EXPORT_OUTPUT]=='csv' || $_GET[EXPORT_DELIM]==",") {
		$encloser = '"';
		$delimiter = ",";
	} elseif($_GET[EXPORT_OUTPUT]=='rtf') {
		$encloser="";
		$delimiter=" \\intbl\\cell ";
	} else {
		$encloser = "";
		$delimiter = "\t";
	}
	unset($this->col_info);
	
	// set the time headers for the export
	header('Expires: ' . gmdate('D, d M Y H:i:s',time()-4800) . ' GMT');
	// if the export filename isn't availiabe get it from the parser prameters for "exportquery"
	if(!isset($_GET[EXPORT_FILENAME]))
		$_GET[EXPORT_FILENAME] = $parse->params["exportrquery"];
	
	if( $_GET[EXPORT_OUTPUT] == 'xlsx' ) {
		include_once("/home/serv/library/xlsxwriter.class.php");
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $_GET[EXPORT_FILENAME] . '.xlsx"');
		header('Cache-Control: max-age=0');
		$writer = new XLSXWriter();
		$sheet=1;
	} elseif( $_GET[EXPORT_OUTPUT] != 'apps' ) {
		// force the headers to attache a file
		header( "Content-Type: application/octet-stream" ); 
		header('Content-Disposition: attachment; filename="' . $_GET[EXPORT_FILENAME] . '.' . $_GET[EXPORT_OUTPUT] . '"');
		// force the browse to revalidate and make it public
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');	
	}
	$this->query_run=0;
	
	// we need to write this to a temporary file cause we'll run out of memory on a big file
	if($_GET[EXPORT_OUTPUT]=='apps') {
		$appsfilename = '/tmp/' . microtime(true);
	}
	

	// main loop. finds all the queries beging run. parser found them
	for ($q=1; $q<=sizeof($parse->query); $q++) {
	
		// find multiple queries || loop #2
		for ($j=0; $j<sizeof($parse->query[$q]["query"]); $j++) {
			$ignoreSheet = false;

			// display table header (and see if there is a select in it)
			if (eregi("^select ", $parse->query[$q]["options"][3])) {
				if( $_GET[EXPORT_OUTPUT] == 'apps' ) {
					file_put_contents($appsfilename, strip_tags (
							ereg_replace("<br>" , "\n" , $this->background->get_var (
								$parse->query[$q]["options"][3])
							)
						) . "\r\n", FILE_APPEND );
				} elseif( $_GET[EXPORT_OUTPUT]=='xlsx' ) {
				} else {
				print	strip_tags (
							ereg_replace("<br>" , "\n" , $this->background->get_var (
								$parse->query[$q]["options"][3])
							)
						) . "\r\n";
				}
			} elseif ($parse->query[$q]["options"][3]) {
				if( $_GET[EXPORT_OUTPUT] == 'apps' ) {
					file_put_contents( $appsfilename, strip_tags (
							ereg_replace("<br>" , "\n" , $parse->query[$q]["options"][3])
						) . "\r\n", FILE_APPEND );
				} elseif( $_GET[EXPORT_OUTPUT]=='xlsx' ) {
				} else {
				print	strip_tags (
							ereg_replace("<br>" , "\n" , $parse->query[$q]["options"][3])
						) . "\r\n";
				}
			} else
				print	"";

							if(eregi("order by (.*)$",$parse->query[$q]["query"][$j], $regs)) {
								$this->doorderlinks=true;
								$regs[1]=ereg_replace(" limit.*","",$regs[1]);
								if(isset($_GET["FIELD_ORDERBY"])) {
									$_REQUEST["FIELD_ORDERBY"]=$_GET["FIELD_ORDERBY"];
									$_REQUEST["TABLE_ORDERBY"]=$_GET["TABLE_ORDERBY"];
									$_REQUEST["FIELD_ORDERDIR"]=$_GET["FIELD_ORDERDIR"];
								}
								if(isset($_REQUEST["FIELD_ORDERBY"]) && $_REQUEST["TABLE_ORDERBY"]==($this->query_run+1)) {
									$tempsql=eregi_replace(" as "," \x02 ",$parse->query[$q]["query"][$j]);
									$this->current_order_display=$_REQUEST["FIELD_ORDERBY"];
									if(eregi("date_format\(max\(([^,]*))[^\x02]*) \x02 ".$_REQUEST["FIELD_ORDERBY"], $tempsql, $regs2)) $this->current_order=$regs2[1];
									elseif(eregi("date_format\(([^,]*)[^\x02]*) \x02 ".$_REQUEST["FIELD_ORDERBY"], $tempsql, $regs2)) $this->current_order=$regs2[1]; 
									else $this->current_order=$_REQUEST["FIELD_ORDERBY"]; 
									$this->current_order_direction=$_REQUEST["FIELD_ORDERDIR"];
									$parse->query[$q]["query"][$j]=str_ireplace("order by ".str_replace("(","(",str_replace(")",")",$regs[1])),"order by `".str_replace(".","`.`",$this->current_order)."` ".$this->current_order_direction,$parse->query[$q]["query"][$j]);
								}
							}


						
			// find the results for the current query
			if(ereg("OTHERSERVER:([^:]*):([^:]*):([^:]*):([^:.]*)[.]", $parse->query[$q]["query"][$j], $regs) || ereg("OTHERSERVER[.]", $parse->query[$q]["query"][$j])) {
				if(sizeof($regs)>1) {
					$this->otherhost=$regs[1];
					$this->otheruser=$regs[2];
					$this->otherpass=$regs[3];
					$this->otherdbname=$regs[4];
				}
				if(!$this->otherdbh) $this->otherdbh=mysql_connect(str_replace(";",":",$this->otherhost), $this->otheruser, $this->otherpass);
				$mylink=$this->otherdbh;
				$parse->query[$q]["query"][$j]=str_replace("OTHERSERVER.", "", ereg_replace("OTHERSERVER:[^:]*:[^:]*:[^:]*:[^:.]*[.]","",$parse->query[$q]["query"][$j]));
			} else $mylink=$this->dbh;
			$mysqlstatement=$parse->resolve (
						calc_found_rows (
							limit ($parse->query[$q]["query"][$j], $parse->query[$q]["options"][1])
						)
					);
			$this->query_run++;
			$tmp = mysql_query (
					$mysqlstatement,$mylink
				);
			if(!$tmp) echo $mysqlstatement."\r\n\r\n".mysql_error($mylink);
			$y = 0;$mycols=array();
			while($y < @mysql_num_fields($tmp)) {
				$this->col_info[$y] = @mysql_fetch_field($tmp);
				if( $_GET[EXPORT_OUTPUT]=='xlsx' ) {
					if ($this->col_info[$y]->name == '*id' || $this->col_info[$y]->name == '*id2' || $this->col_info[$y]->name == '<br>' || $this->col_info[$y]->name == '*title' || $this->col_info[$y]->name == '*title2' || $this->col_info[$y]->name == 'NULL') {
						$ignoreSheet = true;
					} else {
						$mycols[]=$this->col_info[$y]->name;
					}
				} else {
					$mycols[]=$this->col_info[$y]->name;
				}
				$y++;
			}
			

				
			// find the field name details
			if (!isset( $_GET[suppressfieldheaders])) {
				for ($i=0; $i<count($this->col_info); $i++)
				{
					if( $_GET[EXPORT_OUTPUT]=='apps' ) {
						file_put_contents( $appsfilename, strip_tags(
								ereg_replace("<br>" , "\n" , '"' . $this->col_info[$i]->name . '"')
							) . ((($i+1)!=count($this->col_info)) ? $delimiter : ""), FILE_APPEND);
					} elseif( $_GET[EXPORT_OUTPUT]=='xlsx' ) {
					} else {
					print	strip_tags(
								ereg_replace("<br>" , "\n" , '"' . $this->col_info[$i]->name . '"')
							) . ((($i+1)!=count($this->col_info)) ? $delimiter : "");
					}
				}
				if( $_GET[EXPORT_OUTPUT]=='apps' ) {
					file_put_contents( $appsfilename, "\r\n", FILE_APPEND );
				} elseif( $_GET[EXPORT_OUTPUT]=='xlsx' ) {
					if (!$ignoreSheet) $writer->writeSheetRow("Sheet".$sheet, $mycols);
				} else print "\r\n";
			}
			
			while($data = mysql_fetch_row($tmp)) {
				// DATA loop
				// each field loop
				// field loop identifier
				$loop = 0; $hiddenfields = 0;
				$displaydata=array();
				foreach($data as $set) {
					$numberofFields = sizeof ($data);
					if(strpos($this->col_info[$loop]->name,"$")>-1)
						$set = number_format ($set/=100,2);
					
					$set = eregi_replace ("<[^>]*>", "", $set);

					if ($encloser == "" && ereg("\r\n" , $set))
						$set =	ereg_replace ("\r\n", "\n", ereg_replace (
									"^(.*\r\n.*)$", "\"\\1\"", str_replace(
										'"', '""', $set)
									)
								);
					if($delimiter=="\t" && strpos($set,"\t")!==false ) $enclosertemp='"'; else $enclosertemp=$encloser;								
					if ($this->col_info[$loop]->hidezeros)
						$hiddenfields++;
					else
						if( $_GET[EXPORT_OUTPUT]=='apps' ) {
							file_put_contents( $appsfilename, str_replace("&lt;", "<", 
									str_replace (
										"&gt;", ">", strip_tags (
											$enclosertemp . 
											str_replace($enclosertemp, $enclosertemp.$enclosertemp, $set) . 
											$enclosertemp . 
											($numberofFields == (sizeof($set)-1) ? "" : $delimiter)
										)
									)
								), FILE_APPEND );
						} elseif( $_GET[EXPORT_OUTPUT]=='xlsx' ) {
							$displaydata[]=$set;
						} else {
						print	str_replace("&lt;", "<", 
									str_replace (
										"&gt;", ">", strip_tags (
											$enclosertemp . 
											str_replace($enclosertemp, $enclosertemp.$enclosertemp, $set) . 
											$enclosertemp . 
											($numberofFields == (sizeof($set)-1) ? "" : $delimiter)
										)
									)
								);
						}
					$loop++;
				}
				if( $_GET[EXPORT_OUTPUT] == 'apps' ) file_put_contents( $appsfilename, "\r\n", FILE_APPEND );
				elseif( $_GET[EXPORT_OUTPUT]=='xlsx' ) {
					if (!$ignoreSheet) $writer->writeSheetRow("Sheet".$sheet, $displaydata);
				} else print "\r\n";
			}
/*			if( $_GET[EXPORT_OUTPUT] == 'xlsx' ) {
				$writer->finalizeSheet("Sheet".($sheet++));
			}*/
			if (!$ignoreSheet) $sheet++;
		}
		
		if($_GET[EXPORT_OUTPUT]=='xls') print "\r\nReport generated ".date("m/d/Y g:i A")."\r\n";
		@mysql_free_result($tmp);
		unset($tmp, $this->ex_field_names, $hiddenfields, $loop, $set);
	}
		if( $_GET[EXPORT_OUTPUT] == 'xlsx' ) {
			$writer->writeToStdOut();//like echo $writer->writeToString();
		}
		if( $_GET[EXPORT_OUTPUT] == 'apps' ) {
			include "/home/serv/public_html/ebindr/includes/googleapps.php";
			if( !googledrive::import( $_GET[EXPORT_FILENAME] . ".tsv", file_get_contents($appsfilename) ) ) {
				echo 'error:noacct';
				exit;
			}
			unset($appsfilename);
			//echo $appsfilename;
			//exit;
		}	

}

		/**
		 * @return void
		 * @desc will parse through queries and query groups to find results and push them to an
		 * xls (excel spreadsheet) export
		 */
		function export()
		{
			global $parse;
			$this->query_run=0;
			if(!isset($_GET[EXPORT_OUTPUT])) $_GET[EXPORT_OUTPUT]='xls';
			header('Expires: ' . gmdate('D, d M Y H:i:s',time()-4800) . ' GMT');
			if(!isset($_GET[EXPORT_FILENAME])) $_GET[EXPORT_FILENAME]=$parse->params["exportrquery"];
			header( "Content-Type: application/octet-stream" ); 
			header('Content-Disposition: attachment; filename="' . $_GET[EXPORT_FILENAME] . '.'.$_GET[EXPORT_OUTPUT].'"');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');	
			if($_GET[EXPORT_OUTPUT]=='rtf') {
				list($part1,$part2)=explode("[exportword]",$this->background->get_var("select content from ".QUERY_DB.".document where title='ExportWord.rtf'"));
				list($part1,)=explode("}}\ltrrow", $part1, 2);
				echo $part1."}}";
			}
			for($i=1; $i<=sizeof($parse->query); $i++)
			{
				$this->ex_table_header[$this->row_run]=$parse->query[$i]["options"][3];
				for($c=0; $c<sizeof($parse->query[$i]["query"]); $c++)
				{
							if(eregi("order by (.*)$",$parse->query[$i]["query"][$c], $regs)) {
								$this->doorderlinks=true;
								$regs[1]=ereg_replace(" limit.*","",$regs[1]);
								if(isset($_GET["FIELD_ORDERBY"])) {
									$_REQUEST["FIELD_ORDERBY"]=$_GET["FIELD_ORDERBY"];
									$_REQUEST["TABLE_ORDERBY"]=$_GET["TABLE_ORDERBY"];
									$_REQUEST["FIELD_ORDERDIR"]=$_GET["FIELD_ORDERDIR"];
								}
								if(isset($_REQUEST["FIELD_ORDERBY"]) && $_REQUEST["TABLE_ORDERBY"]==($this->query_run+1)) {
									$tempsql=eregi_replace(" as "," \x02 ",$parse->query[$i]["query"][$c]);
									$this->current_order_display=$_REQUEST["FIELD_ORDERBY"];
									if(eregi("date_format\(max\(([^,]*))[^\x02]*) \x02 ".$_REQUEST["FIELD_ORDERBY"], $tempsql, $regs2)) $this->current_order=$regs2[1];
									elseif(eregi("date_format\(([^,]*)[^\x02]*) \x02 ".$_REQUEST["FIELD_ORDERBY"], $tempsql, $regs2)) $this->current_order=$regs2[1]; 
									else $this->current_order=$_REQUEST["FIELD_ORDERBY"]; 
									$this->current_order_direction=$_REQUEST["FIELD_ORDERDIR"];
									$parse->query[$i]["query"][$c]=str_ireplace("order by ".str_replace("(","(",str_replace(")",")",$regs[1])),"order by `".str_replace(".","`.`",$this->current_order)."` ".$this->current_order_direction,$parse->query[$i]["query"][$c]);
								}
							}

					$this->results($parse->resolve(calc_found_rows(limit($parse->query[$i]["query"][$c], $parse->query[$i]["options"][1]))));
					$this->construct_export(); $this->query_run++;
				}
			}
			$tablewidth=10800;
			for($h=0; $h<count($this->ex_field_names); $h++)
			{
				$totalsize=0;
				foreach($this->ex_sizes[$h] as $size) $totalsize+=$size;
				$letterwidth=$tablewidth/$totalsize;
				if($_GET[EXPORT_OUTPUT]=='rtf') print "{
\\trowd\\trgaph144\\trrh0\\clvertalc
\\clbrdrt\\brdrs\\clbrdrl\\brdrs\\clbrdrb\\brdrs\\clbrdrr\\brdrs\\clcfpat0\\clcbpat0\\clshdng1000
\\cellx$tablewidth
\\qc{\\b\\f1\\fs20 ";
				if(eregi("^select ", $this->ex_table_header[$h]))
					print strip_tags(ereg_replace("<br>","\n",$this->background->get_var($this->ex_table_header[$h])))."\r\n";
				elseif($this->ex_table_header[$h])
					print strip_tags(ereg_replace("<br>","\n",$this->ex_table_header[$h]))."\r\n";
				if($_GET[EXPORT_OUTPUT]=='rtf') print "\\intbl\\cell
}\\row\r\n";
				if(!isset($_GET[suppressfieldheaders])) {
					if($_GET[EXPORT_OUTPUT]=='rtf') {
						print "\\trowd\\trrh30\\vertalt\\trgaph144";
						//$width=floor($tablewidth/(substr_count($this->ex_field_names[$h], " \\intbl\\cell ")+1));
						$width=0;
						for($i=0;$i<(substr_count($this->ex_field_names[$h], " \\intbl\\cell ")+1);$i++) {
							$width+=round($letterwidth*$this->ex_sizes[$h][$i]);
//							print "\\clbrdrt\\brdrs\\clbrdrl\\brdrs\\clbrdrb\\brdrs\\clbrdrr\\brdrs\\clcfpat12\\clcbpat12\\clshdng1000\\cellx".(($i+1)*$width);
							print "\\clbrdrt\\brdrs\\clbrdrl\\brdrs\\clbrdrb\\brdrs\\clbrdrr\\brdrs\\clcfpat17\\clshdng10000\\cellx".$width;
						}
						echo " ";
					}
					if($_GET[EXPORT_OUTPUT]=='rtf') print "{\\cf8\\f1\\fs20\\b
";
					print strip_tags(ereg_replace("<br>","\n",$this->ex_field_names[$h]));// . "\r\n";
					if($_GET[EXPORT_OUTPUT]=='rtf') print " \\intbl\\cell
}\\row\r\n"; else print "\r\n";
				}
				for($u=0; $u<count($this->ex_items[$h]); $u++)
				{
					if($_GET[EXPORT_OUTPUT]=='rtf') {
						print "\\trowd\\trrh30\\vertalt\\trgaph144";
						$width=0;
						for($i=0;$i<(substr_count($this->ex_field_names[$h], " \\intbl\\cell ")+1);$i++) {
							$width+=round($letterwidth*$this->ex_sizes[$h][$i]);
//							print "\\clbrdrt\\brdrs\\clbrdrl\\brdrs\\clbrdrb\\brdrs\\clbrdrr\\brdrs\\cellx".(($i+1)*$width);
							print "\\clbrdrt\\brdrs\\clbrdrl\\brdrs\\clbrdrb\\brdrs\\clbrdrr\\brdrs\\cellx".$width;
						}
						echo " ";
					}

						//print "\\trowd\\trrh30\\vertalt\\trgaph144\\clbrdrt\\brdrs\\clbrdrl\\brdrs\\clbrdrb\\brdrs\\clbrdrr\\brdrs\\cellx800\\clbrdrt\\brdrs\\clbrdrl\\brdrs\\clbrdrb\\brdrs\\clbrdrr\\brdrs\\cellx1558\\clbrdrt\\brdrs\\clbrdrl\\brdrs\\clbrdrb\\brdrs\\clbrdrr\\brdrs\\cellx2316\\clbrdrt\\brdrs\\clbrdrl\\brdrs\\clbrdrb\\brdrs\\clbrdrr\\brdrs\\cellx3074\\clbrdrt\\brdrs\\clbrdrl\\brdrs\\clbrdrb\\brdrs\\clbrdrr\\brdrs\\cellx3832\\clbrdrt\\brdrs\\clbrdrl\\brdrs\\clbrdrb\\brdrs\\clbrdrr\\brdrs\\cellx4590\\clbrdrt\\brdrs\\clbrdrl\\brdrs\\clbrdrb\\brdrs\\clbrdrr\\brdrs\\cellx5348\\clbrdrt\\brdrs\\clbrdrl\\brdrs\\clbrdrb\\brdrs\\clbrdrr\\brdrs\\cellx6106\\clbrdrt\\brdrs\\clbrdrl\\brdrs\\clbrdrb\\brdrs\\clbrdrr\\brdrs\\cellx6864\\clbrdrt\\brdrs\\clbrdrl\\brdrs\\clbrdrb\\brdrs\\clbrdrr\\brdrs\\cellx7622\\clbrdrt\\brdrs\\clbrdrl\\brdrs\\clbrdrb\\brdrs\\clbrdrr\\brdrs\\cellx8380\\clbrdrt\\brdrs\\clbrdrl\\brdrs\\clbrdrb\\brdrs\\clbrdrr\\brdrs\\cellx9138\\clbrdrt\\brdrs\\clbrdrl\\brdrs\\clbrdrb\\brdrs\\clbrdrr\\brdrs\\cellx9896\\clbrdrt\\brdrs\\clbrdrl\\brdrs\\clbrdrb\\brdrs\\clbrdrr\\brdrs\\cellx10800 ";
					if($_GET[EXPORT_OUTPUT]=='rtf') print "{\\f1\\fs20
";
					for($t=0; $t<count($this->ex_items[$h][$u]); $t++) {
							print str_replace("&lt;","<",str_replace("&gt;",">",strip_tags($this->ex_items[$h][$u][$t])));
					}
					if($_GET[EXPORT_OUTPUT]=='rtf') print " \\intbl\\cell }";
					if($_GET[EXPORT_OUTPUT]=='rtf') print "\\row"; else print "\r\n";
				}
				if($_GET[EXPORT_OUTPUT]=='rtf') print "\r\n}\r\n\r\n\\par\\par\r\n";
			}
			if($_GET[EXPORT_OUTPUT]=='rtf') echo "{".$part2;
			exit();
		}
		
		/**
		 * @return void
		 * @desc constructs the fields and result sets for proper exporting replaces this->construct for export purposes
		 */
		function construct_export()
		{
			if($_GET[EXPORT_OUTPUT]=='csv' || $_GET[EXPORT_DELIM]==",") { $encloser='"'; $delimiter=","; } else { $encloser=""; $delimiter="\t"; $delimiter=($_GET[EXPORT_OUTPUT]=='rtf'?" \\intbl\\cell ":"\t"); }
			if($this->col_info)
			{
//			print_r($this->col_info);
	//		exit();
				$temparray=$this->results(null,ARRAY_N);
				for($i=0; $i<count($this->col_info); $i++)
				{
					$this->col_info[$i]->hidezeros=false;
					if(ereg(" [$]$", $this->col_info[$i]->name)) {
						$this->col_info[$i]->hidezeros=true;
						for($z=0; $z<count($temparray); $z++)
						{
							if(null($temparray[$z][$i])!=0) $this->col_info[$i]->hidezeros=false;
						}
					} 
					if(!$this->col_info[$i]->hidezeros) {
						$this->ex_sizes[$this->row_run][]=strlen($this->col_info[$i]->name)+7;
						$this->ex_field_names[$this->row_run] .= ($_GET[EXPORT_OUTPUT]!='rtf'?'"':'').$this->col_info[$i]->name.($_GET[EXPORT_OUTPUT]!='rtf'?'"':'');
						if(($i+1) != count($this->col_info))
							$this->ex_field_names[$this->row_run] .= $delimiter;
					}
				}
				if($this->last_result)
				{
					$g=0;
					foreach ($temparray as $one_row)
					{
						$d=0; $hiddenfields=0;
						for($jj=0;$jj<sizeof($one_row);$jj++)
						{
							$item=$one_row[$jj];
							if(strpos($this->col_info[$d]->name,"$")>-1) $item=number_format($item/=100,2);
							$item = eregi_replace("<[^>]*>","",$item);
							if($encloser=="" && ereg("\r\n",$item)) $item=ereg_replace("\r\n", "\n",ereg_replace("^(.*\r\n.*)$", "\"\\1\"",str_replace('"','""', $item)));
							if($delimiter=="\t" && strpos($item,"\t")!==false && $_GET[EXPORT_OUTPUT]!='rtf') $enclosertemp='"'; else $enclosertemp=$encloser;
							if($this->col_info[$d]->hidezeros)
								$hiddenfields++;
							else {
								$this->ex_items[$this->row_run][$g][$d-$hiddenfields] = $enclosertemp.str_replace($enclosertemp, $enclosertemp.$enclosertemp, $item).$enclosertemp.($jj==(sizeof($one_row)-1)?"":$delimiter); //str_replace(",", "", $item) . "\t";
								if(strlen($item)>$this->ex_sizes[$this->row_run][$d-$hiddenfields]) $this->ex_sizes[$this->row_run][$d-$hiddenfields]=strlen($item);
							}
							$d++;
						}
							
						$g++;
					}
				}
				//}
			}
			$this->row_run++;
		}

		function MakeGraph($data, $options, $excludetotal) {
			list($title, $yaxis, $xaxis) = explode(":",$options[3],3);
			if(sizeof($data)>0) $offset=1; else $offset=0;
			if(sizeof($this->col_info)==2) {
				foreach($data as $onerow) {
					$j=0;
					foreach($onerow as $key=>$val) {
						if($j) $x2[] = $val; else $x[]=$val;
						$j++;
					}
				}
				$a[] = $x;
				$a[] = $x2;
				$legend=array();
				$drawsets=array("1");//++$ii;
			} else {
				for($i=$offset;$i<sizeof($this->col_info);$i++) {
					if(!($excludetotal && eregi("total",$this->col_info[$i]->name))) $x[]=$this->col_info[$i]->name;
				}
				$a[] = $x;
				foreach($data as $onerow) {
					$i=0; unset($row);
					foreach($onerow as $key=>$value) {
						if(!($excludetotal && eregi("total",$key))) {
							if(++$i>$offset) $row[]=$value; else  { $legend[]=$value; $drawsets[]=++$ii; }
						}
					}
					$a[]=$row;
					if($excludetotal && sizeof($a)>4) break;
				}
			}
//$yaxis=sizeof($data[0]);
			phpplot(array(
				"box_showbox"=> true,
				"grid"=> true,
				"cubic"=> true,
				"title_text"=> $title,
				"yaxis_labeltext"=> $yaxis,
				"xaxis_labeltext"=> $xaxis,
				"legend_shift"=> array(-450,0),
				"size"=> array($options[1],$options[2]) ));
			
			phpdata($a);
			switch($options[0]) {
				case "v":$graphtype="bargraph";break;
				case "vl":$graphtype="linepoints";break;
				case "h":$graphtype="bargraph2";break;
				case "p":$graphtype="piegraph";break;
				case "a":$graphtype="areagraph";break;
				default: $graphtype="bargraph";
			}
			phpdraw($graphtype,array(
				"drawsets" => $drawsets,
				"legend"   => $legend,
				"barspacing"=> 8,
				"showvalue"=> false ));
			
			phpshow();
			return;
		}
		function GetRating($bid) {
			require_once('nusoap.php');
			$serverpath ='http://www.labbb.org/companyrating.asmx';
			// create client object
			$client = new nusoapclient($serverpath, true, false, false, false, false, 0, 600);
			$client->debug_flag=true;
			$param = array('bureau'=>'kansascity', 'user'=>'councilsoap', 'password'=>'g4kVRE43');
			$result = $client->call('RequestFTP',$param,"urn:ebindrwsdl");
			html_print_r($result); 
			exit(); 
			$param = array('bureau'=>'charlotte', 'startdate'=>'2005-1-1','enddate'=>'2005-1-7', 'user'=>'councilsoap', 'password'=>'g4kVRE43');
			$result = $client->call('GetComplaintsByDate',$param,"urn:ebindrwsdl");
			//if (isset($fault)) print "Error: ". $fault;
			html_print_r($result); 
			/*$param = array('bureau'=>'fortcollins', 'ComplaintID'=>'4000019', 'user'=>'councilsoap', 'password'=>'g4kVRE43');
			$result = $client->call('SetTimeStamp',$param,"urn:ebindrwsdl");
			if (isset($fault)) print "Error: ". $fault;
			html_print_r($result);
			$param = array('bureau'=>'fortcollins', 'user'=>'councilsoap', 'password'=>'g4kVRE43');
			$result = $client->call('GetReopenedComplaints',$param,"urn:ebindrwsdl");
			if (isset($fault)) print "Error: ". $fault;
			html_print_r($result);
			/*$param = array('bureau'=>'fortcollins', 'user'=>'councilsoap', 'password'=>'g4kVRE43');
			$result = $client->call('GetReopenedComplaints',$param,"urn:ebindrwsdl");
			if (isset($fault)) print "Error: ". $fault;
			html_print_r($result);*/
			//echo $client->debug_str;
			// if a fault occurred, output error info
			//} else {
				// otherwise output the result
			//}
			// kill object
			unset($client);
		}
		function SetDocTable($bidcid, $value, $scanfile=false) {
			global $mybindr;
			if($bidcid!="cid" && $bidcid!="bid") return;
 			if($bidcid=="cid") $tablename="complaintdoc"; else $tablename="businessdoc";
			$sql = "delete from $tablename where $bidcid=$value";
			$this->background->query($sql);
			$stack[] = DOCS_BASE_DIR.'/'.$bidcid.'/'.substr($value,strlen($value)-2,2).'/'.substr($value,0,strlen($value)-2);
			while ($stack) {
				$current_dir = array_pop($stack);
				if ($dh = @opendir($current_dir)) {
					while (($file = readdir($dh)) !== false) {
						if ($file !== '.' AND $file !== '..') {
							$current_file = "{$current_dir}/{$file}";
							if (is_file($current_file)) {
								if(ereg("^.*/docs/$bidcid/([0-9X]+)/([0-9]+)/trash/.*$", $current_file)) continue;
								if(ereg("^.*/docs/$bidcid/([0-9X]+)/([0-9]+)/(.+)$", $current_file, $regs)) {
									if($regs[1]=="XX") $regs[1]="";
									if($bidcid=="cid")
										$sql = "insert into $tablename (bid,cid,filename,updated) values (null, ".$regs[2].$regs[1].", '".addslashes($regs[3])."',now())";
									else
										$sql = "insert into $tablename (bid,filename,updated) values(".$regs[2].$regs[1].", '".addslashes($regs[3])."',now())";
									$this->background->query($sql);
								}
							} elseif (is_dir($current_file)) {
								$stack[] = $current_file;
							}
						}
					}
				}
			}
			if($bidcid=="cid" && PRINT_SCANS=="ON") {
				$sql = "update $tablename inner join complaint using(cid) set $tablename.bid=complaint.bid where complaint.cid=$value";
				$this->background->query($sql);
				$this->background->select(NO_REPLICATE_DB);
				$sql = "delete rtfdoc from rtfdoc left join ".$_COOKIE["reportr_db"].".complaintdoc using(bid,cid,filename) where rtfdoc.cid=$value and ((length(rtf)<100 and day<now()-interval 10 minute) or complaintdoc.filename is null) and custid=".BBBID.($scanfile?" and rtfdoc.filename='".$scanfile."'":"");
				$this->background->query($sql);
				$sql = "insert ignore into rtfdoc (custid,bid,cid,filename,day) select ".BBBID.", bid, cid, filename, now() from ".$_COOKIE["reportr_db"].".complaintdoc where cid=$value".($scanfile?" and filename='".$scanfile."'":"");
				$this->background->query($sql);
				$sql = "select distinct filename from rtfdoc inner join ".$_COOKIE["reportr_db"].".step on rtfdoc.cid=step.cid and rtfdoc.filename=step.document inner join ".$_COOKIE["reportr_db"].".complaint on step.cid=complaint.cid where (complaint.closedate is null or complaint.closedate>curdate()-interval 5 day) and rtfdoc.cid=$value and rtf='' and filename like '%pdf' and custid=".BBBID.($scanfile?" and rtfdoc.filename='".$scanfile."'":"");
				$this->background->query($sql);
				$r=$this->background->last_result;
				if(!is_array($r) || $_GET["CONVERT_SCANS"]!="YES") { $this->background->select($_COOKIE["reportr_db"]); return is_array($r); }
				$sql = "update rtfdoc inner join ".$_COOKIE["reportr_db"].".step on rtfdoc.cid=step.cid and rtfdoc.filename=step.document set rtf='YES' where rtfdoc.cid=$value and rtf='' and filename like '%pdf' and custid=".BBBID.($scanfile?" and rtfdoc.filename='".$scanfile."'":"");
				$this->background->query($sql);
				if(!ereg("/report/merge", $_SERVER['REQUEST_URI'])) {
					echo "<HTML>
    <STYLE TYPE='text/css'>
    #cache {
    position:absolute; left=10; top:10px; z-index:10; visibility:hidden;
    }
    </STYLE>
    <SCRIPT LANGUAGE='JavaScript'>
    ver = navigator.appVersion.substring(0,1)
    if (ver >= 4)
    	{
    	document.write('<DIV ID=\"cache\"><TABLE WIDTH=200 align=\"left\" BGCOLOR=#000000 BORDER=0 CELLPADDING=2 CELLSPACING=0><TR><TD ALIGN=left VALIGN=middle>		<TABLE WIDTH=100% BGCOLOR=#FFFFFF BORDER=0 CELLPADDING=0 CELLSPACING=0><TR>		<TD ALIGN=center VALIGN=middle><FONT FACE=\"Arial, Verdana\" SIZE=4><B><BR>Converting files... please wait.<BR><BR></B></FONT></TD> </TR></TABLE></TD> </TR></TABLE></DIV>');
    	var navi = (navigator.appName == \"Netscape\" && parseInt(navigator.appVersion) >= 4);
    	var HIDDEN = (navi) ? 'hide' : 'hidden';
    	var VISIBLE = (navi) ? 'show' : 'visible';
    	var cache = (navi) ? document.cache : document.all.cache.style;
    	largeur = screen.width;
    	cache.left = Math.round(0);
    	cache.visibility = VISIBLE;
    	}
    </SCRIPT><body onload=\"window.close()\"></body></html>";
					flush();
				}
				if(!is_object($mybindr)) { include_once(_MYBINDR); $mybindr = new mybindr; }
				$mybindr->database=NO_REPLICATE_DB;
				foreach($r as $onerow) {
					$filesize=filesize(DOCS_BASE_DIR.'/cid/'.substr($value,strlen($value)-2,2).'/'.substr($value,0,strlen($value)-2)."/".$onerow["filename"]);
					$starttime=explode(" ",microtime());
					$ide="/usr/local/bin/identify";
					if(exec($ide)=="") { $ide="identify"; }
					$thisfile=DOCS_BASE_DIR.'/cid/'.substr($value,strlen($value)-2,2).'/'.substr($value,0,strlen($value)-2)."/".$onerow["filename"];
					//$pages=exec($ide." \"$thisfile\" 2>> /dev/null | wc -l");
					$pages=$mybindr->my_exec($ide." \"$thisfile\" 2>> /dev/null | wc -l",30);
					if($pages>30) $rtf="Attachment conversion is limited to 30 pages. This file contains $pages pages.";
					else $rtf=$mybindr->GetParm("includepdf ".$thisfile);
					for($i=0;$i<strlen($rtf);$i+=50000) {
						$rtfpiece=substr($rtf, $i, 50000);
						$sql = "update rtfdoc set rtf=concat(if(rtf='YES','',rtf),'".addslashes($rtfpiece)."') where cid=$value and filename='".$onerow["filename"]."' and custid=".BBBID;
						$mybindr->mybindr_query($sql);
					}
					$endtime=explode(" ",microtime());
					$timetook=number_format($endtime[0]+$endtime[1]-$starttime[0]-$starttime[1],3);
					$sql = "update rtfdoc set pages='$pages', filesize=$filesize, converttime=$timetook, day=now() where cid=$value and filename='".$onerow["filename"]."' and custid=".BBBID;
					$mybindr->mybindr_query($sql);
				}
				$mybindr->database=$_COOKIE["reportr_db"];
			}
			$this->background->select($_COOKIE["reportr_db"]);
			return false;
		}
	}
}

?>