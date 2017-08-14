<?
class editr extends mybindr {

	var $alias;
	var $dropdowns;
	var $tag;
	var $javascript;
	var $TitleCaseFields = array("address.street1"=>'address', "address.street2"=>'address', "address.city"=>'address', "person.firstname"=>'contact', "person.lastname"=>'contact', "person.title"=>'contact', "person.prename"=>'contact', "person.postname"=>'contact', "dba.name"=>'dba', "main.name"=>'dba', "main.street1"=>'address', "main.street2"=>'address', "main.city"=>'address', "main.firstname"=>'contact', "main.lastname"=>'contact', "main.prename"=>'contact', "main.title"=>'contact');
	
	function editr() {
		$this->mybindr();
		$this->addparm("enctype", "");
		$maxup=str_replace("M","",ini_get("post_max_size"));
		if($maxup>str_replace("M","",ini_get("upload_max_filesize"))) $maxup=str_replace("M","",ini_get("upload_max_filesize"));
		$this->addparm("max upload message", "Uploaded files cannot exceed $maxup MB in size");
	}
	
	function noyes($query) {
		unset($this->alias);
		$query=$this->removealias($query);
		for($i=0;$i<sizeof($this->alias);$i++) {
			$this->alias[$i] = $this->ResolveMerge($this->alias[$i]);
			$this->alias[$i] = ereg_replace("^ *'(.*)' *$", "\\1", $this->alias[$i]);
			$this->alias[$i] = ereg_replace("^ *`(.*)` *$", "\\1", $this->alias[$i]);
			$this->alias[$i] = str_replace(" ", "_", $this->alias[$i]);
			if($_POST[$this->alias[$i]]=='y') {
				if(eregi("custom:(.*)_",$this->alias[$i],$regs)) {
					$ret[]="custom".$regs[1];
				} else {
					eregi("keyword:(.*)_", $this->alias[$i], $regs);
					$ret[]=$regs[1];
				}
			} elseif(eregi("zipcode",$this->alias[$i]) || eregi("postal",$this->alias[$i])) {
				$myzip = strtoupper($_POST[$this->alias[$i]]);
				if(eregi('[a-z]', $myzip)) $myzip=substr($myzip,0,3); else $myzip=substr($myzip,0,5);
				$query = "select ifnull(zipcity.bureau like concat(value,'%'),0), zipcity.bureau from common.zipcity left join setup on code=318 and bureau like concat(value,'%') where zip='".$_POST[$this->alias[$i]]."'";
				$query = "select ifnull(bureauarea.bid like concat(value,'%'),0), bureauarea.bid from common.bureauarea left join setup on code=318 and bid like concat(value,'%') where '".$myzip."' between startpostalcode and endpostalcode";
//echo $query;
				$result=$this->mybindr_query($query);
				if(mysql_num_rows($result)>0) { //$ret;
					$row=mysql_fetch_row($result);
					if($row[0]==0) $ret[]="BBB ".$row[1];
				} else $ret[]="BBB 9999";
			}
		}
		return $ret;
	}
	function setform($query, $updateallfield=false) {
		global $parse, $reportr;
		unset($this->alias);
		$query=$this->removealias($query);
		$result = $this->mybindr_query($query);
		if(!$result) return;
		$fields = $this->getinfo($result);
		$tables = $this->gettables($query);
		$row = mysql_fetch_row($result);
		$update = "update $tables set ";
		for($i=0;$i<sizeof($fields);$i++) {
			if($fields[$i]->sends3public && isset($_FILES[$fields[$i]->name]) && file_exists($_FILES[$fields[$i]->name]['tmp_name'])) {
//			die($fields[$i]->sends3public);
				if(eregi("[.]([^.]+)$", $_FILES[$fields[$i]->name]["name"], $regs)) $ext=strtolower($regs[1]);
				$fields[$i]->sends3public.=".".$ext;
				$this->sends3public($fields[$i]->sends3public, array("file"=>$_FILES[$fields[$i]->name]['tmp_name']));
			}
			if($fields[$i]->isfile && isset($_FILES[$fields[$i]->name]) && file_exists($_FILES[$fields[$i]->name]['tmp_name'])) {
				$_POST[$fields[$i]->name]=$_FILES[$fields[$i]->name]["name"];
				if($fields[$i]->table) {
					$fp = fopen($_FILES[$fields[$i]->name]['tmp_name'], "r");
					$update.=$fields[$i]->table.".".$fields[$i]->name."_file=";
					$update.= "'".str_replace("\n","\\n",str_replace("\r","\\r",addslashes(fread($fp,1048576))))."', \r\n";
					fclose($fp);
				} elseif ($fields[$i]->browsefolder>'') {
					$parse->params[$fields[$i]->name]=$_FILES[$fields[$i]->name]["name"];
					@mkdir($this->GetScanDir("bid", ereg_replace("^-","",$this->params["bid"]))."/".$fields[$i]->browsefolder);
					@move_uploaded_file($_FILES[$fields[$i]->name]['tmp_name'], $this->docsdir("bid", ereg_replace("^-","",$this->params["bid"]))."/".$fields[$i]->browsefolder."/".$_FILES[$fields[$i]->name]["name"]);
				} else {
					$parse->params[$fields[$i]->name]=$_FILES[$fields[$i]->name]["name"];
					$parse->params[$fields[$i]->name."_file"]=$_FILES[$fields[$i]->name]['tmp_name'];
				}
			}
			if($fields[$i]->isdate && $fields[$i]->table=="") {
				$_POST[$fields[$i]->name]=$this->cleanvals($fields[$i]);
				$parse->params[$fields[$i]->name]=$_POST[$fields[$i]->name];
			}

			if (!($fields[$i]->table>"" && eregi("ST123".$fields[$i]->name."ST123", $fieldlist))) {
				if($fields[$i]->table>"") $fieldlist.="ST123".$fields[$i]->name."ST123,";
				$_POST[$fields[$i]->name]=$this->cleanvals($fields[$i]);
				$_POST[$fields[$i]->name."_old"]=$this->cleanvals($fields[$i], "_old");
//				if($i==11) die(print_r($_POST,true));
				if($fields[$i]->table>"" || $fields[$i]->blob /* added per ticket 1415378345 so special characters would be "cleaned" properly in BLOB/memo fields that don't have underlying table field */) {
					$this->params[$fields[$i]->name]=$_POST[$fields[$i]->name];
					$parse->params[$fields[$i]->name]=$_POST[$fields[$i]->name];
					$this->params[$fields[$i]->name."_old"]=$_POST[$fields[$i]->name."_old"];
					$parse->params[$fields[$i]->name."_old"]=$_POST[$fields[$i]->name."_old"];
				}
				$setval=0;
				if(!isset($this->params["bid"]) && eregi("bid",$fields[$i]->name)) $this->params["bid"]=$_POST[$fields[$i]->name."_old"];
				if($fields[$i]->set) {
					$ii=0;
					if($fields[$i]->pseudoset) {
						$setval="";
						foreach($fields[$i]->set as $item) {
							$setval.=($_POST[$fields[$i]->name.($ii++)]>0?$item.",":"");
						}
						$setval="'".substr($setval,0,strlen($setval)-1)."'";
					} else {
						$setval="";
						foreach($fields[$i]->set as $item) {
							$setval.=($_POST[$fields[$i]->name.($ii++)]>0?$item.",":"");//    +=$_POST[$fields[$i]->name.($ii++)];
						}
						$setval="'".substr($setval,0,strlen($setval)-1)."'";
					}
					if($fields[$i]->table) {
						$update.=$fields[$i]->table.".".$fields[$i]->name."=";
						$update.= $setval.", \r\n";
					}
					$this->params[$fields[$i]->name]=$setval;
					$parse->params[$fields[$i]->name]=$setval;
					$_POST[$fields[$i]->name]=trim($setval,"'");
				}
				if(/* !$fields[$i]->hidden && */$fields[$i]->table>"" && (($_POST[$fields[$i]->name] != $_POST[$fields[$i]->name."_old"]) || (strlen($_POST[$fields[$i]->name]) != strlen($_POST[$fields[$i]->name."_old"])) || $updateallfield)) {
					$fieldupdates[$fields[$i]->table.".".$fields[$i]->name]=array($_POST[$fields[$i]->name."_old"], $_POST[$fields[$i]->name]);
					$myfieldlist.=" or find_in_set('".$fields[$i]->table.".".$fields[$i]->name."',Name)>0";
					if($fields[$i]->table=="special") { $myfieldlist.=" or tablename='special'"; }
					$update.=$fields[$i]->table.".".$fields[$i]->name."=";
					if($fields[$i]->ismoney || $fields[$i]->istotal) $fields[$i]->dollar=100; else $fields[$i]->dollar=1;
					if($fields[$i]->type=="int")
						$update.=($_POST[$fields[$i]->name]*$fields[$i]->dollar+0).", \r\n";
					else if($fields[$i]->isdate)
						$update.="date_format('".$_POST[$fields[$i]->name]."','%Y-%m-%d'), \r\n";
					else if($fields[$i]->isdatetime)
						$update.="date_format('".$_POST[$fields[$i]->name]."','%Y-%m-%d %H:%i:%s'), \r\n";
					else if($fields[$i]->istime) {
						$value=ereg_replace("[^0-9AaPpMm:]","",$_POST[$fields[$i]->name]);
						if(strpos($value,":")===false) $value=ereg_replace("([0-9])([^0-9]|$)", "\\1:00\\2", $value);
						if(!eregi("^([0-9]+):([0-9]+)(.*)$", $value, $regs))
							$value='null';
						else {
							list(,$hour, $minute, $ampm)=$regs;
							if($hour>24) $hour=0;
							if(($hour<7 && !eregi("a",$ampm)) || ($hour>6 && $hour<12 && eregi("p",$ampm))) $hour+=12;
							if($minute<0) $minute=0;
							if($minute>59) $minute=59;
							$value="'".$hour.":".$minute.":00'";
						}
						$update.=$value.", \r\n";
						$parse->params[$fields[$i]->name]=str_replace("'","",$value);
					} else
						$update.="'".$_POST[$fields[$i]->name]."', \r\n";
				}
			} 
			//elseif(1) { die(print_r($fields[$i],true)); $parse->params[$fields[$i]->name]=$this->cleanvals($fields[$i]); }
			//die(print_r($fields,true));
			if($fields[$i]->primary_key) {
				if($fields[$i]->primary_key && mysql_field_type($result, $i)=="string" && !eregi("^(bid|cid)$",$fields[$i]->name)) $this->addparm("MY_KEY2", $_POST[$fields[$i]->name]); elseif($fields[$i]->primary_key && !eregi("^(bid|cid)$",$fields[$i]->name)) $this->addparm("MY_KEY1", $_POST[$fields[$i]->name."_old"]);
				$where.=$fields[$i]->table.".".$fields[$i]->name."=";
				if(ereg("enum",$fields[$i]->flags) && eregi("[a-z]",$_POST[$fields[$i]->name."_old"]))
					$where.="'".$_POST[$fields[$i]->name."_old"]."' and \r\n";
				elseif($fields[$i]->type=="int" || ereg("enum",$fields[$i]->flags))
					$where.=$_POST[$fields[$i]->name."_old"]." and \r\n";
				else if($fields[$i]->isdate)
					$where.="date_format(\"".$_POST[$fields[$i]->name]."\",\"%Y-%m-%d\") and \r\n";
				else
					$where.="\"".$_POST[$fields[$i]->name."_old"]."\" and \r\n";
			}
		}
		$where=substr($where,0,strlen($where)-7);
		$myfieldlist=substr($myfieldlist,4);
//		die($myfieldlist);
//		print_r($fieldupdates); exit();
		if(ereg("([-+])$", $reportr->current_query, $regs) || $myfieldlist!='') {
			$changetype="Edit";
			if($regs[1]=="+" && $reportr->current_query!="lite button m+") $changetype='Insert';
			if($regs[1]=="-" && $reportr->current_query!="lite button m-") $changetype='Delete';
			$sql = "select FID, Name from common.auditfield where $myfieldlist";
			$result = $this->mybindr_query($sql,$this->database, false);
			if(mysql_num_rows($result)>0) {
				while($row=mysql_fetch_row($result)) {
					foreach($fieldupdates as $key => $value) {
						if((eregi($key,$row[1]) || ($row[1]=="special" && ereg("special", $key))) && $changetype=='Edit') $updates[$row[0]].=",".substr($key,strpos($key,".")+1)."=>".$value[0]."=>".$value[1];
						elseif($changetype=='Edit') $updates[999].=",".$key."=>".$value[0]."=>".$value[1];
						if(eregi($key,$row[1]) && $changetype=='Insert') $updates[$row[0]].=",".substr($key,strpos($key,".")+1)."=>".$value[1];
					}
				} 
				foreach($updates as $key => $value) {
					if($key==37 && $this->params["bid"]<0) continue; //per ticket 1240933923 // $value=",WHAT IF: ".substr($value,1); // per ticket 1237478031
					$this->addparm("MY_VALUE", str_replace("|","',char(124),'",substr($value,1)));
/*					$replaceformat="'DATE=>',now(),',STAFF=>|staff|,|OLD_VALUE|',char(124),'".str_replace("\r\n",'',$where)."'";
					$sql = "replace into audit select ifnull(a.bid,|bid|), ifnull(a.fid,$key), ifnull(concat($replaceformat,\"\r\n\",a.history), concat($replaceformat)) from audit a where bid=|bid| and fid=$key having count(*)=1 or count(*)=0";*/
					$sql = "insert into changeaudit (FID, Type, BID, CID, Key1, Key2, Day, Staff, History) values ($key, '$changetype', if('|bid|'='',null,abs('|bid|')), if('|cid|'=0,null,abs('|cid|')), if('|MY_KEY1|'='',null,'|MY_KEY1|'), if('|MY_KEY2|'='',null,'|MY_KEY2|'), now(), '|reportr_username|', concat('|MY_VALUE|'))";
					$result = $this->mybindr_query($this->ResolvePipes($sql),"", false);
				}
			} elseif($changetype=="Delete") {
				$updates="";
				$sql = "select FID, tablename from common.auditfield where button='".ereg_replace("[-+]$","",$reportr->current_query)."' and button>'' limit 1";
				if(list($FID, $tablename)=mysql_fetch_row($this->mybindr_query($sql,"",false))) {
					$r = $this->mybindr_query("select * from $tablename limit 0","",false);
					for($ii=0;$ii<mysql_num_fields($r);$ii++) if(eregi("primary_key",mysql_field_flags($r,$ii))) {
						if(eregi("^bid$",mysql_field_name($r, $ii))) $mywhere.=" and bid=|bid|";
						elseif(eregi("^cid$",mysql_field_name($r, $ii))) $mywhere.=" and cid=|cid|";
						else {
							$mywhere.=" and ".mysql_field_name($r, $ii)."='|key2|'";
							if(mysql_field_type($r,$ii)=="string") $this->addparm("MY_KEY2", $this->params["key2"]); else  $this->addparm("MY_KEY1", $this->params["key2"]);
						}
					}
					$r = $this->mybindr_query($this->ResolvePipes("select * from $tablename where ".substr($mywhere,5)),"",false);
					$ii=0;
					if(mysql_num_rows($r)>0) {
						$row = mysql_fetch_array($r, MYSQL_ASSOC);
						foreach($row as $key=>$value) {
							if(!eregi("primary_key",mysql_field_flags($r,$ii)) || mysql_field_type($r,$ii)=="string") $updates.=", $key:".addslashes($value);
							$ii++;
						}
					}
					$this->addparm("MY_VALUE", substr($updates,2));
					
				}
				if($updates>'') { 
					$this->DeleteSQL = "insert into changeaudit (FID, Type, BID, CID, Key1, Key2, Day, Staff, History) values ($FID, 'Delete', if('|bid|'='',null,'|bid|'), if('|cid|'=0,null,'|cid|'), if('|MY_KEY1|'='',null,'|MY_KEY1|'), if('|MY_KEY2|'='',null,'|MY_KEY2|'), now(), '|reportr_username|', concat('|MY_VALUE|'))";
				}
			} else {
				foreach($fieldupdates as $key => $value) {
					if($changetype=='Edit') $updates[999].=",".$key."=>".$value[0]."=>".$value[1];
					if($changetype=='Insert') $updates[999].=",".$key."=>".$value[1];
				}
				foreach($updates as $key => $value) {
					$this->addparm("MY_VALUE", str_replace("|","',char(124),'",substr($value,1)));
					$sql = "insert into changeaudit (FID, Type, BID, CID, Key1, Key2, Day, Staff, History) values ($key, '$changetype', if('|bid|'='',null,abs('|bid|')), if('|cid|'=0,null,abs('|cid|')), if('|MY_KEY1|'='',null,'|MY_KEY1|'), if('|MY_KEY2|'='',null,'|MY_KEY2|'), now(), '|reportr_username|', concat('|MY_VALUE|'))";
					$result = $this->mybindr_query($this->ResolvePipes($sql),"", false);
				}
			}
		}
		$query = substr($update,0,strlen($update)-4)." where $where";
		if($query=="update   where ") return "select null";
//		echo $query;exit();
	//	$result = mysql_db_query($this->database, $query, $this->db) or die(mysql_error($result));
		return $query;
	}
	
