<?php
if(!function_exists('html_entity_decode')) {
	function html_entity_decode($str) {
		return str_replace("&quot;", '"', $str);
	}
}
if(!class_exists('parse'))
{
	class parse
	{
		var $params; // query parameters
		var $validate_types; // array
		var $conf_file; // where the entered params are stored and cached
		var $flag; // what type of query is being parsed
		var $query; // array of all the queries to be executed after being parsed
		var $num_queries; // number of queries in a query group
		var $orphan; // array of all params with no values
		
		var $html_prompt; // string of html that is retunred to adopt the orphans
		var $pipe_options; // array of options for each piped parameter
		var $pipe_validate; // whether a parameter needs to be validated or not.
		var $update_errors="";
		var $logqueries=false;
		var $noLC = array('the', 'a', 'an', 'and', 'or', 'but', 'aboard', 'about', 'above', 'across', 'after', 'against', 'along', 'amid', 'among', 'around', 'as', 'at', 'before', 'behind', 'below', 'beneath', 'beside', 'besides', 'between', 'beyond', 'but', 'by', 'for', 'from', 'in', 'inside', 'into', 'like', 'minus', 'near', 'of', 'off', 'on', 'onto', 'opposite', 'outside', 'over', 'past', 'per', 'plus', 'regarding', 'since', 'than', 'through', 'to', 'toward', 'towards', 'under', 'underneath', 'unlike', 'until', 'up', 'upon', 'versus', 'via', 'with', 'within', 'without');	
		var $noACRO = array('mr', 'mrs', 'ms', 'dr');
		/**
		 * @return voice
		 * @desc class constructor builds params from POST variables
		 */
		function parse()
		{
			global $validate_types, $params, $device;
			$device->define("logout_minus_five", (AUTO_LOGOUT_TIME-300)*1000);
			foreach($_POST as $key=>$value) if(is_array($value)) $_POST[$key]=implode(",", $value);
			$this->params = array_merge($params, get_defined_constants(), $_COOKIE, $_SERVER, $_POST, $_GET, array("COUNTRY"=>(COUNTRY=="COUNTRY"?'USA':COUNTRY)));
			$this->conf_file = DIR_CONF . $_COOKIE["reportr_conf"];
			
			if(file_exists($this->conf_file) && (!is_dir($this->conf_file))) {
				$conf_file = fopen($this->conf_file, "a+");
				foreach(array_merge($_POST, $_GET) as $key => $value) {
					fwrite($conf_file, "$key:$value\r\n");
				}
				fclose($conf_file);
			}
			
			foreach ($this->params as $key => $value) {
				switch($key) {
					case "reportr_username" : $key = 'staff'; break;
					case "reportr_db" : $key = 'db'; break;
					case "reportr_auto_home" : $key = 'auto_home'; break;
					case "reportr_keys" : $key = 'keys'; break;
				}
				$this->params[str_replace("_", " ", $key)] = $value;
				if($value=="is_date") $this->params[str_replace("_", " ", $key)]=date("Y-m-d",strtotime($this->params["view_".$key]));
			}
			$this->validate_types = $validate_types;
		}
		
		/**
		 * @return string
		 * @param mytext string
		 * @desc calls the resolving of all codes for the merge document
		 */
		function resolve($mytext)
		{
			if(ereg("^login::", $mytext))
			{
				$this->flag = "login";
				$mytext = str_replace("login::", "", $mytext);
			}
			$mytext = $this->resolve_pipes($mytext);
			if(!ereg("/report/database master", urldecode($_SERVER['REQUEST_URI'])))
				$mytext = $this->resolve_merge($mytext);
			$mytext = $this->resolve_pipes($mytext);
			$mytext = eregi_replace("from " . QUERY_TABLE . " ", "from " . QUERY_DB . "." . QUERY_TABLE . " ", $mytext);
			if(COUNTRY=='CANADA') {
					$mytext = eregi_replace("( as `[^`_]*)zip([^`]*`)", "\\1PostalCode\\2", $mytext);
					$mytext = eregi_replace("( as `[^`_]*)state([^`]*`)", "\\1Province\\2", $mytext);            
			}

			return $this->output = str_replace("<<", "", str_replace(">>", "", $mytext));
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
		
		/**
		 * @return void
		 * @desc checks to see if any orphans need adoption (parmeters need filling)
		 */
		function advance()
		{
			if(!$this->orphan)
				return true;
			else
				return false;
		}
		
		/**
		 * @return string
		 * @param mytext string
		 * @desc resolves pipes and sets parameters for each piped definition also rids special characters
		 */
		function resolve_pipes($mytext)
		{
			while ($param = $this->get_next_pipe($mytext))
			{
//			if(ereg("[|]ifhurdman.+",$param)) die($param);
				$new_param = $this->get_param(str_replace("|","",$param));
				if($new_param == 'NULL')
					$new_param = '';
				else
					$new_param = $new_param;
				$mytext = substr_replace($mytext, $new_param, strpos($mytext,$param), strlen($param));
//				$mytext = str_replace($param, $new_param,$mytext);
			}
			return $mytext;
		}
		
		/**
		 * @return string
		 * @param mytext string
		 * @desc resolves the if merge code and sets it up to move through each code removing the [ ]  around each code
		 */
		function resolve_if_merge($mytext)
		{
			$ifmerge = $this->get_next_if_merge($mytext);
			do
			{
				if(!$ifmerge) break;
				$mergecode = $this->get_next_merge_code($ifmerge);
				$mymerge = $this->merge_code(str_replace("[","",str_replace("]","",$mergecode)));
				if($mymerge != "")
					$newmerge = str_replace($mergecode,$mymerge,$ifmerge);
				else
					$newmerge = "<<>>";
				$mytext = str_replace($ifmerge,substr($newmerge,2,strlen($newmerge)-4),$mytext);
			}
			while ($ifmerge = $this->get_nex_if_merge($mytext));
			return $mytext;
		}

		/**
		 * @return string
		 * @param mytext string
		 * @desc resolves the merge code and sets it up to move through each code removing the [ ]  around each code
		 */
		function resolve_merge($mytext)
		{
			$mergecode = $this->get_next_merge_code($mytext);
			do
			{
				if(!$mergecode) break;
				$mytext = str_replace($mergecode, $this->merge_code(str_replace("[", "", str_replace("]", "", $mergecode))), $mytext);
			}
			while ($mergecode = $this->get_next_merge_code($mytext));
			return $mytext;
		}
		
		/**
		 * @return string
		 * @param code string
		 * @desc gets the sql query according to merge code from the databaes and executes it for its content
		 */
		function merge_code($code)
		{
			global $task;
			if(ereg("^mycomplaints.+$", $code)) {
				$retcode = $task->get_var("SELECT sqlstatement FROM " . LOCAL_DB . "." . QUERY_TABLE . " WHERE mergecode in ('" . $code . "','" . $code . " ".$this->params["reportr_flag"]."') order by length(mergecode) desc limit 1");
			} else {
				if(isset($_GET["ebindr2"])) $retcode = $task->get_var("SELECT sqlstatement FROM " . LOCAL_DB . "." . QUERY_TABLE . " WHERE mergecode in ('$code','e2.$code') and description!='MYBINDR ONLY' order by mergecode like 'e2.%' desc limit 1");
				else $retcode = $task->get_var("SELECT sqlstatement FROM " . LOCAL_DB . "." . QUERY_TABLE . " WHERE mergecode LIKE '" . $code . "' and description!='MYBINDR ONLY'");
			}
	        if($task->num_rows < 1)	{
				if(isset($_GET["ebindr2"])) $retcode = $task->get_var("SELECT sqlstatement FROM " . QUERY_DB . "." . QUERY_TABLE . " WHERE mergecode in ('$code','e2.$code') and description!='MYBINDR ONLY' order by mergecode like 'e2.%' desc limit 1");
				else $retcode = $task->get_var("SELECT sqlstatement FROM " . QUERY_DB . "." . QUERY_TABLE . " WHERE mergecode LIKE '" . $code . "' and description!='MYBINDR ONLY'");
			}
	        if($task->num_rows < 1) return false;
			return $retcode;
		}

		function is_empty_dir($path) {
			if($d = @opendir($path)) {
				while(false !== ($filename = readdir($d))) {
					if(!in_array($filename, array(".","..","trash")) && is_dir($path."/".$filename)) {
						if(!$this->is_empty_dir($path."/".$filename)) {
							@closedir($d);
							return false;
						}
					} elseif(!in_array($filename, array(".","..","trash"))) {
						@closedir($d);
						return false;
					}
				}
			}
			@closedir($d);
			return true;
		}

		/**
		 * @return array
		 * @param myparm string
		 * @desc gets the value for a parameter
		 */
		function get_param($myparam)
		{
			global $reportr;
			$myparam = $this->def_val($myparam);

			if(eregi("^(BID|CID|MED|SC|MISC|VORP|VIP) FILE LINK$",$myparam,$regs)) {
				$filecid=$this->params[strtolower($regs[1])];
				if(!$filecid) $filecid=$this->params[strtoupper($regs[1])];
				if(strlen($filecid)<3) $filecid.="XX";
				$directory = DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2);
				if($d = @opendir($directory)) {
					while(false !== ($filename = readdir($d)))
						if ($filename != "." && $filename != ".." && $filename!='trash' && !ereg("^[.]", $filename)) $filelist[]=$filename;
				}
				@closedir($d);
//				if(isset($filelist) && sizeof($filelist)>0)	$item="docs"; else $item="docsnone";
				if(!$this->is_empty_dir($directory)) $item="docs"; else $item="docsnone";
				return "<a title=\"Click here to add/manage/view documents associated with this record.\" href=javascript:window.parent.FileBrowser(\"".strtolower($regs[1])."\",\"$filecid\")><img src=\"/css/$item.gif\" border=0></a>";
			}
			if(eregi("^(BID|CID|MED|SC|MISC|VORP|VIP) FILES$",$myparam,$regs)) {
				return "'".$regs[1]."FILES'";
			}
			if(ereg("^get council complaints(.*)$",$myparam, $regs)) {
				if($regs[1]=="" && strlen($this->params["council complaint start date"])<10) return "0";
				$startdate=$this->params["council complaint start date"];
				$enddate=$this->params["council complaint end date"];
				if($regs[1]>'') {
					$reportr->background->query($this->resolve_pipes($regs[1]));
					if(!$reportr->background->last_result) return -1; else {
						$startdate=$reportr->background->last_result[0]["startdate"];
						$enddate=$reportr->background->last_result[0]["enddate"];
					}
				}
				//$result=$this->GetCouncilComplaints($startdate, $enddate);
				//if(ereg("Timeout expired", $result)) $result=$this->GetCouncilComplaints($startdate, $enddate, 1);
				$result=$this->GetNewCouncilComplaints();
				return $result;
			}
		if( eregi( "^approvecbbbvideo", $myparam) ) {
			list( $vid, $bid ) = explode( "_", $this->params['rid'] );
			$r = $reportr->background->query("select setup(318) value, embedcode from reportvideos where vid='$vid' and bid = '$bid'");
			$bbbid = $reportr->background->last_result[0]["value"];
			$embedcode = $reportr->background->last_result[0]["embedcode"];
			$token = 'asdflkkjl4nFSJDDf09dadsjfSD0asdjkal44534JLSjlsiEEFS945';
			if( !class_exists('nusoap_client') ) include "/home/serv/library/nusoap/nusoap.php";
			$client = new nusoap_client("http://services.hurdman.org/ooyala/?wsdl");
			return $client->call( 'approve', array(
				'token' => $token,
				'embedcode' => $embedcode,
				'bid' => $bid,
				'bbbid' => $bbbid
			));
		}
		if( eregi( "^rejectcbbbvideo", $myparam) ) {
			list( $vid, $bid ) = explode( "_", $this->params['rid'] );
			$r = $reportr->background->query("select setup(318) value, embedcode from reportvideos where vid='$vid' and bid = '$bid'");
			$bbbid = $reportr->background->last_result[0]["value"];
			$embedcode = $reportr->background->last_result[0]["embedcode"];
			$token = 'asdflkkjl4nFSJDDf09dadsjfSD0asdjkal44534JLSjlsiEEFS945';
			if( !class_exists('nusoap_client') ) include "/home/serv/library/nusoap/nusoap.php";
			$client = new nusoap_client("http://services.hurdman.org/ooyala/?wsdl");
			return $client->call( 'reject', array(
				'token' => $token,
				'embedcode' => $embedcode,
				'bid' => $bid,
				'bbbid' => $bbbid
			));
		}
		if(eregi("^approvebrphoto", $myparam) ) {
			$r = $reportr->background->query("select value from setup where code = '126'");
			$url = $reportr->background->last_result[0]["value"];
			
			$params = array();
			$params["pass"] = "jv8re0jg4394f3jj94j3io";
			$params["bid"] = $this->params["rid"];
			$params["pid"] = $this->params["value"];
			$agent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.4) Gecko/20030624 Netscape/7.1 (ax)"; 
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,"http://" . $url . "/brphoto/create.html?bid=" . $params["bid"] . "&pid=" . $params["pid"]);
			curl_setopt($ch, CURLOPT_USERAGENT, $agent);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 120);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			$result = curl_exec ($ch);
			curl_close ($ch);
			return $result;
		}
			if(ereg("^get seal clicks(.*)$",$myparam, $regs)) {
				$reportr->background->query($this->resolve_pipes($regs[1]));
				if(!$reportr->background->last_result) return -1; 
				else {
					$startdate=$reportr->background->last_result[0]["startdate"];
					$enddate=$reportr->background->last_result[0]["enddate"];
				}
				return $this->GetSealClicks($startdate, $enddate);
			}

			if(ereg("^stored files (cid|bid)$",$myparam,$regs)) {
				$filecid=$this->params[$regs[1]];
				if(strlen($filecid)<3) $filecid.="XX";
				$directory = DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2);
				if($d = @opendir($directory)) {
					while(false !== ($filename = readdir($d)))
						if ($filename != "." && $filename != ".." && $filename!='trash') $filelist[]=$filename;
				}
				if(!isset($filelist) || $this->is_empty_dir($directory)) return "0"; else return sizeof($filelist);
			}
			if(ereg("^stored file list (cid|bid)$",$myparam,$regs)) {
				$filecid=$this->params[$regs[1]];
				if(strlen($filecid)<3) $filecid.="XX";
				$directory = DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2);
				if($d = @opendir($directory)) {
					while(false !== ($filename = readdir($d)))
						if ($filename != "." && $filename != ".." && $filename!='trash') $filelist[]=$filename;
				}
				if(!isset($filelist) || $this->is_empty_dir($directory)) return "'***NOFILES***'"; else return "'".implode("','",$filelist)."'";
			}
			if(ereg("^stored file list ([a-z]+) (.+)$",$myparam,$regs)) {
				$filecid=$this->params[$regs[1]];
				if(strlen($filecid)<3) $filecid.="XX";
				$directory = DOCS_BASE_DIR.'/'.strtolower(($regs[1]!="cid"?"bid":"cid")).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2);
				if($regs[2]>"") $directory.="/".$regs[2];
				if($d = @opendir($directory)) {
					while(false !== ($filename = readdir($d)))
						if ($filename != "." && $filename != ".." && $filename!='trash') $filelist[]=str_replace('"','\\"',$filename);
				}
				if(!isset($filelist) || $this->is_empty_dir($directory)) return "('***NOFILES***')"; else return "(\"".implode("\"),(\"",$filelist)."\")";
			}
			
			if(eregi("^fixphone# (.*)",$myparam,$regs)) {
				$myparam=$regs[1];
				$this->params[$myparam] = (ereg("^[+]", $this->params[$myparam])?ereg_replace("^[+](.*[a-zA-Z]+.*)$", "\\1", $this->params[$myparam]):ereg_replace("[^0-9]", "", $this->params[$myparam]));
				return $this->params[$myparam];
			}
			if(eregi("^caliberrating$", $myparam, $regs)) {
				$reportr->background->query("CREATE TEMPORARY TABLE caliberrating_update ( BID int(11) NOT NULL, BusinessStartDate char(10) NOT NULL, TOBScore smallint(6) NOT NULL, GovtActScore smallint(6) NOT NULL, LicenseScore smallint(6) NOT NULL, Unanswered smallint(6) NOT NULL, Unresolved smallint(6) NOT NULL, Serious smallint(6) NOT NULL, Total smallint(6) NOT NULL, MembershipStatus smallint(6) NOT NULL, AdReviewScore smallint(6) NOT NULL, BackgroundScore smallint(6) NOT NULL, PRIMARY KEY (BID));");
				$reportr->background->query($this->resolve_pipes("insert ignore into caliberrating (bid) values (|bid|)"));
				$reportr->background->query($this->resolve_pipes("insert into caliberrating_update select business.bid, ifnull(ifnull(businessstartdate,ifnull(localstartdate,ifnull(incorporationdate,fileopendate))),curdate()), 1, 1, 4, sum(closecode=200), sum(closecode=120), sum(ifnull(complaintextra.serious,'n')='y'), count(complaint.cid), if(member='y' and member.pending!='y' and business.reportstatus not like 'SUSP%',1,0), if(sum(closecode in (120,200,951,955,964,965) and type in ('adv','adc') and serious='y')>0,3,if(sum(closecode in (120,200,951,955,964,965) and type in ('adv','adc'))>0,2,1)), 1 from (business left join complaint on business.bid=complaint.bid and complaint.closedate>curdate()-interval 36 month and complaint.closecode>100 and complaint.cid>0) left join complaintextra on complaint.cid=complaintextra.cid left join member on business.bid=member.bid where business.bid=|bid| group by business.bid"));
				$reportr->background->query($this->resolve_pipes("update (caliberrating_update c left join tobs on c.bid=tobs.bid and tobs.main='y') left join tob on tobs.tob=tob.code set tobscore=ifnull(case tob.score when 1 then 5 when 2 then 4 when 3 then 2 when 4 then 1 when 5 then 1 end,5), backgroundscore=if(tobs.bid is null,0,backgroundscore), licensescore=if(tob.licenserequired='y',3,2) where c.bid=|bid|"));
				$reportr->background->query($this->resolve_pipes("update caliberrating_update c inner join reporttext on c.bid=reporttext.bid and reporttext.section=7 set govtactscore=reporttext.seriousness where c.bid=|bid|"));
				$reportr->background->query($this->resolve_pipes("update caliberrating_update c inner join license on c.bid=license.bid and number='*NOLICENSE*' set licensescore=1 where c.bid=|bid| and licensescore=3"));
				$reportr->background->query($this->resolve_pipes("update caliberrating_update c left join license on c.bid=license.bid and number!='*NOLICENSE*' and number>'' and expirydate>curdate() set licensescore=4 where c.bid=|bid| and license.bid is null and licensescore=3"));
				$reportr->background->query($this->resolve_pipes("update caliberrating_update c set backgroundscore=0 where businessstartdate is null and c.bid=|bid|"));
				$reportr->background->query($this->resolve_pipes("update caliberrating_update c left join phone on c.bid=phone.bid and phone.report='y' set backgroundscore=0 where phone.bid is null and c.bid=|bid|"));
				$reportr->background->query($this->resolve_pipes("update caliberrating_update c left join person on c.bid=person.bid and person.report='y' set backgroundscore=0 where person.bid is null and c.bid=|bid|"));
				$reportr->background->query($this->resolve_pipes("update caliberrating_update c inner join caliberrating cc using(bid) set cc.BusinessStartDate=c.BusinessStartDate, cc.TOBScore=c.TOBScore, cc.GovtActScore=c.GovtActScore, cc.LicenseScore=c.LicenseScore, cc.Unanswered=c.Unanswered, cc.Unresolved=c.Unresolved, cc.Serious=c.Serious, cc.Total=c.Total, cc.MembershipStatus=c.MembershipStatus, cc.AdReviewScore=c.AdReviewScore, cc.BackgroundScore=c.BackgroundScore where c.bid=|bid|"));
				$reportr->background->query($this->resolve_pipes("update caliberrating c inner join rating r using(bid) set c.rating=r.letter, c.lastupdate=now() where r.letter='NA' and c.bid=|bid|"));
				$reportr->background->query($this->resolve_pipes("update caliberrating c inner join rating r using(bid) set c.lastupdate=null where c.rating='NA' and r.letter!='NA' and c.bid=|bid|"));
				$reportr->background->query($this->resolve_pipes("update caliberrating set lastupdate=null where rating='' and bid=|bid|"));
				$result = $reportr->background->query($this->resolve_pipes("select BID, BusinessStartDate, TOBScore, GovtActScore, LicenseScore, Unanswered, Unresolved, Serious, Total, MembershipStatus, AdReviewScore, BackgroundScore, ifnull(lastchange>lastupdate,1) as needrun from caliberrating where bid=|bid|"));
				if(sizeof($reportr->background->last_result)>0) {
					list($mybid, $businessstartdate, $tob, $govtact, $license, $unans, $unres, $serious, $cmpl, $membership, $adrev, $background, $needrun) = array_values($reportr->exceptionlist=$reportr->background->last_result[0]);
					if($needrun) {
						require_once('nusoap.php');
						$client = new nusoapclient('http://66.240.197.236/CompanyRating.asmx', true, false, false, false, false, 0, 600);
						$param = array("AlgorithmID"=>1, "BusinessStartDate"=>date("n/j/Y",strtotime($businessstartdate)), "TobScore"=>$tob, "GovernmentActionScore"=>$govtact, "LicenseScore"=>$license, "UnansweredComplaintCount"=>$unans, "UnresolvedComplaintCount"=>$unres, "SeriousComplaintCount"=>$serious, "TotalComplaintCount"=>$cmpl, "MemberShipStatus"=>$membership, "AdReviewScore"=>$adrev, "BackgroundScore"=>$background, "sCompositScore"=>0, "ErrMessage"=>"");
						$result = $client->call('RateCompany',$param,"http://tempuri.org/", "http://tempuri.org/RateCompany", false, null, "rpc", "literal");
						unset($client);
						$reportr->background->query($this->resolve_pipes("update caliberrating set rating='".$result["RateCompanyResult"]."', RatingValue='".$result["sCompositScore"]."', lastchange=ifnull(lastchange,now()), lastupdate=now(), callsmade=callsmade+1 where bid=|bid|"));
						return print_r($result, true); //["RateCompanyResult"];
					} else return "Up-to-date";
				} else return "ERR";
			}
			if(eregi("getwebpage(:| )(.*)", $myparam, $regs)) {
				$ch = curl_init();
				$agent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.4) Gecko/20030624 Netscape/7.1 (ax)"; // identify as your own user agent (like ‘MSIE’) if you want
				$cookie_file_path = "";
				$regs[2]=$this->resolve_pipes(str_replace("~", "|", $regs[2]));
				if(ereg("^addslashes ", $regs[2])) {
					$addslash=true;
					$regs[2]=ereg_replace("^addslashes ", "", $regs[2]);
				}
				if(!eregi("^http", $regs[2])) $regs[2]="http://".$regs[2];
				$regs[2]=preg_replace("/(^.+\/report\/)([^\/?]+)(\/|[?])(.+$)/e", "'\\1'.rawurlencode('\\2').'\\3\\4'", $regs[2]);