	function getqueryinfo($query) {
		$query = $this->removealias($query);
		$fields = $this->getinfo($this->mybindr_query($this->resolvepipes($query)." limit 0"));
		for($i=0;$i<sizeof($fields);$i++) {
			if(eregi("auto_increment", $fields[$i]->flags)) {
				$autofield=$fields[$i]; 
				$autonum=$i+1;
			} elseif($fields[$i]->primary_key) $primaryfield=$fields[$i];
		}
		if($primaryfield=="") $primaryfield=$fields[1];
		if($autofield=="") { $autofield=$fields[0]; $autonum=1; }
		$query = eregi_replace("group", "\xFF", $query);
		$query = eregi_replace("order", "\xFF", $query);
		eregi(" from (.*) where ([^\xFF]*)", $query, $regs);
		$tableclause=$regs[1];
		$whereclause=$regs[2];
		if(!$tableclause) {
			eregi(" from (.*)([^\xFF]*)", $query, $regs);
			$tableclause=$regs[1];
		}
		return array($primaryfield, $autofield, $autonum, $tableclause, $whereclause);
	}

	function getdefaultquery($query, $actualdesc="") {
		$origqueryname = $query;
		eregi("([^\+-]*)([\+-]?)\.editr", $query, $regs);
		list($query, $desc) = $this->getquery($regs[1]);
		$query = $this->querygroup($query);
		$query = $query[select][0];
		list($primaryfield, $autofield, $autonum, $tableclause, $whereclause) = $this->getqueryinfo($query);
//		print_r( $this->getqueryinfo($query));
	$query=eregi_replace(" order by .*", "", $query);
		switch($regs[2]) {
			case "-":
				if(!$primaryfield->primary_key)
					$query = "select '!$actualdesc?'
||
delete from $tableclause where $autofield->name='|key$autonum|' and '|!$actualdesc?|'='y'";
				else
					$query = "select '!$actualdesc?'
||
delete from $tableclause where $primaryfield->name='|$primaryfield->name|' and $autofield->name=|key$autonum| and '|!$actualdesc?|'='y'";
				$actualdesc="Delete Record";
//		echo $query;
				break;
			case "+":
				if(eregi("auto_increment", $autofield->flags))
					$lastinsert = "last_insert_id()";
				else
					$lastinsert = "''";
				if($whereclause)
					$query = "insert ignore into $tableclause ($primaryfield->name) values ('|$primaryfield->name|')
||
$query and $autofield->name=$lastinsert";
				else
					$query = "insert ignore into $tableclause ($primaryfield->name) values ('|$primaryfield->name|')
||
$query where $autofield->name=$lastinsert";
				break;
			case "":
//					$query=str_replace($whereclause, "$whereclause and $autofield->name='|key$autonum|' ", $query);
				if($whereclause)
					$query .= " and $autofield->name='|key$autonum|' ";
				else {
					$tableclause=eregi_replace("(.*)\xFF(.*)","\\1",$tableclause);
					$query=str_replace("from $tableclause", "from $tableclause where $autofield->name='|key$autonum|' ", $query);
				}
				break;
		}
		$this->mybindr_query("update ".QUERY_DB.".".QUERY_TABLE." set sqlstatement=\"".addslashes($query)."\" where mergecode=\"$origqueryname\"");
		return array($query, $actualdesc);
	}

	function getform($query, $step=0, $heading="") {
		global $reportr;
		unset($this->alias);
//		exit();
		$query=$query[$step];
		if(ereg("^display ", $query)) {
//		die($this->ResolvePipes(ereg_replace("^display ", "", $query)));
			$result = $this->mybindr_query($this->ResolvePipes(ereg_replace("^display ", "", $query)));
			while($row=mysql_fetch_row($result)) {
				for($i=0;$i<sizeof($row);$i++) $output.=$row[$i];
			}
			return $output;
		}
		$query=$this->ResolvePipes($query);
		$query=$this->removealias($query);
	//	print_r($this->alias);
		$result = $this->mybindr_query($query);
		$fields = $this->getinfo($result);
		$row = mysql_fetch_row($result);
		$div=0;
		$buttons="buttons_next.tpl";
		if(isset($_GET["noback"]) || (isset($_GET["editr"]) && ereg("^lite button [a-z][a-z]\+$",$reportr->current_query, $regss)))
			$buttons = "buttons_next_noback.tpl";
		if(isset($_GET["readonlyeditr"])) $buttons = "buttons_readonly.tpl";
		$this->addparm("title", " ");
		$this->addparm("subtitle", "");
		$this->addparm("readonlyeditrfalse", "");
		$isfirst=true;
		if(!isset($this->params["customfieldlist"])) $this->params["customfieldlist"]="";
		$this->params["SHOW_REQD"]="";
		$this->params["SHOW_COND_REQD"]="";
		$this->params["savereqd"]=(isset($_GET["savereqd"])?"true":"false");
		for($i=0;$i<sizeof($fields);$i++) {
			if(isset($fields[$i]->overridevalue)) $row[$i]=$fields[$i]->overridevalue;
			if($this->alias[$i]=="`NEXT`") {
				if($div==0) $show="inline"; else $show="none";
				if($totalfield)
					$this->addparm("totalscript", "$totalfield=\"$\"+(".ereg_replace("\+$",");\r\n",$totalfields));
				else
					$this->addparm("totalscript", "");		
				$this->addparm("submitstep", ($step+1));
				$this->addparm("submitbackstep", ($step-1));
				$this->addparm("step", $div);
				$this->addparm("nextstep", ($div+1));
				$this->addparm("backstep", ($div-1));
				$this->addparm("show", $show);
				$this->addparm("rows", $output);
				if(strlen($javascript)<6) $javascript="false    ";
				$this->addparm("fieldlist", substr($javascript, 0, strlen($javascript)-4));
				$this->addparm("changelist", $changemadejs);
				$javascript="";
				$setitems='';
				$this->addparm("javascript", $this->merge("js_verify.tpl"));
				$this->addparm("buttons", $this->merge($buttons));
				$buttons="buttons_both.tpl";
				$output="";
				//$this->addparm("heading", $heading);
				
				$form.=$this->merge("div.tpl");
				$this->addparm("title", " ");
				$this->addparm("heading", " ");
				$this->addparm("subtitle", " ");
				$this->params["SHOW_REQD"]="";
				$this->params["SHOW_COND_REQD"]="";
				$div++;
			} elseif (eregi("`TITLE`",$this->alias[$i])) {
				if($row[$i]>"") {
					$this->addparm("title", $row[$i]);
					$this->addparm("htmltitle", strip_tags(str_replace("<br>"," - ",$row[$i])));
				} else {
					$this->addparm("title", $fields[$i]->name);
					$this->addparm("htmltitle", strip_tags(str_replace("<br>"," - ",$fields[$i]->name)));
				}
			} elseif ($this->alias[$i]=="`SUBTITLE`") {
				if($row[$i])
					$this->addparm("subtitle", $row[$i]);
				else
					$this->addparm("subtitle", $fields[$i]->name);
			} elseif ($fields[$i]->table>"" && eregi("ST123".$fields[$i]->name."ST123", $fieldlist)) {
			} else {
				$this->params["input_class"]="";
				$this->addparm("onblur", "");
//				$this->addparm("value","");
//				if($fields[$i]->set && $row[$i]=="") $row[$i]="0";
				$row[$i]=str_replace(chr(0)," ",preg_replace(array("/\xc9/","/xe2\xae/", "/\xae/", "/\xe0/", "/\xe6/", "/\xe7/", "/\xe8/", "/\xe9/", "/\xea/", "/\xeb/", "/\xee/", "/\xef/", "/\xf4/", "/\xfb/", "/\xfc/"), array("&Eacute;","&reg;","&reg;", "&agrave;", "&aelig;", "&ccedil;", "&egrave;", "&eacute;", "&ecirc;", "&euml;", "&icirc;", "&iuml;", "&ocirc;", "&ucirc;", "&uuml;"), $row[$i]));
				if($fields[$i]->cass) $row[$i]="<b><a style='font-color:green;font-size:14px;' href='javascript:dotrigger_".$fields[$i]->name."()'>Click here to certify this address</a></b>";
				if($fields[$i]->approvecc) {
					if(defined("AUTHORIZE_NET_LOGIN_ID")) { $row[$i]="<b><a id='process_cc_link' style='font-color:green;font-size:14px;' onclick='if(!this.disabled) { this.innerText=\"Please wait...\"; this.disabled=true; dotrigger_".$fields[$i]->name."(); }' href='#'>Click here to process this credit card transaction</a></b>";
						$javascript.="\r\nif(RadioValue(document.complaintform.elements[\"CardNumber\"])!='' && RadioValue(document.complaintform.elements[\"ResponseCode\"])!='Approved') fieldlist=fieldlist+\"\\r\\nPlease process the credit card transaction first\";\r\n    ";
					} else $row[$i]="<b>Authorize.net account required for this feature</b>";
				}
				if(is_array($fields[$i]->query) && !eregi("(bid|cid)document", $fields[$i]->alias)) $row[$i]=array_search($row[$i], $fields[$i]->query)+1;
				$this->addparm("FIELD_VALUE", $row[$i]);
				if($fields[$i]->isonly || $fields[$i]->isyesno) $row[$i]=strtolower($row[$i]);
				$this->addparm("iframesrc","/blank.html");
				$this->addparm("sdiframesrc","/blank.html");
				$this->addparm("searchfield","");
				if($fields[$i]->help>"") $this->addparm("onfocus", "self.status='".$fields[$i]->help."';");
				else $this->addparm("onfocus", "");
				if($fields[$i]->help>"") $this->addparm("onfocus", "document.getElementById('helptext').innerText='".addslashes($fields[$i]->help)."';");
				else $this->addparm("onfocus", "");
				if($fields[$i]->scrub) {
					$this->addparm("focusrows","20");
					$this->addparm("focuscols","100");
					$this->addparm("scrub image","<div style='float:left;margin:5px;'><img class='scrub' lang='".$fields[$i]->name."' src='/ebindr/images/icons16x/scrub.jpg' border=0><br><img class='scrub-one' lang='".$fields[$i]->name."' src='/ebindr/images/icons16x/redact-one.jpg' border=0><br><img class='scrub-multi' lang='".$fields[$i]->name."' src='/ebindr/images/icons16x/redact-multi.png' border=0></div>"); 
				} else {
					$this->addparm("focusrows","10");
					$this->addparm("focuscols","50");
					$this->addparm("scrub image","");
				}
				$this->addparm("help", $fields[$i]->help);
				if($fields[$i]->extrahelp>"") {
					$this->addparm("helptext", $fields[$i]->extrahelp);
					$this->addparm("extrahelp", $this->merge("extrahelp.tpl"));
					$this->addparm("onclickhelp", $this->merge("extrahelponclick.tpl"));
					$this->addparm("extrahelpclass", "help ");
				} else {
					$this->addparm("extrahelp", "");
					$this->addparm("onclickhelp", "");
					$this->addparm("extrahelpclass", "");
				}
				if($fields[$i]->table>"") $fieldlist.="ST123".$fields[$i]->name."ST123,";
/*				$row[$i]=ereg_replace('\[', '%5B', $row[$i]);
				$row[$i]=ereg_replace('\]', '%5D', $row[$i]);
				$row[$i]=ereg_replace('<', '&lt;', $row[$i]);
				$row[$i]=ereg_replace('>', '&gt;', $row[$i]); */
				if($row[$i])
					$this->addparm("message", $row[$i]);
				elseif(trim($fields[$i]->alias)=='')
					$this->addparm("message", $fields[$i]->name);
				else
					$this->addparm("message", ""); //$fields[$i]->name);
				if(trim($fields[$i]->alias)=='') {
					$fields[$i]->name=""; 
				}
				if($fields[$i]->table>'' && $fields[$i]->name=="Staff" && $row[$i]!=$_COOKIE["reportr_username"] && ereg("^lite button ([a-z][a-z])$", $reportr->current_query, $readregs)) {
					list($readonlystaff)=mysql_fetch_row($this->mybindr_query("select readonlystaff from staff where initials='".$_COOKIE["reportr_username"]."'"));
					if(ereg($readregs[1], $readonlystaff)) {
						$_GET["readonlyeditr"]="YES";
						$this->addparm("readonlyeditrfalse", "return false;");
					}

				}
				if($fields[$i]->oneline) $double="2_1";
				elseif(!ereg('NODOUBLE', $fields[$i]->alias) && $fields[$i]->isyesno && strlen(eregi_replace("<!--.*-->","",$fields[$i]->alias))>30 && strlen(eregi_replace("<!--.*-->","",$fields[$i]->alias))<50) $double="3";
				elseif(!ereg('NODOUBLE', $fields[$i]->alias) && strlen(eregi_replace("<!--.*-->","",$fields[$i]->alias))>30) $double="2"; 
				else $double="";
				if($fields[$i]->pseudoset || $fields[$i]->set || $fields[$i]->alias=='NOTHING') {
/*					$ifs="";
					for($setnum=0;$setnum<sizeof($fields[$i]->set);$setnum++)
						$ifs.=" || document.complaintform.elements[\"".$fields[$i]->name.$setnum."\"].checked != document.complaintform.elements[\"".$fields[$i]->name.$setnum."\"].defaultChecked";
					$changemadejs.="\r\nif(".substr($ifs,4).") changelist=changelist+\"\\r\\n".$fields[$i]->name."\";\r\n";*/
				} elseif($fields[$i]->query && $fields[$i]->type=='int' && !$fields[$i]->isprotectcol) {
					$changemadejs.="\r\nif(RadioValue(document.complaintform.elements[\"".$fields[$i]->name."\"]) != '$row[$i]') changelist=changelist+\"\\r\\n".$fields[$i]->name."\";\r\n";
					$lastchangemadejs="\r\nif(RadioValue(document.complaintform.elements[\"".$fields[$i]->name."\"]) != '$row[$i]') changelist=changelist+\"\\r\\n".$fields[$i]->name."\";\r\n";
				} elseif (!$fields[$i]->isprotectcol) {
					$changemadejs.="\r\nif(RadioValue(document.complaintform.elements[\"".$fields[$i]->name."\"]) != RadioValue(document.complaintform.elements[\"".$fields[$i]->name."_old\"])) changelist=changelist+\"\\r\\n".$fields[$i]->name."\";\r\n";
				}
				if(strlen($row[$i])<20 && ereg("%([^%:]+)%([^%]+:[^%]+)%", $row[$i], $regs)) {   
						list(, $fields[$i]->name, $fields[$i]->reqdcond)=$regs;
						$reqdcondmessage=eregi_replace("<[^<>]*>","",$row[$i]);
				} else $reqdcondmessage=$fields[$i]->alias;
				if($fields[$i]->isreqd) {
					$this->params["SHOW_REQD"]=" ";
					$editrow_tpl = "editrow_reqd$double.tpl";
					if($fields[$i]->isreqdnum)
						$javascript.="\r\nif(document.complaintform.elements[\"".$fields[$i]->name."\"].value==0) fieldlist=fieldlist+\"\\r\\n".ereg_replace("<br>.+", "", ereg_replace("(.{3,})<!--.+-->","\\1",$fields[$i]->alias))."\";\r\n    ";
					if($fields[$i]->isyesno && $fields[$i]->name) 
						$javascript.="\r\nif(!document.complaintform.elements[\"".$fields[$i]->name."\"][0].checked && !document.complaintform.elements[\"".$fields[$i]->name."\"][1].checked) fieldlist=fieldlist+\"\\r\\n".ereg_replace("<br>.+", "", ereg_replace("(.{3,})<!--.+-->","\\1",$fields[$i]->alias))."\";\r\n    ";
//						$javascript.="\r\n(!document.complaintform.elements[\"".$fields[$i]->name."\"][0].checked && !document.complaintform.elements[\"".$fields[$i]->name."\"][1].checked) || ";
					elseif($fields[$i]->name) 
						$javascript.="\r\nif(RadioValueNoSpace(document.complaintform.elements[\"".$fields[$i]->name."\"])=='') fieldlist=fieldlist+\"\\r\\n".ereg_replace("<br>.+", "", ereg_replace("(.{3,})<!--.+-->","\\1",$fields[$i]->alias))."\";\r\n    ";
//						$javascript.="\r\nRadioValue(document.complaintform.elements[\"".$fields[$i]->name."\"])=='' || ";
					elseif($fields[$i]->isyesno && $fields[$i]->alias) 
						$javascript.="\r\nif(!document.complaintform.elements[\"".$fields[$i]->alias."\"][0].checked && !document.complaintform.elements[\"".$fields[$i]->alias."\"][1].checked) fieldlist=fieldlist+\"\\r\\n".ereg_replace("<br>.+", "", ereg_replace("(.{3,})<!--.+-->","\\1",$fields[$i]->alias))."\";\r\n    ";
//						$javascript.="\r\n(!document.complaintform.elements[\"".$fields[$i]->alias."\"][0].checked && !document.complaintform.elements[\"".$fields[$i]->alias."\"][1].checked) || ";
					elseif($fields[$i]->alias) 
						$javascript.="\r\nif(RadioValue(document.complaintform.elements[\"".$fields[$i]->alias."\"])=='') fieldlist=fieldlist+\"\\r\\n".ereg_replace("<br>.+", "", ereg_replace("(.{3,})<!--.+-->","\\1",$fields[$i]->alias))."\";\r\n    ";
//						$javascript.="\r\nRadioValue(document.complaintform.elements[\"".$fields[$i]->alias."\"])=='' || ";
				} elseif(strlen($fields[$i]->reqdcond)>0) {
					$this->params["SHOW_COND_REQD"]=" ";
					$editrow_tpl = "editrow_reqdcond$double.tpl";
					list($field, $value) = explode(":", $fields[$i]->reqdcond);
					if($value=="ENTERED")
						$javascript.="\r\nif((document.complaintform.elements[\"".$fields[$i]->name."\"].value=='' && RadioValue(document.complaintform.elements[\"".$field."\"])>'')) fieldlist=fieldlist+\"\\r\\n".ereg_replace("<br>.+", "", ereg_replace("(.{3,})<!--.+-->","\\1",$fields[$i]->alias))."\";\r\n    ";
					else
						$javascript.="\r\nif((document.complaintform.elements[\"".$fields[$i]->name."\"].value=='' && RadioValue(document.complaintform.elements[\"".$field."\"])=='$value')) fieldlist=fieldlist+\"\\r\\n".ereg_replace("<br>.+", "", ereg_replace("(.{3,})<!--.+-->","\\1",$fields[$i]->alias))."\";\r\n    ";
				} else
					$editrow_tpl = "editrow$double.tpl";
				if($fields[$i]->confirm) {
					$javascript.="\r\nif(document.complaintform.elements[\"".$fields[$i]->name."\"].value!=document.complaintform.elements[\"".eregi_replace("^confirm","",$fields[$i]->name)."\"].value) fieldlist=fieldlist+\"\\r\\n".$fields[$i]->alias." does not match ".eregi_replace("^confirm *","",$fields[$i]->alias)."\";\r\n    ";
				}
				if($fields[$i]->alias=='') $editrow_tpl="editrow_blank.tpl";
				if($fields[$i]->alias=='NOTHING') $editrow_tpl="editrow_nothing.tpl";
				$myinput=$this->getinput($fields[$i]);
				if($fields[$i]->isonly && $row[$i]=='y') $myinput="input_radio_yes.tpl";
				if(!($fields[$i]->approvecc) && !($fields[$i]->cass) && !ereg('^@',$fields[$i]->name) && trim($fields[$i]->alias)>'' && !isset($fields[$i]->overridevalue) && !isset($fields[$i]->fileupload) && $fields[$i]->table=="" && !strstr($fields[$i]->name,$fields[$i]->alias))
					$this->addparm("name",$fields[$i]->alias);
				else
					$this->addparm("name",$fields[$i]->name);
				$this->addparm("name_CLEAN", ereg_replace("[^a-zA-Z]","",$this->params["name"]));
				$this->addparm("maketitlecase","");
				if(!isset($_GET["ebindr2"]) && TITLE_CASE!="OFF" && ereg("^lite button [a-z]{1,2}$",$reportr->current_query) && array_key_exists(strtolower($fields[$i]->table.".".$fields[$i]->name), $this->TitleCaseFields)) $this->addparm("maketitlecase","if(window.parent && window.parent.edittitlecase) this.value=titleCase(this.value,\"".$this->TitleCaseFields[strtolower($fields[$i]->table.".".$fields[$i]->name)]."\");");
				if(isset($_GET["ebindr2"]) && TITLE_CASE!="OFF" && ereg("^lite button [a-z]{1,2}$",$reportr->current_query) && array_key_exists(strtolower($fields[$i]->table.".".$fields[$i]->name), $this->TitleCaseFields)) $this->addparm("maketitlecase","if(window.parent.ebindr && window.parent.ebindr.data.store.edittitlecase>0) this.value=titleCase(this.value,\"".$this->TitleCaseFields[strtolower($fields[$i]->table.".".$fields[$i]->name)]."\");");
				if(TITLE_CASE!="OFF" && ereg("^lite button [a-z]{1,2}\+$",$reportr->current_query) && array_key_exists(strtolower($fields[$i]->table.".".$fields[$i]->name), $this->TitleCaseFields)) $this->addparm("maketitlecase","this.value=titleCase(this.value,\"".$this->TitleCaseFields[strtolower($fields[$i]->table.".".$fields[$i]->name)]."\");");
				$this->addparm("validatedate","");
				if($fields[$i]->isdatecan && $row[$i]!="") {
					$row[$i]=substr($row[$i],8,2)."/".substr($row[$i],5,2)."/".substr($row[$i],0,4);
				}elseif($fields[$i]->isdate && $row[$i]!="") {
					$row[$i]=substr($row[$i],5,2)."/".substr($row[$i],8,2)."/".substr($row[$i],0,4);
				}
				//$calendaricon="/js-bin/calendar.gif";
				$calendaricon="/ebindr/images/icons16x/calendar.png";//mars 5/24/2014 - to beautify online sbq form
				if(isset($_GET["ebindr2"])) $calendaricon="/ebindr/images/icons16x/calendar.png";
				if($fields[$i]->isdate) {
					$this->params["input_class"]="date";

//					$row[$i]=date("m/d/Y", strtotime($row[$i]));
				}
				if(isset($_GET["ebindr2"]) && $fields[$i]->isdate) {
					$this->params["input_class"]="date";
					$this->params[extrahelp]='<img class="picker" id="dateimg_'.$fields[$i]->name.'" src="'.$calendaricon.'">'.$this->params[extrahelp];
				}elseif($fields[$i]->isdate) {
					$this->params[extrahelp]='<img id="dateimg_'.$fields[$i]->name.'" src="'.$calendaricon.'" onclick=\'var newDateVar=window.showModalDialog("/js-bin/calendar.htm?'.($fields[$i]->isdatecan?"country=Canada&":"").'timeenabled=disabled", document.complaintform.elements["'.$fields[$i]->name.'"], "help:no;status:no;resizable:no;dialogwidth:274px;dialogheight:282px;center:yes;scroll:no;"); if(newDateVar) document.complaintform.elements["'.$fields[$i]->name.'"].value=newDateVar;\'>'.$this->params[extrahelp];
				}
				if($fields[$i]->istime && $row[$i]!="") {
					$row[$i]=date("g:i A", mktime(substr($row[$i],0,2),substr($row[$i],3,2)));
				}
				if($fields[$i]->isdatetime && $row[$i]!="") {
					$row[$i]=substr($row[$i],5,2)."/".substr($row[$i],8,2)."/".substr($row[$i],0,4)." ".date("g:i A", mktime(substr($row[$i],11,2),substr($row[$i],14,2)));
//					$row[$i]=date("m/d/Y", strtotime($row[$i]));
				}
				if(isset($_GET["ebindr2"]) && $fields[$i]->isdatetime) {
					$this->params["input_class"]="datetime";
					$this->params[extrahelp]='<img class="pickertime" src="'.$calendaricon.'">'.$this->params[extrahelp];
				} elseif($fields[$i]->isdatetime) {
					$this->params[extrahelp]='<img src="'.$calendaricon.'" onclick=\'var newDateVar=window.showModalDialog("/js-bin/calendar.htm", document.complaintform.elements["'.$fields[$i]->name.'"], "help:no;status:no;resizable:no;dialogwidth:274px;dialogheight:282px;center:yes;scroll:no;"); if(newDateVar) document.complaintform.elements["'.$fields[$i]->name.'"].value=newDateVar;\'>'.$this->params[extrahelp];
				}
				if($myinput=="input_set.tpl") {
					if($fields[$i]->query) {
						$query = $this->ResolvePipes($fields[$i]->query);
						$result = $this->mybindr_query($query, $this->database , false);
						$numrows=mysql_num_rows($result);
						while($item = mysql_fetch_row($result)) $items[$item[0]]=$item[1];
					}
					$setcount=0;
					$setitemarr=explode(",",$row[$i]);
					$setitems="";
					$ifs="";
					foreach($fields[$i]->set as $setitem) {
						if(in_array($setitem, $setitemarr))
							$this->addparm("checked","checked".($fields[$i]->pseudosetlocked?" disabled":""));
						else
							$this->addparm("checked","");
						$this->addparm("value",pow(2,$setcount).($setitem=='ALL'?' class="'.$fields[$i]->name.'_ALL"':' class="'.$fields[$i]->name.'_ONE"'));
						$this->addparm("name",$fields[$i]->name.($setcount++));					
						$this->addparm("setname",(isset($items[$setitem]) ? $items[$setitem] : $setitem));
						if($fields[$i]->query && !isset($items[$setitem])) {
						} else {
							$setitems .= $this->merge("input_set_items.tpl");
							$this->params["extrahelp"]="";
							$ifs.=" || document.complaintform.elements[\"".$fields[$i]->name.($setcount-1)."\"].checked != document.complaintform.elements[\"".$fields[$i]->name.($setcount-1)."\"].defaultChecked";
						}
					}
					$this->addparm("name",$fields[$i]->name);
					if($ifs>"") $changemadejs.="\r\nif(".substr($ifs,4).") changelist=changelist+\"\\r\\n".$fields[$i]->name."\";\r\n";
					$this->addparm("setitems", $setitems);
				}
				if($fields[$i]->isphone)
					if(strlen($row[$i])>9 && !ereg("^[+]", $row[$i]))
						$row[$i]="(".substr($row[$i],0,3).") ".substr($row[$i],3,3)."-".substr($row[$i],6,4).(strlen($row[$i])>10 ? ' ext '.substr($row[$i],10) : '' );
				$this->addparm("disabled", "");
				if($fields[$i]->ismoney) {
					$row[$i]="$".number_format($row[$i]/100,2,'.',',');
					$totalfields.="GetValue(RadioValue(document.complaintform.elements[\"".$fields[$i]->name."\"]))+";
					if(!$taxfield) $taxfields.="GetValue(RadioValue(document.complaintform.elements[\"".$fields[$i]->name."\"]))+";
					$this->addparm("disabled", " onchange=\"TotalFields();\"");
				}
				if($fields[$i]->taxamt>0) {
					$this->addparm("disabled", " onkeydown=\"if(event.keyCode!=9) event.preventDefault();\"");
					$taxfield="document.complaintform.elements[\"".$fields[$i]->name."\"].value";
					$taxamount=$fields[$i]->taxamt;
				}
				if($fields[$i]->istotal) {
					$row[$i]="$".number_format($row[$i]/100,2,'.','');
					$this->addparm("disabled", " onkeydown=\"if(event.keyCode!=9) event.preventDefault();\"");
					$totalfield="document.complaintform.elements[\"".$fields[$i]->name."\"].value";
				} else $this->addparm("disabled", $this->params["disabled"]." onkeydown='if(event.keyCode==13) return false'");
				if($fields[$i]->isyesno || $fields[$i]->isyesnopend || $fields[$i]->isonly) {
					if($row[$i]=='y') {
						$this->addparm("checkedy","checked");
						$this->addparm("checkedn","");
						$this->addparm("checkedp","");
					} elseif($row[$i]=='n') {
						$this->addparm("checkedy","");
						$this->addparm("checkedn","checked");
						$this->addparm("checkedp","");
					} elseif($row[$i]=='p') {
						$this->addparm("checkedy","");
						$this->addparm("checkedn","");
						$this->addparm("checkedp","checked");
					} else {
						$this->addparm("checkedy","");
						$this->addparm("checkedn","");
						$this->addparm("checkedp","");
					}
				}
				$this->addparm("size", ($fields[$i]->fieldlen+2));
				if($fields[$i]->validate) $this->addparm("validateclass", "VALID ".$fields[$i]->validate); else $this->addparm("validateclass", "");
				$this->addparm("maxlength",$fields[$i]->maxlen);
				if($fields[$i]->primary_key)
					$this->addparm("comment","<script type='text/javascript'>var editrundo".(++$undo)."='".$row[$i]."';window.parent.undo".($undo)."='".$row[$i]."';</script>");
				else
					$this->addparm("comment","");
				if($fields[$i]->triggersql)
					$this->addparm("onblur", "dotrigger_".$this->params["name_CLEAN"]."();");
				if($fields[$i]->searchdrop)
					$this->addparm("options", "<select style='z-index:-1' id=\"".$fields[$i]->name."\" name=\"".$fields[$i]->name."\" onfocus=\"[[onfocus]]\" onblur='self.status=\"\";[[onblur]]'>\r\n</select>");
				else {
					$this->addparm("options", $this->getoptions($fields[$i]->query,$row[$i], $fields[$i]->querydesc, $fields[$i]->name, $fields[$i]));
					if(ereg("^CHANGEINPUT:(.+)$", $this->params["options"], $myops)) {
						$myinput=$myops[1];
						$changemadejs=str_replace($lastchangemadejs, "\r\nif(RadioValue(document.complaintform.elements[\"".$fields[$i]->name."\"]) != RadioValue(document.complaintform.elements[\"".$fields[$i]->name."_old\"])) changelist=changelist+\"\\r\\n".$fields[$i]->name."\";\r\n", $changemadejs);
					} elseif($fields[$i]->query>'' && $fields[$i]->ismoney) {
						$changemadejs=str_replace($lastchangemadejs, "\r\nif(RadioValue(document.complaintform.elements[\"".$fields[$i]->name."\"]) != RadioValue(document.complaintform.elements[\"".$fields[$i]->name."_old\"])) changelist=changelist+\"\\r\\n".$fields[$i]->name."\";\r\n", $changemadejs);
					}
				}
				if($fields[$i]->formatnumber && $row[$i]>999) {
					$row[$i]=number_format($row[$i]);
				}
				$this->addparm("expandedinfo","");
				if(ereg("EXPANDED:(.*)", $fields[$i]->querydesc, $regs)) {
					$this->addparm("expanded_info_url", $regs[1]);
					$this->addparm("expandedinfo", $this->merge("expanded_info.tpl"));
				}
				//if($myinput!='input_textarea.tpl') 
				if($fields[$i]->blob) 
					$this->addparm("value",str_replace(array("&quot;", "&nbsp;", "<", ">", '"', "'") , array("&amp;quot;", "&amp;nbsp;", "&lt;","&gt;",'&quot;','&apos;'),$row[$i])."");
				else
					$this->addparm("value",trim(str_replace("'", "&#39;", str_replace('"','&quot;',$row[$i])."")));
				$this->addparm("description",$fields[$i]->alias);
				$iframe='';
				if($fields[$i]->searchdrop) {
					$this->addparm("searchdropsql", urlencode($fields[$i]->searchdrop));
					$searchdrop=str_replace("%7CSEARCH%7C","<[\"+escape(RadioValue(document.complaintform.".$fields[$i]->name."_search))+\"]>",urlencode($fields[$i]->searchdrop));
					$searchdrop="DATASEARCH^\"+escape(RadioValue(document.complaintform.".$fields[$i]->name."_search))+\"";
//					if($row[$i]>'' || $fields[$i]->querydesc=="FORCE SEARCH") $this->addparm("iframesrc", "[[DIR_JAVASCRIPT]]searchdrop.php?query=".str_replace("%7CSEARCH%7C","<[".$row[$i]."]>",urlencode($fields[$i]->searchdrop))."&name=[[name]]&value=[[value]]&sname=".$fields[$i]->dropdownname);
//					if($row[$i]>'' || $fields[$i]->querydesc=="FORCE SEARCH") $this->addparm("sdiframesrc", "[[DIR_JAVASCRIPT]]searchdrop.php?query=".str_replace("%7CSEARCH%7C","<[".$row[$i]."]>",urlencode($fields[$i]->searchdrop))."&name=[[name]]&value=[[value]]&sname=".$fields[$i]->dropdownname);
					if($row[$i]>'' || $fields[$i]->querydesc=="FORCE SEARCH") $this->addparm("sdiframesrc", "[[DIR_JAVASCRIPT]]searchdrop.php?query=DATASEARCH".urlencode("^".$row[$i])."&name=[[name]]&value=[[value]]&sname=".$fields[$i]->dropdownname);
					$this->addparm("dropdownname", $fields[$i]->dropdownname);
					$this->addparm("searchdropsql", $searchdrop);
					$this->addparm("searchonblur", "dosearchdrop_".$fields[$i]->name."();");
					$this->addparm("searchfield", $this->merge("input_search_field.tpl"));
					$iframe=$this->merge("iframe.tpl");
					$iframe.=$this->merge("searchdrop_js.tpl");
				}
				if($fields[$i]->triggersql) {
					if(ereg("^JAVASCRIPT:", $fields[$i]->triggersql)) {
						$this->addparm("triggersql", ereg_replace("^JAVASCRIPT:","",$fields[$i]->triggersql));
						$this->addparm("onblur", "dotrigger_".$this->params["name_CLEAN"]."();");
						if($iframe=='') $iframe=$this->merge("iframe.tpl");
//						$iframe=$this->merge("iframe.tpl");
						$iframe.=$this->merge("trigger_js_only.tpl");
					} else {
						$this->addparm("triggersql", $fields[$i]->triggersql);
						$triggersql=$fields[$i]->triggersql;
						$triggersql=str_replace("select","99938282f04071859941e18f16efcf42",$triggersql);
						$triggersql=str_replace("'|trigger|'","'<[\"+escape(String(RadioValue(document.complaintform.".$fields[$i]->name.")).replace(\"'\",\"\\\\\"+\"'\"))+\"]>'",$triggersql);
						$triggersql=str_replace("|trigger|","<[\"+escape(RadioValue(document.complaintform.".$fields[$i]->name."))+\"]>",$triggersql);
						$triggersql=ereg_replace("'[|]([^|]+)[|]'", "'<[\"+escape(String(RadioValue(document.complaintform.\\1)).replace(\"'\",\"\"))+\"]>'", $triggersql);
						$triggersql=ereg_replace("[|]([^|]+)[|]", "<[\"+escape(RadioValue(document.complaintform.\\1))+\"]>", $triggersql);
						$triggersql=str_replace("APPROVECC", "APPROVECC&CardNumber=\"+escape(RadioValue(document.complaintform.CardNumber))+\"&cvv2=\"+escape(RadioValue(document.complaintform.cvv2))+\"&ExpiryDate=\"+escape(RadioValue(document.complaintform.ExpiryDate))+\"&Amount=\"+RadioValue(document.complaintform.Amount)+\"&Refund=\"+RadioValue(document.complaintform.Refund)+\"&TransactionID=\"+RadioValue(document.complaintform.TransactionID)+\"&BID=\"+RadioValue(document.complaintform.bid)+\"", $triggersql);
						$triggersql=str_replace("BCASS CERTIFICATION", "BCASS&street1=\"+escape(RadioValue(document.complaintform.bstreet1))+\"&street2=\"+escape(RadioValue(document.complaintform.bstreet2))+\"&city=\"+RadioValue(document.complaintform.bcity)+\"&stateprov=\"+RadioValue(document.complaintform.bstateorov)+\"&postalcode=\"+RadioValue(document.complaintform.bpostalcode)+\"", $triggersql);
						$triggersql=str_replace("CASS CERTIFICATION", "CASS&bid=\"+escape(RadioValue(document.complaintform.bid))+\"&aid=\"+escape(RadioValue(document.complaintform.aid))+\"&street1=\"+escape(RadioValue(document.complaintform.Street1))+\"&street2=\"+escape(RadioValue(document.complaintform.Street2))+\"&city=\"+RadioValue(document.complaintform.City)+\"&stateprov=\"+RadioValue(document.complaintform.StateProv)+\"&postalcode=\"+RadioValue(document.complaintform.PostalCode)+\"", $triggersql);
						$this->addparm("triggersql", $triggersql."&tname=".$fields[$i]->triggername);
						$this->addparm("onblur", "dotrigger_".$this->params["name_CLEAN"]."();");
						if($iframe=='') $iframe=$this->merge("iframe.tpl");
						$iframe.=$this->merge("trigger_js.tpl");
					}
				} elseif(!$fields[$i]->searchdrop) {
					$this->addparm("onblur", "");
					$iframe="";
				}
				//} else
				if($fields[$i]->creditcard) {
					$this->params[onblur].="if(!isValidCreditCard(this.value)) { alert(typeOfCard(this.value)+\" credit card number appears to be invalid\"); this.value=\"BAD:\"+this.value; }";
				}
				if($fields[$i]->fileuploadmulti>0) {
					$allinputs="";
					for($filenum=0;$filenum<$fields[$i]->fileuploadmulti;$filenum++) {
						$allinputs.="<input type='file' name='".$fields[$i]->name.($filenum)."' onchange=\"".($filenum<($fields[$i]->fileuploadmulti-1)?"document.complaintform.".$fields[$i]->name.($filenum+1).".style.display='block';":"")."\" style='clear:both;display:".($filenum==0?"block":"none").";'>";
					}
					$this->addparm("input",$allinputs);
					$output.=$this->merge($editrow_tpl);
				} elseif($fields[$i]->hidden) {
					$output.=$this->merge($myinput);
				} else {
					$this->addparm("input",$iframe.$this->merge($myinput));
					$output.=$this->merge($editrow_tpl);
				}
				$verifyform.=$this->merge("verify_element.tpl");
//				$this->addparm("subtitle", "");
			}
		}
		if(0==$div) $show="inline"; else $show="none";
		$this->addparm("step", $div);
		if($totalfield && $totalfields) {
			$totalscript = "$totalfield=\"$\"+(GetValue(".ereg_replace("\+$","));\r\n",$totalfields);
			$totalscript.="$totalfield=FixValue($totalfield);\r\n";
			if($taxfield) {
				$totalscript="$taxfield=\"$0.00\";\r\n$taxfield=\"$\"+(((".ereg_replace("\+$",")",$taxfields)."*$taxamount)/100);\r\n".$totalscript;
				$totalscript.="\r\n$taxfield=FixValue($taxfield);";
			}
			$this->addparm("totalscript", $totalscript);
		} 		else
			$this->addparm("totalscript", "");		
		$this->addparm("submitstep", ($step+1));
		$this->addparm("submitbackstep", ($step-1));
		$this->addparm("nextstep", ($div+1));
		$this->addparm("backstep", ($div-1));
		$this->addparm("show", $show);
		$this->addparm("rows", $output);
		if(strlen($javascript)<6) $javascript="false    ";
		$this->addparm("changelist", $changemadejs);
		$this->addparm("fieldlist", substr($javascript, 0, strlen($javascript)-4));
		$javascript="";
		$this->addparm("javascript", $this->merge("js_verify.tpl"));
		if(isset($_GET["readonlyeditr"])) $buttons = "buttons_readonly.tpl";
		elseif($div==0 && (isset($_GET[noback]) || (isset($_GET["editr"]) && ereg("^lite button [a-z][a-z]\+$",$reportr->current_query, $regss))))
			$buttons = "buttons_submit.tpl";		
		elseif($div==0 && isset($_GET[backsubmit]))
			$buttons = "buttons_next_submit_back.tpl";		
		elseif($div==0)
			$buttons = "buttons_next_submit.tpl";
		else
			$buttons = "buttons_both_submit.tpl";

		//if(isset($_GET['calendaronlyeditr'])) $buttons = "buttons_calendaronly.tpl";


		$this->addparm("buttons", $this->merge($buttons));
//		$this->addparm("heading", $heading);
		$form.=$this->merge("div.tpl");
		$this->addparm("verifyelements", $verifyform);
		$this->addparm("form", $form);
		return $this->merge("form.tpl");

	}
	