//				return $regs[2];

				curl_setopt($ch, CURLOPT_URL,$regs[2]);
				curl_setopt($ch, CURLOPT_USERAGENT, $agent);
	//			curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file_path);
				curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file_path);
				curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_TIMEOUT, 43200);
	//			curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
				$result = curl_exec ($ch);
				$result.=curl_error($ch);
				curl_close ($ch);
				if($addslash) return str_replace(array(">","|", "[", "]"), array(">\n", "',char(124),'", "',char(91),'", "',char(93),'"), addslashes($result)); else return $result;
			}
			if(eregi("^makemoney (.*)",$myparam,$regs)) {
				$myparam=$regs[1];
//				$this->params[$myparam] = ereg_replace("[^0-9.]", "", $this->params[$myparam])*100;
				return ereg_replace("[^0-9.]", "", $this->params[$myparam])*100; //$this->params[$myparam];
			}
			if(!(strpos("|" . $myparam, "|phone# ") === false))
			{
				$myparam = trim(str_replace("phone# ","",$myparam));
				$myparam="if(length($myparam)<10,LPAD($myparam,10,' '),$myparam)";
				//return "FormatPhone($myparam)"; 
				if(FORMAT_PHONE_FUNC=="FormatPhoneP") return "if($myparam like '+%',if($myparam regexp concat(char(91),'a-z',char(93)),trim('+' from $myparam),$myparam),concat('(',mid($myparam,1,3),') ',mid($myparam,4,3),'-',mid($myparam,7,4),if(length($myparam)>10,concat(' ext ',mid($myparam,11)),'')))";
				else return "if($myparam like '+%',if($myparam regexp concat(char(91),'a-z',char(93)),trim('+' from $myparam),$myparam),concat(mid($myparam,1,3),' ',mid($myparam,4,3),'-',mid($myparam,7,4),if(length($myparam)>10,concat(' ext ',mid($myparam,11)),'')))";
			}
			if(!(strpos("|" . $myparam, "|mydate ") === false))
			{
				$myparam = trim(str_replace("mydate ","",$myparam));
				if($this->params[$myparam])
					$myparam = $this->params[$myparam];
				if(!ereg('([[:alnum:]]*)[^[:alnum:]]+([[:alnum:]]*)[^[:alnum:]]+([[:alnum:]]*)(.*)', $myparam, $regs))
					$regs = array($myparam, substr($myparam,0,2), substr($myparam,2,2), substr($myparam,4,4),substr($myparam,8));
				if(!checkdate($regs[1], $regs[2], $regs[3])) { $temp=$regs[2]; $regs[2]=$regs[1]; $regs[1]=$temp; }
				if(!checkdate($regs[1], $regs[2], $regs[3])) return 'null';
				else return $regs[3]."-".$regs[1]."-".$regs[2].date(" H-i-s", strtotime($regs[4]));
//				return substr($myparam,6)."/".substr($myparam,0,5);
			}
			if(!(strpos("|" . $myparam, "|usdate ") === false))
			{
				$myparam=trim(str_replace("usdate ","",$myparam));
				if(COUNTRY=='CANADA')
					return $myparam;
				else
					return " date_format($myparam,'%m-%d-%Y')";
			}
			if(eregi("^htmlencode (.*)",$myparam,$regs)) {
				$myparam=$regs[1];
				return "replace(replace(replace($myparam,'<','&lt;'),'>','&gt;'),'\\r\\n','<br>')";
			}
			if(eregi("^EXCEPTION LIST(.*)$",$myparam,$regs)) {
				$mycode=$reportr->current_query;
//				print_r($reportr->variables);
				if($mycode=="exportr") $mycode=$reportr->variables[1];
				$result = $reportr->background->query("select trim(',' from list) as list from exceptionlist where mergecode=\"".($regs[1]==""?$mycode:trim($regs[1]))."\" having list>''");
				if(sizeof($reportr->background->last_result)>0) {
					if($_POST["EXCEPTIONLIST"]=="VIEW") {
						$reportr->exceptionlist=$reportr->background->last_result[0]["list"];
						return $this->params["EXCEPTION LIST"]="-99999999999999";
					} else
						return $this->params["EXCEPTION LIST"]=ereg_replace("[,]{2,}", ",", $reportr->background->last_result[0]["list"]);
				}
				else return $this->params["EXCEPTION LIST"]="-99999999999999";
			}
			if(eregi("^stripmerge (.*)",$myparam,$regs)) {
				$myparam=$regs[1];
				return str_replace("[", "',char(91),'", str_replace("]", "',char(93),'", $this->params[$myparam]));
			}
			if(eregi("^encodeapostrophe (.*)",$myparam,$regs)) {
				$myparam=$regs[1];
				return str_replace("'", "&#39;", $this->params[$myparam]);
			}
			if(eregi("^urlencode (.*)",$myparam,$regs)) {
				$myparam=$regs[1];
				return rawurlencode($this->params[$myparam]);
			}
			if(eregi("^urlencodestrip (.*)",$myparam,$regs)) {
				$myparam=$regs[1];
				return rawurlencode(stripslashes($this->params[$myparam]));
			}
			if(eregi("^fulladdress *(.*|$)",$myparam,$regs)) {
				$myparam=$regs[1];
				if($myparam=="") $myparam="address";
				return "concat($myparam.street1, ' ',$myparam.street2,' ',$myparam.city,', ',$myparam.stateprov,' ',$myparam.postalcode)";
			}
			if(eregi("^get new cid$",$myparam,$regs)) {
				global $reportr;
				$reportr->background->query("lock table setup write, complaint read");
				do {
					$reportr->background->query("select value+1 as cid from setup where code=10");
					$row=$reportr->background->last_result[0];
					$reportr->background->query("update setup set value=value+1 where code=10");
					$reportr->background->query("select cid from complaint where abs(cid)=".$row["cid"]);
				} while($reportr->background->num_rows>0);
				$reportr->background->query("unlock tables");
				$this->params[$myparam] = $row["cid"];
				$this->params[cid] = $row["cid"];
				$this->params[CID] = $row["cid"];
				return $this->params[$myparam];
			}
			if(eregi("^get new bid$",$myparam,$regs)) {
				global $reportr;
				$reportr->background->query("lock table setup write, business read");
				do {
					$reportr->background->query("select value+1 as bid from setup where code=9");
					$row=$reportr->background->last_result[0];
					$reportr->background->query("update setup set value=value+1 where code=9");
					$reportr->background->query("select bid from business where abs(bid)=".$row["bid"]);
				} while($reportr->background->num_rows>0);
				$result=$reportr->background->query("unlock tables");
				$this->params[$myparam] = $row["bid"];
				$this->params[bid] = $row["bid"];
				return $this->params[$myparam];
			}
			if(eregi("^runquery (.*)$", $myparam, $regs)) {
				$myquerytorun=$this->resolve_pipes(str_replace("PIPE","|",$regs[1]));
				$reportr->query($myquerytorun);
				return "#Pre-processed query: $myquerytorun; Affected row(s): ".$reportr->affected_rows;     
			}
			if(ereg("^bookmarks$", $myparam)) {
				global $reportr;
				$reportr->background->query("select replace(bookmarks,',',' ') as bookmarks from staff where initials='".$this->params["staff"]."'");
				if(!$row=$reportr->background->last_result[0]) {
					$reportr->background->query("select replace(bookmarks,',',' ') as bookmarks from common.staff where initials='".$this->params["staff"]."'");
					$row=$reportr->background->last_result[0];
				}
				if(!$row["bookmarks"]) $row["bookmarks"]="-1";
				return str_replace(" ",",",str_replace(",,",",",ereg_replace("[^0-9,-]"," ",trim($row["bookmarks"]))));
			}
			if(eregi("^titlecase (.*)",$myparam,$regs)) {
				if(TITLE_CASE=="OFF") return $this->params[$regs[1]];
				$myparam=preg_replace(array("/([a-z])\/([a-z])/e", "/Mc([a-z])/e", "/Ceo($|[^a-zA-Z])/", "/Ne($|[^a-zA-Z])/", "/Nw($|[^a-zA-Z])/", "/Se($|[^a-zA-Z])/", "/Sw($|[^a-zA-Z])/", "/Pmb($|[^a-zA-Z])/", "/Ps($|[^a-zA-Z])/", "/Dds($|[^a-zA-Z])/", "/Dmd($|[^a-zA-Z])/", "/Md($|[^a-zA-Z])/", "/Pllc($|[^a-zA-Z])/", "/P[.]o[.]($|[^a-zA-Z])/", "/Po($|[^a-zA-Z])/", "/Llc($|[^a-zA-Z])/", "/Llp($|[^a-zA-Z])/", "/Pc($|[^a-zA-Z])/"), array("'\\1/'.strtoupper('\\2')", "'Mc'.strtoupper('\\1')", "CEO\\1", "NE\\1", "NW\\1", "SE\\1", "SW\\1", "PMB\\1", "PS\\1", "DDS\\1", "DMD\\1", "MD\\1", "PLLC\\1", "P.O.\\1", "PO\\1", "LLC\\1", "LLP\\1", "PC\\1"),ucwords(strtolower($this->params[$regs[1]])));
//				$myparam=ereg_replace("Po($|[^a-zA-Z])", "PO\\1",ucwords(strtolower($this->params[$regs[1]])));
				return "$myparam";
			}
			if(eregi("^ifhurdman(.*)$", $myparam, $regs)) {
				if(ereg(",HURDMAN,",$this->params["keys"])) return $regs[1]; else return '';
			}
			if(eregi("^nothurdman(.*)$", $myparam, $regs)) {
				if(ereg(",HURDMAN,",$this->params["keys"])) return ''; else return $regs[1];
			}
			if(eregi("^READFILE (.*)$", $myparam, $regs)) {
				if(!($fp = fopen($this->params[$regs[1]], "rb"))) return "''";
				return "concat('".str_replace("<", "',char(60),'", str_replace(">", "',char(62),'", str_replace("[", "',char(91),'",str_replace("]", "',char(93),'",str_replace("|", "',char(124),'",str_replace("\n","\\n",str_replace("\r","\\r",addslashes(fread($fp,1048576)))))))))."')";
			}
			if(eregi("^getdocument (.*)$", $myparam, $regs)) {
				if($this->params[$regs[1]]=="") return"";
				$reportr->background->query("select content from document where title='".addslashes($this->params[$regs[1]])."'");
				$row=$reportr->background->last_result[0];
				foreach($row as $key=>$value) return str_replace("[", "', char(91), '", str_replace("]", "', char(93), '", str_replace("|", "', char(124), '", str_replace("\n","\\n",str_replace("\r","\\r",addslashes($value))))));
				return "";
			}
			if(eregi("^getval (.*)$", $myparam, $regs)) {
				$reportr->background->query($regs[1]); //
				$row=$reportr->background->last_result[0]; 
				if( is_array($row) ){
					foreach($row as $key=>$value) { return $value; }
				}
				return "";
			}
			if(eregi("^setup[(]([0-9]+)[)]$", $myparam, $regs)) {
				$reportr->background->query("select value from setup where code=".$regs[1]); //
				$row=$reportr->background->last_result[0];
				foreach($row as $key=>$value) return $value;
			}
			if(eregi("^SCAN DOC TABLE (CID|BID)$", $myparam, $regs)) {
				$bidcid=strtolower($regs[1]);
				if($bidcid=="cid") $tablename="complaintdoc"; else $tablename="businessdoc";
				if($reportr->background->query("select count(*) from $tablename")) return $tablename;
				$sql = "drop table if exists $tablename";
				$reportr->background->query($sql);
				if($bidcid=="cid")
					$sql = "create table $tablename (BID int, CID int, FileName char(100) not null, key(BID), key(CID))";
				else
					$sql = "create table $tablename (BID int, FileName char(100) not null, key(BID))";
				$reportr->background->query($sql);
				$stack[] = DOCS_BASE_DIR.'/'.$bidcid;
				while ($stack) {
					$current_dir = array_pop($stack);
					if ($dh = opendir($current_dir)) {
						while (($file = readdir($dh)) !== false) {
							if ($file !== '.' AND $file !== '..') {
								$current_file = "{$current_dir}/{$file}";
								if (is_file($current_file)) {
									if(ereg("^.*/docs/$bidcid/([0-9X]+)/([0-9]+)/trash/.*$", $current_file)) continue;
									if(ereg("^.*/docs/$bidcid/([0-9X]+)/([0-9]+)/(.+)$", $current_file, $regs)) {
										if($regs[1]=="XX") $regs[1]="";
										if($bidcid=="cid")
											$sql = "insert into $tablename (bid,cid,filename) values(null, ".$regs[2].$regs[1].", '".addslashes($regs[3])."')";
										else
											$sql = "insert into $tablename (bid,filename) values(".$regs[2].$regs[1].", '".addslashes($regs[3])."')";
										$reportr->background->query($sql);
									}
								} elseif (is_dir($current_file)) {
									$stack[] = $current_file;
								}
							}
						}
					}
				}
				if($bidcid=="cid") {
					$sql = "update $tablename inner join complaint using(cid) set $tablename.bid=complaint.bid";
					$reportr->background->query($sql);
				}
				return $tablename;
			}
			if(eregi("^(REPLICATION IS CURRENT|insync)$",$myparam,$regs)) {
			die($reportr->db->dbhost);
				if($reportr->db->dbhost=="166.70.32.197") return "2";
				$reportr->background->query("show slave status");
				if(!$row=$reportr->background->get_row(null, 0, ARRAY_N)) return "0";
				list(,,,,$slave_read_file,$slave_read_pos,,,$slave_exec_file,,,,,,,,$slave_exec_pos)=$row;
				if($slave_read_file == $slave_exec_file && ($slave_read_pos-$slave_exec_pos)<100000) return "1";
				else return "0";
			}
			if(eregi("^validcc (.*)", $myparam, $regs)) {
				$myparam=$regs[1];
				return "case when left($myparam,1) between 3 and 6 and ((case when length($myparam) between 15 and 16 then right($myparam,1) + if(mid($myparam,15-(length($myparam)=15),1)*2>9,mid($myparam,15-(length($myparam)=15),1)*2-9,mid($myparam,15-(length($myparam)=15),1)*2) + mid($myparam,14-(length($myparam)=15),1) + if(mid($myparam,13-(length($myparam)=15),1)*2>9,mid($myparam,13-(length($myparam)=15),1)*2-9,mid($myparam,13-(length($myparam)=15),1)*2) + mid($myparam,12-(length($myparam)=15),1) + if(mid($myparam,11-(length($myparam)=15),1)*2>9,mid($myparam,11-(length($myparam)=15),1)*2-9,mid($myparam,11-(length($myparam)=15),1)*2) + mid($myparam,10-(length($myparam)=15),1) + if(mid($myparam,9-(length($myparam)=15),1)*2>9,mid($myparam,9-(length($myparam)=15),1)*2-9,mid($myparam,9-(length($myparam)=15),1)*2) + mid($myparam,8-(length($myparam)=15),1) + if(mid($myparam,7-(length($myparam)=15),1)*2>9,mid($myparam,7-(length($myparam)=15),1)*2-9,mid($myparam,7-(length($myparam)=15),1)*2) + mid($myparam,6-(length($myparam)=15),1) + if(mid($myparam,5-(length($myparam)=15),1)*2>9,mid($myparam,5-(length($myparam)=15),1)*2-9,mid($myparam,5-(length($myparam)=15),1)*2) + mid($myparam,4-(length($myparam)=15),1) + if(mid($myparam,3-(length($myparam)=15),1)*2>9,mid($myparam,3-(length($myparam)=15),1)*2-9,mid($myparam,3-(length($myparam)=15),1)*2) + mid($myparam,2-(length($myparam)=15),1) + if(mid($myparam,1-(length($myparam)=15),1)*2>9,mid($myparam,1-(length($myparam)=15),1)*2-9,mid($myparam,1-(length($myparam)=15),1)*2) else -1 end) % 10)=0 then 1 else 0 end";
			}
			if(eregi("^number (.*)",$myparam,$regs)) {
				$myparam=$regs[1];
				return "if($myparam=0,'-',format($myparam,2)) as `$myparam`";
			}
			if(eregi("^rows affected (.+)$",$myparam,$regs)) {
				global $reportr;
//				return $reportr->loop_db." rows affected";
				return $this->params[$reportr->loop_db." rows affected ".$regs[1]];
			}
			if(eregi("^noreplicate_db$",$myparam,$regs)) {
				if(defined("NO_REPLICATE_DB")) return NO_REPLICATE_DB; else return "|db|";
			}
			if(eregi("^money (.*)",$myparam,$regs)) {
				$myparam=$regs[1];
				return "if($myparam=0,'-',format($myparam/100,2)) as `$myparam`";
			}
			if(eregi("^yahoomap1 (.*)$",$myparam,$regs)) {
				if(strtoupper($reportr->get_var("select reportGoogleMaps from common.config where ebindrdatabase=database() limit 1"))=="Y") $this->params[$myparam]="concat('<a target=new href=\'http://maps.google.com/?q=',urlencode(".$regs[1].".street1),' ',urlencode(".$regs[1].".city),', ',urlencode(".$regs[1].".stateprov),'&zip=',".$regs[1].".postalcode,'\'><img border=0 vspace=0 style=\'display:inline;position:absolute;left:197px\' src=\'/css/map.gif\'></a>')";
				else $this->params[$myparam]="concat('<a target=new href=\'http://maps.yahoo.".((COUNTRY=="CANADA")?"ca":"com")."/py/maps.py?addr=',urlencode(".$regs[1].".street1),'&zip=',".$regs[1].".postalcode,'\'><img border=0 vspace=0 style=\'display:inline;position:absolute;left:197px\' src=\'/css/map.gif\'></a>')";
				return $this->params[$myparam];
			}
			if(eregi("^yahoomap2 (.*)$",$myparam,$regs)) {
				if(strtoupper($reportr->get_var("select reportGoogleMaps from common.config where ebindrdatabase=database() limit 1"))=="Y") $this->params[$myparam]="concat('<a target=new href=\'http://maps.google.com/?q=',urlencode(".$regs[1].".street1),' ',urlencode(".$regs[1].".city),', ',urlencode(".$regs[1].".stateprov),'&zip=',".$regs[1].".postalcode,'\'><img border=0 vspace=0 width=18 height=17 src=\'/css/map.gif\'></a>')";
				else $this->params[$myparam]="concat('<a target=new href=\'http://maps.yahoo.".((COUNTRY=="CANADA")?"ca":"com")."/py/maps.py?addr=',urlencode(".$regs[1].".street1),'%20',".$regs[1].".postalcode,'\'><img border=0 vspace=0 width=18 height=17 src=\'/css/map.gif\'></a>')";
				return $this->params[$myparam];
			}
			if(eregi("^(\*|\@)",$myparam,$regs) && !empty($this->params[$myparam])) {
				if(strlen($this->params[$myparam])<200) $this->usedparameters[$myparam]=stripslashes($this->params[$myparam]);
				return stripslashes($this->params[$myparam]);
			}
			if(eregi("^fieldurlencode (.*)$", $myparam, $regs)) {
				$this->params[$myparam]="replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(".$regs[1].",char(37),'%25'),char(32),'%20'),char(33),'%21'),char(34),'%22'),char(35),'%23'),char(36),'%24'),char(38),'%26'),char(39),'%27'),char(40),'%28'),char(41),'%29'),char(42),'%2A'),char(43),'%2B'),char(44),'%2C'),char(45),'%2D'),char(46),'%2E'),char(47),'%2F'),char(59),'%3B'),char(60),'%3C'),char(61),'%3D'),char(62),'%3E'),char(63),'%3F'),char(64),'%40'),char(91),'%5B'),char(92),'%5C'),char(93),'%5D'),char(94),'%5E'),char(95),'%5F'),char(96),'%60')";
				return $this->params[$myparam];
			}
			if(!(strpos("|" . $myparam, "|shadow ") === false))
			{
				$myparam=trim(str_replace("shadow ","",$myparam));
				return "shadow($myparam)";
//				return "replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(if($myparam regexp concat(char(94),'the '),mid($myparam,5),$myparam),' and ',''),'.',''),'!',''),'@',''),'#',''),'$',''),'%',''),char(6),''),'&',''),'*',''),'(',''),')',''),'-',''),'_',''),'=',''),'+',''),' ',''),';',''),'\\'',''),char(34),''),'/',''),',','')";
			}
			if(!(strpos("|" . $myparam, "|stripped ") === false))
			{
				$myparam=trim(str_replace("stripped ","",$myparam));
				return "stripped($myparam)";
//				return "replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace($myparam,' and ',''),'.',''),'!',''),'@',''),'#',''),'$',''),'%',''),char(6),''),'&',''),'*',''),'(',''),')',''),'-',''),'_',''),'=',''),'+',''),';',''),'\\'',''),char(34),''),'/','')";
			}
			if(!(strpos("|" . $myparam, "|shadowval ") === false))
			{
				$myparam=trim(str_replace("shadowval ","",$myparam));
				$ret=trim(strtolower($this->params[$myparam]));
				$ret=ereg_replace("['\.!@\#\$\^&\*\(\)_=\+/;\\\"]","",$ret);
				$ret=ereg_replace("([^a-z])(inc|llc)($|%)","\\1\\3",$ret);
				$ret=ereg_replace("([^a-z])(company|co)($|%)","\\1\\3",$ret);
				$ret=ereg_replace("([^a-z])(corporation|corp)($|%)","\\1\\3",$ret);
				$ret=str_replace("-","",eregi_replace(" and%$","%",eregi_replace(" and ","",eregi_replace("^([%]{0,1})[ ]*the ","\\1",$ret))));
				$ret=str_replace(","," ",$ret);
				$ret=ereg_replace("([^a-z])(the)($|%)","\\1\\3",$ret);
				return str_replace(" ","",$ret);
//				return str_replace("-","",ereg_replace("[,'\.!@\#\$\^&\*\(\)_=\+ /;\\\"]","",eregi_replace(" and%$","%",eregi_replace(" and ","",eregi_replace("^([%]{0,1})[ ]*the ","\\1",trim($this->params[$myparam]))))));
			}
			if(isset($this->params[$myparam])) {
				if(isset($_POST[$myparam])) return str_replace("|","{PIPE}",$this->params[$myparam]);
				$this->usedparameters[$myparam]=$this->params[$myparam];
				return $this->params[$myparam];
			} else
			{
				if(@!in_array($myparam, $this->orphan) && !ereg("NOPROMPT", $myparam) && $myparam!='ebindr2' && !ereg("^limit[0-9]", $myparam) && !ereg("^limitback[0-9]", $myparam) && !ereg("^which_table", $myparam))
					$this->orphan[] = $myparam;
//					echo $myparam."<br>\r\n";
			}
		}
		
		/**
		 * @return array
		 * @param string string
		 * @desc returns new query and subqueries
		 */
		function subquery($string)
		{
			// in fall-back cases
			$original = $string;
			// Loops until it can't find any more ( ) pairs with no ( ) between them
			while(ereg("\( *([[:alpha:]]*)[^()]*\) *([[:alpha:]]*)", $string, $value)) {
					if(trim(strtolower($value[1])) !="select" || trim(strtolower($value[2])) != "as")
						// if there is no 'select' or 'as' before or after the ( ) pair, then replace the ( ) with &lp; and &rp;
					   $string = str_replace($value[0], str_replace(")", "&rp;", str_replace("(", "&lp;", $value[0])), $string);
					 else
						// looping ends if there is a 'select' and an 'as' before and after the ( ) pair
						break;
			}
			
			// Loops until it can't find any more subqueries in the format:   ( ... ) as ...
			while(ereg("\(([^()]*)\) *as ([[:alpha:]\|]*)", $string, $value))
			{
				// If 'select' is not at the beginning of the subquery found, then replace the ( ) with &lp; and &rp;
				if(!ereg("\( *select", $value[0]))
					$string = str_replace($value[0], str_replace(")", "&rp;", str_replace("(", "&lp;", $value[0])), $string);
				else
				{
					// Store the create table syntax for the subquery
					// $value[1] contains the select statement, and $value[2] contains the subquery table name
					$sub_query[] = "CREATE TABLE my" . $value[2] . "  " . str_replace("&lp;", "(", str_replace("&rp;", ")", $value[1]));
					$tables[] = "my" . $value[2];
					$string = str_replace($value[0], "my" . $value[2], $string);
				}
			}
			return array("num_sub" => sizeof($sub_query), "tables" => $tables, "query" => $string, "subquery" => $sub_query, "original" => $original);
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
			$display = new display(array("table", "prompt", "prompt_selector"));
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
		 * @param myparam string
		 * @desc gets called on each parameter passed and finds defaults and validations
		 */		
		function def_val($myparam)
		{
			global $task;
			$doselected=false;
			if(ereg("~([^~]+)$", $myparam, $regs)) {
				$myparam=ereg_replace("~[^~]+$", "", $myparam);
				$this->instructions[ereg_replace("^([^:]+):.+$", "\\1", $myparam)]=$regs[1];
			}
			if(!ereg("^ifhurdman", $myparam) && strpos($myparam, ":"))
			{
				$string = explode(":", $myparam);
				if(sizeof($string) > 1)
				{
					for($i=1; $i<sizeof($string); $i++)
					{	
						if(eregi("^=", $string[$i+1])) {
							$string[$i+1]=$string[$i].":".$string[$i+1];
							$i++;
						}
						if(eregi("^select ", $string[$i]) || eregi("^[(]select ", $string[$i]))
						{
							$string[$i]=ereg_replace("PIPE", "|", $string[$i]);
							$myquery=$this->resolve_pipes($this->resolve_merge($string[$i]));
							$temp = $task->get_results($myquery);
							if($task->num_rows > 0 && is_array($temp)) {
								foreach($temp as $row) {
									if(count($task->col_info) > 2) {
										$options[] = array(0 => $row[$task->col_info[0]->name], 1 => $row[$task->col_info[1]->name], 2 => $row[$task->col_info[2]->name]);
										if($row[$task->col_info[2]->name]=="selected") $doselected=true;
									} elseif(count($task->col_info) > 1)
										$options[] = array(0 => $row[$task->col_info[0]->name], 1 => $row[$task->col_info[1]->name]);
									else
										$options[] = array(1 => $row[$task->col_info[0]->name]);
								}
							}
						}
						else
							$options[] = array(1 => $string[$i]);
					}
				}
				else
					$options[] = $string;
				if(!$doselected) $options[0][2]="selected";
				$temp = explode(":", $myparam);
				if(strpos($temp[0], "date")) $this->pipe_validate[$temp[0]] = $temp[0];
				$this->pipe_options[$string[0]] = $options;
				$myparam = $string[0];
			}
			return $myparam;
		}
		
		/**
		 * @return string
		 * @param myqueries string
		 * @desc gets the next query
		 */
		function get_next_query($myqueries)
		{
			$myqueries = str_replace("||","\xee",$myqueries);
			ereg("\xee[^\xee]+\xee", $myqueries, $returned);
			$returned[0] = str_replace("\xee","||",$returned[0]);
			return $returned[0];
		}
		
		/**
		 * @return string
		 * @param mytext string
		 * @desc gets the next if merge code in sequence
		 */
		function get_next_if_merge($mytext)
		{
			ereg ("<<[^<>]*\[[^]]*\][^<>]*>>", $mytext, $returned);
			return $returned[0];
		}

		/**
		 * @return string
		 * @param mytext string
		 * @desc gets the next merge code in sequence
		 */
		function get_next_merge_code($mytext)
		{
			ereg ("\[[^]]*\]",  $mytext, $returned);
			return $returned[0];
		}
		
		/**
		 * @return string
		 * @param mytext string
		 * @desc gets the next pipe in sequence
		 */
		function get_next_pipe($mytext)
		{
			ereg ("[^|](\|[^|]+\|)([^|]|$)",  $mytext, $returned);
			return $returned[1];
		}
		
		function setreportdefaults() {
			global $task, $reportr;
			$find = $task->get_results("SELECT parameter, value FROM reportdefault WHERE MergeCode = '" . $reportr->current_query . "'", ARRAY_N);
			if(sizeof($find)<1) $find = $task->get_results("SELECT parameter, value FROM reportdefault WHERE MergeCode = '" . $this->params[exportrquery] . "'", ARRAY_N);
			if(sizeof($find)<1) return true;
			foreach($find as $row) {
				list($key, $value)=$row;
				if(eregi("^select ",$value)) list(list($value))=$task->get_results($value, ARRAY_N);
				$this->params[$key]=$value;
			}
			return true;
		}
		function LicenseUpdate($table) {
			global $task, $reportr;
			$ch = curl_init();
			$agent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.4) Gecko/20030624 Netscape/7.1 (ax)"; // identify as your own user agent (like ^ÑMSIE^Ò) if you want
			$cookie_file_path = "";
			curl_setopt($ch, CURLOPT_USERAGENT, $agent);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_COOKIEFILE, "/var/tmp/licenseupdate_".$_COOKIE["reportr_username"]);
			curl_setopt($ch, CURLOPT_COOKIEJAR, "/var/tmp/licenseupdate_".$_COOKIE["reportr_username"]);
			$find = $task->get_results("SELECT bid, lid, url, phpcode from $table where url>'' and newexpirydate is null", ARRAY_N);
			if(is_array($find)) foreach($find as $row) {
				list($bid, $lid, $url, $phpcode)=$row;
				curl_setopt($ch, CURLOPT_URL, $url);
				$rawbuffer=curl_exec($ch);
				$buffer = ereg_replace("<[^<>]*>","~",$rawbuffer);
				$curlops=curl_getinfo($ch);
//				if($curlops["CURLINFO_EFFECTIVE_URL"]!=$url) {
//					$rawbuffer=curl_exec($ch);
//					$buffer = ereg_replace("<[^<>]*>","~",$rawbuffer);
//				}
				$mydate="";$licensecomment="";$licensestatus="Undetermined";
				if($phpcode>"") {
					eval($phpcode);
				}
				if($mydate=="") {
					if(eregi("~([^~]*expire date[^~]*)~+([^~]+)~",$buffer,$regs)) $mydate=date("Y-m-d",strtotime($regs[2]));
					elseif(eregi("~([^~]*expir[^~]*)~+([0-9/-]+)~",$buffer,$regs)) $mydate=date("Y-m-d",strtotime($regs[2]));
					elseif(eregi("~([^~]*expir[^~]*)~+([^~]+)~",$buffer,$regs)) $mydate=date("Y-m-d",strtotime($regs[2]));
					elseif(eregi("(expiration[^0-9]+)([0-9][0123456789\/]+)~",$buffer,$regs)) $mydate=date("Y-m-d",strtotime($regs[2]));
					elseif(eregi("~(exp date[^0-9]+)([0-9][^~]+)~",$buffer,$regs)) $mydate=date("Y-m-d",strtotime($regs[2]));
				}
				if($licensecomment!='') $reportr->background->query("update $table set newcomment='".addslashes($licensecomment)."' where bid=$bid and lid=$lid");
				if($licensestatus!='') $reportr->background->query("update $table set newstatus='".addslashes($licensestatus)."' where bid=$bid and lid=$lid");
				if($mydate) $reportr->background->query("update $table set newexpirydate='".date("Y-m-d",strtotime($mydate))."' where bid=$bid and lid=$lid");
				else $reportr->background->query("update $table set newexpirydate='0000-00-00' where bid=$bid and lid=$lid");                                     
			}
			curl_close ($ch);

		}		
		function TitleCase($mystr) {
			$parts = explode(' ', $mystr);
			if(sizeof($parts) == 0) return '';
			foreach($parts as $i) {
				$fix = '';
				if(in_array($i,$this->noLC))
					$fix = strtolower($i);
				elseif(eregi("^[A-Z][.]+$", $i)) // will mess up "i.e." and like
					$fix = strtoupper($i);
				elseif(eregi("^[^aeiouy]+$") && ereg("^[a-z]+$", $i) && !in_array($i, $this->noACRO)) // voweless words are almost always acronyms
					$fix = strtoupper($i);
				elseif(eregi("^[A-Z]{1,2}$", $i) && !in_array($i, $this->noACRO)) // voweless words are almost always acronyms
					$fix = strtoupper($i);
				elseif(ereg("^[A-Z]{2}[a-z]+$", $i))
					$fix = strtoupper(substr($i,0,1)).strtolower(substr($i,1,1)).substr($i,2,strlen($i)-2);
				else
					$fix = strtoupper(substr($i,0,1)).substr($i,1,strlen($i));
				$fixed[]=$fix;
			}
			$fixed[0] = strtoupper(substr($fixed[0],0,1)).substr($fixed[0],1,strlen($fixed[0]));
			return implode(' ', $fixed);
		}
		function GetCouncilComplaints($startdate, $enddate, $whichserver=0) {
			global $reportr;
			$reportr->query("update setup set value=if(@oldvalue:=value,now(),now()) where code=734");
			require_once('/home/serv/library/nusoap.php');
			if($whichserver==0) $serverpath='https://odr-ws.bbb.org/ComplaintExport/complaintexport.asmx';
			else $serverpath='http://download.bbb.org/ws/ComplaintExport/ComplaintExport.asmx';
			// create client object
			$client = new nusoapclient($serverpath, true, false, false, false, false, 0, 600);
			// make the call
			$bureauid=$reportr->get_var("select setup(318)");
			$mycount=0;
			while($startdate<=$enddate) {
				$param = array("StartDate"=>$startdate, "EndDate"=>$startdate, "BureauID"=>$bureauid, "UserName"=>"hurdmanbureaus", "Password"=>"h@#456643wel");
				$startdate=date("Y-m-d", strtotime("$startdate+1 day"));
				$result = $client->call('GetAllComplaints',$param,"http://tempuri.org/ComplaintExport/Service1", 'http://tempuri.org/ComplaintExport/Service1/GetAllComplaints', null, null, "rpc", "literal");
				if(isset($result["faultstring"])) {
					$reportr->query("update setup set value=@oldvalue where code=734");
					return htmlspecialchars($result["faultstring"])." Zero";
				}
				if(!is_array($result["diffgram"])) continue;
				$complaints=$result["diffgram"]["NewDataSet"]["Table"];
				if(!is_array($complaints)) continue;
				if(!is_array($complaints[0])) $complaints=array($complaints);
				foreach($complaints as $complaint) {
					$query = "insert ignore into councilcomplaint (ComplaintID, BBBID, Title, FirstName, MiddleName, LastName, Suffix, Address1, Address2, City, State, ZipCode, ZipCodeExtention, Country, PhoneNumber, PhoneNumberExtention, PhoneNumberEvening, PhoneNumberEveningExtention, FaxNumber, ConsumerEmailAddress, BusinessID, BusinessName, BusinessAddress, BusinessCity, BusinessState, BusinessZipCode, BusinessPhoneNumber, BusinessPhoneNumberExtension, BusinessURL, DateFiled, Classification1, Classification2, NarrativeDescription, DesiredOutcome, DesiredSettlementID, Product_Or_Service, Order_Number, Account_Number, DateServiceStarted, Purchase_Price, Disputed_Amount, BusinessParentID, BranchName, BranchAddress, BranchCity, BranchState, BranchZipCode, BranchPhoneNumber, BranchURL, DownloadedDate) values('".addslashes($complaint["ComplaintID"])."', '".addslashes($complaint["BBBID"])."', '".addslashes($complaint["Title"])."', '".addslashes($complaint["FirstName"])."', '".addslashes($complaint["MiddleName"])."', '".addslashes($complaint["LastName"])."', '".addslashes($complaint["Suffix"])."', '".addslashes($complaint["Address1"])."', '".addslashes($complaint["Address2"])."', '".addslashes($complaint["City"])."', '".addslashes($complaint["State"])."', '".addslashes($complaint["ZipCode"])."', '".addslashes($complaint["ZipCodeExtention"])."', '".addslashes($complaint["Country"])."', '".addslashes($complaint["PhoneNumber"])."', '".addslashes($complaint["PhoneNumberExtention"])."', '".addslashes($complaint["PhoneNumberEvening"])."', '".addslashes($complaint["PhoneNumberEveningExtention"])."', '".addslashes($complaint["FaxNumber"])."', '".addslashes($complaint["ConsumerEmailAddress"])."', '".addslashes($complaint["BusinessID"])."', '".addslashes($complaint["BusinessName"])."', '".addslashes($complaint["BusinessAddress"])."', '".addslashes($complaint["BusinessCity"])."', '".addslashes($complaint["BusinessState"])."', '".addslashes($complaint["BusinessZipCode"])."', '".addslashes($complaint["BusinessPhoneNumber"])."', '".addslashes($complaint["BusinessPhoneNumberExtension"])."', '".addslashes($complaint["BusinessURL"])."', '".addslashes($complaint["DateFiled"])."', '".addslashes($complaint["Classification1"])."', '".addslashes($complaint["Classification2"])."', '".addslashes(html_entity_decode($complaint["NarrativeDescription"]))."', '".addslashes($complaint["DesiredOutcome"])."', '".addslashes($complaint["DesiredSettlementID"])."', '".addslashes($complaint["Product_Or_Service"])."', '".addslashes($complaint["Order_Number"])."', '".addslashes($complaint["Account_Number"])."', '".addslashes($complaint["DateServiceStarted"])."', '".addslashes($complaint["Purchase_Price"])."', '".addslashes($complaint["Disputed_Amount"])."', '".addslashes($complaint["BusinessParentID"])."', '".addslashes($complaint["BranchName"])."', '".addslashes($complaint["BranchAddress"])."', '".addslashes($complaint["BranchCity"])."', '".addslashes($complaint["BranchState"])."', '".addslashes($complaint["BranchZipCode"])."', '".addslashes($complaint["BranchPhoneNumber"])."', '".addslashes($complaint["BranchURL"])."', now())";
					$reportr->query($query);
					if($reportr->affected_rows>0) $mycount+=$reportr->affected_rows;
					//echo $query."\r\n";
				}
				unset($complaints);
			}
			unset($result);
			unset($client);
			if($mycount==0) $mycount="Zero";
			return $mycount;
	
		}

		function GetNewCouncilComplaints() {
			global $task, $reportr;
			$reportr->query("flush table common.councilcomplaint");
			list($host,$port)=explode(":",DATABASE_HOST);
			if($port=="") $port="3306";
			$bureauid=$reportr->get_var("select setup(318)");
			echo "<!-- ".htmlspecialchars(shell_exec("mysqldump -h$host -u".DATABASE_USER." -p".DATABASE_PASS." -P$port -c --disable-keys=false --insert-ignore --lock-tables=false --no-create-info --triggers=false common councilcomplaint -w\"bbbid='$bureauid' and confirmationticket is null\" | mysql -h$host -u".DATABASE_USER." -p".DATABASE_PASS." -P$port ".$reportr->selected_db." >> /dev/stdout 2>> /dev/stdout"))."-->";
//			$reportr->query("insert ignore into councilcomplaint select * from common.councilcomplaint where bbbid=setup(318) and confirmationticket is null");
			$reportr->query("select complaintid from councilcomplaint where imported='n'");
			$mycount=$reportr->num_rows;
			$commondb=mysql_connect(COMMON_HOST, COMMON_USER, COMMON_PASS);
			$query="select l.complaintid, l.assignedcid, l.assignedbid from councilcomplaint l inner join common.councilcomplaint c using(complaintid) where c.assignedcid is null and l.assignedcid is not null";
			$find = $task->get_results($query, ARRAY_N);
			foreach($find as $row) {
				list($complaintid, $assignedcid, $assignedbid)=$row;
				mysql_db_query("common", "update councilcomplaint set assignedcid=$assignedcid, assignedbid=$assignedbid, imported='y' where complaintid=$complaintid", $commondb);
			}

			$query="select l.complaintid from councilcomplaint l inner join common.councilcomplaint c using(complaintid) where c.assignedcid is null and l.assignedcid is null and l.imported='x'";
			$find = $task->get_results($query, ARRAY_N);
			foreach($find as $row) {
				list($complaintid, $assignedcid, $assignedbid)=$row;
				mysql_db_query("common", "update councilcomplaint set assignedcid=9999, assignedbid=9999, imported='x' where complaintid=$complaintid", $commondb);
			}

			mysql_close($commondb);
			if($mycount==0) $mycount="Zero";
			return $mycount;
			
			$reportr->query("update setup set value=if(@oldvalue:=value,now(),now()) where code=734");
			require_once('/home/serv/library/nusoap.php');
			$serverpath="https://odr-ws.bbb.org/ComplaintRouting/routingservice.asmx";
			// create client object
			$client = new nusoapclient($serverpath, true, false, false, false, false, 0, 600);
			// make the call
			$bureauid=$reportr->get_var("select setup(318)");
			$query="select complaintid, assignedcid from councilcomplaint where confirmationticket is null";
			$find = $task->get_results($query, ARRAY_N);
			foreach($find as $row) {
				list($complaintid, $assignedcid)=$row;
				$param = array("bbbid"=>$bureauid, "userName"=>"hurdmanbureaus", "password"=>"h@#456643wel", "complaintExportTicket"=>array("ComplaintExportTicket"=>array("ODRComplaintID"=>$complaintid, "BBBComplaintID"=>$assignedcid)));
				$result = $client->call('ComplaintConfirmationTicket',$param,"http://cdw.bbb.org/schema", 'http://cdw.bbb.org/schema/ComplaintConfirmationTicket', null, null, "rpc", "literal");
				$reportr->query("update councilcomplaint set confirmationticket=$result where complaintid=$complaintid");
				unset($result);
			}
			
			$mycount=0;
			$param = array("bbbid"=>$bureauid, "userName"=>"hurdmanbureaus", "password"=>"h@#456643wel");
			$result = $client->call('GetPendingComplaints',$param,"http://cdw.bbb.org/schema", 'http://cdw.bbb.org/schema/GetPendingComplaints', null, null, "rpc", "literal");
			if(!is_array($result)) {
				$reportr->query("update setup set value=@oldvalue where code=734");
				return htmlspecialchars($result)." Zero";
			}
			$complaints=$result["ComplaintExport"];
			if(!is_array($complaints[0])) $complaints=array($complaints);
			foreach($complaints as $complaint) {
				$query = "insert ignore into councilcomplaint (ComplaintID, BBBID, Title, FirstName, MiddleName, LastName, Suffix, Address1, Address2, City, State, ZipCode, ZipCodeExtention, Country, PhoneNumber, PhoneNumberExtention, PhoneNumberEvening, PhoneNumberEveningExtention, FaxNumber, ConsumerEmailAddress, BusinessID, BusinessName, BusinessAddress, BusinessCity, BusinessState, BusinessZipCode, BusinessPhoneNumber, BusinessPhoneNumberExtension, BusinessURL, DateFiled, Classification1, Classification2, NarrativeDescription, DesiredOutcome, DesiredSettlementID, Product_Or_Service, Order_Number, Account_Number, DateServiceStarted, Purchase_Price, Disputed_Amount, BusinessParentID, BranchName, BranchAddress, BranchCity, BranchState, BranchZipCode, BranchPhoneNumber, BranchURL, DownloadedDate) values('".addslashes($complaint["ComplaintID"])."', '".addslashes($complaint["BBBID"])."', '".addslashes($complaint["Title"])."', '".addslashes($complaint["FirstName"])."', '".addslashes($complaint["MiddleName"])."', '".addslashes($complaint["LastName"])."', '".addslashes($complaint["Suffix"])."', '".addslashes($complaint["Address1"])."', '".addslashes($complaint["Address2"])."', '".addslashes($complaint["City"])."', '".addslashes($complaint["State"])."', '".addslashes($complaint["ZipCode"])."', '".addslashes($complaint["ZipCodeExtention"])."', '".addslashes($complaint["Country"])."', '".addslashes($complaint["PhoneNumber"])."', '".addslashes($complaint["PhoneNumberExtention"])."', '".addslashes($complaint["PhoneNumberEvening"])."', '".addslashes($complaint["PhoneNumberEveningExtention"])."', '".addslashes($complaint["FaxNumber"])."', '".addslashes($complaint["ConsumerEmailAddress"])."', '".addslashes($complaint["BusinessID"])."', '".addslashes($complaint["BusinessName"])."', '".addslashes($complaint["BusinessAddress"])."', '".addslashes($complaint["BusinessCity"])."', '".addslashes($complaint["BusinessState"])."', '".addslashes($complaint["BusinessZipCode"])."', '".addslashes($complaint["BusinessPhoneNumber"])."', '".addslashes($complaint["BusinessPhoneNumberExtension"])."', '".addslashes($complaint["BusinessURL"])."', '".addslashes($complaint["DateFiled"])."', '".addslashes($complaint["Classification1"])."', '".addslashes($complaint["Classification2"])."', '".addslashes(html_entity_decode($complaint["NarrativeDescription"]))."', '".addslashes($complaint["DesiredOutcome"])."', '".addslashes($complaint["DesiredSettlementID"])."', '".addslashes($complaint["Product_Or_Service"])."', '".addslashes($complaint["Order_Number"])."', '".addslashes($complaint["Account_Number"])."', '".addslashes($complaint["DateServiceStarted"])."', '".addslashes($complaint["Purchase_Price"])."', '".addslashes($complaint["Disputed_Amount"])."', '".addslashes($complaint["BusinessParentID"])."', '".addslashes($complaint["BranchName"])."', '".addslashes($complaint["BranchAddress"])."', '".addslashes($complaint["BranchCity"])."', '".addslashes($complaint["BranchState"])."', '".addslashes($complaint["BranchZipCode"])."', '".addslashes($complaint["BranchPhoneNumber"])."', '".addslashes($complaint["BranchURL"])."', now())";
				$reportr->query($query);
				if($reportr->affected_rows>0) $mycount+=$reportr->affected_rows;
				//echo $query."\r\n";
			}
			unset($complaints);
			unset($result);
			unset($client);
			if($mycount==0) $mycount="Zero";
			return $mycount;
	
		}

		function ObjToMySQL($obj, $table=0) {
			if(!is_object($obj)) {
				$value=$obj;
				$obj=new stdClass();
				$obj->value=$value;
			}
			$inserts=array(); $creates=array();
			foreach($obj as $key=>$val) {
					if(is_array($val)) {
							foreach($val as $obj) {
								list($create, $insert)=$this->ObjToMySQL($obj, $table+1);
								$inserts=array_merge($inserts,$insert);
							}
							$creates=array_merge($create, $creates);
							$fields[]=$key;
							$vals[]="'_json_results_".($table+1)."'";
					} else {
							$fields[]=$key;
							$vals[]="'".addslashes($val)."'";
					}
			}
			$inserts[]="insert into _json_results_$table (".implode(", ",$fields).") values (".implode(", ", $vals).");";
			$creates[]="create temporary table _json_results_$table (".implode(" text, ", $fields)." text);";
			return array($creates, $inserts);
		}		

		function googleAnalyticsImport( $method, $day, $end = null ) {
			global $reportr;
			// bring in the needed gapi, hurdman-ga and import wrapper class
			include_once "../public_html/ebindr/includes/gapi.class.php";
			include_once "../public_html/ebindr/includes/hurdman-ga.php";
			include_once "../public_html/ebindr/includes/functions.php";
			include_once "../public_html/ebindr/includes/google-analytics-import.php";


			/*function is_class_method($type="public", $method, $class) {
        		try {
				    $refl = new ReflectionMethod($class, $method);
    				switch($type) {
        				case "static":
        					return $refl->isStatic();
        					break;
						case "public":
					        return $refl->isPublic();
					        break;
						case "private":
					        return $refl->isPrivate();
					        break;
					}
				} catch( Exception $e) {
					return false;
				}
			}*/
			
			// check to see if we are using council's profile or not
			if( preg_match( "/^Council/i", $method ) ) {
				$usecouncil = true;
				$method = str_replace( "Council", "", $method );
			} else {
				$usecouncil = false;
			}

			
			if( !isset( $this->_analytics ) ) {
				// get this bbb's google analytics profile id
				$reportr->background->query("select id, profile, councilprofile from common.ganalyticsid where bbbid = setup(318) limit 1", ARRAY_N);
	
				// return if we don't have google analytics profile configured
				if( sizeof($reportr->background->last_result) < 1 ) {
					return -1; //"Google analytics import cannot run because there is no profile configured."
				}
			
				// connect to google
				$analytics = new hurdman_ga('bbbseohurd@gmail.com', 'Hurd2SEO802*');
				$analytics->profile = ( $usecouncil ? $reportr->background->last_result[0]["councilprofile"] : $reportr->background->last_result[0]["profile"] );
				$analytics->usecouncil = $usecouncil;
				
				// set the global variable
				$this->_analytics = $analytics;
			} else {
				$this->_analytics->usecouncil = $usecouncil;
			}
			
			// call the method and return the result
			//if( is_class_method( "public", $method, "GoogleAnalyticsImport" ) ) {
				return GoogleAnalyticsImport::$method( $this->_analytics, $day, $reportr, $end );
			//} else return 'nomethod';
		}
		function GAImportMobile($day) {
			global $reportr;
			include_once "../public_html/ebindr/includes/gapi.class.php";
			include_once "../public_html/ebindr/includes/hurdman-ga.php";
			
			include_once "../public_html/ebindr/includes/functions.php";
			
			// check to see if we have any entries for the $day we don't want to run it again for the same day
			$rows = $reportr->background->get_var( "select count(*) from gareportsmobile where day like '" . $day . "%'" );
			if( $rows > 0 ) {
				//die( "Already ran google analytics import for $day" );
				return -1;
			}
			
			// get this bbb's google analytics profile id
			$reportr->background->query("select id, profile from common.ganalyticsid where bbbid = setup(318) limit 1", ARRAY_N);
			
			if( sizeof($reportr->background->last_result) < 1 ) {
				return -1; //"Google analytics import cannot run because there is no profile configured."
			}
		if( phpversion() != '5.0.4' ) {
			// connect to google analytics
			$analytics = new hurdman_ga('bbbseohurd@gmail.com', 'Hurd2SEO802*');
//			list( $id, $profile ) = $reportr->background->last_result[0];
			$id = $reportr->background->last_result[0]["id"];
			$profile = $reportr->background->last_result[0]["profile"];
			//$profile = '3835649';
			$analytics->profile = $profile;
		}
			//$analytics->setProfile($id,'webPropertyId',$profile);
			
			// pages of results in case we have some results that are longer than 10,000 records
			$pages = array();
			$loop = 0;
			$perpage = 10000;
			
			// get the analytics for the directory
			while(1) {
				if( phpversion() == '5.0.4' ) {
					$reportr->background->query("select setup(126) as url");
					$host = str_replace( "atlanticprovinces", "maritimeprovinces", $reportr->background->last_result[0]["url"] );
					$data = json_decode(file_get_contents("http://".$host."/private-api/ebindr/ganalytics/reportstatsmobile/$day"));
					$data = $data->results;
					foreach( $data as $i => $row ) {
						$data[$i] = (array) $row;
					}
				} else $data = $analytics->reportStatsMobile($day, null, ($loop*$perpage == 0 ? 1 : ($loop*$perpage)+1), $perpage );
				if( sizeof($data) > 0 ) $pages[] = $data;
				$loop++;
				// if we didn't have more than 10,000 then don't loop again
				if( sizeof($data) != $perpage ) {
					break;
				}
			}
			$totalrecords=0;

			if(sizeof($pages)==0) $reportr->background->query( "insert into gareportsmobile (day, path) values ('$day', '- No data for this date -')" );// or die(mysql_error());
			foreach( $pages as $p => $data ) {
				foreach( $data as $i => $row ) {
					foreach($row as $key => $value ) $row[$key] = mysql_real_escape_string(str_replace( "(not set)", "", $value ));
					$totalrecords++;
					$reportr->background->query( $onequery="insert into gareportsmobile (bid, day, mobile, mobiledeviceinfo, mobiledevicebranding, overview, complaints, photos, directions, customerreviews, equote, filecomplaint, pageviews, uniquepageviews, path) values ('".$row['bid']."', '".$row['day']."', ".( $row['mobile'] ? '1' : '0' ).", '".$row['mobiledeviceinfo']."', '".$row['mobiledevicebranding']."', '".$row['overview']."', '".$row['complaints']."', '".$row['photos']."', '".$row['directions']."', '".$row['customerreviews']."', '".$row['equote']."', '".$row['filecomplaint']."',  '".$row['pageviews']."', '".$row['uniquepageviews']."', '".$row['path']."') on duplicate key update overview=overview+'".$row['overview']."', complaints=complaints+'".$row['complaints']."', photos=photos+'".$row['photos']."', directions=directions+'".$row['directions']."', customerreviews=customerreviews+'".$row['customerreviews']."', equote=equote+'".$row['equote']."', filecomplaint=filecomplaint+'".$row['filecomplaint']."', pageviews=pageviews+'".$row['pageviews']."', uniquepageviews=uniquepageviews+'".$row['uniquepageviews']."'");
//					echo $onequery."<br>\r\n";
				}
			
			}
			return $totalrecords;

		}
				
		function GAImport($day) {
			global $reportr;
			include_once "../public_html/ebindr/includes/gapi.class.php";
			include_once "../public_html/ebindr/includes/hurdman-ga.php";
			
			include_once "../public_html/ebindr/includes/functions.php";
			
			// check to see if we have any entries for the $day we don't want to run it again for the same day
			$rows = $reportr->background->get_var( "select count(*) from gadirectory where day like '" . $day . "%'" );
			if( $rows > 0 ) {
				//die( "Already ran google analytics import for $day" );
				return -1;
			}
			
			// get this bbb's google analytics profile id
			$reportr->background->query("select id, profile from common.ganalyticsid where bbbid = setup(318) limit 1", ARRAY_N);
			
			if( sizeof($reportr->background->last_result) < 1 ) {
				return -1; //"Google analytics import cannot run because there is no profile configured."
			}

		if( phpversion() != '5.0.4' ) {
			// connect to google analytics
			$analytics = new hurdman_ga('bbbseohurd@gmail.com', 'Hurd2SEO802*');
//			list( $id, $profile ) = $reportr->background->last_result[0];
			$id = $reportr->background->last_result[0]["id"];
			$profile = $reportr->background->last_result[0]["profile"];
			//$profile = '3835649';
			$analytics->profile = $profile;
		}
			//$analytics->setProfile($id,'webPropertyId',$profile);
			
			// pages of results in case we have some results that are longer than 10,000 records
			$pages = array();
			$loop = 0;
			$perpage = 10000;
			
			// get the analytics for the directory
			while(1) {
				if( phpversion() == '5.0.4' ) {
					$reportr->background->query("select setup(126) as url");
					$host = str_replace( "atlanticprovinces", "maritimeprovinces", $reportr->background->last_result[0]["url"] );
					$data = json_decode(file_get_contents("http://".$host."/private-api/ebindr/ganalytics/directorystats/$day"));
					$data = $data->results;
					foreach( $data as $i => $row ) {
						$data[$i] = (array) $row;
					}
					} else $data = $analytics->directoryStats($day, null, ($loop*$perpage == 0 ? 1 : ($loop*$perpage)+1), $perpage );
				if( sizeof($data) > 0 ) $pages[] = $data;
				$loop++;
				// if we didn't have more than 10,000 then don't loop again
				if( sizeof($data) != $perpage ) {
					break;
				}
			}
			
			$totalrecords=0;

			if(sizeof($pages)==0) $reportr->background->query( "insert into gadirectory (day, path) values ('$day', '- No data for this date -')" );// or die(mysql_error());
			
			foreach( $pages as $p => $data ) {
				foreach( $data as $i => $row ) {
					foreach($row as $key => $value ) $row[$key] = mysql_real_escape_string(str_replace( "(not set)", "", $value ));
					$totalrecords++;
					$reportr->background->query( "insert into gadirectory (day, tobseoshadow, value1, value2, search, landing, mobile, geocountry, georegion, geocity, pageviews, uniquepageviews, path) select '".$row['day']."', '".$row['tob']."', '".$row['value1']."', '".$row['value2']."', '".($row['search'] ? '1' : '0')."', '".($row['landing'] ? '1' : '0' )."', '".( $row['mobile'] ? '1' : '0' )."', '".$row['country']."', '".$row['region']."', '".$row['city']."', '".$row['pageviews']."', '".$row['uniquepageviews']."', '".$row['path']."'" );// or die(mysql_error());
				}
			
			}
			
			$reportr->background->query( "update gadirectory inner join tob on gadirectory.tobseoshadow = tob.seoshadow set tob = tob.code where gadirectory.tob is null" );
			return $totalrecords;

		}
		function GetSealClicks($startdate, $enddate) {
			global $reportr;
			require_once('/home/serv/library/nusoap.php');
	ini_set("display_errors","1");
			$serverpath ='https://www.bbb.org/online/service/sealclickservice.asmx';
			// create client object
			$client = new nusoapclient($serverpath, true, false, false, false, false, 0, 600);
			// make the call
			$bureauid=$reportr->get_var("select setup(318)");
			$curdate=date("Y-m-d", strtotime($startdate));
			while($curdate<$enddate) {
				$year=date("Y", strtotime($curdate));
				$month=strtolower(date("F", strtotime($curdate)));
				$param = array("bbbID"=>"$bureauid", "year"=>"$year", "month"=>"$month");
//				print_r($param);
				$startdate=date("Y-m-d", strtotime("$startdate+1 day"));
				$result = $client->call('GetSealClickData',$param,"http://www.bbbonline.org/", 'http://www.bbbonline.org/GetSealClickData',null, null, "rpc", "literal");
				if(isset($result["faultstring"])) {
					return htmlspecialchars($result["faultstring"])." Zero";
				}
				if(!is_array($result["diffgram"])) {
					$curdate=date("Y-m-d", strtotime($curdate."+1 month"));
					continue;
				}
				$clicks=$result["diffgram"]["NewDataSet"]["Table"];
				if(!is_array($clicks)) return;
				if(!is_array($clicks[0])) $clicks=array($clicks);
				$mycount=0;
//				print_r($clicks); die();
				foreach($clicks as $click) {
					$query = "insert ignore into sealclicksdownload (month, company_id, company_name, corporate_name, phone_number, Main_x0020_URL, Clicks, DownloadedDate) values('".date("Y-m", strtotime($curdate))."', '".addslashes($click["company_id"])."', '".addslashes($click["company_name"])."', '".addslashes($click["corporate_name"])."', '".addslashes($click["phone_number"])."', '".addslashes($click["Main_x0020_URL"])."', '".addslashes($click[$month])."', now())";
					$reportr->query($query);
					if($reportr->affected_rows>0) $mycount+=$reportr->affected_rows;
					//echo $query."\r\n";
				}
				$curdate=date("Y-m-d", strtotime($curdate."+1 month"));
				unset($clicks);
			}
			unset($result);
			unset($client);
			if($mycount==0) $mycount="Zero";
			return $mycount;
	
		}

	}




}

?>