	function getinput($field) {
		if($field->ishtml) 
			return "input_htmlarea.tpl";
		elseif($field->ColorPicker) 
			return "input_colorpicker.tpl";
		elseif($field->blob && !($field->query && ereg("<!--", $field->alias))) 
			if($field->scrub) return "input_textarea_scrub.tpl"; elseif($field->lengthcounter) return "input_textarea_lengthcounter.tpl"; else return "input_textarea.tpl";
		elseif($field->fileupload) {
			$this->addparm("enctype", "enctype=\"multipart/form-data\"");
			return "input_fileupload.tpl";
		} elseif($field->isfile) {
			$this->addparm("enctype", "enctype=\"multipart/form-data\"");
			return "input_attachfile.tpl";
		} elseif($field->isprotectcol)
			return "input_blank.tpl";
		elseif($field->hidden)
			return "input_hidden.tpl";
		elseif($field->set || $field->pseudoset)
			return "input_set.tpl";
		elseif($field->query > "")
			return "input_combo.tpl";
		elseif($field->isyesno || $field->isonly)
			return "input_radio.tpl";
		elseif($field->isyesnopend)
			return "input_radio_pending.tpl";
		elseif($field->alias=="" || $field->alias=="NOTHING" || $field->cass || $field->approvecc)
			return "input_blank.tpl";
		elseif($field->isphone)
			return "input_phone.tpl";
		elseif($field->password)
			return "input_password.tpl";
		else
			return "input_textbox.tpl";
	}
	
	function getoptions($query, $value, $doradio, $name='', $field) {
		if(!$query) return;
		if(is_array($query)) {
			$numrows=sizeof($query);
			$i=1;
			foreach($query as $row) {
				if(is_array($row)) {
					$rows[]=$row;
				} else {
					if(strval($value)==$row) $value=$i;
					$rows[]=array($i++, $row);
				}
			}
		} else {
			$query = $this->ResolvePipes($query);
			$result = $this->mybindr_query($query, $this->database , false);
			$numrows=mysql_num_rows($result);
			while($row = mysql_fetch_row($result)) $rows[]=$row;
			if(!$result) return "";
		}
		if(($numrows>5 && $doradio!="RADIO") || ereg("DROPDOWN",$doradio)) {
			$item_tpl = "input_combo_items.tpl";
			$selected='selected';
		} elseif($numrows<1) {
			$field->query="";
			return "CHANGEINPUT:".$this->getinput($field);
			$item_tpl = $this->getinput($field); //"input_textbox.tpl";
			$selected='none';
		} else {
			$item_tpl = "input_radio_multi_items.tpl";
			$selected='checked';
		}
		$matches=false;
//		while($row = mysql_fetch_row($result)) {
		if(is_array($rows)) {
			foreach($rows as $row) if(strlen($row[1])>$mymax) $mymax=strlen($row[1]);
			foreach($rows as $row) {
				$row[1]=str_replace(chr(0)," ",preg_replace(array("/\xc9/","/xe2\xae/", "/\xae/", "/\xe0/", "/\xe6/", "/\xe7/", "/\xe8/", "/\xe9/", "/\xea/", "/\xeb/", "/\xee/", "/\xef/", "/\xf4/", "/\xfb/", "/\xfc/"), array("&Eacute;","&reg;","&reg;", "&agrave;", "&aelig;", "&ccedil;", "&egrave;", "&eacute;", "&ecirc;", "&euml;", "&icirc;", "&iuml;", "&ocirc;", "&ucirc;", "&uuml;"), $row[1]));
				if($doradio!="RADIO") {
					$row[1]=str_replace("<","&lt;",$row[1]);
					$row[1]=str_replace(">","&gt;",$row[1]);
				}
				$this->addparm("value",str_replace("'", "&#39;", $row[0]));
				$option_bold=eregi("[{]bold[}]", $row[1]);
				$row[1]=eregi_replace("[{]bold[}]", "", $row[1]);
				if(eregi("[{](.+)[}]", $row[1], $option_color)) $option_color=$option_color[1]; else $option_color=false;
				$row[1]=eregi_replace("[{].+[}]", "", $row[1]);
				$this->addparm("dropdown option style","style='".($option_bold?"font-weight: bold; ":"").($option_color?"color: $option_color; ":"")."' ");

				$this->addparm("description",$row[1].(sizeof($rows)<3 && $mymax<5?"&nbsp;":"<br>"));
				$this->addparm("name",$name);
				if ($name == "GuidelineA" || $name == "GuidelineB" || $name == "GuidelineC" ) {
					if( (strval($row[0])==strval($value) && strlen($row[0]) == strlen($value) ) || $numrows==1) {
						$this->addparm("selected", $selected);
						$matches = true;
					} else
						$this->addparm("selected", "");
				} else {
					if($row[0]==$value || $numrows==1) {
						$this->addparm("selected", $selected);
						$matches = true;
					} else
						$this->addparm("selected", "");
				}
				$output.=$this->merge($item_tpl);
			}
		}
		if(!$matches && $selected=='none') {
			$this->addparm("value",$value);
		}
		if(!$matches && ($selected=='selected' || $selected=='checked')) {
			if($value==$name) $value='';
			$this->addparm("value",str_replace("'", "&#39;",$value));
			$this->addparm("description","--- Please select from the list ---<br>");
			$this->addparm("selected", $selected);
			$output.=$this->merge($item_tpl);
		}
		if($selected=='checked')
			return "<script>window.setTimeout(\"[[onblur]]\",300);</script>".$output;
		elseif($selected=='none') {
			$output=$this->merge($item_tpl);
			return "<!-- NOSELECT -->".$output;
		} else
			return "<script>window.setTimeout(\"[[onblur]]\",300);</script><select style='z-index:-1' name=\"$name\" id=\"$name\" onfocus=\"[[onfocus]]\" onblur='self.status=\"\";[[onblur]]' onclick='[[onblur]]' onkeyup='[[onblur]]'>\r\n$output\r\n</select>";
		
	}

}
?>
